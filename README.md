# 🏫 Sistem Pengaduan Aspirasi Sekolah

## Panduan Setup & Penggunaan

---

## 📋 Daftar Isi
1. [Setup Database](#setup-database)
2. [Data Login Default](#data-login-default)
3. [Fitur Admin](#fitur-admin)
4. [Fitur Siswa](#fitur-siswa)
5. [Struktur Folder](#struktur-folder)

---

## 🔧 Setup Database

### Langkah 1: Akses Setup Page
1. Buka browser dan akses: `http://localhost/pengadu/setup.php`
2. Klik tombol atau refresh halaman
3. Tunggu sampai semua tabel berhasil dibuat

### Langkah 2: Verifikasi
Jika muncul pesan ✅ "Setup Berhasil!", maka database sudah siap digunakan.

---

## 🔐 Data Login Default

### Admin
- **URL Login:** http://localhost/pengadu/index.php
- **Role:** Admin
- **Username:** `admin`
- **Password:** `12345`

### Siswa
- **URL Login:** http://localhost/pengadu/index.php
- **Role:** Siswa
- **NIS:** `001`, `002`, `003`
- **Password:** Sama dengan NIS (contoh: NIS 001, password 001)

---

## 👨‍💼 Fitur Admin

### 1. Dashboard Admin
- **URL:** `/admin/dashboard.php`
- **Fitur:**
  - Melihat statistik total aspirasi
  - Melihat jumlah aspirasi per status (Menunggu, Proses, Selesai)
  - Melihat 10 aspirasi terbaru

### 2. Daftar Aspirasi
- **URL:** `/admin/aspirasi.php`
- **Fitur:**
  - Melihat semua aspirasi
  - Filter berdasarkan:
    - Siswa (NIS)
    - Kategori
    - Status
    - Bulan/Tanggal
  - Lihat detail aspirasi
  - Update status & memberikan feedback

### 3. Update Status & Feedback
- Dilakukan melalui halaman detail aspirasi
- Admin bisa mengubah status: Menunggu → Proses → Selesai
- Admin bisa memberikan feedback/tanggapan untuk siswa

---

## 👨‍🎓 Fitur Siswa

### 1. Dashboard Siswa
- **URL:** `/siswa/dashboard.php`
- **Fitur:**
  - Melihat statistik aspirasi mereka
  - Melihat 5 aspirasi terbaru
  - Shortcut untuk input aspirasi baru

### 2. Input Aspirasi
- **URL:** `/siswa/input_aspirasi.php`
- **Form Input:**
  - NIS (auto dari session login)
  - Kategori aspirasi (dropdown)
  - Lokasi kejadian
  - Keterangan aspirasi (textarea)
- **Fitur:**
  - Validasi form
  - Alert sukses/gagal
  - Data langsung tersimpan ke database

### 3. Histori Aspirasi
- **URL:** `/siswa/histori.php`
- **Fitur:**
  - Melihat semua aspirasi yang pernah dibuat
  - Urut dari terbaru ke terlama
  - Melihat status aspirasi
  - Lihat detail aspirasi

### 4. Detail Aspirasi
- **URL:** `/siswa/detail.php?id=ID`
- **Fitur:**
  - Melihat detail lengkap aspirasi
  - Melihat status saat ini
  - Melihat feedback dari admin (jika status Selesai)
  - Timeline progres aspirasi

### 5. Progres Perbaikan
- **URL:** `/siswa/progres.php`
- **Fitur:**
  - Grafik persentase status aspirasi
  - Tabel detail progres setiap aspirasi
  - Melihat update terakhir

---

## 📁 Struktur Folder

```
pengadu/
├── index.php                 # Halaman login utama
├── setup.php                # Halaman setup database
├── config/
│   └── koneksi.php          # Konfigurasi database
├── admin/
│   ├── dashboard.php        # Dashboard admin
│   ├── aspirasi.php         # List & detail aspirasi
│   ├── edit_status.php      # Redirect edit_status
│   ├── login.php            # Redirect login
│   ├── logout.php           # Logout admin
│   └── proses_login.php     # Proses login redirect
├── siswa/
│   ├── dashboard.php        # Dashboard siswa
│   ├── input_aspirasi.php   # Form input aspirasi
│   ├── detail.php           # Detail aspirasi
│   ├── histori.php          # Histori aspirasi
│   ├── progres.php          # Progres perbaikan
│   ├── logout.php           # Logout siswa
│   └── proses_input.php     # Proses input redirect
├── assets/
│   └── style.css            # Styling Bootstrap & Custom
└── database.sql             # SQL untuk backup database
```

---

## 🗄️ Struktur Database

### Tabel `admin`
```
- username (PK)
- password
```

### Tabel `siswa`
```
- nis (PK)
- nama
- kelas
- password
```

### Tabel `kategori`
```
- id_kategori (PK, AUTO_INCREMENT)
- ket_kategori
```

### Tabel `input_aspirasi`
```
- id_pelaporan (PK, AUTO_INCREMENT)
- nis (FK ke siswa)
- id_kategori (FK ke kategori)
- lokasi
- ket
- tanggal_lapor (TIMESTAMP)
```

### Tabel `aspirasi`
```
- id_aspirasi (PK, AUTO_INCREMENT)
- id_pelaporan (FK ke input_aspirasi)
- status (ENUM: Menunggu, Proses, Selesai)
- feedback
- tanggal_update (TIMESTAMP)
```

---

## 🔄 Alur Sistem

1. **Siswa Login** → Input aspirasi
2. **Data Input** → Disimpan ke `input_aspirasi` + `aspirasi` (status: Menunggu)
3. **Admin View** → Melihat aspirasi baru di dashboard
4. **Admin Process** → Mengubah status & memberikan feedback
5. **Siswa View** → Melihat perubahan status & feedback di histori/progres

---

## 🎨 Fitur Desain

- ✅ Bootstrap 5 untuk responsive design
- ✅ Gradient color untuk navbar & stat cards
- ✅ Badge status untuk visual status
- ✅ Alert untuk success/error messages
- ✅ Sidebar navigation untuk easy access
- ✅ Timeline untuk progres aspirasi
- ✅ Responsive untuk mobile & desktop

---

## 💡 Tips Penggunaan

### Untuk Admin:
1. Selalu cek dashboard untuk statistik terbaru
2. Filter aspirasi untuk memudahkan pencarian
3. Berikan feedback yang jelas dan membantu
4. Update status secara berkala

### Untuk Siswa:
1. Login dengan NIS dan password yang benar
2. Input aspirasi dengan detail dan jelas
3. Pantau progres aspirasi di dashboard
4. Baca feedback dari admin di detail aspirasi

---

## 📞 Troubleshooting

### Tabel Tidak Ditemukan?
- Akses: `http://localhost/pengadu/setup.php`
- Refresh halaman untuk membuat tabel

### Login Gagal?
- Pastikan menggunakan username/NIS & password yang benar
- Username admin: `admin`, password: `12345`
- NIS siswa: `001/002/003`, password: sama dengan NIS

### Data Tidak Muncul?
- Pastikan database sudah ter-setup dengan baik
- Refresh halaman browser
- Cek browser console untuk error

---

**Dibuat: 26 Januari 2026**
**Versi: 1.0**
