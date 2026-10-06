<?php
$hostDatabase = '127.0.0.1';
$namaDatabase = 'ecommerce';
$userDatabase = 'root';
$passwordDatabase = '';

$dsn = "mysql:host={$hostDatabase};dbname={$namaDatabase};charset=utf8mb4";

$koneksi = new PDO($dsn, $userDatabase, $passwordDatabase, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);