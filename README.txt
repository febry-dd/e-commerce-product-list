# E-Commerce Product List

Project sederhana untuk tugas JavaScript:

1. Membuat array data produk.
2. Data berisi nama, harga, deskripsi, gambar, dan kategori.
3. Menampilkan produk menggunakan looping JavaScript.
4. Menambahkan filter produk berdasarkan kategori.

## Form tambah produk (PHP)

Halaman `tambah_produk.php` menerima nama produk, harga, dan deskripsi, memeriksa agar semua field terisi, lalu menyimpan data ke tabel `products` menggunakan prepared statement.

Form produk juga mewajibkan foto JPG, PNG, atau WebP maksimal 3 MB. File foto disimpan di `uploads/products` dan ditampilkan pada dashboard. Jika PHP menolak file sebelum validasi aplikasi, atur `upload_max_filesize` menjadi minimal `3M` dan `post_max_size` menjadi minimal `4M` di `php.ini`, lalu restart Apache.

1. Jalankan Apache dan MySQL melalui Laragon.
2. Import `ecommerce.sql` ke MySQL. Database yang digunakan bernama `ecommerce`.
3. Buka `http://localhost/e-commerce/` melalui browser. PHP tidak berjalan jika halaman dibuka langsung sebagai file HTML.

Koneksi bawaan memakai host `127.0.0.1`, user `root`, dan password kosong. Sesuaikan bagian koneksi di `tambah_produk.php` jika pengaturan MySQL berbeda.

## Cara menjalankan

1. Extract file ZIP.
2. Buka folder project.
3. Buka `index.html` menggunakan browser.

## Struktur

e-commerce/
├── index.html
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
