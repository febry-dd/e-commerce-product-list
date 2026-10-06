<?php
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/config.php';

$kategoriDipilih = trim($_GET['kategori'] ?? '');
$kategoriQuery = $koneksi->query(
    'SELECT DISTINCT kategori FROM products WHERE kategori <> "" ORDER BY kategori'
);
$kategoriProduk = $kategoriQuery->fetchAll(PDO::FETCH_COLUMN);

if ($kategoriDipilih !== '' && in_array($kategoriDipilih, $kategoriProduk, true)) {
    $pernyataan = $koneksi->prepare(
        'SELECT id, nama_produk, harga, deskripsi, kategori FROM products WHERE kategori = :kategori ORDER BY id DESC'
    );
    $pernyataan->execute([':kategori' => $kategoriDipilih]);
} else {
    $kategoriDipilih = '';
    $pernyataan = $koneksi->query(
        'SELECT id, nama_produk, harga, deskripsi, kategori FROM products ORDER BY id DESC'
    );
}

$produk = $pernyataan->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Store - E-Commerce</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Product Store</h1>
        <p>Daftar Produk E-Commerce</p>
    </header>

    <main>
        <section class="manage-products">
            <h2>Kelola Produk</h2>
            <div class="manage-actions">
                <a class="add-product-link" href="tambah_produk.php">Tambah Produk</a>
                <a class="cart-link" href="keranjang.php">Keranjang (<?= array_sum($_SESSION['keranjang'] ?? []) ?>)</a>
            </div>
        </section>

        <section class="filter-section">
            <h2>Filter Produk</h2>
            <form class="filter-form" method="get" action="index.php">
                <label for="kategori">Kategori</label>
                <select id="kategori" name="kategori">
                    <option value="">Semua kategori</option>
                    <?php foreach ($kategoriProduk as $kategori): ?>
                        <option value="<?= aman($kategori) ?>" <?= $kategoriDipilih === $kategori ? 'selected' : '' ?>>
                            <?= aman($kategori) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Terapkan filter</button>
            </form>
        </section>

        <section>
            <h2>Daftar Produk</h2>
            <?php
            $pesan = [
                'produk_diperbarui' => 'Produk berhasil diperbarui.',
                'produk_dihapus' => 'Produk berhasil dihapus.',
                'produk_tidak_ditemukan' => 'Produk tidak ditemukan.',
                'produk_gagal_dihapus' => 'Produk tidak dapat dihapus karena sudah digunakan dalam pesanan.',
            ][$_GET['pesan'] ?? ''] ?? '';
            ?>
            <?php if ($pesan !== ''): ?><p class="form-message success" role="status"><?= aman($pesan) ?></p><?php endif; ?>
            <?php if ($produk === []): ?>
                <p class="empty-products">Belum ada produk untuk kategori ini.</p>
            <?php else: ?>
                <div class="product-container">
                    <?php foreach ($produk as $item): ?>
                        <?php
                        $gambarProduk = gambar_produk((int) $item['id']);
                        ?>
                        <article class="product-card">
                            <?php if ($gambarProduk !== ''): ?>
                                <img src="<?= aman($gambarProduk) ?>" alt="<?= aman($item['nama_produk']) ?>">
                            <?php else: ?>
                                <div class="product-image-placeholder" aria-hidden="true">Produk</div>
                            <?php endif; ?>
                            <div class="product-content">
                                <div class="product-category"><?= aman($item['kategori']) ?></div>
                                <h3 class="product-name"><?= aman($item['nama_produk']) ?></h3>
                                <p class="product-description"><?= aman($item['deskripsi'] ?? '') ?></p>
                                <div class="product-price">Rp <?= number_format((float) $item['harga'], 0, ',', '.') ?></div>
                                <div class="product-actions">
                                    <form method="post" action="keranjang.php">
                                        <input type="hidden" name="token_csrf" value="<?= aman(token_csrf()) ?>">
                                        <input type="hidden" name="aksi" value="tambah">
                                        <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                                        <button type="submit">Tambah ke Keranjang</button>
                                    </form>
                                    <a href="edit_produk.php?id=<?= (int) $item['id'] ?>">Edit</a>
                                    <form method="post" action="hapus_produk.php" onsubmit="return confirm('Hapus produk ini?')">
                                        <input type="hidden" name="token_csrf" value="<?= aman(token_csrf()) ?>">
                                        <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                                        <button class="button-danger" type="submit">Hapus</button>
                                    </form>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>