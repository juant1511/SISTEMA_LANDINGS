<?php
/**
 * token_helper.php — Helper para generación y resolución de tokens cifrados/aleatorios
 * Utilizado por SISTEMA_LANDINGS, SISTEMA_PASARELA y SISTEMA_PANEL.
 */

if (!function_exists('generarTokenAleatorio')) {
    function generarTokenAleatorio($bytes = 16) {
        return bin2hex(random_bytes($bytes));
    }
}

if (!function_exists('obtenerOCrearTokenLanding')) {
    function obtenerOCrearTokenLanding($slug, $producto = '', $precio = 0, $pdo = null) {
        if (!$pdo) {
            global $pdo;
        }
        if (!$pdo) return false;

        $slug = preg_replace('/[^a-z0-9\-]/', '', strtolower(trim($slug)));
        if (empty($slug)) return false;

        $stmt = $pdo->prepare("SELECT * FROM landings WHERE slug = ?");
        $stmt->execute([$slug]);
        $landing = $stmt->fetch();

        if ($landing && !empty($landing['token'])) {
            return $landing['token'];
        }

        $nuevo_token = generarTokenAleatorio(16);

        if ($landing) {
            $stmt_upd = $pdo->prepare("UPDATE landings SET token = ? WHERE slug = ?");
            $stmt_upd->execute([$nuevo_token, $slug]);
        } else {
            $stmt_ins = $pdo->prepare("INSERT INTO landings (slug, producto, precio, token) VALUES (?, ?, ?, ?)");
            $stmt_ins->execute([$slug, $producto, $precio, $nuevo_token]);
        }

        return $nuevo_token;
    }
}

if (!function_exists('obtenerLandingPorToken')) {
    function obtenerLandingPorToken($token, $pdo = null) {
        if (!$pdo) {
            global $pdo;
        }
        if (!$pdo || empty($token)) return false;

        $token_clean = trim($token);
        $stmt = $pdo->prepare("SELECT * FROM landings WHERE token = ?");
        $stmt->execute([$token_clean]);
        return $stmt->fetch();
    }
}
?>
