<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function aman(string $nilai): string
{
    return htmlspecialchars($nilai, ENT_QUOTES, 'UTF-8');
}

function token_csrf(): string
{
    if (!isset($_SESSION['token_csrf'])) {
        $_SESSION['token_csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['token_csrf'];
}

function csrf_valid(): bool
{
    return isset($_POST['token_csrf'], $_SESSION['token_csrf'])
        && hash_equals($_SESSION['token_csrf'], (string) $_POST['token_csrf']);
}

function gambar_produk(int $id): string
{
    foreach (['jpg', 'png', 'webp'] as $ekstensi) {
        $namaFile = $id . '.' . $ekstensi;
        if (is_file(__DIR__ . '/uploads/products/' . $namaFile)) {
            return 'uploads/products/' . $namaFile;
        }
    }

    return '';
}

function hapus_gambar_produk(int $id): void
{
    foreach (['jpg', 'png', 'webp'] as $ekstensi) {
        $file = __DIR__ . '/uploads/products/' . $id . '.' . $ekstensi;
        if (is_file($file)) {
            unlink($file);
        }
    }
}