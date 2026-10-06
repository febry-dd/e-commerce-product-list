<?php
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_valid()) {
    http_response_code(400);
    exit('Permintaan tidak valid.');
}

$idProduk = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
if (!$idProduk || $idProduk < 1) {
    http_response_code(400);
    exit('ID produk tidak valid.');
}

try {
    $pernyataan = $koneksi->prepare('DELETE FROM products WHERE id = :id');
    $pernyataan->execute([':id' => $idProduk]);
    if ($pernyataan->rowCount() === 0) {
        header('Location: index.php?pesan=produk_tidak_ditemukan');
        exit;
    }

    hapus_gambar_produk((int) $idProduk);
    unset($_SESSION['keranjang'][$idProduk]);
    header('Location: index.php?pesan=produk_dihapus');
} catch (PDOException $kesalahan) {
    header('Location: index.php?pesan=produk_gagal_dihapus');
}
exit;