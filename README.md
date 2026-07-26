# Max AI

Situs kumpulan **Tools AI**, dibangun dengan **Laravel 13 + PHP 8.5 + PostgreSQL**.

Fitur pertama: **Remove Background** — hapus background foto otomatis pakai AI (model U2Net via [rembg](https://github.com/danielgatis/rembg)), dijalankan sebagai microservice Python terpisah dan dipanggil dari Laravel.

## Arsitektur singkat

```
Browser  --->  Laravel (PHP 8.5, PostgreSQL)  --->  Python microservice (FastAPI + rembg)
                     |                                        |
              simpan history di DB                    proses hapus background (U2Net)
                     |                                        |
              simpan file di storage/app/public   <-----------+
```

Laravel **tidak** melakukan proses AI langsung di PHP. Laravel menerima upload, mengirim file ke microservice Python via HTTP, lalu menyimpan hasil PNG transparan yang dikembalikan.

## Persiapan

### 1. Requirement
- PHP 8.5 + ekstensi: `pdo_pgsql`, `mbstring`, `xml`, `curl`, `gd`, `zip`
- Composer 2.x
- PostgreSQL 15+
- Python 3.10+ (untuk microservice rembg, khusus fitur Remove Background)
- **Tesseract OCR** (untuk fitur Gambar ke Teks): `sudo apt install tesseract-ocr tesseract-ocr-ind`
- Node.js (opsional, jika ingin build asset; saat ini Tailwind dipakai via CDN jadi tidak wajib)

### 2. Install dependency Laravel

```bash
composer install
cp .env.example .env
php artisan key:generate
```

### 3. Konfigurasi PostgreSQL

Buat database dulu:

```bash
createdb max_ai
```

Isi `.env`:

```
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=max_ai
DB_USERNAME=postgres
DB_PASSWORD=isi_password_kamu
```

Lalu jalankan migration:

```bash
php artisan migrate
php artisan storage:link
```

### 4. Jalankan microservice Remove Background (Python)

```bash
cd python-service
python3 -m venv venv
source venv/bin/activate      # Windows: venv\Scripts\activate
pip install -r requirements.txt
uvicorn main:app --host 127.0.0.1 --port 8001
```

Model U2Net akan otomatis di-download saat request pertama (butuh internet sekali di awal, lalu di-cache).

Pastikan `.env` Laravel menunjuk ke service ini:

```
REMBG_SERVICE_URL=http://127.0.0.1:8001
```

### 5. Jalankan Laravel

```bash
php artisan serve
```

Buka `http://localhost:8000` lalu klik tool **Remove Background**.

## Menu & Fitur

**Home** - katalog semua tools.

**Proses Gambar:**
| Tool | File utama |
|---|---|
| Remove Background | `app/Http/Controllers/Tools/RemoveBackgroundController.php` + `python-service/main.py` (microservice FastAPI + rembg/U2Net) |
| Gambar ke PDF | `app/Http/Controllers/Tools/ImageToPdfController.php` (pakai TCPDF) |
| Gambar ke Teks (OCR) | `app/Http/Controllers/Tools/ImageToTextController.php` (pakai Tesseract OCR via `thiagoalessio/tesseract_ocr`) |

**Proses PDF:**
| Tool | File utama |
|---|---|
| Gabung PDF (Merge) | `app/Http/Controllers/Tools/PdfMergeController.php` (pakai FPDI + TCPDF) |
| Pecah PDF (Split) | `app/Http/Controllers/Tools/PdfSplitController.php` (pakai FPDI + TCPDF, hasil di-zip) |

**Tool Lainnya:** placeholder untuk pengembangan berikutnya (Text to Image, Speech to Text, PDF Summarizer).

## Member Area (file manager ala Google Drive)

Fitur register/login + file manager pribadi per user, lengkap dengan folder.

| Bagian | File utama |
|---|---|
| Register/Login/Logout | `app/Http/Controllers/Auth/AuthController.php` |
| Folder (buat/rename/hapus/navigasi) | `app/Http/Controllers/Member/MemberFolderController.php` |
| File (upload/rename/download/hapus) | `app/Http/Controllers/Member/MemberFileController.php` |
| Model folder | `app/Models/MemberFolder.php` |
| Model file | `app/Models/MemberFile.php` |
| Whitelist ekstensi & kuota | `config/uploads.php` |

**Fitur file manager:**
- Buat folder, masuk ke dalam folder (breadcrumb navigasi), rename & hapus folder (hapus folder = hapus semua isi di dalamnya)
- Upload, rename, **pindahkan (move)**, **salin (copy)**, download, hapus file
- **Pindahkan folder** ke folder lain (dicegah pindah ke dalam dirinya sendiri/sub-foldernya)
- Folder bawaan **"Hasil"** otomatis dibuat saat user daftar — tidak bisa dihapus/rename/dipindah. Semua hasil proses dari tools (Remove Background, Gambar ke PDF, Gabung PDF, Pecah PDF, **Gambar ke Teks/OCR**) otomatis tersimpan ke folder ini kalau user sedang login, jadi tidak perlu download manual lalu upload ulang.

**Keamanan upload file member:**
- Hanya ekstensi di whitelist (`config/uploads.php`) yang diterima — sengaja tidak menyertakan `php`, `phtml`, `js`, `html`, `svg`, `exe`, `sh`, dll agar file yang diupload tidak bisa dieksekusi sebagai script.
- Nama file disimpan dengan UUID acak (bukan nama asli), jadi tidak bisa ditebak/di-path-traversal.
- Ada kuota per user (`quota_mb_per_user`, default 1GB) supaya storage server tidak penuh.
- File & folder hanya bisa diakses oleh pemiliknya (dicek `user_id` di setiap request).
- `storage/app/public/.htaccess` mematikan eksekusi PHP/script apapun di folder ini (proteksi tambahan untuk server Apache). **Kalau pakai Nginx**, tambahkan blok berikut di server config kamu:
  ```nginx
  location ^~ /storage/ {
      location ~ \.(php|phtml|pl|py|cgi|sh)$ {
          deny all;
      }
  }
  ```

## Roadmap tools berikutnya

Landing page (`resources/views/home/index.blade.php`) sudah dikelompokkan per kategori, tinggal tambah card baru mengikuti pola folder `app/Http/Controllers/Tools/`.

## Catatan keamanan

- Jangan commit file `.env` ke repo (sudah ada di `.gitignore`).
- Batasi ukuran upload (`max:8192` KB di validator) untuk menghindari abuse.
- Untuk production, jalankan microservice Python di belakang reverse proxy / auth token internal, jangan expose port 8001 langsung ke publik.
