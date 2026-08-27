<?php
header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
require_once __DIR__ . '/config.php';
$landing_slug  = 'logitech-g-pro-x2-superstrike';
$nombre_marca  = 'Logitech';
try {
    if (isset($pdo)) {
        $stmt_m = $pdo->prepare("SELECT marca FROM landings WHERE slug = ?");
        $stmt_m->execute([$landing_slug]);
        $row_m = $stmt_m->fetch(PDO::FETCH_ASSOC);
        if (!empty($row_m['marca'])) { $nombre_marca = $row_m['marca']; }
    }
} catch (Exception $e) {}
$landing_token = '35fa348dd87249a49dd7b9ed28d6ca9f';
$precio_num    = 249060;
$precio_fmt    = '249.060';
/* Cuantas unidades se compraron el mes pasado. Lo sortea el exportador al
   generar y queda fijo aqui: si cambiara en cada carga no seria creible. */
$compras_mes   = '9 K+';
$es_modo_edicion = isset($_GET['modo_edicion']) && $_GET['modo_edicion'] == '1';
$app_version   = file_exists(__FILE__) ? md5_file(__FILE__) : (string)time();
/* ─── URLs de las pasarelas ───────────────────────────────────────────────
   Se parte de las constantes de config.php y, si la base de datos tiene un
   valor mas reciente (columnas url_bold / url_mercado), se usa ese. Asi, al
   cambiar el dominio de una pasarela en el builder, esta landing lo toma en
   la siguiente carga sin necesidad de regenerarla ni volver a subirla.
   La RUTA no cambia nunca: solo la base. */
$url_pasarela_bold = defined('URL_PASARELA_CHECKOUT') ? URL_PASARELA_CHECKOUT : '';
$url_pasarela_meli = defined('URL_PASARELA_MERCADOLIBRE') ? URL_PASARELA_MERCADOLIBRE : '';
try {
    if (isset($pdo)) {
        $stmt_u = $pdo->prepare("SELECT url_bold, url_mercado FROM landings WHERE slug = ?");
        $stmt_u->execute([$landing_slug]);
        $row_u = $stmt_u->fetch(PDO::FETCH_ASSOC);
        if (!empty($row_u['url_bold']))    { $url_pasarela_bold = rtrim((string)$row_u['url_bold'], '/'); }
        if (!empty($row_u['url_mercado'])) { $url_pasarela_meli = rtrim((string)$row_u['url_mercado'], '/'); }
    }
} catch (Exception $e) { /* sin base de datos se usan las constantes locales */ }

// ─── Cargar Productos de Otras Landings o Productos Demo ───
$otros_productos = [];
try {
    if (isset($pdo)) {
        $stmt = $pdo->prepare("SELECT slug, producto, precio, imagenes FROM landings WHERE slug != ? ORDER BY id DESC LIMIT 12");
        $stmt->execute([$landing_slug]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $r) {
            $other_slug = $r['slug'];
            $imgs = is_array($r['imagenes']) ? $r['imagenes'] : (json_decode($r['imagenes'] ?? '{}', true) ?: []);
            $raw_img = $imgs['img_1'] ?? ($imgs['producto'] ?? ($imgs['desktop'] ?? 'img/img_1.webp'));
            
            $final_img = (strpos($raw_img, 'http') === 0) ? $raw_img : "../{$other_slug}/" . ltrim($raw_img, '/');
            $otros_productos[] = [
                'slug'   => $other_slug,
                'nombre' => $r['producto'],
                'precio' => '$ ' . number_format($r['precio'], 0, ',', '.'),
                'url'    => "../{$other_slug}/",
                'img'    => $final_img
            ];
            if (count($otros_productos) >= 6) break;
        }
    }
} catch (Exception $e) {}

// Fallback con productos demo de alta calidad para previsualización
if (empty($otros_productos)) {
    $otros_productos = array (
  0 => 
  array (
    'nombre' => 'Logitech Kit de accesorios compatible - Logitech G PRO X2 SUPERSTRIKE',
    'precio' => '$ 54.793',
    'url' => '#',
    'img' => 'img/img_1.jpg',
  ),
  1 => 
  array (
    'nombre' => 'Logitech Estuche de transporte premium - Logitech G PRO X2 SUPERSTRIKE',
    'precio' => '$ 37.359',
    'url' => '#',
    'img' => 'img/img_2.jpg',
  ),
  2 => 
  array (
    'nombre' => 'Logitech Garantia extendida 12 meses - Logitech G PRO X2 SUPERSTRIKE',
    'precio' => '$ 29.887',
    'url' => '#',
    'img' => 'img/img_3.jpg',
  ),
  3 => 
  array (
    'nombre' => 'Logitech Pack x2 con descuento - Logitech G PRO X2 SUPERSTRIKE',
    'precio' => '$ 435.855',
    'url' => '#',
    'img' => 'img/img_4.jpg',
  ),
  4 => 
  array (
    'nombre' => 'Logitech Repuesto original - Logitech G PRO X2 SUPERSTRIKE',
    'precio' => '$ 44.831',
    'url' => '#',
    'img' => 'img/img_5.jpg',
  ),
  5 => 
  array (
    'nombre' => 'Logitech Combo completo edicion limitada - Logitech G PRO X2 SUPERSTRIKE',
    'precio' => '$ 348.684',
    'url' => '#',
    'img' => 'img/img_6.jpg',
  ),
);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="description" content="Diseñado con profesionales, diseñado para ganar: diseñado junto con los mejores atletas de deportes electrónicos del mundo, el mouse inalámbrico Logitech G">
    <title><?= htmlspecialchars('Logitech G PRO X2 SUPERSTRIKE') ?></title>
    
    <!-- FAVICON / NAVICON -->
    <?php if (file_exists(__DIR__ . '/logo.svg')): ?>
        <link rel="icon" type="image/svg+xml" href="logo.svg">
        <link rel="apple-touch-icon" href="logo.svg">
    <?php elseif (file_exists(__DIR__ . '/logo.webp')): ?>
        <link rel="icon" type="image/webp" href="logo.webp">
        <link rel="shortcut icon" type="image/webp" href="logo.webp">
        <link rel="apple-touch-icon" href="logo.webp">
    <?php elseif (file_exists(__DIR__ . '/logo.png')): ?>
        <link rel="icon" type="image/png" href="logo.png">
        <link rel="shortcut icon" type="image/png" href="logo.png">
        <link rel="apple-touch-icon" href="logo.png">
    <?php endif; ?>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://fonts.cdnfonts.com">
    <link href="https://fonts.cdnfonts.com/css/sf-pro-display" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&family=Inter:wght@400;500;600;700;900&family=Nunito+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@dotlottie/player-component@latest/dist/dotlottie-player.mjs" type="module"></script>
    
    <style>
        :root {
            --primary: #1a1a1a;
            --primary-hover: #434343;
            --accent: #666666;
            --btn-bg: #1a1a1a;
            --topbar-bg: #1a1a1a;
            --body-bg: #ffffff;
            --card-bg: #ffffff;
            --text-main: #1d1d1f;
            --text-muted: #86868b;
            --border-color: #d2d2d7;
            --border-light: #e5e5ea;
            --star-color: #1a1a1a;
            --font-heading: 'SF Pro Display', -apple-system, BlinkMacSystemFont, 'SF Pro', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            --font-body: 'SF Pro Text', 'SF Pro Display', -apple-system, BlinkMacSystemFont, 'SF Pro', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            --font-ml: 'Proxima Nova', 'Nunito Sans', -apple-system, BlinkMacSystemFont, 'Roboto', Arial, sans-serif;
        }

        /* ─── RESET Y CONTROL ESTRICTO DE OVERFLOW HORIZONTAL ─── */
        *, *::before, *::after {
            box-sizing: border-box !important;
        }

        html, body {
            margin: 0;
            padding: 0;
            width: 100% !important;
            max-width: 100% !important;
            overflow-x: hidden !important;
            position: relative;
            font-family: var(--font-body);
            background-color: var(--body-bg);
            color: var(--text-main);
            line-height: 1.47059;
            letter-spacing: -0.011em;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }


        /* ─── TOPBAR ESTÁTICO (ENVÍOS A TODO COLOMBIA) ─── */
        .top-announcement {
            background-color: var(--topbar-bg, #000000);
            color: #ffffff;
            font-family: var(--font-heading);
            font-size: 11px;
            font-weight: 700;
            padding: 7px 12px;
            line-height: 1.2;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            box-sizing: border-box;
            user-select: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1001;
            height: 36px;
        }
        /* Spacer para compensar el top-announcement + navbar fijos */
        body {
            padding-top: 108px; /* 36px topbar + 72px navbar */
        }
        @media (min-width: 992px) {
            body { padding-top: 118px; } /* 36px topbar + 82px navbar desktop */
        }

        /* ─── NAVBAR CON LOGO CENTRADO (APPLE FROSTED GLASS Y SCROLL DINÁMICO) ─── */
        .navbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 20px;
            background-color: rgba(255, 255, 255, 0.92);
            backdrop-filter: saturate(180%) blur(20px);
            -webkit-backdrop-filter: saturate(180%) blur(20px);
            border-bottom: 1px solid #d2d2d7;
            position: fixed;
            top: 36px; /* debajo del top-announcement bar */
            left: 0;
            right: 0;
            z-index: 1000;
            min-height: 70px;
            width: 100%;
            box-sizing: border-box;
            transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), background-color 0.2s ease;
            will-change: transform;
        }
        .navbar.nav-hidden {
            transform: translateY(-110%);
        }
        .nav-left { width: 44px; display: flex; align-items: center; justify-content: flex-start; flex-shrink: 0; }
        .nav-hamburger-btn {
            background: none;
            border: none;
            cursor: pointer;
            color: #1d1d1f;
            padding: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            transition: background 0.15s ease, transform 0.15s ease;
        }
        .nav-hamburger-btn:hover {
            background: rgba(0, 0, 0, 0.05);
        }
        .nav-hamburger-btn:active {
            transform: scale(0.92);
        }
        .nav-center-logo { flex: 1; display: flex; justify-content: center; align-items: center; text-align: center; }
        .brand-logo-img { height: 56px; max-height: 60px; max-width: 220px; width: auto; object-fit: contain; transition: transform 0.2s ease; display: block; margin: 0 auto; }
        .brand-logo-img:hover { transform: scale(1.02); }
        .brand-logo-text { font-family: var(--font-heading); font-size: 26px; font-weight: 700; letter-spacing: -0.02em; color: var(--text-main); text-transform: uppercase; text-decoration: none; display: inline-block; }
        .nav-right { width: 44px; display: flex; justify-content: flex-end; align-items: center; flex-shrink: 0; }
        .cart-trigger { position: relative; background: none; border: none; cursor: pointer; color: #1d1d1f; padding: 6px; }
        .cart-badge-count { position: absolute; top: -2px; right: -4px; background-color: var(--primary); color: #ffffff; font-size: 10px; font-weight: 700; width: 18px; height: 18px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid #ffffff; }
        @media (min-width: 768px) {
            .navbar { padding: 12px 28px; min-height: 80px; }
            .brand-logo-img { height: 68px; max-height: 72px; max-width: 280px; }
            .brand-logo-text { font-size: 30px; letter-spacing: -0.02em; }
        }

        /* ─── DRAWER / MENÚ HAMBURGUESA DESPLEGABLE ─── */
        .nav-menu-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            z-index: 99999;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s cubic-bezier(0.16, 1, 0.3, 1), visibility 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .nav-menu-overlay.open {
            opacity: 1;
            visibility: visible;
        }
        .nav-menu-drawer {
            position: absolute;
            top: 0;
            left: 0;
            bottom: 0;
            width: 82%;
            max-width: 320px;
            background: #ffffff;
            box-shadow: 4px 0 30px rgba(0, 0, 0, 0.18);
            display: flex;
            flex-direction: column;
            transform: translateX(-100%);
            transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .nav-menu-overlay.open .nav-menu-drawer {
            transform: translateX(0);
        }
        .nav-menu-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 20px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.08);
        }
        .nav-menu-brand-text {
            font-family: var(--font-heading);
            font-size: 17px;
            font-weight: 700;
            color: #1d1d1f;
            letter-spacing: -0.01em;
            text-transform: uppercase;
        }
        .nav-menu-close-btn {
            background: rgba(0, 0, 0, 0.05);
            border: none;
            font-size: 16px;
            font-weight: 700;
            color: #1d1d1f;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.15s ease;
        }
        .nav-menu-close-btn:hover {
            background: rgba(0, 0, 0, 0.1);
        }
        .nav-menu-links {
            display: flex;
            flex-direction: column;
            padding: 20px 14px;
            gap: 8px;
            flex: 1;
            overflow-y: auto;
        }
        .nav-menu-link {
            display: block;
            padding: 14px 18px;
            color: #1d1d1f;
            text-decoration: none;
            font-family: var(--font-heading);
            font-size: 15.5px;
            font-weight: 600;
            letter-spacing: -0.01em;
            border-radius: 12px;
            transition: background 0.15s ease, color 0.15s ease;
        }
        .nav-menu-link:hover, .nav-menu-link:active {
            background: #f5f5f7;
            color: var(--primary);
        }

        /* ─── SEPARACIÓN GRIS CLARO ENTRE NAVBAR Y GALERÍA ─── */
        .navbar-gallery-spacer {
            width: 100%;
            background-color: #ededed;
            display: block;
            border-bottom: 1px solid #e0e0e0;
            padding: 11px 20px;
            box-sizing: border-box;
        }
        /* El titulo del producto vive dentro de esta banda gris, alineado a
           izquierda con la galeria que viene justo debajo. */
        .navbar-gallery-spacer .product-title {
            margin: 0;
            padding: 0;
        }
        /* Titulo a la izquierda y calificacion a la derecha, como en la ficha
           de MercadoLibre. La calificacion no se encoge ni parte de linea. */
        .spacer-head {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 16px;
        }
        .spacer-head .product-title { flex: 1 1 auto; min-width: 0; }
        .spacer-head .rating-row { flex: 0 0 auto; margin-bottom: 0; }
        /* Mismas estrellas que el modulo de opiniones (#de7921), no el amarillo
           general: se quieren identicas a las de las resenas. */
        .spacer-head .stars-container { color: #de7921; }
        .spacer-head .rating-number { font-family: var(--font-ml); font-size: 13px; font-weight: 700; color: #1d1d1f; }
        .spacer-head .reviews-count { font-family: var(--font-ml); font-size: 13px; color: var(--text-muted); }
        /* "N K+ comprados el mes pasado": el dato en negrita, el resto apagado. */
        .bought-month {
            font-family: var(--font-ml);
            font-size: 13.5px;
            line-height: 1.35;
            color: var(--text-muted);
            margin-top: 6px;
        }
        .bought-month strong { font-weight: 800; color: #1d1d1f; }
        @media (max-width: 600px) {
            /* En pantallas estrechas el titulo largo aplasta la calificacion:
               mejor que caiga debajo y a la izquierda. */
            .spacer-head { flex-direction: column; align-items: flex-start; gap: 7px; }
        }
        @media (min-width: 992px) {
            .navbar-gallery-spacer {
                padding: 13px 36px;
                background-color: #ededed;
                border-bottom: 1px solid #e0e0e0;
            }
        }

        /* ─── PÁGINA FULL WIDTH (SIN CONTAINER ESTRECHO) ─── */
        .landing-container {
            max-width: 100% !important;
            width: 100% !important;
            margin: 0;
            padding: 0 0 30px 0;
            box-sizing: border-box;
            overflow-x: hidden;
        }
        @media (min-width: 992px) {
            .landing-container {
                padding: 20px 36px 40px 36px;
            }
        }

        .product-grid-layout {
            display: flex;
            flex-direction: column;
            gap: 20px;
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
            background: transparent;
            border-radius: 0;
            padding: 0;
            box-shadow: none;
            border: none;
            margin: 0;
        }
        @media (min-width: 992px) {
            .product-grid-layout {
                display: grid;
                grid-template-columns: 1.1fr 1fr;
                gap: 48px;
                align-items: start;
                max-width: 100%;
                padding: 0;
            }
        }

        /* ─── GALLERY SECTION CON SLIDE Y PUNTICOS INDICADORES ─── */
        .gallery-wrapper-desktop {
            display: flex;
            flex-direction: column;
            gap: 0;
            width: 100%;
            max-width: 100%;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        .gallery-slider-container {
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            margin: 0;
            padding: 0;
        }
        .main-image-wrap {
            order: 1;
            width: 100%;
            aspect-ratio: 1 / 1;
            background-color: #fbfbfd;
            border-radius: 0;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            box-shadow: none;
            border: none;
            cursor: pointer;
            box-sizing: border-box;
            touch-action: pan-y pinch-zoom;
            margin: 0;
            padding: 0;
        }

        /* Miniaturas ocultas por defecto en móvil */
        .gallery-thumbnails-strip {
            display: none;
        }

        /* ─── DESKTOP: layout con thumbnails a la izquierda ─── */
        @media (min-width: 992px) {
            .gallery-wrapper-desktop {
                flex-direction: row;
                border-top: none;
                padding: 0;
                align-items: flex-start;
                gap: 12px;
                max-width: 100%;
                margin: 0;
            }
            .gallery-thumbnails-strip {
                display: flex;
                flex-direction: column;
                gap: 8px;
                width: 76px;
                flex-shrink: 0;
                padding: 4px 0;
            }
            .gallery-thumb-item {
                width: 74px;
                height: 74px;
                border-radius: 10px;
                overflow: hidden;
                cursor: pointer;
                border: 2px solid transparent;
                transition: border-color 0.2s ease, box-shadow 0.2s ease, opacity 0.2s ease;
                flex-shrink: 0;
                background: #f5f5f7;
                opacity: 0.65;
            }
            .gallery-thumb-item:hover {
                border-color: #86868b;
                opacity: 0.9;
            }
            .gallery-thumb-item.active {
                border-color: #1d1d1f;
                box-shadow: 0 0 0 1px #1d1d1f;
                opacity: 1;
            }
            .gallery-thumb-item img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                display: block;
            }
            .gallery-slider-container {
                flex: 1;
                min-width: 0;
            }
            .main-image-wrap {
                border-radius: 18px;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
                border: 1px solid rgba(0, 0, 0, 0.06);
            }
        }

        .gallery-dots-indicator {
            order: 2;
            display: flex !important;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin: 14px 0 6px 0;
            width: 100%;
            padding: 4px 0;
        }
        @media (min-width: 992px) {
            .gallery-dots-indicator {
                display: none !important;
            }
        }
        .gallery-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #d2d2d7;
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.2, 0.8, 0.2, 1);
        }
        .gallery-dot.active {
            background: #59595e;
            width: 24px;
            border-radius: 999px;
        }
        .gallery-dot:hover:not(.active) {
            background: #86868b;
        }
        .main-image-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: opacity 0.5s ease;
            user-select: none;
            -webkit-user-drag: none;
            display: block;
        }

        /* ─── BOTONES DE MODO VISUALIZACIÓN (LIGHTBOX) ─── */
        .lightbox-nav-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 48px;
            height: 48px;
            background: rgba(255, 255, 255, 0.9) !important;
            border: none !important;
            border-radius: 50%;
            color: #1d1d1f !important;
            font-size: 20px;
            font-weight: 700;
            cursor: pointer;
            display: flex !important;
            align-items: center;
            justify-content: center;
            z-index: 20;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3) !important;
            opacity: 1 !important;
            visibility: visible !important;
            transition: transform 0.15s ease;
        }
        .lightbox-nav-btn:active {
            transform: translateY(-50%) scale(0.92);
        }
        .lightbox-nav-btn.prev { left: 14px; }
        .lightbox-nav-btn.next { right: 14px; }
        @media (min-width: 992px) {
            .lightbox-nav-btn.prev { left: 30px; }
            .lightbox-nav-btn.next { right: 30px; }
        }

        @media (min-width: 1025px) and (hover: hover) {
            .main-image-wrap:hover img {
                transform: scale(1.03);
            }
        }
        @media (max-width: 1024px) {
            .main-image-wrap img {
                transform: none !important;
            }
        }

        .thumbnails-strip { order: 2; display: flex; gap: 10px; overflow-x: auto; padding-bottom: 6px; scrollbar-width: none; -webkit-overflow-scrolling: touch; }
        .thumbnails-strip::-webkit-scrollbar { display: none; }
        .thumb-item { flex: 0 0 68px; height: 68px; border-radius: 12px; overflow: hidden; border: 1.5px solid var(--border-light); cursor: pointer; transition: all 0.2s ease; background: #fbfbfd; }
        .thumb-item.active { border-color: var(--primary); box-shadow: 0 0 0 1px var(--primary); }
        .thumb-item img { width: 100%; height: 100%; object-fit: cover; }

        /* ─── PRODUCT INFORMATION (APPLE MINIMALIST) ─── */
        .product-info { padding: 0 20px; box-sizing: border-box; }
        @media (min-width: 992px) {
            .product-info { padding: 0; }
        }
        .product-title {
            /* Misma familia que el banner de MercadoLibre (Proxima Nova, con
               Nunito Sans de reemplazo: Proxima no esta en Google Fonts). */
            font-family: var(--font-ml);
            font-size: 24px;
            font-weight: 700;
            color: #1d1d1f;
            line-height: 1.25;
            letter-spacing: -0.01em;
            margin-bottom: 10px;
        }
        .rating-row { display: flex; align-items: center; gap: 6px; margin-bottom: 14px; }
        .stars-container { display: flex; color: var(--star-color); font-size: 14px; letter-spacing: 1px; }
        .rating-number { font-size: 13px; font-weight: 600; color: #1d1d1f; }
        .reviews-count { font-size: 13px; color: var(--text-muted); }

        .price-row { display: flex; align-items: baseline; gap: 12px; margin-bottom: 8px; flex-wrap: wrap; }
        .current-price {
            font-family: var(--font-heading);
            font-size: 30px;
            font-weight: 700;
            color: #1d1d1f;
            letter-spacing: -0.025em;
        }
        .old-price {
            font-size: 16px;
            color: var(--text-muted);
            text-decoration: line-through;
            font-weight: 500;
        }
        .discount-pill {
            background: #e6f7ed;
            color: #00a650;
            font-size: 12px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 980px;
            letter-spacing: -0.01em;
        }

        /* ─── BADGE DE ÚLTIMAS UNIDADES (ESTILO NARANJA) ─── */
        /* ─── PUNTOS COLOMBIA (bajo el precio) ───
           Acumulacion oficial: 1 punto por cada $700 de compra. */
        .puntos-colombia-row {
            display: flex;
            align-items: center;
            gap: 7px;
            margin-bottom: 14px;
            font-family: var(--font-heading);
            font-size: 13px;
            line-height: 1.3;
            color: #662D91;
            user-select: none;
        }
        .puntos-colombia-row .pc-mark { flex-shrink: 0; display: block; border-radius: 4px; }
        .puntos-colombia-row .pc-text { font-weight: 500; letter-spacing: -0.01em; }
        .puntos-colombia-row .pc-text b { font-weight: 800; }
        .puntos-colombia-row .pc-info {
            flex-shrink: 0; width: 14px; height: 14px; color: #662D91; opacity: .55;
            cursor: help; transition: opacity .15s ease;
        }
        .puntos-colombia-row .pc-info:hover { opacity: 1; }

        @media (max-width: 480px) {
            .puntos-colombia-row { font-size: 12.5px; gap: 6px; }
        }
        .current-price {
            font-family: var(--font-heading);
            font-size: 30px;
            font-weight: 700;
            color: #1d1d1f;
            letter-spacing: -0.025em;
        }
        .old-price {
            font-size: 16px;
            color: var(--text-muted);
            text-decoration: line-through;
            font-weight: 500;
        }
        .discount-pill {
            background: #e6f7ed;
            color: #00a650;
            font-size: 12px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 980px;
            letter-spacing: -0.01em;
        }

        /* ─── BADGE DE ÚLTIMAS UNIDADES (ESTILO NARANJA) ─── */
        .stock-urgency-badge {
            display: inline-block;
            background-color: #f76b1c;
            color: #ffffff;
            font-size: 11px;
            font-weight: 800;
            line-height: 1.2;
            padding: 4px 8px;
            border-radius: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
            width: fit-content;
            font-family: var(--font-heading);
            user-select: none;
        }

        /* ─── CAJA DE ENVÍO URGENTE Y CONTADOR (ESTILO MERCADOLIBRE / APPLE) ─── */
        .apple-shipping-urgency-box {
            background: #fbfbfd;
            border: 1px solid rgba(0, 166, 80, 0.25);
            border-radius: 12px;
            padding: 12px 14px;
            margin-bottom: 18px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .shipping-lead-row {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .shipping-flash-icon {
            flex-shrink: 0;
            fill: #f59e0b;
            stroke: #f59e0b;
        }
        .shipping-lead-text {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        .shipping-badge-highlight {
            color: #00a650;
            font-size: 14.5px;
            font-weight: 600;
            letter-spacing: -0.01em;
        }
        .shipping-badge-highlight b {
            font-weight: 800;
        }
        .shipping-timer-subtext {
            font-size: 12.5px;
            color: #6e6e73;
            font-weight: 400;
        }
        .shipping-countdown-val {
            color: #d9383a;
            font-weight: 700;
            letter-spacing: 0.2px;
        }

        .variant-block { margin-bottom: 16px; border-top: 1px solid var(--border-light); padding-top: 14px; }
        .variant-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; font-size: 13px; }
        .variant-label { font-weight: 600; color: #1d1d1f; letter-spacing: -0.01em; }
        .variant-label span { font-weight: 400; color: var(--text-muted); }
        .swatches-row { display: flex; gap: 12px; align-items: center; }
        .swatch-circle { width: 34px; height: 34px; border-radius: 50%; cursor: pointer; position: relative; border: 2px solid transparent; box-shadow: inset 0 0 0 1px rgba(0,0,0,0.12); transition: all 0.2s ease; }
        .swatch-circle.active { border-color: var(--primary); transform: scale(1.08); }

        .size-block { margin-bottom: 20px; }
        .size-pills-row { display: flex; gap: 10px; }
        .size-pill {
            padding: 8px 18px;
            border: 1.5px solid var(--primary);
            background: rgba(0, 113, 227, 0.05);
            color: var(--primary);
            border-radius: 980px;
            font-size: 13px;
            font-weight: 600;
            font-family: var(--font-body);
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .size-pill:hover {
            background: rgba(0, 113, 227, 0.1);
        }

        .desktop-action-row { display: none; gap: 12px; align-items: center; margin-bottom: 22px; }
        .qty-controls-desktop {
            display: flex;
            align-items: center;
            border: 1px solid var(--border-color);
            border-radius: 980px;
            overflow: hidden;
            height: 48px;
            background: #f5f5f7;
        }
        .qty-btn-desktop {
            background: transparent;
            border: none;
            width: 40px;
            height: 100%;
            font-size: 17px;
            font-weight: 600;
            cursor: pointer;
            color: #1d1d1f;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.15s;
        }
        .qty-btn-desktop:hover { background: #e5e5ea; }
        .qty-val-desktop { width: 40px; text-align: center; font-size: 14px; font-weight: 700; color: #1d1d1f; }
        .btn-add-desktop {
            flex: 1;
            height: 48px;
            background-color: var(--btn-bg, #0071e3);
            color: #ffffff;
            border: none;
            border-radius: 980px;
            font-family: var(--font-heading);
            font-size: 15px;
            font-weight: 600;
            letter-spacing: -0.01em;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 14px rgba(0, 113, 227, 0.28);
        }
        .btn-add-desktop:hover {
            background-color: var(--primary-hover, #0077ed);
            transform: scale(1.015);
            box-shadow: 0 6px 20px rgba(0, 113, 227, 0.38);
        }

        .accordion-item { border-top: 1px solid var(--border-light); background: transparent; }
        .accordion-item:last-of-type { border-bottom: 1px solid var(--border-light); }
        .accordion-header {
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            background: none;
            border: none;
            font-family: var(--font-heading);
            font-size: 14.5px;
            font-weight: 600;
            color: #1d1d1f;
            cursor: pointer;
            text-align: left;
            letter-spacing: -0.01em;
        }
        .accordion-body { display: none; padding-bottom: 15px; font-size: 13.5px; color: #424245; line-height: 1.55; }
        .accordion-body.open { display: block; }

        .secure-trust-box {
            background: #fbfbfd;
            border: 1px solid var(--border-light);
            border-radius: 12px;
            padding: 14px 18px;
            margin-top: 20px;
        }
        .secure-trust-header { display: flex; align-items: center; gap: 8px; font-family: var(--font-heading); font-size: 13.5px; font-weight: 700; color: #1d1d1f; margin-bottom: 8px; }
        .secure-trust-header svg { flex-shrink: 0; }
        .secure-trust-list { list-style: none; display: flex; flex-direction: column; gap: 6px; padding: 0; margin: 0; }
        .secure-trust-list li { display: flex; align-items: flex-start; gap: 8px; font-size: 12.5px; color: #6e6e73; line-height: 1.4; }
        .secure-trust-list .check-icon { color: #00a650; font-weight: 800; font-size: 13px; line-height: 1.3; }

        /* ─── BANNER MERCADOLIBRE ───
           Specs tomadas del design system real de Mercado Libre (Andes):
           boton radio 6px / alto 48px / peso 600, azul #3483fa, texto rgba(0,0,0,.9) y .55 */
        .ml-promo-banner-wrap { display: block; width: 100%; max-width: 100%; margin: 25px 0 10px 0; background: #ffe600; box-sizing: border-box; cursor: pointer; overflow-x: hidden; text-decoration: none; color: inherit; transition: background-color 0.15s ease; }
        .ml-promo-banner-wrap:hover { background: #ffea1a; }
        .ml-promo-banner-wrap:focus-visible { outline: 3px solid #2d3277; outline-offset: -3px; }

        /* El amarillo llega a los bordes; el contenido se topa a 1200px como en Mercado Libre */
        .ml-banner-inner { max-width: 1280px; margin: 0 auto; padding: 18px 24px; display: flex; align-items: center; justify-content: space-between; gap: 20px; width: 100%; box-sizing: border-box; }

        .ml-banner-left { display: flex; align-items: center; gap: 18px; flex-shrink: 0; }
        .ml-logo-img { height: 72px; max-width: 240px; width: auto; object-fit: contain; display: block; }
        .ml-banner-divider { width: 1px; height: 62px; background: rgba(0, 0, 0, 0.12); flex-shrink: 0; }

        .ml-banner-center { flex: 1 1 auto; min-width: 0; display: flex; flex-direction: column; align-items: flex-start; justify-content: center; gap: 6px; overflow: hidden; padding-right: 8px; }
        .ml-kicker { display: inline-flex; align-items: center; gap: 6px; max-width: 100%; background: #ffffff; border-radius: 4px; padding: 5px 10px 5px 8px; font-family: var(--font-ml); font-weight: 700; font-size: 11px; letter-spacing: 0.6px; text-transform: uppercase; color: #2d3277; white-space: nowrap; overflow: hidden; }
        .ml-kicker svg { width: 14px; height: 14px; flex-shrink: 0; color: #3483fa; }
        .ml-product-name { max-width: 100%; font-family: var(--font-ml); font-weight: 600; font-size: 16px; line-height: 1.25; color: rgba(0, 0, 0, 0.9); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .ml-trust-line { max-width: 100%; font-family: var(--font-ml); font-weight: 400; font-size: 13px; line-height: 1.3; color: rgba(0, 0, 0, 0.55); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        .ml-banner-right { flex-shrink: 0; display: flex; align-items: center; gap: 18px; }

        /* Imagen "ENVÍO GRATIS EN TU PRIMERA COMPRA" */
        .ml-shipping-img { height: 40px; width: auto; max-width: 384px; object-fit: contain; display: block; }

        /* Replica en CSS del mismo diseno, mientras no exista el archivo de imagen */
        .ml-ship-pill { display: inline-flex; align-items: center; background: #ffffff; border-radius: 999px; padding: 4px; font-family: var(--font-ml); }
        .ml-ship-pill .pill-dark { background: #2d3277; color: #ffffff; border-radius: 999px; padding: 8px 17px; font-size: 12.5px; font-weight: 800; letter-spacing: 0.3px; white-space: nowrap; }
        .ml-ship-pill .pill-white { color: #2d3277; padding: 0 17px 0 13px; font-size: 12.5px; font-weight: 600; letter-spacing: 0.3px; white-space: nowrap; }
        .ml-ship-pill .pill-white b { font-weight: 800; }

        /* CTA con las specs exactas del boton "loud" de Andes */
        .ml-cta { display: inline-flex; align-items: center; justify-content: center; height: 48px; padding: 0 24px; background: #3483fa; color: #ffffff; border-radius: 6px; font-family: var(--font-ml); font-size: 16px; font-weight: 600; line-height: 48px; white-space: nowrap; transition: background-color 0.15s ease; -webkit-font-smoothing: antialiased; }
        .ml-promo-banner-wrap:hover .ml-cta { background: #2968c8; }

        @media (max-width: 1280px) {
            .ml-banner-inner { padding: 18px 20px; gap: 16px; flex-wrap: wrap; justify-content: center; }
            .ml-banner-left { gap: 14px; }
            .ml-logo-img { height: 60px; max-width: 200px; }
            .ml-banner-divider { display: none; }
            .ml-banner-center { flex: 1 1 100%; align-items: center; text-align: center; gap: 5px; order: 2; padding-right: 0; }
            .ml-banner-right { flex: 1 1 100%; order: 3; flex-direction: column; gap: 14px; }
            .ml-shipping-img { height: 38px; max-width: 100%; }
        }

        @media (max-width: 480px) {
            .ml-banner-inner { padding: 16px 16px; }
            .ml-logo-img { height: 52px; max-width: 170px; }
            .ml-product-name { font-size: 15px; white-space: normal; }
            .ml-trust-line { font-size: 12px; white-space: normal; }
            .ml-shipping-img { height: 32px; }
            .ml-ship-pill .pill-dark { padding: 7px 12px; font-size: 11px; }
            .ml-ship-pill .pill-white { padding: 0 12px 0 10px; font-size: 11px; }
            .ml-cta { width: 100%; height: 46px; line-height: 46px; font-size: 15px; padding: 0 18px; }
        }

        /* ─── CUSTOMER REVIEWS SECTION (AMAZON STYLE 2-COLUMNS) ─── */
        .customer-reviews-section {
            max-width: 1240px;
            width: 100%;
            margin: 40px auto 30px auto;
            padding: 0 20px;
            box-sizing: border-box;
            overflow-x: hidden;
            font-family: var(--font-body);
            opacity: 0;
            transform: translateY(24px);
            transition: opacity 0.6s cubic-bezier(0.16, 1, 0.3, 1), transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .customer-reviews-section.scroll-visible {
            opacity: 1;
            transform: translateY(0);
        }

        .customer-reviews-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 32px;
            align-items: start;
        }
        @media (min-width: 992px) {
            .customer-reviews-grid {
                grid-template-columns: 330px 1fr;
                gap: 48px;
            }
        }

        /* ─── COLUMNA IZQUIERDA: RESUMEN AMAZON ─── */
        .reviews-summary-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 0;
            box-sizing: border-box;
        }
        @media (min-width: 992px) {
            .reviews-summary-card {
                position: sticky;
                top: 120px;
            }
        }
        .reviews-sidebar-title {
            font-family: var(--font-heading);
            font-size: 22px;
            font-weight: 700;
            color: #0f1111;
            margin: 0 0 10px 0;
            letter-spacing: -0.015em;
        }
        .reviews-score-hero {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 4px;
        }
        .reviews-stars-hero {
            color: #de7921;
            font-size: 19px;
            letter-spacing: 2px;
            line-height: 1;
        }
        .reviews-score-text {
            font-size: 17px;
            font-weight: 700;
            color: #0f1111;
        }
        .reviews-total-ratings-sub {
            font-size: 13.5px;
            color: #565959;
            margin-bottom: 18px;
        }

        /* BARRAS DE DISTRIBUCIÓN */
        .rating-breakdown-table {
            display: flex;
            flex-direction: column;
            gap: 9px;
            margin-bottom: 18px;
        }
        .rating-bar-row {
            display: grid;
            grid-template-columns: 78px 1fr 38px;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            padding: 4px 6px;
            border-radius: 6px;
            transition: background 0.15s ease;
            user-select: none;
        }
        .rating-bar-row:hover {
            background: #f7fafa;
        }
        .bar-label {
            font-size: 13px;
            color: #007185;
            font-weight: 500;
            white-space: nowrap;
        }
        .bar-track {
            height: 18px;
            background: #f0f2f2;
            border: 1px solid #d5d9d9;
            border-radius: 4px;
            overflow: hidden;
            box-shadow: inset 0 1px 2px rgba(0,0,0,0.06);
            position: relative;
        }
        .bar-fill {
            height: 100%;
            background: #de7921;
            border-radius: 3px 0 0 3px;
            transition: width 0.7s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .bar-pct {
            font-size: 13px;
            color: #007185;
            font-weight: 500;
            text-align: right;
        }

        /* EXPLICACIÓN DESPLEGABLE */
        .reviews-explanation-accordion {
            margin: 6px 0 16px 0;
        }
        .explanation-toggle-btn {
            background: none;
            border: none;
            padding: 0;
            color: #007185;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            text-align: left;
            transition: color 0.15s;
        }
        .explanation-toggle-btn:hover {
            color: #c7511f;
            text-decoration: underline;
        }
        .explanation-arrow {
            font-size: 11px;
            transition: transform 0.2s;
        }
        .explanation-content {
            display: none;
            font-size: 12.5px;
            color: #565959;
            line-height: 1.45;
            padding: 8px 4px 4px 4px;
            background: #fbfbfd;
            border-radius: 8px;
            margin-top: 6px;
        }
        .explanation-content.open {
            display: block;
        }

        .reviews-sidebar-divider {
            height: 1px;
            background: #e7e7e7;
            margin: 20px 0;
        }

        /* SECCIÓN ESCRIBIR OPINIÓN */
        .write-review-block {
            margin-top: 8px;
        }
        .write-review-title {
            font-size: 17px;
            font-weight: 700;
            color: #0f1111;
            margin: 0 0 4px 0;
            font-family: var(--font-heading);
        }
        .write-review-subtitle {
            font-size: 13px;
            color: #565959;
            margin: 0 0 16px 0;
        }
        .btn-write-review {
            width: 100%;
            height: 40px;
            background: #ffffff;
            border: 1px solid #d5d9d9;
            border-radius: 999px;
            font-size: 13.5px;
            font-weight: 600;
            color: #0f1111;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(213,217,217,0.5);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        .btn-write-review:hover {
            background: #f7fafa;
            border-color: #0f1111;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
            transform: translateY(-1px);
        }

        /* ─── COLUMNA DERECHA: FILTROS Y FEED ─── */
        .reviews-feed-column {
            width: 100%;
            min-width: 0;
        }
        .reviews-filters-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            padding-bottom: 14px;
            border-bottom: 1px solid var(--border-light);
            margin-bottom: 12px;
        }
        .filters-left-group {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        .filters-right-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .review-filter-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12.5px;
            color: #374151;
            font-weight: 600;
        }
        .filter-select-box {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 6px 24px 6px 10px;
            font-size: 12.5px;
            color: #1d1d1f;
            background: #ffffff;
            appearance: none;
            cursor: pointer;
            font-weight: 500;
        }
        .review-card-item {
            display: grid;
            grid-template-columns: 200px 1fr auto;
            gap: 20px;
            padding: 20px 0;
            border-bottom: 1px solid var(--border-light);
            align-items: start;
        }
        @media (max-width: 768px) {
            .review-card-item {
                grid-template-columns: 1fr;
                gap: 8px;
            }
        }
        .reviewer-col {
            display: flex;
            flex-direction: column;
            gap: 3px;
        }
        .reviewer-name {
            font-weight: 700;
            font-size: 14px;
            color: #1d1d1f;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .reviewer-badge-verified {
            font-size: 10.5px;
            background: #e6f7ed;
            color: #059669;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 4px;
        }
        .reviewer-meta {
            font-size: 12px;
            color: var(--text-muted);
        }
        .review-content-col {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .review-stars-row {
            color: #de7921;
            font-size: 14px;
            letter-spacing: 1px;
        }
        .review-comment-text {
            font-size: 13.5px;
            color: #1d1d1f;
            line-height: 1.5;
            font-weight: 400;
            margin: 0;
        }
        .review-date-badge {
            font-size: 11.5px;
            color: var(--text-muted);
            white-space: nowrap;
            text-align: right;
        }
        .review-actions-wrap {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 8px;
        }
        .btn-delete-user-review {
            background: transparent;
            border: none;
            padding: 4px;
            margin: 0;
            color: #8e8e93;
            opacity: 0.45;
            cursor: pointer;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: opacity 0.2s ease, color 0.2s ease, background 0.2s ease;
            line-height: 1;
        }
        .btn-delete-user-review:hover {
            opacity: 1;
            color: #d32f2f;
            background: rgba(211, 47, 47, 0.08);
        }
        @media (max-width: 768px) {
            .review-date-badge {
                text-align: left;
            }
            .review-actions-wrap {
                justify-content: flex-start;
            }
        }
        .reviews-pagination-row {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 8px;
            margin-top: 22px;
            font-size: 12.5px;
            color: var(--text-muted);
        }
        .page-btn {
            width: 30px;
            height: 30px;
            border: 1px solid transparent;
            background: transparent;
            border-radius: 50%;
            cursor: pointer;
            font-size: 12.5px;
            font-weight: 600;
            color: #1d1d1f;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }
        .page-btn.active {
            background: #1d1d1f;
            color: #ffffff;
        }
        .page-btn:hover:not(.active) {
            background: #e5e5ea;
        }

        /* ─── MODAL ESCRIBIR OPINIÓN ─── */
        .write-review-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            z-index: 100000;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 16px;
            box-sizing: border-box;
        }
        .write-review-modal-overlay.open {
            display: flex;
        }
        .write-review-modal-dialog {
            background: #ffffff;
            border-radius: 18px;
            max-width: 520px;
            width: 100%;
            padding: 26px 28px;
            box-sizing: border-box;
            box-shadow: 0 20px 60px rgba(0,0,0,0.25);
            animation: modalFadeIn 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            max-height: 90vh;
            overflow-y: auto;
        }
        @keyframes modalFadeIn {
            from { opacity: 0; transform: scale(0.94) translateY(12px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }
        .write-review-modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
            padding-bottom: 12px;
            border-bottom: 1px solid #e7e7e7;
        }
        .write-review-modal-header h3 {
            margin: 0;
            font-family: var(--font-heading);
            font-size: 19px;
            font-weight: 700;
            color: #0f1111;
        }
        .modal-close-btn {
            background: #f0f2f2;
            border: none;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            color: #565959;
            transition: all 0.15s;
        }
        .modal-close-btn:hover {
            background: #e3e6e6;
            color: #0f1111;
        }
        .write-review-form {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .form-label {
            font-size: 13.5px;
            font-weight: 700;
            color: #0f1111;
        }
        .form-label .required {
            color: #c40000;
        }
        .form-label .optional {
            font-weight: 400;
            color: #565959;
            font-size: 12px;
        }
        .form-input, .form-textarea {
            width: 100%;
            border: 1px solid #888c8c;
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 14px;
            font-family: var(--font-body);
            color: #0f1111;
            box-sizing: border-box;
            transition: border-color 0.2s, box-shadow 0.2s;
            background: #ffffff;
        }
        .form-input:focus, .form-textarea:focus {
            outline: none;
            border-color: #007185;
            box-shadow: 0 0 0 3px rgba(0,113,133,0.15);
        }
        .star-rating-picker {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 6px 0;
        }
        .star-pick {
            font-size: 28px;
            color: #d5d9d9;
            cursor: pointer;
            transition: color 0.15s, transform 0.15s;
            user-select: none;
            line-height: 1;
        }
        .star-pick.hovered, .star-pick.selected {
            color: #de7921;
        }
        .star-pick:hover {
            transform: scale(1.18);
        }
        .star-rating-text {
            font-size: 13.5px;
            font-weight: 600;
            color: #0f1111;
            margin-left: 8px;
        }
        .write-review-modal-actions {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 10px;
            margin-top: 8px;
            padding-top: 14px;
            border-top: 1px solid #e7e7e7;
        }
        .btn-review-cancel {
            padding: 9px 18px;
            background: #ffffff;
            border: 1px solid #d5d9d9;
            border-radius: 999px;
            font-size: 13.5px;
            font-weight: 600;
            color: #0f1111;
            cursor: pointer;
            transition: background 0.15s;
        }
        .btn-review-cancel:hover {
            background: #f7fafa;
        }
        .btn-review-submit {
            padding: 10px 24px;
            background: #ffd814;
            border: 1px solid #fcd200;
            border-radius: 999px;
            font-size: 13.5px;
            font-weight: 700;
            color: #0f1111;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(213,217,217,0.5);
            transition: all 0.2s;
        }
        .btn-review-submit:hover {
            background: #f7ca00;
            border-color: #f2c200;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        /* ─── VISTAS INTERNAS DEL MODAL DE OPINIÓN Y VERIFICACIÓN ─── */
        .review-modal-view {
            display: none;
            animation: modalFadeIn 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .review-modal-view.active {
            display: block;
        }

        /* ENUNCIADO COMPRADOR VERIFICADO (10% OFF) */
        .verified-buyer-promo-box {
            background: linear-gradient(135deg, #f8faff 0%, #edf5ff 100%);
            border: 1.5px solid #b8daff;
            border-radius: 14px;
            padding: 16px 18px;
            margin-bottom: 20px;
            text-align: left;
            position: relative;
            box-shadow: 0 4px 16px rgba(0, 113, 227, 0.06);
        }
        .promo-box-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: #0071e3;
            color: #ffffff;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 9px;
            border-radius: 999px;
            margin-bottom: 8px;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }
        .promo-box-title {
            font-family: var(--font-heading);
            font-size: 15.5px;
            font-weight: 700;
            color: #0f1111;
            margin: 0 0 4px 0;
        }
        .promo-box-text {
            font-size: 13px;
            color: #334155;
            line-height: 1.45;
            margin: 0 0 4px 0;
            font-weight: 500;
        }
        .promo-box-terms {
            font-size: 11px;
            color: #8c9b9e;
            font-style: italic;
            margin-bottom: 12px;
        }
        .btn-verify-purchase-trigger {
            background: #ffffff;
            border: 1.5px solid #0071e3;
            color: #0071e3;
            font-size: 13px;
            font-weight: 700;
            padding: 7px 18px;
            border-radius: 999px;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 2px 6px rgba(0, 113, 227, 0.12);
        }
        .btn-verify-purchase-trigger:hover {
            background: #0071e3;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(0, 113, 227, 0.28);
            transform: translateY(-1px);
        }

        .discount-blue-underlined {
            color: #0071e3;
            font-weight: 700;
            text-decoration: underline;
            text-decoration-color: #0071e3;
            text-decoration-thickness: 2px;
            text-underline-offset: 3px;
        }

        /* CABECERA VISTA VERIFICAR */
        .verify-buyer-header {
            text-align: center;
            margin-bottom: 18px;
        }
        .verify-buyer-header h4 {
            margin: 0 0 6px 0;
            font-family: var(--font-heading);
            font-size: 19px;
            font-weight: 700;
            color: #0f1111;
        }
        .verify-buyer-header p {
            margin: 6px 0 0 0;
            font-size: 13.5px;
            color: #565959;
            line-height: 1.4;
        }

        /* RESULTADO NO ADQUIRIDO (UPSELL) */
        .upsell-result-box {
            text-align: center;
            padding: 4px 6px 4px 6px;
        }
        .upsell-logo-wrap {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 0 0 10px 0;
            margin: 0 auto;
        }
        .upsell-brand-logo {
            max-width: 220px;
            width: 80%;
            height: auto;
            max-height: 85px;
            object-fit: contain;
            display: block;
            margin: 0 auto;
        }
        .upsell-brand-logo-text {
            font-size: 38px;
            font-weight: 900;
            font-family: var(--font-heading);
            letter-spacing: 2px;
            color: #0f1111;
            margin: 0;
        }
        .upsell-title {
            font-size: 18px;
            font-weight: 700;
            color: #0f1111;
            margin: 0 0 6px 0;
            line-height: 1.35;
        }
        .upsell-question {
            font-size: 21px;
            font-weight: 800;
            color: #0071e3;
            margin: 0 0 10px 0;
        }
        .upsell-desc {
            font-size: 13.5px;
            color: #565959;
            margin: 0 auto 22px auto;
            max-width: 400px;
            line-height: 1.45;
        }
        .upsell-actions-row {
            display: flex;
            gap: 12px;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
        }
        .btn-upsell-cart {
            flex: 1;
            min-width: 170px;
            height: 44px;
            background: #ffd814;
            border: 1px solid #fcd200;
            border-radius: 999px;
            font-size: 14px;
            font-weight: 700;
            color: #0f1111;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        .btn-upsell-cart:hover {
            background: #f7ca00;
            border-color: #f2c200;
            box-shadow: 0 4px 14px rgba(0,0,0,0.18);
            transform: scale(1.02);
        }
        .btn-upsell-review {
            flex: 1;
            min-width: 170px;
            height: 44px;
            background: #ffffff;
            border: 1px solid #d5d9d9;
            border-radius: 999px;
            font-size: 14px;
            font-weight: 600;
            color: #0f1111;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        .btn-upsell-review:hover {
            background: #f7fafa;
            border-color: #0f1111;
        }

        /* ─── MÁS PRODUCTOS RECOMENDADOS (CRUZADOS) ─── */
        /* ─── QUIENES VIERON ESTE PRODUCTO TAMBIÉN COMPRARON (SLIDE SINGLE-ROW) ─── */
        .more-to-love-section {
            max-width: 1240px;
            width: 100%;
            margin: 36px auto 44px auto;
            padding: 0 20px;
            box-sizing: border-box;
            text-align: center;
            position: relative;
        }
        .section-heading-center {
            font-family: var(--font-heading);
            font-size: 20px;
            font-weight: 700;
            letter-spacing: -0.015em;
            color: #1d1d1f;
            margin-bottom: 20px;
        }
        .more-slider-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            width: 100%;
        }
        .more-grid {
            display: flex !important;
            flex-wrap: nowrap !important;
            gap: 16px;
            overflow-x: auto !important;
            scroll-snap-type: x mandatory;
            scroll-behavior: smooth;
            padding: 8px 4px 14px 4px;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
            width: 100%;
        }
        .more-grid::-webkit-scrollbar {
            display: none;
        }
        .more-card {
            flex: 0 0 200px !important;
            min-width: 200px !important;
            max-width: 200px !important;
            scroll-snap-align: start;
            background: #ffffff;
            border: 1px solid rgba(0,0,0,0.06);
            border-radius: 16px;
            overflow: hidden;
            padding: 12px;
            text-align: left;
            text-decoration: none;
            display: flex;
            flex-direction: column;
            transition: all 0.25s ease;
            box-shadow: 0 2px 10px rgba(0,0,0,0.02);
        }
        .more-card:hover {
            box-shadow: 0 8px 24px rgba(0,0,0,0.08);
            transform: translateY(-2px);
            border-color: var(--primary);
        }
        @media (max-width: 640px) {
            .more-card {
                flex: 0 0 155px !important;
                min-width: 155px !important;
                max-width: 155px !important;
                padding: 10px;
            }
        }
        .more-card-img { width: 100%; aspect-ratio: 1/1; border-radius: 10px; object-fit: cover; background: #fbfbfd; }
        .more-card-title { font-size: 13px; font-weight: 600; color: #1d1d1f; margin: 8px 0 4px 0; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.35; }
        .more-card-stars { font-size: 12px; color: var(--star-color); margin-bottom: 4px; }
        .more-card-price { font-weight: 700; font-size: 14.5px; color: #1d1d1f; margin-top: auto; letter-spacing: -0.01em; }

        .more-products-dots {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 14px;
            width: 100%;
        }
        .more-prod-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #d2d2d7;
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.2, 0.8, 0.2, 1);
        }
        .more-prod-dot.active {
            background: #59595e;
            width: 22px;
            border-radius: 999px;
        }

        /* ─── 7. FOOTER MODERNO (ESTILO SHEGLAM / AMAZON) ─── */
        .generic-footer {
            background: #000000;
            color: #ffffff;
            padding: 40px 20px 34px 20px;
            margin-top: 45px;
            margin-bottom: 0;
            width: 100%;
            box-sizing: border-box;
            overflow-x: hidden;
        }
        @media (max-width: 991px) {
            .generic-footer {
                padding-bottom: 105px !important; /* Espacio para barra flotante móvil sin huecos blancos */
            }
        }


        /* Flechas del carrusel recomendados - solo desktop */
        .more-slider-arrow {
            display: none;
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            z-index: 10;
            background: #ffffff;
            border: 1px solid rgba(0,0,0,0.12);
            border-radius: 50%;
            width: 38px;
            height: 38px;
            font-size: 14px;
            font-weight: 700;
            color: #1d1d1f;
            cursor: pointer;
            box-shadow: 0 2px 10px rgba(0,0,0,0.10);
            transition: background 0.15s ease, box-shadow 0.15s ease;
            align-items: center;
            justify-content: center;
        }
        .more-slider-arrow:hover {
            background: #f5f5f7;
            box-shadow: 0 4px 14px rgba(0,0,0,0.16);
        }
        .more-slider-arrow.prev { left: -18px; }
        .more-slider-arrow.next { right: -18px; }
        @media (min-width: 992px) {
            .more-slider-arrow {
                display: flex;
            }
        }

        .footer-content-wrap {
            max-width: 900px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            gap: 22px;
            position: relative;
        }
        /* ─── BARRA DE 3 PILARES DE CONFIANZA EN EL FOOTER ─── */
        .footer-trust-benefits-bar {
            width: 100%;
            max-width: 860px;
            margin: 0 auto 10px auto;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 58px;
            padding-bottom: 24px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.12);
            box-sizing: border-box;
        }
        .trust-benefit-col {
            display: flex;
            align-items: center;
            gap: 12px;
            text-align: left;
            flex: 0 1 auto;
        }
        .trust-benefit-icon {
            width: 33px;
            height: 33px;
            object-fit: contain;
            flex-shrink: 0;
            display: block;
        }
        .trust-benefit-text {
            font-size: 14px;
            font-weight: 700;
            color: #ffffff;
            line-height: 1.22;
            letter-spacing: -0.01em;
            font-family: var(--font-heading);
            white-space: nowrap;
        }

        /* ─── RESPONSIVE MÓVIL (MANTIENE 100% ORDEN HORIZONTAL Y EQUIDISTANTE) ─── */
        @media (max-width: 768px) {
            .footer-trust-benefits-bar {
                gap: 26px;
                padding-bottom: 18px;
            }
            .trust-benefit-col {
                gap: 8px;
            }
            .trust-benefit-icon {
                width: 26px;
                height: 26px;
            }
            .trust-benefit-text {
                font-size: 11.5px;
                line-height: 1.18;
            }
        }

        @media (max-width: 480px) {
            .footer-trust-benefits-bar {
                gap: 14px;
                padding-bottom: 15px;
            }
            .trust-benefit-col {
                gap: 6px;
            }
            .trust-benefit-icon {
                width: 22px;
                height: 22px;
            }
            .trust-benefit-text {
                font-size: 10px;
                line-height: 1.15;
                letter-spacing: -0.015em;
            }
        }
        .footer-payments-row {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            flex-wrap: wrap;
            margin-top: 4px;
        }
        .footer-payment-badge {
            width: 44px;
            height: 27px;
            background: #ffffff;
            border-radius: 4px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 1px 2px;
            box-sizing: border-box;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
            transition: transform 0.2s ease;
            overflow: hidden;
            flex-shrink: 0;
        }
        .footer-payment-badge:hover {
            transform: translateY(-2px) scale(1.06);
        }
        .footer-payment-badge img,
        .footer-payment-badge svg {
            max-width: 100%;
            max-height: 100%;
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
        }
        .footer-payment-badge.badge-amex img {
            transform: scale(1.25);
        }
        .footer-payment-badge.badge-visa img {
            transform: scale(1.05);
        }
        .footer-payment-badge.badge-master img {
            transform: scale(0.90);
        }
        .footer-payment-badge.badge-pse img {
            transform: scale(0.86);
        }
        .footer-payment-badge.badge-nequi img {
            transform: scale(0.86);
        }
        .footer-payment-badge.badge-contraentrega img {
            transform: scale(0.92);
        }
        .footer-legal-row {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 32px;
            flex-wrap: wrap;
            margin-top: 10px;
        }
        .footer-sic-badge,
        .footer-camara-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.2s ease;
        }
        .footer-sic-badge:hover,
        .footer-camara-badge:hover {
            transform: scale(1.04);
        }
        .footer-sic-badge img,
        .footer-camara-badge img {
            height: 42px;
            width: auto;
            max-width: 240px;
            object-fit: contain;
            display: block;
            filter: drop-shadow(0 2px 6px rgba(0,0,0,0.5));
        }
        .footer-bottom-row {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            margin-top: 8px;
            padding: 0 44px;
            box-sizing: border-box;
        }
        .footer-copyright-text {
            font-size: 12px;
            color: #9ca3af;
            font-weight: 500;
            letter-spacing: 0.3px;
            text-align: center;
            line-height: 1.5;
        }
        .btn-scroll-top {
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.25);
            color: #ffffff;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(0,0,0,0.4);
            flex-shrink: 0;
        }
        .btn-scroll-top:hover {
            background: #ffffff;
            color: #000000;
            transform: translateY(-50%) scale(1.1);
        }
        @media (max-width: 640px) {
            .footer-legal-row {
                gap: 16px;
            }
            .footer-bottom-row {
                flex-direction: row;
                justify-content: center;
                padding-right: 44px;
                padding-left: 10px;
            }
            .btn-scroll-top {
                position: absolute;
                right: 0;
                top: 50%;
                transform: translateY(-50%);
            }
            .btn-scroll-top:hover {
                transform: translateY(-50%) scale(1.1);
            }
        }

        .sticky-footer-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #ffffff;
            border-top: 1px solid var(--border-color);
            padding: 10px 16px 14px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            z-index: 900;
            box-shadow: 0 -4px 15px rgba(0,0,0,0.06);
            max-width: 540px;
            margin: 0 auto;
            transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1);
            will-change: transform;
        }
        .sticky-footer-bar.bar-hidden {
            transform: translateY(120%) !important;
        }
        .support-btn { width: 46px; height: 46px; border-radius: 10px; border: 1px solid var(--border-color); background: #ffffff; display: flex; align-items: center; justify-content: center; color: #111111; text-decoration: none; flex-shrink: 0; }
        .btn-add-to-cart { flex: 1; height: 48px; background-color: var(--btn-bg); color: #ffffff; border: none; border-radius: 12px; font-family: var(--font-heading); font-size: 14px; font-weight: 800; display: flex; align-items: center; justify-content: center; gap: 6px; cursor: pointer; letter-spacing: 0.5px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); }

        @media (min-width: 992px) {
            .product-grid-layout { display: grid; grid-template-columns: 1.1fr 1fr; gap: 48px; align-items: start; max-width: 100%; }
            .desktop-action-row { display: flex; }
            .sticky-footer-bar { display: none !important; }
            body { padding-bottom: 0 !important; }
        }

        .lightbox-modal { position: fixed; inset: 0; background: rgba(0, 0, 0, 0.94); z-index: 99999; display: none; align-items: center; justify-content: center; flex-direction: column; backdrop-filter: blur(5px); }
        .lightbox-modal.open { display: flex; }
        .lightbox-close-btn { position: absolute; top: 20px; right: 24px; background: rgba(255, 255, 255, 0.15); border: none; color: #ffffff; width: 44px; height: 44px; border-radius: 50%; font-size: 24px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background 0.2s; z-index: 100; }
        .lightbox-main-view { max-width: 90vw; max-height: 78vh; display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden; }
        .lightbox-main-view img { max-width: 100%; max-height: 78vh; object-fit: contain; border-radius: 8px; box-shadow: 0 10px 40px rgba(0,0,0,0.5); cursor: zoom-in; transition: transform 0.22s ease-out; }
        .lightbox-main-view img.zoomed { transform: scale(2.2); cursor: zoom-out; }
        
        .lightbox-nav-btn.prev { left: -70px; }
        .lightbox-nav-btn.next { right: -70px; }
        @media (max-width: 768px) { .lightbox-nav-btn.prev { left: 10px; } .lightbox-nav-btn.next { right: 10px; } }
        .lightbox-thumbs-row { display: flex; gap: 10px; margin-top: 20px; max-width: 90vw; overflow-x: auto; padding: 8px; }
        .lightbox-thumb { width: 54px; height: 54px; border-radius: 6px; overflow: hidden; border: 2px solid transparent; cursor: pointer; opacity: 0.6; transition: all 0.2s; }
        .lightbox-thumb.active { border-color: #ffffff; opacity: 1; transform: scale(1.1); }
        .lightbox-thumb img { width: 100%; height: 100%; object-fit: cover; }

        .cart-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.45);
            z-index: 10000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.35s cubic-bezier(0.32, 0.72, 0, 1);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
        }
        .cart-overlay.open {
            opacity: 1;
            visibility: visible;
        }
        .cart-drawer {
            position: fixed;
            top: 0;
            right: -100%;
            bottom: 0;
            width: 100%;
            max-width: 430px;
            background: #ffffff;
            z-index: 10001;
            transition: right 0.38s cubic-bezier(0.32, 0.72, 0, 1);
            display: flex;
            flex-direction: column;
            box-shadow: -10px 0 35px rgba(0, 0, 0, 0.12);
            border-top-left-radius: 20px;
            border-bottom-left-radius: 20px;
        }
        .cart-overlay.open .cart-drawer {
            right: 0;
        }
        .cart-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 24px;
            border-bottom: 1px solid var(--border-light);
        }
        .cart-header h3 {
            font-family: var(--font-heading);
            font-size: 17px;
            font-weight: 700;
            color: #1d1d1f;
            margin: 0;
            letter-spacing: -0.015em;
        }
        .close-cart-btn {
            background: #f5f5f7;
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            color: #86868b;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }
        .close-cart-btn:hover {
            background: #e5e5ea;
            color: #1d1d1f;
        }
        .shipping-progress-wrap {
            background: rgba(0, 166, 80, 0.05);
            padding: 12px 24px;
            border-bottom: 1px solid rgba(0, 166, 80, 0.15);
        }
        .shipping-progress-text {
            font-size: 12.5px;
            font-weight: 700;
            color: #00a650;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .shipping-bar {
            height: 5px;
            background: #e5e5ea;
            border-radius: 980px;
            overflow: hidden;
        }
        .shipping-bar-fill {
            height: 100%;
            background: #00a650;
            width: 100%;
            border-radius: 980px;
        }
        .cart-items-list {
            flex: 1;
            overflow-y: auto;
            padding: 18px 24px;
        }
        .cart-item {
            display: flex;
            gap: 14px;
            padding-bottom: 18px;
            border-bottom: 1px solid #f2f2f7;
            margin-bottom: 18px;
        }
        .cart-item-img {
            width: 72px;
            height: 72px;
            border-radius: 12px;
            object-fit: cover;
            background: #fbfbfd;
            flex-shrink: 0;
            border: 1px solid var(--border-light);
        }
        .cart-item-info {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .cart-item-title {
            font-size: 13.5px;
            font-weight: 600;
            color: #1d1d1f;
            line-height: 1.35;
            letter-spacing: -0.01em;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .cart-item-variant {
            font-size: 12px;
            color: #86868b;
            margin-top: 3px;
        }
        .cart-item-bottom {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 8px;
        }
        .cart-item-price {
            font-weight: 700;
            font-size: 14.5px;
            color: #1d1d1f;
            letter-spacing: -0.01em;
        }
        .qty-controls {
            display: flex;
            align-items: center;
            border: 1px solid var(--border-color);
            border-radius: 980px;
            overflow: hidden;
            background: #f5f5f7;
        }
        .qty-btn {
            background: transparent;
            border: none;
            width: 26px;
            height: 26px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1d1d1f;
            transition: background 0.15s;
        }
        .qty-btn:hover {
            background: #e5e5ea;
        }
        .qty-value {
            width: 26px;
            text-align: center;
            font-size: 12px;
            font-weight: 700;
            color: #1d1d1f;
        }
        .cart-footer {
            padding: 20px 24px;
            background: #ffffff;
            border-top: 1px solid var(--border-light);
            box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.04);
        }
        .cart-summary-row {
            display: flex;
            justify-content: space-between;
            font-size: 13.5px;
            color: #6e6e73;
            margin-bottom: 8px;
        }
        .cart-summary-row.total {
            font-family: var(--font-heading);
            font-size: 17px;
            font-weight: 700;
            color: #1d1d1f;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid var(--border-light);
            letter-spacing: -0.015em;
        }
        .btn-checkout {
            width: 100%;
            height: 50px;
            background-color: var(--btn-bg, #0071e3);
            color: #ffffff;
            border: none;
            border-radius: 980px;
            font-family: var(--font-heading);
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 14px rgba(0, 113, 227, 0.28);
            letter-spacing: -0.01em;
        }
        .btn-checkout:hover {
            background-color: var(--primary-hover, #0077ed);
            transform: scale(1.015);
            box-shadow: 0 6px 20px rgba(0, 113, 227, 0.38);
        }

        .editor-top-toolbar { position: fixed; top: 0; left: 0; right: 0; background: #0f172a; color: #ffffff; padding: 10px 20px; display: flex; align-items: center; justify-content: space-between; z-index: 999999; box-shadow: 0 4px 20px rgba(0,0,0,0.4); font-family: 'Inter', sans-serif; font-size: 13px; }
        .editor-badge { background: #22c55e; color: #000; font-weight: 800; font-size: 11px; padding: 4px 10px; border-radius: 20px; text-transform: uppercase; }
        .editor-actions { display: flex; gap: 10px; align-items: center; }
        .btn-editor-save { background: #3b82f6; color: #ffffff; border: none; padding: 8px 18px; border-radius: 8px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 6px; }
        .btn-editor-preview { background: rgba(255,255,255,0.15); color: #ffffff; border: 1px solid rgba(255,255,255,0.2); padding: 8px 16px; border-radius: 8px; font-weight: 600; text-decoration: none; }

        body.modo-edicion-activo [data-editable="true"]:hover { outline: 2px dashed #3b82f6 !important; cursor: text; background: rgba(59, 130, 246, 0.05); }
        body.modo-edicion-activo [data-editable="true"]:focus { outline: 2px solid #22c55e !important; background: rgba(34, 197, 94, 0.08); }

        #landing-loader { display: none; position: fixed; inset: 0; background: rgba(255, 255, 255, 0.95); z-index: 99999; flex-direction: column; justify-content: center; align-items: center; }
        .spinner { width: 44px; height: 44px; border: 3px solid #f3f4f6; border-top-color: var(--primary); border-radius: 50%; animation: spin 0.8s linear infinite; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    
        

    
        
        /* ─── ANIMACIONES PROFESIONALES DEL CARRITO Y LOTTIE ─── */
        .cart-trigger {
            position: relative;
            background: none;
            border: none;
            cursor: pointer;
            color: #111827;
            padding: 6px;
            transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .cart-pop-active {
            animation: cartPopBounce 0.45s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
        }
        @keyframes cartPopBounce {
            0% { transform: scale(1); }
            35% { transform: scale(1.4); }
            70% { transform: scale(0.92); }
            100% { transform: scale(1); }
        }
        .cart-ripple-effect {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 46px;
            height: 46px;
            margin-left: -23px;
            margin-top: -23px;
            border-radius: 50%;
            border: 2.5px solid var(--primary, #111111);
            pointer-events: none;
            animation: cartRippleAnim 0.65s cubic-bezier(0.1, 0.8, 0.3, 1) forwards;
        }
        @keyframes cartRippleAnim {
            0% { transform: scale(0.4); opacity: 0.95; }
            100% { transform: scale(1.85); opacity: 0; }
        }
        .cart-badge-bounce {
            animation: cartBadgeBounce 0.45s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        @keyframes cartBadgeBounce {
            0% { transform: scale(1); }
            45% { transform: scale(1.5); }
            100% { transform: scale(1); }
        }

        /* ─── 5.2 REVIEWS WITH VIDEOS CAROUSEL (AMAZON STYLE) ─── */
        .video-reviews-section {
            max-width: 1200px;
            margin: 28px auto 14px auto;
            padding: 0 16px;
            box-sizing: border-box;
        }
        .video-reviews-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 14px;
            gap: 12px;
            flex-wrap: wrap;
        }
        .video-reviews-title-wrap {
            display: flex;
            flex-direction: column;
            gap: 3px;
        }
        .video-reviews-main-title {
            font-family: var(--font-heading, 'Montserrat', sans-serif);
            font-size: 20px;
            font-weight: 800;
            color: #111827;
            margin: 0;
            letter-spacing: -0.3px;
        }
        .video-reviews-subtitle {
            font-size: 13px;
            color: var(--text-muted, #6b7280);
            font-weight: 500;
        }
        .video-reviews-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .btn-add-video-card {
            background: #22c55e;
            color: #ffffff;
            border: none;
            padding: 7px 14px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 2px 8px rgba(34, 197, 94, 0.35);
            transition: transform 0.2s ease, background 0.2s ease;
        }
        .btn-add-video-card:hover {
            background: #16a34a;
            transform: scale(1.03);
        }
        .video-carousel-arrows {
            display: flex;
            gap: 6px;
        }
        .video-arrow-btn {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            color: #1f2937;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            cursor: pointer;
            box-shadow: 0 2px 6px rgba(0,0,0,0.06);
            transition: all 0.2s ease;
        }
        .video-arrow-btn:hover {
            background: #111827;
            color: #ffffff;
            border-color: #111827;
        }

        .video-reviews-carousel-track {
            display: flex;
            gap: 14px;
            overflow-x: auto;
            scroll-snap-type: x mandatory;
            padding: 8px 4px 18px 4px;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 transparent;
        }
        .video-reviews-carousel-track::-webkit-scrollbar {
            height: 6px;
        }
        .video-reviews-carousel-track::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 6px;
        }

        .video-review-card {
            flex: 0 0 160px;
            height: 250px;
            border-radius: 14px;
            position: relative;
            overflow: hidden;
            background: #000000;
            cursor: pointer;
            scroll-snap-align: start;
            box-shadow: 0 4px 12px rgba(0,0,0,0.12);
            transition: transform 0.25s cubic-bezier(0.2, 0.8, 0.2, 1), box-shadow 0.25s ease;
            user-select: none;
        }
        @media (max-width: 640px) {
            .video-review-card {
                flex: 0 0 140px;
                height: 220px;
                border-radius: 12px;
            }
        }
        .video-review-card:hover {
            transform: translateY(-4px) scale(1.02);
            box-shadow: 0 10px 22px rgba(0,0,0,0.25);
        }
        .video-card-thumb {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform 0.4s ease;
            background: #1e293b;
        }
        .video-review-card:hover .video-card-thumb {
            transform: scale(1.06);
        }
        .video-card-gradient {
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.92) 0%, rgba(0,0,0,0.5) 45%, rgba(0,0,0,0.05) 80%, transparent 100%);
            pointer-events: none;
        }
        .video-card-badge-play {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(0,0,0,0.65);
            backdrop-filter: blur(4px);
            color: #ffffff;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            padding-left: 2px;
            border: 1px solid rgba(255,255,255,0.3);
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
            transition: all 0.2s ease;
            z-index: 2;
        }
        .video-review-card:hover .video-card-badge-play {
            background: #e11d48;
            border-color: #e11d48;
            transform: scale(1.15);
        }
        .video-card-info {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 12px 10px;
            color: #ffffff;
            z-index: 2;
            pointer-events: none;
        }
        .video-card-stars {
            color: #f97316;
            font-size: 13px;
            letter-spacing: 1.5px;
            margin-bottom: 3px;
            text-shadow: 0 1px 3px rgba(0,0,0,0.8);
        }
        .video-card-duration {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 12px;
            font-weight: 800;
            font-family: var(--font-heading, sans-serif);
            color: #ffffff;
            text-shadow: 0 1px 4px rgba(0,0,0,0.9);
        }
        .play-icon-mini {
            font-size: 10px;
            opacity: 0.9;
        }
        .video-card-title-text {
            font-size: 11px;
            font-weight: 600;
            color: #e2e8f0;
            margin-top: 3px;
            line-height: 1.25;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-shadow: 0 1px 3px rgba(0,0,0,0.9);
        }

        .video-card-admin-bar {
            position: absolute;
            top: 8px;
            left: 8px;
            display: flex;
            gap: 4px;
            z-index: 15;
        }
        .btn-vcard-edit {
            background: #3b82f6;
            color: #ffffff;
            border: none;
            font-size: 10px;
            font-weight: 700;
            padding: 4px 8px;
            border-radius: 6px;
            cursor: pointer;
            box-shadow: 0 2px 6px rgba(0,0,0,0.3);
        }
        .btn-vcard-del {
            background: #ef4444;
            color: #ffffff;
            border: none;
            font-size: 10px;
            font-weight: 700;
            padding: 4px 8px;
            border-radius: 6px;
            cursor: pointer;
            box-shadow: 0 2px 6px rgba(0,0,0,0.3);
        }

        /* ─── VIDEO LIGHTBOX MODAL ─── */
        .video-modal-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.88);
            backdrop-filter: blur(8px);
            z-index: 9999999;
            justify-content: center;
            align-items: center;
            padding: 16px;
            box-sizing: border-box;
            opacity: 0;
            transition: opacity 0.25s ease;
        }
        .video-modal-backdrop.active {
            display: flex;
            opacity: 1;
        }
        .video-modal-container {
            position: relative;
            width: 100%;
            max-width: 820px;
            background: #000000;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.15);
            transform: scale(0.94);
            transition: transform 0.25s cubic-bezier(0.2, 0.8, 0.2, 1);
        }
        .video-modal-backdrop.active .video-modal-container {
            transform: scale(1);
        }
        .video-modal-close-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 20;
            background: rgba(0, 0, 0, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.35);
            color: #ffffff;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }
        .video-modal-close-btn:hover {
            background: #ef4444;
            border-color: #ef4444;
            transform: scale(1.1);
        }
        .video-modal-iframe-wrapper {
            position: relative;
            width: 100%;
            padding-top: 56.25%; /* 16:9 Aspect Ratio */
            background: #000000;
        }
        .video-modal-iframe-wrapper iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: 0;
        }
            .review-title-text { font-family: var(--font-heading); font-weight: 700; font-size: 13.5px; color: #1d1d1f; margin: 2px 0 4px; line-height: 1.35; }
        .reviewer-place { font-size: 11.5px; color: #86868b; }
        .review-photos { display: flex; gap: 8px; margin-top: 10px; flex-wrap: wrap; }
        .review-photo { width: 74px; height: 74px; object-fit: cover; border-radius: 8px; border: 1px solid var(--border-light); cursor: zoom-in; transition: transform .2s ease; background: #fbfbfd; }
        .review-photo:hover { transform: scale(1.06); }
        .review-helpful { margin-top: 9px; font-size: 11.5px; color: #86868b; }
        .review-photo-backdrop { position: fixed; inset: 0; z-index: 10000; background: rgba(0,0,0,.88); display: none; align-items: center; justify-content: center; padding: 24px; cursor: zoom-out; }
        .review-photo-backdrop.open { display: flex; }
        .review-photo-backdrop img { max-width: 100%; max-height: 100%; border-radius: 10px; object-fit: contain; }
    </style>
    <script src="https://unpkg.com/@dotlottie/player-component@latest/dist/dotlottie-player.mjs" type="module"></script>
</head>
<body class="<?= $es_modo_edicion ? 'modo-edicion-activo' : '' ?>" style="<?= $es_modo_edicion ? 'margin-top: 50px;' : '' ?>">

    <?php if ($es_modo_edicion): ?>
    <div class="editor-top-toolbar" id="editorToolbar">
        <div style="display:flex; align-items:center; gap:12px;">
            <span class="editor-badge">🎨 Modo Edición Activo</span>
            <span style="color:#94a3b8; font-size:12px;">💡 Haz <b>doble clic</b> en cualquier texto para modificarlo en vivo.</span>
        </div>
        <div class="editor-actions">
            <button class="btn-editor-save" onclick="guardarCambiosVisuales()">💾 Guardar Cambios</button>
            <a href="?" class="btn-editor-preview">👁️ Ver como Cliente</a>
        </div>
    </div>
    <?php endif; ?>

    <div id="landing-loader">
        <div class="spinner"></div>
        <p style="margin-top: 14px; font-family: var(--font-heading); font-weight: 700; font-size: 14px;">Preparando tu pedido seguro...</p>
    </div>

    <!-- 1. TOP ANNOUNCEMENT BAR ESTÁTICO -->
    <div class="top-announcement"><span data-editable="true">ENVIO GRATIS A TODA COLOMBIA - PAGO CONTRAENTREGA - GARANTIA</span></div>

    <nav class="navbar">
        <div class="nav-left">
            <button class="nav-hamburger-btn" onclick="toggleNavMenu()" aria-label="Abrir menú de navegación" title="Menú">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <line x1="3" y1="12" x2="21" y2="12"></line>
                    <line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>
            </button>
        </div>

        <div class="nav-center-logo">
            <?php if (file_exists(__DIR__ . '/logo.svg')): ?>
                <img src="logo.svg" class="brand-logo-img" alt="<?= htmlspecialchars('Logitech') ?>">
            <?php elseif (file_exists(__DIR__ . '/logo.webp')): ?>
                <img src="logo.webp" class="brand-logo-img" alt="<?= htmlspecialchars('Logitech') ?>">
            <?php else: ?>
                <span class="brand-logo-text" data-editable="true"><?= htmlspecialchars('Logitech') ?></span>
            <?php endif; ?>
        </div>

        <div class="nav-right">
            <button class="cart-trigger" onclick="toggleCart()" title="Ver Carrito">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="9" cy="21" r="1"></circle>
                    <circle cx="20" cy="21" r="1"></circle>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                </svg>
                <span class="cart-badge-count" id="cartBadge" style="display:none;">0</span>
            </button>
        </div>
    </nav>

    <!-- MENÚ HAMBURGUESA DESPLEGABLE -->
    <div class="nav-menu-overlay" id="navMenuOverlay" onclick="if(event.target===this) toggleNavMenu()">
        <div class="nav-menu-drawer">
            <div class="nav-menu-header">
                <div class="nav-menu-brand">
                    <?php if (file_exists(__DIR__ . '/logo.svg')): ?>
                        <img src="logo.svg" style="height:40px; max-width:130px; object-fit:contain; display:block;" alt="<?= htmlspecialchars('Logitech') ?>">
                    <?php elseif (file_exists(__DIR__ . '/logo.webp')): ?>
                        <img src="logo.webp" style="height:40px; max-width:130px; object-fit:contain; display:block;" alt="<?= htmlspecialchars('Logitech') ?>">
                    <?php else: ?>
                        <span class="nav-menu-brand-text"><?= htmlspecialchars('Logitech') ?></span>
                    <?php endif; ?>
                </div>
                <button class="nav-menu-close-btn" onclick="toggleNavMenu()" aria-label="Cerrar menú">✕</button>
            </div>
            <nav class="nav-menu-links">
                <a href="#productSection" class="nav-menu-link" onclick="navegarSeccion(event, 'productSection')">
                    Producto
                </a>
                <a href="#videoReviewsSection" class="nav-menu-link" onclick="navegarSeccion(event, 'videoReviewsSection')">
                    Video Reviews
                </a>
                <a href="#customerReviewsSection" class="nav-menu-link" onclick="navegarSeccion(event, 'customerReviewsSection')">
                    Customer Reviews
                </a>
                <a href="#recommendedProductsSection" class="nav-menu-link" onclick="navegarSeccion(event, 'recommendedProductsSection')">
                    Quienes vieron este producto
                </a>
            </nav>
        </div>
    </div>

    <!-- SEPARACION GRIS ENTRE NAVBAR Y GALERIA: aloja el titulo -->
    <div class="navbar-gallery-spacer">
        <div class="spacer-head">
            <h1 class="product-title" data-editable="true"><?= htmlspecialchars('Logitech G PRO X2 SUPERSTRIKE') ?></h1>
            <div class="rating-row">
                <span class="rating-number">4.5</span>
                <div class="stars-container">★★★★★</div>
                <span class="reviews-count" data-editable="true">(568)</span>
            </div>
        </div>
        <?php if (trim($compras_mes) !== ''): ?>
        <div class="bought-month"><strong><?= htmlspecialchars($compras_mes) ?> comprados</strong> el mes pasado</div>
        <?php endif; ?>
    </div>

    <!-- 4. CONTENIDO PRINCIPAL -->
    <main class="landing-container" id="productSection">
        <div class="product-grid-layout">

            <!-- COLUMNA 1: GALERÍA CON SLIDE Y PUNTICOS INDICADORES -->
            <section class="gallery-wrapper-desktop">
                <!-- MINIATURAS DESKTOP (IZQUIERDA) -->
                <div class="gallery-thumbnails-strip" id="galleryThumbsStrip"></div>

                <div class="gallery-slider-container">
                    <div class="main-image-wrap" id="mainGallerySlider" onclick="abrirLightbox(activeImgIndex)" title="Haz clic para ampliar">
                        <img id="mainImage" src="img/img_1.jpg" alt="<?= htmlspecialchars('Logitech G PRO X2 SUPERSTRIKE') ?>">
                    </div>
                    <!-- PUNTICOS INDICADORES DE LA GALERÍA (solo móvil) -->
                    <div class="gallery-dots-indicator" id="galleryDotsIndicator"></div>
                </div>
            </section>

            <!-- COLUMNA 2: INFORMACIÓN Y COMPRA -->
            <section class="product-info">

                <!-- BADGE DE ÚLTIMAS UNIDADES ARRIBA DEL PRECIO -->
                <div class="stock-urgency-badge" data-editable="true">ÚLTIMAS UNIDADES</div>

                <div class="price-row">
                    <span class="current-price" data-editable="true">$ 249.060</span>
                    <span class="old-price" data-editable="true">$ 602.350</span>
                    <span class="discount-pill" data-editable="true">-59% OFF</span>
                </div>
                <?php
                    /* Puntos Colombia: 1 punto por cada $700 de compra (tasa
                       oficial de acumulacion). Se calcula del precio real de la
                       landing, asi que cambia solo si cambia el precio. */
                    $pc_puntos = (int)floor(((int)$precio_num) / PUNTOS_COLOMBIA_PESOS_POR_PUNTO);
                ?>
                <?php if ($pc_puntos > 0): ?>
                <div class="puntos-colombia-row">
                    <!-- Marca oficial de Puntos Colombia en morado de marca -->
                    <svg class="pc-mark" width="22" height="22" viewBox="0 0 30 30" aria-hidden="true">
                        <rect width="30" height="30" rx="4" fill="#662D91"/>
                        <path d="M5.83,25H.626A.627.627,0,0,1,0,24.374V10.461A10.655,10.655,0,0,1,10.651,0l.206,0a10.65,10.65,0,0,1,7.377,18.126A10.66,10.66,0,0,1,6.844,20.6a.289.289,0,0,0-.1-.019.285.285,0,0,0-.285.284v3.509A.626.626,0,0,1,5.83,25ZM10.977,5.113a5.155,5.155,0,0,0-3.9,1.5A5.648,5.648,0,0,0,5.646,10.65a5.7,5.7,0,0,0,1.4,4.032,4.934,4.934,0,0,0,3.8,1.5,4.958,4.958,0,0,0,3.3-1.1,4.211,4.211,0,0,0,1.5-2.879H13.293a2.388,2.388,0,0,1-2.467,2.005,2.444,2.444,0,0,1-2.05-.987,4.108,4.108,0,0,1-.752-2.574A4.127,4.127,0,0,1,8.79,8.068a2.46,2.46,0,0,1,2.066-.98,2.4,2.4,0,0,1,2.437,2h2.362a4.408,4.408,0,0,0-1.481-2.886A4.758,4.758,0,0,0,10.977,5.113Z"
                              transform="translate(5.6 3.2) scale(0.79)" fill="#fff"/>
                    </svg>
                    <span class="pc-text">Acumulas hasta <b><?= number_format($pc_puntos, 0, ',', '.') ?></b> Puntos Colombia</span>
                    <svg class="pc-info" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round" role="img"
                         aria-label="Acumulas 1 punto por cada $<?= number_format(PUNTOS_COLOMBIA_PESOS_POR_PUNTO, 0, ',', '.') ?> de compra">
                        <title>Acumulas 1 Punto Colombia por cada $<?= number_format(PUNTOS_COLOMBIA_PESOS_POR_PUNTO, 0, ',', '.') ?> de compra.</title>
                        <circle cx="12" cy="12" r="9"/><path d="M12 11v5"/>
                        <circle cx="12" cy="7.6" r="1" fill="currentColor" stroke="none"/>
                    </svg>
                </div>
                <?php endif; ?>


                <!-- CAJA DE ENVÍO URGENTE Y CONTADOR PERSISTENTE -->
                <div class="apple-shipping-urgency-box">
                    <div class="shipping-lead-row">
                        <svg class="shipping-flash-icon" viewBox="0 0 24 24" width="20" height="20" fill="#f59e0b" stroke="#f59e0b" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
                        </svg>
                        <div class="shipping-lead-text">
                            <span class="shipping-badge-highlight" data-editable="true">Llega gratis <b>mañana</b></span>
                            <div class="shipping-timer-subtext">
                                <span data-editable="true">Comprando dentro de las próximas</span> <span class="shipping-countdown-val" id="shippingCountdown">20 h 40 min 00 s</span>
                            </div>
                        </div>
                    </div>
                </div>

                

                <div class="size-block">
                    <div class="variant-header"><div class="variant-label">Presentación:</div></div>
                    <div class="size-pills-row">
                        <button class="size-pill" data-editable="true"><?= htmlspecialchars('1 Unidad') ?></button>
                    </div>
                </div>

                <div class="desktop-action-row">
                    <div class="qty-controls-desktop">
                        <button class="qty-btn-desktop" onclick="cambiarCantidad(-1)">-</button>
                        <span class="qty-val-desktop" id="qtyDesktopDisplay">1</span>
                        <button class="qty-btn-desktop" onclick="cambiarCantidad(1)">+</button>
                    </div>
                    <button class="btn-add-desktop" onclick="agregarAlCarrito(event)" data-editable="true">
                        Añadir al carro
                    </button>
                </div>

                <div class="accordion-item">
                    <button class="accordion-header" onclick="toggleAccordion(this)">
                        <span data-editable="true">Descripción y Beneficios</span>
                        <span>▾</span>
                    </button>
                    <div class="accordion-body open"><p data-editable="true">Diseñado con profesionales, diseñado para ganar: diseñado junto con los mejores atletas de deportes electrónicos del mundo, el mouse inalámbrico Logitech G PRO X2 SUPERSTRIKE ofrece el clic más rápido y totalmente personalizable Domina con una velocidad líder en la industria: clics 30 ms más rápidos para obtener el máximo rendimiento en cada partido de deportes electrónicos y personalización profunda con puntos de actuación de 10 niveles y reinicio rápido del gatillo de 5 niveles Comentarios hápticos: este innovador mouse háptico para juegos con sistema de disparo inductivo háptico (HITS) proporciona retroalimentación en tiempo real para una experiencia inmersiva inigualable para cualquier escenario de juego o estilo de juego Precisión desde dentro: el sensor HERO 2 de este mouse para juegos de PC ofrece seguimiento a más de 888 IPS, 3.10 oz de fuerza y hasta 44,000 DPI, asegurando la precisión milimétrica en la que confían los campeones para cada jugada Juega más tiempo: con 60-90 horas de duración de la batería y LIGHTSPEED inalámbrico, este mouse recargable para juegos (con cable USB-A a USB-C incluido) ofrece sondeo de 8 kHz sin retrasos para un enfoque ininterrumpido Ultraduradero, este mouse ligero está construido con una carcasa de pared delgada de 0.028 in y pies UHMWPE deslizantes extragrandes para un movimiento sin esfuerzo PRO X2 SUPERSTRIKE es compatible con PC y Mac; POWERPLAY 2 habilitado para un juego inalámbrico infinito, sin interrupciones ni preocupaciones de carga (1) Las características avanzadas requieren el software Logitech G HUB; rendimiento de sondeo probado en la alfombrilla de mouse G640; (2) POWERPLAY se vende por separado</p>
                    </div>
                </div>

                <div class="accordion-item">
                    <button class="accordion-header" onclick="toggleAccordion(this)">
                        <span data-editable="true">Garantía y Devoluciones</span>
                        <span>▾</span>
                    </button>
                    <div class="accordion-body">
                        <p data-editable="true">Todos nuestros productos cuentan con garantia de 30 dias contra defectos de fabrica. Si no estas 100% satisfecho(a), te devolvemos tu dinero.</p>
                    </div>
                </div>

                <!-- SECURE PAYMENT -->
                <div class="secure-trust-box">
                    <div class="secure-trust-header">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                            <path d="m9 12 2 2 4-4"></path>
                        </svg>
                        <span data-editable="true">Secure Payment</span>
                    </div>
                    <ul class="secure-trust-list">
                        <li><span class="check-icon">✓</span> <span data-editable="true"><b>Pago Contra Entrega disponible:</b> Paga en efectivo cuando recibas tu pedido en la puerta de tu casa.</span></li>
                        <li><span class="check-icon">✓</span> <span data-editable="true">Tus datos y compras están protegidos con cifrado de seguridad.</span></li>
                        <li><span class="check-icon">✓</span> <span data-editable="true"><?= htmlspecialchars('Logitech') ?> comparte información de pago únicamente con proveedores de pago confiables comprometidos con proteger tus datos.</span></li>
                    </ul>
                </div>
            </section>

        </div>
    </main>

    <!-- 5.2 REVIEWS WITH VIDEOS CAROUSEL (AMAZON STYLE) -->
    <section class="video-reviews-section" id="videoReviewsSection">
        <div class="video-reviews-header">
            <div class="video-reviews-title-wrap">
                <h2 class="video-reviews-main-title" data-editable="true">Reviews with videos</h2>
                <span class="video-reviews-subtitle" data-editable="true">Opiniones y unboxings en video de clientes verificados</span>
            </div>
            <div class="video-reviews-controls">
                <?php if ($es_modo_edicion): ?>
                    <button type="button" class="btn-add-video-card" onclick="agregarNuevoVideoReview()">➕ Agregar Video</button>
                <?php endif; ?>
            </div>
        </div>

        <div class="video-reviews-carousel-track" id="videoReviewsTrack">
            <div class="video-review-card" data-youtube-id="D0O7ogAqEgk" data-video-title="" onclick="manejarClickVideoCard(this, event)">
                <img class="video-card-thumb" src="https://i.ytimg.com/vi/D0O7ogAqEgk/hqdefault.jpg" referrerpolicy="no-referrer" alt="" loading="lazy">
                <div class="video-card-gradient"></div>
                <div class="video-card-badge-play">▶</div>
                <div class="video-card-info">
                    <div class="video-card-stars">★★★★★</div>
                    
                    <div class="video-card-title-text" data-editable="true"></div>
                </div>
                <?php if ($es_modo_edicion): ?>
                <div class="video-card-admin-bar" onclick="event.stopPropagation()">
                    <button type="button" class="btn-vcard-edit" onclick="editarVideoCard(this.closest('.video-review-card'))" title="Editar link de YouTube">✏️ Editar</button>
                    <button type="button" class="btn-vcard-del" onclick="eliminarVideoCard(this.closest('.video-review-card'))" title="Eliminar video">🗑️</button>
                </div>
                <?php endif; ?>
            </div>
            <div class="video-review-card" data-youtube-id="9cVWD6-BMmA" data-video-title="" onclick="manejarClickVideoCard(this, event)">
                <img class="video-card-thumb" src="https://i.ytimg.com/vi/9cVWD6-BMmA/hqdefault.jpg" referrerpolicy="no-referrer" alt="" loading="lazy">
                <div class="video-card-gradient"></div>
                <div class="video-card-badge-play">▶</div>
                <div class="video-card-info">
                    <div class="video-card-stars">★★★★★</div>
                    
                    <div class="video-card-title-text" data-editable="true"></div>
                </div>
                <?php if ($es_modo_edicion): ?>
                <div class="video-card-admin-bar" onclick="event.stopPropagation()">
                    <button type="button" class="btn-vcard-edit" onclick="editarVideoCard(this.closest('.video-review-card'))" title="Editar link de YouTube">✏️ Editar</button>
                    <button type="button" class="btn-vcard-del" onclick="eliminarVideoCard(this.closest('.video-review-card'))" title="Eliminar video">🗑️</button>
                </div>
                <?php endif; ?>
            </div>
            <div class="video-review-card" data-youtube-id="WaDwGGCcQzE" data-video-title="" onclick="manejarClickVideoCard(this, event)">
                <img class="video-card-thumb" src="https://i.ytimg.com/vi/WaDwGGCcQzE/hqdefault.jpg" referrerpolicy="no-referrer" alt="" loading="lazy">
                <div class="video-card-gradient"></div>
                <div class="video-card-badge-play">▶</div>
                <div class="video-card-info">
                    <div class="video-card-stars">★★★★★</div>
                    
                    <div class="video-card-title-text" data-editable="true"></div>
                </div>
                <?php if ($es_modo_edicion): ?>
                <div class="video-card-admin-bar" onclick="event.stopPropagation()">
                    <button type="button" class="btn-vcard-edit" onclick="editarVideoCard(this.closest('.video-review-card'))" title="Editar link de YouTube">✏️ Editar</button>
                    <button type="button" class="btn-vcard-del" onclick="eliminarVideoCard(this.closest('.video-review-card'))" title="Eliminar video">🗑️</button>
                </div>
                <?php endif; ?>
            </div>
            <div class="video-review-card" data-youtube-id="c_81Ij5MCRE" data-video-title="" onclick="manejarClickVideoCard(this, event)">
                <img class="video-card-thumb" src="https://i.ytimg.com/vi/c_81Ij5MCRE/hqdefault.jpg" referrerpolicy="no-referrer" alt="" loading="lazy">
                <div class="video-card-gradient"></div>
                <div class="video-card-badge-play">▶</div>
                <div class="video-card-info">
                    <div class="video-card-stars">★★★★★</div>
                    
                    <div class="video-card-title-text" data-editable="true"></div>
                </div>
                <?php if ($es_modo_edicion): ?>
                <div class="video-card-admin-bar" onclick="event.stopPropagation()">
                    <button type="button" class="btn-vcard-edit" onclick="editarVideoCard(this.closest('.video-review-card'))" title="Editar link de YouTube">✏️ Editar</button>
                    <button type="button" class="btn-vcard-del" onclick="eliminarVideoCard(this.closest('.video-review-card'))" title="Eliminar video">🗑️</button>
                </div>
                <?php endif; ?>
            </div>
            <div class="video-review-card" data-youtube-id="bFWOTh1szQA" data-video-title="" onclick="manejarClickVideoCard(this, event)">
                <img class="video-card-thumb" src="https://i.ytimg.com/vi/bFWOTh1szQA/hqdefault.jpg" referrerpolicy="no-referrer" alt="" loading="lazy">
                <div class="video-card-gradient"></div>
                <div class="video-card-badge-play">▶</div>
                <div class="video-card-info">
                    <div class="video-card-stars">★★★★★</div>
                    
                    <div class="video-card-title-text" data-editable="true"></div>
                </div>
                <?php if ($es_modo_edicion): ?>
                <div class="video-card-admin-bar" onclick="event.stopPropagation()">
                    <button type="button" class="btn-vcard-edit" onclick="editarVideoCard(this.closest('.video-review-card'))" title="Editar link de YouTube">✏️ Editar</button>
                    <button type="button" class="btn-vcard-del" onclick="eliminarVideoCard(this.closest('.video-review-card'))" title="Eliminar video">🗑️</button>
                </div>
                <?php endif; ?>
            </div>
            <div class="video-review-card" data-youtube-id="aSbRPrw9ZTU" data-video-title="" onclick="manejarClickVideoCard(this, event)">
                <img class="video-card-thumb" src="https://i.ytimg.com/vi/aSbRPrw9ZTU/hqdefault.jpg" referrerpolicy="no-referrer" alt="" loading="lazy">
                <div class="video-card-gradient"></div>
                <div class="video-card-badge-play">▶</div>
                <div class="video-card-info">
                    <div class="video-card-stars">★★★★★</div>
                    
                    <div class="video-card-title-text" data-editable="true"></div>
                </div>
                <?php if ($es_modo_edicion): ?>
                <div class="video-card-admin-bar" onclick="event.stopPropagation()">
                    <button type="button" class="btn-vcard-edit" onclick="editarVideoCard(this.closest('.video-review-card'))" title="Editar link de YouTube">✏️ Editar</button>
                    <button type="button" class="btn-vcard-del" onclick="eliminarVideoCard(this.closest('.video-review-card'))" title="Eliminar video">🗑️</button>
                </div>
                <?php endif; ?>
            </div>
            <div class="video-review-card" data-youtube-id="zpQeYUnSdfw" data-video-title="" onclick="manejarClickVideoCard(this, event)">
                <img class="video-card-thumb" src="https://i.ytimg.com/vi/zpQeYUnSdfw/hqdefault.jpg" referrerpolicy="no-referrer" alt="" loading="lazy">
                <div class="video-card-gradient"></div>
                <div class="video-card-badge-play">▶</div>
                <div class="video-card-info">
                    <div class="video-card-stars">★★★★★</div>
                    
                    <div class="video-card-title-text" data-editable="true"></div>
                </div>
                <?php if ($es_modo_edicion): ?>
                <div class="video-card-admin-bar" onclick="event.stopPropagation()">
                    <button type="button" class="btn-vcard-edit" onclick="editarVideoCard(this.closest('.video-review-card'))" title="Editar link de YouTube">✏️ Editar</button>
                    <button type="button" class="btn-vcard-del" onclick="eliminarVideoCard(this.closest('.video-review-card'))" title="Eliminar video">🗑️</button>
                </div>
                <?php endif; ?>
            </div>
            <div class="video-review-card" data-youtube-id="Pkfkl833ldE" data-video-title="" onclick="manejarClickVideoCard(this, event)">
                <img class="video-card-thumb" src="https://i.ytimg.com/vi/Pkfkl833ldE/hqdefault.jpg" referrerpolicy="no-referrer" alt="" loading="lazy">
                <div class="video-card-gradient"></div>
                <div class="video-card-badge-play">▶</div>
                <div class="video-card-info">
                    <div class="video-card-stars">★★★★★</div>
                    
                    <div class="video-card-title-text" data-editable="true"></div>
                </div>
                <?php if ($es_modo_edicion): ?>
                <div class="video-card-admin-bar" onclick="event.stopPropagation()">
                    <button type="button" class="btn-vcard-edit" onclick="editarVideoCard(this.closest('.video-review-card'))" title="Editar link de YouTube">✏️ Editar</button>
                    <button type="button" class="btn-vcard-del" onclick="eliminarVideoCard(this.closest('.video-review-card'))" title="Eliminar video">🗑️</button>
                </div>
                <?php endif; ?>
            </div>
            <div class="video-review-card" data-youtube-id="P8TwYZqTbRg" data-video-title="" onclick="manejarClickVideoCard(this, event)">
                <img class="video-card-thumb" src="https://i.ytimg.com/vi/P8TwYZqTbRg/hqdefault.jpg" referrerpolicy="no-referrer" alt="" loading="lazy">
                <div class="video-card-gradient"></div>
                <div class="video-card-badge-play">▶</div>
                <div class="video-card-info">
                    <div class="video-card-stars">★★★★★</div>
                    
                    <div class="video-card-title-text" data-editable="true"></div>
                </div>
                <?php if ($es_modo_edicion): ?>
                <div class="video-card-admin-bar" onclick="event.stopPropagation()">
                    <button type="button" class="btn-vcard-edit" onclick="editarVideoCard(this.closest('.video-review-card'))" title="Editar link de YouTube">✏️ Editar</button>
                    <button type="button" class="btn-vcard-del" onclick="eliminarVideoCard(this.closest('.video-review-card'))" title="Eliminar video">🗑️</button>
                </div>
                <?php endif; ?>
            </div>
            <div class="video-review-card" data-youtube-id="bt5heQX09gY" data-video-title="" onclick="manejarClickVideoCard(this, event)">
                <img class="video-card-thumb" src="https://i.ytimg.com/vi/bt5heQX09gY/hqdefault.jpg" referrerpolicy="no-referrer" alt="" loading="lazy">
                <div class="video-card-gradient"></div>
                <div class="video-card-badge-play">▶</div>
                <div class="video-card-info">
                    <div class="video-card-stars">★★★★★</div>
                    
                    <div class="video-card-title-text" data-editable="true"></div>
                </div>
                <?php if ($es_modo_edicion): ?>
                <div class="video-card-admin-bar" onclick="event.stopPropagation()">
                    <button type="button" class="btn-vcard-edit" onclick="editarVideoCard(this.closest('.video-review-card'))" title="Editar link de YouTube">✏️ Editar</button>
                    <button type="button" class="btn-vcard-del" onclick="eliminarVideoCard(this.closest('.video-review-card'))" title="Eliminar video">🗑️</button>
                </div>
                <?php endif; ?>
            </div>
            <div class="video-review-card" data-youtube-id="p2fYTXZhwtc" data-video-title="" onclick="manejarClickVideoCard(this, event)">
                <img class="video-card-thumb" src="https://i.ytimg.com/vi/p2fYTXZhwtc/hqdefault.jpg" referrerpolicy="no-referrer" alt="" loading="lazy">
                <div class="video-card-gradient"></div>
                <div class="video-card-badge-play">▶</div>
                <div class="video-card-info">
                    <div class="video-card-stars">★★★★★</div>
                    
                    <div class="video-card-title-text" data-editable="true"></div>
                </div>
                <?php if ($es_modo_edicion): ?>
                <div class="video-card-admin-bar" onclick="event.stopPropagation()">
                    <button type="button" class="btn-vcard-edit" onclick="editarVideoCard(this.closest('.video-review-card'))" title="Editar link de YouTube">✏️ Editar</button>
                    <button type="button" class="btn-vcard-del" onclick="eliminarVideoCard(this.closest('.video-review-card'))" title="Eliminar video">🗑️</button>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- VIDEO MODAL LIGHTBOX -->
    <div id="videoModalLightbox" class="video-modal-backdrop" onclick="cerrarVideoModal(event)">
        <div class="video-modal-container" onclick="event.stopPropagation()">
            <button type="button" class="video-modal-close-btn" onclick="cerrarVideoModal(event)" aria-label="Cerrar video">✕</button>
            <div class="video-modal-iframe-wrapper">
                <iframe id="videoModalIframe" src="" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
            </div>
        </div>
    </div>

    <!-- 5.5 CUSTOMER REVIEWS SECTION (ESTILO AMAZON) -->
    <section class="customer-reviews-section" id="customerReviewsSection">
        <div class="customer-reviews-grid">
            <!-- COLUMNA IZQUIERDA: RESUMEN AMAZON-STYLE -->
            <div class="reviews-summary-card" id="reviewsSummaryCard">
                <h2 class="reviews-sidebar-title" data-editable="true">Opiniones de clientes</h2>
                
                <div class="reviews-score-hero">
                    <div class="reviews-stars-hero" id="reviewsHeroStars">★★★★★</div>
                    <div class="reviews-score-text">
                        <span id="scoreAvgDisplay">4.5</span> de 5
                    </div>
                </div>
                
                <div class="reviews-total-ratings-sub" id="reviewsTotalCountSub">568 calificaciones globales</div>

                <div class="reviews-sidebar-divider"></div>

                <!-- SECCIÓN ESCRIBIR OPINIÓN -->
                <div class="write-review-block">
                    <h3 class="write-review-title">Escribir opinión de este producto</h3>
                    <p class="write-review-subtitle">Comparte tu opinión con otros clientes</p>
                    <button type="button" class="btn-write-review" onclick="abrirModalEscribirOpinion()">
                        Escribir mi opinión
                    </button>
                </div>
            </div>

            <!-- COLUMNA DERECHA: FILTROS Y LISTA DE OPINIONES -->
            <div class="reviews-feed-column">
                <div class="reviews-filters-row">
                    <div class="filters-left-group">
                        
                        <div class="review-filter-pill">
                            <span>Calificación</span>
                            <select class="filter-select-box" id="filterRating" onchange="renderReviews()">
                                <option value="All">Todas</option>
                                <option value="5">5 Estrellas</option>
                                <option value="4">4 Estrellas</option>
                                <option value="3">3 Estrellas</option>
                            </select>
                        </div>
                    </div>

                    <div class="filters-right-group">
                        <div class="review-filter-pill">
                            <span>Ordenar por</span>
                            <select class="filter-select-box" id="filterSort" onchange="renderReviews()">
                                <option value="Default">Predeterminado</option>
                                <option value="Most Recent">Más recientes</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="reviews-list-wrap" id="reviewsListContainer"></div>

                <div class="reviews-pagination-row" id="reviewsPaginationContainer"></div>
            </div>
        </div>
    </section>

    <!-- MODAL INTERACTIVO PARA ESCRIBIR OPINIÓN Y VERIFICAR COMPRA -->
    <div class="write-review-modal-overlay" id="writeReviewModal" onclick="if(event.target===this) cerrarModalEscribirOpinion()">
        <div class="write-review-modal-dialog">
            <div class="write-review-modal-header">
                <h3 id="modalReviewHeaderTitle">Escribir opinión</h3>
                <button type="button" class="modal-close-btn" onclick="cerrarModalEscribirOpinion()" aria-label="Cerrar">✕</button>
            </div>

            <!-- VISTA 1: FORMULARIO PRINCIPAL DE OPINIÓN CON ENUNCIADO DE COMPRADOR VERIFICADO -->
            <div id="reviewModalViewWrite" class="review-modal-view active">
                <!-- ENUNCIADO DE COMPRADOR VERIFICADO CON 10% DE DESCUENTO -->
                <div class="verified-buyer-promo-box">
                    <div class="promo-box-badge">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:-1px; margin-right:4px;">
                            <path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"></path>
                            <path d="M13 5v2"></path>
                            <path d="M13 17v2"></path>
                            <path d="M13 11v2"></path>
                        </svg>
                        <span>Descuento Exclusivo</span>
                    </div>
                    <div class="promo-box-title">¿Ya te llegó el producto?</div>
                    <p class="promo-box-text">¡Danos la opinión de el producto como comprador verificado y te hacemos un <span class="discount-blue-underlined">10% de descuento</span> en tu siguiente compra!</p>
                    <div class="promo-box-terms">*sujeto a términos y condiciones*</div>
                    <button type="button" class="btn-verify-purchase-trigger" onclick="mostrarVistaVerificarCompra()">
                        Verificar compra
                    </button>
                </div>

                <form id="writeReviewForm" onsubmit="guardarNuevaOpinion(event)" class="write-review-form">
                    <!-- PUNTUACIÓN DE ESTRELLAS -->
                    <div class="form-group">
                        <label class="form-label">Calificación general <span class="required">*</span></label>
                        <div class="star-rating-picker" id="starRatingPicker">
                            <span class="star-pick selected" data-val="1" onmouseover="hoverStars(1)" onmouseout="resetStars()" onclick="selectStars(1)">★</span>
                            <span class="star-pick selected" data-val="2" onmouseover="hoverStars(2)" onmouseout="resetStars()" onclick="selectStars(2)">★</span>
                            <span class="star-pick selected" data-val="3" onmouseover="hoverStars(3)" onmouseout="resetStars()" onclick="selectStars(3)">★</span>
                            <span class="star-pick selected" data-val="4" onmouseover="hoverStars(4)" onmouseout="resetStars()" onclick="selectStars(4)">★</span>
                            <span class="star-pick selected" data-val="5" onmouseover="hoverStars(5)" onmouseout="resetStars()" onclick="selectStars(5)">★</span>
                            <span class="star-rating-text" id="starRatingLabel">Excelente (5 de 5)</span>
                        </div>
                        <input type="hidden" id="reviewRatingInput" value="5">
                    </div>

                    <!-- NOMBRE -->
                    <div class="form-group">
                        <label class="form-label" for="reviewAuthorInput">Tu nombre o alias <span class="required">*</span></label>
                        <input type="text" id="reviewAuthorInput" class="form-input" placeholder="Ej. Carlos M. o Andrés Gómez" required maxlength="40">
                    </div>

                    <!-- TÍTULO -->
                    <div class="form-group">
                        <label class="form-label" for="reviewTitleInput">Título de la reseña <span class="optional">(opcional)</span></label>
                        <input type="text" id="reviewTitleInput" class="form-input" placeholder="Ej. ¡Excelente estabilización y calidad en 4K!" maxlength="80">
                    </div>

                    <!-- COMENTARIO -->
                    <div class="form-group">
                        <label class="form-label" for="reviewCommentInput">Escribe tu opinión <span class="required">*</span></label>
                        <textarea id="reviewCommentInput" class="form-textarea" rows="4" placeholder="¿Qué te pareció el producto? ¿Cómo fue tu experiencia de uso y envío?" required minlength="6" maxlength="800"></textarea>
                    </div>

                    <div class="write-review-modal-actions">
                        <button type="button" class="btn-review-cancel" onclick="cerrarModalEscribirOpinion()">Cancelar</button>
                        <button type="submit" class="btn-review-submit">Publicar opinión</button>
                    </div>
                </form>
            </div>

            <!-- VISTA 2: FORMULARIO DE VERIFICACIÓN DE COMPRA -->
            <div id="reviewModalViewVerify" class="review-modal-view">
                <div class="verify-buyer-header">
                    <h4>Verificación de Compra</h4>
                    <dotlottie-player src="https://lottie.host/ee25be13-6ccf-4bae-be53-1813b28bca0a/XXHRxw0szZ.lottie" background="transparent" speed="1" style="width: 140px; height: 140px; margin: 8px auto 12px auto; display: block;" autoplay loop></dotlottie-player>
                    <p>Ingresa tus datos para validar tu compra y activar tu <span class="discount-blue-underlined">10% de descuento</span>.</p>
                </div>
                <form id="verifyBuyerForm" onsubmit="ejecutarVerificacionCompra(event)" class="write-review-form">
                    <div class="form-group">
                        <label class="form-label" for="verifyReceiptNumber">Número de recibo <span class="required">*</span></label>
                        <input type="text" id="verifyReceiptNumber" class="form-input" placeholder="Ej. REC-984210" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="verifyBuyerName">Nombre <span class="required">*</span></label>
                        <input type="text" id="verifyBuyerName" class="form-input" placeholder="Ej. Juan Pérez" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="verifyBuyerEmail">Correo <span class="required">*</span></label>
                        <input type="email" id="verifyBuyerEmail" class="form-input" placeholder="tu-correo@ejemplo.com" required>
                    </div>
                    <div class="write-review-modal-actions">
                        <button type="button" class="btn-review-cancel" onclick="mostrarVistaEscribirOpinion()">Volver</button>
                        <button type="submit" class="btn-review-submit" id="btnDoVerify">Verificar</button>
                    </div>
                </form>
            </div>

            <!-- VISTA 3: RESULTADO (NO ADQUIRIDO + UPSELL) -->
            <div id="reviewModalViewUpsell" class="review-modal-view">
                <div class="upsell-result-box">
                    <div class="upsell-logo-wrap">
                        <?php if (file_exists(__DIR__ . '/logo.svg')): ?>
                            <img src="logo.svg" alt="<?= htmlspecialchars($nombre_marca) ?>" class="upsell-brand-logo">
                        <?php elseif (file_exists(__DIR__ . '/logo.webp')): ?>
                            <img src="logo.webp" alt="<?= htmlspecialchars($nombre_marca) ?>" class="upsell-brand-logo">
                        <?php elseif (file_exists(__DIR__ . '/logo.png')): ?>
                            <img src="logo.png" alt="<?= htmlspecialchars($nombre_marca) ?>" class="upsell-brand-logo">
                        <?php else: ?>
                            <h2 class="upsell-brand-logo-text"><?= htmlspecialchars($nombre_marca) ?></h2>
                        <?php endif; ?>
                    </div>
                    <h4 class="upsell-title">Ups, al parecer no has adquirido nuestro producto.</h4>
                    <p class="upsell-question">¿Qué esperas?</p>
                    <p class="upsell-desc">Añádelo ahora a tu carro y aprovecha envío gratis a toda Colombia más despacho prioritario hoy mismo.</p>
                    
                    <div class="upsell-actions-row">
                        <button type="button" class="btn-upsell-cart" onclick="ejecutarCompraDesdeModal(event)">
                            Añadir al carrito
                        </button>
                        <button type="button" class="btn-upsell-review" onclick="mostrarVistaEscribirOpinion()">
                            Escribir opinión
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- 5. BANNER OFICIAL ESTILO MERCADOLIBRE (ANTES DE PRODUCTOS RECOMENDADOS) -->
    <a class="ml-promo-banner-wrap"
       href="<?= htmlspecialchars($url_pasarela_meli) ?>/index.php?token=<?= $landing_token ?>">
        <div class="ml-banner-inner">
            <div class="ml-banner-left">
                <?php if (file_exists(__DIR__ . '/mercadito.webp')): ?>
                    <img src="mercadito.webp" alt="Mercado Libre" class="ml-logo-img">
                <?php elseif (file_exists(__DIR__ . '/../../mercadito.webp')): ?>
                    <img src="../../mercadito.webp" alt="Mercado Libre" class="ml-logo-img">
                <?php else: ?>
                    <img src="/mercadito.webp" alt="Mercado Libre" class="ml-logo-img">
                <?php endif; ?>
            </div>

            <div class="ml-banner-divider"></div>

            <div class="ml-banner-center">
                <span class="ml-kicker">
                    <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M23 12l-2.44-2.79.34-3.69-3.61-.82-1.89-3.2L12 2.96 8.6 1.5 6.71 4.69 3.1 5.5l.34 3.7L1 12l2.44 2.79-.34 3.7 3.61.82L8.6 22.5l3.4-1.47 3.4 1.46 1.89-3.19 3.61-.82-.34-3.69L23 12zm-12.91 4.72l-3.8-3.81 1.48-1.48 2.32 2.33 5.85-5.87 1.48 1.48-7.33 7.35z"></path></svg>
                    Distribuidor autorizado
                </span>
                <span class="ml-product-name"><?= htmlspecialchars('Logitech G PRO X2 SUPERSTRIKE') ?></span>
                <span class="ml-trust-line">Compra Protegida &middot; Pagas con Mercado Pago</span>
            </div>

            <div class="ml-banner-right">
                <?php
                    $ml_ship_img = null;
                    foreach (['envio_gratis.png', 'envio_gratis.webp', 'envio_gratis.svg', 'envio_gratis.jpg'] as $__f) {
                        if (file_exists(__DIR__ . '/' . $__f))            { $ml_ship_img = $__f; break; }
                        if (file_exists(__DIR__ . '/../../' . $__f))      { $ml_ship_img = '../../' . $__f; break; }
                    }
                ?>
                <?php if ($ml_ship_img): ?>
                    <img src="<?= htmlspecialchars($ml_ship_img) ?>" alt="Envío gratis en tu primera compra" class="ml-shipping-img">
                <?php else: ?>
                    <div class="ml-ship-pill">
                        <span class="pill-dark">ENVÍO GRATIS</span>
                        <span class="pill-white">EN TU <b>PRIMERA COMPRA</b></span>
                    </div>
                <?php endif; ?>

                <span class="ml-cta">Comprar en web de Mercado Libre</span>
            </div>
        </div>
    </a>

    <!-- 6. SECCIÓN QUIENES VIERON ESTE PRODUCTO TAMBIÉN COMPRARON -->
    <section class="more-to-love-section" id="recommendedProductsSection">
        <h2 class="section-heading-center" data-editable="true">Quienes vieron este producto también compraron</h2>

        <div class="more-slider-wrapper" style="padding: 0 10px;">
            <button type="button" class="more-slider-arrow prev" onclick="slideRecommendedProducts(-1)" aria-label="Anterior">❮</button>
            <div class="more-grid" id="recommendedProductsTrack">
                <?php if (!empty($otros_productos)): ?>
                    <?php foreach ($otros_productos as $o): ?>
                    <a href="<?= htmlspecialchars($o['url']) ?>" class="more-card">
                        <img src="<?= htmlspecialchars($o['img']) ?>" class="more-card-img" alt="<?= htmlspecialchars($o['nombre']) ?>">
                        <div class="more-card-title"><?= htmlspecialchars($o['nombre']) ?></div>
                        <div class="more-card-stars">★★★★★</div>
                        <div class="more-card-price"><?= htmlspecialchars($o['precio'] ?? 'Ver Oferta ➔') ?></div>
                    </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <button type="button" class="more-slider-arrow next" onclick="slideRecommendedProducts(1)" aria-label="Siguiente">❯</button>
        </div>

        <!-- PUNTICOS INDICADORES DEL CARRUSEL DE PRODUCTOS -->
        <div class="more-products-dots" id="recommendedProductsDots"></div>
    </section>

    <!-- 7. FOOTER MODERNO ESTILO SHEGLAM -->
    <footer class="generic-footer">
        <div class="footer-content-wrap">
            <!-- BENEFICIOS / TRUST BAR: 1. PAGA EN LÍNEA, 2. COMPRAS SEGURAS, 3. ACUMULAS PUNTOS COLOMBIA -->
            <div class="footer-trust-benefits-bar">
                <div class="trust-benefit-col">
                    <img src="tarjeta.svg" alt="Paga en línea o en efectivo" class="trust-benefit-icon">
                    <span class="trust-benefit-text" data-editable="true">Paga en línea<br>o en efectivo</span>
                </div>
                <div class="trust-benefit-col">
                    <img src="escudo_candado.svg" alt="Compras seguras" class="trust-benefit-icon">
                    <span class="trust-benefit-text" data-editable="true">Compras<br>seguras</span>
                </div>
                <div class="trust-benefit-col">
                    <img src="puntos_colombia.svg" alt="Acumulas Puntos Colombia" class="trust-benefit-icon">
                    <span class="trust-benefit-text" data-editable="true">Acumulas<br>Puntos Colombia</span>
                </div>
            </div>

            <!-- MEDIOS DE PAGO (AMEX, VISA, MASTE, PSE, NEQUI, MERCADITO, CONTRAENTREGA) CON FONDO BLANCO -->
            <div class="footer-payments-row">
                <!-- AMERICAN EXPRESS -->
                <div class="footer-payment-badge badge-amex" title="American Express">
                    <img src="amex.svg" alt="American Express">
                </div>
                <!-- VISA -->
                <div class="footer-payment-badge badge-visa" title="Visa">
                    <img src="visa.svg" alt="Visa">
                </div>
                <!-- MASTERCARD -->
                <div class="footer-payment-badge badge-master" title="Mastercard">
                    <img src="maste.svg" alt="Mastercard">
                </div>
                <!-- PSE -->
                <div class="footer-payment-badge badge-pse" title="PSE">
                    <img src="pse.png" alt="PSE">
                </div>
                <!-- NEQUI -->
                <div class="footer-payment-badge badge-nequi" title="Nequi">
                    <img src="Nequi_Colombia_logo.svg.webp" alt="Nequi">
                </div>
                <!-- CONTRAENTREGA -->
                <div class="footer-payment-badge badge-contraentrega" title="Pago Contraentrega">
                    <img src="contraentrega.png" alt="Pago Contraentrega">
                </div>
            </div>

            <!-- SUPERINTENDENCIA (BLANCO) & CÁMARA DE COMERCIO -->
            <div class="footer-legal-row">
                <?php if (file_exists(__DIR__ . '/sic_blanco.png')): ?>
                    <div class="footer-sic-badge" title="Superintendencia de Industria y Comercio">
                        <img src="sic_blanco.png" alt="Superintendencia de Industria y Comercio">
                    </div>
                <?php elseif (file_exists(__DIR__ . '/sic.png')): ?>
                    <div class="footer-sic-badge" title="Superintendencia de Industria y Comercio">
                        <img src="sic.png" alt="Superintendencia de Industria y Comercio">
                    </div>
                <?php else: ?>
                    <span class="footer-legal-text" data-editable="true">Superintendencia de Industria y Comercio</span>
                <?php endif; ?>

                <?php if (file_exists(__DIR__ . '/comerciocamara.png')): ?>
                    <div class="footer-camara-badge" title="Cámara Colombiana de Comercio Electrónico">
                        <img src="comerciocamara.png" alt="Cámara Colombiana de Comercio Electrónico">
                    </div>
                <?php else: ?>
                    <span class="footer-legal-text" data-editable="true">Cámara Colombiana de Comercio Electrónico</span>
                <?php endif; ?>
            </div>

            <!-- COPYRIGHT & DATOS LEGALES -->
            <div class="footer-bottom-row">
                <div class="footer-copyright-text">
                    © <?= date('Y') ?> TODOS LOS DERECHOS RESERVADOS<br>
                    <span data-editable="true"><?= htmlspecialchars($nombre_marca ?? "DJI") ?> Store Colombia S.A.S. NIT 901.834.729-3. Avenida El Dorado (Calle 26) N.º 62 - 47, Bogotá, Colombia</span>
                </div>
                <button type="button" class="btn-scroll-top" onclick="window.scrollTo({top: 0, behavior: 'smooth'})" title="Volver arriba" aria-label="Volver arriba">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="18 15 12 9 6 15"></polyline>
                    </svg>
                </button>
            </div>
        </div>
    </footer>

    <!-- 8. STICKY BOTTOM ACTION BAR (MOBILE ONLY) -->
    <div class="sticky-footer-bar">
        

        

        <button class="btn-add-to-cart" id="btnAddToCart" onclick="agregarAlCarrito(event)" data-editable="true">
            Añadir al carro
        </button>
    </div>

    <!-- 9. LIGHTBOX / ZOOM FLOTANTE -->
    <div class="lightbox-modal" id="imageLightbox" onclick="if(event.target===this) cerrarLightbox()">
        <button class="lightbox-close-btn" onclick="cerrarLightbox()" title="Cerrar (Esc)">✕</button>
        <div class="lightbox-main-view">
            <button class="lightbox-nav-btn prev" onclick="cambiarImagenLightbox(-1)">❮</button>
            <img id="lightboxImage" src="" alt="Vista ampliada" onclick="toggleLightboxZoom(event)" onmousemove="actualizarPosicionZoom(event, this)" title="Haz clic para activar/desactivar zoom">
            <button class="lightbox-nav-btn next" onclick="cambiarImagenLightbox(1)">❯</button>
        </div>
        <div class="lightbox-thumbs-row" id="lightboxThumbs"></div>
    </div>

    <!-- 10. SHOPPING CART DRAWER -->
    <div class="cart-overlay" id="cartOverlay" onclick="if(event.target===this) toggleCart()">
        <div class="cart-drawer">
            <div class="cart-header">
                <h3 id="cartDrawerTitle">Tu Carrito (0)</h3>
                <button class="close-cart-btn" onclick="toggleCart()">✕</button>
            </div>
            <div class="shipping-progress-wrap">
                <div class="shipping-progress-text">
                    <span>¡Felicidades! Tienes <b>Envío Gratis</b> incluido</span>
                </div>
                <div class="shipping-bar"><div class="shipping-bar-fill"></div></div>
            </div>
            <div class="cart-items-list" id="cartItemsContainer"></div>
            <div class="cart-footer">
                <div class="cart-summary-row"><span>Subtotal</span><span id="cartSubtotal">$ 249.060</span></div>
                <div class="cart-summary-row"><span>Envío</span><span style="color:#059669; font-weight:700;">GRATIS</span></div>
                <div class="cart-summary-row total"><span>Total</span><span id="cartTotal">$ 249.060</span></div>
                <button class="btn-checkout" onclick="procederAlCheckout()">
                    <span>Finalizar Compra Segura</span>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- 11. JAVASCRIPT ROBUSTO CON ZOOM Y PAGINACIÓN DE OPINIONES -->
    <script>
        <?php
        $supabaseImages = [
            'img/img_1.jpg',
            'img/img_2.jpg',
            'img/img_3.jpg',
            'img/img_4.jpg',
            'img/img_5.jpg',
            'img/img_6.jpg',
            'img/img_7.jpg',
            'img/img_8.jpg',
            'img/img_9.jpg',
            'img/img_10.jpg'
        ];
        ?>
        const IMAGENES = <?= json_encode($supabaseImages) ?>;
        const SWATCHES = [];
        const REVIEWS_LIST = [{"author":"Joseanup","size":"1 Unidad","stars":"★★★★★","comment":"He tenido y probado muchos mouse para gaming a lo largo de los años, y el Logitech G Superstrike X2 es, sin duda, el mejor que he usado. No juego de forma competitiva — principalmente disfruto de juegos de aventura, títulos de acción y otras experiencias inmersivas — pero este mouse ha hecho que cada sesión de juego sea más agradable. Combina una comodidad increíble, una calidad de construcción premium y características que realmente destacan.","date":"2026.08.17","fechaTexto":"Hace 1 mes","titulo":"El mejor mouse para gaming que he usado","ubicacion":"Medellín, Colombia","likes":43,"img":"img_reviews/logitech_rev_1.jpg","img2":""},{"author":"Angelina","size":"1 Unidad","stars":"★★★★★","comment":"Este mouse superó mis expectativas en todos los aspectos. Lo primero que noté fue lo ridículamente ligero que se siente — se desliza tan suavemente que apuntar en juegos de FPS se sintió instantáneamente más rápido y preciso. La conexión inalámbrica es impecable, con cero lag, y los clics se sienten super satisfactorios y reactivos.","date":"2026.08.11","fechaTexto":"Hace 3 meses","titulo":"¡Mouse para gaming increíblemente bueno!","ubicacion":"Bucaramanga, Colombia","likes":16,"img":"img_reviews/logitech_rev_2.jpg","img2":""},{"author":"Serena Johnson","size":"1 Unidad","stars":"★★★★★","comment":"Tengo este mouse desde hace casi una semana, y puedo decir con confianza que es, sin duda, el mejor mouse que he usado. Es increíblemente ligero, el rendimiento inalámbrico ha sido impecable y simplemente se siente de alta calidad en general.","date":"2026.08.05","fechaTexto":"Hace 6 meses","titulo":"El mejor mouse que he usado","ubicacion":"Ibagué, Colombia","likes":29,"img":"img_reviews/logitech_rev_3.jpg","img2":""},{"author":"Danny Lim","size":"1 Unidad","stars":"★★★★★","comment":"Estoy tan impresionado que la latencia de clic y la retroalimentación se sienten mucho más rápidas que los interruptores mecánicos tradicionales. Sí, puedes ajustarlo mediante Logitech G Hub para modificar el disparo rápido, el clic háptico y la latencia del gatillo. Tan pronto como lo puse a 0 para el clic háptico, se siente notablemente más rápido y ahora puedo hacer más CPS (Clics Por Segundo), especialmente en Counter-Strike 2 y otros juegos multijugador competitivos. Además, el peso del mouse es de 61 g, lo que aún se siente ligero, no pesado. La durabilidad y el material se sienten de alta calidad, ni demasiado blandos, sin ruidos, sin problemas. Sin embargo, en cuanto al precio, sí, lamentablemente es caro a 179,99 USD, pero oye, pagas por lo que vale en valor y rendimiento. En resumen, este es un cambio enorme y definitivamente va a cambiar el meta cuando se trata de torneos de g","date":"2026.07.30","fechaTexto":"Hace 6 meses","titulo":"¡El mejor mouse para gaming con gatillo háptico-inductivo y conexión Lightspeed inalámbrica!","ubicacion":"Medellín, Colombia","likes":42,"img":"img_reviews/logitech_rev_4.jpg","img2":""},{"author":"Alex Y","size":"1 Unidad","stars":"★★★★★","comment":"Lo he estado usando todos los días durante aproximadamente un mes y medio. Lo primero que notas es lo increíblemente ligero que es — mi mano no se cansa incluso después de sesiones largas.","date":"2026.07.24","fechaTexto":"Hace 2 meses","titulo":"Ligero, botones agradables de presionar","ubicacion":"Bucaramanga, Colombia","likes":15,"img":"img_reviews/logitech_rev_5.jpg","img2":"img_reviews/logitech_rev_6.jpg"},{"author":"Adnan Zilic","size":"1 Unidad","stars":"★★★★★","comment":"Le di 5 estrellas a este mouse simplemente por los gatillos hápticos. Solo he usado antes el mouse Razer Viper Pro y, para mis manos grandes, es el único que me queda cómodo. El Superstrike es un poco más pequeño en mi mano, pero nada que me haga dejar de usarlo. Es más pesado que el Razer y, para mi sorpresa, en realidad me gusta y prefiero eso. La duración de la batería del mouse es excelente. Las configuraciones que elijas para los gatillos hápticos afectan la batería, pero no es nada importante. Ahora, la razón principal por la que compré este mouse fue por el sistema háptico que usa, y seré franco: es lo mejor que existe y espero que más empresas lo adapten. Poder ajustar los puntos de activación es fantástico. Tomará unos días acostumbrarse a la sensibilidad si lo llevas al máximo, pero una vez que te acostumbras, nunca querrás volver a un mouse normal. En general, creo que es un m","date":"2026.07.18","fechaTexto":"Hace 3 meses","titulo":"El háptico es el futuro para los mouse","ubicacion":"Ibagué, Colombia","likes":28,"img":"img_reviews/logitech_rev_7.jpg","img2":""},{"author":"techside","size":"1 Unidad","stars":"★★★☆☆","comment":"Podría ser de 5 estrellas. A este precio, aunque, esperaba un cable y patines de mejor calidad.","date":"2026.07.12","fechaTexto":"Hace 2 meses","titulo":"La etiqueta de precio es incluso más alta (si lo arreglas).","ubicacion":"Medellín, Colombia","likes":41,"img":"img_reviews/logitech_rev_8.jpg","img2":""},{"author":"Sergio Get","size":"1 Unidad","stars":"★★★★★","comment":"Un buen mouse, me encanto","date":"2026.07.06","fechaTexto":"Hace 3 meses","titulo":"De los mejores mouse que he probado","ubicacion":"Bucaramanga, Colombia","likes":14,"img":"img_reviews/logitech_rev_9.jpg","img2":"img_reviews/logitech_rev_10.jpg"},{"author":"Luke","size":"1 Unidad","stars":"★★★★★","comment":"Lo compré principalmente porque básicamente es silencioso al hacer clic en los botones izquierdo y derecho principales. Cuando se combina con el mousepad que Amazon recomienda, esta combinación es excelente en cuanto a sensación y respuesta. También viene con cinta de agarre que tiene una textura agradable pero un olor realmente fuerte a cuero falso barato.","date":"2026.06.30","fechaTexto":"Hace 3 meses","titulo":"Gran mouse","ubicacion":"Ibagué, Colombia","likes":27,"img":"img_reviews/logitech_rev_11.jpg","img2":""},{"author":"Alexander","size":"1 Unidad","stars":"★★★★★","comment":"Recientemente compré este mouse como mi primer paso hacia el gaming inalámbrico, específicamente el Superstrike X2, después de usar un Razer Basilisk V3 con cable durante mucho tiempo. Estaba pensando en hacer el cambio hace un tiempo, y en general, me alegra haberlo hecho.","date":"2026.06.24","fechaTexto":"Hace 4 meses","titulo":"Problema con los botones laterales","ubicacion":"Medellín, Colombia","likes":40,"img":"img_reviews/logitech_rev_12.jpg","img2":""}];
        const PRECIO_UNITARIO = 249060;
        const PRODUCTO_TITULO = "Logitech G PRO X2 SUPERSTRIKE";
        const LANDING_TOKEN = "<?= $landing_token ?>";
        const LANDING_SLUG = "logitech-g-pro-x2-superstrike";
        const CHECKOUT_URL = "<?= htmlspecialchars($url_pasarela_bold, ENT_QUOTES) ?>/checkout.php?token=" + LANDING_TOKEN;
        const ES_MODO_EDICION = <?= $es_modo_edicion ? 'true' : 'false' ?>;

        let activeImgIndex = 0;
        let lightboxIndex = 0;
        let currentReviewPage = 1;
        const REVIEWS_PER_PAGE = 5;
        let cartState = { qty: 0, hasAdded: false, variant: "", size: "1 Unidad" };

        function toggleNavMenu() {
            const overlay = document.getElementById('navMenuOverlay');
            if (!overlay) return;
            const isOpen = overlay.classList.toggle('open');
            document.body.style.overflow = isOpen ? 'hidden' : '';
        }

        function navegarSeccion(e, targetId) {
            if (e) e.preventDefault();
            toggleNavMenu();
            const target = document.getElementById(targetId);
            if (target) {
                setTimeout(() => {
                    const navbar = document.querySelector('.navbar');
                    const navHeight = navbar ? navbar.offsetHeight : 70;
                    const targetPos = target.getBoundingClientRect().top + window.pageYOffset - (navHeight + 8);
                    window.scrollTo({ top: Math.max(0, targetPos), behavior: 'smooth' });
                }, 180);
            }
        }

        function initGallery() {
            const mainImg = document.getElementById('mainImage');
            const dotsContainer = document.getElementById('galleryDotsIndicator');
            const thumbsStrip = document.getElementById('galleryThumbsStrip');

            if (dotsContainer) dotsContainer.innerHTML = '';
            if (thumbsStrip) thumbsStrip.innerHTML = '';
            if (IMAGENES.length > 0 && mainImg) mainImg.src = IMAGENES[0];

            // Punticos (solo móvil)
            if (dotsContainer) {
                IMAGENES.forEach((src, idx) => {
                    const dot = document.createElement('div');
                    dot.className = 'gallery-dot' + (idx === 0 ? ' active' : '');
                    dot.onclick = () => seleccionarImagen(idx);
                    dot.setAttribute('title', `Imagen ${idx + 1}`);
                    dotsContainer.appendChild(dot);
                });
            }

            // Miniaturas desktop
            if (thumbsStrip) {
                IMAGENES.forEach((src, idx) => {
                    const thumb = document.createElement('div');
                    thumb.className = 'gallery-thumb-item' + (idx === 0 ? ' active' : '');
                    thumb.onclick = () => seleccionarImagen(idx);
                    thumb.setAttribute('title', `Imagen ${idx + 1}`);
                    thumb.innerHTML = `<img src="${src}" alt="Imagen ${idx + 1}" loading="lazy">`;
                    thumbsStrip.appendChild(thumb);
                });
            }
        }

        function seleccionarImagen(idx) {
            if (idx < 0 || idx >= IMAGENES.length) return;
            activeImgIndex = idx;
            const mainImg = document.getElementById('mainImage');
            if (mainImg) {
                // Fade out lento
                mainImg.style.opacity = '0';
                setTimeout(() => {
                    mainImg.src = IMAGENES[idx];
                    // Fade in lento cuando la imagen carga
                    mainImg.onload = () => { mainImg.style.opacity = '1'; };
                    // Fallback: mostrar aunque no haya evento onload
                    setTimeout(() => { mainImg.style.opacity = '1'; }, 100);
                }, 350);
            }
            // Sincronizar dots (móvil)
            document.querySelectorAll('.gallery-dot').forEach((el, i) => el.classList.toggle('active', i === idx));
            // Sincronizar thumbnails (desktop)
            document.querySelectorAll('.gallery-thumb-item').forEach((el, i) => el.classList.toggle('active', i === idx));
        }

        function cambiarImagenRelativa(step) {
            let next = (activeImgIndex + step + IMAGENES.length) % IMAGENES.length;
            seleccionarImagen(next);
        }

        function abrirLightbox(idx) {
            if (ES_MODO_EDICION) return;
            lightboxIndex = (idx !== undefined) ? idx : activeImgIndex;
            const modal = document.getElementById('imageLightbox');
            const img = document.getElementById('lightboxImage');
            const thumbsContainer = document.getElementById('lightboxThumbs');
            if (img) {
                img.classList.remove('zoomed');
                img.style.transformOrigin = 'center center';
                if (IMAGENES[lightboxIndex]) img.src = IMAGENES[lightboxIndex];
            }
            if (thumbsContainer) {
                thumbsContainer.innerHTML = '';
                IMAGENES.forEach((src, i) => {
                    const t = document.createElement('div');
                    t.className = 'lightbox-thumb' + (i === lightboxIndex ? ' active' : '');
                    t.onclick = () => setLightboxImage(i);
                    t.innerHTML = `<img src="${src}">`;
                    thumbsContainer.appendChild(t);
                });
            }
            if (modal) modal.classList.add('open');
            document.body.style.overflow = 'hidden';
        }

        function setLightboxImage(idx) {
            lightboxIndex = idx;
            const img = document.getElementById('lightboxImage');
            if (img) {
                img.classList.remove('zoomed');
                img.style.transformOrigin = 'center center';
                if (IMAGENES[idx]) img.src = IMAGENES[idx];
            }
            document.querySelectorAll('.lightbox-thumb').forEach((el, i) => el.classList.toggle('active', i === idx));
        }

        function cambiarImagenLightbox(delta) {
            let next = (lightboxIndex + delta + IMAGENES.length) % IMAGENES.length;
            setLightboxImage(next);
        }

        function toggleLightboxZoom(e) {
            if (!window.matchMedia('(min-width: 1025px) and (hover: hover)').matches) return;
            const img = document.getElementById('lightboxImage');
            if (!img) return;
            img.classList.toggle('zoomed');
            if (img.classList.contains('zoomed')) {
                actualizarPosicionZoom(e, img);
            } else {
                img.style.transformOrigin = 'center center';
            }
        }

        function actualizarPosicionZoom(e, img) {
            if (!img || !img.classList.contains('zoomed')) return;
            const rect = img.getBoundingClientRect();
            const x = Math.max(0, Math.min(100, ((e.clientX - rect.left) / rect.width) * 100));
            const y = Math.max(0, Math.min(100, ((e.clientY - rect.top) / rect.height) * 100));
            img.style.transformOrigin = `${x}% ${y}%`;
        }

        function cerrarLightbox() {
            const modal = document.getElementById('imageLightbox');
            const img = document.getElementById('lightboxImage');
            if (img) img.classList.remove('zoomed');
            if (modal) modal.classList.remove('open');
            document.body.style.overflow = '';
        }

        document.addEventListener('keydown', (e) => {
            const modal = document.getElementById('imageLightbox');
            if (modal && modal.classList.contains('open')) {
                if (e.key === 'Escape') cerrarLightbox();
                if (e.key === 'ArrowLeft') cambiarImagenLightbox(-1);
                if (e.key === 'ArrowRight') cambiarImagenLightbox(1);
            }
        });

        function initSwatches() {
            const container = document.getElementById('swatchesContainer');
            if (!container) return;
            container.innerHTML = '';
            SWATCHES.forEach((colorHex, idx) => {
                const swatch = document.createElement('div');
                swatch.className = 'swatch-circle' + (idx === 0 ? ' active' : '');
                swatch.style.background = colorHex;
                swatch.onclick = () => {
                    document.querySelectorAll('.swatch-circle').forEach(s => s.classList.remove('active'));
                    swatch.classList.add('active');
                };
                container.appendChild(swatch);
            });
        }

        function toggleAccordion(btn) {
            const body = btn.nextElementSibling;
            if (body) {
                body.classList.toggle('open');
                const arrow = btn.querySelector('span:last-child');
                if (arrow) arrow.textContent = body.classList.contains('open') ? '▾' : '▸';
            }
        }

                                const CART_STORAGE_KEY = 'tridente_global_cart';
        let globalCart = [];

        function cargarCarritoStorage() {
            try {
                const saved = localStorage.getItem(CART_STORAGE_KEY);
                if (saved) {
                    const parsed = JSON.parse(saved);
                    if (Array.isArray(parsed)) {
                        globalCart = parsed.filter(item => item && item.qty > 0).map(item => {
                            let itemImg = item.image || '';
                            if (itemImg && !itemImg.startsWith('http://') && !itemImg.startsWith('https://') && !itemImg.startsWith('/')) {
                                itemImg = window.location.origin + '/' + itemImg.replace(/^\.\//, '');
                            }
                            let itemToken = (typeof LANDING_SLUG !== 'undefined' && item.slug === LANDING_SLUG && typeof LANDING_TOKEN !== 'undefined') ? LANDING_TOKEN : (item.token || LANDING_TOKEN);
                            return {
                                token: itemToken,
                                slug: item.slug || '',
                                title: item.title || 'Producto',
                                price: Number(item.price) || 0,
                                image: itemImg,
                                variant: item.variant || '',
                                size: item.size || '',
                                qty: Math.min(10, Math.max(1, Number(item.qty) || 1))
                            };
                        });
                        return;
                    }
                }
            } catch (e) {}
            globalCart = [];
        }

        function guardarCarritoEnStorage() {
            try {
                localStorage.setItem(CART_STORAGE_KEY, JSON.stringify(globalCart));
            } catch (e) {}
        }

        function obtenerItemActual() {
            return globalCart.find(i => i.token === LANDING_TOKEN);
        }

        function toggleCart() {
            if (ES_MODO_EDICION) return;
            const overlay = document.getElementById('cartOverlay');
            if (!overlay) return;
            const isOpen = overlay.classList.toggle('open');
            document.body.style.overflow = isOpen ? 'hidden' : '';
            renderCart();
        }

        function animarVueloAlCarrito(btn, callback) {
            const cartTrigger = document.querySelector('.cart-trigger');
            const mainImg = document.getElementById('mainImage');
            let imgSrc = mainImg ? mainImg.src : ((typeof IMAGENES !== 'undefined' && IMAGENES.length > 0) ? IMAGENES[0] : 'producto.png');
            try { imgSrc = new URL(imgSrc, window.location.href).href; } catch(e) {}

            const activeBtn = btn || document.querySelector('.btn-add-desktop') || document.getElementById('btnAddToCart');
            const origBtnHtml = activeBtn ? activeBtn.innerHTML : '';

            if (activeBtn) {
                activeBtn.style.transition = 'all 0.2s ease';
                activeBtn.style.transform = 'scale(0.97)';
                activeBtn.innerHTML = `
                    <div style="display:flex; align-items:center; justify-content:center; width:100%; height:100%; overflow:hidden;">
                        <dotlottie-player src="https://lottie.host/b86261fc-a05c-4c50-a871-4f9ed870ec53/OwNQtMEoZd.lottie" background="transparent" speed="1.2" style="width:48px; height:48px;" autoplay></dotlottie-player>
                    </div>
                `;
                setTimeout(() => { if (activeBtn) activeBtn.style.transform = 'scale(1)'; }, 180);
            }

            const btnRect = activeBtn ? activeBtn.getBoundingClientRect() : { left: window.innerWidth / 2, top: window.innerHeight / 2, width: 60, height: 60 };
            const startX = btnRect.left + (btnRect.width / 2) - 35;
            const startY = btnRect.top + (btnRect.height / 2) - 35;

            const flyWrap = document.createElement('div');
            flyWrap.style.position = 'fixed';
            flyWrap.style.left = '0';
            flyWrap.style.top = '0';
            flyWrap.style.zIndex = '999999';
            flyWrap.style.pointerEvents = 'none';
            flyWrap.style.transform = `translate3d(${startX}px, ${startY}px, 0) scale(0.6)`;
            flyWrap.style.opacity = '0';
            flyWrap.style.transition = 'transform 0.85s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.25s ease';

            const flyImg = document.createElement('img');
            flyImg.src = imgSrc;
            flyImg.style.width = '70px';
            flyImg.style.height = '70px';
            flyImg.style.borderRadius = '16px';
            flyImg.style.objectFit = 'cover';
            flyImg.style.border = '2.5px solid #ffffff';
            flyImg.style.boxShadow = '0 16px 40px rgba(0, 0, 0, 0.35)';
            flyImg.style.transition = 'transform 0.85s cubic-bezier(0.4, 0, 0.2, 1), border-radius 0.85s ease, opacity 0.85s ease';
            flyImg.style.transformOrigin = 'center center';

            flyWrap.appendChild(flyImg);
            document.body.appendChild(flyWrap);

            setTimeout(() => {
                flyWrap.style.opacity = '1';
                flyWrap.style.transform = `translate3d(${startX}px, ${startY}px, 0) scale(1)`;

                requestAnimationFrame(() => {
                    const cartRect = cartTrigger ? cartTrigger.getBoundingClientRect() : { left: window.innerWidth - 60, top: 20, width: 40, height: 40 };
                    const destX = cartRect.left + (cartRect.width / 2) - 18;
                    const destY = cartRect.top + (cartRect.height / 2) - 18;

                    flyWrap.style.transform = `translate3d(${destX}px, ${destY}px, 0) scale(0.45)`;
                    flyImg.style.borderRadius = '50%';
                    flyImg.style.transform = 'rotate(18deg)';
                    flyImg.style.opacity = '0.35';
                });
            }, 300);

            setTimeout(() => {
                if (flyWrap.parentNode) flyWrap.parentNode.removeChild(flyWrap);

                if (cartTrigger) {
                    const ripple = document.createElement('div');
                    ripple.className = 'cart-ripple-effect';
                    cartTrigger.appendChild(ripple);
                    setTimeout(() => { if (ripple.parentNode) ripple.parentNode.removeChild(ripple); }, 650);

                    cartTrigger.classList.add('cart-pop-active');
                    setTimeout(() => { cartTrigger.classList.remove('cart-pop-active'); }, 450);
                }

                if (activeBtn) {
                    activeBtn.innerHTML = origBtnHtml;
                }

                if (callback) callback();
            }, 1100);
        }

        function agregarAlCarrito(e) {
            if (ES_MODO_EDICION) return;
            let clickedBtn = null;
            if (e) {
                clickedBtn = e.currentTarget || (e.target ? e.target.closest('button') : null);
            }
            if (!clickedBtn) {
                clickedBtn = document.querySelector('.btn-add-desktop') || document.getElementById('btnAddToCart');
            }

            const mainImg = document.getElementById('mainImage');
            let imgSrc = mainImg ? mainImg.src : ((typeof IMAGENES !== 'undefined' && IMAGENES.length > 0) ? IMAGENES[0] : 'producto.png');
            try { imgSrc = new URL(imgSrc, window.location.href).href; } catch(e) {}

            const prodTitulo = (typeof PRODUCTO_TITULO !== 'undefined') ? PRODUCTO_TITULO : 'Producto';
            const precioUnit = (typeof PRECIO_UNITARIO !== 'undefined') ? PRECIO_UNITARIO : 0;
            const variantVal = (typeof cartState !== 'undefined' && cartState.variant) ? cartState.variant : 'Estándar';
            const sizeVal = (typeof cartState !== 'undefined' && cartState.size) ? cartState.size : 'Único';

            let existingIndex = globalCart.findIndex(i => i.token === LANDING_TOKEN);
            if (existingIndex !== -1) {
                if (globalCart[existingIndex].qty < 10) {
                    globalCart[existingIndex].qty += 1;
                } else {
                    globalCart[existingIndex].qty = 10;
                }
                globalCart[existingIndex].image = imgSrc; // Asegurar miniatura absoluta
                globalCart[existingIndex].variant = variantVal;
                globalCart[existingIndex].size = sizeVal;
            } else {
                globalCart.push({
                    token: LANDING_TOKEN,
                    slug: typeof LANDING_SLUG !== 'undefined' ? LANDING_SLUG : '',
                    title: prodTitulo,
                    price: precioUnit,
                    image: imgSrc,
                    variant: variantVal,
                    size: sizeVal,
                    qty: 1
                });
            }

            guardarCarritoEnStorage();
            actualizarControlesPagina();

            animarVueloAlCarrito(clickedBtn, () => {
                renderCart();
                const overlay = document.getElementById('cartOverlay');
                if (overlay && !overlay.classList.contains('open')) {
                    overlay.classList.add('open');
                    document.body.style.overflow = 'hidden';
                }
            });
        }

        function cambiarCantidadItem(token, delta) {
            let idx = globalCart.findIndex(i => i.token === token);
            if (idx !== -1) {
                let newQty = globalCart[idx].qty + delta;
                if (newQty > 10) newQty = 10;
                if (newQty <= 0) {
                    globalCart.splice(idx, 1);
                } else {
                    globalCart[idx].qty = newQty;
                }
            }
            guardarCarritoEnStorage();
            actualizarControlesPagina();
            renderCart();
        }

        function cambiarCantidad(delta) {
            cambiarCantidadItem(LANDING_TOKEN, delta);
        }

        function actualizarControlesPagina() {
            const currentItem = obtenerItemActual();
            const currentQty = currentItem ? currentItem.qty : 0;

            const desktopQty = document.getElementById('qtyDesktopDisplay');
            if (desktopQty) desktopQty.textContent = Math.max(1, currentQty);

            const mobileBtn = document.getElementById('btnAddToCart');
            if (mobileBtn) {
                mobileBtn.textContent = 'Añadir al carro';
            }
            const desktopBtn = document.querySelector('.btn-add-desktop');
            if (desktopBtn) {
                desktopBtn.textContent = 'Añadir al carro';
            }
        }

        function formatMoney(num) {
            return '$ ' + num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }

        function renderCart() {
            const container = document.getElementById('cartItemsContainer');
            const totalUnits = globalCart.reduce((sum, item) => sum + (item.qty || 0), 0);
            const subtotalMoney = globalCart.reduce((sum, item) => sum + ((item.price || 0) * (item.qty || 0)), 0);
            const fmtTotal = formatMoney(subtotalMoney);

            const badge = document.getElementById('cartBadge');
            if (badge) {
                badge.textContent = totalUnits;
                badge.style.display = totalUnits > 0 ? 'flex' : 'none';
            }
            const drawerTitle = document.getElementById('cartDrawerTitle');
            if (drawerTitle) drawerTitle.textContent = `Tu Carrito (${totalUnits})`;
            const subtotalEl = document.getElementById('cartSubtotal');
            if (subtotalEl) subtotalEl.textContent = fmtTotal;
            const totalEl = document.getElementById('cartTotal');
            if (totalEl) totalEl.textContent = fmtTotal;

            const checkoutBtn = document.querySelector('.btn-checkout');
            if (checkoutBtn) {
                if (globalCart.length === 0 || totalUnits <= 0) {
                    checkoutBtn.style.opacity = '0.45';
                    checkoutBtn.style.pointerEvents = 'none';
                    checkoutBtn.innerHTML = `<span>Carrito Vacío</span>`;
                } else {
                    checkoutBtn.style.opacity = '1';
                    checkoutBtn.style.pointerEvents = 'auto';
                    checkoutBtn.innerHTML = `
                        <span>Finalizar Compra Segura</span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                    `;
                }
            }

            if (container) {
                if (globalCart.length === 0 || totalUnits <= 0) {
                    container.innerHTML = `
                        <div style="text-align: center; padding: 48px 20px; color: var(--text-muted);">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin: 0 auto 12px auto; display: block; opacity: 0.4;">
                                <circle cx="9" cy="21" r="1"></circle>
                                <circle cx="20" cy="21" r="1"></circle>
                                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                            </svg>
                            <p style="font-size: 15px; font-weight: 700; margin: 0 0 6px 0; color: var(--text-main);">Tu carrito está vacío</p>
                            <p style="font-size: 13px; margin: 0;">Agrega productos para continuar con tu compra.</p>
                        </div>
                    `;
                } else {
                    container.innerHTML = globalCart.map(item => `
                        <div class="cart-item" data-token="${item.token}">
                            <img src="${item.image}" class="cart-item-img" alt="${item.title}">
                            <div class="cart-item-info">
                                <div>
                                    <div class="cart-item-title">${item.title}</div>
                                    <div class="cart-item-variant">${item.size}</div>
                                </div>
                                <div class="cart-item-bottom">
                                    <div class="cart-item-price">${formatMoney(item.price)}</div>
                                    <div class="qty-controls">
                                        <button class="qty-btn" onclick="cambiarCantidadItem('${item.token}', -1)">-</button>
                                        <span class="qty-value">${item.qty}</span>
                                        <button class="qty-btn" onclick="cambiarCantidadItem('${item.token}', 1)" ${item.qty >= 10 ? 'style="opacity:0.4;cursor:not-allowed;"' : ''}>+</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `).join('');
                }
            }
        }

        function procederAlCheckout() {
            if (!globalCart || globalCart.length === 0) return;
            const loader = document.getElementById('landing-loader');
            if (loader) loader.style.display = 'flex';
            
            const primaryItem = globalCart.find(i => i.token === LANDING_TOKEN) || globalCart[0];
            const tokensList = globalCart.map(i => `${i.token}:${i.qty}`).join(',');
            
            const targetUrl = CHECKOUT_URL + '&qty=' + primaryItem.qty + '&cart_tokens=' + encodeURIComponent(tokensList);
            setTimeout(() => { window.location.href = targetUrl; }, 350);
        }

        // ─── SISTEMA DE OPINIONES DE CLIENTES (LOCALSTORAGE + ESTADÍSTICAS AMAZON) ───
        const USER_REVIEWS_KEY = 'dji_user_custom_reviews_v1';
        let selectedStarRating = 5;
        const starLabelsMap = {
            1: "Malo (1 de 5)",
            2: "Regular (2 de 5)",
            3: "Bueno (3 de 5)",
            4: "Muy bueno (4 de 5)",
            5: "Excelente (5 de 5)"
        };

        let isPurchaseVerifiedSuccessfully = false;

        function cargarOpinionesUsuario() {
            try {
                const saved = localStorage.getItem(USER_REVIEWS_KEY);
                if (saved) {
                    const parsed = JSON.parse(saved);
                    if (Array.isArray(parsed) && parsed.length > 0) {
                        parsed.forEach(ur => {
                            // Las opiniones de usuario por defecto no tienen compra verificada a menos que haya sido verificada con éxito
                            if (ur.isUserVerified !== true) {
                                ur.isUserVerified = false;
                            }
                            if (!REVIEWS_LIST.some(r => r.id && r.id === ur.id)) {
                                REVIEWS_LIST.unshift(ur);
                            }
                        });
                    }
                }
            } catch (e) {}
        }

        function setReviewModalView(viewId, titleText) {
            const views = document.querySelectorAll('.review-modal-view');
            views.forEach(v => v.classList.remove('active'));

            const targetView = document.getElementById(viewId);
            if (targetView) targetView.classList.add('active');

            const headerTitle = document.getElementById('modalReviewHeaderTitle');
            if (headerTitle) {
                // Solo mostrar título en el header si es la vista de escribir opinión para evitar duplicidad
                if (viewId === 'reviewModalViewWrite') {
                    headerTitle.textContent = titleText || 'Escribir opinión';
                    headerTitle.style.display = 'block';
                } else {
                    headerTitle.textContent = '';
                    headerTitle.style.display = 'none';
                }
            }
        }

        function mostrarVistaVerificarCompra() {
            setReviewModalView('reviewModalViewVerify', '');
            const receiptInput = document.getElementById('verifyReceiptNumber');
            if (receiptInput) receiptInput.focus();
        }

        function mostrarVistaEscribirOpinion() {
            setReviewModalView('reviewModalViewWrite', 'Escribir opinión');
        }

        function ejecutarVerificacionCompra(e) {
            if (e) e.preventDefault();
            const btn = document.getElementById('btnDoVerify');
            const origHtml = btn ? btn.innerHTML : 'Verificar';
            if (btn) {
                btn.innerHTML = 'Verificando...';
                btn.disabled = true;
            }

            setTimeout(() => {
                if (btn) {
                    btn.innerHTML = origHtml;
                    btn.disabled = false;
                }
                setReviewModalView('reviewModalViewUpsell', '');
            }, 550);
        }

        function ejecutarCompraDesdeModal(e) {
            cerrarModalEscribirOpinion();
            agregarAlCarrito(e);
        }

        function abrirModalEscribirOpinion() {
            const modal = document.getElementById('writeReviewModal');
            if (modal) {
                mostrarVistaEscribirOpinion();
                modal.classList.add('open');
                document.body.style.overflow = 'hidden';
                selectStars(5);
                const nameInput = document.getElementById('reviewAuthorInput');
                if (nameInput) nameInput.focus();
            }
        }

        function cerrarModalEscribirOpinion() {
            const modal = document.getElementById('writeReviewModal');
            if (modal) {
                modal.classList.remove('open');
                document.body.style.overflow = '';
                // Restablecer a vista normal al cerrar
                setTimeout(mostrarVistaEscribirOpinion, 300);
            }
        }

        function hoverStars(val) {
            const stars = document.querySelectorAll('#starRatingPicker .star-pick');
            stars.forEach((s, idx) => {
                s.classList.toggle('hovered', idx < val);
            });
            const lbl = document.getElementById('starRatingLabel');
            if (lbl) lbl.textContent = starLabelsMap[val] || `${val} de 5`;
        }

        function resetStars() {
            const stars = document.querySelectorAll('#starRatingPicker .star-pick');
            stars.forEach((s, idx) => {
                s.classList.remove('hovered');
                s.classList.toggle('selected', idx < selectedStarRating);
            });
            const lbl = document.getElementById('starRatingLabel');
            if (lbl) lbl.textContent = starLabelsMap[selectedStarRating] || `${selectedStarRating} de 5`;
        }

        function selectStars(val) {
            selectedStarRating = val;
            const input = document.getElementById('reviewRatingInput');
            if (input) input.value = val;
            resetStars();
        }

        function toggleExplanationReviews(btn) {
            const box = document.getElementById('reviewsExplanationBox');
            if (box) {
                const isOpen = box.classList.toggle('open');
                const arrow = btn.querySelector('.explanation-arrow');
                if (arrow) arrow.textContent = isOpen ? '▴' : '▾';
            }
        }

        function filtrarPorEstrellasDirecto(starCount) {
            const selectRating = document.getElementById('filterRating');
            if (selectRating) {
                selectRating.value = starCount.toString();
                currentReviewPage = 1;
                renderReviews();
                const section = document.getElementById('customerReviewsSection');
                if (section) section.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }

        function calcularEstadisticasReviews() {
            // Distribución realista estilo Amazon de 48 calificaciones globales
            const BASELINE_TOTAL = 568;
            const BASELINE_COUNTS = { 5: 424, 4: 64, 3: 40, 2: 20, 1: 20 };
            
            let userAddedCount = 0;
            const userAddedCounts = { 1: 0, 2: 0, 3: 0, 4: 0, 5: 0 };

            if (REVIEWS_LIST && Array.isArray(REVIEWS_LIST)) {
                REVIEWS_LIST.forEach(r => {
                    if (r.id || r.isUserCustom || r.isUserVerified) {
                        userAddedCount++;
                        let starCount = 5;
                        if (r.ratingNum) starCount = r.ratingNum;
                        else if (r.stars) starCount = (r.stars.match(/★/g) || []).length || 5;
                        starCount = Math.max(1, Math.min(5, starCount));
                        userAddedCounts[starCount] = (userAddedCounts[starCount] || 0) + 1;
                    }
                });
            }

            const total = BASELINE_TOTAL + userAddedCount;
            let sumStars = 0;
            const finalCounts = {};

            for (let s = 1; s <= 5; s++) {
                finalCounts[s] = (BASELINE_COUNTS[s] || 0) + (userAddedCounts[s] || 0);
                sumStars += finalCounts[s] * s;
            }

            const avg = (sumStars / total).toFixed(1);
            const avgDisplay = document.getElementById('scoreAvgDisplay');
            if (avgDisplay) avgDisplay.textContent = avg;

            const countDisplay = document.getElementById('reviewsTotalCountSub');
            if (countDisplay) {
                countDisplay.textContent = `${total} calificaciones globales`;
            }

            for (let s = 1; s <= 5; s++) {
                const pct = Math.round((finalCounts[s] / total) * 100);
                const barFill = document.getElementById(`barFill${s}`);
                const barPct = document.getElementById(`barPct${s}`);
                if (barFill) barFill.style.width = `${pct}%`;
                if (barPct) barPct.textContent = `${pct}%`;
            }
        }

        function guardarNuevaOpinion(e) {
            if (e) e.preventDefault();
            const submitBtn = document.querySelector('#writeReviewForm .btn-review-submit');
            const authorInput = document.getElementById('reviewAuthorInput');
            const titleInput = document.getElementById('reviewTitleInput');
            const commentInput = document.getElementById('reviewCommentInput');

            const author = (authorInput ? authorInput.value.trim() : '') || 'Cliente Verificado';
            const title = titleInput ? titleInput.value.trim() : '';
            const comment = commentInput ? commentInput.value.trim() : '';
            const starsNum = selectedStarRating || 5;

            if (!comment) {
                alert('Por favor escribe un comentario para tu opinión.');
                return;
            }

            // Estado de carga con delay
            const origSubmitText = submitBtn ? submitBtn.textContent : 'Publicar opinión';
            if (submitBtn) {
                submitBtn.textContent = 'Publicando opinión...';
                submitBtn.disabled = true;
                submitBtn.style.opacity = '0.7';
                submitBtn.style.cursor = 'not-allowed';
            }

            setTimeout(() => {
                const now = new Date();
                const year = now.getFullYear();
                const month = String(now.getMonth() + 1).padStart(2, '0');
                const day = String(now.getDate()).padStart(2, '0');
                const dateFormatted = `${year}.${month}.${day}`;

                let fullComment = comment;
                if (title) {
                    fullComment = `<b>${title}</b><br>${comment}`;
                }

                const newReviewObj = {
                    id: 'rev_' + Date.now(),
                    author: author,
                    color: "Creator Combo",
                    size: "Kit Completo 6 en 1",
                    stars: "★".repeat(starsNum) + "☆".repeat(5 - starsNum),
                    ratingNum: starsNum,
                    comment: fullComment,
                    date: dateFormatted,
                    isUserVerified: isPurchaseVerifiedSuccessfully
                };

                // Guardar en localStorage
                try {
                    let savedReviews = [];
                    const existing = localStorage.getItem(USER_REVIEWS_KEY);
                    if (existing) savedReviews = JSON.parse(existing);
                    savedReviews.unshift(newReviewObj);
                    localStorage.setItem(USER_REVIEWS_KEY, JSON.stringify(savedReviews));
                } catch (err) {}

                // Añadir al inicio del arreglo en memoria
                REVIEWS_LIST.unshift(newReviewObj);

                // Limpiar formulario y restaurar botón
                if (authorInput) authorInput.value = '';
                if (titleInput) titleInput.value = '';
                if (commentInput) commentInput.value = '';
                if (submitBtn) {
                    submitBtn.textContent = origSubmitText;
                    submitBtn.disabled = false;
                    submitBtn.style.opacity = '';
                    submitBtn.style.cursor = '';
                }
                cerrarModalEscribirOpinion();

                // Renderizar y saltar a la primera página
                currentReviewPage = 1;
                renderReviews();

                // Notificación elegante sin emojis
                const alertBox = document.createElement('div');
                alertBox.style.cssText = 'position:fixed; bottom:24px; right:24px; background:#1d1d1f; color:#ffffff; padding:14px 22px; border-radius:12px; font-weight:600; font-size:14px; z-index:999999; box-shadow:0 10px 30px rgba(0,0,0,0.3); display:flex; align-items:center; gap:8px; animation: modalFadeIn 0.3s ease;';
                alertBox.innerHTML = '<span>Tu opinión ha sido publicada exitosamente.</span>';
                document.body.appendChild(alertBox);
                setTimeout(() => { if (alertBox.parentNode) alertBox.parentNode.removeChild(alertBox); }, 3800);
            }, 850);
        }

        function eliminarOpinionUsuario(reviewId) {
            if (!reviewId) return;
            const confirmacion = window.confirm('¿Estás seguro de eliminar tu comentario?');
            if (!confirmacion) return;

            const idx = REVIEWS_LIST.findIndex(r => r.id === reviewId);
            if (idx !== -1) {
                REVIEWS_LIST.splice(idx, 1);
            }

            try {
                const saved = localStorage.getItem(USER_REVIEWS_KEY);
                if (saved) {
                    let parsed = JSON.parse(saved);
                    if (Array.isArray(parsed)) {
                        parsed = parsed.filter(r => r.id !== reviewId);
                        localStorage.setItem(USER_REVIEWS_KEY, JSON.stringify(parsed));
                    }
                }
            } catch (e) {}

            calcularEstadisticasReviews();
            renderReviews();
        }

        function initReviewsScrollObserver() {
            const section = document.getElementById('customerReviewsSection');
            if (!section) return;

            if ('IntersectionObserver' in window) {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            section.classList.add('scroll-visible');
                            observer.unobserve(section);
                        }
                    });
                }, { threshold: 0.12 });
                observer.observe(section);
            } else {
                section.classList.add('scroll-visible');
            }
        }

        function renderReviews() {
            const container = document.getElementById('reviewsListContainer');
            const paginationContainer = document.getElementById('reviewsPaginationContainer');
            if (!container || !REVIEWS_LIST || REVIEWS_LIST.length === 0) return;

            const filterColor = document.getElementById('filterColor') ? document.getElementById('filterColor').value : 'All';
            const filterRating = document.getElementById('filterRating') ? document.getElementById('filterRating').value : 'All';
            const sortBy = document.getElementById('filterSort') ? document.getElementById('filterSort').value : 'Default';

            let filtered = [...REVIEWS_LIST];

            if (filterColor !== 'All') {
                filtered = filtered.filter(r => r.color === filterColor);
            }
            if (filterRating !== 'All') {
                const targetRating = parseInt(filterRating, 10);
                filtered = filtered.filter(r => {
                    const starsCount = r.ratingNum || (r.stars ? (r.stars.match(/★/g) || []).length : 5);
                    return starsCount === targetRating;
                });
            }
            if (sortBy === 'Most Recent') {
                filtered.sort((a, b) => b.date.localeCompare(a.date));
            }

            const totalPages = Math.max(1, Math.ceil(filtered.length / REVIEWS_PER_PAGE));
            if (currentReviewPage > totalPages) currentReviewPage = 1;

            const startIdx = (currentReviewPage - 1) * REVIEWS_PER_PAGE;
            const pageItems = filtered.slice(startIdx, startIdx + REVIEWS_PER_PAGE);

            container.innerHTML = '';
            if (pageItems.length === 0) {
                container.innerHTML = `
                    <div style="text-align:center; padding:36px 16px; color:#565959;">
                        <p style="font-size:15px; font-weight:600; margin-bottom:6px;">No hay opiniones que coincidan con los filtros seleccionados.</p>
                        <p style="font-size:13px; margin:0;">Sé el primero en <a href="javascript:void(0)" onclick="abrirModalEscribirOpinion()" style="color:#007185; font-weight:700; text-decoration:underline;">escribir una opinión</a>.</p>
                    </div>
                `;
            } else {
                pageItems.forEach(r => {
                    const item = document.createElement('div');
                    item.className = 'review-card-item';
                    item.innerHTML = `
                        <div class="reviewer-col">
                            <span class="reviewer-name" data-editable="true">
                                ${r.author}
                                ${(!r.id || r.isUserVerified === true) ? '<span class="reviewer-badge-verified">Compra verificada</span>' : ''}
                            </span>
                            ${r.size ? `<span class="reviewer-meta" data-editable="true">Size: ${r.size}</span>` : ''}
                            ${r.ubicacion ? `<span class="reviewer-meta reviewer-place">${r.ubicacion}</span>` : ''}
                        </div>
                        <div class="review-content-col">
                            <div class="review-stars-row">${r.stars}</div>
                            ${r.titulo ? `<div class="review-title-text" data-editable="true">${r.titulo}</div>` : ''}
                            <p class="review-comment-text" data-editable="true">${r.comment}</p>
                            ${(r.img || r.img2) ? `<div class="review-photos">${[r.img, r.img2].filter(Boolean).map(u => `<img src="${u}" class="review-photo" loading="lazy" alt="Foto de la opinión" onclick="abrirFotoOpinion('${u}')">`).join('')}</div>` : ''}
                            ${r.likes ? `<div class="review-helpful">${r.likes} personas encontraron esto útil</div>` : ''}
                        </div>
                        <div class="review-actions-wrap">
                            <div class="review-date-badge" data-editable="true">${r.fechaTexto || r.date}</div>
                            ${r.id ? `
                                <button type="button" class="btn-delete-user-review" onclick="eliminarOpinionUsuario('${r.id}')" title="Eliminar mi opinión" aria-label="Eliminar opinión">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M3 6h18"></path>
                                        <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path>
                                        <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path>
                                        <line x1="10" y1="11" x2="10" y2="17"></line>
                                        <line x1="14" y1="11" x2="14" y2="17"></line>
                                    </svg>
                                </button>
                            ` : ''}
                        </div>
                    `;
                    container.appendChild(item);
                });
            }

            if (paginationContainer) {
                if (totalPages <= 1) {
                    paginationContainer.innerHTML = '';
                } else {
                    let pagesHtml = `<span>Total <b>${totalPages}</b> Páginas</span>`;
                    pagesHtml += `<button class="page-btn" onclick="cambiarPaginaReviews(${currentReviewPage - 1}, ${totalPages})" ${currentReviewPage === 1 ? 'disabled style="opacity:0.35;cursor:not-allowed;"' : ''}>&lt;</button>`;
                    
                    for (let i = 1; i <= totalPages; i++) {
                        pagesHtml += `<button class="page-btn ${i === currentReviewPage ? 'active' : ''}" onclick="cambiarPaginaReviews(${i}, ${totalPages})">${i}</button>`;
                    }

                    pagesHtml += `<button class="page-btn" onclick="cambiarPaginaReviews(${currentReviewPage + 1}, ${totalPages})" ${currentReviewPage === totalPages ? 'disabled style="opacity:0.35;cursor:not-allowed;"' : ''}>&gt;</button>`;
                    paginationContainer.innerHTML = pagesHtml;
                }
            }

            calcularEstadisticasReviews();
            initModoEdicion();
        }


        function cambiarPaginaReviews(nuevaPagina, totalPages) {
            if (nuevaPagina < 1 || nuevaPagina > totalPages) return;
            currentReviewPage = nuevaPagina;
            renderReviews();
            const section = document.getElementById('customerReviewsSection');
            if (section) {
                section.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }

        function initModoEdicion() {
            if (!ES_MODO_EDICION) return;
            document.querySelectorAll('[data-editable="true"]').forEach(el => {
                el.addEventListener('dblclick', function(e) {
                    e.stopPropagation();
                    this.contentEditable = "true";
                    this.focus();
                });
                el.addEventListener('blur', function() { this.contentEditable = "false"; });
            });
        }

        async function guardarCambiosVisuales() {
            const btn = document.querySelector('.btn-editor-save');
            if (btn) {
                btn.innerHTML = '⏳ Guardando...';
                btn.disabled = true;
            }

            const docClone = document.documentElement.cloneNode(true);
            const tb = docClone.querySelector('#editorToolbar');
            if (tb) tb.remove();

            const bodyEl = docClone.querySelector('body');
            if (bodyEl) {
                bodyEl.classList.remove('modo-edicion-activo');
                bodyEl.style.marginTop = '';
            }

            const htmlToSave = '<!DOCTYPE html>' + docClone.outerHTML;

            const formData = new FormData();
            formData.append('slug', LANDING_SLUG);
            formData.append('html_content', htmlToSave);

            try {
                const res = await fetch('../../guardar_visual.php', { method: 'POST', body: formData });
                const data = await res.json();
                if (data.success) { alert('✅ ' + data.message); }
                else { alert('❌ Error: ' + data.message); }
            } catch (err) { alert('❌ Error de conexión al guardar los cambios'); }

            if (btn) {
                btn.innerHTML = '💾 Guardar Cambios';
                btn.disabled = false;
            }
        }

        // ─── CONTADOR DE ENVÍO URGENTE PERSISTENTE EN LOCALSTORAGE ───
        function initShippingCountdown() {
            const STORAGE_KEY = 'dji_shipping_countdown_deadline_v1';
            let deadline = localStorage.getItem(STORAGE_KEY);
            const now = Date.now();

            // Si no existe o ya venció, establecer 20 horas y 40 minutos en el futuro
            if (!deadline || isNaN(parseInt(deadline, 10)) || parseInt(deadline, 10) <= now) {
                const duracionMs = (20 * 3600 + 40 * 60) * 1000;
                deadline = now + duracionMs;
                localStorage.setItem(STORAGE_KEY, deadline.toString());
            } else {
                deadline = parseInt(deadline, 10);
            }

            function actualizarDisplay() {
                const actual = Date.now();
                let restante = deadline - actual;

                if (restante <= 0) {
                    const duracionMs = (20 * 3600 + 40 * 60) * 1000;
                    deadline = actual + duracionMs;
                    localStorage.setItem(STORAGE_KEY, deadline.toString());
                    restante = deadline - actual;
                }

                const h = Math.floor(restante / (1000 * 60 * 60));
                const m = Math.floor((restante % (1000 * 60 * 60)) / (1000 * 60));
                const s = Math.floor((restante % (1000 * 60)) / 1000);

                const el = document.getElementById('shippingCountdown');
                if (el) {
                    el.textContent = `${h} h ${m < 10 ? '0' + m : m} min ${s < 10 ? '0' + s : s} s`;
                }
            }

            actualizarDisplay();
            setInterval(actualizarDisplay, 1000);
        }

        // ─── SLIDER Y PUNTICOS PARA 'QUIENES VIERON ESTE PRODUCTO TAMBIÉN COMPRARON' ───
        function initRecommendedProductsSlider() {
            const track = document.getElementById('recommendedProductsTrack');
            const dotsContainer = document.getElementById('recommendedProductsDots');
            if (!track || !dotsContainer) return;

            const cards = track.querySelectorAll('.more-card');
            if (cards.length === 0) return;

            dotsContainer.innerHTML = '';
            cards.forEach((card, idx) => {
                const dot = document.createElement('div');
                dot.className = 'more-prod-dot' + (idx === 0 ? ' active' : '');
                dot.setAttribute('title', `Producto ${idx + 1}`);
                dot.onclick = () => {
                    card.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
                };
                dotsContainer.appendChild(dot);
            });

            track.addEventListener('scroll', () => {
                const scrollLeft = track.scrollLeft;
                const cardWidth = cards[0].offsetWidth + 16;
                const activeIdx = Math.min(cards.length - 1, Math.max(0, Math.round(scrollLeft / cardWidth)));
                dotsContainer.querySelectorAll('.more-prod-dot').forEach((dot, i) => {
                    dot.classList.toggle('active', i === activeIdx);
                });
            }, { passive: true });
        }

        function slideRecommendedProducts(delta) {
            const track = document.getElementById('recommendedProductsTrack');
            if (!track) return;
            const card = track.querySelector('.more-card');
            const scrollAmount = card ? (card.offsetWidth + 16) * 1.5 : 300;
            track.scrollBy({ left: delta * scrollAmount, behavior: 'smooth' });
        }

        // ─── SCROLL DINÁMICO: OCULTAR/MOSTRAR NAVBAR Y STICKY ADD TO CART ───
        (function() {
            let lastScrollY = 0;
            let ticking = false;

            function updateNavScroll() {
                const currentScrollY = Math.max(0, window.scrollY || window.pageYOffset || document.documentElement.scrollTop || 0);
                const navbar = document.querySelector('.navbar');
                const stickyBar = document.querySelector('.sticky-footer-bar');
                const delta = currentScrollY - lastScrollY;

                if (currentScrollY <= 10) {
                    // En la cima → siempre visible
                    if (navbar) navbar.classList.remove('nav-hidden');
                    if (stickyBar) stickyBar.classList.remove('bar-hidden');
                } else if (delta < -2) {
                    // Scroll UP (delta negativo) → mostrar
                    if (navbar) navbar.classList.remove('nav-hidden');
                    if (stickyBar) stickyBar.classList.remove('bar-hidden');
                } else if (delta > 4 && currentScrollY > 60) {
                    // Scroll DOWN (delta positivo, superada zona inicial) → ocultar
                    if (navbar) navbar.classList.add('nav-hidden');
                    if (stickyBar) stickyBar.classList.add('bar-hidden');
                }

                lastScrollY = currentScrollY;
                ticking = false;
            }

            function onScroll() {
                if (!ticking) {
                    ticking = true;
                    window.requestAnimationFrame(updateNavScroll);
                }
            }

            window.addEventListener('scroll', onScroll, { passive: true });
        })();

        document.addEventListener('DOMContentLoaded', () => {
            cargarCarritoStorage();
            cargarOpinionesUsuario();
            actualizarControlesPagina();
            initGallery();
            initSwatches();
            renderCart();
            renderReviews();
            initModoEdicion();
            initShippingCountdown();
            initRecommendedProductsSlider();
            initReviewsScrollObserver();
        });
    
        // ─── GESTOS TÁCTILES (SWIPE) PARA MÓVIL EN GALERÍA Y LIGHTBOX ───
        (function() {
            function habilitarSwipe(elem, accionIzquierda, accionDerecha) {
                if (!elem) return;
                let startX = 0, startY = 0;
                elem.addEventListener('touchstart', function(e) {
                    if (e.touches && e.touches.length === 1) {
                        startX = e.touches[0].clientX;
                        startY = e.touches[0].clientY;
                    }
                }, { passive: true });
                elem.addEventListener('touchend', function(e) {
                    if (e.changedTouches && e.changedTouches.length === 1) {
                        let diffX = e.changedTouches[0].clientX - startX;
                        let diffY = e.changedTouches[0].clientY - startY;
                        if (Math.abs(diffX) > 35 && Math.abs(diffX) > Math.abs(diffY)) {
                            if (diffX < 0) {
                                accionIzquierda();
                            } else {
                                accionDerecha();
                            }
                        }
                    }
                }, { passive: true });
            }

            document.addEventListener('DOMContentLoaded', function() {
                const mainWrap = document.querySelector('.main-image-wrap');
                if (mainWrap) {
                    habilitarSwipe(mainWrap, () => cambiarImagenRelativa(1), () => cambiarImagenRelativa(-1));
                }

                const lbView = document.getElementById('imageLightbox');
                if (lbView) {
                    habilitarSwipe(lbView, () => cambiarImagenLightbox(1), () => cambiarImagenLightbox(-1));
                }
            });
        })();

        // ─── 5.2 FUNCIONES PARA REVIEWS CON VIDEO Y REPRODUCTOR YOUTUBE ───
        function extraerYouTubeId(url) {
            if (!url) return '';
            url = url.trim();
            const regExp = /(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=|shorts\/)|youtu\.be\/)([^"&?\/\s]{11})/;
            const match = url.match(regExp);
            if (match && match[1]) return match[1];
            if (url.length === 11 && !url.includes('/') && !url.includes('.')) return url;
            return url;
        }

        function abrirVideoModal(youtubeId) {
            const modal = document.getElementById('videoModalLightbox');
            const iframe = document.getElementById('videoModalIframe');
            if (!modal || !iframe || !youtubeId) return;
            iframe.src = 'https://www.youtube.com/embed/' + youtubeId + '?autoplay=1&rel=0&modestbranding=1&playsinline=1';
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function cerrarVideoModal(e) {
            if (e && e.target && e.target.classList && e.target.classList.contains('video-modal-container')) {
                return;
            }
            if (e) e.stopPropagation();
            const modal = document.getElementById('videoModalLightbox');
            const iframe = document.getElementById('videoModalIframe');
            if (!modal) return;
            modal.classList.remove('active');
            if (iframe) iframe.src = '';
            document.body.style.overflow = '';
        }

        function manejarClickVideoCard(card, event) {
            if (typeof ES_MODO_EDICION !== 'undefined' && ES_MODO_EDICION) {
                return;
            }
            const ytid = card.getAttribute('data-youtube-id');
            if (ytid) {
                abrirVideoModal(ytid);
            }
        }

        function desplazarVideoCarrusel(direccion) {
            const track = document.getElementById('videoReviewsTrack');
            if (track) {
                track.scrollBy({ left: direccion * 320, behavior: 'smooth' });
            }
        }

        function editarVideoCard(card) {
            if (!card) return;
            const currentId = card.getAttribute('data-youtube-id') || '';
            const currentDurElem = card.querySelector('.video-duration-text');
            const currentTitleElem = card.querySelector('.video-card-title-text');
            
            const currentDur = currentDurElem ? currentDurElem.innerText.trim() : '1:30';
            const currentTitle = currentTitleElem ? currentTitleElem.innerText.trim() : 'Review DJI Pocket 3';

            const newUrl = prompt('Ingresa el Link de YouTube o ID del video:\n(Ej: https://www.youtube.com/watch?v=... o https://youtu.be/... o https://youtube.com/shorts/...)', currentId ? 'https://www.youtube.com/watch?v=' + currentId : '');
            if (newUrl === null) return;
            
            const parsedId = extraerYouTubeId(newUrl);
            if (!parsedId) {
                alert('No se pudo reconocer un ID de YouTube válido.');
                return;
            }

            const newDur = prompt('Duración del video (ej. 1:45):', currentDur) || currentDur;
            const newTitle = prompt('Título o descripción corta:', currentTitle) || currentTitle;

            card.setAttribute('data-youtube-id', parsedId);
            const thumb = card.querySelector('.video-card-thumb');
            if (thumb) {
                thumb.src = 'https://i.ytimg.com/vi/' + parsedId + '/hqdefault.jpg';
                thumb.setAttribute('referrerpolicy', 'no-referrer');
            }
            if (currentDurElem) currentDurElem.innerText = newDur;
            if (currentTitleElem) currentTitleElem.innerText = newTitle;

            alert('✅ Video actualizado. Recuerda hacer clic en "💾 Guardar Cambios" para guardar.');
        }

        function eliminarVideoCard(card) {
            if (!card) return;
            if (confirm('¿Estás seguro de eliminar este video del carrusel?')) {
                card.remove();
            }
        }

        function agregarNuevoVideoReview() {
            const url = prompt('Ingresa el link de YouTube del nuevo video:\n(Ej: https://www.youtube.com/watch?v=... o https://youtu.be/...)');
            if (!url) return;
            const id = extraerYouTubeId(url);
            if (!id) {
                alert('Link de YouTube no válido.');
                return;
            }
            const dur = prompt('Duración del video (ej. 1:30):', '1:30') || '1:30';
            const title = prompt('Título / Resumen:', 'Opinión DJI Osmo Pocket 3') || 'Opinión DJI Osmo Pocket 3';

            const track = document.getElementById('videoReviewsTrack');
            if (!track) return;

            const card = document.createElement('div');
            card.className = 'video-review-card';
            card.setAttribute('data-youtube-id', id);
            card.setAttribute('onclick', 'manejarClickVideoCard(this, event)');
            card.innerHTML = `
                <img class="video-card-thumb" src="https://i.ytimg.com/vi/${id}/hqdefault.jpg" referrerpolicy="no-referrer" alt="Video Review" loading="lazy">
                <div class="video-card-gradient"></div>
                <div class="video-card-badge-play">▶</div>
                <div class="video-card-info">
                    <div class="video-card-stars">★★★★★</div>
                    <div class="video-card-duration">
                        <span class="play-icon-mini">▶</span> <span class="video-duration-text" data-editable="true">${dur}</span>
                    </div>
                    <div class="video-card-title-text" data-editable="true">${title}</div>
                </div>
                <div class="video-card-admin-bar" onclick="event.stopPropagation()">
                    <button type="button" class="btn-vcard-edit" onclick="editarVideoCard(this.closest('.video-review-card'))" title="Editar link de YouTube">✏️ Editar</button>
                    <button type="button" class="btn-vcard-del" onclick="eliminarVideoCard(this.closest('.video-review-card'))" title="Eliminar video">🗑️</button>
                </div>
            `;
            track.appendChild(card);
            if (typeof initModoEdicion === 'function') {
                initModoEdicion();
            }
            alert('✅ Video agregado al carrusel. Recuerda hacer clic en "💾 Guardar Cambios" para guardar permanentemente.');
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                cerrarVideoModal();
            }
        });

        // ─── ACTUALIZACIÓN EN TIEMPO REAL AL PUBLICAR NUEVA VERSIÓN ───
        (function() {
            const CURRENT_VERSION = '<?= $app_version ?>';
            const CHECK_INTERVAL = 8000; // Chequear cada 8 segundos
            let isChecking = false;

            async function checkVersion() {
                if (isChecking) return;
                // No recargar automáticamente si el usuario está en modo de edición visual
                if (typeof ES_MODO_EDICION !== 'undefined' && ES_MODO_EDICION) return;

                isChecking = true;
                try {
                    const res = await fetch('version.php?t=' + Date.now(), { 
                        cache: 'no-store',
                        headers: { 'Cache-Control': 'no-cache', 'Pragma': 'no-cache' }
                    });
                    if (res.ok) {
                        const data = await res.json();
                        if (data && data.version && data.version !== CURRENT_VERSION) {
                            console.log('🔄 Nueva versión detectada en producción (' + data.version + '). Actualizando en tiempo real...');
                            window.location.reload();
                        }
                    }
                } catch (e) {
                    // Manejo silencioso en caso de micro-cortes de red
                } finally {
                    isChecking = false;
                }
            }

            // Chequeo periódico en segundo plano
            setInterval(checkVersion, CHECK_INTERVAL);

            // Chequeo instantáneo cuando el usuario vuelve a enfocar la pestaña
            document.addEventListener('visibilitychange', function() {
                if (document.visibilityState === 'visible') {
                    checkVersion();
                }
            });
            window.addEventListener('focus', checkVersion);
        })();

    </script>
    <div class="review-photo-backdrop" id="reviewPhotoBackdrop" onclick="this.classList.remove('open')">
        <img id="reviewPhotoBig" src="" alt="Foto de la opinión ampliada">
    </div>
    <script>
        function abrirFotoOpinion(url) {
            var b = document.getElementById('reviewPhotoBackdrop');
            var i = document.getElementById('reviewPhotoBig');
            if (!b || !i) return;
            i.src = url;
            b.classList.add('open');
        }
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                var b = document.getElementById('reviewPhotoBackdrop');
                if (b) b.classList.remove('open');
            }
        });
    </script>
</body>
</html>
