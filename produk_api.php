<?php
header('Content-Type: application/json; charset=utf-8');

try {
    $koneksi = new PDO(
        'mysql:host=127.0.0.1;dbname=ecommerce;charset=utf8mb4',
        'root',
        '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $pernyataan = $koneksi->query(
        'SELECT id, nama_produk, harga, deskripsi FROM products ORDER BY id DESC'
    );

    $produk = $pernyataan->fetchAll(PDO::FETCH_ASSOC);
    $ekstensiGambarDiizinkan = ['jpg', 'png', 'webp'];

    foreach ($produk as &$item) {
        $item['gambar'] = '';
        foreach ($ekstensiGambarDiizinkan as $ekstensi) {
            $namaFile = $item['id'] . '.' . $ekstensi;
            if (is_file(__DIR__ . '/uploads/products/' . $namaFile)) {
                $item['gambar'] = 'uploads/products/' . $namaFile;
                break;
            }
        }
    }
    unset($item);

    echo json_encode($produk, JSON_UNESCAPED_UNICODE);
} catch (PDOException $kesalahan) {
    http_response_code(500);
    echo json_encode(['error' => 'Produk dari database belum dapat dimuat.']);
}