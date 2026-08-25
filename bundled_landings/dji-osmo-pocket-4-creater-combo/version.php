<?php
header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

$indexPath = __DIR__ . '/index.php';
$version = file_exists($indexPath) ? md5_file($indexPath) : (string)time();

echo json_encode([
    'status'    => 'ok',
    'version'   => $version,
    'timestamp' => time()
]);
