<?php
require_once __DIR__ . '/config.php';

function aman(string $nilai): string
{
    return htmlspecialchars($nilai, ENT_QUOTES, 'UTF-8');
}

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
            <a class="add-product-link" href="tambah_produk.php">Tambah Produk</a>
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
            <?php if ($produk === []): ?>
                <p class="empty-products">Belum ada produk untuk kategori ini.</p>
            <?php else: ?>
                <div class="product-container">
                    <?php foreach ($produk as $item): ?>
                        <?php
                        $gambarProduk = '';
                        foreach (['jpg', 'png', 'webp'] as $ekstensi) {
                            $namaFile = $item['id'] . '.' . $ekstensi;
                            if (is_file(__DIR__ . '/uploads/products/' . $namaFile)) {
                                $gambarProduk = 'uploads/products/' . $namaFile;
                                break;
                            }
                        }
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
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>