# 🚀 Flarum on Docker + Cloudflare Tunnel

A complete deployment guide for Flarum (Forum Software) using Docker with a modern architecture. Web traffic is routed through **Cloudflare Tunnel (`cloudflared`)** already installed on the VPS, so no ports are exposed to the public internet.

> 📖 **[Baca dalam Bahasa Indonesia](README.id.md)**

## 🏗 Architecture

- **PHP-FPM (Custom Image)**: Runs the Flarum core. Includes Composer and auto-installs Flarum on first run.
- **Nginx**: Internal web server, only listens on `127.0.0.1:8080`. Equipped with rate limiting, security headers, and read-only filesystem.
- **MariaDB**: Flarum database. Isolated in an internal network (no internet access). Includes healthcheck.
- **Cloudflare Tunnel**: Uses `cloudflared` already installed on the VPS (not inside Docker). Routes traffic from your domain to `http://localhost:8080`.

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
| **Resource Limits** | CPU & memory capped per container |
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
   - **Service**: `http://localhost:8080`

**Or via config file** (`/etc/cloudflared/config.yml`):
```yaml
- hostname: forum.yourdomain.com
  service: http://localhost:8080
```

Then restart cloudflared:
```bash
sudo systemctl restart cloudflared
```

### 3. Start

```bash
docker compose up -d --build
```

Flarum will **auto-install** on the first run. Monitor the progress:

```bash
docker logs -f flarum_app
```

### 4. Complete Web Installation

Open your domain in a browser. Flarum will show the installation page. Fill in:
- **Database Host**: `db`
- **Database Name**: `flarum` (or as set in `.env`)
- **Database User**: `flarum` (or as set in `.env`)
- **Database Password**: (as set in `MYSQL_PASSWORD` in `.env`)

---

## 🔧 Useful Commands

```bash
# Start all services
docker compose up -d --build

# View all logs
docker compose logs -f

# Enter the Flarum container (to install extensions, etc.)
docker exec -it flarum_app sh

# Install a Flarum extension (example)
docker exec -it flarum_app su-exec www-data composer require fof/user-bio

# Restart services
docker compose restart

# Stop all services
docker compose down

# Stop + delete all data (CAUTION!)
docker compose down -v
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
| **Database backup** | `docker exec flarum_db mysqldump -u root -p flarum > backup.sql` |
