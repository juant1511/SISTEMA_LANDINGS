<?php
// 1. Compatibilidad con servidor interno de PHP (Nixpacks / Railway / CLI-Server)
if (php_sapi_name() === 'cli-server') {
    $uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $filePath = __DIR__ . $uriPath;
    if ($uriPath !== '/' && $uriPath !== '' && is_file($filePath)) {
        return false; // PHP sirve la imagen / archivo estático directamente
    }
}

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$uri = urldecode($uri);
$filePath = __DIR__ . $uri;

// 2. Si es un archivo físico existente en disco
if ($uri !== '/' && $uri !== '' && $uri !== '/index.php' && file_exists($filePath) && !is_dir($filePath)) {
    $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    $mimes = [
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'webp' => 'image/webp',
        'svg'  => 'image/svg+xml',
        'gif'  => 'image/gif',
        'css'  => 'text/css',
        'js'   => 'application/javascript',
        'json' => 'application/json'
    ];
    
    if (isset($mimes[$ext])) {
        header('Content-Type: ' . $mimes[$ext]);
        header('Content-Length: ' . filesize($filePath));
        header('Access-Control-Allow-Origin: *');
        header('Cache-Control: public, max-age=86400');
        readfile($filePath);
        exit;
    }
    
    if ($ext === 'php') {
        require $filePath;
        exit;
    }
}

// 3. Si es una carpeta de landing (ej: /landings/dji-osmo-pocket-3/)
$trimmedUri = rtrim($filePath, '/');
if (is_dir($trimmedUri) && file_exists($trimmedUri . '/index.php')) {
    require $trimmedUri . '/index.php';
    exit;
}

// 4. Si se solicita builder directamente
if ($uri === '/builder.php' || $uri === '/builder') {
    require_once __DIR__ . '/builder.php';
    exit;
}

// 5. Cargar builder.php directamente
require_once __DIR__ . '/builder.php';
