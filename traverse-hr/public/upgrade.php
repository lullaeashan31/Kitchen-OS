<?php

/**
 * One-time, browser-based upgrade runner.
 *
 * Applies pending database migrations and backfills signed PDFs for
 * acceptances recorded before PDF generation existed. Needed because this
 * hosting account has no terminal, so `php artisan migrate` is unreachable.
 *
 * Reads the app's existing .env — it never rewrites configuration and never
 * touches storage/, so uploading a new build cannot disturb live data.
 * Gated by a token baked in at build time; safe to re-run, but delete it
 * when you're done.
 */

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

require __DIR__.'/../vendor/autoload.php';

const UPGRADE_TOKEN = '__UPGRADE_TOKEN__';

function page(string $title, string $body, string $status = ''): never
{
    $cls = $status ? " class=\"msg {$status}\"" : '';
    echo <<<HTML
<!doctype html><html><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{$title} — Traverse HR</title><style>
body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;background:#f7f8fa;color:#1a2233;margin:0;padding:1.5rem}
.card{background:#fff;border:1px solid #e3e6ea;border-radius:10px;padding:1.5rem;max-width:620px;margin:1rem auto}
h1{font-size:1.2rem;margin-top:0}.msg{padding:.8rem 1rem;border-radius:8px;margin-bottom:1rem}
.msg.error{background:#fdecea;color:#a12622}.msg.ok{background:#e7f6ec;color:#146c43}
pre{background:#f4f6f9;padding:.8rem;border-radius:8px;overflow-x:auto;font-size:.78rem;line-height:1.5}
.btn{display:inline-block;margin-top:1rem;padding:.6rem 1.2rem;background:#2f5fdb;color:#fff;border:none;
border-radius:6px;font-size:1rem;cursor:pointer;text-decoration:none}.muted{color:#5b6472;font-size:.88rem}
</style></head><body><div class="card"><h1>{$title}</h1><div{$cls}>{$body}</div></div></body></html>
HTML;
    exit;
}

if (! hash_equals(UPGRADE_TOKEN, $_POST['token'] ?? $_GET['token'] ?? '')) {
    page('Upgrade token required', '<p>This link is missing or has the wrong token.</p>', 'error');
}

if (! file_exists(__DIR__.'/../.env')) {
    page('Not installed', '<p>No <code>.env</code> found — this app has not been installed yet. Run <code>install.php</code> first.</p>', 'error');
}

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    DB::connection()->getPdo();
} catch (\Throwable $e) {
    page('Cannot reach the database', '<p>'.htmlspecialchars($e->getMessage(), ENT_QUOTES).'</p>', 'error');
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    Artisan::call('migrate:status');
    $pendingMigrations = substr_count(Artisan::output(), 'Pending');
    // The column only exists after this upgrade's migration runs, so on a
    // first pass every signed acceptance needs a PDF generated.
    $pendingPdfs = \Illuminate\Support\Facades\Schema::hasColumn('employee_documents', 'signed_pdf_path')
        ? (int) DB::table('employee_documents')->where('status', 'signed')->whereNull('signed_pdf_path')->count()
        : (int) DB::table('employee_documents')->where('status', 'signed')->count();
    $t = htmlspecialchars(UPGRADE_TOKEN, ENT_QUOTES);

    page('Upgrade Traverse HR', <<<HTML
<p class="muted">This applies database changes for the new build and generates signed PDFs
for any acceptance recorded before that feature existed. It does not touch your
configuration, your uploaded documents, or anything already signed.</p>
<p><strong>{$pendingMigrations}</strong> pending database change(s)<br>
<strong>{$pendingPdfs}</strong> signed acceptance(s) needing a PDF generated</p>
<form method="POST"><input type="hidden" name="token" value="{$t}">
<button class="btn" type="submit">Run upgrade</button></form>
HTML);
}

$log = '';
try {
    Artisan::call('migrate', ['--force' => true]);
    $log .= trim(Artisan::output())."\n";

    Artisan::call('traverse:backfill-signed-documents');
    $log .= trim(Artisan::output())."\n";

    // Seeders are idempotent (firstOrCreate) — this adds any newly
    // introduced permission without disturbing existing assignments.
    Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\RolesAndPermissionsSeeder', '--force' => true]);
    $log .= trim(Artisan::output())."\n";

    // Clear every cache, not just config: this build adds new routes
    // (Signatories) and new views, and a stale cached route table would
    // make those pages 404 on a site that had been optimised.
    Artisan::call('optimize:clear');
    $log .= trim(Artisan::output())."\n";
} catch (\Throwable $e) {
    page('Upgrade failed', '<p>'.htmlspecialchars($e->getMessage(), ENT_QUOTES).'</p><pre>'
        .htmlspecialchars($log, ENT_QUOTES).'</pre>', 'error');
}

page('Upgrade complete', '<p>Done. <strong>Delete <code>public/upgrade.php</code></strong> now.</p><pre>'
    .htmlspecialchars(trim($log), ENT_QUOTES).'</pre>'
    .'<a class="btn" href="/login">Go to Traverse HR</a>', 'ok');
