<?php
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/config.php';

$idProduk = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idProduk = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
}

if (!$idProduk || $idProduk < 1) {
    http_response_code(400);
    exit('ID produk tidak valid.');
}

$pernyataan = $koneksi->prepare('SELECT * FROM products WHERE id = :id');
$pernyataan->execute([':id' => $idProduk]);
$produk = $pernyataan->fetch();
if (!$produk) {
    http_response_code(404);
    exit('Produk tidak ditemukan.');
}

$daftarKategori = ['Elektronik', 'Fashion', 'Aksesoris'];
$pesanError = '';
$ukuranMaksimumGambar = 3 * 1024 * 1024;
$tipeGambarDiizinkan = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $namaProduk = trim($_POST['nama_produk'] ?? '');
    $harga = trim($_POST['harga'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $kategori = trim($_POST['kategori'] ?? '');
    $fileGambar = $_FILES['gambar'] ?? null;
    $ekstensiGambar = '';

    if (!csrf_valid()) {
        $pesanError = 'Sesi formulir tidak valid. Muat ulang halaman lalu coba lagi.';
    } elseif ($namaProduk === '' || $harga === '' || $deskripsi === '') {
        $pesanError = 'Nama, harga, dan deskripsi wajib diisi.';
    } elseif (!is_numeric($harga) || (float) $harga <= 0) {
        $pesanError = 'Harga harus berupa angka lebih besar dari nol.';
    } elseif (!in_array($kategori, $daftarKategori, true)) {
        $pesanError = 'Kategori produk tidak valid.';
    } elseif ($fileGambar !== null && $fileGambar['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($fileGambar['error'] !== UPLOAD_ERR_OK || $fileGambar['size'] <= 0 || $fileGambar['size'] > $ukuranMaksimumGambar) {
            $pesanError = 'Foto gagal diunggah atau melebihi batas 3 MB.';
        } else {
            $tipeGambar = (new finfo(FILEINFO_MIME_TYPE))->file($fileGambar['tmp_name']);
            $ekstensiGambar = $tipeGambarDiizinkan[$tipeGambar] ?? '';
            if ($ekstensiGambar === '') {
                $pesanError = 'Format foto harus JPG, PNG, atau WebP.';
            }
        }
    }

    if ($pesanError === '') {
        $fileBaru = '';
        try {
            $pernyataan = $koneksi->prepare(
                'UPDATE products SET nama_produk = :nama_produk, harga = :harga, deskripsi = :deskripsi, kategori = :kategori WHERE id = :id'
            );
            $pernyataan->execute([
                ':nama_produk' => $namaProduk,
                ':harga' => $harga,
                ':deskripsi' => $deskripsi,
                ':kategori' => $kategori,
                ':id' => $idProduk,
            ]);

            if ($ekstensiGambar !== '') {
                $direktoriGambar = __DIR__ . '/uploads/products';
                if (!is_dir($direktoriGambar) && !mkdir($direktoriGambar, 0755, true) && !is_dir($direktoriGambar)) {
                    throw new RuntimeException('Direktori foto tidak dapat dibuat.');
                }
                $fileBaru = $direktoriGambar . '/' . $idProduk . '.' . $ekstensiGambar;
                if (!move_uploaded_file($fileGambar['tmp_name'], $fileBaru)) {
                    throw new RuntimeException('Foto tidak dapat disimpan.');
                }
                foreach (['jpg', 'png', 'webp'] as $ekstensiLama) {
                    $fileLama = $direktoriGambar . '/' . $idProduk . '.' . $ekstensiLama;
                    if ($ekstensiLama !== $ekstensiGambar && is_file($fileLama)) {
                        unlink($fileLama);
                    }
                }
            }

            header('Location: index.php?pesan=produk_diperbarui');
            exit;
        } catch (Throwable $kesalahan) {
            $pesanError = 'Produk gagal diperbarui. Periksa koneksi database dan izin folder uploads.';
        }
    }

    $produk['nama_produk'] = $namaProduk;
    $produk['harga'] = $harga;
    $produk['deskripsi'] = $deskripsi;
    $produk['kategori'] = $kategori;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Produk - E-Commerce</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header><h1>Edit Produk</h1><p>Perbarui informasi produk</p></header>
    <main class="form-page">
        <section class="form-panel">
            <h2><?= aman($produk['nama_produk']) ?></h2>
            <?php if ($pesanError !== ''): ?><p class="form-message error" role="alert"><?= aman($pesanError) ?></p><?php endif; ?>
            <form class="product-form" method="post" action="edit_produk.php" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= (int) $idProduk ?>">
                <input type="hidden" name="token_csrf" value="<?= aman(token_csrf()) ?>">
                <label for="nama_produk">Nama produk<input id="nama_produk" name="nama_produk" type="text" maxlength="100" required value="<?= aman((string) $produk['nama_produk']) ?>"></label>
                <label for="harga">Harga (Rp)<input id="harga" name="harga" type="number" min="0.01" step="0.01" required value="<?= aman((string) $produk['harga']) ?>"></label>
                <label for="deskripsi">Deskripsi<textarea id="deskripsi" name="deskripsi" required><?= aman((string) $produk['deskripsi']) ?></textarea></label>
                <label for="kategori">Kategori<select id="kategori" name="kategori" required><?php foreach ($daftarKategori as $kategori): ?><option value="<?= aman($kategori) ?>" <?= $produk['kategori'] === $kategori ? 'selected' : '' ?>><?= aman($kategori) ?></option><?php endforeach; ?></select></label>
                <label for="gambar">Ganti foto (opsional, JPG/PNG/WebP maksimal 3 MB)<input id="gambar" name="gambar" type="file" accept="image/jpeg,image/png,image/webp"></label>
                <button type="submit">Simpan Perubahan</button>
            </form>
        </section>
        <a class="back-link" href="index.php">Kembali ke daftar produk</a>
    </main>
</body>
</html>