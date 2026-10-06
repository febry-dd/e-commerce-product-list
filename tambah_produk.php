<?php
require_once __DIR__ . '/helpers.php';

$namaProduk = '';
$harga = '';
$deskripsi = '';
$kategoriDipilih = '';
$pesanError = '';
$pesanSukses = '';
$ukuranMaksimumGambar = 3 * 1024 * 1024;
$tipeGambarDiizinkan = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp',
];
$fileGambar = $_FILES['gambar'] ?? null;
$ekstensiGambar = '';
$daftarKategori = ['Elektronik', 'Fashion', 'Aksesoris'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $namaProduk = trim($_POST['nama_produk'] ?? '');
    $harga = trim($_POST['harga'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $kategoriDipilih = trim($_POST['kategori'] ?? '');

    if (!csrf_valid()) {
        $pesanError = 'Sesi formulir tidak valid. Muat ulang halaman lalu coba lagi.';
    } elseif ($namaProduk === '' || $harga === '' || $deskripsi === '' || $kategoriDipilih === '') {
        $pesanError = 'Nama, harga, deskripsi, dan kategori wajib diisi.';
    } elseif (!in_array($kategoriDipilih, $daftarKategori, true)) {
        $pesanError = 'Kategori produk tidak valid.';
    } elseif (!is_numeric($harga) || (float) $harga <= 0) {
        $pesanError = 'Harga harus berupa angka lebih besar dari nol.';
    } elseif ($fileGambar === null || $fileGambar['error'] === UPLOAD_ERR_NO_FILE) {
        $pesanError = 'Foto produk wajib diunggah.';
    } elseif ($fileGambar['error'] !== UPLOAD_ERR_OK) {
        $pesanError = 'Foto gagal diunggah. Pastikan ukurannya tidak lebih dari 3 MB.';
    } elseif ($fileGambar['size'] <= 0 || $fileGambar['size'] > $ukuranMaksimumGambar) {
        $pesanError = 'Ukuran foto maksimal 3 MB.';
    } else {
        $informasiFile = new finfo(FILEINFO_MIME_TYPE);
        $tipeGambar = $informasiFile->file($fileGambar['tmp_name']);
        $ekstensiGambar = $tipeGambarDiizinkan[$tipeGambar] ?? '';

        if ($ekstensiGambar === '') {
            $pesanError = 'Format foto harus JPG, PNG, atau WebP.';
        }
    }

    if ($pesanError === '') {
        $koneksi = null;
        $lokasiGambarTersimpan = '';

        try {
            require_once __DIR__ . '/config.php';

            $koneksi->beginTransaction();
            $pernyataan = $koneksi->prepare(
                'INSERT INTO products (nama_produk, harga, deskripsi, kategori) VALUES (:nama_produk, :harga, :deskripsi, :kategori)'
            );
            $pernyataan->execute([
                ':nama_produk' => $namaProduk,
                ':harga' => $harga,
                ':deskripsi' => $deskripsi,
                ':kategori' => $kategoriDipilih,
            ]);

            $direktoriGambar = __DIR__ . '/uploads/products';
            if (!is_dir($direktoriGambar) && !mkdir($direktoriGambar, 0755, true) && !is_dir($direktoriGambar)) {
                throw new RuntimeException('Direktori foto tidak dapat dibuat.');
            }

            $namaFileGambar = $koneksi->lastInsertId() . '.' . $ekstensiGambar;
            $lokasiGambarTersimpan = $direktoriGambar . '/' . $namaFileGambar;
            if (!move_uploaded_file($fileGambar['tmp_name'], $lokasiGambarTersimpan)) {
                throw new RuntimeException('Foto tidak dapat disimpan.');
            }

            $koneksi->commit();
            $pesanSukses = 'Produk berhasil disimpan ke database.';
            $namaProduk = '';
            $harga = '';
            $deskripsi = '';
            $kategoriDipilih = '';
        } catch (Throwable $kesalahan) {
            if ($koneksi instanceof PDO && $koneksi->inTransaction()) {
                $koneksi->rollBack();
            }
            if ($lokasiGambarTersimpan !== '' && is_file($lokasiGambarTersimpan)) {
                unlink($lokasiGambarTersimpan);
            }
            $pesanError = 'Produk belum dapat disimpan. Pastikan MySQL aktif dan database ecommerce tersedia.';
        }
    }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Produk - E-Commerce</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Tambah Produk</h1>
        <p>Masukkan informasi produk baru</p>
    </header>

    <main class="form-page">
        <section class="form-panel">
            <h2>Informasi Produk</h2>

            <?php if ($pesanError !== ''): ?>
                <p class="form-message error" role="alert"><?= aman($pesanError) ?></p>
            <?php elseif ($pesanSukses !== ''): ?>
                <p class="form-message success" role="status"><?= aman($pesanSukses) ?></p>
            <?php endif; ?>

            <form class="product-form" method="post" action="tambah_produk.php" enctype="multipart/form-data" id="form-produk">
                <input type="hidden" name="token_csrf" value="<?= aman(token_csrf()) ?>">
                <label for="nama_produk">
                    Nama produk
                    <input id="nama_produk" name="nama_produk" type="text" maxlength="100" required value="<?= aman($namaProduk) ?>">
                </label>

                <label for="harga">
                    Harga (Rp)
                    <input id="harga" name="harga" type="number" min="0.01" step="0.01" required value="<?= aman($harga) ?>">
                </label>

                <label for="deskripsi">
                    Deskripsi
                    <textarea id="deskripsi" name="deskripsi" required><?= aman($deskripsi) ?></textarea>
                </label>

                <label for="kategori">
                    Kategori
                    <select id="kategori" name="kategori" required>
                        <option value="">Pilih kategori</option>
                        <?php foreach ($daftarKategori as $kategori): ?>
                            <option value="<?= aman($kategori) ?>" <?= $kategoriDipilih === $kategori ? 'selected' : '' ?>>
                                <?= aman($kategori) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label for="gambar">
                    Foto produk (JPG, PNG, atau WebP; maksimal 3 MB)
                    <input id="gambar" name="gambar" type="file" accept="image/jpeg,image/png,image/webp" required>
                </label>
                <p class="form-message error" id="pesan-gambar" role="alert" hidden></p>

                <button type="submit">Simpan Produk</button>
            </form>
        </section>

        <a class="back-link" href="index.php">Kembali ke daftar produk</a>
    </main>
    <script>
        const formProduk = document.getElementById('form-produk');
        const inputGambar = document.getElementById('gambar');
        const pesanGambar = document.getElementById('pesan-gambar');
        const ukuranMaksimumGambar = 3 * 1024 * 1024;

        formProduk.addEventListener('submit', function(event) {
            const fileGambar = inputGambar.files[0];
            if (fileGambar && fileGambar.size > ukuranMaksimumGambar) {
                event.preventDefault();
                pesanGambar.textContent = 'Ukuran foto maksimal 3 MB.';
                pesanGambar.hidden = false;
            }
        });

        inputGambar.addEventListener('change', function() {
            pesanGambar.hidden = true;
        });
    </script>
</body>
</html>