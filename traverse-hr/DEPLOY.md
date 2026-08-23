# DEPLOY.md — Taking Traverse HR live on cPanel

Status of what you're deploying: **M1 (auth, employees, job roles, outlets,
audit log) + the document/locker slice of M2** (document types, the
in-person "classic session" signing flow, and the employee locker QR/link).
Payroll, recruitment, and offer letters are not built yet — this is a real,
working deployment of the onboarding-paperwork part of the system, not a
placeholder.

This guide assumes a standard cPanel account (the kind Kitchen-OS is
already hosted on). It covers **three paths** — pick whichever matches
what you actually have or want:

- **Path C — Upload a ZIP, visit one page. No terminal, no commands.**
  The closest thing to "copy-paste a file like `operations.php`" this app
  has. Everything (code + dependencies) is pre-built into one ZIP; you
  upload it via File Manager, extract it, point the subdomain at it, and
  fill in one form in your browser. This is the recommended path if you
  don't have or don't want to use a terminal. **Covered in §3 Path C.**
- **Path A — Terminal/SSH available.** For if you'd rather run the
  commands yourself, or want git-based updates going forward. Look for a
  "Terminal" icon in cPanel (under "Advanced").
- **Path B — File Manager only, doing the steps by hand.** The manual
  version of what Path C automates — useful if you want to understand or
  customize each step. Most people should use Path C instead.

**If you don't know which to pick: use Path C.** It needs nothing beyond
cPanel's File Manager and a web browser.

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

### Path C (upload a ZIP, no terminal needed at all) — tested end to end

This is the file I built and tested for you: `traverse-hr.zip` — the app
code **with** all dependencies (`vendor/`) already installed, plus a
one-time setup page (`install.php`) that does everything `artisan` would
normally do — write `.env`, run migrations, seed the base data, create
your Super Admin account — from your browser, no command line at all.
I ran this exact ZIP through a full install against a real MySQL database
before handing it to you, so it's not a guess.

1. In cPanel **File Manager**, navigate to your account's home directory
   (one level above `public_html`).
2. **Upload** `traverse-hr.zip` there, then right-click it → **Extract**.
   This creates a `traverse-hr/` folder.
3. Do step 2 above (create the subdomain, document root
   `.../traverse-hr/public`) if you haven't already.
4. In File Manager, right-click the `traverse-hr` folder → **Permissions**,
   and make sure `storage` and `bootstrap/cache` (and everything inside
   them) are writable — 775 is a safe setting for directories. If File
   Manager offers "Apply to subdirectories," use it, scoped to just those
   two folders.
5. Visit `https://hr.traverseinc.in/install.php?token=<the token I gave
   you separately>` in your browser.
6. Fill in the form: the database name/username/password from step 1, the
   site URL, and your own name/email/password for the Super Admin
   account. Submit.
7. You'll land on a "You're live" page. **Delete `install.php`** via File
   Manager right after (it locks itself against re-running, but there's
   no reason to leave it reachable).
8. Log in at `https://hr.traverseinc.in/login` and set up 2FA immediately
   — save the recovery codes shown, they're shown once.

That's the entire deployment. Skip straight to **§8 — smoke test** below;
sections 4–7 describe what `install.php` just did for you automatically,
useful only if something needs troubleshooting or you'd rather do it by
hand.

### Path B (File Manager, doing it by hand)

If you want to understand or customize what Path C automated: upload and
extract the same ZIP as Path C, but instead of visiting `install.php`,
follow steps 4–6 below manually, editing `.env` directly through File
Manager's text editor. You'll need *some* way to run `php artisan
migrate` once — check cPanel for a **"Setup Python App"** or **"Setup
Node.js App"**-style tool; many plans have a PHP equivalent
("MultiPHP"/"Application Manager") with an *"Enter to the virtual
environment"* command giving a one-off shell. If nothing like that
exists, Path C's `install.php` already does this step without needing
one — there's rarely a reason to do this path instead.

---

## 4. Configure `.env`

*(Path C already did this for you — skip to §8. This section is for Path
A/B.)*

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

*(Path C already did this for you — skip to §6, which is still worth
doing on every path. This section is for Path A/B.)*

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

*(Path C: permissions were covered in Path C step 4. Do the browser check
below regardless of path — it's the single most important thing to
verify before calling this live.)*

```bash
chmod -R 775 storage bootstrap/cache   # Path A/B only
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
