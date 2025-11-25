README: Deploying the PHP backend on InfinityFree

Overview
--------
This guide explains how to deploy the PHP backend (`submit_form.php`) with a MySQL database and send emails with an infographic on InfinityFree.

Important notes about InfinityFree
- InfinityFree allows PHP and MySQL (MariaDB) but may block outbound SMTP ports. Because of that, the script supports SendGrid API (HTTP) which works fine from InfinityFree.
- Do NOT store secrets (API keys, DB passwords) in a public repo. Use `config.php` (not committed) as explained below.

Files you should have locally
- `formulario.html` (already created)
- `submit_form.php` (already created)
- `config.php.example` -> copy to `config.php` and fill
- `infografia.pdf` (your infographic)
- `create_table.sql` (SQL to create table)

Steps
-----
1) Create an account on InfinityFree
   - Go to https://infinityfree.net/ and sign up.

2) Create a new hosting account and domain/subdomain
   - Create an account and note the FTP credentials and the control panel (VistaPanel) URL.

3) Create a MySQL database
   - Open your account's control panel (MySQL Databases)
   - Create a database. Note the DB name, username and password and the host (often `sqlXXX.infinityfree.com`).

4) Upload files
   - In the File Manager (or via FTP), upload the site files to the `htdocs` folder:
     - `formulario.html`
     - `submit_form.php`
     - `config.php` (create from `config.php.example` but DO NOT commit it to GitHub)
     - `infografia.pdf`
     - (optional) `vendor/` folder if you install PHPMailer locally

5) Create the table
   - Open phpMyAdmin from the control panel, select the database and run the SQL in `create_table.sql`.

6) Configure `config.php`
   - Copy `config.php.example` → `config.php`
   - Fill `db.host`, `db.name`, `db.user`, `db.pass` with InfinityFree values.
   - Set `site_url` to your site URL, e.g. `https://yourusername.epizy.com`.
   - If you have a SendGrid account (recommended), set `sendgrid_api_key` to your API key so the script attaches the infographic reliably.
   - If you prefer SMTP and your provider supports it from InfinityFree, fill `smtp` settings.

7) (Optional) Install PHPMailer
   - If you want PHPMailer (SMTP) instead of SendGrid API, download PHPMailer from GitHub (https://github.com/PHPMailer/PHPMailer) or run Composer locally and upload the `vendor/` folder to the server. Place `vendor/` next to `submit_form.php`.

8) Set form `action`
   - If you uploaded `formulario.html` and `submit_form.php` to the same folder, the existing relative `action="submit_form.php"` will work.
   - If they are on different hosts, change the `action` to the absolute URL of `submit_form.php`.

9) Test
   - Open `formulario.html` in your site, complete fields and submit.
   - Check phpMyAdmin for a new row in `contacts` and check the recipient email inbox.

Troubleshooting
- If email fails and you used SendGrid: check `sendgrid_api_key` and the server has outbound HTTPS (curl). Check server error logs and the response in the JSON returned by `submit_form.php`.
- If you used PHPMailer and SMTP fails: InfinityFree often blocks external SMTP ports. Use SendGrid API instead.

Security
- Never commit `config.php` to GitHub. Add it to `.gitignore` locally.
- Use HTTPS for your site (InfinityFree offers free SSL or use Cloudflare).

If you want, I can prepare a `.gitignore` and a `config.php` template filled with placeholder values ready for you to edit, or I can prepare a PHPMailer `vendor/` zip you can upload. Which do you prefer?
