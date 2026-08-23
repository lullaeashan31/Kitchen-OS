# DEPLOY.md — Taking Traverse HR live on cPanel

Status of what you're deploying: **M1 (auth, employees, job roles, outlets,
audit log) + the document/locker slice of M2** (document types, the
in-person "classic session" signing flow, and the employee locker QR/link).
Payroll, recruitment, and offer letters are not built yet — this is a real,
working deployment of the onboarding-paperwork part of the system, not a
placeholder.

This guide assumes a standard cPanel account (the kind Kitchen-OS is
already hosted on). It covers **two paths** — pick whichever matches what
you actually have:

- **Path A — Terminal/SSH available.** Faster, fewer moving parts. Look
  for a "Terminal" icon in cPanel (under "Advanced"), or ask your host if
  SSH is enabled for your account.
- **Path B — No terminal, File Manager only.** More manual, but doesn't
  need SSH. Covered in §6 below.

If you're not sure which you have: log into cPanel and look for a
**Terminal** icon. If it's there, use Path A.

---

## 0. Before you start — decide the URL

Pick a subdomain for this app, e.g. `hr.traverseinc.in` (matching however
Kitchen-OS's subdomain is named — if that's `kitchen.traverseinc.in`, use
the same pattern). You'll create this in cPanel in step 2.

**Critical rule, repeated from the brief:** the subdomain's document root
must point at this app's `public/` folder **only**. The rest of the app
(`app/`, `.env`, `storage/`, the database credentials) must sit *above*
that, never reachable over HTTP. Step 2 below sets this up correctly —
don't skip it or point the subdomain at the project root by mistake.

---

## 1. Create the MySQL database

In cPanel:

1. Open **MySQL® Databases**.
2. Under "Create New Database", enter a name — e.g. `traverse_hr`. cPanel
   will usually prefix it with your account username, e.g.
   `traverseinc_traverse_hr`. Click **Create Database**.
3. Under "MySQL Users → Add New User", create a user — e.g.
   `traverse_hr_app` (again, cPanel will prefix it). Set a strong,
   randomly generated password and **save it somewhere safe** (a password
   manager, not a text file on the server) — you'll need it in step 4.
4. Under "Add User To Database", select the user and database you just
   created, click **Add**, and on the next screen tick **ALL PRIVILEGES**,
   then **Make Changes**.

Write down the three values you now have — full database name, full
username, password — you'll paste them into `.env` shortly.

---

## 2. Create the subdomain, pointing at `public/`

In cPanel, open **Subdomains** (or **Domains**, depending on your cPanel
version):

1. Create the subdomain you picked in step 0 (e.g. `hr`).
2. For the document root, cPanel will suggest something like
   `public_html/hr`. **Change it** to point inside where you're about to
   put this app's code, with `/public` appended — e.g. if you'll upload
   the app to `/home/traverseinc/traverse-hr`, set the document root to
   `/home/traverseinc/traverse-hr/public`.

If your cPanel doesn't let you set an arbitrary document root at creation
time, create the subdomain first, then edit it afterward to change the
document root — every cPanel version supports editing it later.

---

## 3. Get the code onto the server

### Path A (Terminal/SSH)

```bash
# SSH in, or open cPanel's Terminal
cd ~
git clone -b claude/traverse-hr-system-ountsf https://github.com/lullaeashan31/Kitchen-OS.git traverse-hr-checkout
mv traverse-hr-checkout/traverse-hr ~/traverse-hr
rm -rf traverse-hr-checkout
cd ~/traverse-hr
```

(This clones the whole Kitchen-OS repo temporarily just to pull out the
`traverse-hr` subfolder, then discards the rest — the two apps aren't
meant to overlap on disk. Once you're happy with this, we can also split
`traverse-hr` into its own repository so this step becomes a plain
`git clone` — see DECISIONS.md.)

### Path B (File Manager, no terminal)

Composer and `artisan` need a command line to run — File Manager alone
can't execute them. Do this instead:

1. Tell me when you're at this step and I'll build a deployment ZIP
   locally (app code **with** the `vendor/` dependencies already
   installed, so nothing needs to run composer on the server) and hand it
   to you as a file to download.
2. In cPanel File Manager, upload that ZIP to `/home/<youraccount>/`,
   right-click → **Extract**.
3. Rename the extracted folder to `traverse-hr`.

Either way, artisan commands (migrations, key generation) still need to
run somehow. Check cPanel for a **"Setup Python App"** or **"Setup Node.js
App"**-style tool — many cPanel installs have an equivalent for PHP
("MultiPHP" / "Application Manager") that includes a *"Enter to the
virtual environment"* command giving you a one-off shell. If nothing like
that exists on your plan, tell your host you need either SSH access or a
way to run one-off PHP CLI commands (`php artisan migrate`) — this is a
completely standard hosting request, not unusual.

---

## 4. Configure `.env`

```bash
cd ~/traverse-hr
cp .env.example .env
```

Edit `.env` (via `nano .env` over SSH, or File Manager's built-in editor)
and set:

```env
APP_NAME="Traverse HR"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://hr.traverseinc.in

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=traverseinc_traverse_hr
DB_USERNAME=traverseinc_traverse_hr_app
DB_PASSWORD=<the password from step 1>

MAIL_MAILER=smtp
MAIL_HOST=<your cPanel mail host, e.g. mail.traverseinc.in>
MAIL_PORT=587
MAIL_USERNAME=<a mailbox you create in cPanel, e.g. hr@traverseinc.in>
MAIL_PASSWORD=<its password>
MAIL_FROM_ADDRESS=hr@traverseinc.in
MAIL_FROM_NAME="${APP_NAME}"

SECURITY_ALERT_EMAIL=<your own email — repeated-failed-login alerts land here>
```

Then, over SSH/Terminal:

```bash
chmod 600 .env
composer install --no-dev --optimize-autoloader   # Path A only — Path B already has vendor/
php artisan key:generate
```

`chmod 600 .env` matches the brief's requirement — only the app can read
it, nothing else on the server can.

---

## 5. Run migrations, seed the essentials, create your account

```bash
php artisan migrate --force
php artisan db:seed --class=RolesAndPermissionsSeeder --force
php artisan db:seed --class=OutletAndJobRoleSeeder --force
php artisan db:seed --class=RetentionPolicySeeder --force
php artisan db:seed --class=DocumentTemplateSeeder --force
php artisan traverse:create-admin
```

That last command asks for your name, email, and a password interactively
— it creates your named Super Admin account. **Do not** run the plain
`db:seed` (no `--class`) in production — nothing seeds a default password
account, by design (§6: no shared accounts).

Log in once at `https://hr.traverseinc.in/login`, and set up your 2FA
(mandatory for Super Admin) with Google Authenticator or Authy the moment
you're prompted. **Save the recovery codes shown** — they're shown once.

---

## 6. Storage permissions and the web root check

```bash
chmod -R 775 storage bootstrap/cache
```

Then verify from a browser (not the server) that these both come back
**404 Not Found** — if either doesn't, STOP and fix your subdomain's
document root before going further, per §8's hard rule:

```
https://hr.traverseinc.in/.env
https://hr.traverseinc.in/storage/app/private/document-template-versions/
```

If `.env` is downloadable, your subdomain's document root is pointing at
the wrong folder (the project root instead of `public/`) — go back to
step 2.

---

## 7. Cron — one job drives everything

In cPanel → **Cron Jobs**, add one job running every minute:

```
* * * * * cd /home/<youraccount>/traverse-hr && php artisan schedule:run >> /dev/null 2>&1
```

Nothing is scheduled yet in this build (no backups, no link-expiry job —
those come with M2's continuation and §7), but this cron entry costs
nothing to have running now and everything added later picks it up
automatically.

---

## 8. Smoke test before telling anyone the link works

1. Log in, confirm 2FA works.
2. Admin → Outlets: confirm "Alinea" is there (or add your real outlets —
   see the open item in DECISIONS.md about the full outlet/role list).
3. Admin → Employees → add one real test employee (delete it after).
4. Employees → Documents → open one document, type a name, confirm it
   records "Accepted."
5. Employees → Locker → confirm the QR renders and the link opens on your
   own phone (over mobile data, not wifi, to prove it's really public).
6. Delete the test employee's data if you don't want it in the real
   database (Employee delete is soft-delete, so it won't show in normal
   lists, but say so if you want it hard-removed).

If all six pass, you're live for onboarding documents.

---

## 9. What "live" does NOT cover yet

No payroll, no recruitment, no offer letters — those modules aren't
built. Nothing in this deployment lets anyone run payroll or generate an
offer. See DECISIONS.md's "before go-live" checklist for what's needed
before those are built.

No automated backups yet either (§7 is not implemented) — until it is,
treat the database as **not durably backed up**. If that matters before
the backup system is built, say so and I'll prioritize a manual/cron
`mysqldump`-to-somewhere-off-host as an interim measure — it's a small
job on its own, just not done automatically today.

## 10. If something breaks

- **500 error, blank page**: `APP_DEBUG` should be `false` in production
  (errors go to `storage/logs/laravel.log`, not the browser) — check that
  log file first: `tail -50 storage/logs/laravel.log`.
- **"could not find driver" on migrate**: your PHP build is missing the
  `pdo_mysql` extension — ask your host to enable it (cPanel's "MultiPHP
  Extensions" (or "Select PHP Version") tool usually has a checkbox).
- **Composer fails with a version error**: check your cPanel's PHP
  version (Software → "MultiPHP Manager" or "Select PHP Version") is
  8.2 or higher; this app needs it.
- **2FA codes never verify**: check your server's clock is correct
  (`date` over SSH) — TOTP codes are time-based and drift if the server
  clock is wrong. Ask your host to fix NTP if so.
