<?php
/* ============================================================
   💾 GUARDAR EDICIÓN VISUAL (WYSIWYG)
   Recibe el HTML modificado desde el modo de edición en vivo
   Preserva la cabecera PHP para garantizar token y carrito.
   ============================================================ */

require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$slug = preg_replace('/[^a-z0-9\-]/', '', strtolower(trim($_POST['slug'] ?? '')));
$html_content = $_POST['html_content'] ?? '';

if (empty($slug) || empty($html_content)) {
    echo json_encode(['success' => false, 'message' => 'Parámetros incompletos (slug o contenido faltante)']);
    exit;
}

$landing_file = __DIR__ . '/landings/' . $slug . '/index.php';

if (!file_exists($landing_file)) {
    echo json_encode(['success' => false, 'message' => "La landing '{$slug}' no existe"]);
    exit;
}

// Guardar copia de seguridad rápida
@copy($landing_file, __DIR__ . '/landings/' . $slug . '/index.backup.php');

// Extraer el bloque PHP original del archivo actual si existe para preservarlo
$current_code = file_get_contents($landing_file);
$php_header = '';
if (preg_match('/^(<\?php.*?\?>\s*)/s', $current_code, $matches)) {
    $php_header = $matches[1];
}

// Si el nuevo HTML no empieza con PHP pero teníamos un header PHP, reincorporarlo al inicio
if (!empty($php_header) && strpos(trim($html_content), '<?php') !== 0) {
    $final_content = $php_header . "\n" . $html_content;
} else {
    $final_content = $html_content;
}

// Escribir el nuevo archivo
$res = file_put_contents($landing_file, $final_content);

if ($res !== false) {
    echo json_encode(['success' => true, 'message' => '¡Página guardada exitosamente con todos tus cambios visuales!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error de permisos al escribir el archivo']);
}
