<?php
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/config.php';

if (!isset($_SESSION['keranjang']) || !is_array($_SESSION['keranjang'])) {
    $_SESSION['keranjang'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        http_response_code(400);
        exit('Sesi keranjang tidak valid. Muat ulang halaman lalu coba lagi.');
    }

    $idProduk = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
    $aksi = $_POST['aksi'] ?? '';

    if ($aksi === 'tambah' && $idProduk && $idProduk > 0) {
        $cekProduk = $koneksi->prepare('SELECT id FROM products WHERE id = :id');
        $cekProduk->execute([':id' => $idProduk]);
        if ($cekProduk->fetchColumn()) {
            $_SESSION['keranjang'][$idProduk] = min(99, ($_SESSION['keranjang'][$idProduk] ?? 0) + 1);
        }
    } elseif (isset($_POST['hapus_id'])) {
        $idHapus = filter_var($_POST['hapus_id'], FILTER_VALIDATE_INT);
        if ($idHapus) {
            unset($_SESSION['keranjang'][$idHapus]);
        }
    } elseif ($aksi === 'kosongkan') {
        $_SESSION['keranjang'] = [];
    } elseif ($aksi === 'perbarui') {
        foreach ($_POST['kuantitas'] ?? [] as $id => $kuantitas) {
            $id = filter_var($id, FILTER_VALIDATE_INT);
            $kuantitas = filter_var($kuantitas, FILTER_VALIDATE_INT);
            if (!$id || !array_key_exists($id, $_SESSION['keranjang'])) {
                continue;
            }
            if (!$kuantitas || $kuantitas < 1) {
                unset($_SESSION['keranjang'][$id]);
            } else {
                $_SESSION['keranjang'][$id] = min(99, $kuantitas);
            }
        }
    }

    header('Location: keranjang.php');
    exit;
}

$barisKeranjang = [];
$totalKeranjang = 0.0;
$jumlahItem = array_sum($_SESSION['keranjang']);
if ($_SESSION['keranjang'] !== []) {
    $idProduk = array_map('intval', array_keys($_SESSION['keranjang']));
    $placeholder = implode(',', array_fill(0, count($idProduk), '?'));
    $pernyataan = $koneksi->prepare(
        "SELECT id, nama_produk, harga FROM products WHERE id IN ({$placeholder})"
    );
    $pernyataan->execute($idProduk);
    $barisKeranjang = $pernyataan->fetchAll();

    $idYangDitemukan = array_map('intval', array_column($barisKeranjang, 'id'));
    foreach (array_keys($_SESSION['keranjang']) as $idTersimpan) {
        if (!in_array((int) $idTersimpan, $idYangDitemukan, true)) {
            unset($_SESSION['keranjang'][$idTersimpan]);
        }
    }
    $jumlahItem = array_sum($_SESSION['keranjang']);

    foreach ($barisKeranjang as &$item) {
        $item['kuantitas'] = (int) $_SESSION['keranjang'][$item['id']];
        $item['subtotal'] = (float) $item['harga'] * $item['kuantitas'];
        $totalKeranjang += $item['subtotal'];
    }
    unset($item);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang - E-Commerce</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header><h1>Keranjang Belanja</h1><p><?= $jumlahItem ?> produk</p></header>
    <main>
        <?php if ($barisKeranjang === []): ?>
            <p class="empty-products">Keranjang masih kosong.</p>
        <?php else: ?>
            <form method="post" action="keranjang.php" class="cart-form">
                <input type="hidden" name="token_csrf" value="<?= aman(token_csrf()) ?>">
                <input type="hidden" name="aksi" value="perbarui">
                <div class="cart-list">
                    <?php foreach ($barisKeranjang as $item): ?>
                        <article class="cart-row">
                            <div>
                                <h2><?= aman($item['nama_produk']) ?></h2>
                                <p>Rp <?= number_format((float) $item['harga'], 0, ',', '.') ?> per item</p>
                            </div>
                            <label>Kuantitas
                                <input type="number" name="kuantitas[<?= (int) $item['id'] ?>]" min="0" max="99" value="<?= $item['kuantitas'] ?>">
                            </label>
                            <strong>Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></strong>
                            <button class="button-danger" type="submit" name="hapus_id" value="<?= (int) $item['id'] ?>">Hapus</button>
                        </article>
                    <?php endforeach; ?>
                </div>
                <div class="cart-summary">
                    <strong>Total: Rp <?= number_format($totalKeranjang, 0, ',', '.') ?></strong>
                    <div class="cart-actions">
                        <button type="submit">Perbarui keranjang</button>
                        <button class="button-secondary" type="submit" name="aksi" value="kosongkan">Kosongkan</button>
                    </div>
                </div>
            </form>
        <?php endif; ?>
        <a class="back-link" href="index.php">Lanjut belanja</a>
    </main>
</body>
</html>