# E-Commerce Product List

Project sederhana untuk tugas PHP dan JavaScript:

1. Koneksi PHP ke database MySQL melalui `config.php`.
2. Menampilkan produk dari database dengan looping PHP.
3. Memfilter produk berdasarkan kategori melalui query SQL.

## Form tambah produk (PHP)

Halaman `tambah_produk.php` menerima nama, harga, deskripsi, kategori, dan foto. Data produk disimpan menggunakan prepared statement.

Form produk juga mewajibkan foto JPG, PNG, atau WebP maksimal 3 MB. File foto disimpan di `uploads/products` dan ditampilkan pada dashboard. Jika PHP menolak file sebelum validasi aplikasi, atur `upload_max_filesize` menjadi minimal `3M` dan `post_max_size` menjadi minimal `4M` di `php.ini`, lalu restart Apache.

1. Jalankan Apache dan MySQL melalui Laragon.
2. Untuk database baru, import `ecommerce.sql`. Untuk database yang sudah ada, jalankan `migrasi_kategori_produk.sql` satu kali di database `ecommerce`.
3. Buka `http://localhost/e-commerce/` melalui browser. Dashboard berjalan dari `index.php`; jangan membukanya langsung sebagai file HTML.

Koneksi bawaan di `config.php` memakai host `127.0.0.1`, database `ecommerce`, user `root`, dan password kosong. Sesuaikan konfigurasi tersebut jika pengaturan MySQL berbeda.

## Cara menjalankan

1. Extract file ZIP.
2. Buka folder project.
3. Jalankan melalui Apache/Laragon di `http://localhost/e-commerce/`.

## Struktur

e-commerce/
├── config.php
├── index.php
├── tambah_produk.php
├── migrasi_kategori_produk.sql
├── style.css
├── script.js
├── README.txt
└── images/

Catatan:
Folder `images` disediakan sebagai tempat gambar produk.
Jika ingin menggunakan gambar sendiri, masukkan file JPG sesuai nama:
- laptop.jpg
- smartphone.jpg
- sepatu.jpg
- tas.jpg
- smartwatch.jpg
- headphone.jpg
