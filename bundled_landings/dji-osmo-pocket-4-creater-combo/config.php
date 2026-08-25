<?php
/**
 * config.php generado por SISTEMA TRIDENTE Builder v5.0.0
 * Landing: dji-osmo-pocket-4-creater-combo
 */
if (!defined('LANDING_SLUG'))      define('LANDING_SLUG', 'dji-osmo-pocket-4-creater-combo');
if (!defined('LANDING_TOKEN'))     define('LANDING_TOKEN', 'c384598dcd43f72d5e5d943491288cfd');
if (!defined('PRODUCTO_NOMBRE'))   define('PRODUCTO_NOMBRE', 'DJI Osmo Pocket 4 Creater Combo');
if (!defined('PRODUCTO_PRECIO'))   define('PRODUCTO_PRECIO', 1127980);
if (!defined('WHATSAPP_NUMERO'))   define('WHATSAPP_NUMERO', '');

/**
 * CONFIGURACIÓN GLOBAL DEL PROYECTO
 * Panel 2.0 + Checkout + Banco + Telegram
 * -----------------------------------------
 * TODO EL PROYECTO LEE ESTE ARCHIVO
 * EVITA DUPLICAR CONFIGURACIONES
 */

date_default_timezone_set('America/Bogota');

// Sincronización automática de código de landings desde bundled_landings hacia el volumen persistente de Railway
(function() {
    $bundled = __DIR__ . '/bundled_landings';
    $dest = __DIR__ . '/landings';
    if (!is_dir($bundled)) return;

    $script = $_SERVER['SCRIPT_FILENAME'] ?? '';
    if (strpos($script, 'landings') !== false && strpos($script, 'bundled_landings') === false) {
        $slug = basename(dirname($script));
        $bundledIndex = $bundled . '/' . $slug . '/index.php';
        if (file_exists($bundledIndex) && file_exists($script)) {
            $currentSize = filesize($script);
            $bundledSize = filesize($bundledIndex);
            if ($currentSize !== $bundledSize || filemtime($bundledIndex) > filemtime($script)) {
                @copy($bundledIndex, $script);
                header("Refresh:0");
                exit;
            }
        }
    }
})();

/* =========================================
   HOSTS CONFIGURATION (MICROSERVICES)
========================================= */
// URL Base donde se aloja el SISTEMA_LANDINGS (usado para formar URLs absolutas de imágenes)
define('URL_LANDINGS', 'https://sistemalandings-production.up.railway.app');

// URL Base donde se aloja el SISTEMA_PASARELA (Checkout normal)
define('URL_PASARELA_CHECKOUT', 'https://pago-bold.com');
define('URL_PASARELA', 'https://pago-bold.com');

// URL Base donde se aloja el entorno MercadoLibre
define('URL_PASARELA_MERCADOLIBRE', 'https://pagos-mercadopago.com');

/* =========================================
   BASE DE DATOS — Supabase (PostgreSQL)
   API REST con clave anónima
========================================= */

require_once __DIR__ . '/supabase.php';

/* Conexión Supabase — compatible con $pdo existente */
$pdo = new SupabasePDO();

require_once __DIR__ . '/token_helper.php';

/* =========================================
   FUNCIONES UTILES GLOBALMENTE
========================================= */

function jsonResponse($arr) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($arr, JSON_UNESCAPED_UNICODE);
    exit;
}

function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

function getSupabaseGalleryImages($bucketName, $folderPrefix = '') {
    $url = SUPABASE_URL . '/storage/v1/object/list/' . $bucketName;
    $body = json_encode([
        'prefix' => $folderPrefix,
        'limit' => 50,
        'offset' => 0,
        'sortBy' => ['column' => 'created_at', 'order' => 'asc']
    ]);
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'apikey: ' . SUPABASE_KEY,
        'Authorization: Bearer ' . SUPABASE_KEY,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $urls = [];
    if ($httpCode >= 200 && $httpCode < 300) {
        $items = json_decode($response, true);
        if (is_array($items)) {
            foreach ($items as $item) {
                if (empty($item['name']) || $item['name'] === '.emptyFolderPlaceholder') continue;
                // Formar URL publica
                $urls[] = SUPABASE_URL . '/storage/v1/object/public/' . $bucketName . '/' . (!empty($folderPrefix) ? $folderPrefix . '/' : '') . $item['name'];
            }
        }
    }
    return $urls;
}
