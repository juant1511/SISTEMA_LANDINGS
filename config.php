<?php
/**
 * CONFIGURACIÓN GLOBAL DEL PROYECTO
 * Panel 2.0 + Checkout + Banco + Telegram
 * -----------------------------------------
 * TODO EL PROYECTO LEE ESTE ARCHIVO
 * EVITA DUPLICAR CONFIGURACIONES
 */

date_default_timezone_set('America/Bogota');

/* =========================================
   HOSTS CONFIGURATION (MICROSERVICES)
========================================= */
// URL Base donde se aloja el SISTEMA_LANDINGS (usado para formar URLs absolutas de imágenes)
define('URL_LANDINGS', 'http://localhost/TRIDENTE_LANDINGS/SISTEMA_LANDINGS');

// URL Base donde se aloja el SISTEMA_PASARELA (usado para redirigir al checkout)
define('URL_PASARELA', 'http://localhost/TRIDENTE_LANDINGS/SISTEMA_PASARELA');

/* =========================================
   BASE DE DATOS — Supabase (PostgreSQL)
   API REST con clave anónima
========================================= */

require_once __DIR__ . '/supabase.php';

/* Conexión Supabase — compatible con $pdo existente */
$pdo = new SupabasePDO();

require_once __DIR__ . '/token_helper.php';


/* =========================================
   LOGIN DEL PANEL (NUEVO)
========================================= */

define("PANEL_USER", "molleja");               // Cambia si deseas otro usuario
define("PANEL_PASS", "isla0109$$");     // Cambia la contraseña


/* =========================================
   TELEGRAM – Solo para envío (no callbacks)
========================================= */

define('TG_BOT_TOKEN', '');
define('TG_CHAT_ID',   '');

function enviarTelegramGlobal($msg) {
    if (empty(TG_BOT_TOKEN) || empty(TG_CHAT_ID)) {
        error_log("Telegram: Token o Chat ID no configurados");
        return false;
    }
    
    $url = "https://api.telegram.org/bot".TG_BOT_TOKEN."/sendMessage";
    $data = [
        'chat_id' => TG_CHAT_ID,
        'text' => $msg,
        'parse_mode' => 'Markdown'
    ];
    
    $context = stream_context_create([
        'http' => [
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data),
            'timeout' => 10
        ]
    ]);
    
    $result = @file_get_contents($url, false, $context);
    
    if ($result === false) {
        error_log("Error enviando mensaje a Telegram");
        return false;
    }
    
    return true;
}


/* =========================================
   RUTAS DEL PROYECTO
========================================= */

define('PANEL_PATH',       '/panel_v2/admin/');
define('PANEL_API',        '/panel_v2/process/panel_api_bridge.php');
define('CHECKOUT_PATH',    '/checkout.php');
define('PAGO_PATH',        '/pago/');


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

?>
