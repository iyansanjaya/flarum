# 🚀 Flarum on Docker + Cloudflare Tunnel

Repositori ini adalah panduan lengkap untuk melakukan _deployment_ Flarum (Software Forum) menggunakan Docker dengan arsitektur modern. Trafik web diakses melalui **Cloudflare Tunnel (`cloudflared`)** yang sudah terinstall di VPS, sehingga tidak ada port yang terekspos ke internet publik.

> 📖 **[Read in English](README.md)**

## 🏗 Arsitektur Sistem

- **PHP-FPM (Custom Image)**: Menjalankan _core_ Flarum. Dilengkapi dengan Composer dan auto-install Flarum saat pertama kali dijalankan.
- **Nginx**: Web server internal, hanya listen di `127.0.0.1:8281`. Dilengkapi rate limiting, security headers, dan read-only filesystem.
- **MariaDB**: Database untuk Flarum. Terisolasi di network internal (tanpa akses internet). Dilengkapi _healthcheck_.
- **Cloudflare Tunnel**: Menggunakan `cloudflared` yang sudah terinstall di VPS (bukan di dalam Docker). Meneruskan trafik dari domain Anda ke `http://localhost:8281`.

---

## 🔒 Fitur Keamanan

| Fitur | Detail |
|-------|--------|
| **Zero Open Ports** | Nginx hanya listen di `127.0.0.1:8080` — tidak bisa diakses dari luar VPS |
| **Rate Limiting** | 10 req/detik per IP, burst 20 — mencegah brute force & DDoS |
| **Security Headers** | `X-Frame-Options`, `X-Content-Type-Options`, `X-XSS-Protection`, `Referrer-Policy` |
| **Upload Limit** | Max 5MB — mencegah disk penuh |
| **Server Tokens Off** | Versi Nginx disembunyikan |
| **Hidden Files Blocked** | Akses ke `.env`, `.git`, dll diblokir |
| **No New Privileges** | Semua container tidak bisa eskalasi privilege |
| **Resource Limits** | CPU & memory dibatasi per container |
| **Read-only Nginx** | Filesystem Nginx read-only (tmpfs untuk cache) |
| **Database Terisolasi** | MariaDB di network internal, tidak bisa akses internet |
| **Secrets di .env** | Password tidak hardcoded di `compose.yml` |

---

## 📂 Struktur Direktori

```text
flarum-docker/
├── compose.yml        # Definisi semua service Docker
├── Dockerfile         # Custom PHP-FPM image + auto-install Flarum
├── entrypoint.sh      # Script otomatis install Flarum saat pertama run
├── nginx.conf         # Konfigurasi Nginx (+ security hardening)
├── .env.example       # Template variabel environment
├── .env               # Variabel environment (JANGAN commit ke git!)
├── .gitignore         # Mengecualikan .env dan data dari git
├── README.md          # Versi Inggris (utama)
└── README.id.md       # File ini (Bahasa Indonesia)
```

---

## ⚡ Cara Deploy

### 1. Clone & Setup Environment

```bash
git clone <url-repo-anda>
cd flarum-docker

# Salin template environment
cp .env.example .env
```

Generate password yang kuat lalu isi ke `.env`:

```bash
# Generate password random yang kuat
openssl rand -base64 32
```

Jalankan dua kali — satu untuk `MYSQL_ROOT_PASSWORD` dan satu untuk `MYSQL_PASSWORD`. Paste hasilnya ke `.env`.

### 2. Konfigurasi Cloudflare Tunnel

Karena `cloudflared` sudah terinstall di VPS, cukup tambahkan **Public Hostname** baru pada tunnel yang sudah ada:

**Via Cloudflare Zero Trust Dashboard:**
1. Buka tunnel yang aktif di VPS Anda
2. Tambahkan Public Hostname baru:
   - **Domain**: domain/subdomain untuk forum Anda
   - **Service**: `http://localhost:8281`

**Atau via config file** (`/etc/cloudflared/config.yml`):
```yaml
- hostname: forum.domainanda.com
  service: http://localhost:8281
```

Lalu restart cloudflared:
```bash
sudo systemctl restart cloudflared
```

### 3. Jalankan

```bash
docker compose build
docker compose up -d
docker logs -f flarum_app
```

Flarum akan **otomatis terinstall** pada pertama kali dijalankan.

> 💡 **Tip**: Jika Anda mengubah `Dockerfile`, gunakan `docker compose build --no-cache` untuk rebuild dari awal.

### 4. Selesaikan Instalasi Web

Buka domain Anda di browser. Flarum akan menampilkan halaman instalasi. Isi:
- **Database Host**: `db`
- **Database Name**: `flarum` (atau sesuai `.env`)
- **Database User**: `flarum` (atau sesuai `.env`)
- **Database Password**: (sesuai `MYSQL_PASSWORD` di `.env`)

---

## 🔧 Perintah Berguna

```bash
# Build & jalankan semua service
docker compose build
docker compose up -d

# Rebuild dari awal (setelah ubah Dockerfile)
docker compose build --no-cache
docker compose up -d

# Lihat log semua service
docker compose logs -f

# Masuk ke container Flarum (untuk install ekstensi, dll)
docker exec -it flarum_app sh

# Install ekstensi Flarum (contoh)
docker exec -it flarum_app su-exec www-data composer require fof/user-bio

# Restart service
docker compose restart

# Matikan semua service
docker compose down

# Matikan + hapus data (HATI-HATI!)
docker compose down -v
```

---

## 🖥️ Rekomendasi Hardening VPS

Selain keamanan di level Docker, pastikan VPS Anda juga di-hardening:

| Item | Perintah |
|------|----------|
| **Firewall (UFW)** | `sudo ufw allow OpenSSH && sudo ufw enable` |
| **Disable root login** | `PermitRootLogin no` di `/etc/ssh/sshd_config` |
| **SSH Key only** | `PasswordAuthentication no` di `/etc/ssh/sshd_config` |
| **Fail2Ban** | `sudo apt install fail2ban` |
| **Auto security updates** | `sudo apt install unattended-upgrades` |
| **Backup database** | `docker exec flarum_db mysqldump -u root -p flarum > backup.sql` |
