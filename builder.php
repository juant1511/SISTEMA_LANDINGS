<?php
/* ============================================================
   🏗️ LANDING BUILDER - Generador de Landings E-Commerce
   Con Subida de Logo, Navbar Centrado, Envíos Colombia,
   Banner MercadoLibre Oficial, Productos Cruzados y Editor Visual
   ============================================================ */

require_once __DIR__ . '/config.php';

// Si por error se accede a /builder.php/landings/slug/... redirigir a /landings/slug/...
if (!empty($_SERVER['REQUEST_URI']) && preg_match('#^/builder\.php/landings/(.*)$#i', $_SERVER['REQUEST_URI'], $m)) {
    header("Location: /landings/" . $m[1]);
    exit;
}

$base_dir = __DIR__ . '/landings/';
$msg = '';
$msg_type = '';

// ─── PROCESAMIENTO DEL FORMULARIO ───
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generar'])) {

    $slug           = preg_replace('/[^a-z0-9\-]/', '', strtolower(trim($_POST['slug'] ?? '')));
    $producto       = trim($_POST['producto'] ?? 'Producto Oficial');
    $titulo_pagina  = trim($_POST['titulo_pagina'] ?? $producto);
    $marca          = trim($_POST['marca'] ?? 'OFICIAL');
    $categoria_1    = trim($_POST['categoria_1'] ?? 'COSMÉTICOS');
    $categoria_2    = trim($_POST['categoria_2'] ?? 'CUIDADO PERSONAL');
    $precio         = (int)preg_replace('/\D/', '', $_POST['precio'] ?? '15658');
    $precio_antiguo = (int)preg_replace('/\D/', '', $_POST['precio_antiguo'] ?? '');
    if ($precio_antiguo <= 0 && $precio > 0) {
        $precio_antiguo = (int)round($precio * 1.85);
    }

    $rating         = trim($_POST['rating'] ?? '5.0');
    $review_count   = (int)($_POST['review_count'] ?? 7);
    if ($review_count <= 0) $review_count = 7;

    $announcement   = trim($_POST['announcement'] ?? 'ENVÍOS GRATIS A COLOMBIA');
    if (empty($announcement)) $announcement = 'ENVÍOS GRATIS A COLOMBIA';

    $promo_badge    = trim($_POST['promo_badge'] ?? 'GET 5% OFF');
    $color_nombre   = trim($_POST['color_nombre'] ?? 'Original');
    $net_content    = trim($_POST['net_content'] ?? '1 Unidad');
    $whatsapp       = trim($_POST['whatsapp'] ?? '573000000000');
    $descripcion    = trim($_POST['descripcion'] ?? 'Producto de alta calidad y máxima duración. Formulado con los mejores estándares para brindarte resultados excepcionales desde el primer uso.');

    // ─── Paleta de Colores ───
    $preset_palette = trim($_POST['palette_preset'] ?? 'luxe_wood');
    $color_primary  = trim($_POST['color_primary'] ?? '#3e281b');
    $color_accent   = trim($_POST['color_accent'] ?? '#c59b6d');
    $color_button   = trim($_POST['color_button'] ?? '#252525');
    $color_topbar   = trim($_POST['color_topbar'] ?? '#000000');
    $color_bg       = trim($_POST['color_bg'] ?? '#ffffff');

    // Variantes de color (swatches)
    $swatches_raw   = trim($_POST['swatches'] ?? '#d89f82, #e6c589, #dfc6cf');
    $swatches_array = array_filter(array_map('trim', explode(',', $swatches_raw)));
    if (empty($swatches_array)) {
        $swatches_array = ['#d89f82', '#e6c589', '#dfc6cf'];
    }

    if (empty($slug)) {
        $msg = 'El slug (nombre de carpeta) es obligatorio.';
        $msg_type = 'error';
    } else {
        $dest = $base_dir . $slug . '/';
        $dest_img = $dest . 'img/';
        if (!is_dir($dest)) { mkdir($dest, 0777, true); }
        if (!is_dir($dest_img)) { mkdir($dest_img, 0777, true); }

        // ─── Subir Logo de Marca ───
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $ext_logo = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
            if (in_array($ext_logo, ['png', 'jpg', 'jpeg', 'webp', 'svg', 'gif'])) {
                move_uploaded_file($_FILES['logo']['tmp_name'], $dest . 'logo.png');
            }
        }

        // Copiar mercadito.webp para el banner
        if (file_exists(__DIR__ . '/mercadito.webp')) {
            copy(__DIR__ . '/mercadito.webp', $dest . 'mercadito.webp');
        }

        // ─── Subir y Procesar Galería de Imágenes ───
        $imagenes_subidas = [];
        if (isset($_FILES['galeria']) && is_array($_FILES['galeria']['name'])) {
            $total_files = count($_FILES['galeria']['name']);
            for ($i = 0; $i < $total_files; $i++) {
                if ($_FILES['galeria']['error'][$i] === UPLOAD_ERR_OK) {
                    $ext = strtolower(pathinfo($_FILES['galeria']['name'][$i], PATHINFO_EXTENSION));
                    if (in_array($ext, ['png', 'jpg', 'jpeg', 'webp', 'gif'])) {
                        $idx = count($imagenes_subidas) + 1;
                        $filename = "img_{$idx}.{$ext}";
                        $target_path = $dest_img . $filename;
                        if (move_uploaded_file($_FILES['galeria']['tmp_name'][$i], $target_path)) {
                            $imagenes_subidas[] = "img/{$filename}";
                        }
                    }
                }
            }
        }

        // Si no subió imágenes en esta edición, buscar las existentes en img/
        if (empty($imagenes_subidas) && is_dir($dest_img)) {
            $existing_files = scandir($dest_img);
            foreach ($existing_files as $f) {
                if ($f !== '.' && $f !== '..' && preg_match('/\.(png|jpg|jpeg|webp|gif)$/i', $f)) {
                    $imagenes_subidas[] = "img/{$f}";
                }
            }
        }

        // Si todavía no hay imágenes, usar placeholders elegantes
        if (empty($imagenes_subidas)) {
            $imagenes_subidas = [
                'https://images.unsplash.com/photo-1596462502278-27bfdc403348?w=800&auto=format&fit=crop&q=80',
                'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?w=800&auto=format&fit=crop&q=80',
                'https://images.unsplash.com/photo-1512496015851-a90fb38ba796?w=800&auto=format&fit=crop&q=80',
                'https://images.unsplash.com/photo-1571781926291-c477ebfd024b?w=800&auto=format&fit=crop&q=80'
            ];
        }

        // Guardar primera imagen como producto.png y desktop.png para compatibilidad total con Pasarela
        if (isset($imagenes_subidas[0]) && strpos($imagenes_subidas[0], 'img/') === 0) {
            $first_img_path = $dest . $imagenes_subidas[0];
            if (file_exists($first_img_path)) {
                copy($first_img_path, $dest . 'producto.png');
                copy($first_img_path, $dest . 'desktop.png');
            }
        }

        // ─── Guardar en la Base de Datos (Supabase) ───
        $imagenes_paths_db = [];
        foreach ($imagenes_subidas as $idx => $img_rel) {
            if (strpos($img_rel, 'http') === 0) {
                $imagenes_paths_db['img_' . ($idx + 1)] = $img_rel;
            } else {
                $imagenes_paths_db['img_' . ($idx + 1)] = URL_LANDINGS . "/landings/{$slug}/{$img_rel}";
            }
        }
        $imagenes_paths_db['producto'] = $imagenes_paths_db['img_1'] ?? '';
        $imagenes_paths_db['desktop']  = $imagenes_paths_db['img_1'] ?? '';

        // Token de la landing
        $stmt_check = $pdo->prepare("SELECT id, token FROM landings WHERE slug = ?");
        $stmt_check->execute([$slug]);
        $existe = $stmt_check->fetch();

        $landing_token = (!empty($existe) && !empty($existe['token'])) ? $existe['token'] : generarTokenAleatorio(16);

        $theme_config = [
            'primary' => $color_primary,
            'accent'  => $color_accent,
            'button'  => $color_button,
            'topbar'  => $color_topbar,
            'bg'      => $color_bg,
            'marca'   => $marca,
            'cat1'    => $categoria_1,
            'cat2'    => $categoria_2
        ];

        if ($existe) {
            $stmt = $pdo->prepare("UPDATE landings SET producto = ?, precio = ?, imagenes = ?, config_botones = ?, token = ? WHERE slug = ?");
            $stmt->execute([$producto, $precio, json_encode($imagenes_paths_db), json_encode($theme_config), $landing_token, $slug]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO landings (slug, producto, precio, imagenes, config_botones, token) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$slug, $producto, $precio, json_encode($imagenes_paths_db), json_encode($theme_config), $landing_token]);
        }

        // ─── Generación de index.php para la Landing ───
        $default_reviews = [
            ['author' => 'S***h', 'color' => $color_nombre, 'size' => $net_content, 'stars' => '★★★★★', 'comment' => 'Excelente calidad y acabados de primera. 100% recomendado.', 'date' => '2026.04.12'],
            ['author' => 's***m', 'color' => $color_nombre, 'size' => $net_content, 'stars' => '★★★★★', 'comment' => 'Very nice, producto totalmente original y el envío fue muy rápido.', 'date' => '2026.05.02'],
            ['author' => 'j***5', 'color' => $color_nombre, 'size' => $net_content, 'stars' => '★★★★★', 'comment' => 'Very nice, superó todas mis expectativas.', 'date' => '2026.05.18'],
            ['author' => 'T***m', 'color' => $color_nombre, 'size' => $net_content, 'stars' => '★★★★★', 'comment' => 'Excelente servicio y pago contraentrega en la puerta de la casa.', 'date' => '2026.06.01'],
            ['author' => 'B***i', 'color' => $color_nombre, 'size' => $net_content, 'stars' => '★★★★★', 'comment' => 'Increíble diseño y funcionalidad. Siempre compro esta marca.', 'date' => '2026.06.14'],
            ['author' => 'A***r', 'color' => $color_nombre, 'size' => $net_content, 'stars' => '★★★★★', 'comment' => 'Gran compra, rendimiento insuperable.', 'date' => '2026.06.20'],
            ['author' => 'K***y', 'color' => $color_nombre, 'size' => $net_content, 'stars' => '★★★★★', 'comment' => 'Materiales de altísima gama y empaque impecable.', 'date' => '2026.06.28']
        ];

        $imagenes_json      = json_encode($imagenes_subidas, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $swatches_json      = json_encode($swatches_array, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $reviews_json       = json_encode($default_reviews, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $producto_json      = json_encode($producto, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $marca_json         = json_encode($marca, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $color_json         = json_encode($color_nombre, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $net_json           = json_encode($net_content, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $token_json         = json_encode($landing_token);
        $slug_json          = json_encode($slug);

        $precio_fmt = number_format($precio, 0, ',', '.');
        $precio_antiguo_fmt = $precio_antiguo > 0 ? number_format($precio_antiguo, 0, ',', '.') : '';
        $descuento_pct = ($precio_antiguo > $precio && $precio_antiguo > 0) ? round((($precio_antiguo - $precio) / $precio_antiguo) * 100) : 0;

        $landing_code = <<<HTML
<?php
require_once __DIR__ . '/../../config.php';
\$landing_slug  = '{$slug}';
\$landing_token = obtenerOCrearTokenLanding(\$landing_slug, {$producto_json}, {$precio});
\$precio_num    = {$precio};
\$precio_fmt    = '{$precio_fmt}';
\$es_modo_edicion = isset(\$_GET['modo_edicion']) && \$_GET['modo_edicion'] == '1';

// ─── Cargar Productos de Otras Landings del Sistema ───
\$otros_productos = [];
try {
    if (isset(\$pdo)) {
        \$stmt = \$pdo->prepare("SELECT slug, producto, precio, imagenes FROM landings WHERE slug != ? ORDER BY id DESC LIMIT 12");
        \$stmt->execute([\$landing_slug]);
        \$rows = \$stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach (\$rows as \$r) {
            \$other_slug = \$r['slug'];
            \$imgs = is_array(\$r['imagenes']) ? \$r['imagenes'] : (json_decode(\$r['imagenes'] ?? '{}', true) ?: []);
            \$raw_img = \$imgs['img_1'] ?? (\$imgs['producto'] ?? (\$imgs['desktop'] ?? 'img/img_1.jpg'));

            \$final_img = (strpos(\$raw_img, 'http') === 0) ? \$raw_img : "../{\$other_slug}/" . ltrim(\$raw_img, '/');
            \$otros_productos[] = [
                'slug'   => \$other_slug,
                'nombre' => \$r['producto'],
                'precio' => '$ ' . number_format(\$r['precio'], 0, ',', '.'),
                'url'    => "../{\$other_slug}/",
                'img'    => \$final_img
            ];
            if (count(\$otros_productos) >= 6) break;
        }
    }
} catch (Exception \$e) {}

// Fallback por escaneo directo de archivos locales
if (empty(\$otros_productos) && is_dir(__DIR__ . '/../')) {
    \$carpetas = scandir(__DIR__ . '/../');
    foreach (\$carpetas as \$c) {
        if (\$c !== '.' && \$c !== '..' && \$c !== 'uploads' && \$c !== \$landing_slug && is_dir(__DIR__ . '/../' . \$c) && file_exists(__DIR__ . '/../' . \$c . '/index.php')) {
            \$html_land = file_get_contents(__DIR__ . '/../' . \$c . '/index.php');
            
            \$nombre = ucwords(str_replace('-', ' ', \$c));
            if (preg_match('/<title>(.*?)<\/title>/is', \$html_land, \$m_t)) {
                \$nombre = trim(\$m_t[1]);
            }

            \$img_url = '';
            if (preg_match('/<img\s+id="mainImage"\s+src="([^"]+)"/i', \$html_land, \$m_i)) {
                \$img_url = \$m_i[1];
            } elseif (preg_match('/const\s+IMAGENES\s*=\s*\[\s*"([^"]+)"/i', \$html_land, \$m_i2)) {
                \$img_url = \$m_i2[1];
            } elseif (file_exists(__DIR__ . '/../' . \$c . '/producto.png')) {
                \$img_url = "../{\$c}/producto.png";
            }

            \$precio_text = 'Ver Oferta ➔';
            if (preg_match('/<span\s+class="current-price"[^>]*>([^<]+)<\/span>/i', \$html_land, \$m_p)) {
                \$precio_text = trim(\$m_p[1]);
            }

            if (!empty(\$img_url)) {
                \$otros_productos[] = [
                    'slug'   => \$c,
                    'nombre' => \$nombre,
                    'precio' => \$precio_text,
                    'url'    => "../{\$c}/",
                    'img'    => \$img_url
                ];
            }
            if (count(\$otros_productos) >= 6) break;
        }
    }
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
    <title>{$titulo_pagina}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&family=Inter:wght@400;500;600;700;900&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: {$color_primary};
            --accent: {$color_accent};
            --btn-bg: {$color_button};
            --topbar-bg: {$color_topbar};
            --body-bg: {$color_bg};
            --text-main: #111111;
            --text-muted: #6b7280;
            --border-color: #e5e7eb;
            --star-color: #f59e0b;
            --font-heading: 'Montserrat', sans-serif;
            --font-body: 'Inter', sans-serif;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            font-family: var(--font-body);
            background-color: var(--body-bg);
            color: var(--text-main);
            line-height: 1.4;
            padding-bottom: 0;
            overflow-x: hidden;
        }

        @media (max-width: 991px) {
            body { padding-bottom: 85px; }
        }

        .navbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 24px;
            background-color: #ffffff;
            border-bottom: 1px solid #f3f4f6;
            position: sticky;
            top: 0;
            z-index: 100;
            min-height: 75px;
        }

        .nav-left {
            width: 50px;
            display: flex;
            align-items: center;
        }

        .nav-center-logo {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .brand-logo-img {
            max-height: 56px;
            max-width: 220px;
            object-fit: contain;
            transition: transform 0.2s ease;
        }
        .brand-logo-img:hover {
            transform: scale(1.03);
        }

        .brand-logo-text {
            font-family: var(--font-heading);
            font-size: 26px;
            font-weight: 900;
            letter-spacing: 3px;
            color: #111111;
            text-transform: uppercase;
            text-decoration: none;
        }

        @media (min-width: 768px) {
            .brand-logo-img { max-height: 66px; max-width: 260px; }
            .brand-logo-text { font-size: 30px; letter-spacing: 3.5px; }
        }

        .nav-btn-icon {
            background: none;
            border: none;
            cursor: pointer;
            color: #111827;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 4px;
        }

        .nav-right {
            width: 40px;
            display: flex;
            justify-content: flex-end;
            align-items: center;
        }

        .cart-trigger {
            position: relative;
            background: none;
            border: none;
            cursor: pointer;
            color: #111827;
            padding: 4px;
        }

        .cart-badge-count {
            position: absolute;
            top: -2px;
            right: -4px;
            background-color: var(--primary);
            color: #ffffff;
            font-size: 10px;
            font-weight: 700;
            width: 17px;
            height: 17px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #ffffff;
        }

        /* ─── MAIN CONTAINER & PRODUCT GRID ─── */
        .landing-container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 20px 20px 30px 20px;
        }

        .product-grid-layout {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        /* ─── GALLERY SECTION ─── */
        .gallery-wrapper-desktop {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .main-image-wrap {
            order: 1;
            width: 100%;
            aspect-ratio: 1 / 1;
            background-color: #fafafa;
            border-radius: 16px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04);
            cursor: zoom-in;
        }

        .main-image-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: opacity 0.25s ease, transform 0.3s ease;
        }

        .gallery-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 38px;
            height: 38px;
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid var(--border-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 10;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .gallery-arrow.prev { left: 16px; }
        .gallery-arrow.next { right: 16px; }

        .thumbnails-strip {
            order: 2;
            display: flex;
            gap: 10px;
            overflow-x: auto;
            padding-bottom: 6px;
            scrollbar-width: none;
            -webkit-overflow-scrolling: touch;
        }

        .thumbnails-strip::-webkit-scrollbar { display: none; }

        .thumb-item {
            flex: 0 0 68px;
            height: 68px;
            border-radius: 10px;
            overflow: hidden;
            border: 2px solid var(--border-color);
            cursor: pointer;
            transition: all 0.2s ease;
            background: #f9fafb;
        }

        .thumb-item.active {
            border-color: var(--primary);
            box-shadow: 0 0 0 1px var(--primary);
        }

        .thumb-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* ─── PRODUCT INFO ─── */
        .product-info { padding: 0 4px; }

        .product-title {
            font-family: var(--font-heading);
            font-size: 20px;
            font-weight: 800;
            color: #111111;
            line-height: 1.35;
            margin-bottom: 8px;
        }

        .rating-row {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 14px;
        }

        .stars-container {
            display: flex;
            color: var(--star-color);
            font-size: 15px;
        }

        .rating-number {
            font-size: 13px;
            font-weight: 700;
            color: #374151;
        }

        .reviews-count {
            font-size: 13px;
            color: var(--text-muted);
        }

        .price-row {
            display: flex;
            align-items: baseline;
            gap: 10px;
            margin-bottom: 20px;
        }

        .current-price {
            font-family: var(--font-heading);
            font-size: 26px;
            font-weight: 900;
            color: #111111;
        }

        .old-price {
            font-size: 16px;
            color: #9ca3af;
            text-decoration: line-through;
            font-weight: 600;
        }

        .discount-pill {
            background-color: #fee2e2;
            color: #ef4444;
            font-size: 11px;
            font-weight: 800;
            padding: 3px 8px;
            border-radius: 6px;
        }

        /* ─── VARIANTS ─── */
        .variant-block {
            margin-bottom: 18px;
            border-top: 1px solid #f3f4f6;
            padding-top: 14px;
        }

        .variant-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            font-size: 13px;
        }

        .variant-label { font-weight: 700; color: #111111; }
        .variant-label span { font-weight: 500; color: #4b5563; }

        .swatches-row {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .swatch-circle {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            cursor: pointer;
            position: relative;
            border: 2px solid transparent;
            box-shadow: inset 0 0 0 1px rgba(0,0,0,0.15);
            transition: all 0.2s ease;
        }

        .swatch-circle.active {
            border-color: #111111;
            transform: scale(1.1);
        }

        /* ─── NET CONTENT / SIZE ─── */
        .size-block { margin-bottom: 22px; }

        .size-pills-row { display: flex; gap: 10px; }

        .size-pill {
            padding: 9px 20px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            background: #111111;
            color: #ffffff;
            border: none;
            cursor: pointer;
        }

        /* ─── DESKTOP PURCHASE ACTION ROW ─── */
        .desktop-action-row {
            display: none;
            gap: 14px;
            align-items: center;
            margin-bottom: 24px;
        }

        .qty-controls-desktop {
            display: flex;
            align-items: center;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            overflow: hidden;
            height: 50px;
            background: #ffffff;
        }

        .qty-btn-desktop {
            background: #f9fafb;
            border: none;
            width: 42px;
            height: 100%;
            font-size: 18px;
            font-weight: 700;
            cursor: pointer;
            color: #374151;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
        }

        .qty-btn-desktop:hover { background: #f3f4f6; }

        .qty-val-desktop {
            width: 44px;
            text-align: center;
            font-size: 15px;
            font-weight: 700;
        }

        .btn-add-desktop {
            flex: 1;
            height: 50px;
            background-color: var(--btn-bg);
            color: #ffffff;
            border: none;
            border-radius: 10px;
            font-family: var(--font-heading);
            font-size: 14px;
            font-weight: 800;
            letter-spacing: 1px;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-add-desktop:hover {
            opacity: 0.92;
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(0,0,0,0.18);
        }

        /* ─── ACCORDION & TRUST ─── */
        .accordion-item { border-bottom: 1px solid var(--border-color); }

        .accordion-header {
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 0;
            background: none;
            border: none;
            font-family: var(--font-heading);
            font-size: 14px;
            font-weight: 700;
            color: #111111;
            cursor: pointer;
            text-align: left;
        }

        .accordion-body {
            display: none;
            padding-bottom: 14px;
            font-size: 13px;
            color: #4b5563;
            line-height: 1.6;
        }

        .accordion-body.open { display: block; }

        /* ─── SECURE PAYMENT (ESTILO SHEGLAM / MERCADOLIBRE) ─── */
        .secure-trust-box {
            background: #f0fdf4;
            border: 1px solid #dcfce7;
            border-radius: 10px;
            padding: 14px 18px;
            margin-top: 22px;
        }

        .secure-trust-header {
            display: flex;
            align-items: center;
            gap: 8px;
            font-family: var(--font-heading);
            font-size: 14px;
            font-weight: 800;
            color: #111111;
            margin-bottom: 10px;
        }

        .secure-trust-header svg {
            flex-shrink: 0;
        }

        .secure-trust-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .secure-trust-list li {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            font-size: 12.5px;
            color: #374151;
            line-height: 1.45;
        }

        .secure-trust-list .check-icon {
            color: #10b981;
            font-weight: 900;
            font-size: 13px;
            line-height: 1.3;
        }

        /* ─── BANNER OFICIAL ESTILO MERCADOLIBRE CON EL PRODUCTO ─── */
        .ml-promo-banner-wrap {
            max-width: 1280px;
            margin: 25px auto 10px auto;
            padding: 0 20px;
            cursor: pointer;
        }

        .ml-banner-inner {
            background: #ffe600;
            border-radius: 50px;
            padding: 10px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            box-shadow: 0 4px 15px rgba(255, 230, 0, 0.35);
            transition: transform 0.2s, box-shadow 0.2s;
            overflow: hidden;
        }

        .ml-banner-inner:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 230, 0, 0.5);
        }

        .ml-banner-left {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }

        .ml-handshake-icon {
            width: 36px;
            height: 36px;
            background: #ffffff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #2d3277;
            padding: 4px;
        }

        .ml-handshake-icon img, .ml-handshake-icon svg {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .ml-text-block {
            display: flex;
            flex-direction: column;
            line-height: 1;
            font-family: 'Inter', sans-serif;
            font-weight: 900;
            font-size: 13px;
            color: #2d3277;
        }

        .ml-banner-divider {
            width: 1px;
            height: 28px;
            background: rgba(0, 0, 0, 0.15);
            flex-shrink: 0;
        }

        .ml-banner-center {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 10px;
            overflow: hidden;
            white-space: nowrap;
        }

        .ml-brand-name {
            font-family: 'Montserrat', sans-serif;
            font-weight: 900;
            font-size: 15px;
            color: #111111;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .ml-product-name {
            font-family: 'Montserrat', sans-serif;
            font-weight: 800;
            font-size: 14px;
            color: #111111;
            text-transform: uppercase;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .ml-stripes {
            color: #d97706;
            font-weight: 900;
            font-size: 16px;
            letter-spacing: -2px;
            display: flex;
        }

        .ml-banner-right { flex-shrink: 0; }

        .ml-free-shipping-pill {
            display: flex;
            align-items: center;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
            font-family: 'Montserrat', sans-serif;
            font-size: 11px;
            font-weight: 800;
        }

        .pill-dark {
            background: #0a1945;
            color: #ffffff;
            padding: 8px 14px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .pill-white {
            background: #ffffff;
            color: #0a1945;
            padding: 8px 12px;
        }

        @media (max-width: 768px) {
            .ml-banner-inner {
                border-radius: 16px;
                padding: 10px 14px;
                flex-wrap: wrap;
                gap: 8px;
            }
            .ml-banner-divider { display: none; }
            .ml-free-shipping-pill { font-size: 10px; }
            .pill-dark, .pill-white { padding: 6px 10px; }
        }

        /* ─── CUSTOMER REVIEWS SECTION ─── */
        .customer-reviews-section {
            max-width: 1280px;
            margin: 45px auto 30px auto;
            padding: 0 20px;
            font-family: '__MiddleEast_309aa8', 'Montserrat', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        .reviews-header-block {
            text-align: center;
            margin-bottom: 20px;
        }

        .reviews-main-title {
            font-family: '__MiddleEast_309aa8', 'Montserrat', sans-serif;
            font-size: 26px;
            font-weight: 900;
            color: #111111;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }

        .overall-rating-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 24px;
        }

        .overall-rating-num {
            font-size: 24px;
            font-weight: 900;
            color: #111111;
        }

        .overall-stars-gold {
            color: #f59e0b;
            font-size: 20px;
            letter-spacing: 2px;
        }

        .reviews-filters-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 14px;
            padding-bottom: 16px;
            border-bottom: 1px solid #f3f4f6;
            margin-bottom: 10px;
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
            font-size: 12px;
            color: #374151;
            font-weight: 600;
        }

        .filter-select-box {
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 6px 24px 6px 10px;
            font-size: 12px;
            color: #111111;
            background: #ffffff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'%3E%3Cpath fill='%236B7280' d='M0 0l5 6 5-6z'/%3E%3C/svg%3E") no-repeat right 8px center;
            background-size: 8px;
            appearance: none;
            cursor: pointer;
            font-weight: 500;
        }

        .review-card-item {
            display: grid;
            grid-template-columns: 220px 1fr auto;
            gap: 20px;
            padding: 22px 0;
            border-bottom: 1px solid #f3f4f6;
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
            font-weight: 800;
            font-size: 13.5px;
            color: #111111;
        }

        .reviewer-meta {
            font-size: 11.5px;
            color: #6b7280;
        }

        .review-content-col {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .review-stars-row {
            color: #f59e0b;
            font-size: 14px;
            letter-spacing: 1px;
        }

        .review-comment-text {
            font-size: 13px;
            color: #1f2937;
            line-height: 1.5;
            font-weight: 600;
        }

        .review-date-badge {
            font-size: 11px;
            color: #9ca3af;
            white-space: nowrap;
            text-align: right;
        }

        @media (max-width: 768px) {
            .review-date-badge {
                text-align: left;
            }
        }

        .reviews-pagination-row {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 8px;
            margin-top: 24px;
            font-size: 12px;
            color: #6b7280;
        }

        .page-btn {
            width: 28px;
            height: 28px;
            border: 1px solid transparent;
            background: transparent;
            border-radius: 50%;
            cursor: pointer;
            font-size: 12px;
            font-weight: 700;
            color: #374151;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .page-btn.active {
            background: #111111;
            color: #ffffff;
        }

        .page-btn:hover:not(.active) {
            background: #f3f4f6;
        }

        /* ─── SECCIÓN MÁS PRODUCTOS (CRUZADOS) ─── */
        .more-to-love-section {
            max-width: 1280px;
            margin: 30px auto 40px auto;
            padding: 0 20px;
            text-align: center;
        }

        .section-heading-center {
            font-family: var(--font-heading);
            font-size: 22px;
            font-weight: 900;
            letter-spacing: 1px;
            color: #111111;
            margin-bottom: 18px;
            text-transform: uppercase;
        }

        .more-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
            gap: 16px;
        }

        .more-card {
            background: #ffffff;
            border: 1px solid #f3f4f6;
            border-radius: 12px;
            overflow: hidden;
            padding: 10px;
            text-align: left;
            text-decoration: none;
            display: flex;
            flex-direction: column;
            transition: all 0.2s ease;
        }

        .more-card:hover {
            box-shadow: 0 8px 20px rgba(0,0,0,0.06);
            transform: translateY(-2px);
            border-color: var(--accent);
        }

        .more-card-img {
            width: 100%;
            aspect-ratio: 1/1;
            border-radius: 8px;
            object-fit: cover;
            background: #fafafa;
        }

        .more-card-title {
            font-size: 13px;
            font-weight: 700;
            color: #111111;
            margin: 8px 0 4px 0;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .more-card-stars {
            font-size: 12px;
            color: var(--star-color);
            margin-bottom: 4px;
        }

        .more-card-price {
            font-weight: 900;
            font-size: 14px;
            color: #111111;
            margin-top: auto;
        }

        /* ─── FOOTER GENÉRICO (SIN HIPERVÍNCULOS) ─── */
        .generic-footer {
            background: #000000;
            color: #ffffff;
            padding: 40px 20px 24px 20px;
            margin-top: 60px;
        }

        .generic-footer-container {
            max-width: 1280px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 28px;
            border-bottom: 1px solid #222222;
            padding-bottom: 30px;
        }

        .footer-pillar {
            display: flex;
            gap: 12px;
            align-items: flex-start;
        }

        .footer-pillar .icon { font-size: 24px; }

        .footer-pillar h4 {
            font-family: var(--font-heading);
            font-size: 13px;
            font-weight: 800;
            color: #ffffff;
            margin-bottom: 4px;
            text-transform: uppercase;
        }

        .footer-pillar p {
            font-size: 12px;
            color: #9ca3af;
            line-height: 1.4;
        }

        .footer-bottom-generic {
            max-width: 1280px;
            margin: 20px auto 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 11px;
            color: #6b7280;
            flex-wrap: wrap;
            gap: 14px;
        }

        /* ─── STICKY BOTTOM BAR (MOBILE ONLY) ─── */
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
        }

        .support-btn {
            width: 46px;
            height: 46px;
            border-radius: 10px;
            border: 1px solid var(--border-color);
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #111111;
            text-decoration: none;
            flex-shrink: 0;
        }

        .btn-add-to-cart {
            flex: 1;
            height: 48px;
            background-color: var(--btn-bg);
            color: #ffffff;
            border: none;
            border-radius: 12px;
            font-family: var(--font-heading);
            font-size: 14px;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            cursor: pointer;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .floating-promo-tag {
            position: absolute;
            top: -42px;
            right: 16px;
            background: #000000;
            color: #ffffff;
            font-size: 11px;
            font-weight: 800;
            padding: 8px 14px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.25);
            animation: bounceSoft 2.5s infinite ease-in-out;
            cursor: pointer;
        }

        @keyframes bounceSoft {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }

        /* ─── DESKTOP ADAPTATIONS (MIN-WIDTH 992px) ─── */
        @media (min-width: 992px) {
            .product-grid-layout {
                display: grid;
                grid-template-columns: 1.15fr 1fr;
                gap: 50px;
                align-items: start;
            }

            .gallery-wrapper-desktop {
                flex-direction: row;
                gap: 16px;
            }

            .thumbnails-strip {
                order: 1;
                flex-direction: column;
                width: 76px;
                max-height: 520px;
                overflow-y: auto;
                overflow-x: hidden;
                padding-bottom: 0;
            }

            .thumb-item {
                flex: 0 0 74px;
                height: 74px;
            }

            .main-image-wrap {
                order: 2;
                flex: 1;
                max-width: 520px;
            }

            .desktop-action-row {
                display: flex;
            }

            .sticky-footer-bar {
                display: none !important;
            }

            .more-grid {
                grid-template-columns: repeat(6, 1fr);
            }
        }

        /* ─── LIGHTBOX POPUP MODAL (VENTANA FLOTANTE) ─── */
        .lightbox-modal {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.94);
            z-index: 99999;
            display: none;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            backdrop-filter: blur(5px);
        }

        .lightbox-modal.open { display: flex; }

        .lightbox-close-btn {
            position: absolute;
            top: 20px;
            right: 24px;
            background: rgba(255, 255, 255, 0.15);
            border: none;
            color: #ffffff;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            font-size: 24px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
            z-index: 100;
        }

        .lightbox-close-btn:hover { background: rgba(255, 255, 255, 0.3); }

        .lightbox-main-view {
            max-width: 90vw;
            max-height: 78vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .lightbox-main-view img {
            max-width: 100%;
            max-height: 78vh;
            object-fit: contain;
            border-radius: 8px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.5);
        }

        .lightbox-nav-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.2);
            border: none;
            border-radius: 50%;
            color: #ffffff;
            font-size: 24px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
        }

        .lightbox-nav-btn:hover { background: rgba(255, 255, 255, 0.4); }
        .lightbox-nav-btn.prev { left: -70px; }
        .lightbox-nav-btn.next { right: -70px; }

        @media (max-width: 768px) {
            .lightbox-nav-btn.prev { left: 10px; }
            .lightbox-nav-btn.next { right: 10px; }
        }

        .lightbox-thumbs-row {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            max-width: 90vw;
            overflow-x: auto;
            padding: 8px;
        }

        .lightbox-thumb {
            width: 54px;
            height: 54px;
            border-radius: 6px;
            overflow: hidden;
            border: 2px solid transparent;
            cursor: pointer;
            opacity: 0.6;
            transition: all 0.2s;
        }

        .lightbox-thumb.active {
            border-color: #ffffff;
            opacity: 1;
            transform: scale(1.1);
        }

        .lightbox-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* ─── SHOPPING CART DRAWER ─── */
        .cart-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.6);
            z-index: 10000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            backdrop-filter: blur(2px);
        }

        .cart-overlay.open { opacity: 1; visibility: visible; }

        .cart-drawer {
            position: fixed;
            top: 0;
            right: -100%;
            bottom: 0;
            width: 100%;
            max-width: 420px;
            background: #ffffff;
            z-index: 10001;
            transition: right 0.35s cubic-bezier(0.16, 1, 0.3, 1);
            display: flex;
            flex-direction: column;
            box-shadow: -5px 0 25px rgba(0,0,0,0.2);
        }

        .cart-overlay.open .cart-drawer { right: 0; }

        .cart-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 20px;
            border-bottom: 1px solid var(--border-color);
        }

        .cart-header h3 {
            font-family: var(--font-heading);
            font-size: 16px;
            font-weight: 800;
            color: #111111;
        }

        .close-cart-btn {
            background: none;
            border: none;
            font-size: 22px;
            cursor: pointer;
            color: #6b7280;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 4px;
        }

        .shipping-progress-wrap {
            background: #f9fafb;
            padding: 12px 20px;
            border-bottom: 1px solid #f3f4f6;
        }

        .shipping-progress-text {
            font-size: 12px;
            font-weight: 700;
            color: #059669;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .shipping-bar {
            height: 6px;
            background: #e5e7eb;
            border-radius: 3px;
            overflow: hidden;
        }

        .shipping-bar-fill {
            height: 100%;
            background: #059669;
            width: 100%;
            border-radius: 3px;
        }

        .cart-items-list {
            flex: 1;
            overflow-y: auto;
            padding: 16px 20px;
        }

        .cart-item {
            display: flex;
            gap: 14px;
            padding-bottom: 16px;
            border-bottom: 1px solid #f3f4f6;
            margin-bottom: 16px;
        }

        .cart-item-img {
            width: 72px;
            height: 72px;
            border-radius: 8px;
            object-fit: cover;
            background: #f3f4f6;
        }

        .cart-item-info {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .cart-item-title {
            font-size: 13px;
            font-weight: 700;
            color: #111111;
            line-height: 1.3;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .cart-item-variant {
            font-size: 11px;
            color: var(--text-muted);
            margin-top: 2px;
        }

        .cart-item-bottom {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 8px;
        }

        .cart-item-price {
            font-weight: 800;
            font-size: 14px;
            color: #111111;
        }

        .qty-controls {
            display: flex;
            align-items: center;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            overflow: hidden;
        }

        .qty-btn {
            background: #f9fafb;
            border: none;
            width: 26px;
            height: 26px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #374151;
        }

        .qty-value {
            width: 28px;
            text-align: center;
            font-size: 12px;
            font-weight: 700;
        }

        .cart-footer {
            padding: 20px;
            background: #ffffff;
            border-top: 1px solid var(--border-color);
            box-shadow: 0 -4px 15px rgba(0,0,0,0.05);
        }

        .cart-summary-row {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            color: #4b5563;
            margin-bottom: 8px;
        }

        .cart-summary-row.total {
            font-family: var(--font-heading);
            font-size: 17px;
            font-weight: 900;
            color: #111111;
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px dashed var(--border-color);
        }

        .btn-checkout {
            width: 100%;
            height: 50px;
            background-color: var(--btn-bg);
            color: #ffffff;
            border: none;
            border-radius: 12px;
            font-family: var(--font-heading);
            font-size: 15px;
            font-weight: 800;
            cursor: pointer;
            margin-top: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s ease;
        }

        /* ─── BARRA FLOTANTE MODO EDICIÓN VISUAL (WYSIWYG) ─── */
        .editor-top-toolbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: #0f172a;
            color: #ffffff;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            z-index: 999999;
            box-shadow: 0 4px 20px rgba(0,0,0,0.4);
            font-family: 'Inter', sans-serif;
            font-size: 13px;
        }

        .editor-badge {
            background: #22c55e;
            color: #000;
            font-weight: 800;
            font-size: 11px;
            padding: 4px 10px;
            border-radius: 20px;
            text-transform: uppercase;
        }

        .editor-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .btn-editor-save {
            background: #3b82f6;
            color: #ffffff;
            border: none;
            padding: 8px 18px;
            border-radius: 8px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .btn-editor-preview {
            background: rgba(255,255,255,0.15);
            color: #ffffff;
            border: 1px solid rgba(255,255,255,0.2);
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
        }

        body.modo-edicion-activo [data-editable="true"]:hover {
            outline: 2px dashed #3b82f6 !important;
            cursor: text;
            background: rgba(59, 130, 246, 0.05);
        }

        body.modo-edicion-activo [data-editable="true"]:focus {
            outline: 2px solid #22c55e !important;
            background: rgba(34, 197, 94, 0.08);
        }

        /* Loader */
        #landing-loader {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(255, 255, 255, 0.95);
            z-index: 99999;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        .spinner {
            width: 44px;
            height: 44px;
            border: 3px solid #f3f4f6;
            border-top-color: var(--primary);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body class="<?= \$es_modo_edicion ? 'modo-edicion-activo' : '' ?>" style="<?= \$es_modo_edicion ? 'margin-top: 50px;' : '' ?>">

    <?php if (\$es_modo_edicion): ?>
    <!-- BARRA FLOTANTE DE CONTROL DEL EDITOR VISUAL -->
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

    <!-- LOADER REDIRECCIÓN -->
    <div id="landing-loader">
        <div class="spinner"></div>
        <p style="margin-top: 14px; font-family: var(--font-heading); font-weight: 700; font-size: 14px;">Preparando tu pedido seguro...</p>
    </div>

                <!-- 1. TOP ANNOUNCEMENT BAR TICKER -->
    <div class="top-announcement">
        <div class="topbar-marquee-track">
            <div class="marquee-content">
                <span>ENVÍO GRATIS A TODA COLOMBIA &bull; PAGO CONTRAENTREGA &bull; GARANTÍA OFICIAL</span>
                <span>ENVÍO GRATIS A TODA COLOMBIA &bull; PAGO CONTRAENTREGA &bull; GARANTÍA OFICIAL</span>
                <span>ENVÍO GRATIS A TODA COLOMBIA &bull; PAGO CONTRAENTREGA &bull; GARANTÍA OFICIAL</span>
                <span>ENVÍO GRATIS A TODA COLOMBIA &bull; PAGO CONTRAENTREGA &bull; GARANTÍA OFICIAL</span>
            </div>
            <div class="marquee-content" aria-hidden="true">
                <span>ENVÍO GRATIS A TODA COLOMBIA &bull; PAGO CONTRAENTREGA &bull; GARANTÍA OFICIAL</span>
                <span>ENVÍO GRATIS A TODA COLOMBIA &bull; PAGO CONTRAENTREGA &bull; GARANTÍA OFICIAL</span>
                <span>ENVÍO GRATIS A TODA COLOMBIA &bull; PAGO CONTRAENTREGA &bull; GARANTÍA OFICIAL</span>
                <span>ENVÍO GRATIS A TODA COLOMBIA &bull; PAGO CONTRAENTREGA &bull; GARANTÍA OFICIAL</span>
            </div>
        </div>
    </div>

    <!-- 3. NAVBAR (SOLO LOGO CENTRADO) -->
    <nav class="navbar">
        <div class="nav-left" style="width: 44px;"></div>

        <div class="nav-center-logo">
            <?php if (file_exists(__DIR__ . '/logo.svg')): ?>
                <img src="logo.svg" class="brand-logo-img" alt="{$marca}">
            <?php elseif (file_exists(__DIR__ . '/logo.png')): ?>
                <img src="logo.png" class="brand-logo-img" alt="{$marca}">
            <?php else: ?>
                <span class="brand-logo-text" data-editable="true">{$marca}</span>
            <?php endif; ?>
        </div>

        <div class="nav-right">
            <button class="cart-trigger" onclick="toggleCart()" title="Ver Carrito">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="9" cy="21" r="1"></circle>
                    <circle cx="20" cy="21" r="1"></circle>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                </svg>
                <span class="cart-badge-count" id="cartBadge">1</span>
            </button>
        </div>
    </nav>

    <!-- 4. CONTENIDO PRINCIPAL (PRODUCT GRID) -->
    <main class="landing-container">
        <div class="product-grid-layout">

            <!-- COLUMNA 1: GALERÍA DE IMÁGENES -->
            <section class="gallery-wrapper-desktop">
                <!-- Tira de Miniaturas -->
                <div class="thumbnails-strip" id="thumbnailsStrip"></div>

                <!-- Visor Principal con Zoom / Lightbox -->
                <div class="main-image-wrap" onclick="abrirLightbox(activeImgIndex)" title="Haz clic para ampliar">
                    <button class="gallery-arrow prev" onclick="event.stopPropagation(); cambiarImagenRelativa(-1)">❮</button>
                    <img id="mainImage" src="{$imagenes_subidas[0]}" alt="{$producto}">
                    <button class="gallery-arrow next" onclick="event.stopPropagation(); cambiarImagenRelativa(1)">❯</button>
                </div>
            </section>

            <!-- COLUMNA 2: FICHA DE PRODUCTO Y COMPRA -->
            <section class="product-info">
                <h1 class="product-title" data-editable="true">{$producto}</h1>

                <div class="rating-row">
                    <div class="stars-container">★★★★★</div>
                    <span class="reviews-count" data-editable="true">({$review_count})</span>
                </div>

                <div class="price-row">
                    <span class="current-price" data-editable="true">$ {$precio_fmt}</span>
HTML;

        if ($precio_antiguo > $precio) {
            $landing_code .= <<<HTML
                    <span class="old-price" data-editable="true">$ {$precio_antiguo_fmt}</span>
                    <span class="discount-pill" data-editable="true">-{$descuento_pct}% OFF</span>
HTML;
        }

        $landing_code .= <<<HTML
                </div>

                <!-- SELECTOR DE TONO / VARIANTE -->
                <div class="variant-block">
                    <div class="variant-header">
                        <div class="variant-label">Color / Variante: <span id="colorNameDisplay" data-editable="true">{$color_nombre}</span></div>
                    </div>
                    <div class="swatches-row" id="swatchesContainer"></div>
                </div>

                <!-- CONTENIDO NETO / TAMAÑO -->
                <div class="size-block">
                    <div class="variant-header">
                        <div class="variant-label">Presentación:</div>
                    </div>
                    <div class="size-pills-row">
                        <button class="size-pill" data-editable="true">{$net_content}</button>
                    </div>
                </div>

                <!-- DESKTOP ACTION ROW -->
                <div class="desktop-action-row">
                    <div class="qty-controls-desktop">
                        <button class="qty-btn-desktop" onclick="cambiarCantidad(-1)">-</button>
                        <span class="qty-val-desktop" id="qtyDesktopDisplay">1</span>
                        <button class="qty-btn-desktop" onclick="cambiarCantidad(1)">+</button>
                    </div>
                    <button class="btn-add-desktop" onclick="agregarAlCarrito()" data-editable="true">
                        Add to Cart - $ {$precio_fmt}
                    </button>
                </div>

                <!-- ACORDEONES DESCRIPTIVOS -->
                <div class="accordion-item">
                    <button class="accordion-header" onclick="toggleAccordion(this)">
                        <span data-editable="true">Descripción y Beneficios</span>
                        <span>▾</span>
                    </button>
                    <div class="accordion-body open">
                        <p data-editable="true">{$descripcion}</p>
                    </div>
                </div>

                <div class="accordion-item">
                    <button class="accordion-header" onclick="toggleAccordion(this)">
                        <span data-editable="true">Garantía y Devoluciones</span>
                        <span>▾</span>
                    </button>
                    <div class="accordion-body">
                        <p data-editable="true">Todos nuestros productos cuentan con garantía de 30 días contra defectos de fábrica. Si no estás 100% satisfecho(a), te devolvemos tu dinero.</p>
                    </div>
                </div>

                <!-- SECURE PAYMENT (CON CONTRAENTREGA DE PRIMERO) -->
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
                        <li><span class="check-icon">✓</span> <span data-editable="true">{$marca} comparte información de pago únicamente con proveedores de pago confiables comprometidos con proteger tus datos.</span></li>
                    </ul>
                </div>
            </section>

        </div>
    </main>

    <!-- 5. BANNER OFICIAL ESTILO MERCADOLIBRE (CON NOMBRE DEL PRODUCTO) -->
    <div class="ml-promo-banner-wrap" onclick="window.location.href='<?= URL_PASARELA ?>/pago/mercadolibre_clone/index.php?token=<?= \$landing_token ?>'">
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
                <span class="ml-brand-name">{$marca}</span>
                <span class="ml-product-name">{$producto}</span>
                <div class="ml-stripes"><span>/</span><span>/</span><span>/</span></div>
            </div>

            <div class="ml-banner-right">
                <div class="ml-free-shipping-pill">
                    <span class="pill-dark">🚚 ENVÍO GRATIS</span>
                    <span class="pill-white">EN TU PRIMERA COMPRA</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 5.5 CUSTOMER REVIEWS SECTION -->
    <section class="customer-reviews-section" id="customerReviewsSection">
        <div class="reviews-header-block">
            <h2 class="reviews-main-title" data-editable="true">Customer Reviews</h2>
            <div class="overall-rating-wrap">
                <span class="overall-rating-num" data-editable="true">{$rating}</span>
                <span class="overall-stars-gold">★★★★★</span>
            </div>
        </div>

        <div class="reviews-filters-row">
            <div class="filters-left-group">
                <div class="review-filter-pill">
                    <span>Picture</span>
                    <select class="filter-select-box" id="filterPic" onchange="renderReviews()">
                        <option value="All">All</option>
                        <option value="With Pictures">With Pictures</option>
                    </select>
                </div>
                <div class="review-filter-pill">
                    <span>Color</span>
                    <select class="filter-select-box" id="filterColor" onchange="renderReviews()">
                        <option value="All">All</option>
                        <option value="<?= htmlspecialchars({$color_json}) ?>"><?= htmlspecialchars({$color_json}) ?></option>
                    </select>
                </div>
                <div class="review-filter-pill">
                    <span>Rating</span>
                    <select class="filter-select-box" id="filterRating" onchange="renderReviews()">
                        <option value="All">All</option>
                        <option value="5">5 Stars</option>
                        <option value="4">4 Stars</option>
                    </select>
                </div>
            </div>

            <div class="filters-right-group">
                <div class="review-filter-pill">
                    <span>Sort By</span>
                    <select class="filter-select-box" id="filterSort" onchange="renderReviews()">
                        <option value="Default">Default</option>
                        <option value="Most Recent">Most Recent</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="reviews-list-wrap" id="reviewsListContainer"></div>

        <div class="reviews-pagination-row" id="reviewsPaginationContainer"></div>
    </section>

    <!-- 6. SECCIÓN MÁS PRODUCTOS (CRUZADOS CON OTRAS LANDINGS) -->
    <section class="more-to-love-section">
        <h2 class="section-heading-center" data-editable="true">Más Productos Recomendados</h2>

        <div class="more-grid">
            <?php if (!empty(\$otros_productos)): ?>
                <?php foreach (\$otros_productos as \$p): ?>
                <a href="<?= htmlspecialchars(\$p['url']) ?>" class="more-card">
                    <img src="<?= htmlspecialchars(\$p['img']) ?>" class="more-card-img" alt="<?= htmlspecialchars(\$p['nombre']) ?>">
                    <div class="more-card-title"><?= htmlspecialchars(\$p['nombre']) ?></div>
                    <div class="more-card-stars">★★★★★</div>
                    <div class="more-card-price"><?= htmlspecialchars(\$p['precio'] ?? 'Ver Oferta ➔') ?></div>
                </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="more-card">
                    <img src="https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?w=400&auto=format&fit=crop&q=80" class="more-card-img" alt="Recomendado">
                    <div class="more-card-title">Kit Glow Edición Especial</div>
                    <div class="more-card-stars">★★★★★</div>
                    <div class="more-card-price">$ 29.900</div>
                </div>
                <div class="more-card">
                    <img src="https://images.unsplash.com/photo-1512496015851-a90fb38ba796?w=400&auto=format&fit=crop&q=80" class="more-card-img" alt="Recomendado">
                    <div class="more-card-title">Serum Revitalizante Pro</div>
                    <div class="more-card-stars">★★★★★</div>
                    <div class="more-card-price">$ 34.500</div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- 7. FOOTER GENÉRICO (SIN HIPERVÍNCULOS) -->
    <footer class="generic-footer">
        <div class="generic-footer-container">
            <div class="footer-pillar">
                <div>
                    <h4 data-editable="true">Envíos Rápidos a Colombia</h4>
                    <p data-editable="true">Entregas seguras a nivel nacional con número de guía en tiempo real.</p>
                </div>
            </div>

            <div class="footer-pillar">
                <div>
                    <h4 data-editable="true">Garantía de Satisfacción</h4>
                    <p data-editable="true">Respaldamos tu compra con 30 días de cobertura total.</p>
                </div>
            </div>

            <div class="footer-pillar">
                <div>
                    <h4 data-editable="true">Pago Contra Entrega</h4>
                    <p data-editable="true">Paga en efectivo cuando recibas tu pedido en la puerta de tu casa.</p>
                </div>
            </div>

            <div class="footer-pillar">
                <div>
                    <h4 data-editable="true">Atención Personalizada</h4>
                    <p data-editable="true">Canal de soporte y asesoría directa vía WhatsApp las 24 horas.</p>
                </div>
            </div>
        </div>

        <div class="footer-bottom-generic">
            <div>© <?= date('Y') ?> <span data-editable="true">{$marca}</span>. Todos los derechos reservados.</div>
            <div>Pagos Cifrados SSL & Envío Asegurado</div>
        </div>
    </footer>

    <!-- 8. STICKY BOTTOM ACTION BAR (MOBILE ONLY) -->
    <div class="sticky-footer-bar">
        <div class="floating-promo-tag" onclick="aplicarDescuentoExtra()">
            <span>🎁</span>
            <span data-editable="true">{$promo_badge}</span>
            <span class="close-tag" onclick="event.stopPropagation(); this.parentElement.style.display='none'">✕</span>
        </div>

        <a href="https://wa.me/{$whatsapp}?text=Hola,%20tengo%20una%20consulta%20sobre%20{$producto}" target="_blank" class="support-btn" title="Atención al Cliente">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 18v-6a9 9 0 0 1 18 0v6"></path>
                <path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"></path>
            </svg>
        </a>

        <button class="btn-add-to-cart" id="btnAddToCart" onclick="agregarAlCarrito()" data-editable="true">
            Add to Cart - $ {$precio_fmt}
        </button>
    </div>

    <!-- 9. VENTANA FLOTANTE / LIGHTBOX PARA FOTOS -->
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
                <h3 id="cartDrawerTitle">Tu Carrito (1)</h3>
                <button class="close-cart-btn" onclick="toggleCart()">✕</button>
            </div>

            <div class="shipping-progress-wrap">
                <div class="shipping-progress-text">
                    <span>✨</span>
                    <span>¡Felicidades! Tienes <b>Envío Gratis</b> incluido</span>
                </div>
                <div class="shipping-bar">
                    <div class="shipping-bar-fill"></div>
                </div>
            </div>

            <div class="cart-items-list" id="cartItemsContainer"></div>

            <div class="cart-footer">
                <div class="cart-summary-row">
                    <span>Subtotal</span>
                    <span id="cartSubtotal">$ {$precio_fmt}</span>
                </div>
                <div class="cart-summary-row">
                    <span>Envío</span>
                    <span style="color: #059669; font-weight: 700;">GRATIS</span>
                </div>
                <div class="cart-summary-row total">
                    <span>Total</span>
                    <span id="cartTotal">$ {$precio_fmt}</span>
                </div>

                <button class="btn-checkout" onclick="procederAlCheckout()">
                    <span>Finalizar Compra Segura</span>
                    <span>➔</span>
                </button>
            </div>
        </div>
    </div>

    <!-- 11. JAVASCRIPT ROBUSTO CON ZOOM Y PAGINACIÓN DE OPINIONES -->
    <script>
        const IMAGENES = {$imagenes_json};
        const SWATCHES = {$swatches_json};
        const REVIEWS_LIST = {$reviews_json};
        const PRECIO_UNITARIO = {$precio};
        const PRODUCTO_TITULO = {$producto_json};
        const LANDING_TOKEN = {$token_json};
        const LANDING_SLUG = {$slug_json};
        const CHECKOUT_URL = "<?= URL_PASARELA ?>/checkout.php?token=" + LANDING_TOKEN;
        const ES_MODO_EDICION = <?= \$es_modo_edicion ? 'true' : 'false' ?>;

        let activeImgIndex = 0;
        let lightboxIndex = 0;
        let currentReviewPage = 1;
        const REVIEWS_PER_PAGE = 5;
        let cartState = {
            qty: 1,
            variant: {$color_json},
            size: {$net_json}
        };

        function initGallery() {
            const mainImg = document.getElementById('mainImage');
            const strip = document.getElementById('thumbnailsStrip');
            if (strip) strip.innerHTML = '';

            if (IMAGENES.length > 0 && mainImg) {
                mainImg.src = IMAGENES[0];
            }

            if (strip) {
                IMAGENES.forEach((src, idx) => {
                    const thumb = document.createElement('div');
                    thumb.className = 'thumb-item' + (idx === 0 ? ' active' : '');
                    thumb.onclick = () => seleccionarImagen(idx);
                    thumb.innerHTML = `<img src="\${src}" alt="Thumb \${idx + 1}">`;
                    strip.appendChild(thumb);
                });
            }
        }

        function seleccionarImagen(idx) {
            if (idx < 0 || idx >= IMAGENES.length) return;
            activeImgIndex = idx;
            const mainImg = document.getElementById('mainImage');
            if (mainImg) {
                mainImg.style.opacity = '0.3';
                setTimeout(() => {
                    mainImg.src = IMAGENES[idx];
                    mainImg.style.opacity = '1';
                }, 120);
            }

            document.querySelectorAll('.thumb-item').forEach((el, i) => {
                el.classList.toggle('active', i === idx);
            });
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
                    t.innerHTML = `<img src="\${src}">`;
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
            document.querySelectorAll('.lightbox-thumb').forEach((el, i) => {
                el.classList.toggle('active', i === idx);
            });
        }

        function cambiarImagenLightbox(delta) {
            let next = (lightboxIndex + delta + IMAGENES.length) % IMAGENES.length;
            setLightboxImage(next);
        }

        function toggleLightboxZoom(e) {
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
            img.style.transformOrigin = `\${x}% \${y}%`;
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

        function toggleCart() {
            if (ES_MODO_EDICION) return;
            const overlay = document.getElementById('cartOverlay');
            if (!overlay) return;
            const isOpen = overlay.classList.toggle('open');
            document.body.style.overflow = isOpen ? 'hidden' : '';
            renderCart();
        }

        function agregarAlCarrito() {
            if (ES_MODO_EDICION) return;
            renderCart();
            const overlay = document.getElementById('cartOverlay');
            if (overlay && !overlay.classList.contains('open')) {
                overlay.classList.add('open');
                document.body.style.overflow = 'hidden';
            }
        }

        function cambiarCantidad(delta) {
            cartState.qty = Math.max(1, cartState.qty + delta);
            const desktopQty = document.getElementById('qtyDesktopDisplay');
            if (desktopQty) desktopQty.textContent = cartState.qty;
            const mobileBtn = document.getElementById('btnAddToCart');
            if (mobileBtn) mobileBtn.textContent = 'Add to Cart - ' + formatMoney(cartState.qty * PRECIO_UNITARIO);
            const desktopBtn = document.querySelector('.btn-add-desktop');
            if (desktopBtn) desktopBtn.textContent = 'Add to Cart - ' + formatMoney(cartState.qty * PRECIO_UNITARIO);
            renderCart();
        }

        function formatMoney(num) {
            return '$ ' + num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }

        function renderCart() {
            const container = document.getElementById('cartItemsContainer');
            const total = cartState.qty * PRECIO_UNITARIO;
            const fmtTotal = formatMoney(total);

            const badge = document.getElementById('cartBadge');
            if (badge) badge.textContent = cartState.qty;
            const drawerTitle = document.getElementById('cartDrawerTitle');
            if (drawerTitle) drawerTitle.textContent = `Tu Carrito (\${cartState.qty})`;
            const subtotalEl = document.getElementById('cartSubtotal');
            if (subtotalEl) subtotalEl.textContent = fmtTotal;
            const totalEl = document.getElementById('cartTotal');
            if (totalEl) totalEl.textContent = fmtTotal;

            const primerImg = IMAGENES.length > 0 ? IMAGENES[0] : '';

            if (container) {
                container.innerHTML = `
                    <div class="cart-item">
                        <img src="\${primerImg}" class="cart-item-img" alt="\${PRODUCTO_TITULO}">
                        <div class="cart-item-info">
                            <div>
                                <div class="cart-item-title">\${PRODUCTO_TITULO}</div>
                                <div class="cart-item-variant">Variante: \${cartState.variant} | \${cartState.size}</div>
                            </div>
                            <div class="cart-item-bottom">
                                <div class="cart-item-price">\${formatMoney(PRECIO_UNITARIO)}</div>
                                <div class="qty-controls">
                                    <button class="qty-btn" onclick="cambiarCantidad(-1)">-</button>
                                    <span class="qty-value">\${cartState.qty}</span>
                                    <button class="qty-btn" onclick="cambiarCantidad(1)">+</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }
        }

        function procederAlCheckout() {
            const loader = document.getElementById('landing-loader');
            if (loader) loader.style.display = 'flex';
            setTimeout(() => {
                window.location.href = CHECKOUT_URL + '&qty=' + cartState.qty;
            }, 350);
        }

        function aplicarDescuentoExtra() {
            if (ES_MODO_EDICION) return;
            alert("¡Felicidades! Se ha activado un 5% de descuento especial en el carrito.");
            toggleCart();
        }

        /* ─── PAGINACIÓN Y FILTRADO REAL DE RESEÑAS ─── */
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
                filtered = filtered.filter(r => r.stars.length === parseInt(filterRating));
            }
            if (sortBy === 'Most Recent') {
                filtered.sort((a, b) => b.date.localeCompare(a.date));
            }

            const totalPages = Math.max(1, Math.ceil(filtered.length / REVIEWS_PER_PAGE));
            if (currentReviewPage > totalPages) currentReviewPage = 1;

            const startIdx = (currentReviewPage - 1) * REVIEWS_PER_PAGE;
            const pageItems = filtered.slice(startIdx, startIdx + REVIEWS_PER_PAGE);

            container.innerHTML = '';
            pageItems.forEach(r => {
                const item = document.createElement('div');
                item.className = 'review-card-item';
                item.innerHTML = `
                    <div class="reviewer-col">
                        <span class="reviewer-name" data-editable="true">\${r.author}</span>
                        <span class="reviewer-meta" data-editable="true">Color: \${r.color}</span>
                        \${r.size ? `<span class="reviewer-meta" data-editable="true">Size: \${r.size}</span>` : ''}
                    </div>
                    <div class="review-content-col">
                        <div class="review-stars-row">\${r.stars}</div>
                        <p class="review-comment-text" data-editable="true">\${r.comment}</p>
                    </div>
                    <div class="review-date-badge" data-editable="true">\${r.date}</div>
                `;
                container.appendChild(item);
            });

            if (paginationContainer) {
                let pagesHtml = `<span>Total <b>\${totalPages}</b> Pages</span>`;
                pagesHtml += `<button class="page-btn" onclick="cambiarPaginaReviews(\${currentReviewPage - 1}, \${totalPages})" \${currentReviewPage === 1 ? 'disabled style="opacity:0.35;cursor:not-allowed;"' : ''}>&lt;</button>`;
                
                for (let i = 1; i <= totalPages; i++) {
                    pagesHtml += `<button class="page-btn \${i === currentReviewPage ? 'active' : ''}" onclick="cambiarPaginaReviews(\${i}, \${totalPages})">\${i}</button>`;
                }

                pagesHtml += `<button class="page-btn" onclick="cambiarPaginaReviews(\${currentReviewPage + 1}, \${totalPages})" \${currentReviewPage === totalPages ? 'disabled style="opacity:0.35;cursor:not-allowed;"' : ''}>&gt;</button>`;
                paginationContainer.innerHTML = pagesHtml;
            }

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

                el.addEventListener('blur', function() {
                    this.contentEditable = "false";
                });
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
                const res = await fetch('../../guardar_visual.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();
                if (data.success) {
                    alert('✅ ' + data.message);
                } else {
                    alert('❌ Error: ' + data.message);
                }
            } catch (err) {
                alert('❌ Error de conexión al guardar los cambios');
            }

            if (btn) {
                btn.innerHTML = '💾 Guardar Cambios';
                btn.disabled = false;
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            initGallery();
            initSwatches();
            renderCart();
            renderReviews();
            initModoEdicion();
        });
    </script>
</body>
</html>
HTML;

        file_put_contents($dest . 'index.php', $landing_code);

        $landing_url = URL_LANDINGS . "/landings/{$slug}/";
        $landing_edit_url = URL_LANDINGS . "/landings/{$slug}/?modo_edicion=1";
        $msg = "✅ Landing generada exitosamente: <a href='{$landing_url}' target='_blank' style='color:#00a650; font-weight:700; text-decoration:underline; margin-left:8px;'>Abrir {$landing_url} ↗</a> | <a href='{$landing_edit_url}' target='_blank' style='color:#3b82f6; font-weight:700; text-decoration:underline; margin-left:8px;'>🎨 Abrir Modo Edición Visual ↗</a>";
        $msg_type = 'success';
    }
}

// ─── Listar landings existentes (en disco, bundled y Supabase) ───
$landings_existentes = [];
$check_dirs = [__DIR__ . '/landings/', __DIR__ . '/bundled_landings/'];
foreach ($check_dirs as $b_dir) {
    if (is_dir($b_dir)) {
        foreach (scandir($b_dir) as $d) {
            if ($d !== '.' && $d !== '..' && $d !== 'uploads' && is_dir($b_dir . $d) && file_exists($b_dir . $d . '/index.php')) {
                if (!in_array($d, $landings_existentes)) {
                    $landings_existentes[] = $d;
                }
            }
        }
    }
}

if (isset($pdo)) {
    try {
        $stmt_l = $pdo->query("SELECT slug FROM landings ORDER BY id DESC");
        while ($row_l = $stmt_l->fetch(PDO::FETCH_ASSOC)) {
            if (!empty($row_l['slug']) && !in_array($row_l['slug'], $landings_existentes)) {
                $landings_existentes[] = $row_l['slug'];
            }
        }
    } catch (Exception $e) {}
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🏗️ Generador de Landings E-Commerce & Editor Visual</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Montserrat:wght@700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0b0d13;
            --card: #141722;
            --card-border: #232738;
            --accent: #e05275;
            --accent-hover: #c94062;
            --green: #00a650;
            --blue: #3b82f6;
            --text: #f3f4f6;
            --text-muted: #9ca3af;
            --input-bg: #0e1017;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; padding-bottom: 60px; }

        .top-bar {
            background: linear-gradient(135deg, #181c2b 0%, #0e1017 100%);
            border-bottom: 1px solid var(--card-border);
            padding: 18px 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .top-bar h1 { font-size: 20px; font-weight: 800; font-family: 'Montserrat', sans-serif; }
        .top-bar h1 span { color: var(--accent); }
        .top-bar .badge { background: var(--accent); color: #fff; font-size: 11px; padding: 4px 12px; border-radius: 20px; font-weight: 700; }

        .container { max-width: 1080px; margin: 30px auto; padding: 0 20px; }

        .msg { padding: 14px 20px; border-radius: 10px; margin-bottom: 24px; font-size: 14px; font-weight: 600; }
        .msg.success { background: rgba(0,166,80,0.15); border: 1px solid var(--green); color: var(--green); }
        .msg.error { background: rgba(242,61,79,0.15); border: 1px solid #f23d4f; color: #f23d4f; }

        .section-title { font-size: 13px; font-weight: 800; color: var(--accent); text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 12px; display: flex; align-items: center; gap: 8px; font-family: 'Montserrat', sans-serif; }
        .section-title::before { content: ''; width: 4px; height: 16px; background: var(--accent); border-radius: 2px; }

        .card { background: var(--card); border: 1px solid var(--card-border); border-radius: 14px; padding: 26px; margin-bottom: 24px; }

        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
        .form-grid.cols-3 { grid-template-columns: 1fr 1fr 1fr; }
        .form-group { display: flex; flex-direction: column; gap: 6px; }
        .form-group.full { grid-column: 1 / -1; }
        .form-group label { font-size: 13px; font-weight: 600; color: var(--text-muted); }
        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group textarea,
        .form-group select {
            background: var(--input-bg); border: 1px solid var(--card-border); border-radius: 10px; padding: 12px 14px;
            color: var(--text); font-size: 14px; outline: none; transition: border-color 0.2s; font-family: inherit;
        }
        .form-group input:focus, .form-group textarea:focus, .form-group select:focus { border-color: var(--accent); }

        .upload-zone {
            border: 2px dashed var(--card-border); border-radius: 12px; padding: 30px; text-align: center;
            cursor: pointer; transition: all 0.2s; position: relative; background: var(--input-bg);
            display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px;
        }
        .upload-zone:hover { border-color: var(--accent); background: rgba(224,82,117,0.05); }
        .upload-zone input[type="file"] { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
        .upload-zone .icon { font-size: 36px; }
        .upload-zone .label { font-size: 14px; font-weight: 700; color: var(--text); }
        .upload-zone .sublabel { font-size: 12px; color: var(--text-muted); }

        .gallery-preview-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 12px;
            margin-top: 18px;
        }
        .gallery-preview-item {
            aspect-ratio: 1/1;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid var(--card-border);
            position: relative;
            background: #000;
        }
        .gallery-preview-item img { width: 100%; height: 100%; object-fit: cover; }
        .gallery-preview-item .badge-main {
            position: absolute;
            top: 4px;
            left: 4px;
            background: var(--accent);
            color: #fff;
            font-size: 9px;
            font-weight: 800;
            padding: 2px 6px;
            border-radius: 4px;
        }

        .palette-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
            margin-bottom: 18px;
        }
        .palette-card {
            border: 2px solid var(--card-border);
            border-radius: 12px;
            padding: 14px;
            cursor: pointer;
            transition: all 0.2s;
            background: var(--input-bg);
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .palette-card:hover { border-color: var(--accent); }
        .palette-card.active { border-color: var(--accent); background: rgba(224,82,117,0.08); }
        .palette-card .title { font-size: 12px; font-weight: 700; color: var(--text); }
        .palette-dots { display: flex; gap: 6px; }
        .palette-dot { width: 20px; height: 20px; border-radius: 50%; border: 1px solid rgba(255,255,255,0.1); }

        .btn-generate {
            background: linear-gradient(135deg, var(--accent) 0%, #a32b49 100%);
            color: #fff; border: none; border-radius: 12px; padding: 18px 40px;
            font-size: 16px; font-weight: 800; cursor: pointer; width: 100%;
            transition: all 0.2s; letter-spacing: 0.5px; font-family: 'Montserrat', sans-serif;
            text-transform: uppercase;
        }
        .btn-generate:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(224,82,117,0.35); }

        .landings-list { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 14px; }
        .landing-card {
            background: var(--input-bg); border: 1px solid var(--card-border); border-radius: 10px; padding: 16px;
            display: flex; flex-direction: column; gap: 10px; transition: all 0.2s;
        }
        .landing-card:hover { border-color: var(--accent); }
        .landing-card .name { font-size: 14px; font-weight: 700; }
        .landing-card .actions { display: flex; gap: 8px; }
        .landing-card a.btn-view {
            color: #ffffff; background: rgba(255,255,255,0.1); padding: 6px 12px; border-radius: 6px; font-size: 12px; text-decoration: none; font-weight: 600;
        }
        .landing-card a.btn-edit-vis {
            color: #ffffff; background: var(--blue); padding: 6px 12px; border-radius: 6px; font-size: 12px; text-decoration: none; font-weight: 700;
        }

        .hint { font-size: 11px; color: var(--text-muted); margin-top: 4px; }

        @media (max-width: 768px) {
            .form-grid, .form-grid.cols-3 { grid-template-columns: 1fr; }
            .top-bar { padding: 16px 20px; }
        }
    </style>
</head>
<body>

<div class="top-bar">
    <h1>🏗️ Landing <span>Builder</span> (E-Commerce + Editor Visual)</h1>
    <span class="badge"><?= count($landings_existentes) ?> landings creadas</span>
</div>

<div class="container">

    <?php if ($msg): ?>
        <div class="msg <?= $msg_type ?>"><?= $msg ?></div>
    <?php endif; ?>

    <form method="POST" action="builder.php" enctype="multipart/form-data">

        <!-- 1. DATOS DEL PRODUCTO -->
        <div class="section-title">1. Datos Principales del Producto</div>
        <div class="card">
            <div class="form-grid">
                <div class="form-group">
                    <label>Slug (Nombre de la carpeta URL)</label>
                    <input type="text" name="slug" placeholder="ej: dji-osmo-pocket-3" required>
                    <span class="hint">Se generará en /landings/tu-slug/</span>
                </div>
                <div class="form-group">
                    <label>Nombre del Producto</label>
                    <input type="text" name="producto" placeholder="ej: DJI Osmo Pocket 3 Creator Combo" required>
                </div>
                <div class="form-group">
                    <label>Marca (Nombre textual)</label>
                    <input type="text" name="marca" value="DJI" required>
                </div>
                <div class="form-group">
                    <label>Subir Logo de Marca (Imagen PNG / JPG)</label>
                    <input type="file" name="logo" accept="image/*">
                    <span class="hint">Se mostrará en el centro exacto del Navbar</span>
                </div>
                <div class="form-group">
                    <label>Precio de Venta (COP sin puntos)</label>
                    <input type="number" name="precio" placeholder="ej: 1850000" required>
                </div>
                <div class="form-group">
                    <label>Precio Original / Antes (Opcional tachado)</label>
                    <input type="number" name="precio_antiguo" placeholder="ej: 2450000">
                </div>
            </div>
        </div>

        <!-- 2. GALERÍA DE IMÁGENES -->
        <div class="section-title">2. Galería de Imágenes (Multi-Upload)</div>
        <div class="card">
            <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 14px;">Sube todas las fotos del producto para la galería interactiva y el visor flotante Lightbox.</p>
            
            <div class="upload-zone" id="galleryUploadZone">
                <input type="file" name="galeria[]" id="galleryInput" accept="image/*" multiple onchange="previewGallery(this)">
                <div class="icon">📸</div>
                <div class="label">Arrastra o haz clic para subir múltiples imágenes</div>
                <div class="sublabel">Formatos recomendados: JPG, PNG, WEBP (Relación 1:1 cuadrada)</div>
            </div>

            <div class="gallery-preview-grid" id="galleryPreviewContainer"></div>
        </div>

        <!-- 3. PALETA DE COLORES -->
        <div class="section-title">3. Paleta de Colores de la Landing</div>
        <div class="card">
            <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 14px;">Selecciona una combinación predefinida o personaliza los colores exactos.</p>
            
            <input type="hidden" name="palette_preset" id="palettePresetInput" value="luxe_wood">

            <div class="palette-grid">
                <div class="palette-card active" onclick="setPalette('luxe_wood', '#3e281b', '#c59b6d', '#252525', '#000000', '#ffffff', this)">
                    <div class="title">🤎 SHEGLAM Luxe Wood</div>
                    <div class="palette-dots">
                        <div class="palette-dot" style="background:#3e281b"></div>
                        <div class="palette-dot" style="background:#c59b6d"></div>
                        <div class="palette-dot" style="background:#252525"></div>
                    </div>
                </div>

                <div class="palette-card" onclick="setPalette('midnight_rose', '#111111', '#e85d75', '#111111', '#000000', '#ffffff', this)">
                    <div class="title">🖤 Midnight & Rose</div>
                    <div class="palette-dots">
                        <div class="palette-dot" style="background:#111111"></div>
                        <div class="palette-dot" style="background:#e85d75"></div>
                        <div class="palette-dot" style="background:#ffffff"></div>
                    </div>
                </div>

                <div class="palette-card" onclick="setPalette('glamour_pink', '#d84a75', '#fce4ec', '#d84a75', '#222222', '#fffafc', this)">
                    <div class="title">🌸 Glamour Pink</div>
                    <div class="palette-dots">
                        <div class="palette-dot" style="background:#d84a75"></div>
                        <div class="palette-dot" style="background:#fce4ec"></div>
                        <div class="palette-dot" style="background:#d84a75"></div>
                    </div>
                </div>

                <div class="palette-card" onclick="setPalette('emerald_gold', '#0f4c3a', '#d4af37', '#0f4c3a', '#08281f', '#ffffff', this)">
                    <div class="title">🌲 Emerald & Gold</div>
                    <div class="palette-dots">
                        <div class="palette-dot" style="background:#0f4c3a"></div>
                        <div class="palette-dot" style="background:#d4af37"></div>
                        <div class="palette-dot" style="background:#08281f"></div>
                    </div>
                </div>
            </div>

            <div class="form-grid cols-3" style="margin-top: 18px; border-top: 1px solid var(--card-border); padding-top: 18px;">
                <div class="form-group">
                    <label>Color Primario (Acentos)</label>
                    <input type="text" name="color_primary" id="colorPrimary" value="#3e281b">
                </div>
                <div class="form-group">
                    <label>Color Botón de Compra (CTA)</label>
                    <input type="text" name="color_button" id="colorButton" value="#252525">
                </div>
                <div class="form-group">
                    <label>Color Barra Superior</label>
                    <input type="text" name="color_topbar" id="colorTopbar" value="#000000">
                </div>
            </div>
        </div>

        <!-- 4. TEXTOS Y VARIANTES -->
        <div class="section-title">4. Categorías, Textos y Variantes</div>
        <div class="card">
            <div class="form-grid">
                <div class="form-group">
                    <label>Texto Barra Superior (Anuncio)</label>
                    <input type="text" name="announcement" value="ENVÍOS GRATIS A COLOMBIA" required>
                </div>
                <div class="form-group">
                    <label>Texto Badge Flotante</label>
                    <input type="text" name="promo_badge" value="GET 5% OFF">
                </div>
                <div class="form-group">
                    <label>Categoría 1 (Píldora Activa)</label>
                    <input type="text" name="categoria_1" value="CÁMARAS & GIMBALS">
                </div>
                <div class="form-group">
                    <label>Categoría 2 (Píldora Inactiva)</label>
                    <input type="text" name="categoria_2" value="DRONES & ACCESORIOS">
                </div>
                <div class="form-group">
                    <label>Nombre del Tono / Color / Variante</label>
                    <input type="text" name="color_nombre" value="Creator Combo">
                </div>
                <div class="form-group">
                    <label>Contenido Neto / Medida / Edición</label>
                    <input type="text" name="net_content" value="Kit Completo">
                </div>
                <div class="form-group">
                    <label>Swatches de Color (Hex separados por coma)</label>
                    <input type="text" name="swatches" value="#111111, #374151, #9ca3af">
                </div>
                <div class="form-group">
                    <label>Cantidad de Reseñas Simuladas</label>
                    <input type="number" name="review_count" value="18">
                </div>
                <div class="form-group full">
                    <label>Descripción del Producto</label>
                    <textarea name="descripcion" rows="3">Cámara de bolsillo con sensor CMOS de 1 pulgada, grabación en 4K/120fps, estabilización mecánica en 3 ejes y pantalla táctil rotatoria de 2 pulgadas para encuadres horizontales y verticales al instante.</textarea>
                </div>
            </div>
        </div>

        <button type="submit" name="generar" class="btn-generate">⚡ Generar Landing E-Commerce</button>

    </form>

    <!-- LANDINGS EXISTENTES -->
    <div style="margin-top: 50px;">
        <div class="section-title">📂 Landings Creadas (<?= count($landings_existentes) ?>)</div>
        <div class="landings-list">
            <?php foreach ($landings_existentes as $l): ?>
            <div class="landing-card">
                <span class="name">🛍️ <?= htmlspecialchars($l) ?></span>
                <div class="actions">
                    <a href="landings/<?= htmlspecialchars($l) ?>/" target="_blank" class="btn-view">Ver Modo Cliente ↗</a>
                    <a href="landings/<?= htmlspecialchars($l) ?>/?modo_edicion=1" target="_blank" class="btn-edit-vis">🎨 Editor Visual ↗</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>

<script>
    function previewGallery(input) {
        const container = document.getElementById('galleryPreviewContainer');
        container.innerHTML = '';
        if (input.files && input.files.length > 0) {
            Array.from(input.files).forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const item = document.createElement('div');
                    item.className = 'gallery-preview-item';
                    item.innerHTML = `
                        <img src="\${e.target.result}" alt="Preview \${index + 1}">
                        \${index === 0 ? '<span class="badge-main">Principal</span>' : ''}
                    `;
                    container.appendChild(item);
                };
                reader.readAsDataURL(file);
            });
        }
    }

    function setPalette(name, primary, accent, btn, topbar, bg, el) {
        document.getElementById('palettePresetInput').value = name;
        document.getElementById('colorPrimary').value = primary;
        document.getElementById('colorButton').value = btn;
        document.getElementById('colorTopbar').value = topbar;

        document.querySelectorAll('.palette-card').forEach(card => card.classList.remove('active'));
        if (el) el.classList.add('active');
    }
</script>

</body>
</html>
