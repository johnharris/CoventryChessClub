# Deploying the Coventry Chess Club Website

**Author:** Manus AI
**Date:** 4 August 2026

This guide takes the repository from a local copy to a live site. It is written to be host-agnostic, because the club's hosting has not yet been chosen, and it covers the two realistic options for a club of this size. Read Part 1 to choose a host, then follow either Part 3 (shared hosting) or Part 4 (virtual server).

---

## Part 1 — What the site needs, and choosing a host

The site is a standard Laravel application, so its requirements are modest but specific:

| Requirement | Detail |
|---|---|
| PHP | 8.3 or newer, with `mbstring`, `xml`, `curl`, `zip`, `bcmath`, `openssl`, `pdo` and either `pdo_sqlite` or `pdo_mysql` |
| Web server | Apache with `mod_rewrite`, or Nginx |
| Document root | Must point at the `public/` directory, **not** the project root |
| Database | SQLite needs nothing at all; MySQL 8 or MariaDB 10.4+ also supported |
| Node.js | **Not required on the server.** Assets are compiled before deployment and committed |
| Composer | Useful on the server, but dependencies can be uploaded instead |
| HTTPS | Strongly recommended; free via Let's Encrypt on all the hosts below |

The absence of a Node.js requirement is deliberate and widens the choice of host considerably, because cheap shared hosting rarely provides it.

### Realistic options

| Option | Cost (approx.) | Effort | Suits the club if |
|---|---|---|---|
| **UK shared hosting with cPanel** — e.g. Krystal, TSOhost, Names.co | £3–6 / month | Lowest. Upload files, point the domain, done. No server administration ever. | Nobody wants to be a system administrator. **This is the recommended default.** |
| **Small VPS** — e.g. Hetzner CX22, DigitalOcean, Linode | £4–6 / month | Moderate. You install and update PHP, Nginx and certificates yourself. | Somebody at the club enjoys this, or you want room for other projects. |
| **Managed Laravel platform** — e.g. Laravel Cloud, Ploi, Cloudways | £10–20 / month | Low, but the most expensive. Deploys straight from GitHub on every push. | The club values automatic deployment more than the saving. |

For a chess club website updated a few times a month, **shared cPanel hosting is the sensible choice**. It is the cheapest, needs no maintenance, includes email addresses at the club's domain, and provides free HTTPS. When you have chosen, tell me which and I will tailor the remaining steps precisely.

### A note on the domain

The club currently uses `coventrychessclub.blogspot.com`. A domain such as `coventrychessclub.org.uk` costs roughly £8–12 per year and is worth having; most hosts will register it as part of the signup. The existing Blogspot site can then link to the new address so that nothing is lost.

---

## Part 2 — Preparing a release (do this before either route)

Run these steps on a machine with PHP, Composer and Node — not on the server.

```bash
git clone https://github.com/johnharris/CoventryChessClub.git
cd CoventryChessClub

# 1. Install PHP dependencies, optimised for production
composer install --no-dev --optimise-autoloader

# 2. Compile the CSS and JavaScript
pnpm install
pnpm run build
```

The compiled output lands in `public/build/` and is committed to the repository, so this second step can be skipped entirely if you have not changed any CSS or JavaScript.

Then create the production `.env`. Copy `.env.example` and set at minimum:

```ini
APP_NAME="Coventry Chess Club"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://coventrychessclub.org.uk

# Generate with: php artisan key:generate --show
APP_KEY=base64:REPLACE_WITH_YOUR_OWN_KEY

# --- Database: SQLite (simplest) ---
DB_CONNECTION=sqlite
# DB_DATABASE defaults to database/database.sqlite

# --- Database: MySQL (if your host provides it) ---
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=coventrychess
# DB_USERNAME=coventrychess
# DB_PASSWORD=your-database-password

# The first administrator, created by the seeder. Change the password.
ADMIN_NAME="Club Administrator"
ADMIN_EMAIL=secretary@coventrychessclub.org.uk
ADMIN_PASSWORD=choose-something-long-and-unique

# Where contact form enquiries are emailed. Leave blank to store them on the
# site only; they are always stored regardless.
CLUB_ENQUIRY_EMAIL=secretary@coventrychessclub.org.uk

# Outbound email, once your host has given you SMTP details
MAIL_MAILER=smtp
MAIL_HOST=mail.yourhost.com
MAIL_PORT=587
MAIL_USERNAME=secretary@coventrychessclub.org.uk
MAIL_PASSWORD=your-mailbox-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=secretary@coventrychessclub.org.uk
MAIL_FROM_NAME="Coventry Chess Club"
```

> **`APP_DEBUG=false` is not optional.** With debugging enabled, an error page reveals file paths, configuration values and database details to any visitor.

> **`.env` must never be committed to Git.** The repository's `.gitignore` already excludes it.

---

## Part 3 — Shared hosting with cPanel (recommended)

### Step 1 — Upload

Upload the whole project *above* the web root. A typical cPanel account looks like this:

```
/home/username/
├── coventrychess/        <- the entire project goes here
└── public_html/          <- the web root
```

Upload as a single ZIP through cPanel's File Manager and extract it there; this is far faster than uploading thousands of individual files.

### Step 2 — Point the web root at `public/`

There are two ways, in order of preference.

**Either** set the document root directly. In cPanel, under *Domains*, edit the domain and set its document root to `coventrychess/public`. This is the cleanest arrangement, and the rest of the project stays unreachable from the web.

**Or**, if your host does not allow that, make `public_html` the project's public directory:

```bash
rm -rf ~/public_html
ln -s ~/coventrychess/public ~/public_html
```

If symbolic links are also disallowed, copy the contents of `public/` into `public_html/` and edit `public_html/index.php` so its two `require` paths point at `../coventrychess/`.

### Step 3 — Select the PHP version

In cPanel, open *MultiPHP Manager*, select the domain and choose **PHP 8.3** or newer. Then in *MultiPHP INI Editor* or *Select PHP Extensions*, confirm that `mbstring`, `xml`, `curl`, `zip`, `bcmath`, `openssl` and your chosen PDO driver are enabled.

### Step 4 — Set permissions and prepare the database

Using cPanel's Terminal, or SSH if available:

```bash
cd ~/coventrychess
chmod -R 775 storage bootstrap/cache

# SQLite only:
touch database/database.sqlite
chmod 664 database/database.sqlite

php artisan migrate --force
php artisan db:seed --force        # creates the first administrator and content
php artisan storage:link
```

If your host provides no terminal at all, run the migrations once from your own machine against the host's MySQL database (using its remote access credentials), or ask the host's support to run the two commands — this is a routine request.

### Step 5 — Cache the configuration

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

These three commands are the single largest performance improvement available and should be re-run after any change to `.env` or the routes.

### Step 6 — Enable HTTPS

In cPanel, open *SSL/TLS Status* and run **AutoSSL**, which issues a free Let's Encrypt certificate. Then add a redirect so visitors always arrive on the secure address; cPanel's *Domains* screen has a "Force HTTPS Redirect" switch.

### Step 7 — Sign in and change the password

Visit `https://your-domain/login`, sign in with the `ADMIN_EMAIL` and `ADMIN_PASSWORD` from `.env`, and change the password immediately under *My profile*. Then whitelist the club's members from the *Members* screen.

---

## Part 4 — A virtual server (Ubuntu 24.04 with Nginx)

Choose this only if somebody is willing to apply security updates.

### Step 1 — Install the software

```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y nginx php8.3-fpm php8.3-mbstring php8.3-xml php8.3-curl \
                    php8.3-zip php8.3-sqlite3 php8.3-mysql php8.3-bcmath \
                    php8.3-gd git unzip

curl -sS https://getcomposer.org/installer | sudo php -- \
     --install-dir=/usr/local/bin --filename=composer
```

### Step 2 — Deploy the code

```bash
sudo mkdir -p /var/www/coventrychess
sudo chown -R $USER:www-data /var/www/coventrychess
git clone https://github.com/johnharris/CoventryChessClub.git /var/www/coventrychess
cd /var/www/coventrychess

composer install --no-dev --optimise-autoloader
cp .env.example .env
php artisan key:generate
nano .env                      # fill in as per Part 2

touch database/database.sqlite  # SQLite only
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link

sudo chown -R www-data:www-data storage bootstrap/cache database
sudo chmod -R 775 storage bootstrap/cache
```

### Step 3 — Configure Nginx

Create `/etc/nginx/sites-available/coventrychess`:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name coventrychessclub.org.uk www.coventrychessclub.org.uk;
    root /var/www/coventrychess/public;

    index index.php;
    charset utf-8;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Cache the compiled assets hard: their filenames contain a content hash.
    location ^~ /build/assets/ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    location ~ /\.(?!well-known).* { deny all; }

    client_max_body_size 8M;
}
```

Enable it and reload:

```bash
sudo ln -s /etc/nginx/sites-available/coventrychess /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
```

### Step 4 — Add HTTPS

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d coventrychessclub.org.uk -d www.coventrychessclub.org.uk
```

Certbot edits the Nginx configuration and installs a renewal timer automatically.

### Step 5 — Cache the configuration

```bash
cd /var/www/coventrychess
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

---

## Part 5 — Updating the site later

Any future change follows the same short sequence:

```bash
cd /var/www/coventrychess          # or ~/coventrychess on shared hosting
php artisan down                   # optional maintenance page

git pull
composer install --no-dev --optimise-autoloader
php artisan migrate --force

php artisan config:cache
php artisan route:cache
php artisan view:cache

php artisan up
```

If the change touched CSS or JavaScript, run `pnpm run build` locally and commit `public/build/` before pulling.

---

## Part 6 — Backups

The whole site is two things: the database and the `.env` file. On SQLite, a complete backup is a copy of one file.

```bash
# SQLite
cp database/database.sqlite ~/backups/chess-$(date +%F).sqlite

# MySQL
mysqldump -u coventrychess -p coventrychess > ~/backups/chess-$(date +%F).sql
```

A weekly cron entry is enough for a club site:

```cron
0 3 * * 1 cp /var/www/coventrychess/database/database.sqlite /home/backup/chess-$(date +\%F).sqlite
```

Keep a copy of `.env` somewhere safe and private — without `APP_KEY`, encrypted values cannot be recovered.

---

## Part 7 — Troubleshooting

| Symptom | Cause and remedy |
|---|---|
| Blank white page | An error with `APP_DEBUG=false`. Read `storage/logs/laravel.log` for the real cause. |
| "500 Server Error" immediately after deploying | Almost always permissions. `chmod -R 775 storage bootstrap/cache` and ensure the web server user owns them. |
| Pages load but are unstyled | The web root is not pointing at `public/`, or `public/build/` was not uploaded. |
| Boards do not appear | The JavaScript bundle is missing; confirm `public/build/` exists and `APP_URL` matches the address in the browser's address bar. |
| "No application encryption key" | `APP_KEY` is empty. Run `php artisan key:generate`. |
| Routes 404 except the home page | Apache is missing `mod_rewrite`, or Nginx lacks the `try_files` line. |
| Changes to `.env` have no effect | Configuration is cached. Run `php artisan config:cache` again. |
| Contact form works but no email arrives | The `MAIL_*` values are wrong. Enquiries are still stored — check the Enquiries screen. |
| Cannot sign in as administrator | The seeder did not run, or ran before `.env` was set. Re-run `php artisan db:seed --force`. |

---

## Part 8 — Security checklist before going live

- [ ] `APP_DEBUG=false` and `APP_ENV=production`
- [ ] `APP_KEY` generated and unique to this installation
- [ ] The administrator password changed from the value in `.env`
- [ ] HTTPS working, with HTTP redirecting to it
- [ ] `.env` not readable from the web (it is outside the web root if you followed Part 3 or 4)
- [ ] `database/database.sqlite` not readable from the web, for the same reason
- [ ] Backups running and, more importantly, tested by restoring one
- [ ] PHP 8.3 or newer, and set to update with the host's security patches

---

## References

[1] [Laravel Deployment Documentation](https://laravel.com/docs/deployment) — official guidance on server requirements and optimisation.
[2] [Let's Encrypt](https://letsencrypt.org/) — free automated certificates.
[3] [Certbot](https://certbot.eff.org/) — the client used to obtain and renew them on a VPS.
