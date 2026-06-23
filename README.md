# 🚀 Flarum on Docker + Cloudflare Tunnel

A complete deployment guide for Flarum (Forum Software) using Docker with a modern architecture. Web traffic is routed through **Cloudflare Tunnel (`cloudflared`)** already installed on the VPS, so no ports are exposed to the public internet.

> 📖 **[Baca dalam Bahasa Indonesia](README.id.md)**

## 🏗 Architecture

- **PHP-FPM (Custom Image)**: Runs the Flarum core. Includes Composer and auto-installs Flarum on first run.
- **Nginx**: Internal web server, only listens on `127.0.0.1:8281`. Equipped with rate limiting, security headers, and read-only filesystem.
- **MariaDB**: Flarum database. Isolated in an internal network (no internet access). Includes healthcheck.
- **Cloudflare Tunnel**: Uses `cloudflared` already installed on the VPS (not inside Docker). Routes traffic from your domain to `http://localhost:8281`.

---

## 🔒 Security Features

| Feature | Detail |
|---------|--------|
| **Zero Open Ports** | Nginx only listens on `127.0.0.1:8080` — inaccessible from outside the VPS |
| **Rate Limiting** | 10 req/s per IP, burst 20 — prevents brute force & DDoS |
| **Security Headers** | `X-Frame-Options`, `X-Content-Type-Options`, `X-XSS-Protection`, `Referrer-Policy` |
| **Upload Limit** | Max 5MB — prevents disk exhaustion |
| **Server Tokens Off** | Nginx version hidden from attackers |
| **Hidden Files Blocked** | Access to `.env`, `.git`, etc. is denied |
| **No New Privileges** | All containers cannot escalate privileges |
| **Read-only Nginx** | Nginx filesystem is read-only (tmpfs for cache) |
| **Isolated Database** | MariaDB on internal network, no internet access |
| **Secrets in .env** | Passwords are not hardcoded in `compose.yml` |

---

## 📂 Directory Structure

```text
flarum-docker/
├── compose.yml        # Docker service definitions
├── Dockerfile         # Custom PHP-FPM image + auto-install Flarum
├── entrypoint.sh      # Auto-install script on first run
├── nginx.conf         # Nginx configuration (+ security hardening)
├── .env.example       # Environment variable template
├── .env               # Environment variables (DO NOT commit to git!)
├── .gitignore         # Excludes .env and data from git
├── README.md          # This file (English)
└── README.id.md       # Indonesian version
```

---

## ⚡ Deployment

### 1. Clone & Setup Environment

```bash
git clone <your-repo-url>
cd flarum-docker

# Copy the environment template
cp .env.example .env
```

Generate strong passwords and fill in `.env`:

```bash
# Generate a strong random password
openssl rand -base64 32
```

Run it twice — one for `MYSQL_ROOT_PASSWORD` and one for `MYSQL_PASSWORD`. Paste the results into `.env`.

### 2. Configure Cloudflare Tunnel

Since `cloudflared` is already installed on the VPS, just add a new **Public Hostname** to your existing tunnel:

**Via Cloudflare Zero Trust Dashboard:**
1. Open your active tunnel
2. Add a new Public Hostname:
   - **Domain**: your forum domain/subdomain
   - **Service**: `http://localhost:8281`

**Or via config file** (`/etc/cloudflared/config.yml`):
```yaml
- hostname: forum.yourdomain.com
  service: http://localhost:8281
```

Then restart cloudflared:
```bash
sudo systemctl restart cloudflared
```

### 3. Start

```bash
docker compose build
docker compose up -d
docker logs -f flarum_app
```

Flarum will **auto-install** on the first run.

> 💡 **Tip**: If you've modified the `Dockerfile`, use `docker compose build --no-cache` to rebuild from scratch.

### 4. Complete Web Installation

Open your domain in a browser. Flarum will show the installation page. Fill in the form:

| Field | Value | Notes |
|-------|-------|-------|
| **Forum Title** | *(your choice)* | Name of your forum |
| **MySQL Host** | `db` | ⚠️ **Do NOT use `localhost`** — use `db` (the Docker service name) |
| **MySQL Database** | `flarum` | Must match `MYSQL_DATABASE` in `.env` |
| **MySQL Username** | `flarum` | Must match `MYSQL_USER` in `.env` |
| **MySQL Password** | *(see `.env`)* | Must match `MYSQL_PASSWORD` in `.env` (**not** `MYSQL_ROOT_PASSWORD`) |
| **Table Prefix** | *(leave empty)* | Optional, leave blank unless you have a reason |
| **Admin Username** | *(your choice)* | Your forum admin login |
| **Admin Email** | *(your email)* | Used for notifications and password reset |
| **Admin Password** | *(your choice)* | Use a strong password |

> [!CAUTION]
> The default MySQL Host is `localhost`, but this **will not work** in Docker. You must change it to `db`, which is the container name of the MariaDB service defined in `compose.yml`.

> [!IMPORTANT]
> **`MYSQL_ROOT_PASSWORD` vs `MYSQL_PASSWORD`** — Your `.env` contains two passwords:
> - `MYSQL_ROOT_PASSWORD` → Superadmin password for database maintenance/backup only. **Do not use this for Flarum.**
> - `MYSQL_PASSWORD` → The password for the `flarum` user. **Use this one in the installer.**

---

## 🔧 Useful Commands

```bash
# Build & start all services
sudo docker compose build
sudo docker compose up -d

# Rebuild from scratch (after Dockerfile changes)
sudo docker compose build --no-cache
sudo docker compose up -d

# View all logs
sudo docker compose logs -f

# Enter the Flarum container
sudo docker exec -it flarum_app sh

# Install a Flarum extension (example)
sudo docker exec -it flarum_app su-exec www-data composer require fof/user-bio

# Remove a Flarum extension (example)
sudo docker exec -it flarum_app su-exec www-data composer remove fof/user-bio

# Clear Flarum cache (run after install/remove extensions)
sudo docker exec -it flarum_app su-exec www-data php flarum cache:clear

# Restart services
sudo docker compose restart

# Stop all services
sudo docker compose down

# Stop + delete all data (CAUTION!)
sudo docker compose down -v
```

---

## 🖥️ VPS Hardening Recommendations

In addition to Docker-level security, make sure your VPS is also hardened:

| Item | Command |
|------|---------|
| **Firewall (UFW)** | `sudo ufw allow OpenSSH && sudo ufw enable` |
| **Disable root login** | `PermitRootLogin no` in `/etc/ssh/sshd_config` |
| **SSH Key only** | `PasswordAuthentication no` in `/etc/ssh/sshd_config` |
| **Fail2Ban** | `sudo apt install fail2ban` |
| **Auto security updates** | `sudo apt install unattended-upgrades` |

---

## 💾 Backup & Restore

A complete Flarum backup requires **two parts**: the database and the file data.

### Manual Backup

```bash
# 1. Backup database
docker exec flarum_db mysqldump -u root -p"$(grep MYSQL_ROOT_PASSWORD .env | cut -d= -f2)" flarum > backup_db.sql

# 2. Backup file data (uploads, avatars, config, extensions)
tar -czf backup_files.tar.gz ./flarum-data
```

### Manual Restore

```bash
# 1. Restore database
docker exec -i flarum_db mysql -u root -p"$(grep MYSQL_ROOT_PASSWORD .env | cut -d= -f2)" flarum < backup_db.sql

# 2. Restore file data
tar -xzf backup_files.tar.gz
```

### What's inside `flarum-data/`?

| Content | Path |
|---------|------|
| User avatars | `flarum-data/public/assets/avatars/` |
| Uploaded files | `flarum-data/public/assets/files/` |
| Extensions | `flarum-data/vendor/` |
| Site config | `flarum-data/config.php` |

### Automated Backup (Cron)

Create a backup script (e.g. `~/backup-flarum.sh`):

```bash
#!/bin/bash
BACKUP_DIR="$HOME/backups/flarum"
DATE=$(date +%Y-%m-%d_%H-%M)
FLARUM_DIR="$HOME/services/web/flarum"

mkdir -p "$BACKUP_DIR"

# Backup database
docker exec flarum_db mysqldump -u root -p"$(grep MYSQL_ROOT_PASSWORD $FLARUM_DIR/.env | cut -d= -f2)" flarum > "$BACKUP_DIR/db_$DATE.sql"

# Backup files
tar -czf "$BACKUP_DIR/files_$DATE.tar.gz" -C "$FLARUM_DIR" flarum-data

# Keep only last 7 backups
ls -t "$BACKUP_DIR"/db_*.sql | tail -n +8 | xargs -r rm
ls -t "$BACKUP_DIR"/files_*.tar.gz | tail -n +8 | xargs -r rm

echo "Backup completed: $DATE"
```

Then schedule it to run daily at 3 AM:

```bash
chmod +x ~/backup-flarum.sh
crontab -e
# Add this line:
0 3 * * * ~/backup-flarum.sh >> ~/backups/flarum/backup.log 2>&1
```

