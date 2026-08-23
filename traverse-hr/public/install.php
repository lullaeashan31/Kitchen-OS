<?php

/**
 * One-time, browser-based installer for Traverse HR.
 *
 * Point of this file: let someone with only cPanel File Manager (no
 * terminal, no composer, no artisan) go live by uploading the app and
 * visiting one URL, the same way they'd deploy a plain PHP script. It
 * writes .env, runs migrations + seeders, and creates the first Super
 * Admin — everything DEPLOY.md's Path A does from the command line.
 *
 * Gated by a random token baked in at build time, and locks itself
 * permanently after a successful run (storage/installed.lock). Delete
 * this file once you're done, via File Manager — it's not needed again.
 */

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

require __DIR__.'/../vendor/autoload.php';

const SETUP_TOKEN = '__SETUP_TOKEN__'; // replaced at build time with a random value

$lockPath = __DIR__.'/../storage/installed.lock';
$envPath = __DIR__.'/../.env';

function h(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES);
}

function render(string $title, string $body, string $status = ''): never
{
    $statusBlock = $status ? "<div class=\"msg {$status}\">".$body.'</div>' : $body;
    echo <<<HTML
<!doctype html>
<html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>{$title} — Traverse HR setup</title>
<style>
body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;background:#f7f8fa;color:#1a2233;margin:0;padding:1.5rem;}
.card{background:#fff;border:1px solid #e3e6ea;border-radius:10px;padding:1.5rem;max-width:480px;margin:1rem auto;}
h1{font-size:1.2rem;margin-top:0;}
label{display:block;font-size:.85rem;color:#5b6472;margin:.7rem 0 .2rem;}
input{width:100%;padding:.55rem;border:1px solid #e3e6ea;border-radius:6px;font-size:1rem;box-sizing:border-box;}
.btn{display:inline-block;margin-top:1.2rem;padding:.6rem 1.2rem;background:#2f5fdb;color:#fff;border:none;border-radius:6px;font-size:1rem;cursor:pointer;width:100%;}
.msg{padding:.8rem 1rem;border-radius:8px;margin-bottom:1rem;}
.msg.error{background:#fdecea;color:#a12622;}
.msg.ok{background:#e7f6ec;color:#146c43;}
.muted{color:#5b6472;font-size:.85rem;}
fieldset{border:1px solid #e3e6ea;border-radius:8px;margin-top:1rem;padding:.5rem 1rem 1rem;}
legend{font-size:.85rem;color:#5b6472;padding:0 .3rem;}
</style></head>
<body><div class="card"><h1>{$title}</h1>{$statusBlock}</div></body></html>
HTML;
    exit;
}

if (file_exists($lockPath)) {
    render('Already installed', '<p>Traverse HR has already been set up on this server.</p>'
        .'<p class="muted">If you genuinely need to re-run this (e.g. a fresh database), delete '
        .'<code>storage/installed.lock</code> via cPanel File Manager first — not recommended on a live site '
        .'with real data, since it will re-seed the base data.</p>', 'error');
}

$token = $_POST['token'] ?? $_GET['token'] ?? '';
if (! hash_equals(SETUP_TOKEN, $token)) {
    render('Setup token required', '<p>This link is missing or has the wrong setup token.</p>'
        .'<p class="muted">Use the exact URL given to you, including the <code>?token=...</code> part.</p>', 'error');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    render('Set up Traverse HR', <<<HTML
<p class="muted">Fill this in once. You'll need a MySQL database already created in cPanel
(MySQL® Databases icon) before you start — this doesn't create the database itself, only
connects the app to it.</p>
<form method="POST">
<input type="hidden" name="token" value="{$token}">
<fieldset><legend>Database (from cPanel → MySQL® Databases)</legend>
<label>Database host</label><input name="db_host" value="127.0.0.1" required>
<label>Database name</label><input name="db_database" required placeholder="e.g. traverseinc_traverse_hr">
<label>Database username</label><input name="db_username" required placeholder="e.g. traverseinc_hr_app">
<label>Database password</label><input name="db_password" type="password" required>
</fieldset>
<fieldset><legend>This site</legend>
<label>Site URL</label><input name="app_url" value="https://hr.traverseinc.in" required>
</fieldset>
<fieldset><legend>Your Super Admin account</legend>
<label>Your name</label><input name="admin_name" required>
<label>Your email</label><input name="admin_email" type="email" required>
<label>Password (min. 12 characters)</label><input name="admin_password" type="password" minlength="12" required>
</fieldset>
<fieldset><legend>Optional — outgoing mail (leave blank to configure later)</legend>
<label>SMTP host</label><input name="mail_host" placeholder="e.g. mail.traverseinc.in">
<label>SMTP username</label><input name="mail_username">
<label>SMTP password</label><input name="mail_password" type="password">
<label>From address</label><input name="mail_from_address" placeholder="hr@traverseinc.in">
<label>Security alert email (repeated-login-failure alerts)</label><input name="security_alert_email" type="email">
</fieldset>
<button class="btn" type="submit">Install</button>
</form>
HTML);
}

// --- POST: run the install ---

$required = ['db_host', 'db_database', 'db_username', 'app_url', 'admin_name', 'admin_email', 'admin_password'];
foreach ($required as $field) {
    if (trim($_POST[$field] ?? '') === '') {
        render('Missing field', '<p>"'.h($field).'" is required. Go back and fill in every field marked required.</p>', 'error');
    }
}
if (strlen($_POST['admin_password']) < 12) {
    render('Password too short', '<p>Your Super Admin password needs to be at least 12 characters.</p>', 'error');
}
if (! filter_var($_POST['admin_email'], FILTER_VALIDATE_EMAIL)) {
    render('Invalid email', '<p>That doesn\'t look like a valid email address.</p>', 'error');
}

function setEnvValue(string $env, string $key, string $value): string
{
    $escaped = preg_match('/\s|#/', $value) ? '"'.addslashes($value).'"' : $value;
    $line = "{$key}={$escaped}";

    return preg_match("/^{$key}=.*/m", $env)
        ? preg_replace("/^{$key}=.*/m", $line, $env)
        : rtrim($env)."\n{$line}\n";
}

$env = file_exists($envPath) ? file_get_contents($envPath) : file_get_contents(__DIR__.'/../.env.example');

$fields = [
    'APP_ENV' => 'production',
    'APP_DEBUG' => 'false',
    'APP_URL' => $_POST['app_url'],
    'DB_CONNECTION' => 'mysql',
    'DB_HOST' => $_POST['db_host'],
    'DB_PORT' => $_POST['db_port'] ?: '3306',
    'DB_DATABASE' => $_POST['db_database'],
    'DB_USERNAME' => $_POST['db_username'],
    'DB_PASSWORD' => $_POST['db_password'],
];
if (trim($_POST['mail_host'] ?? '') !== '') {
    $fields += [
        'MAIL_MAILER' => 'smtp',
        'MAIL_HOST' => $_POST['mail_host'],
        'MAIL_USERNAME' => $_POST['mail_username'] ?? '',
        'MAIL_PASSWORD' => $_POST['mail_password'] ?? '',
        'MAIL_FROM_ADDRESS' => $_POST['mail_from_address'] ?? '',
    ];
}
if (trim($_POST['security_alert_email'] ?? '') !== '') {
    $fields['SECURITY_ALERT_EMAIL'] = $_POST['security_alert_email'];
}

foreach ($fields as $key => $value) {
    $env = setEnvValue($env, $key, $value);
}

if (! preg_match('/^APP_KEY=base64:.+/m', $env)) {
    $env = setEnvValue($env, 'APP_KEY', 'base64:'.base64_encode(random_bytes(32)));
}

if (file_put_contents($envPath, $env) === false) {
    render('Could not write .env', '<p>The web server can\'t write to <code>.env</code>. In File Manager, right-click '
        .'the app\'s root folder → Permissions, and make sure it\'s writable, then try again.</p>', 'error');
}
@chmod($envPath, 0600);

// Boot the app fresh so it picks up the .env we just wrote.
$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    DB::connection('mysql')->getPdo();
} catch (\Throwable $e) {
    render('Could not connect to the database', '<p>'.h($e->getMessage()).'</p>'
        .'<p class="muted">Double check the database details, and that you created the database and user '
        .'(with all privileges on that database) in cPanel\'s MySQL® Databases tool first.</p>', 'error');
}

Artisan::call('migrate', ['--force' => true]);
foreach (['RolesAndPermissionsSeeder', 'OutletAndJobRoleSeeder', 'RetentionPolicySeeder', 'DocumentTemplateSeeder'] as $seeder) {
    Artisan::call('db:seed', ['--class' => "Database\\Seeders\\{$seeder}", '--force' => true]);
}

$admin = User::create([
    'name' => $_POST['admin_name'],
    'email' => $_POST['admin_email'],
    'password' => Hash::make($_POST['admin_password']),
]);
$admin->assignRole('super_admin');

file_put_contents($lockPath, 'Installed '.date('c')." for {$_POST['admin_email']}\n");

render('You\'re live', '<p>Traverse HR is set up. Log in and set up your two-factor authentication '
    .'(mandatory for Super Admin) the moment you\'re asked — save the recovery codes shown, they\'re shown once.</p>'
    .'<p><strong>Now delete this file</strong> (<code>public/install.php</code>) via File Manager — it\'s locked '
    .'against re-running, but there\'s no reason to leave it reachable.</p>'
    .'<p><a class="btn" style="text-decoration:none;text-align:center;display:block;" href="'
    .h($_POST['app_url']).'/login">Go to login</a></p>', 'ok');
