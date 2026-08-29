<?php
header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
require_once __DIR__ . '/config.php';
$landing_slug  = 'dji-osmo-pocket-4-creater-combo';
$nombre_marca  = 'DJI';
try {
    if (isset($pdo)) {
        $stmt_m = $pdo->prepare("SELECT marca FROM landings WHERE slug = ?");
        $stmt_m->execute([$landing_slug]);
        $row_m = $stmt_m->fetch(PDO::FETCH_ASSOC);
        if (!empty($row_m['marca'])) { $nombre_marca = $row_m['marca']; }
    }
} catch (Exception $e) {}
try {
    if (isset($pdo)) {
        $stmt_m = $pdo->prepare("SELECT marca FROM landings WHERE slug = ?");
        $stmt_m->execute([$landing_slug]);
        $row_m = $stmt_m->fetch(PDO::FETCH_ASSOC);
        if (!empty($row_m['marca'])) { $nombre_marca = $row_m['marca']; }
    }
} catch (Exception $e) {}
$landing_token = 'c384598dcd43f72d5e5d943491288cfd';
$precio_num    = 1127980;
$precio_fmt    = '1.127.980';
/* Digito de unidades compradas el mes pasado. Lo sortea el exportador al
   generar y queda fijo aqui: si cambiara en cada carga no seria creible.
   De este mismo numero salen los dos formatos: "6 K+" en movil y
   "Mas de 6.000" en escritorio. */
$compras_mes   = '6 K+';
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

/* ─── Banner de Mercado Libre ───
   Lo enciende o apaga el builder (Secciones > Banner de Mercado Libre). Por
   defecto apagado: no toda landing tiene publicacion en Mercado Libre, y un
   banner que lleva a una publicacion inexistente es peor que no tenerlo. */
$mostrar_ml_banner = false;

/* ─── Videos propios de la seccion de opiniones ───
   Si la carpeta `videos/` tiene archivos, el carrusel los usa y NO se carga
   nada de YouTube: sin iframes, sin su interfaz y con una fraccion del peso
   (medido: los diez embeds eran el 98% del trafico de la pagina).
   Si la carpeta esta vacia se mantiene el motor de YouTube, para que la
   seccion nunca quede en blanco. */
$videos_locales = [];
foreach (glob(__DIR__ . '/videos/*.{mp4,webm,mov,m4v}', GLOB_BRACE) ?: [] as $ruta_video) {
    if (is_file($ruta_video)) { $videos_locales[] = 'videos/' . rawurlencode(basename($ruta_video)); }
}
natsort($videos_locales);
$videos_locales = array_values($videos_locales);

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
    'nombre' => 'DJI Kit de accesorios compatible - DJI Osmo Pocket 4 Creater Combo Cámara p',
    'precio' => '$ 248.156',
    'url' => '#',
    'img' => 'img/img_1.webp',
  ),
  1 => 
  array (
    'nombre' => 'DJI Estuche de transporte premium - DJI Osmo Pocket 4 Creater Combo Cámara p',
    'precio' => '$ 169.197',
    'url' => '#',
    'img' => 'img/img_2.webp',
  ),
  2 => 
  array (
    'nombre' => 'DJI Garantia extendida 12 meses - DJI Osmo Pocket 4 Creater Combo Cámara p',
    'precio' => '$ 135.358',
    'url' => '#',
    'img' => 'img/img_3.webp',
  ),
  3 => 
  array (
    'nombre' => 'DJI Pack x2 con descuento - DJI Osmo Pocket 4 Creater Combo Cámara p',
    'precio' => '$ 1.973.965',
    'url' => '#',
    'img' => 'img/img_4.webp',
  ),
  4 => 
  array (
    'nombre' => 'DJI Repuesto original - DJI Osmo Pocket 4 Creater Combo Cámara p',
    'precio' => '$ 203.036',
    'url' => '#',
    'img' => 'img/img_1.webp',
  ),
  5 => 
  array (
    'nombre' => 'DJI Combo completo edicion limitada - DJI Osmo Pocket 4 Creater Combo Cámara p',
    'precio' => '$ 1.579.172',
    'url' => '#',
    'img' => 'img/img_2.webp',
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
    <meta name="description" content="Una cámara compacta y profesional, perfecta para creadores de contenido, viajes, vlogs, redes sociales y videos de alta calidad.

Características princip">
    <title><?= htmlspecialchars('DJI Osmo Pocket 4 Creater Combo Cámara para Vlogs 4K 120 fps CMOS 1"') ?></title>
    
    <!-- FAVICON / NAVICON -->
    <?php if (file_exists(__DIR__ . '/assets/marca/logo.svg')): ?>
        <link rel="icon" type="image/svg+xml" href="assets/marca/logo.svg">
        <link rel="apple-touch-icon" href="assets/marca/logo.svg">
    <?php elseif (file_exists(__DIR__ . '/assets/marca/logo.webp')): ?>
        <link rel="icon" type="image/webp" href="logo.webp">
        <link rel="shortcut icon" type="image/webp" href="logo.webp">
        <link rel="apple-touch-icon" href="logo.webp">
    <?php elseif (file_exists(__DIR__ . '/assets/marca/logo.png')): ?>
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
        /* En movil no se pinta: quien hace de banda gris es .product-header-block,
           que ademas lleva el titulo. En escritorio vuelve a ser el separador
           fino de siempre, porque alli la cabecera se va a la columna derecha. */
        .navbar-gallery-spacer { display: none; }

        /* Cabecera del producto (titulo + calificacion + compras).
           Es el primer hijo del grid, asi que en movil sale sobre la galeria
           con el aspecto de la banda gris de antes. */
        .product-header-block {
            background-color: #ededed;
            border-bottom: 1px solid #e0e0e0;
            padding: 11px 20px;
            box-sizing: border-box;
            /* .product-grid-layout es flex con gap:20px en movil; este margen
               lo cancela para que la galeria quede pegada a la banda, igual
               que cuando la banda vivia fuera del grid. */
            margin-bottom: -20px;
        }
        .product-header-block .product-title {
            margin: 0;
            padding: 0;
        }
        /* Piezas que solo se leen en escritorio. */
        .rc-word { display: none; }
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
            /* Escritorio: separador fino arriba y la cabecera se muda a la
               columna derecha, sobre el precio (la coloca el grid mas abajo). */
            .navbar-gallery-spacer {
                display: block;
                width: 100%;
                height: 28px;
                background-color: #ededed;
                border-bottom: 1px solid #e0e0e0;
            }
            .product-header-block {
                background-color: transparent;
                border-bottom: none;
                padding: 0;
                margin-bottom: 0;
            }
            /* Titulo arriba y calificacion debajo, no uno al lado del otro. */
            .spacer-head { flex-direction: column; align-items: flex-start; gap: 6px; }
            .rc-word { display: inline; }
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
                column-gap: 48px;
                row-gap: 14px;
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

        /* El precio tachado va SOBRE el precio final; el descuento y los
           Puntos Colombia se leen a su lado, en la misma linea. */
        .price-block { margin-bottom: 8px; }
        .price-row { display: flex; align-items: center; gap: 12px; row-gap: 6px; flex-wrap: wrap; }

        /* ─── PUNTOS COLOMBIA (al lado del precio final) ───
           Acumulacion oficial: 1 punto por cada $700 de compra. */
        .puntos-colombia-row {
            display: flex;
            align-items: center;
            gap: 7px;
            font-family: var(--font-heading);
            font-size: 13px;
            line-height: 1.3;
            color: #662D91;
            user-select: none;
            /* Si no cabe junto al precio, baja entera a su propia linea en
               vez de partirse a la mitad. */
            flex-basis: 100%;
        }
        .puntos-colombia-row .pc-mark { flex-shrink: 0; display: block; height: 22px; width: auto; }
        .puntos-colombia-row .pc-text { font-weight: 500; letter-spacing: -0.01em; }
        .puntos-colombia-row .pc-text b { font-weight: 800; }
        .puntos-colombia-row .pc-info {
            flex-shrink: 0; width: 14px; height: 14px; color: #662D91; opacity: .55;
            cursor: help; transition: opacity .15s ease;
        }
        .puntos-colombia-row .pc-info:hover { opacity: 1; }

        @media (min-width: 380px) {
            /* A partir de aqui ya suele caber en la misma linea que el precio. */
            .puntos-colombia-row { flex-basis: auto; }
        }
        @media (max-width: 480px) {
            .puntos-colombia-row { font-size: 12.5px; gap: 6px; }
        }
        .current-price {
            font-family: var(--font-heading);
            font-size: 30px;
            font-weight: 700;
            color: #101010;
            letter-spacing: -0.025em;
        }
        .old-price {
            display: block;
            font-size: 15px;
            color: #7A7A7A;
            text-decoration: line-through;
            font-weight: 500;
            margin-bottom: 2px;
        }
        .discount-pill {
            background: #F2F3F5;
            color: #202124;
            font-size: 12px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 980px;
            letter-spacing: -0.01em;
        }

        /* ─── CAJA DE ENVÍO URGENTE Y CONTADOR (ESTILO MERCADOLIBRE / APPLE) ─── */
        .apple-shipping-urgency-box {
            background: #FAFAFA;
            border: 1px solid #D1D5DB;
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
            fill: #27272A;
            stroke: #27272A;
        }
        .shipping-lead-text {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        .shipping-badge-highlight {
            color: #18181B;
            font-size: 14.5px;
            font-weight: 600;
            letter-spacing: -0.01em;
        }
        .shipping-badge-highlight b {
            font-weight: 800;
        }
        .shipping-timer-subtext {
            font-size: 12.5px;
            color: #686868;
            font-weight: 400;
        }
        .shipping-countdown-val {
            color: #686868;
            font-weight: 700;
            letter-spacing: 0.2px;
        }

        /* ─── COMPRA DIRECTA: "Comprar ahora" + "Agregar al carrito" ───
           Van justo bajo precio/envio, antes de elegir presentacion. */
        .direct-purchase-row {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 18px;
        }
        .btn-buy-now,
        .btn-add-cart-outline {
            width: 100%;
            height: 52px;
            border-radius: 12px;
            font-family: var(--font-heading);
            font-size: 15.5px;
            font-weight: 700;
            letter-spacing: -0.01em;
            cursor: pointer;
            transition: transform 0.15s ease, box-shadow 0.15s ease, background-color 0.15s ease;
        }
        .btn-buy-now {
            background-color: #101010;
            color: #ffffff;
            border: none;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.18);
        }
        .btn-buy-now:hover {
            background-color: #000000;
            transform: scale(1.01);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.24);
        }
        .btn-add-cart-outline {
            background-color: #ffffff;
            color: #101010;
            border: 1px solid #D1D5DB;
        }
        .btn-add-cart-outline:hover {
            background-color: #FAFAFA;
            border-color: #b8bcc4;
        }

        .variant-block { margin-bottom: 16px; border-top: 1px solid var(--border-light); padding-top: 14px; }
        .variant-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; font-size: 13px; }
        .variant-label { font-weight: 600; color: #1d1d1f; letter-spacing: -0.01em; }
        .variant-label span { font-weight: 400; color: var(--text-muted); }
        .swatches-row { display: flex; gap: 12px; align-items: center; }
        .swatch-circle { width: 34px; height: 34px; border-radius: 50%; cursor: pointer; position: relative; border: 2px solid transparent; box-shadow: inset 0 0 0 1px rgba(0,0,0,0.12); transition: all 0.2s ease; }
        .swatch-circle.active { border-color: var(--primary); transform: scale(1.08); }

        .size-block { margin-bottom: 20px; }

        .desktop-action-row { display: none; gap: 16px; align-items: center; margin-bottom: 22px; }
        .qty-label { font-size: 13px; font-weight: 600; color: #1d1d1f; letter-spacing: -0.01em; }
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
        /* Cabecera sin plegado: la descripcion la despliega "Ver mas". */
        .accordion-header-fijo { cursor: default; }
        .accordion-body { display: none; padding-bottom: 15px; font-size: 13.5px; color: #424245; line-height: 1.55; }
        .accordion-body.open { display: block; }

        /* ─── TABLA DE DETALLES DEL PRODUCTO (ESTILO AMAZON) ───
           Hay dos copias en el HTML, generadas del mismo array de PHP: la de
           movil va en el flujo sobre la descripcion y la de escritorio bajo la
           galeria. Solo se ve una a la vez. */
        .product-details-block { margin-bottom: 20px; }
        .pd-desktop { display: none; }
        .product-details-title {
            font-family: var(--font-heading);
            font-size: 15px;
            font-weight: 700;
            color: #1d1d1f;
            letter-spacing: -0.01em;
            margin: 0 0 10px 0;
        }
        .product-details-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            font-family: var(--font-body);
            font-size: 13px;
            line-height: 1.45;
        }
        .product-details-table th,
        .product-details-table td {
            padding: 9px 12px;
            text-align: left;
            vertical-align: top;
            word-break: break-word;
        }
        .product-details-table th { width: 42%; font-weight: 600; color: #565959; }
        .product-details-table td { color: #0f1111; }
        /* Filas intercaladas gris/blanco, como la ficha de detalles de Amazon */
        .product-details-table tr:nth-child(odd)  { background: #f5f5f5; }
        .product-details-table tr:nth-child(even) { background: #ffffff; }

        /* ─── DESCRIPCION POR BLOQUES ─── */
        .desc-cuerpo > p { margin: 0 0 4px 0; }
        .desc-subtitulo {
            font-family: var(--font-heading);
            font-size: 13.5px;
            font-weight: 700;
            color: #1d1d1f;
            letter-spacing: -0.01em;
            margin: 15px 0 7px 0;
        }
        .desc-lista {
            margin: 0;
            padding-left: 18px;
            list-style: disc;
        }
        .desc-lista li { margin-bottom: 6px; }
        .desc-lista:last-of-type li:last-child { margin-bottom: 0; }
        .desc-lista li::marker { color: #86868b; }

        /* ─── DESCRIPCION PLEGADA CON "VER MAS" ───
           El recorte se hace por alto, no cortando caracteres: asi no se parte
           ninguna etiqueta del texto y el HTML sigue siendo valido.
           El max-height de apertura es un tope holgado, no la altura real: hace
           falta un valor concreto para que la transicion pueda animarse. */
        .desc-item .desc-cuerpo {
            max-height: 104px;
            overflow: hidden;
            transition: max-height 0.38s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .desc-item.desc-abierta .desc-cuerpo { max-height: 1400px; }

        .desc-toggle {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 3px;
            padding: 0;
            background: none;
            border: none;
            font-family: var(--font-heading);
            font-size: 13px;
            font-weight: 600;
            color: #007185;
            cursor: pointer;
        }
        .desc-toggle:hover .desc-toggle-txt { text-decoration: underline; }
        /* Flecha fina que gira al desplegar */
        .desc-chevron {
            width: 15px;
            height: 15px;
            flex-shrink: 0;
            transition: transform 0.38s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .desc-item.desc-abierta .desc-chevron { transform: rotate(180deg); }

        /* ─── BANNER MERCADOLIBRE ───
           Specs tomadas del design system real de Mercado Libre (Andes):
           boton radio 6px / alto 48px / peso 600, azul #3483fa, texto rgba(0,0,0,.9) y .55 */
        .ml-promo-banner-wrap { display: block; width: 100%; max-width: 100%; margin: 25px 0 10px 0; background: #ffe600; box-sizing: border-box; cursor: pointer; overflow-x: hidden; text-decoration: none; color: inherit; transition: background-color 0.15s ease; }
        .ml-promo-banner-wrap:hover { background: #ffea1a; }
        .ml-promo-banner-wrap:focus-visible { outline: 3px solid #2d3277; outline-offset: -3px; }

        /* El amarillo llega a los bordes; el contenido se topa a 1200px como en Mercado Libre */
        .ml-banner-inner { max-width: 1280px; margin: 0 auto; padding: 18px 24px; display: flex; align-items: center; justify-content: space-between; gap: 20px; width: 100%; box-sizing: border-box; }

        .ml-banner-left { display: flex; align-items: center; gap: 18px; flex-shrink: 0; }
        .ml-logo-img { height: 92px; max-width: 300px; width: auto; object-fit: contain; display: block; }
        .ml-banner-divider { width: 1px; height: 62px; background: rgba(0, 0, 0, 0.12); flex-shrink: 0; }

        .ml-banner-center { flex: 1 1 auto; min-width: 0; display: flex; flex-direction: column; align-items: flex-start; justify-content: center; gap: 6px; overflow: hidden; padding-right: 8px; }
        .ml-headline { max-width: 100%; font-family: var(--font-ml); font-weight: 700; font-size: 18px; line-height: 1.25; color: rgba(0, 0, 0, 0.9); }
        .ml-subline { max-width: 100%; font-family: var(--font-ml); font-weight: 400; font-size: 13.5px; line-height: 1.3; color: rgba(0, 0, 0, 0.55); }

        .ml-banner-right { flex-shrink: 0; display: flex; align-items: center; gap: 18px; }

        /* CTA con las specs exactas del boton "loud" de Andes */
        .ml-cta { display: inline-flex; align-items: center; justify-content: center; height: 48px; padding: 0 24px; background: #3483fa; color: #ffffff; border-radius: 6px; font-family: var(--font-ml); font-size: 16px; font-weight: 600; line-height: 48px; white-space: nowrap; transition: background-color 0.15s ease; -webkit-font-smoothing: antialiased; }
        .ml-promo-banner-wrap:hover .ml-cta { background: #2968c8; }

        @media (max-width: 1280px) {
            .ml-banner-inner { padding: 18px 20px; gap: 16px; flex-wrap: wrap; justify-content: center; }
            .ml-banner-left { gap: 14px; }
            .ml-logo-img { height: 76px; max-width: 250px; }
            .ml-banner-divider { display: none; }
            .ml-banner-center { flex: 1 1 100%; align-items: center; text-align: center; gap: 5px; order: 2; padding-right: 0; }
            .ml-banner-right { flex: 1 1 100%; order: 3; flex-direction: column; gap: 14px; }
        }

        @media (max-width: 480px) {
            .ml-banner-inner { padding: 16px 16px; }
            .ml-logo-img { height: 64px; max-width: 205px; }
            .ml-headline { font-size: 16px; }
            .ml-subline { font-size: 12.5px; }
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
        /* Escritorio usa la misma columna unica que movil: la cabecera de
           opiniones arriba a lo ancho y el listado debajo (antes era
           330px 1fr con resumen sticky). */

        /* ─── COLUMNA IZQUIERDA: RESUMEN AMAZON ─── */
        .reviews-summary-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 0;
            box-sizing: border-box;
        }
        /* El resumen se reduce al titulo en todos los anchos: la nota y el
           recuento ya viajan dentro de cada opinion y duplicaban la lectura. */
        .reviews-summary-card .reviews-score-hero,
        .reviews-summary-card .reviews-total-ratings-sub { display: none; }
        .reviews-summary-card .reviews-sidebar-subtitle { margin-bottom: 0; }
        @media (max-width: 991px) {
            .customer-reviews-grid { gap: 18px; }
        }
        /* Titulo de seccion, nivel 1: mismo tamano y peso que
           "Opiniones en video" y "Tambien compraron". */
        .reviews-sidebar-title {
            font-family: var(--font-heading);
            font-size: 20px;
            font-weight: 800;
            color: #0f1111;
            margin: 0 0 3px 0;
            letter-spacing: -0.015em;
        }
        /* Mismo registro que el subtitulo de Opiniones en video. */
        .reviews-sidebar-subtitle {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: var(--text-muted, #6b7280);
            margin: 0 0 10px 0;
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
            text-align: center;
        }
        /* Titulo de bloque, nivel 2: igual que "Detalles del producto". */
        .write-review-title {
            font-size: 15px;
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
        /* ─── OPINION: una sola columna, como la ficha de Amazon ───
           Orden: quien opina, valoracion, titulo, procedencia, producto,
           fotos y por ultimo el texto. */
        .review-card-item {
            position: relative;
            padding: 20px 0;
            border-bottom: 1px solid var(--border-light);
        }
        .rev-cabecera { display: flex; align-items: center; gap: 8px; margin-bottom: 7px; flex-wrap: wrap; }
        /* Si la API no responde, el onerror lo sustituye por la inicial: el
           mismo estilo sirve para la imagen y para el recambio de texto. */
        .rev-avatar {
            width: 30px; height: 30px; flex-shrink: 0;
            border-radius: 50%;
            background: #e9eaec;
            color: #6b7280;
            font-family: var(--font-heading);
            font-size: 13px; font-weight: 700;
            display: flex; align-items: center; justify-content: center;
            object-fit: cover;
            user-select: none;
        }
        .rev-nombre { font-family: var(--font-heading); font-size: 14px; font-weight: 500; color: #0f1111; }

        /* Estrellas y sello juntos, con poco aire entre medias */
        /* Estrellas y titular en la MISMA linea: el titulo es lo que las
           estrellas califican, tenerlo suelto lo dejaba huerfano. En pantallas
           estrechas el titulo largo baja entero a la linea siguiente. */
        .rev-valoracion { display: flex; align-items: center; gap: 9px; margin-bottom: 3px; flex-wrap: wrap; row-gap: 2px; }
        /* Estrellas dibujadas, no el caracter tipografico: se ven limpias y
           del mismo tamano en cualquier sistema. */
        /* Sin separacion: en la referencia las cinco se tocan */
        .rev-estrellas { display: inline-flex; gap: 0; }
        .rev-estrellas svg { width: 16px; height: 16px; display: block; }
        .rev-estrellas .llena { color: #de7921; }
        .rev-estrellas .vacia { color: #d5d9d9; }
        /* Chip de verificacion junto al nombre: habla del comprador, no de la
           nota, asi que vive en la linea de identidad. */
            display: inline-flex; align-items: center; gap: 4px;
            padding: 2.5px 9px;
            border-radius: 999px;
            background: #e7f4ec;
            font-family: var(--font-heading);
            font-size: 11px; font-weight: 700; color: #157347;
            white-space: nowrap;
        }

        /* Cuerpo mas alto: a 13.5px el texto quedaba corto en una columna
           ancha y toda la derecha se veia vacia. */
        .rev-titulo { font-family: var(--font-heading); font-size: 15.5px; font-weight: 700; color: #0f1111; line-height: 1.3; }
        .rev-meta { font-size: 13.5px; color: #565959; line-height: 1.55; }
        .rev-meta b { font-weight: 600; color: #0f1111; }

        .rev-texto {
            font-size: 14.5px;
            color: #0f1111;
            line-height: 1.55;
            margin: 10px 0 0 0;
            max-height: 96px;
            overflow: hidden;
            transition: max-height 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .review-card-item.rev-abierta .rev-texto { max-height: 1200px; }
        .rev-leer-mas {
            display: inline-flex; align-items: center; gap: 5px;
            margin-top: 6px; padding: 0;
            background: none; border: none;
            font-family: var(--font-heading);
            font-size: 12.5px; font-weight: 600; color: #007185;
            cursor: pointer;
        }
        .rev-leer-mas:hover span { text-decoration: underline; }
        .rev-leer-mas svg { width: 14px; height: 14px; transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1); }
        .review-card-item.rev-abierta .rev-leer-mas svg { transform: rotate(180deg); }
        @media (max-width: 768px) {
            .review-card-item {
                grid-template-columns: 1fr;
                gap: 8px;
            }
        }
        .reviewer-name {
            font-weight: 700;
            font-size: 14px;
            color: #1d1d1f;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        /* El boton de borrar la propia opinion pasa a la esquina */
        .review-actions-wrap {
            position: absolute;
            top: 18px;
            right: 0;
            display: flex;
            align-items: center;
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
            .review-actions-wrap {
                justify-content: flex-start;
            }
        }
        /* Naranja de Amazon para el sello de compra verificada. */
        .rev-verificada {
            font-family: var(--font-heading);
            font-size: 12.5px;
            font-weight: 700;
            color: #C7511F;
            margin-top: 2px;
            line-height: 1.3;
        }
        /* Las burbujas van DENTRO del bloque de opiniones, encima de la linea
           que lo cierra: colgadas por debajo del separador de la ultima
           opinion se leian como algo ajeno a la lista.
           La clase la pone renderReviews solo cuando hay mas de una pagina,
           asi la ultima opinion conserva su linea si no hay paginador. */
        .reviews-list-wrap.con-paginador .review-card-item:last-child {
            border-bottom: none;
            padding-bottom: 6px;
        }
        /* ESCRITORIO: renderReviews pone .rev-dos-columnas y mete las
           tarjetas en dos wrappers .rev-col (mitad y mitad, orden preservado).
           Sin la clase (movil) la lista sigue en una columna paginada. */
        .reviews-list-wrap.rev-dos-columnas {
            display: grid;
            grid-template-columns: 1fr 1fr;
            column-gap: 48px;
            align-items: start;
        }
        .rev-col { min-width: 0; }
        /* La ultima tarjeta de CADA columna pierde su linea inferior para no
           dejar rayas sueltas colgando al fondo de las columnas. */
        .rev-col .review-card-item:last-child {
            border-bottom: none;
            padding-bottom: 6px;
        }
        /* Mientras el JS pinta las opiniones el contenedor no debe colapsar:
           la pagina se acortaba y en movil, al terminar los videos, parecia
           que ya no habia nada mas abajo. El :empty se deja de cumplir en
           cuanto entra la primera tarjeta, asi que no deja hueco despues. */
        #reviewsListContainer:empty { min-height: 70vh; }

        .reviews-pagination-row {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            margin-top: 22px;
            font-size: 12.5px;
            color: var(--text-muted);
        }
        .reviews-pagination-row:not(:empty) {
            margin-top: 12px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border-light);
        }
        /* La columna unica ocupa todo el ancho en cualquier pantalla:
           las burbujas van centradas siempre (la regla base ya lo hace). */
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
            background: #5f6368;
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
            font-weight: 800;
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
            .product-grid-layout {
                display: grid;
                grid-template-columns: 1.1fr 1fr;
                grid-template-rows: auto 1fr;
                column-gap: 48px;
                row-gap: 14px;
                align-items: start;
                max-width: 100%;
            }
            /* La galeria ocupa la columna izquierda entera; a la derecha,
               primero la cabecera y debajo el bloque de compra. */
            .product-header-block      { grid-column: 2; grid-row: 1; }
            .gallery-wrapper-desktop   { grid-column: 1; grid-row: 1 / span 2; }
            /* La columna de la galeria admite una segunda linea: ahi cae la
               tabla de detalles, que antes dejaba ese hueco en blanco. */
            .gallery-wrapper-desktop { flex-wrap: wrap; }
            .pd-desktop { display: block; flex-basis: 100%; margin-top: 26px; margin-bottom: 0; }
            .pd-movil { display: none; }
            .product-info              { grid-column: 2; grid-row: 2; }
            /* El orden del DOM es el de movil. En escritorio los botones de
               compra bajan por debajo de Presentacion y Cantidad. */
            .product-info { display: flex; flex-direction: column; }
            .product-info > *                          { order: 6; }
            .product-info > .price-block               { order: 1; }
            .product-info > .apple-shipping-urgency-box{ order: 2; }
            .product-info > .desktop-action-row        { order: 4; }
            .product-info > .direct-purchase-row       { order: 5; }
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
        /* En movil no hay flechas: se pasa de foto deslizando, que es el gesto
           que el usuario ya intenta. Las flechas tapaban parte de la imagen y
           en pantalla tactil sobran. El deslizamiento ya estaba habilitado
           sobre #imageLightbox, asi que no se pierde forma de navegar. */
        @media (max-width: 768px) {
            /* La regla base usa !important, asi que aqui hace falta tambien. */
            .lightbox-nav-btn { display: none !important; }
        }
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
        /* Anunciar envio gratis con el carrito vacio no significa nada: el
           bloque solo existe cuando hay algo que enviar. Se pliega en vez de
           desaparecer de golpe, y la barra se llena al aparecer. */
        .shipping-progress-wrap {
            background: rgba(0, 166, 80, 0.05);
            padding: 0 24px;
            border-bottom: 1px solid rgba(0, 166, 80, 0);
            max-height: 0;
            opacity: 0;
            overflow: hidden;
            transition: max-height .38s cubic-bezier(.16, 1, .3, 1),
                        padding .38s cubic-bezier(.16, 1, .3, 1),
                        opacity .26s ease,
                        border-bottom-color .3s ease;
        }
        .shipping-progress-wrap.visible {
            max-height: 92px;
            padding: 12px 24px;
            opacity: 1;
            border-bottom-color: rgba(0, 166, 80, 0.15);
        }
        .shipping-progress-text {
            font-size: 12.5px;
            font-weight: 700;
            color: #00a650;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 6px;
            transform: translateY(-5px);
            opacity: 0;
            transition: transform .4s cubic-bezier(.16, 1, .3, 1) .08s, opacity .4s ease .08s;
        }
        .shipping-progress-wrap.visible .shipping-progress-text {
            transform: none;
            opacity: 1;
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
            width: 0;
            border-radius: 980px;
            transition: width .75s cubic-bezier(.16, 1, .3, 1) .14s;
        }
        .shipping-progress-wrap.visible .shipping-bar-fill { width: 100%; }
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
            letter-spacing: -0.015em;
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
        .video-carousel-wrap { position: relative; }

        .video-reviews-carousel-track {
            display: flex;
            gap: 14px;
            /* Sin desplazamiento nativo: el impulso del navegador recorria
               varias tarjetas de un gesto y ademas peleaba con el reciclado
               del bucle. La posicion la fija el JS, una tarjeta por gesto.
               touch-action: pan-y deja libre el desplazamiento vertical de la
               pagina; solo se captura el horizontal. */
            overflow-x: hidden;
            touch-action: pan-y;
            outline: none;
            /* Sin relleno de centrado: ese hueco era el blanco de los extremos.
               El bucle recicla tarjetas, asi que siempre hay vecinas a los dos
               lados y cualquiera puede quedar centrada. */
            padding: 8px 0 18px 0;
            -webkit-overflow-scrolling: touch;
            /* Barra oculta: el desplazamiento se maneja con las flechas */
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        .video-reviews-carousel-track::-webkit-scrollbar { display: none; }

        /* Indicador discreto: barra corta.
           Se descarto el contador "n / total" porque con varias tarjetas a la
           vista jamas alcanza el total (se queda en 5/10) y se lee como roto. */
        .video-carousel-progreso {
            position: relative;
            width: 92px;
            height: 3px;
            margin: 4px auto 0 auto;
            border-radius: 999px;
            background: #e5e7eb;
            overflow: hidden;
        }
        .video-carousel-progreso .vc-barra {
            position: absolute;
            top: 0;
            bottom: 0;
            border-radius: 999px;
            background: #1f2937;
            transition: left 0.18s ease, width 0.18s ease;
        }

        /* ─── TARJETA DE SHORT ───
           Video vertical 9:16 arriba y franja de producto abajo. Solo la
           tarjeta activa reproduce; las demas quedan atenuadas. */
        .video-review-card {
            flex: 0 0 208px;
            border-radius: 16px;
            overflow: hidden;
            background: #ffffff;
            cursor: pointer;
            scroll-snap-align: center;
            box-shadow: 0 3px 14px rgba(0,0,0,0.10);
            /* Sin atenuar los lados: las vecinas se ven enteras y a color;
               la activa se distingue por tamano y sombra, no por opacidad. */
            transform: scale(0.95);
            transition: transform 0.32s cubic-bezier(0.2, 0.8, 0.2, 1), box-shadow 0.32s ease;
            user-select: none;
        }
        .video-review-card.activa {
            transform: scale(1);
            box-shadow: 0 12px 30px rgba(0,0,0,0.20);
        }

        /* ─── MOVIL: una tarjeta centrada y media a cada lado ───
           Las medidas van en vw, no en %: el % se resolveria contra el
           contenido de la pista, que su propio relleno ya encogio, y la
           tarjeta saldria diminuta.
           Con tarjeta = 50vw - gap entran justo la central entera y la mitad
           de cada vecina; el relleno de 25vw + gap/2 es lo que permite que la
           primera y la ultima tambien lleguen al centro. */
        @media (max-width: 640px) {
            /* El carrusel va de borde a borde; el encabezado conserva su margen. */
            .video-reviews-section { padding-left: 0; padding-right: 0; }
            .video-reviews-header { padding-left: 16px; padding-right: 16px; }

            .video-review-card { flex: 0 0 calc(50vw - 14px); border-radius: 14px; }
            /* Sin relleno lateral: lo aporta el bucle recirculando tarjetas. */
            .video-reviews-carousel-track { padding-left: 0; padding-right: 0; }
        }

        .vs-media {
            position: relative;
            aspect-ratio: 9 / 16;
            background: #0b0b0d;
            overflow: hidden;
        }
        .vs-thumb {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        /* El reproductor se monta encima de la miniatura solo en la activa.
           pointer-events:none deja el iframe fuera del alcance del raton: sin
           eso se puede pulsar el titulo o el logo y YouTube abre su pagina. */
        .vs-player {
            position: absolute;
            inset: 0;
            opacity: 0;
            transition: opacity 0.4s ease;
            pointer-events: none;
        }
        .vs-player iframe { width: 100%; height: 100%; border: 0; display: block; }
        /* Motor de video propio. El <video> ocupa la tarjeta entera y su primer
           fotograma hace de portada gracias a preload="metadata", asi que aqui
           no hacen falta ni miniatura ni el juego de opacidades que existia
           solo para tapar la interfaz de YouTube. */
        .vs-video {
            position: absolute; inset: 0;
            width: 100%; height: 100%;
            object-fit: cover; display: block;
            background: #0b0b0d;
            pointer-events: none;   /* lo unico pulsable es el boton de sonido */
        }
        .video-review-card.reproduciendo .vs-player { opacity: 1; }
        /* La miniatura se apaga con un retraso pequeno: si se fuera a la vez
           que aparece el video se veria un parpadeo negro entre medias. */
        .vs-thumb { transition: opacity 0.4s ease 0.12s; }
        .video-review-card.reproduciendo .vs-thumb { opacity: 0; }
        /* Nada del video es seleccionable ni arrastrable. */
        .vs-media { -webkit-user-select: none; user-select: none; -webkit-touch-callout: none; }

        /* Boton de sonido */
        .vs-sonido {
            position: absolute;
            right: 8px;
            bottom: 8px;
            z-index: 4;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            border: 1px solid rgba(255,255,255,0.35);
            background: rgba(0,0,0,0.5);
            backdrop-filter: blur(3px);
            color: #ffffff;
            display: none;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background 0.2s ease, transform 0.2s ease;
        }
        .video-review-card.activa .vs-sonido { display: flex; }
        .vs-sonido:hover { background: rgba(0,0,0,0.72); transform: scale(1.08); }
        .vs-ico { width: 17px; height: 17px; }
        .vs-ico-audio { display: none; }
        .video-review-card.con-sonido .vs-ico-mute { display: none; }
        .video-review-card.con-sonido .vs-ico-audio { display: block; }

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

        .reviewer-place { font-size: 11.5px; color: #86868b; }
        /* ─── FOTOS DE LA OPINION ───
           En fila y desplazables: con dos caben en pantalla y no hay nada que
           desplazar, y a partir de la tercera se recorren de lado sin que la
           tarjeta crezca a lo alto. Sin barra a la vista. */
        .review-photos {
            display: flex;
            flex-wrap: nowrap;
            gap: 10px;
            margin-top: 12px;
            overflow-x: auto;
            scroll-snap-type: x proximity;
            scrollbar-width: none;
            -ms-overflow-style: none;
            touch-action: pan-x pan-y;
            padding-bottom: 2px;
        }
        .review-photos::-webkit-scrollbar { display: none; }
        .review-photo {
            flex: 0 0 auto;
            width: 148px;
            height: 148px;
            object-fit: cover;
            border-radius: 10px;
            border: 1px solid var(--border-light);
            cursor: zoom-in;
            transition: transform .2s ease;
            background: #fbfbfd;
            scroll-snap-align: start;
        }
        .review-photo:hover { transform: scale(1.03); }
        @media (max-width: 480px) {
            .review-photo { width: 132px; height: 132px; }
        }
        /* Fila del voto: boton pulsable + contador. Antes solo estaba el
           contador, que anunciaba votos sin ofrecer forma de votar. */
        .rev-util-fila { display: flex; align-items: center; gap: 12px; margin-top: 12px; flex-wrap: wrap; }
        .rev-util {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 5px 11px;
            border: 1px solid #e3e5e5;
            border-radius: 999px;
            background: #f7f8f8;
            font-family: var(--font-heading);
            /* Icono y texto en un gris apenas mas oscuro que el fondo del
               boton: presente pero discreto, sin competir con la opinion. */
            font-size: 11.5px; font-weight: 600; color: #8a8f8f;
            cursor: pointer;
            transition: background .18s ease, border-color .18s ease, color .18s ease;
        }
        .rev-util svg { width: 13px; height: 13px; }
        .rev-util:hover { background: #eef0f0; border-color: #d5d9d9; color: #0f1111; }
        /* Votado: gris un punto mas oscuro que el de reposo. Suficiente para
           distinguirlo, sin que el boton compita con el texto de la opinion. */
        .rev-util.votado { background: #e8eaea; border-color: #c9cdcd; color: #828787; }
        .rev-util.votado:hover { background: #dfe2e2; border-color: #b9bfbf; }
        .rev-util-cuenta { font-size: 12.5px; color: #565959; }
        /* Visor de las fotos de una opinion. Antes mostraba solo la imagen
           tocada y no habia manera de llegar a las demas. Ahora es una pista
           deslizable con todas, una por pantalla y sin flechas, igual que el
           visor de la galeria. */
        .review-photo-backdrop { position: fixed; inset: 0; z-index: 10000; background: rgba(0,0,0,.88); display: none; flex-direction: column; align-items: center; justify-content: center; padding: 24px 0 26px; }
        .review-photo-backdrop.open { display: flex; }
        .rvf-pista {
            display: flex; width: 100%; flex: 1; min-height: 0;
            overflow-x: auto; overflow-y: hidden;
            scroll-snap-type: x mandatory;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
        }
        .rvf-pista::-webkit-scrollbar { display: none; }
        .rvf-slide { flex: 0 0 100%; scroll-snap-align: center; display: flex; align-items: center; justify-content: center; padding: 0 18px; box-sizing: border-box; }
        .rvf-slide img { max-width: 100%; max-height: 100%; border-radius: 10px; object-fit: contain; }
        .rvf-puntos { display: flex; gap: 6px; margin-top: 16px; height: 6px; }
        .rvf-punto { width: 6px; height: 6px; border-radius: 999px; background: rgba(255,255,255,.35); transition: background .2s ease, width .2s ease; }
        .rvf-punto.activo { background: #ffffff; width: 16px; }
        .rvf-cerrar {
            position: absolute; top: 14px; right: 14px; width: 40px; height: 40px;
            border: none; border-radius: 50%; background: rgba(255,255,255,.14);
            color: #ffffff; font-size: 16px; line-height: 1; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
        }
        .rvf-cerrar:hover { background: rgba(255,255,255,.24); }
    </style>
    <script src="https://unpkg.com/@dotlottie/player-component@latest/dist/dotlottie-player.mjs" type="module"></script>
</head>
<body class="<?= $es_modo_edicion ? 'modo-edicion-activo' : '' ?>" style="<?= $es_modo_edicion ? 'margin-top: 50px;' : '' ?>">
<?php if (!$es_modo_edicion): ?>
    <!-- ═══ SKELETON DE CARGA ═══
         Cubre la pagina desde el primer pintado con siluetas de lo que viene
         (barra, galeria, titulo, precio, botones) y se desvanece al estar el
         DOM listo. Al recargar, la pagina ademas empieza SIEMPRE arriba. -->
    <style>
        #esqueleto { position: fixed; inset: 0; z-index: 99998; background: #ffffff;
                     display: flex; flex-direction: column; align-items: center;
                     opacity: 1; transition: opacity .28s ease; pointer-events: none; }
        #esqueleto.fuera { opacity: 0; }
        .esq-bloque { background: #eceef1; border-radius: 10px; position: relative; overflow: hidden; }
        .esq-bloque::after { content: ''; position: absolute; inset: 0;
                             background: linear-gradient(90deg, transparent, rgba(255,255,255,.65), transparent);
                             transform: translateX(-100%); animation: esqBrillo 1.15s ease-in-out infinite; }
        @keyframes esqBrillo { to { transform: translateX(100%); } }
        .esq-col { width: min(540px, calc(100vw - 40px)); display: flex; flex-direction: column; gap: 14px; padding-top: 14px; }
        #esqueleto .esq-barra   { width: 100vw; height: 36px; border-radius: 0; }
        #esqueleto .esq-nav     { width: 100vw; height: 58px; border-radius: 0; margin-bottom: 4px; }
        #esqueleto .esq-galeria { aspect-ratio: 1 / 1; width: 100%; }
        #esqueleto .esq-titulo  { height: 22px; width: 86%; }
        #esqueleto .esq-linea   { height: 14px; width: 55%; }
        #esqueleto .esq-precio  { height: 30px; width: 42%; }
        #esqueleto .esq-boton   { height: 48px; width: 100%; border-radius: 12px; }
        @media (prefers-reduced-motion: reduce) { .esq-bloque::after { animation: none; } }
    </style>
    <div id="esqueleto" aria-hidden="true">
        <div class="esq-bloque esq-barra"></div>
        <div class="esq-bloque esq-nav"></div>
        <div class="esq-col">
            <div class="esq-bloque esq-galeria"></div>
            <div class="esq-bloque esq-titulo"></div>
            <div class="esq-bloque esq-linea"></div>
            <div class="esq-bloque esq-precio"></div>
            <div class="esq-bloque esq-boton"></div>
        </div>
    </div>
    <script>
        /* La recarga (y la vuelta desde el checkout) empieza arriba: el
           restablecimiento de scroll del navegador dejaria al usuario en
           mitad de una pagina que aun se esta armando. */
        if ('scrollRestoration' in history) history.scrollRestoration = 'manual';
        window.scrollTo(0, 0);
        /* Solo al volver desde la bfcache: en la carga normal `pageshow`
           se dispara tras `load` (con los iframes, segundos despues de ser
           interactiva) y devolvia al usuario arriba, ademas de romper los
           deep-links con ancla. La recarga normal ya la cubren el scrollTo
           sincrono de arriba y scrollRestoration='manual'. */
        window.addEventListener('pageshow', function (e) { if (e.persisted) window.scrollTo(0, 0); });

        (function () {
            var esq = document.getElementById('esqueleto');
            if (!esq) return;
            var fuera = false;
            function retirar() {
                if (fuera) return; fuera = true;
                esq.classList.add('fuera');
                setTimeout(function () { if (esq.parentNode) esq.parentNode.removeChild(esq); }, 340);
            }
            /* Con el DOM listo la pagina ya tiene su maquetacion; un respiro
               minimo evita el parpadeo en cargas rapidas. */
            if (document.readyState !== 'loading') { setTimeout(retirar, 120); }
            else document.addEventListener('DOMContentLoaded', function () { setTimeout(retirar, 120); });
            /* Tope de seguridad: pase lo que pase, el skeleton nunca se queda. */
            setTimeout(retirar, 2500);
        })();
    </script>
    <?php endif; ?>

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
    <div class="top-announcement"><span data-editable="true">ENVIO GRATIS A TODA COLOMBIA</span></div>

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
            <?php if (file_exists(__DIR__ . '/assets/marca/logo.svg')): ?>
                <img src="assets/marca/logo.svg" class="brand-logo-img" alt="<?= htmlspecialchars('DJI') ?>">
            <?php elseif (file_exists(__DIR__ . '/assets/marca/logo.webp')): ?>
                <img src="assets/marca/logo.webp" class="brand-logo-img" alt="<?= htmlspecialchars('DJI') ?>">
            <?php else: ?>
                <span class="brand-logo-text" data-editable="true"><?= htmlspecialchars('DJI') ?></span>
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
                    <?php if (file_exists(__DIR__ . '/assets/marca/logo.svg')): ?>
                        <img src="assets/marca/logo.svg" style="height:40px; max-width:130px; object-fit:contain; display:block;" alt="<?= htmlspecialchars('DJI') ?>">
                    <?php elseif (file_exists(__DIR__ . '/assets/marca/logo.webp')): ?>
                        <img src="assets/marca/logo.webp" style="height:40px; max-width:130px; object-fit:contain; display:block;" alt="<?= htmlspecialchars('DJI') ?>">
                    <?php else: ?>
                        <span class="nav-menu-brand-text"><?= htmlspecialchars('DJI') ?></span>
                    <?php endif; ?>
                </div>
                <button class="nav-menu-close-btn" onclick="toggleNavMenu()" aria-label="Cerrar menú">✕</button>
            </div>
            <nav class="nav-menu-links">
                <a href="#productSection" class="nav-menu-link" onclick="navegarSeccion(event, 'productSection')">
                    Producto
                </a>
                <a href="#videoReviewsSection" class="nav-menu-link" onclick="navegarSeccion(event, 'videoReviewsSection')">
                    Opiniones en video
                </a>
                <a href="#customerReviewsSection" class="nav-menu-link" onclick="navegarSeccion(event, 'customerReviewsSection')">
                    Opiniones de clientes
                </a>
                
            </nav>
        </div>
    </div>

    <!-- SEPARACION GRIS CLARO ENTRE NAVBAR Y GALERIA (solo escritorio) -->
    <div class="navbar-gallery-spacer"></div>

    <!-- 4. CONTENIDO PRINCIPAL -->
    <main class="landing-container" id="productSection">
        <div class="product-grid-layout">

            <!-- CABECERA DEL PRODUCTO: titulo, calificacion y compras.
                 Una sola copia en el DOM. En movil encabeza la pagina sobre
                 la galeria (con la banda gris); en escritorio la CSS la
                 coloca arriba de la columna derecha, sobre el precio. -->
            <div class="product-header-block">
                <div class="spacer-head">
                    <h1 class="product-title" data-editable="true"><?= htmlspecialchars('DJI Osmo Pocket 4 Creater Combo Cámara para Vlogs 4K 120 fps CMOS 1"') ?></h1>
                    <div class="rating-row">
                        <span class="rating-number">4.9</span>
                        <div class="stars-container">★★★★★</div>
                        <span class="reviews-count" data-editable="true">(48)</span>)</span>
                    </div>
                </div>
                <?php if (trim($compras_mes) !== ''): ?>
                <div class="bought-month"><strong><?= htmlspecialchars($compras_mes) ?> K+ comprados</strong> el mes pasado</div>
                <?php endif; ?>
            </div>

            <!-- COLUMNA 1: GALERÍA CON SLIDE Y PUNTICOS INDICADORES -->
            <?php
                /* Detalles del producto. Los valores salen de la propia ficha:
                   la marca la trae la base de datos y el resto ya figura en la
                   descripcion, asi que no hay dato inventado.
                   La tabla se pinta DOS veces, desde este mismo array: en
                   escritorio bajo la galeria (donde sobraba espacio) y en movil
                   dentro del flujo, sobre la descripcion. La CSS enseña solo la
                   que corresponde, asi que el lector nunca ve las dos. */
                $detalles_producto = [
                    'Marca'     => $nombre_marca,
                    'Modelo'    => 'DJI Osmo Pocket 4 Creater Combo Cámara para',
                    'Contenido' => '1 Unidad',
                    'Garant\u00eda'  => '3 a\u00f1os',
                ];
            ?>

            <section class="gallery-wrapper-desktop">
                <!-- MINIATURAS DESKTOP (IZQUIERDA) -->
                <div class="gallery-thumbnails-strip" id="galleryThumbsStrip"></div>

                <div class="gallery-slider-container">
                    <div class="main-image-wrap" id="mainGallerySlider" onclick="abrirLightbox(activeImgIndex)" title="Haz clic para ampliar">
                        <img id="mainImage" src="img/img_1.webp" alt="<?= htmlspecialchars('DJI Osmo Pocket 4 Creater Combo Cámara para Vlogs 4K 120 fps CMOS 1"') ?>">
                    </div>
                    <!-- PUNTICOS INDICADORES DE LA GALERÍA (solo móvil) -->
                    <div class="gallery-dots-indicator" id="galleryDotsIndicator"></div>
                </div>

                <!-- DETALLES DEL PRODUCTO (solo escritorio): baja a una segunda
                     linea de la columna y ocupa el hueco que dejaba la galeria. -->
                <div class="product-details-block pd-desktop">
                    <h3 class="product-details-title" data-editable="true">Detalles del producto</h3>
                    <table class="product-details-table">
                        <tbody>
                        <?php foreach ($detalles_producto as $etiqueta => $valor): ?>
                            <tr>
                                <th scope="row"><?= htmlspecialchars($etiqueta) ?></th>
                                <td><?= htmlspecialchars($valor) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- COLUMNA 2: INFORMACIÓN Y COMPRA -->
            <section class="product-info">
                <div class="price-block">
                    <span class="old-price" data-editable="true">$ 3.150.980</span>

                    <div class="price-row">
                        <span class="current-price" data-editable="true">$ 1.127.980</span>
                        <span class="discount-pill" data-editable="true">-64%</span>

                        <?php
                        /* Puntos Colombia: 1 punto por cada $700 de compra (tasa
                       oficial de acumulacion). Se calcula del precio real de la
                       landing, asi que cambia solo si cambia el precio. */
                            $pc_puntos = (int)floor(((int)$precio_num) / PUNTOS_COLOMBIA_PESOS_POR_PUNTO);
                        ?>
                        <?php if ($pc_puntos > 0): ?>
                    <div class="puntos-colombia-row">
                    <!-- Marca de Puntos Colombia: archivo del proyecto.
                         Lleva aria-hidden porque el texto contiguo ya dice
                         "Puntos Colombia" y repetirlo sobraria al leerlo. -->
                    <img class="pc-mark" src="assets/sellos/puntoscol.svg" alt="" aria-hidden="true">
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
                    </div>
                </div>

                <!-- CAJA DE ENVÍO URGENTE Y CONTADOR PERSISTENTE -->
                <div class="apple-shipping-urgency-box">
                    <div class="shipping-lead-row">
                        <svg class="shipping-flash-icon" viewBox="0 0 24 24" width="20" height="20" fill="#27272A" stroke="#27272A" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
                        </svg>
                        <div class="shipping-lead-text">
                            <span class="shipping-badge-highlight" data-editable="true">Llega gratis <b>mañana</b></span>
                            <div class="shipping-timer-subtext">
                                <span data-editable="true">Comprando dentro de las próximas</span> <span class="shipping-countdown-val" id="shippingCountdown">20 h 40 min</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- COMPRA DIRECTA: dos botones, justo bajo precio/envio -->
                <div class="direct-purchase-row">
                    <button type="button" class="btn-buy-now" onclick="comprarAhora()" data-editable="true">Comprar ahora</button>
                    <button type="button" class="btn-add-cart-outline" onclick="agregarAlCarrito(event)" data-editable="true">Agregar al carrito</button>
                </div>

                <div class="desktop-action-row">
                    <div class="qty-label">Cantidad</div>
                    <div class="qty-controls-desktop">
                        <button class="qty-btn-desktop" onclick="cambiarCantidad(-1)">-</button>
                        <span class="qty-val-desktop" id="qtyDesktopDisplay">1</span>
                        <button class="qty-btn-desktop" onclick="cambiarCantidad(1)">+</button>
                    </div>
                </div>

                <div class="product-details-block pd-movil">
                    <h3 class="product-details-title" data-editable="true">Detalles del producto</h3>
                    <table class="product-details-table">
                        <tbody>
                        <?php foreach ($detalles_producto as $etiqueta => $valor): ?>
                            <tr>
                                <th scope="row"><?= htmlspecialchars($etiqueta) ?></th>
                                <td><?= htmlspecialchars($valor) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="accordion-item desc-item<?= $es_modo_edicion ? ' desc-abierta' : '' ?>">
                    <div class="accordion-header accordion-header-fijo">
                        <span data-editable="true">Descripción y Beneficios</span>
                    </div>
                    <div class="accordion-body open">
                        <div class="desc-cuerpo"><p data-editable="true">Una cámara compacta y profesional, perfecta para creadores de contenido, viajes, vlogs, redes sociales y videos de alta calidad.</p>
                            <p data-editable="true">Características principales</p>
                            <p data-editable="true">Sensor CMOS de 1&quot; para imágenes claras y detalladas.</p>
                            <p data-editable="true">Video en 4K para una excelente calidad de grabación.</p>
                            <p data-editable="true">Estabilización mecánica de 3 ejes para videos suaves y estables.</p>
                            <p data-editable="true">Pantalla táctil giratoria para grabar fácilmente en vertical u horizontal.</p>
                            <p data-editable="true">ActiveTrack para realizar seguimiento inteligente del sujeto.</p>
                            <p data-editable="true">Zoom 2X sin pérdida.</p>
                            <p data-editable="true">Almacenamiento interno de 107 GB.</p>
                            <p data-editable="true">Grabación en 10-bit D-Log para mayor detalle y mejores colores.</p>
                            <p data-editable="true">Compatible con DJI Mic 3 para obtener audio de alta calidad.</p>
                            <p data-editable="true">Creator Combo incluye</p>
                            <p data-editable="true">DJI Osmo Pocket 4</p>
                            <p data-editable="true">Transmisor DJI Mic 3</p>
                            <p data-editable="true">Luz de relleno</p>
                            <p data-editable="true">Lente gran angular</p>
                            <p data-editable="true">Mini trípode</p>
                            <p data-editable="true">Accesorios para transporte y protección</p>
                            <p data-editable="true">Aviso legal</p>
                            <ul class="desc-lista"><li data-editable="true">La duración de la batería depende del uso que se le dé al producto.</li></ul></div>
                        <?php if (!$es_modo_edicion): ?>
                        <button type="button" class="desc-toggle" onclick="toggleDescripcion(this)" aria-expanded="false">
                            <svg class="desc-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="m6 9 6 6 6-6"></path>
                            </svg>
                            <span class="desc-toggle-txt">Ver más</span>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="accordion-item desc-item<?= $es_modo_edicion ? ' desc-abierta' : '' ?>">
                    <div class="accordion-header accordion-header-fijo">
                        <span data-editable="true">Garantía y Devoluciones</span>
                    </div>
                    <div class="accordion-body open">
                        <div class="desc-cuerpo"><p data-editable="true">Todos nuestros productos cuentan con garantia de 3 años contra defectos de fabrica. Si no estas 100% satisfecho(a), te devolvemos tu dinero.</p></div>
                        <?php if (!$es_modo_edicion): ?>
                        <button type="button" class="desc-toggle" onclick="toggleDescripcion(this)" aria-expanded="false">
                            <svg class="desc-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="m6 9 6 6 6-6"></path>
                            </svg>
                            <span class="desc-toggle-txt">Ver más</span>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

        </div>
    </main>

    <!-- 5.2 REVIEWS WITH VIDEOS CAROUSEL (AMAZON STYLE) -->
    <section class="video-reviews-section" id="videoReviewsSection">
        <div class="video-reviews-header">
            <div class="video-reviews-title-wrap">
                <h2 class="video-reviews-main-title" data-editable="true">Opiniones y unboxings en video</h2>
                <span class="video-reviews-subtitle" data-editable="true">Mira la experiencia real de compradores verificados</span>
            </div>
            <div class="video-reviews-controls">
                <?php if ($es_modo_edicion): ?>
                    <button type="button" class="btn-add-video-card" onclick="agregarNuevoVideoReview()">➕ Agregar Video</button>
                <?php endif; ?>
            </div>
        </div>

        <div class="video-carousel-wrap">
        <div class="video-reviews-carousel-track" id="videoReviewsTrack">
<?php if ($videos_locales): ?>
            <?php foreach ($videos_locales as $i_vid => $src_vid): ?>
            <article class="video-review-card" data-video-src="<?= htmlspecialchars($src_vid, ENT_QUOTES, 'UTF-8') ?>">
                <div class="vs-media">
                    <!-- preload="metadata" trae solo la cabecera y el primer
                         fotograma: sirve de portada sin descargar el video. Al
                         llegar al centro se sube a "auto". -->
                    <video class="vs-video" src="<?= htmlspecialchars($src_vid, ENT_QUOTES, 'UTF-8') ?>"
                           preload="metadata" muted loop playsinline
                           disablepictureinpicture controlslist="nodownload noplaybackrate"></video>
                    <button type="button" class="vs-sonido" onclick="alternarSonidoShort(event)" aria-label="Activar sonido" aria-pressed="false">
                        <svg class="vs-ico vs-ico-mute" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M11 5 6 9H2v6h4l5 4V5z"/><path d="m23 9-6 6"/><path d="m17 9 6 6"/>
                        </svg>
                        <svg class="vs-ico vs-ico-audio" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M11 5 6 9H2v6h4l5 4V5z"/><path d="M15.5 8.5a5 5 0 0 1 0 7"/><path d="M18.5 5.5a9 9 0 0 1 0 13"/>
                        </svg>
                    </button>
                </div>
            </article>
            <?php endforeach; ?>
<?php else: ?>
            <article class="video-review-card" data-youtube-id="ZSqH0WNw87I" data-video-title="OJO, que no te engañen: DJI Osmo Pocket 4 Pro">
                <div class="vs-media">
                    <img class="vs-thumb" src="https://i.ytimg.com/vi/ZSqH0WNw87I/oar2.jpg" referrerpolicy="no-referrer" alt="OJO, que no te engañen: DJI Osmo Pocket 4 Pro" loading="lazy">
                    <div class="vs-player"></div>
                    <button type="button" class="vs-sonido" onclick="alternarSonidoShort(event)" aria-label="Activar sonido" aria-pressed="false">
                        <svg class="vs-ico vs-ico-mute" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M11 5 6 9H2v6h4l5 4V5z"/><path d="m23 9-6 6"/><path d="m17 9 6 6"/>
                        </svg>
                        <svg class="vs-ico vs-ico-audio" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M11 5 6 9H2v6h4l5 4V5z"/><path d="M15.5 8.5a5 5 0 0 1 0 7"/><path d="M18.5 5.5a9 9 0 0 1 0 13"/>
                        </svg>
                    </button>
                </div>
            </article>
            <article class="video-review-card" data-youtube-id="yD-401gi7eQ" data-video-title="Mi experiencia con la DJI Osmo Pocket 4P: ¿éxito o decepción?">
                <div class="vs-media">
                    <img class="vs-thumb" src="https://i.ytimg.com/vi/yD-401gi7eQ/oar2.jpg" referrerpolicy="no-referrer" alt="Mi experiencia con la DJI Osmo Pocket 4P: ¿éxito o decepción?" loading="lazy">
                    <div class="vs-player"></div>
                    <button type="button" class="vs-sonido" onclick="alternarSonidoShort(event)" aria-label="Activar sonido" aria-pressed="false">
                        <svg class="vs-ico vs-ico-mute" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M11 5 6 9H2v6h4l5 4V5z"/><path d="m23 9-6 6"/><path d="m17 9 6 6"/>
                        </svg>
                        <svg class="vs-ico vs-ico-audio" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M11 5 6 9H2v6h4l5 4V5z"/><path d="M15.5 8.5a5 5 0 0 1 0 7"/><path d="M18.5 5.5a9 9 0 0 1 0 13"/>
                        </svg>
                    </button>
                </div>
            </article>
            <article class="video-review-card" data-youtube-id="jDW9MBLrH2A" data-video-title="DJI rompe el mercado con la nueva Osmo Pocket 4P">
                <div class="vs-media">
                    <img class="vs-thumb" src="https://i.ytimg.com/vi/jDW9MBLrH2A/oar2.jpg" referrerpolicy="no-referrer" alt="DJI rompe el mercado con la nueva Osmo Pocket 4P" loading="lazy">
                    <div class="vs-player"></div>
                    <button type="button" class="vs-sonido" onclick="alternarSonidoShort(event)" aria-label="Activar sonido" aria-pressed="false">
                        <svg class="vs-ico vs-ico-mute" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M11 5 6 9H2v6h4l5 4V5z"/><path d="m23 9-6 6"/><path d="m17 9 6 6"/>
                        </svg>
                        <svg class="vs-ico vs-ico-audio" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M11 5 6 9H2v6h4l5 4V5z"/><path d="M15.5 8.5a5 5 0 0 1 0 7"/><path d="M18.5 5.5a9 9 0 0 1 0 13"/>
                        </svg>
                    </button>
                </div>
            </article>
            <article class="video-review-card" data-youtube-id="cypAm89SfrM" data-video-title="¿Vale la pena la Osmo Pocket 4?">
                <div class="vs-media">
                    <img class="vs-thumb" src="https://i.ytimg.com/vi/cypAm89SfrM/oar2.jpg" referrerpolicy="no-referrer" alt="¿Vale la pena la Osmo Pocket 4?" loading="lazy">
                    <div class="vs-player"></div>
                    <button type="button" class="vs-sonido" onclick="alternarSonidoShort(event)" aria-label="Activar sonido" aria-pressed="false">
                        <svg class="vs-ico vs-ico-mute" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M11 5 6 9H2v6h4l5 4V5z"/><path d="m23 9-6 6"/><path d="m17 9 6 6"/>
                        </svg>
                        <svg class="vs-ico vs-ico-audio" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M11 5 6 9H2v6h4l5 4V5z"/><path d="M15.5 8.5a5 5 0 0 1 0 7"/><path d="M18.5 5.5a9 9 0 0 1 0 13"/>
                        </svg>
                    </button>
                </div>
            </article>
            <article class="video-review-card" data-youtube-id="DiLyH6StrY0" data-video-title="DJI Osmo Pocket 4: ¿vale la pena?">
                <div class="vs-media">
                    <img class="vs-thumb" src="https://i.ytimg.com/vi/DiLyH6StrY0/oar2.jpg" referrerpolicy="no-referrer" alt="DJI Osmo Pocket 4: ¿vale la pena?" loading="lazy">
                    <div class="vs-player"></div>
                    <button type="button" class="vs-sonido" onclick="alternarSonidoShort(event)" aria-label="Activar sonido" aria-pressed="false">
                        <svg class="vs-ico vs-ico-mute" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M11 5 6 9H2v6h4l5 4V5z"/><path d="m23 9-6 6"/><path d="m17 9 6 6"/>
                        </svg>
                        <svg class="vs-ico vs-ico-audio" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M11 5 6 9H2v6h4l5 4V5z"/><path d="M15.5 8.5a5 5 0 0 1 0 7"/><path d="M18.5 5.5a9 9 0 0 1 0 13"/>
                        </svg>
                    </button>
                </div>
            </article>
            <article class="video-review-card" data-youtube-id="SRcw2RqUq84" data-video-title="La nueva DJI Osmo Pocket 4: buenos colores, pero...">
                <div class="vs-media">
                    <img class="vs-thumb" src="https://i.ytimg.com/vi/SRcw2RqUq84/oar2.jpg" referrerpolicy="no-referrer" alt="La nueva DJI Osmo Pocket 4: buenos colores, pero..." loading="lazy">
                    <div class="vs-player"></div>
                    <button type="button" class="vs-sonido" onclick="alternarSonidoShort(event)" aria-label="Activar sonido" aria-pressed="false">
                        <svg class="vs-ico vs-ico-mute" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M11 5 6 9H2v6h4l5 4V5z"/><path d="m23 9-6 6"/><path d="m17 9 6 6"/>
                        </svg>
                        <svg class="vs-ico vs-ico-audio" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M11 5 6 9H2v6h4l5 4V5z"/><path d="M15.5 8.5a5 5 0 0 1 0 7"/><path d="M18.5 5.5a9 9 0 0 1 0 13"/>
                        </svg>
                    </button>
                </div>
            </article>
            <article class="video-review-card" data-youtube-id="ky8klHTL2Ig" data-video-title="DJI Osmo Pocket 4P: la doble cámara que todos pedíamos">
                <div class="vs-media">
                    <img class="vs-thumb" src="https://i.ytimg.com/vi/ky8klHTL2Ig/oar2.jpg" referrerpolicy="no-referrer" alt="DJI Osmo Pocket 4P: la doble cámara que todos pedíamos" loading="lazy">
                    <div class="vs-player"></div>
                    <button type="button" class="vs-sonido" onclick="alternarSonidoShort(event)" aria-label="Activar sonido" aria-pressed="false">
                        <svg class="vs-ico vs-ico-mute" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M11 5 6 9H2v6h4l5 4V5z"/><path d="m23 9-6 6"/><path d="m17 9 6 6"/>
                        </svg>
                        <svg class="vs-ico vs-ico-audio" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M11 5 6 9H2v6h4l5 4V5z"/><path d="M15.5 8.5a5 5 0 0 1 0 7"/><path d="M18.5 5.5a9 9 0 0 1 0 13"/>
                        </svg>
                    </button>
                </div>
            </article>
            <article class="video-review-card" data-youtube-id="xezLqoTEIno" data-video-title="Esta es la Osmo Pocket 4">
                <div class="vs-media">
                    <img class="vs-thumb" src="https://i.ytimg.com/vi/xezLqoTEIno/oar2.jpg" referrerpolicy="no-referrer" alt="Esta es la Osmo Pocket 4" loading="lazy">
                    <div class="vs-player"></div>
                    <button type="button" class="vs-sonido" onclick="alternarSonidoShort(event)" aria-label="Activar sonido" aria-pressed="false">
                        <svg class="vs-ico vs-ico-mute" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M11 5 6 9H2v6h4l5 4V5z"/><path d="m23 9-6 6"/><path d="m17 9 6 6"/>
                        </svg>
                        <svg class="vs-ico vs-ico-audio" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M11 5 6 9H2v6h4l5 4V5z"/><path d="M15.5 8.5a5 5 0 0 1 0 7"/><path d="M18.5 5.5a9 9 0 0 1 0 13"/>
                        </svg>
                    </button>
                </div>
            </article>
            <article class="video-review-card" data-youtube-id="79S4mVWIqzc" data-video-title="DJI Osmo Pocket 4: análisis a fondo">
                <div class="vs-media">
                    <img class="vs-thumb" src="https://i.ytimg.com/vi/79S4mVWIqzc/oar2.jpg" referrerpolicy="no-referrer" alt="DJI Osmo Pocket 4: análisis a fondo" loading="lazy">
                    <div class="vs-player"></div>
                    <button type="button" class="vs-sonido" onclick="alternarSonidoShort(event)" aria-label="Activar sonido" aria-pressed="false">
                        <svg class="vs-ico vs-ico-mute" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M11 5 6 9H2v6h4l5 4V5z"/><path d="m23 9-6 6"/><path d="m17 9 6 6"/>
                        </svg>
                        <svg class="vs-ico vs-ico-audio" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M11 5 6 9H2v6h4l5 4V5z"/><path d="M15.5 8.5a5 5 0 0 1 0 7"/><path d="M18.5 5.5a9 9 0 0 1 0 13"/>
                        </svg>
                    </button>
                </div>
            </article>
            <article class="video-review-card" data-youtube-id="F_8yrTAzONU" data-video-title="Nadie esperaba esto: DJI Osmo Pocket 4P">
                <div class="vs-media">
                    <img class="vs-thumb" src="https://i.ytimg.com/vi/F_8yrTAzONU/oar2.jpg" referrerpolicy="no-referrer" alt="Nadie esperaba esto: DJI Osmo Pocket 4P" loading="lazy">
                    <div class="vs-player"></div>
                    <button type="button" class="vs-sonido" onclick="alternarSonidoShort(event)" aria-label="Activar sonido" aria-pressed="false">
                        <svg class="vs-ico vs-ico-mute" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M11 5 6 9H2v6h4l5 4V5z"/><path d="m23 9-6 6"/><path d="m17 9 6 6"/>
                        </svg>
                        <svg class="vs-ico vs-ico-audio" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M11 5 6 9H2v6h4l5 4V5z"/><path d="M15.5 8.5a5 5 0 0 1 0 7"/><path d="M18.5 5.5a9 9 0 0 1 0 13"/>
                        </svg>
                    </button>
                </div>
            </article>

<?php endif; ?>
            </div>
        </div>
        </div>

        <div class="video-carousel-progreso" role="presentation">
            <span class="vc-barra" id="videoCarruselBarra"></span>
        </div>
    </section>

    <!-- VIDEO MODAL LIGHTBOX -->
    <!-- 5.5 CUSTOMER REVIEWS SECTION (ESTILO AMAZON) -->
    <section class="customer-reviews-section" id="customerReviewsSection">
        <div class="customer-reviews-grid">
            <!-- COLUMNA IZQUIERDA: RESUMEN AMAZON-STYLE -->
            <div class="reviews-summary-card" id="reviewsSummaryCard">
                <h2 class="reviews-sidebar-title" data-editable="true">Opiniones de clientes</h2>
                <span class="reviews-sidebar-subtitle" data-editable="true">Conoce qué opinan sobre este producto</span>
                
                <div class="reviews-score-hero">
                    <div class="reviews-stars-hero" id="reviewsHeroStars">★★★★★</div>
                    <div class="reviews-score-text">
                        <span id="scoreAvgDisplay">4.9</span> de 5
                    </div>
                </div>
                
                <div class="reviews-total-ratings-sub" id="reviewsTotalCountSub">48 calificaciones globales</div>

            </div>

            <!-- COLUMNA DERECHA: FILTROS Y LISTA DE OPINIONES -->
            <div class="reviews-feed-column">
                <div class="reviews-list-wrap" id="reviewsListContainer"></div>

                <div class="reviews-pagination-row" id="reviewsPaginationContainer"></div>

                <!-- ESCRIBIR OPINIÓN: cierra la lista, cuando ya se leyeron las demás -->
                <div class="write-review-block">
                    <h3 class="write-review-title">Cuéntanos qué te pareció</h3>
                    <p class="write-review-subtitle">Comparte tu experiencia y recibe un 10% de descuento en tu próxima compra.</p>
                    <button type="button" class="btn-write-review" onclick="abrirModalEscribirOpinion()">
                        Compartir mi experiencia
                    </button>
                </div>
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
                        <?php if (file_exists(__DIR__ . '/assets/marca/logo.svg')): ?>
                            <img src="assets/marca/logo.svg" alt="<?= htmlspecialchars($nombre_marca) ?>" class="upsell-brand-logo">
                        <?php elseif (file_exists(__DIR__ . '/assets/marca/logo.webp')): ?>
                            <img src="assets/marca/logo.webp" alt="<?= htmlspecialchars($nombre_marca) ?>" class="upsell-brand-logo">
                        <?php elseif (file_exists(__DIR__ . '/assets/marca/logo.png')): ?>
                            <img src="assets/marca/logo.png" alt="<?= htmlspecialchars($nombre_marca) ?>" class="upsell-brand-logo">
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
<?php if ($mostrar_ml_banner): ?>
    <a class="ml-promo-banner-wrap"
       href="<?= htmlspecialchars($url_pasarela_meli) ?>/index.php?token=<?= $landing_token ?>">
        <div class="ml-banner-inner">
            <div class="ml-banner-left">
                <?php if (file_exists(__DIR__ . '/assets/marca/mercadito.webp')): ?>
                    <img src="assets/marca/mercadito.webp" alt="Mercado Libre" class="ml-logo-img">
                <?php elseif (file_exists(__DIR__ . '/../../mercadito.webp')): ?>
                    <img src="assets/marca/mercadito.webp" alt="Mercado Libre" class="ml-logo-img">
                <?php else: ?>
                    <img src="assets/marca/mercadito.webp" alt="Mercado Libre" class="ml-logo-img">
                <?php endif; ?>
            </div>

            <div class="ml-banner-divider"></div>

            <div class="ml-banner-center">
                <span class="ml-headline">También nos encuentras en Mercado Libre</span>
                <span class="ml-subline">Encuentra este mismo producto en nuestra publicación</span>
            </div>

            <div class="ml-banner-right">
                <span class="ml-cta">Ver producto en Mercado Libre</span>
            </div>
        </div>
    </a>
<?php endif; ?>

    <!-- 6. SECCIÓN QUIENES VIERON ESTE PRODUCTO TAMBIÉN COMPRARON -->
    

    <!-- 7. FOOTER MODERNO ESTILO SHEGLAM -->
    <footer class="generic-footer">
        <div class="footer-content-wrap">
            <!-- BENEFICIOS / TRUST BAR: 1. PAGA EN LÍNEA, 2. COMPRAS SEGURAS, 3. ACUMULAS PUNTOS COLOMBIA -->
            <div class="footer-trust-benefits-bar">
                <div class="trust-benefit-col">
                    <img src="assets/pago/tarjeta.svg" alt="Paga en línea o en efectivo" class="trust-benefit-icon">
                    <span class="trust-benefit-text" data-editable="true">Paga en línea<br>o en efectivo</span>
                </div>
                <div class="trust-benefit-col">
                    <img src="assets/sellos/escudo_candado.svg" alt="Compras seguras" class="trust-benefit-icon">
                    <span class="trust-benefit-text" data-editable="true">Compras<br>seguras</span>
                </div>
                <div class="trust-benefit-col">
                    <img src="assets/sellos/puntos_colombia.svg" alt="Acumulas Puntos Colombia" class="trust-benefit-icon">
                    <span class="trust-benefit-text" data-editable="true">Acumulas<br>Puntos Colombia</span>
                </div>
            </div>

            <!-- MEDIOS DE PAGO (AMEX, VISA, MASTE, PSE, NEQUI, MERCADITO, CONTRAENTREGA) CON FONDO BLANCO -->
            <div class="footer-payments-row">
                <!-- AMERICAN EXPRESS -->
                <div class="footer-payment-badge badge-amex" title="American Express">
                    <img src="assets/pago/amex.svg" alt="American Express">
                </div>
                <!-- VISA -->
                <div class="footer-payment-badge badge-visa" title="Visa">
                    <img src="assets/pago/visa.svg" alt="Visa">
                </div>
                <!-- MASTERCARD -->
                <div class="footer-payment-badge badge-master" title="Mastercard">
                    <img src="assets/pago/maste.svg" alt="Mastercard">
                </div>
                <!-- PSE -->
                <div class="footer-payment-badge badge-pse" title="PSE">
                    <img src="assets/pago/pse.png" alt="PSE">
                </div>
                <!-- NEQUI -->
                <div class="footer-payment-badge badge-nequi" title="Nequi">
                    <img src="assets/pago/Nequi_Colombia_logo.svg.webp" alt="Nequi">
                </div>
                <!-- CONTRAENTREGA -->
                <div class="footer-payment-badge badge-contraentrega" title="Pago Contraentrega">
                    <img src="assets/pago/contraentrega.png" alt="Pago Contraentrega">
                </div>
            </div>

            <!-- SUPERINTENDENCIA (BLANCO) & CÁMARA DE COMERCIO -->
            <div class="footer-legal-row">
                <?php if (file_exists(__DIR__ . '/assets/sellos/sic.png')): ?>
                    <div class="footer-sic-badge" title="Superintendencia de Industria y Comercio">
                        <img src="assets/sellos/sic.png" alt="Superintendencia de Industria y Comercio">
                    </div>
                <?php elseif (file_exists(__DIR__ . '/assets/sellos/sic.png')): ?>
                    <div class="footer-sic-badge" title="Superintendencia de Industria y Comercio">
                        <img src="assets/sellos/sic.png" alt="Superintendencia de Industria y Comercio">
                    </div>
                <?php else: ?>
                    <span class="footer-legal-text" data-editable="true">Superintendencia de Industria y Comercio</span>
                <?php endif; ?>

                <?php if (file_exists(__DIR__ . '/assets/sellos/comerciocamara.png')): ?>
                    <div class="footer-camara-badge" title="Cámara Colombiana de Comercio Electrónico">
                        <img src="assets/sellos/comerciocamara.png" alt="Cámara Colombiana de Comercio Electrónico">
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
                <div class="cart-summary-row"><span>Subtotal</span><span id="cartSubtotal">$ 1.127.980</span></div>
                <div class="cart-summary-row" id="cartEnvioRow"><span>Envío</span><span style="color:#059669; font-weight:700;">GRATIS</span></div>
                <div class="cart-summary-row total"><span>Total</span><span id="cartTotal">$ 1.127.980</span></div>
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
            'img/img_1.webp',
            'img/img_2.webp',
            'img/img_3.webp',
            'img/img_4.webp'
        ];
        ?>
        const IMAGENES = <?= json_encode($supabaseImages) ?>;
        const SWATCHES = [];
        const REVIEWS_LIST = [{"author":"Victor M","size":"1 Unidad","stars":"★★★★★","comment":"Comparada con la versión 3, es mucho mejor. Aprobada por el staff del podcast \"como gordas en tobogán\".","date":"2026.08.23","fechaTexto":"Hace 5 días","titulo":"Profesional","ubicacion":"Pereira, Colombia","likes":36,"img":"img_reviews/dji_rev_1.webp","img2":"img_reviews/dji_rev_2.webp"},{"author":"Laura T","size":"1 Unidad","stars":"★★★★★","comment":"Llegó todo bien cuidado, mi primera pocket, amo la marca dji y sé que no me decepcionará 🥰♥️.","date":"2026.08.17","fechaTexto":"Hace 1 semana","titulo":"Hermosaa","ubicacion":"Cúcuta, Colombia","likes":9,"img":"img_reviews/dji_rev_3.webp","img2":"img_reviews/dji_rev_4.webp"},{"author":"Jose F","size":"1 Unidad","stars":"★★★★★","comment":"Está muy compacta, ideal para el día a día. Realmente se nota la calidad.","date":"2026.08.11","fechaTexto":"Hace 2 semanas","titulo":"Muy completa, la recomiendo","ubicacion":"Cali, Colombia","likes":22,"img":"img_reviews/dji_rev_5.webp","img2":"img_reviews/dji_rev_6.webp"},{"author":"Andres P","size":"1 Unidad","stars":"★★★★★","comment":"Excelente compra calidad dji, viene completamente sellado.","date":"2026.08.05","fechaTexto":"Hace 2 semanas","titulo":"La original","ubicacion":"Pereira, Colombia","likes":35,"img":"img_reviews/dji_rev_7.webp","img2":"img_reviews/dji_rev_8.webp"},{"author":"Fernanda C","size":"1 Unidad","stars":"★★★★★","comment":"Llegó todo bien en caja bien empacado, ya la empecé a utilizar y graba muy bien. Es la primera osmo pocket que me compro, el micrófono también me gustó mucho.","date":"2026.07.30","fechaTexto":"Hace 3 semanas","titulo":"Fue rapido el envio","ubicacion":"Cúcuta, Colombia","likes":8,"img":"img_reviews/dji_rev_9.webp","img2":"img_reviews/dji_rev_10.webp"}];
        const PRECIO_UNITARIO = 1127980;
        const PRODUCTO_TITULO = "DJI Osmo Pocket 4 Creater Combo Cámara para Vlogs 4K 120 fps CMOS 1\"";
        const LANDING_TOKEN = "<?= $landing_token ?>";
        const LANDING_SLUG = "dji-osmo-pocket-4-creater-combo";
        const CHECKOUT_URL = "<?= htmlspecialchars($url_pasarela_bold, ENT_QUOTES) ?>/checkout.php?token=" + LANDING_TOKEN;
        const ES_MODO_EDICION = <?= $es_modo_edicion ? 'true' : 'false' ?>;

        let activeImgIndex = 0;
        let lightboxIndex = 0;
        let currentReviewPage = 1;
        /* Con 5 opiniones y 5 por pagina salia una sola pagina y el paginador
           se pintaba vacio (totalPages <= 1). A 3 por pagina quedan dos. */
        const REVIEWS_PER_PAGE = 3;
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

        /* Despliega o repliega la descripcion larga. El recorte y el giro de la
           flecha los hace la CSS, asi que aqui solo se conmuta la clase.
           El texto se cambia en su <span>, no en el boton entero: escribir
           sobre btn.textContent borraria el SVG de la flecha. */
        /* Un "Ver mas" que no revela nada es un boton roto: si el texto ya
           cabe entero en la altura recortada, se retira. Se recalcula al
           cambiar el ancho porque el texto reflui­do puede pasar a desbordar. */
        function ajustarBotonesVerMas() {
            document.querySelectorAll('.desc-item').forEach((item) => {
                const cuerpo = item.querySelector('.desc-cuerpo');
                const btn = item.querySelector('.desc-toggle');
                if (!cuerpo || !btn) return;
                if (item.classList.contains('desc-abierta')) return;
                const sobra = cuerpo.scrollHeight > cuerpo.clientHeight + 1;
                btn.style.display = sobra ? '' : 'none';
            });
        }

        function toggleDescripcion(btn) {
            const item = btn.closest('.desc-item');
            if (!item) return;
            const abierto = item.classList.toggle('desc-abierta');
            const txt = btn.querySelector('.desc-toggle-txt');
            if (txt) txt.textContent = abierto ? 'Ver menos' : 'Ver más';
            btn.setAttribute('aria-expanded', abierto ? 'true' : 'false');
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

        /* Logica compartida por "Agregar al carrito" y "Comprar ahora":
           mete (o incrementa) el item actual en globalCart. Antes vivia
           duplicada dentro de agregarAlCarrito; se extrae para que
           comprarAhora pueda usar exactamente el mismo camino de datos. */
        function agregarItemActualAlCarrito() {
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

            agregarItemActualAlCarrito();

            animarVueloAlCarrito(clickedBtn, () => {
                renderCart();
                const overlay = document.getElementById('cartOverlay');
                if (overlay && !overlay.classList.contains('open')) {
                    overlay.classList.add('open');
                    document.body.style.overflow = 'hidden';
                }
            });
        }

        /* "Comprar ahora": se asegura de que el producto este en el carrito y
           va directo a la pasarela, sin abrir el cajon.
           Solo lo agrega si aun no estaba: si el comprador ya fijo una
           cantidad con el selector, hay que respetarla en vez de sumarle
           una unidad de mas camino al pago.
           procederAlCheckout() ya arma la URL con token y cantidad. */
        function comprarAhora() {
            if (ES_MODO_EDICION) return;
            const yaEnCarrito = globalCart.some(i => i.token === LANDING_TOKEN);
            if (!yaEnCarrito) agregarItemActualAlCarrito();
            procederAlCheckout();
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
            // El aviso de envio gratis solo tiene sentido si hay algo que enviar.
            const avisoEnvio = document.querySelector('.shipping-progress-wrap');
            if (avisoEnvio) avisoEnvio.classList.toggle('visible', totalUnits > 0);
            // Igual que el aviso: la fila "Envío — GRATIS" del pie solo con unidades.
            const envioRow = document.getElementById('cartEnvioRow');
            if (envioRow) envioRow.style.display = totalUnits > 0 ? '' : 'none';
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




        function toggleExplanationReviews(btn) {
            const box = document.getElementById('reviewsExplanationBox');
            if (box) {
                const isOpen = box.classList.toggle('open');
                const arrow = btn.querySelector('.explanation-arrow');
                if (arrow) arrow.textContent = isOpen ? '▴' : '▾';
            }
        }


        function calcularEstadisticasReviews() {
            // Distribución realista estilo Amazon de 48 calificaciones globales
            const BASELINE_TOTAL = 48;
            const BASELINE_COUNTS = { 5: 44, 4: 3, 3: 1, 2: 0, 1: 0 };
            
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

        /* Nombre corto del producto para la ficha de cada opinion. */
        const PRODUCTO_CORTO = "DJI Osmo Pocket 4 Creater Combo Cámara para";

        /* ─── FOTO DE PERFIL DE QUIEN OPINA ───
           Se pide a una API, no se guarda ninguna imagen en el proyecto: al
           generar otra landing las fotos salen solas.

           pravatar sirve fotografias reales, no dibujos. Su catalogo tiene 70
           imagenes numeradas del 1 al 70.

           Ojo con la semilla de texto (?u=nombre): COLISIONA. Al probarla,
           "victor m" y "andres p" recibian exactamente la misma foto, y dos
           opiniones con la misma cara delatan que estan inventadas. Por eso el
           numero se reparte aqui: se calcula del nombre, pero se usa ?img=N,
           que apunta a una imagen concreta del catalogo. */
        const AVATAR_TOTAL = 70;

        /* El numero de foto sale del INDICE de la opinion, no de un hash del
           nombre. Con hash habia colisiones reales: "Laura T" y "Andres P"
           caian en la misma imagen, y dos opiniones con la misma cara delatan
           que estan inventadas. Por indice no se repite ninguna mientras haya
           menos de 70 opiniones.
           El desplazamiento inicial se deriva de la landing, para que dos
           landings distintas no acaben con la misma tanda de caras. */
        function desplazamientoAvatar() {
            const base = String(typeof LANDING_SLUG !== 'undefined' ? LANDING_SLUG : 'landing');
            let n = 0;
            for (let i = 0; i < base.length; i++) n = (n * 31 + base.charCodeAt(i)) % 100000;
            return n % AVATAR_TOTAL;
        }

        function avatarDe(indice) {
            const n = ((desplazamientoAvatar() + (indice || 0)) % AVATAR_TOTAL) + 1;
            return 'https://i.pravatar.cc/160?img=' + n;
        }

        function estrellasSvg(cantidad) {
            /* Estrella que ocupa todo el marco: es la silueta gruesa de
               Amazon. La anterior era mas fina y dejaba aire alrededor, por
               eso se veian separadas. */
            const trazo = 'M12 .587l3.668 7.431 8.332 1.151-6.064 5.828 1.48 8.279L12 18.896l-7.416 4.38 1.48-8.279L0 9.169l8.332-1.151z';
            let html = '';
            for (let i = 1; i <= 5; i++) {
                html += '<svg viewBox="0 0 24 24" fill="currentColor" class="' + (i <= cantidad ? 'llena' : 'vacia') + '" aria-hidden="true"><path d="' + trazo + '"/></svg>';
            }
            return '<span class="rev-estrellas" role="img" aria-label="' + cantidad + ' de 5 estrellas">' + html + '</span>';
        }

        function contarEstrellas(valor) {
            if (typeof valor === 'number') return Math.max(1, Math.min(5, valor));
            const texto = String(valor || '');
            const llenas = (texto.match(/★/g) || []).length;
            return llenas ? Math.min(5, llenas) : 5;
        }

        const MESES_ES = ['enero','febrero','marzo','abril','mayo','junio',
                          'julio','agosto','septiembre','octubre','noviembre','diciembre'];

        /* Fecha aleatoria dentro del ultimo ano, pero ESTABLE: se calcula una
           sola vez por opinion y se guarda. Si se sorteara en cada pintado
           cambiaria al filtrar o al pasar de pagina, y eso se nota. */
        function fechaCertificacion(r, indice) {
            if (r._fechaTexto) return r._fechaTexto;
            const hoy = new Date();
            // Semilla a partir del indice y del autor: reparte las fechas sin
            // depender de Math.random, asi son distintas pero reproducibles.
            const semilla = (indice * 37 + String(r.author || '').length * 13) % 330;
            const d = new Date(hoy.getTime() - (12 + semilla) * 86400000);
            r._fechaTexto = d.getDate() + ' de ' + MESES_ES[d.getMonth()] + ' de ' + d.getFullYear();
            return r._fechaTexto;
        }

        /* Muestra "Leer mas" solo si el texto de verdad se sale: un boton que
           no revela nada es un boton roto. */
        function ajustarLeerMas(scope) {
            (scope || document).querySelectorAll('.review-card-item').forEach(function (item) {
                const txt = item.querySelector('.rev-texto');
                const btn = item.querySelector('.rev-leer-mas');
                if (!txt || !btn) return;
                if (item.classList.contains('rev-abierta')) return;
                btn.style.display = (txt.scrollHeight > txt.clientHeight + 1) ? '' : 'none';
            });
        }

        /* Boton "Util". Antes solo estaba el contador, sin nada que pulsar.
           El voto se guarda en el navegador de quien lo da: no hay servidor
           donde sumarlo, y sin recordarlo se podria votar sin parar. */
        const UTILES_KEY = 'dji_opiniones_utiles_v1';

        function utilesVotados() {
            try { return JSON.parse(localStorage.getItem(UTILES_KEY) || '[]'); }
            catch (e) { return []; }
        }

        function marcarUtil(btn) {
            const item = btn.closest('.review-card-item');
            const cuenta = item ? item.querySelector('.rev-util-cuenta') : null;
            if (!cuenta) return;

            const clave = (item.querySelector('.rev-nombre') || {}).textContent || '';
            const votados = utilesVotados();
            const yaVotado = btn.getAttribute('aria-pressed') === 'true';

            const base = parseInt(cuenta.dataset.base || cuenta.textContent, 10) || 0;
            if (!cuenta.dataset.base) cuenta.dataset.base = base;

            const nuevo = yaVotado ? base : base + 1;
            cuenta.textContent = nuevo + (nuevo === 1 ? ' persona encontró esto útil' : ' personas encontraron esto útil');
            btn.setAttribute('aria-pressed', yaVotado ? 'false' : 'true');
            btn.classList.toggle('votado', !yaVotado);

            const sin = votados.filter(function (v) { return v !== clave; });
            if (!yaVotado) sin.push(clave);
            try { localStorage.setItem(UTILES_KEY, JSON.stringify(sin)); } catch (e) {}
        }

        /* Al repintar la lista hay que devolver el estado de los votos ya dados. */
        function restaurarUtiles(scope) {
            const votados = utilesVotados();
            (scope || document).querySelectorAll('.review-card-item').forEach(function (item) {
                const btn = item.querySelector('.rev-util');
                const cuenta = item.querySelector('.rev-util-cuenta');
                if (!btn || !cuenta) return;
                const clave = (item.querySelector('.rev-nombre') || {}).textContent || '';
                if (votados.indexOf(clave) === -1) return;
                const base = parseInt(cuenta.textContent, 10) || 0;
                cuenta.dataset.base = base;
                cuenta.textContent = (base + 1) + ' personas encontraron esto útil';
                btn.setAttribute('aria-pressed', 'true');
                btn.classList.add('votado');
            });
        }

        function alternarOpinion(btn) {
            const item = btn.closest('.review-card-item');
            if (!item) return;
            const abierta = item.classList.toggle('rev-abierta');
            const etiqueta = btn.querySelector('span');
            if (etiqueta) etiqueta.textContent = abierta ? 'Leer menos' : 'Leer más';
            btn.setAttribute('aria-expanded', abierta ? 'true' : 'false');
        }

        function renderReviews() {
            const container = document.getElementById('reviewsListContainer');
            const paginationContainer = document.getElementById('reviewsPaginationContainer');
            if (!container || !REVIEWS_LIST || REVIEWS_LIST.length === 0) return;

            // Sin filtros: se muestran todas, en el orden en que vienen.
            const filtered = [...REVIEWS_LIST];

            /* Escritorio (>=992px): sin paginacion, TODAS las opiniones a la
               vez repartidas en dos columnas. Movil: 3 por pagina, como antes. */
            const esEscritorio = window.matchMedia('(min-width: 992px)').matches;

            const totalPages = esEscritorio ? 1 : Math.max(1, Math.ceil(filtered.length / REVIEWS_PER_PAGE));
            if (currentReviewPage > totalPages) currentReviewPage = 1;

            const startIdx = esEscritorio ? 0 : (currentReviewPage - 1) * REVIEWS_PER_PAGE;
            const pageItems = esEscritorio ? filtered : filtered.slice(startIdx, startIdx + REVIEWS_PER_PAGE);

            container.innerHTML = '';
            /* En escritorio las tarjetas van dentro de dos wrappers .rev-col:
               la primera mitad (redondeo hacia arriba) a la izquierda y el
               resto a la derecha, en el mismo orden. Los post-procesos
               (ajustarLeerMas, restaurarUtiles) usan querySelectorAll
               descendente, asi que el nivel extra no les afecta. */
            const dosColumnas = esEscritorio && pageItems.length > 0;
            container.classList.toggle('rev-dos-columnas', dosColumnas);
            let colIzq = null, colDer = null, corteCol = 0;
            if (dosColumnas) {
                corteCol = Math.ceil(pageItems.length / 2);
                colIzq = document.createElement('div');
                colIzq.className = 'rev-col';
                colDer = document.createElement('div');
                colDer.className = 'rev-col';
                container.appendChild(colIzq);
                container.appendChild(colDer);
            }
            if (pageItems.length === 0) {
                container.innerHTML = `
                    <div style="text-align:center; padding:36px 16px; color:#565959;">
                        <p style="font-size:15px; font-weight:600; margin-bottom:6px;">No hay opiniones que coincidan con los filtros seleccionados.</p>
                        <p style="font-size:13px; margin:0;">Sé el primero en <a href="javascript:void(0)" onclick="abrirModalEscribirOpinion()" style="color:#007185; font-weight:700; text-decoration:underline;">escribir una opinión</a>.</p>
                    </div>
                `;
            } else {
                pageItems.forEach((r, idx) => {
                    const item = document.createElement('div');
                    item.className = 'review-card-item';
                    const nEstrellas = contarEstrellas(r.estrellas || r.stars);
                    const inicial = String(r.author || '?').trim().charAt(0).toUpperCase();
                    const foto = avatarDe(startIdx + idx);
                    const fotos = [r.img, r.img2].filter(Boolean);
                    item.innerHTML = `
                        <div class="rev-cabecera">
                            <img class="rev-avatar" src="${foto}" alt="" loading="lazy" aria-hidden="true"
                                 onerror="this.replaceWith(Object.assign(document.createElement('span'),{className:'rev-avatar',textContent:'${inicial}'}))">
                            <span class="rev-nombre" data-editable="true">${r.author}</span>
                        </div>
                        <div class="rev-valoracion">
                            ${estrellasSvg(nEstrellas)}
                            ${r.titulo ? `<span class="rev-titulo" data-editable="true">${r.titulo}</span>` : ''}
                        </div>
                        <div class="rev-meta">Certificado en Colombia el ${fechaCertificacion(r, idx % REVIEWS_PER_PAGE)}</div>
                        <div class="rev-meta">Producto: <b>${PRODUCTO_CORTO}</b></div>
                        <div class="rev-verificada">Compra verificada</div>
                        ${fotos.length ? `<div class="review-photos">${fotos.map(u => `<img src="${u}" class="review-photo" loading="lazy" alt="Foto de la opinión" onclick="abrirFotoOpinion(this)">`).join('')}</div>` : ''}
                        <p class="rev-texto" data-editable="true">${r.comment}</p>
                        <button type="button" class="rev-leer-mas" onclick="alternarOpinion(this)" aria-expanded="false">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                            <span>Leer más</span>
                        </button>
                        <div class="rev-util-fila">
                            <button type="button" class="rev-util" onclick="marcarUtil(this)" aria-pressed="false">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M7 22V11l5-9a2.5 2.5 0 0 1 2.4 3.2L13.5 9H20a2 2 0 0 1 2 2.3l-1.3 8A2.5 2.5 0 0 1 18.2 22H7z"/>
                                    <path d="M7 11H4a1 1 0 0 0-1 1v9a1 1 0 0 0 1 1h3"/>
                                </svg>
                                <span class="rev-util-txt">Útil</span>
                            </button>
                            <span class="rev-util-cuenta">${r.likes || 0} personas encontraron esto útil</span>
                        </div>
                        ${r.id ? `
                        <div class="review-actions-wrap">
                            <button type="button" class="btn-delete-user-review" onclick="eliminarOpinionUsuario('${r.id}')" title="Eliminar mi opinión" aria-label="Eliminar opinión">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 6h18"></path>
                                    <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path>
                                    <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path>
                                    <line x1="10" y1="11" x2="10" y2="17"></line>
                                    <line x1="14" y1="11" x2="14" y2="17"></line>
                                </svg>
                            </button>
                        </div>` : ''}
                    `;
                    (dosColumnas ? (idx < corteCol ? colIzq : colDer) : container).appendChild(item);
                });
            }

            ajustarLeerMas(container);
            restaurarUtiles(container);

            if (paginationContainer) {
                container.classList.toggle('con-paginador', totalPages > 1);
                if (totalPages <= 1) {
                    paginationContainer.innerHTML = '';
                } else {
                    // Solo las burbujas numeradas: el rotulo y las flechas
                    // sobraban con dos paginas.
                    let pagesHtml = '';
                    for (let i = 1; i <= totalPages; i++) {
                        pagesHtml += `<button class="page-btn ${i === currentReviewPage ? 'active' : ''}" onclick="cambiarPaginaReviews(${i}, ${totalPages})" aria-label="Pagina ${i}" aria-current="${i === currentReviewPage ? 'page' : 'false'}">${i}</button>`;
                    }
                    paginationContainer.innerHTML = pagesHtml;
                }
            }

            calcularEstadisticasReviews();
            initModoEdicion();
        }

        /* Se pintan YA, sin esperar a DOMContentLoaded. Hasta que ese evento
           llegaba (1,5 s con la API de YouTube y las imagenes en vuelo) la
           seccion de opiniones era un titulo sobre 200 px de vacio, y en movil
           el usuario lo lee como el final de la pagina y no sigue bajando.
           El script va despues de la seccion en el documento, asi que los
           contenedores ya existen. */
        if (document.getElementById('reviewsListContainer')) {
            try { renderReviews(); } catch (e) {}
        }

        /* Al cruzar el umbral de escritorio (992px) la lista cambia de forma:
           dos columnas sin paginar a un lado, 3 por pagina al otro. Se
           repinta al vuelo, sin recargar. */
        (function () {
            var mqEscritorio = window.matchMedia('(min-width: 992px)');
            var repintarReviews = function () { try { renderReviews(); } catch (e) {} };
            if (mqEscritorio.addEventListener) mqEscritorio.addEventListener('change', repintarReviews);
            else if (mqEscritorio.addListener) mqEscritorio.addListener(repintarReviews);
        })();


        function cambiarPaginaReviews(nuevaPagina, totalPages) {
            if (nuevaPagina < 1 || nuevaPagina > totalPages) return;
            currentReviewPage = nuevaPagina;
            renderReviews();
            /* scrollIntoView deja el titulo debajo de la navbar fija y se ve
               asomar la seccion anterior. Se calcula el destino restando lo que
               ocupan el anuncio y la navbar. */
            const section = document.getElementById('customerReviewsSection');
            if (section) {
                var calcular = function () {
                    var anuncio = document.querySelector('.top-announcement');
                    var navbar  = document.querySelector('.navbar');
                    var fijo = (anuncio ? anuncio.offsetHeight : 0) + (navbar ? navbar.offsetHeight : 0);
                    return Math.max(0, section.getBoundingClientRect().top + window.scrollY - fijo - 12);
                };
                window.scrollTo({ top: calcular(), behavior: 'smooth' });
                /* Lo que hay por encima (el carrusel, las imagenes que aun
                   cargan) se reacomoda mientras dura la animacion y el destino
                   se queda corto. Se corrige al terminar, sin animacion: el
                   ajuste es de pocos pixeles y no se percibe. */
                setTimeout(function () {
                    var d = calcular();
                    if (Math.abs(d - window.scrollY) > 4) window.scrollTo({ top: d, behavior: 'auto' });
                }, 620);
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

                const el = document.getElementById('shippingCountdown');
                if (el) {
                    // Sin segundero: solo horas y minutos.
                    el.textContent = `${h} h ${m < 10 ? '0' + m : m} min`;
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

            // La barra flotante no puede convivir con el boton de carrito en
            // linea: mientras ese siga en pantalla habria dos botones de compra
            // compitiendo. Se mide la geometria en el mismo rAF del scroll.
            function carritoEnLineaALaVista() {
                const enLinea = document.querySelector('.direct-purchase-row .btn-add-cart-outline');
                if (!enLinea) return false;
                const c = enLinea.getBoundingClientRect();
                if (!c.width && !c.height) return false;   // oculto: no estorba
                const alto = window.innerHeight || document.documentElement.clientHeight;
                return c.bottom > 0 && c.top < alto;
            }

            function updateNavScroll() {
                const currentScrollY = Math.max(0, window.scrollY || window.pageYOffset || document.documentElement.scrollTop || 0);
                const navbar = document.querySelector('.navbar');
                const stickyBar = document.querySelector('.sticky-footer-bar');
                const delta = currentScrollY - lastScrollY;
                const tapada = carritoEnLineaALaVista();

                if (currentScrollY <= 10) {
                    // En la cima → siempre visible
                    if (navbar) navbar.classList.remove('nav-hidden');
                    if (stickyBar) stickyBar.classList.toggle('bar-hidden', tapada);
                } else if (delta < -2) {
                    // Scroll UP (delta negativo) → mostrar, salvo que el boton
                    // en linea siga a la vista
                    if (navbar) navbar.classList.remove('nav-hidden');
                    if (stickyBar) stickyBar.classList.toggle('bar-hidden', tapada);
                } else if (delta > 4 && currentScrollY > 60) {
                    // Scroll DOWN (delta positivo, superada zona inicial) → ocultar
                    if (navbar) navbar.classList.add('nav-hidden');
                    if (stickyBar) stickyBar.classList.add('bar-hidden');
                } else if (tapada && stickyBar) {
                    // Sin cambio de direccion, pero el boton en linea acaba de
                    // entrar en pantalla: la barra cede igualmente.
                    stickyBar.classList.add('bar-hidden');
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
            window.addEventListener('resize', onScroll, { passive: true });
            updateNavScroll();   // estado correcto ya en la primera pintura
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
            ajustarBotonesVerMas();
            initVideoCarrusel();
            initShorts();
        });
        window.addEventListener('resize', ajustarBotonesVerMas);
    
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

        /* ═══════════════ SHORTS: REPRODUCCION AUTOMATICA ═══════════════
           Todos los reproductores se crean por adelantado, en silencio y
           ocultos tras la miniatura. Cada uno arranca y, en cuanto entrega su
           primer fotograma, se pausa y vuelve al segundo cero: asi queda
           cargado y con la careta inicial de YouTube ya pasada.
           Cuando una tarjeta se vuelve la activa solo hay que reanudar, no
           crear un iframe: no da tiempo a que asome la interfaz de YouTube. */
        /* 'local' si la carpeta videos/ tiene archivos; si no, 'youtube'. */
        const MOTOR_SHORTS = '<?= $videos_locales ? 'local' : 'youtube' ?>';
        let ytApiLista = (MOTOR_SHORTS === 'local'), ytApiPedida = false;
        const reproductoresShort = new Map();
        const shortsListos = new Set();
        let tarjetaShortActiva = null;
        let sonidoShorts = false;
        let seccionShortsVisible = false;

        function pedirApiYouTube() {
            if (MOTOR_SHORTS === 'local') return;   // no se carga nada de YouTube
            if (ytApiLista || ytApiPedida) return;
            ytApiPedida = true;
            const et = document.createElement('script');
            et.src = 'https://www.youtube.com/iframe_api';
            document.head.appendChild(et);
        }

        window.onYouTubeIframeAPIReady = function () {
            ytApiLista = true;
            precargarShorts();
        };

        /* Tarjetas a las que el navegador no dejo sonar: no se insiste hasta
           que el usuario vuelva a tocar el boton de sonido. */
        const sonidoBloqueado = new Set();

        /* Segundo en el que YouTube ya ha retirado su interfaz (titulo, canal,
           boton de reproduccion). Todos los shorts se dejan aparcados aqui, no
           en 0, para que al aparecer no se vea ni un fotograma de esa UI. */
        const ARRANQUE_SHORT = <?= $videos_locales ? '0' : '1.2' ?>;

        function aplicarSonido(rep, card) {
            try {
                if (sonidoShorts && !(card && sonidoBloqueado.has(card))) {
                    rep.unMute();
                    rep.setVolume(75);
                    /* Imprescindible volver a pedir play. Quitar el mute revoca
                       el permiso de reproduccion silenciosa que tenia el video y
                       el navegador lo PAUSA en el acto. Comprobado con Playwright
                       bajo la politica de Chrome en Android: solo unMute() deja
                       el short congelado; unMute() + playVideo() lo mantiene
                       sonando. Era la causa de que al pasar de short con el
                       volumen abierto se quedara todo parado. */
                    rep.playVideo();
                } else {
                    rep.mute();
                }
            } catch (e) { /* el reproductor aun no responde */ }
            if (card) card.classList.toggle('con-sonido', sonidoShorts);
        }

        /* Adaptador con la MISMA superficie que YT.Player. Envolver el <video>
           en esta interfaz permite que toda la maquinaria del carrusel
           -activarTarjetaShort, aplicarSonido, revelarShort, vigilarReproduccion,
           el bucle infinito y los gestos- siga funcionando sin tocar una linea,
           y que las pruebas automaticas valgan igual para los dos motores. */
        function crearReproductorLocal(card) {
            const v = card.querySelector('video');
            if (!v) return;

            const rep = {
                playVideo()  { const pr = v.play(); if (pr && pr.catch) pr.catch(function () {}); },
                pauseVideo() { try { v.pause(); } catch (e) {} },
                mute()       { v.muted = true; },
                unMute()     { v.muted = false; },
                isMuted()    { return v.muted; },
                setVolume(n) { v.volume = Math.max(0, Math.min(1, (n || 0) / 100)); },
                getVolume()  { return Math.round(v.volume * 100); },
                seekTo(seg)  { try { v.currentTime = seg || 0; } catch (e) {} },
                getCurrentTime() { return v.currentTime || 0; },
                /* Mismos codigos que YouTube: 0 fin, 1 reproduciendo,
                   2 pausado, 3 cargando. */
                getPlayerState() {
                    if (v.ended) return 0;
                    if (v.paused) return 2;
                    return v.readyState < 3 ? 3 : 1;
                },
                destroy() { try { v.pause(); } catch (e) {} },
                elemento: v,
            };

            // Equivalente a onStateChange -> PLAYING.
            v.addEventListener('playing', function () {
                if (!shortsListos.has(card)) {
                    shortsListos.add(card);
                    if (card !== tarjetaShortActiva) {
                        // Precargada: se deja lista y quieta, sin gastar red.
                        try { v.pause(); v.currentTime = ARRANQUE_SHORT; } catch (e) {}
                        return;
                    }
                }
                if (card === tarjetaShortActiva) {
                    aplicarSonido(rep, card);
                    revelarShort(card, rep);
                }
            });

            reproductoresShort.set(card, rep);
            /* Basta con la cabecera para tener portada; el clip completo se
               descarga cuando la tarjeta llega al centro. */
            try { v.load(); } catch (e) {}
        }

        function crearReproductorShort(card) {
            if (MOTOR_SHORTS === 'local') {
                if (card && !reproductoresShort.has(card)) crearReproductorLocal(card);
                return;
            }
            if (!ytApiLista || !card || reproductoresShort.has(card)) return;
            if (typeof YT === 'undefined' || !YT.Player) return;
            const hueco = card.querySelector('.vs-player');
            const id = card.getAttribute('data-youtube-id');
            if (!hueco || !id) return;

            hueco.innerHTML = '<div></div>';
            const rep = new YT.Player(hueco.firstElementChild, {
                videoId: id,
                playerVars: {
                    autoplay: 1, mute: 1, controls: 0, playsinline: 1,
                    rel: 0, modestbranding: 1, fs: 0, disablekb: 1,
                    iv_load_policy: 3,
                    loop: 1, playlist: id
                },
                events: {
                    onReady: function (e) {
                        e.target.mute();
                        e.target.playVideo();
                    },
                    onStateChange: function (e) {
                        if (e.data === YT.PlayerState.PLAYING) {
                            if (!shortsListos.has(card)) {
                                shortsListos.add(card);
                                if (card !== tarjetaShortActiva) {
                                    // Aparcado pasada la UI de YouTube, listo para aparecer limpio.
                                    try { e.target.pauseVideo(); e.target.seekTo(ARRANQUE_SHORT, true); } catch (err) {}
                                    return;
                                }
                            }
                            if (card === tarjetaShortActiva) {
                                aplicarSonido(e.target, card);
                                revelarShort(card, e.target);
                            }
                        } else if (e.data === YT.PlayerState.ENDED) {
                            e.target.playVideo();
                        }
                    }
                }
            });
            reproductoresShort.set(card, rep);
        }

        /* Se crean escalonados: diez iframes a la vez saturarian la red y el
           navegador justo cuando el usuario esta llegando a la seccion. */
        function precargarShorts() {
            if (!ytApiLista) return;
            document.querySelectorAll('.video-review-card').forEach(function (c, i) {
                setTimeout(function () { crearReproductorShort(c); }, i * 220);
            });
        }

        function pausarShort(card) {
            if (!card) return;
            card.classList.remove('reproduciendo');
            const rep = reproductoresShort.get(card);
            if (!rep) return;
            /* Se vuelve al punto limpio, no a 0. Y NO se silencia: si el
               usuario tiene el sonido abierto, los reproductores deben seguir
               desmuteados para que el siguiente suene sin pedir permiso otra
               vez (el permiso solo se concede dentro de un gesto). */
            try { rep.pauseVideo(); rep.seekTo(ARRANQUE_SHORT, true); } catch (e) {}
        }

        function pausarTodosLosShorts() {
            reproductoresShort.forEach(function (rep, card) { pausarShort(card); });
        }

        function activarTarjetaShort(card) {
            if (card === tarjetaShortActiva) return;
            if (tarjetaShortActiva) {
                tarjetaShortActiva.classList.remove('activa');
                pausarShort(tarjetaShortActiva);
            }
            document.querySelectorAll('.video-review-card.activa').forEach(function (c) { c.classList.remove('activa'); });

            tarjetaShortActiva = card || null;
            if (!card) return;
            card.classList.add('activa');
            card.classList.toggle('con-sonido', sonidoShorts);
            if (!seccionShortsVisible) return;

            const rep = reproductoresShort.get(card);
            if (rep) {
                /* El sonido se aplica AQUI, dentro del gesto que trajo esta
                   tarjeta al centro. Un movil solo concede audio si la llamada
                   nace de la interaccion del usuario: hacerlo desde un
                   setTimeout posterior ya cuenta como autoplay con sonido, el
                   navegador lo bloquea y el video se queda pausado. Ese era el
                   fallo al pasar de short con el volumen abierto.
                   aplicarSonido hace unMute + setVolume + playVideo, o mute()
                   si el sonido esta apagado. */
                aplicarSonido(rep, card);
                try { rep.playVideo(); } catch (e) {}
                /* Aqui NO se destapa: el video sigue pausado en su punto de
                   arranque y se veria la caratula. Lo hara revelarShort en
                   cuanto el reproductor confirme que rueda. */
                vigilarReproduccion(rep, card);
            } else {
                pedirApiYouTube();
                crearReproductorShort(card);
            }
        }

        /* Ningun short puede quedarse congelado en el centro: si tras varios
           intentos el navegador sigue sin arrancarlo con sonido, se reproduce
           mudo. Ver un video sin sonido es aceptable; verlo parado, no.
           La tarjeta se apunta en sonidoBloqueado para que onStateChange no
           vuelva a desmutearla y entre en bucle desmutear-pausar. */
        /* Unica puerta por la que se destapa el iframe, y solo cuando el video
           RUEDA de verdad. Antes se destapaba al activar la tarjeta, con el
           video todavia pausado, y se veia la caratula estatica de YouTube
           -boton de reproduccion incluido-. Medido: a +120 ms del gesto el
           reproductor estaba al 50% de opacidad en estado "cargando". */
        function revelarShort(card, rep) {
            setTimeout(function () {
                if (card !== tarjetaShortActiva) return;
                try { if (!rep || !rep.getPlayerState || rep.getPlayerState() !== 1) return; } catch (e) { return; }
                card.classList.add('reproduciendo');
            }, 220);
        }

        function vigilarReproduccion(rep, card) {
            let intentos = 0;
            (function revisar() {
                if (card !== tarjetaShortActiva) return;
                let estado = null;
                try { estado = rep.getPlayerState ? rep.getPlayerState() : null; } catch (e) { return; }
                if (estado === 1) { revelarShort(card, rep); return; }   // ya rueda
                if (estado === 3 && intentos < 6) { intentos++; setTimeout(revisar, 400); return; }
                intentos++;
                try {
                    if (intentos >= 3 && sonidoShorts) {
                        // Se rinde con el audio: la tarjeta deja de anunciarlo.
                        sonidoBloqueado.add(card); rep.mute(); card.classList.remove('con-sonido');
                    }
                    rep.playVideo();
                } catch (e) {}
                if (intentos < 6) setTimeout(revisar, 450);
            })();
        }

        function tarjetaShortCentrada() {
            const track = document.getElementById('videoReviewsTrack');
            if (!track) return null;
            const centro = track.scrollLeft + track.clientWidth / 2;
            let mejor = null, dist = Infinity;
            track.querySelectorAll('.video-review-card').forEach(function (c) {
                const d = Math.abs((c.offsetLeft + c.offsetWidth / 2) - centro);
                if (d < dist) { dist = d; mejor = c; }
            });
            return mejor;
        }

        function alternarSonidoShort(ev) {
            ev.stopPropagation();
            sonidoShorts = !sonidoShorts;
            // Es un gesto real del usuario: vuelve a haber permiso de audio.
            sonidoBloqueado.clear();
            /* Se aplica a TODOS los reproductores, no solo al visible. El
               navegador concede el audio dentro del gesto y por iframe: si se
               deja para cuando el usuario deslice, ese permiso ya no existe y
               el siguiente short aparece "con sonido" pero mudo de verdad.
               Desmutearlos ahora, mientras el dedo sigue en el boton, es lo que
               hace que el sonido viaje al resto del carrusel. */
            reproductoresShort.forEach(function (r, c) {
                try {
                    if (sonidoShorts) { r.unMute(); r.setVolume(75); }
                    else { r.mute(); }
                } catch (e) {}
            });
            const rep = tarjetaShortActiva ? reproductoresShort.get(tarjetaShortActiva) : null;
            if (rep) aplicarSonido(rep, tarjetaShortActiva);
            document.querySelectorAll('.video-review-card').forEach(function (c) { c.classList.toggle('con-sonido', sonidoShorts); });
            document.querySelectorAll('.vs-sonido').forEach(function (b) {
                b.setAttribute('aria-pressed', sonidoShorts ? 'true' : 'false');
                b.setAttribute('aria-label', sonidoShorts ? 'Silenciar' : 'Activar sonido');
            });
        }


        /* ─── BUCLE INFINITO ───
           No se clonan tarjetas: se RECIRCULAN. Cuando la primera queda muy a
           la izquierda se manda al final, y se descuenta su ancho del scroll,
           asi que a la vista no se mueve nada. Con eso siempre hay vecinas a
           ambos lados y no aparece el hueco blanco de los extremos.
           Solo se recolocan tarjetas alejadas de la activa: mover un nodo con
           un iframe dentro lo obliga a recargarse, y no queremos que eso le
           pase al video que se esta viendo. */
        function pasoCarrusel(track) {
            const card = track.querySelector('.video-review-card');
            if (!card) return 0;
            const hueco = parseFloat(getComputedStyle(track).columnGap || getComputedStyle(track).gap || 14) || 14;
            return card.offsetWidth + hueco;
        }

        /* ─── ORDEN VISUAL SIN TOCAR EL DOM ───
           Mover un nodo que contiene un <iframe> obliga al navegador a
           recargarlo: la ventana anterior se descarta y el objeto YT.Player
           se queda apuntando a ella, asi que cada mute()/playVideo()
           posterior falla con "postMessage ... target origin does not match"
           y esa tarjeta deja de responder al boton de sonido.
           La pista es flex, de modo que el orden visual se cambia con la
           propiedad `order` y el iframe nunca se entera: el video sigue
           cargado, en su posicion y sonando. */
        let ordenCarrusel = [];

        function aplicarOrdenCarrusel() {
            ordenCarrusel.forEach(function (c, i) { c.style.order = i; });
        }

        function recolocar(card, alFinal) {
            const i = ordenCarrusel.indexOf(card);
            if (i === -1) return;
            ordenCarrusel.splice(i, 1);
            if (alFinal) ordenCarrusel.push(card);
            else ordenCarrusel.unshift(card);
            aplicarOrdenCarrusel();
        }

        /* Una tarjeta se puede recolocar si esta fuera de la vista (con una
           tarjeta de holgura). Mover un nodo con iframe lo obliga a recargar,
           y eso solo es aceptable donde nadie lo esta mirando.
           Antes la guarda era "no muevas la activa", pero tras un
           desplazamiento largo la activa acaba siendo la primera del DOM y el
           bucle se atascaba justo al llegar al extremo. */
        function fueraDeLaVista(card, track) {
            const paso = pasoCarrusel(track);
            const izq = card.offsetLeft;
            const der = izq + card.offsetWidth;
            return der < track.scrollLeft - paso || izq > track.scrollLeft + track.clientWidth + paso;
        }

        function reciclarCarrusel() {
            const track = document.getElementById('videoReviewsTrack');
            if (!track) return;
            const paso = pasoCarrusel(track);
            if (!paso) return;
            const margen = paso * 2;
            let vueltas = 0;

            while (track.scrollLeft < margen && vueltas++ < 12) {
                const ultima = ordenCarrusel[ordenCarrusel.length - 1];
                if (!ultima || !fueraDeLaVista(ultima, track)) break;
                recolocar(ultima, false);
                track.scrollLeft += paso;
            }
            vueltas = 0;
            while (track.scrollLeft > track.scrollWidth - track.clientWidth - margen && vueltas++ < 12) {
                const primera = ordenCarrusel[0];
                if (!primera || !fueraDeLaVista(primera, track)) break;
                recolocar(primera, true);
                track.scrollLeft -= paso;
            }
        }

        /* Deja la tarjeta indicada en el centro exacto de la pista. */
        function centrarTarjeta(card, suave) {
            const track = card.parentElement;
            const destino = card.offsetLeft - (track.clientWidth - card.offsetWidth) / 2;
            track.scrollTo({ left: Math.max(0, destino), behavior: suave ? 'smooth' : 'auto' });
        }

        /* Al arrancar se llevan varias del final al principio para que la
           primera tenga vecinas a su izquierda y pueda quedar centrada. */
        function prepararBucle() {
            const track = document.getElementById('videoReviewsTrack');
            if (!track) return null;
            // Se numeran ANTES de reordenar: el orden del DOM es el orden
            // real de los videos y ya no vuelve a cambiar.
            ordenCarrusel = Array.prototype.slice.call(track.querySelectorAll('.video-review-card'));
            ordenCarrusel.forEach(function (c, i) { c.dataset.orden = i; });
            if (ordenCarrusel.length < 4) {
                aplicarOrdenCarrusel();
                return ordenCarrusel[0] || null;
            }
            const primeraOriginal = ordenCarrusel[0];
            for (let i = 0; i < 3; i++) {
                ordenCarrusel.unshift(ordenCarrusel.pop());
            }
            aplicarOrdenCarrusel();
            centrarTarjeta(primeraOriginal, false);
            return primeraOriginal;
        }

        /* ─── UN VIDEO POR GESTO ───
           El desplazamiento nativo se apaga (overflow-x: hidden) y el gesto lo
           gobernamos nosotros. Dos motivos:
           1. Con impulso nativo un deslizamiento fuerte recorre varias tarjetas.
           2. Reciclar el bucle toca scrollLeft, y hacerlo mientras el navegador
              anima su propio impulso produce el tiron que se veia.
           Ahora el movimiento es siempre de una tarjeta y el reciclado ocurre
           entre gestos, nunca durante. */
        let carruselAnimando = false;

        function moverCarrusel(direccion) {
            if (carruselAnimando) return;
            const track = document.getElementById('videoReviewsTrack');
            if (!track) return;

            // Se recicla ANTES de movernos para garantizar que haya vecina
            reciclarCarrusel();

            const actual = tarjetaShortActiva || tarjetaShortCentrada();
            if (!actual) return;
            const pos = ordenCarrusel.indexOf(actual);
            const destino = pos === -1 ? null : ordenCarrusel[pos + (direccion > 0 ? 1 : -1)];
            if (!destino) return;

            carruselAnimando = true;
            centrarTarjeta(destino, true);
            activarTarjetaShort(destino);
            actualizarIndicadorVideo();

            // Al terminar la animacion se recicla, ya sin pelear con nadie
            setTimeout(function () {
                carruselAnimando = false;
                reciclarCarrusel();
            }, 430);
        }

        function initGestosCarrusel(track) {
            let x0 = 0, y0 = 0, activo = false;

            track.addEventListener('touchstart', function (e) {
                if (!e.touches || e.touches.length !== 1) return;
                x0 = e.touches[0].clientX;
                y0 = e.touches[0].clientY;
                activo = true;
            }, { passive: true });

            track.addEventListener('touchend', function (e) {
                if (!activo || !e.changedTouches || e.changedTouches.length !== 1) return;
                activo = false;
                const dx = e.changedTouches[0].clientX - x0;
                const dy = e.changedTouches[0].clientY - y0;
                // Solo cuenta si el gesto es claramente horizontal: si no, el
                // usuario esta desplazando la pagina y no hay que estorbarle.
                if (Math.abs(dx) > 40 && Math.abs(dx) > Math.abs(dy)) {
                    moverCarrusel(dx < 0 ? 1 : -1);
                }
            }, { passive: true });

            /* Rueda o panel tactil horizontal: una tarjeta por impulso.
               El bloqueo evita que un solo gesto de trackpad, que dispara
               decenas de eventos, recorra medio carrusel. */
            let ruedaBloqueada = false;
            track.addEventListener('wheel', function (e) {
                if (Math.abs(e.deltaX) <= Math.abs(e.deltaY)) return;   // gesto vertical: es de la pagina
                e.preventDefault();
                if (ruedaBloqueada || carruselAnimando) return;
                ruedaBloqueada = true;
                moverCarrusel(e.deltaX > 0 ? 1 : -1);
                setTimeout(function () { ruedaBloqueada = false; }, 460);
            }, { passive: false });

            /* Teclado, para quien navegue sin raton. */
            track.setAttribute('tabindex', '0');
            track.addEventListener('keydown', function (e) {
                if (e.key === 'ArrowRight') { e.preventDefault(); moverCarrusel(1); }
                else if (e.key === 'ArrowLeft') { e.preventDefault(); moverCarrusel(-1); }
            });
        }

        function initShorts() {
            const track = document.getElementById('videoReviewsTrack');
            const seccion = document.getElementById('videoReviewsSection');
            if (!track || !seccion) return;
            if (typeof ES_MODO_EDICION !== 'undefined' && ES_MODO_EDICION) return;

            // Coloca la primera en el centro con vecinas a los dos lados
            const primera = prepararBucle();
            tarjetaShortActiva = primera || tarjetaShortCentrada();
            if (tarjetaShortActiva) tarjetaShortActiva.classList.add('activa');

            /* La carga empieza ANTES de que la seccion se vea: con 600px de
               margen los videos llegan preparados cuando el usuario aterriza. */
            const obsCarga = new IntersectionObserver(function (entradas) {
                entradas.forEach(function (en) {
                    if (en.isIntersecting) {
                        pedirApiYouTube();
                        if (ytApiLista) precargarShorts();
                        obsCarga.disconnect();
                    }
                });
            }, { rootMargin: '600px 0px' });
            obsCarga.observe(seccion);

            const obsVista = new IntersectionObserver(function (entradas) {
                entradas.forEach(function (en) {
                    seccionShortsVisible = en.isIntersecting;
                    if (en.isIntersecting) {
                        const c = tarjetaShortActiva || tarjetaShortCentrada();
                        if (c) { tarjetaShortActiva = null; activarTarjetaShort(c); }
                    } else {
                        pausarTodosLosShorts();
                    }
                });
            }, { threshold: 0.35 });
            obsVista.observe(seccion);

            initGestosCarrusel(track);
            actualizarIndicadorVideo();

            document.addEventListener('visibilitychange', function () {
                if (document.hidden) { pausarTodosLosShorts(); return; }
                if (!seccionShortsVisible || !tarjetaShortActiva) return;
                const rep = reproductoresShort.get(tarjetaShortActiva);
                if (rep) { try { rep.playVideo(); } catch (e) {} }
            });
        }

        /* Barra de progreso del carrusel. Sin flechas, es la unica pista de
           cuanto queda por recorrer. */
        /* En un carrusel en bucle no hay "cuanto llevas recorrido": el scroll
           se recicla constantemente. La barra se calcula del numero de video
           activo, que si es estable porque cada tarjeta guarda su orden
           original en data-orden. */
        function actualizarIndicadorVideo() {
            const barra = document.getElementById('videoCarruselBarra');
            if (!barra) return;
            const total = document.querySelectorAll('.video-review-card').length;
            if (!total) return;

            const orden = tarjetaShortActiva ? parseInt(tarjetaShortActiva.dataset.orden || '0', 10) : 0;
            const ancho = Math.max(12, 100 / total);
            const avance = total > 1 ? orden / (total - 1) : 0;
            barra.style.width = ancho + '%';
            barra.style.left = (avance * (100 - ancho)) + '%';
        }

        function initVideoCarrusel() {
            const track = document.getElementById('videoReviewsTrack');
            if (!track) return;
            actualizarIndicadorVideo();
            track.addEventListener('scroll', actualizarIndicadorVideo, { passive: true });
            window.addEventListener('resize', actualizarIndicadorVideo);
        }

        function editarVideoCard(card) {
            if (!card) return;
            const currentId = card.getAttribute('data-youtube-id') || '';

            const newUrl = prompt('Pega el link del Short de YouTube:\n(Ej: https://youtube.com/shorts/... o https://youtu.be/...)', currentId ? 'https://www.youtube.com/shorts/' + currentId : '');
            if (newUrl === null) return;

            const parsedId = extraerYouTubeId(newUrl);
            if (!parsedId) {
                alert('No se pudo reconocer un ID de YouTube válido.');
                return;
            }

            card.setAttribute('data-youtube-id', parsedId);
            const thumb = card.querySelector('.vs-thumb');
            if (thumb) {
                // oar2 es el recorte vertical: es el que cuadra con un Short
                thumb.src = 'https://i.ytimg.com/vi/' + parsedId + '/oar2.jpg';
                thumb.setAttribute('referrerpolicy', 'no-referrer');
            }

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
            const track = document.getElementById('videoReviewsTrack');
            if (!track) return;

            const card = document.createElement('article');
            card.className = 'video-review-card';
            card.setAttribute('data-youtube-id', id);
            card.innerHTML = `
                <div class="vs-media">
                    <img class="vs-thumb" src="https://i.ytimg.com/vi/${id}/oar2.jpg" referrerpolicy="no-referrer" alt="" loading="lazy">
                    <div class="vs-player"></div>
                    <button type="button" class="vs-sonido" onclick="alternarSonidoShort(event)" aria-label="Activar sonido" aria-pressed="false">
                        <svg class="vs-ico vs-ico-mute" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M11 5 6 9H2v6h4l5 4V5z"/><path d="m23 9-6 6"/><path d="m17 9 6 6"/>
                        </svg>
                        <svg class="vs-ico vs-ico-audio" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M11 5 6 9H2v6h4l5 4V5z"/><path d="M15.5 8.5a5 5 0 0 1 0 7"/><path d="M18.5 5.5a9 9 0 0 1 0 13"/>
                        </svg>
                    </button>
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
    <div class="review-photo-backdrop" id="reviewPhotoBackdrop" onclick="if (event.target === this) cerrarFotoOpinion()">
        <button type="button" class="rvf-cerrar" onclick="cerrarFotoOpinion()" aria-label="Cerrar">&#10005;</button>
        <div class="rvf-pista" id="reviewPhotoTrack" onscroll="actualizarPuntosFoto()"></div>
        <div class="rvf-puntos" id="reviewPhotoDots"></div>
    </div>
    <script>
        /* Se recibe la <img> pulsada, no su url: asi se sacan del propio DOM
           todas las fotos de esa opinion y en que posicion esta la tocada. */
        function abrirFotoOpinion(img) {
            var b = document.getElementById('reviewPhotoBackdrop');
            var pista = document.getElementById('reviewPhotoTrack');
            var puntos = document.getElementById('reviewPhotoDots');
            if (!b || !pista || !img || !img.parentElement) return;

            var fotos = [].slice.call(img.parentElement.querySelectorAll('.review-photo'));
            if (!fotos.length) fotos = [img];
            var indice = fotos.indexOf(img);
            if (indice < 0) indice = 0;

            pista.innerHTML = fotos.map(function (f) {
                return '<div class="rvf-slide"><img src="' + f.src + '" alt="Foto de la opinión ampliada"></div>';
            }).join('');
            puntos.innerHTML = fotos.length > 1 ? fotos.map(function (_, i) {
                return '<span class="rvf-punto' + (i === indice ? ' activo' : '') + '"></span>';
            }).join('') : '';

            b.classList.add('open');
            document.body.style.overflow = 'hidden';
            // Sin animacion: debe abrirse ya sobre la foto que se toco.
            pista.scrollLeft = pista.clientWidth * indice;
            actualizarPuntosFoto();
        }

        function actualizarPuntosFoto() {
            var pista = document.getElementById('reviewPhotoTrack');
            var puntos = document.getElementById('reviewPhotoDots');
            if (!pista || !puntos || !puntos.children.length) return;
            var i = Math.round(pista.scrollLeft / Math.max(1, pista.clientWidth));
            [].forEach.call(puntos.children, function (p, n) { p.classList.toggle('activo', n === i); });
        }

        function cerrarFotoOpinion() {
            var b = document.getElementById('reviewPhotoBackdrop');
            if (b) b.classList.remove('open');
            document.body.style.overflow = '';
        }

        document.addEventListener('keydown', function (e) {
            var b = document.getElementById('reviewPhotoBackdrop');
            if (!b || !b.classList.contains('open')) return;
            var pista = document.getElementById('reviewPhotoTrack');
            if (e.key === 'Escape') { cerrarFotoOpinion(); }
            else if (e.key === 'ArrowRight' && pista) { pista.scrollBy({ left: pista.clientWidth, behavior: 'smooth' }); }
            else if (e.key === 'ArrowLeft' && pista) { pista.scrollBy({ left: -pista.clientWidth, behavior: 'smooth' }); }
        });
    </script>
</body>
</html>
