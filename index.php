<?php
/**
 * ============================================================================
 * SISTEMA_LANDINGS - Enrutador
 * ============================================================================
 * Este servicio solo ALOJA Y SIRVE landings. El generador (builder) vive
 * aparte, en el equipo local, y publica aqui las carpetas ya construidas.
 *
 * Rutas:
 *   /landings/<slug>/...   landing servida desde el volumen persistente y,
 *                          si aun no esta ahi, desde bundled_landings/.
 *   /                      pagina neutra de servicio.
 *   cualquier otra cosa    404.
 * ============================================================================
 */

/* Las funciones se declaran una sola vez: el runtime (FrankenPHP) reutiliza
   el proceso entre peticiones y volver a declararlas aborta con un fatal. */
if (!function_exists('copyRecursive')) {
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
            /* Solo se copia lo que falta o ha cambiado. Copiar siempre suponia
               reescribir todo bundled_landings (12 MB) en CADA peticion, y como
               cada imagen de la landing vuelve a pasar por aqui, el proceso se
               saturaba hasta agotar el tiempo maximo de ejecucion. */
            if (!file_exists($dst)
                || filesize($src) !== filesize($dst)
                || filemtime($src) > filemtime($dst)) {
                @copy($src, $dst);
            }
        }
    }
}

if (!function_exists('syncBundledLandings')) {
    /** Copia las landings empaquetadas al volumen persistente de Railway. */
    function syncBundledLandings() {
        $bundled = __DIR__ . '/bundled_landings';
        $dest    = __DIR__ . '/landings';
        if (!is_dir($bundled)) return;
        if (!is_dir($dest)) @mkdir($dest, 0777, true);

        $folders = @scandir($bundled);
        if (!$folders) return;

        foreach ($folders as $f) {
            if ($f === '.' || $f === '..') continue;
            if (is_dir($bundled . '/' . $f)) {
                copyRecursive($bundled . '/' . $f, $dest . '/' . $f);
            }
        }
    }
}

syncBundledLandings();

/* 1. Servidor interno de PHP: que sirva el los archivos reales. */
if (php_sapi_name() === 'cli-server') {
    $uriPath  = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $filePath = __DIR__ . $uriPath;
    if ($uriPath !== '/' && $uriPath !== '' && is_file($filePath)) {
        return false;
    }
}

$uri      = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
$filePath = __DIR__ . $uri;

/* 2. Archivo estatico dentro de una landing (imagenes, css, js). */
$fileToServe = null;
if ($uri !== '/' && $uri !== '' && $uri !== '/index.php') {
    if (is_file($filePath)) {
        $fileToServe = $filePath;
    } elseif (strpos($uri, '/landings/') === 0) {
        $alt = __DIR__ . '/bundled_landings/' . substr($uri, 10);
        if (is_file($alt)) $fileToServe = $alt;
    }
}

if ($fileToServe) {
    $ext = strtolower(pathinfo($fileToServe, PATHINFO_EXTENSION));

    // El manifiesto del builder lleva el token de la landing: nunca se sirve.
    if (basename($fileToServe) === 'builder.json') {
        http_response_code(404);
        exit;
    }

    $mimes = [
        'jpg'  => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png'  => 'image/png',
        'webp' => 'image/webp', 'svg'  => 'image/svg+xml', 'gif' => 'image/gif',
        'ico'  => 'image/x-icon', 'avif' => 'image/avif',
        'css'  => 'text/css', 'js' => 'application/javascript', 'json' => 'application/json',
        'woff' => 'font/woff', 'woff2' => 'font/woff2',
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

/* 3. Carpeta de landing: /landings/<slug>/
   Se excluye la raiz explicitamente: con $uri = "/" el rtrim devolvia __DIR__,
   este archivo cumplia la condicion y se incluia a si mismo en bucle infinito.
   De ahi venia el HTTP 500 de la portada. */
$trimmed = rtrim($filePath, '/');
$esRaiz  = ($trimmed === rtrim(__DIR__, '/\\'));
if (!$esRaiz && is_dir($trimmed) && file_exists($trimmed . '/index.php')) {
    require $trimmed . '/index.php';
    exit;
}

if (strpos($uri, '/landings/') === 0) {
    $alt = __DIR__ . '/bundled_landings/' . rtrim(substr($uri, 10), '/');
    if (is_dir($alt) && file_exists($alt . '/index.php')) {
        require $alt . '/index.php';
        exit;
    }
}

/* 4. Raiz: pagina neutra. Antes caia al builder, que ya no vive aqui.
      No se listan las landings a proposito: sus slugs son privados. */
if ($uri === '/' || $uri === '' || $uri === '/index.php') {
    header('Content-Type: text/html; charset=utf-8');
    header('Cache-Control: no-store');
    ?><!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Servicio de landings</title>
<style>
  body{margin:0;min-height:100vh;display:grid;place-items:center;background:#0b0d11;color:#e7e9ee;
       font:15px/1.6 -apple-system,BlinkMacSystemFont,"Segoe UI",system-ui,sans-serif}
  .c{text-align:center;padding:32px;max-width:30rem}
  .p{width:9px;height:9px;border-radius:50%;background:#22c55e;display:inline-block;
     margin-right:7px;vertical-align:middle}
  h1{font-size:17px;font-weight:650;margin:0 0 8px}
  p{margin:0;font-size:13.5px;color:#9aa2b1}
</style>
</head>
<body>
  <div class="c">
    <h1><span class="p"></span>Servicio de landings activo</h1>
    <p>Este servidor solo aloja landings publicadas. Cada una vive en su propia direccion.</p>
  </div>
</body>
</html><?php
    exit;
}

/* 5. Cualquier otra ruta: 404 honesto (antes mostraba el builder). */
http_response_code(404);
header('Content-Type: text/html; charset=utf-8');
echo '<!DOCTYPE html><html lang="es"><head><meta charset="utf-8">'
   . '<meta name="viewport" content="width=device-width,initial-scale=1">'
   . '<meta name="robots" content="noindex,nofollow"><title>No encontrado</title>'
   . '<style>body{margin:0;min-height:100vh;display:grid;place-items:center;'
   . 'background:#0b0d11;color:#e7e9ee;'
   . 'font:15px/1.6 -apple-system,BlinkMacSystemFont,"Segoe UI",system-ui,sans-serif}'
   . 'div{text-align:center;padding:32px}'
   . 'h1{font-size:17px;font-weight:650;margin:0 0 8px}'
   . 'p{margin:0;font-size:13.5px;color:#9aa2b1}</style></head>'
   . '<body><div><h1>404 &middot; No encontrado</h1>'
   . '<p>Esta direccion no corresponde a ninguna landing publicada.</p></div></body></html>';
exit;
