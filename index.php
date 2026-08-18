<?php
// Sincronización automática de landings empaquetadas hacia el volumen persistente de Railway (/app/landings)
function syncBundledLandings() {
    $bundled = __DIR__ . '/bundled_landings';
    $dest = __DIR__ . '/landings';
    if (!is_dir($bundled)) return;
    if (!is_dir($dest)) @mkdir($dest, 0777, true);

    $folders = @scandir($bundled);
    if (!$folders) return;

    foreach ($folders as $f) {
        if ($f === '.' || $f === '..') continue;
        $srcPath = $bundled . '/' . $f;
        $dstPath = $dest . '/' . $f;
        if (is_dir($srcPath)) {
            copyRecursive($srcPath, $dstPath);
        }
    }
}

function copyRecursive($src, $dst) {
    if (is_dir($src)) {
        if (!is_dir($dst)) @mkdir($dst, 0777, true);
        $files = @scandir($src);
        if ($files) {
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..') {
                    copyRecursive($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
    } else if (file_exists($src)) {
        @copy($src, $dst);
    }
}

syncBundledLandings();

// 1. Compatibilidad con servidor interno de PHP (CLI-Server)
if (php_sapi_name() === 'cli-server') {
    $uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $filePath = __DIR__ . $uriPath;
    if ($uriPath !== '/' && $uriPath !== '' && is_file($filePath)) {
        return false;
    }
}

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$uri = urldecode($uri);
$filePath = __DIR__ . $uri;

// 2. Si es un archivo físico en disco (imágenes, scripts, estilos) en landings o bundled_landings
$fileToServe = null;
if ($uri !== '/' && $uri !== '' && $uri !== '/index.php') {
    if (is_file($filePath)) {
        $fileToServe = $filePath;
    } elseif (strpos($uri, '/landings/') === 0) {
        $altPath = __DIR__ . '/bundled_landings/' . substr($uri, 10);
        if (is_file($altPath)) {
            $fileToServe = $altPath;
        }
    }
}

if ($fileToServe) {
    $ext = strtolower(pathinfo($fileToServe, PATHINFO_EXTENSION));
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
        header('Content-Length: ' . filesize($fileToServe));
        header('Access-Control-Allow-Origin: *');
        header('Cache-Control: public, max-age=86400');
        readfile($fileToServe);
        exit;
    }
    
    if ($ext === 'php') {
        require $fileToServe;
        exit;
    }
}

// 3. Si es una carpeta de landing (ej: /landings/dji-osmo-pocket-3/)
$trimmedUri = rtrim($filePath, '/');
if (is_dir($trimmedUri) && file_exists($trimmedUri . '/index.php')) {
    require $trimmedUri . '/index.php';
    exit;
}

if (strpos($uri, '/landings/') === 0) {
    $altLanding = __DIR__ . '/bundled_landings/' . rtrim(substr($uri, 10), '/');
    if (is_dir($altLanding) && file_exists($altLanding . '/index.php')) {
        require $altLanding . '/index.php';
        exit;
    }
}

// 4. Si se solicita builder directamente
if ($uri === '/builder.php' || $uri === '/builder') {
    require_once __DIR__ . '/builder.php';
    exit;
}

// 5. Cargar builder.php por defecto
require_once __DIR__ . '/builder.php';
