# Dokumentasi Konsep PHP dalam Aplikasi Pengaduan

## 1. Tipe Data (Data Types)

Dalam kode ini, beberapa tipe data dasar PHP digunakan untuk menyimpan informasi dari database dan input user.

### String
Menyimpan teks seperti status, feedback, atau URL.

```php
$status = $data['status'] ?? 'Menunggu';
$filter_nis = isset($_GET['filter_nis']) ? $_GET['filter_nis'] : '';
```

### Integer
Menyimpan angka untuk ID dan numerik halaman.

```php
$id = isset($_GET['id']) ? $_GET['id'] : null;           // ID Pelaporan
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
```

### Array
Menyimpan kumpulan data (baris database).

```php
$feedback_history = [];                          // Array kosong
$data = mysqli_fetch_assoc($result);             // Array Asosiatif
```

### Boolean
Hasil dari pengecekan kondisi.

```php
if (isset($_SESSION['login']))                   // Mengembalikan true/false
```

---

## 2. Struktur Kontrol (Control Structures)

Logika alur program diatur menggunakan struktur kontrol berikut:

### Percabangan (if / else)
Digunakan untuk validasi session dan pengecekan data.

```php
if (!isset($_SESSION['login']) || $_SESSION['role'] != 'admin') {
    header('Location: ../index.php');
    exit;
}
```

### Perulangan while
Digunakan untuk mengambil data dari database baris demi baris.

```php
while ($fb = mysqli_fetch_assoc($feedback_result)) {
    $feedback_history[] = $fb;
}
```

### Perulangan for
Digunakan untuk membuat nomor halaman pada pagination.

```php
for($i=1; $i<=$total_pages; $i++){
    $active = ($i == $page) ? 'active' : '';
    echo "<a href='?page=$i$qs' class='page-link $active'>$i</a>";
}
```

---

## 3. Fungsi (Functions)

Kode ini memanfaatkan Built-in Functions (fungsi bawaan PHP) dan fungsi database.

### Fungsi Database

```php
mysqli_query($koneksi, $query);        // Menjalankan perintah SQL
mysqli_fetch_assoc($result);           // Mengambil hasil query sebagai array
mysqli_num_rows($result);              // Menghitung jumlah baris hasil query
```

### Fungsi String & Tanggal

```php
date('d/m/Y H:i', strtotime($data['tanggal_lapor']));  // Format tanggal
htmlspecialchars($filter_nis);                          // Keamanan output (mencegah XSS)
count($feedback_history);                               // Menghitung elemen array
```

---

## 4. Dimensi Array

Array digunakan untuk menyimpan data yang kompleks.

### Array Satu Dimensi (Asosiatif)
Menyimpan satu data pelaporan.

```php
$data = [
    'nis' => '12345',
    'kelas' => 'XII RPL',
    'status' => 'Proses'
];

// Diakses via: $data['nis']
```

### Array Multi Dimensi
Menyimpan riwayat feedback (array di dalam array).

```php
$feedback_history = [
    0 => [
        'status' => 'Proses',
        'feedback' => 'Laporan diterima',
        'tanggal' => '2023-10-01'
    ],
    1 => [
        'status' => 'Selesai',
        'feedback' => 'Perbaikan selesai',
        'tanggal' => '2023-10-02'
    ]
];

// Diakses via: $feedback_history[0]['feedback']
```

---

## 5. Menentukan Panjang Array (Looping)

Panjang array ditentukan untuk keperluan logika penampilan data.

### Menggunakan count()
Menghitung jumlah elemen array.

```php
<div class="card-title">🕒 Riwayat Proses (<?php echo count($feedback_history); ?>)</div>
```

### Iterasi (Looping) dengan foreach/while
Meskipun kode menggunakan while untuk fetch database, konsepnya sama untuk menelusuri setiap elemen array.

```php
// Logika penelusuran array history
if (!empty($feedback_history)):
    // Meloop setiap data di dalam array $feedback_history
    foreach ($feedback_history as $fb):
        // Menampilkan data $fb
    endforeach;
endif;
```

---

## 6. Pengurutan Array (Order By)

Pengurutan data dalam kode ini tidak dilakukan dengan fungsi PHP sort(), melainkan langsung pada level database (SQL Query) untuk efisiensi, hasilnya kemudian dimasukkan ke dalam array PHP.

### Order By di SQL
Mengurutkan berdasarkan waktu terbaru.

```php
$query = "SELECT ia.*, k.ket_kategori...
          WHERE $where
          ORDER BY ia.tanggal_lapor DESC  -- Pengurutan Descending (Terbaru ke Terlama)
          LIMIT $offset, $per_page";
```

### Order By History Feedback
Mengurutkan riwayat feedback.

```php
$feedback_query = "SELECT * FROM aspirasi_feedback
                   WHERE id_aspirasi = $id_aspirasi
                   ORDER BY tanggal_feedback DESC";
```

---

