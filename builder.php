<?php
/* ============================================================
   🏗️ LANDING BUILDER - Generador de Landings Reutilizables
   ============================================================ */

require_once __DIR__ . '/config.php';

$base_dir = __DIR__ . '/landings/';
$msg = '';
$msg_type = '';

// ─── PROCESAMIENTO DEL FORMULARIO ───
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generar'])) {

    $slug        = preg_replace('/[^a-z0-9\-]/', '', strtolower(trim($_POST['slug'] ?? '')));
    $titulo      = trim($_POST['titulo'] ?? 'Producto');
    $producto    = trim($_POST['producto'] ?? 'Producto');
    $precio      = (int)($_POST['precio'] ?? 0);
    $video1      = trim($_POST['video1'] ?? '');
    $video2      = trim($_POST['video2'] ?? '');
    $video3      = trim($_POST['video3'] ?? '');
    $gallery_txt = trim($_POST['gallery_title'] ?? 'Mira el producto en acción');

    // Posiciones del botón (PC)
    $btn_top     = trim($_POST['btn_top'] ?? '66.6');
    $btn_left    = trim($_POST['btn_left'] ?? '5.3');
    $btn_width   = trim($_POST['btn_width'] ?? '25.7');
    $btn_height  = trim($_POST['btn_height'] ?? '7.4');

    // Posiciones del botón (Mobile)
    $btn_m_top   = trim($_POST['btn_m_top'] ?? '79.5');
    $btn_m_left  = trim($_POST['btn_m_left'] ?? '-9.1');
    $btn_m_width = trim($_POST['btn_m_width'] ?? '129.3');
    $btn_m_height= trim($_POST['btn_m_height'] ?? '11.7');

    // Posiciones de videos (PC) – 3 posiciones porcentuales
    $v1_top = trim($_POST['v1_top'] ?? '3.9'); $v1_left = trim($_POST['v1_left'] ?? '73.1'); $v1_w = trim($_POST['v1_w'] ?? '25.2'); $v1_h = trim($_POST['v1_h'] ?? '20.4');
    $v2_top = trim($_POST['v2_top'] ?? '30.4'); $v2_left = trim($_POST['v2_left'] ?? '73.2'); $v2_w = trim($_POST['v2_w'] ?? '25.2'); $v2_h = trim($_POST['v2_h'] ?? '20.4');
    $v3_top = trim($_POST['v3_top'] ?? '59.7'); $v3_left = trim($_POST['v3_left'] ?? '73.2'); $v3_w = trim($_POST['v3_w'] ?? '25.2'); $v3_h = trim($_POST['v3_h'] ?? '20.4');

    if (empty($slug)) {
        $msg = 'El slug (nombre de carpeta) es obligatorio.';
        $msg_type = 'error';
    } else {
        $dest = $base_dir . $slug . '/';
        if (!is_dir($dest)) { mkdir($dest, 0777, true); }

        // ─── Subir imágenes ───
        $imagenes = ['desktop', 'desktop2', 'desktop3', 'mobile', 'mobile2', 'mobile3', 'banner', 'bannermobile', 'producto'];
        foreach ($imagenes as $key) {
            if (isset($_FILES[$key]) && $_FILES[$key]['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($_FILES[$key]['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['png', 'jpg', 'jpeg', 'webp', 'gif'])) {
                    move_uploaded_file($_FILES[$key]['tmp_name'], $dest . $key . '.' . $ext);
                    // Si la extensión no es png, renombrar internamente a .png para la plantilla
                    if ($ext !== 'png') {
                        rename($dest . $key . '.' . $ext, $dest . $key . '.png');
                    }
                }
            }
        }
        // ─── Guardar en la Base de Datos (Supabase) ───
        $imagenes_paths = [];
        $imagenes_lista = ['desktop', 'desktop2', 'desktop3', 'mobile', 'mobile2', 'mobile3', 'banner', 'bannermobile', 'producto'];
        foreach ($imagenes_lista as $key) {
            $ext = strtolower(pathinfo($_FILES[$key]['name'] ?? '', PATHINFO_EXTENSION));
            if ($ext) {
                // Guardar la URL absoluta de la imagen para que la Pasarela pueda leerla
                $imagenes_paths[$key] = URL_LANDINGS . "/landings/{$slug}/{$key}.png";
            }
        }

        $config_botones = [
            'pc' => ['top' => $btn_top, 'left' => $btn_left, 'width' => $btn_width, 'height' => $btn_height],
            'mobile' => ['top' => $btn_m_top, 'left' => $btn_m_left, 'width' => $btn_m_width, 'height' => $btn_m_height]
        ];

        // Verificar si existe el slug para hacer UPDATE o INSERT
        $stmt_check = $pdo->prepare("SELECT id, token FROM landings WHERE slug = ?");
        $stmt_check->execute([$slug]);
        $existe = $stmt_check->fetch();

        $landing_token = (!empty($existe) && !empty($existe['token'])) ? $existe['token'] : generarTokenAleatorio(16);

        if ($existe) {
            $stmt = $pdo->prepare("UPDATE landings SET producto = ?, precio = ?, imagenes = ?, config_botones = ?, token = ? WHERE slug = ?");
            $stmt->execute([$producto, $precio, json_encode($imagenes_paths), json_encode($config_botones), $landing_token, $slug]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO landings (slug, producto, precio, imagenes, config_botones, token) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$slug, $producto, $precio, json_encode($imagenes_paths), json_encode($config_botones), $landing_token]);
        }

        // ─── Generar index.php ───
        $producto_encoded = urlencode($producto);
        $producto_html    = htmlspecialchars($producto, ENT_QUOTES);

        $videos_html_pc = '';
        $videos_html_mobile = '';
        $videos_js = '';

        $vids = array_filter([$video1, $video2, $video3]);
        $vidCount = count($vids);
        $vidIndex = 0;
        foreach ($vids as $vid) {
            $vidIndex++;
            $videos_html_pc .= "            <div class=\"video-wrapper\" id=\"wrap{$vidIndex}\">\n                <div id=\"vid{$vidIndex}\" data-id=\"{$vid}\"></div>\n            </div>\n";
            $videos_html_mobile .= "                    <div class=\"gallery-item\">\n                        <iframe loading=\"lazy\" src=\"https://www.youtube.com/embed/{$vid}?controls=1&modestbranding=1&rel=0&playsinline=1\" allow=\"encrypted-media\" allowfullscreen></iframe>\n                    </div>\n";
        }

        // Posiciones de video CSS
        $vid_positions = '';
        $positions = [
            ['top'=>$v1_top, 'left'=>$v1_left, 'w'=>$v1_w, 'h'=>$v1_h],
            ['top'=>$v2_top, 'left'=>$v2_left, 'w'=>$v2_w, 'h'=>$v2_h],
            ['top'=>$v3_top, 'left'=>$v3_left, 'w'=>$v3_w, 'h'=>$v3_h],
        ];
        for ($i = 0; $i < min($vidCount, 3); $i++) {
            $n = $i + 1;
            $vid_positions .= "        #wrap{$n} { top: {$positions[$i]['top']}%; left: {$positions[$i]['left']}%; width: {$positions[$i]['w']}%; height: {$positions[$i]['h']}%; }\n";
        }

        $yt_init_js = '';
        if ($vidCount > 0) {
            $nums = implode("', '", range(1, $vidCount));
            $yt_init_js = "
    <script src=\"https://www.youtube.com/iframe_api\"></script>
    <script>
        const isMobile = window.innerWidth <= 768;
        function onYouTubeIframeAPIReady() {
            ['{$nums}'].forEach(num => {
                const vidDiv = document.getElementById('vid' + num);
                if (!vidDiv) return;
                const vidId = vidDiv.getAttribute('data-id');
                const wrapper = document.getElementById('wrap' + num);
                new YT.Player('vid' + num, {
                    videoId: vidId,
                    playerVars: { autoplay: isMobile ? 1 : 0, mute: 1, loop: 1, playlist: vidId, controls: 1, rel: 0, modestbranding: 1, playsinline: 1 },
                    events: {
                        'onReady': (event) => {
                            if (!isMobile) {
                                wrapper.addEventListener('mouseenter', () => event.target.playVideo());
                                wrapper.addEventListener('mouseleave', () => event.target.pauseVideo());
                            }
                        }
                    }
                });
            });
        }
    </script>";
        }

        $gallery_section = '';
        if ($vidCount > 0) {
            $gallery_section = "
        <!-- GALERÍA MÓVIL -->
        <div class=\"mobile-gallery-container\">
            <h2 class=\"gallery-title\">" . htmlspecialchars($gallery_txt) . "</h2>
            <div class=\"gallery-wrapper\">
                <button class=\"nav-btn prev-btn\">&#10094;</button>
                <div class=\"mobile-video-gallery\" id=\"mobileGallery\">
{$videos_html_mobile}                </div>
                <button class=\"nav-btn next-btn\">&#10095;</button>
            </div>
        </div>";
        }

        $landing_code = <<<HTML
<?php
require_once __DIR__ . '/../../config.php';
\$landing_slug = '{$slug}';
\$landing_token = obtenerOCrearTokenLanding(\$landing_slug, '{$producto_html}', {$precio});
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$producto_html} - Oferta Especial</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800&display=swap" rel="stylesheet">
    <style>
        body, html {
            margin: 0; padding: 0;
            background-color: #ffffff;
            text-align: center;
            overflow-x: hidden;
        }
        .image-container { position: relative; display: block; width: 100%; max-width: 1920px; margin: 0 auto; }
        .hero-section, .info-section, .reviews-section { position: relative; width: 100%; }
        .img-desktop { width: 100%; display: block; }
        .img-mobile { width: 100%; display: none; }
        .banner-custom-margin { margin: -60px 0; position: relative; z-index: 10; }

        .btn-link {
            position: absolute; display: block; z-index: 10; border-radius: 50px;
            animation: pulse 2s infinite;
            top: {$btn_top}%; left: {$btn_left}%; width: {$btn_width}%; height: {$btn_height}%;
        }
        @keyframes pulse {
            0%   { box-shadow: 0 0 0 0 rgba(255, 230, 0, 0.8); }
            70%  { box-shadow: 0 0 0 20px rgba(255, 230, 0, 0); }
            100% { box-shadow: 0 0 0 0 rgba(255, 230, 0, 0); }
        }

        .video-wrapper { position: absolute; z-index: 10; border-radius: 12px; overflow: hidden; background: #000; }
        .video-wrapper iframe { width: 100%; height: 100%; border: none; }
{$vid_positions}
        .mobile-gallery-container { display: none; }

        @media (max-width: 768px) {
            .img-desktop { display: none; }
            .img-mobile { display: block; }
            .btn-link { top: {$btn_m_top}%; left: {$btn_m_left}%; width: {$btn_m_width}%; height: {$btn_m_height}%; }
            .video-wrapper { display: none !important; }
            .banner-custom-margin { margin: -140px 0 !important; }
            .mobile-gallery-container { display: block; background-color: #ffffff; padding: 40px 0 60px 0; }
            .gallery-title { font-family: 'Montserrat', sans-serif; font-size: 26px; margin: 0 0 25px 0; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: #333; position: relative; display: inline-block; }
            .gallery-title::after { content: ''; display: block; width: 50%; height: 4px; background-color: #ffe600; margin: 8px auto 0; border-radius: 2px; }
            .gallery-wrapper { position: relative; width: 100%; display: flex; align-items: center; }
            .nav-btn { position: absolute; background: #ffe600; color: #333; border: none; width: 40px; height: 40px; border-radius: 50%; font-size: 20px; font-weight: bold; cursor: pointer; z-index: 20; box-shadow: 0 2px 5px rgba(0,0,0,0.2); display: flex; align-items: center; justify-content: center; top: 50%; transform: translateY(-50%); }
            .prev-btn { left: 5px; }
            .next-btn { right: 5px; }
            .mobile-video-gallery { display: flex; overflow-x: auto; scroll-behavior: smooth; scroll-snap-type: x mandatory; -webkit-overflow-scrolling: touch; gap: 20px; padding: 0 20px 20px 20px; scrollbar-width: none; width: 100%; }
            .mobile-video-gallery::-webkit-scrollbar { display: none; }
            .gallery-item { flex: 0 0 85%; scroll-snap-align: center; border-radius: 16px; overflow: hidden; aspect-ratio: 16 / 9; background: #000; position: relative; box-shadow: 0 10px 20px rgba(0,0,0,0.5); }
            .gallery-item iframe { position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: none; }
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body>
    <!-- LOADER -->
    <div id="landing-loader" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(255,255,255,0.9); z-index: 9999; flex-direction: column; justify-content: center; align-items: center;">
        <div style="width: 50px; height: 50px; border: 4px solid #ebebeb; border-top-color: #3483fa; border-radius: 50%; animation: spin 1s linear infinite;"></div>
        <div style="margin-top: 20px; font-family: 'Montserrat', sans-serif; font-size: 18px; color: #333; font-weight: 700;">Cargando...</div>
    </div>

    <div class="image-container">
        <!-- SECCIÓN 1: Hero -->
        <div class="hero-section">
            <img src="desktop.png" alt="{$producto_html} Desktop" class="img-desktop">
            <img src="mobile.png" alt="{$producto_html} Mobile" class="img-mobile">
            <a href="#" onclick="cargarLink('<?= URL_PASARELA ?>/checkout.php?token=<?= \$landing_token ?>', event)" class="btn-link" title="Comprar ahora"></a>
        </div>

        <!-- BANNER -->
        <div class="banner-section banner-custom-margin">
            <a href="#" onclick="cargarLink('<?= URL_PASARELA ?>/pago/mercadolibre_clone/index.php?token=<?= \$landing_token ?>', event)" style="display: block; cursor: pointer;">
                <img src="banner.png" alt="Banner Oficial" class="img-desktop" style="width: 100%; height: auto;">
                <img src="bannermobile.png" alt="Banner Oficial Mobile" class="img-mobile" style="width: 100%; height: auto;">
            </a>
        </div>

        <!-- SECCIÓN 2: Info + Videos -->
        <div class="info-section" id="infoContainer">
            <img src="desktop2.png" alt="{$producto_html} Info PC" class="img-desktop">
            <img src="mobile2.png" alt="{$producto_html} Info Mobile" class="img-mobile">
{$videos_html_pc}        </div>
{$gallery_section}

        <!-- SECCIÓN 3: Opiniones -->
        <div class="reviews-section">
            <img src="desktop3.png" alt="Opiniones PC" class="img-desktop">
            <img src="mobile3.png" alt="Opiniones Mobile" class="img-mobile">
        </div>
    </div>
{$yt_init_js}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const gallery = document.getElementById('mobileGallery');
            const prevBtn = document.querySelector('.prev-btn');
            const nextBtn = document.querySelector('.next-btn');
            if (gallery && prevBtn && nextBtn) {
                const scrollAmount = window.innerWidth * 0.85;
                prevBtn.addEventListener('click', () => gallery.scrollBy({ left: -scrollAmount, behavior: 'smooth' }));
                nextBtn.addEventListener('click', () => gallery.scrollBy({ left: scrollAmount, behavior: 'smooth' }));
            }
        });
        function cargarLink(url, event) {
            if (event) event.preventDefault();
            document.getElementById('landing-loader').style.display = 'flex';
            setTimeout(function() { window.location.href = url; }, 1000);
        }
    </script>
</body>
</html>
HTML;

        file_put_contents($dest . 'index.php', $landing_code);

        $msg = "✅ Landing generada exitosamente en <b>landings/{$slug}/</b>";
        $msg_type = 'success';
    }
}

// ─── Listar landings existentes ───
$landings_existentes = [];
if (is_dir($base_dir)) {
    foreach (scandir($base_dir) as $d) {
        if ($d !== '.' && $d !== '..' && is_dir($base_dir . $d) && file_exists($base_dir . $d . '/index.php')) {
            $landings_existentes[] = $d;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🏗️ Landing Builder</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0f1117;
            --card: #1a1d27;
            --card-border: #2a2d3a;
            --accent: #3483fa;
            --accent-hover: #2968c8;
            --green: #00a650;
            --text: #e4e4e7;
            --text-muted: #9ca3af;
            --danger: #f23d4f;
            --input-bg: #111318;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; }

        .top-bar { background: linear-gradient(135deg, #1a1d27 0%, #0f1117 100%); border-bottom: 1px solid var(--card-border); padding: 20px 40px; display: flex; align-items: center; justify-content: space-between; }
        .top-bar h1 { font-size: 22px; font-weight: 700; }
        .top-bar h1 span { color: var(--accent); }
        .top-bar .badge { background: var(--accent); color: #fff; font-size: 11px; padding: 3px 10px; border-radius: 20px; font-weight: 600; }

        .container { max-width: 1100px; margin: 30px auto; padding: 0 20px; }

        .msg { padding: 14px 20px; border-radius: 8px; margin-bottom: 24px; font-size: 14px; font-weight: 600; }
        .msg.success { background: rgba(0,166,80,0.15); border: 1px solid var(--green); color: var(--green); }
        .msg.error { background: rgba(242,61,79,0.15); border: 1px solid var(--danger); color: var(--danger); }

        .section-title { font-size: 14px; font-weight: 700; color: var(--accent); text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 14px; display: flex; align-items: center; gap: 8px; }
        .section-title::before { content: ''; width: 4px; height: 16px; background: var(--accent); border-radius: 2px; }

        .card { background: var(--card); border: 1px solid var(--card-border); border-radius: 12px; padding: 28px; margin-bottom: 24px; }

        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
        .form-grid.cols-3 { grid-template-columns: 1fr 1fr 1fr; }
        .form-grid.cols-4 { grid-template-columns: 1fr 1fr 1fr 1fr; }
        .form-group { display: flex; flex-direction: column; gap: 6px; }
        .form-group.full { grid-column: 1 / -1; }
        .form-group label { font-size: 13px; font-weight: 600; color: var(--text-muted); }
        .form-group input[type="text"],
        .form-group input[type="number"] {
            background: var(--input-bg); border: 1px solid var(--card-border); border-radius: 8px; padding: 12px 14px;
            color: var(--text); font-size: 14px; outline: none; transition: border-color 0.2s;
        }
        .form-group input:focus { border-color: var(--accent); }

        .upload-zone {
            border: 2px dashed var(--card-border); border-radius: 10px; padding: 24px; text-align: center;
            cursor: pointer; transition: all 0.2s; position: relative; overflow: hidden; min-height: 120px;
            display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 6px;
        }
        .upload-zone:hover { border-color: var(--accent); background: rgba(52,131,250,0.05); }
        .upload-zone.has-file { border-color: var(--green); background: rgba(0,166,80,0.05); }
        .upload-zone input[type="file"] { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
        .upload-zone .icon { font-size: 28px; }
        .upload-zone .label { font-size: 13px; font-weight: 600; color: var(--text-muted); }
        .upload-zone .filename { font-size: 12px; color: var(--green); font-weight: 600; display: none; }
        .upload-zone .preview-img { max-height: 80px; max-width: 100%; border-radius: 6px; display: none; margin-top: 6px; }

        .btn-generate {
            background: linear-gradient(135deg, var(--accent) 0%, #2968c8 100%);
            color: #fff; border: none; border-radius: 10px; padding: 16px 40px;
            font-size: 16px; font-weight: 700; cursor: pointer; width: 100%;
            transition: all 0.2s; letter-spacing: 0.5px;
        }
        .btn-generate:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(52,131,250,0.3); }

        .landings-list { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 14px; }
        .landing-card {
            background: var(--input-bg); border: 1px solid var(--card-border); border-radius: 10px; padding: 16px;
            display: flex; align-items: center; justify-content: space-between; transition: all 0.2s;
        }
        .landing-card:hover { border-color: var(--accent); }
        .landing-card .name { font-size: 14px; font-weight: 600; }
        .landing-card a { color: var(--accent); font-size: 13px; text-decoration: none; font-weight: 600; }

        .hint { font-size: 11px; color: var(--text-muted); margin-top: 4px; }

        @media (max-width: 768px) {
            .form-grid, .form-grid.cols-3, .form-grid.cols-4 { grid-template-columns: 1fr; }
            .top-bar { padding: 16px 20px; }
        }
    </style>
</head>
<body>

<div class="top-bar">
    <h1>🏗️ Landing <span>Builder</span></h1>
    <span class="badge"><?= count($landings_existentes) ?> landings</span>
</div>

<div class="container">

    <?php if ($msg): ?>
        <div class="msg <?= $msg_type ?>"><?= $msg ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">

        <!-- DATOS DEL PRODUCTO -->
        <div class="section-title">Datos del producto</div>
        <div class="card">
            <div class="form-grid">
                <div class="form-group">
                    <label>Slug (nombre de carpeta)</label>
                    <input type="text" name="slug" placeholder="ej: iphone-16-pro" required>
                    <span class="hint">Solo letras minúsculas, números y guiones. Se creará en /landings/slug/</span>
                </div>
                <div class="form-group">
                    <label>Nombre del producto</label>
                    <input type="text" name="producto" placeholder="ej: iPhone 16 Pro Max" required>
                </div>
                <div class="form-group">
                    <label>Título de la página</label>
                    <input type="text" name="titulo" placeholder="ej: iPhone 16 Pro - Oferta Especial">
                </div>
                <div class="form-group">
                    <label>Precio (COP sin puntos)</label>
                    <input type="number" name="precio" placeholder="ej: 1500000" required>
                </div>
            </div>
        </div>

        <!-- IMÁGENES -->
        <div class="section-title">Imágenes de la landing</div>
        <div class="card">
            <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 18px;">Sube las imágenes para PC y Móvil. Todas se guardarán con el nombre correcto automáticamente.</p>
            <div class="form-grid cols-3">
                <!-- Desktop -->
                <div class="form-group">
                    <label>🖥️ Hero (desktop.png)</label>
                    <div class="upload-zone" id="zone-desktop">
                        <input type="file" name="desktop" accept="image/*" onchange="previewFile(this, 'zone-desktop')">
                        <div class="icon">📷</div>
                        <div class="label">Imagen principal PC</div>
                        <div class="filename"></div>
                        <img class="preview-img">
                    </div>
                </div>
                <div class="form-group">
                    <label>🖥️ Info (desktop2.png)</label>
                    <div class="upload-zone" id="zone-desktop2">
                        <input type="file" name="desktop2" accept="image/*" onchange="previewFile(this, 'zone-desktop2')">
                        <div class="icon">📷</div>
                        <div class="label">Sección descriptiva PC</div>
                        <div class="filename"></div>
                        <img class="preview-img">
                    </div>
                </div>
                <div class="form-group">
                    <label>🖥️ Opiniones (desktop3.png)</label>
                    <div class="upload-zone" id="zone-desktop3">
                        <input type="file" name="desktop3" accept="image/*" onchange="previewFile(this, 'zone-desktop3')">
                        <div class="icon">📷</div>
                        <div class="label">Opiniones PC</div>
                        <div class="filename"></div>
                        <img class="preview-img">
                    </div>
                </div>

                <!-- Mobile -->
                <div class="form-group">
                    <label>📱 Hero (mobile.png)</label>
                    <div class="upload-zone" id="zone-mobile">
                        <input type="file" name="mobile" accept="image/*" onchange="previewFile(this, 'zone-mobile')">
                        <div class="icon">📷</div>
                        <div class="label">Imagen principal Móvil</div>
                        <div class="filename"></div>
                        <img class="preview-img">
                    </div>
                </div>
                <div class="form-group">
                    <label>📱 Info (mobile2.png)</label>
                    <div class="upload-zone" id="zone-mobile2">
                        <input type="file" name="mobile2" accept="image/*" onchange="previewFile(this, 'zone-mobile2')">
                        <div class="icon">📷</div>
                        <div class="label">Sección descriptiva Móvil</div>
                        <div class="filename"></div>
                        <img class="preview-img">
                    </div>
                </div>
                <div class="form-group">
                    <label>📱 Opiniones (mobile3.png)</label>
                    <div class="upload-zone" id="zone-mobile3">
                        <input type="file" name="mobile3" accept="image/*" onchange="previewFile(this, 'zone-mobile3')">
                        <div class="icon">📷</div>
                        <div class="label">Opiniones Móvil</div>
                        <div class="filename"></div>
                        <img class="preview-img">
                    </div>
                </div>

                <!-- Banner -->
                <div class="form-group">
                    <label>🏷️ Banner ML (banner.png)</label>
                    <div class="upload-zone" id="zone-banner">
                        <input type="file" name="banner" accept="image/*" onchange="previewFile(this, 'zone-banner')">
                        <div class="icon">📷</div>
                        <div class="label">Banner MercadoLibre PC</div>
                        <div class="filename"></div>
                        <img class="preview-img">
                    </div>
                </div>
                <div class="form-group">
                    <label>🏷️ Banner ML Móvil (bannermobile.png)</label>
                    <div class="upload-zone" id="zone-bannermobile">
                        <input type="file" name="bannermobile" accept="image/*" onchange="previewFile(this, 'zone-bannermobile')">
                        <div class="icon">📷</div>
                        <div class="label">Banner MercadoLibre Móvil</div>
                        <div class="filename"></div>
                        <img class="preview-img">
                    </div>
                </div>

                <!-- Producto -->
                <div class="form-group">
                    <label>🛒 Producto (producto.png)</label>
                    <div class="upload-zone" id="zone-producto">
                        <input type="file" name="producto" accept="image/*" onchange="previewFile(this, 'zone-producto')">
                        <div class="icon">📷</div>
                        <div class="label">Imagen del producto</div>
                        <div class="filename"></div>
                        <img class="preview-img">
                    </div>
                </div>
            </div>
        </div>

        <!-- VIDEOS -->
        <div class="section-title">Videos de YouTube</div>
        <div class="card">
            <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 18px;">Ingresa los IDs de los videos de YouTube (ej: <code style="color: var(--accent);">vYZBr_K38W8</code>). Déjalos vacíos si no aplica.</p>
            <div class="form-grid cols-3">
                <div class="form-group">
                    <label>Video 1</label>
                    <input type="text" name="video1" placeholder="ID de YouTube">
                </div>
                <div class="form-group">
                    <label>Video 2</label>
                    <input type="text" name="video2" placeholder="ID de YouTube">
                </div>
                <div class="form-group">
                    <label>Video 3</label>
                    <input type="text" name="video3" placeholder="ID de YouTube">
                </div>
                <div class="form-group full">
                    <label>Título de la galería móvil</label>
                    <input type="text" name="gallery_title" value="Mira el producto en acción">
                </div>
            </div>
        </div>

<style>
.visual-editor {
    display: flex; gap: 20px; margin-top: 20px;
}
.visual-preview {
    flex: 1; border: 2px dashed var(--card-border); border-radius: 8px; position: relative; overflow: hidden; background: #000;
}
.visual-preview img {
    width: 100%; display: block; opacity: 0.5;
}
.visual-overlay {
    position: absolute; border: 2px solid var(--green); background: rgba(0, 166, 80, 0.3);
    cursor: move; z-index: 10;
}
.visual-resizer {
    width: 10px; height: 10px; background: var(--green); position: absolute; right: -5px; bottom: -5px; cursor: se-resize; border-radius: 50%;
}
</style>

        <div class="section-title">Posición del botón "Comprar ahora" (Visual)</div>
        <div class="card">
            <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 18px;">Sube las imágenes Hero arriba, y luego arrastra y redimensiona el cuadro verde para posicionar el botón. Los porcentajes se actualizarán automáticamente.</p>
            
            <div class="visual-editor">
                <div style="flex: 1;">
                    <div style="font-size: 13px; font-weight: 600; margin-bottom: 8px;">🖥️ Escritorio</div>
                    <div class="visual-preview" id="v-preview-desktop">
                        <img id="v-img-desktop" src="data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=" alt="Preview Desktop">
                        <div class="visual-overlay" id="v-overlay-desktop" style="top: 66.6%; left: 5.3%; width: 25.7%; height: 7.4%;">
                            <div class="visual-resizer" onmousedown="initResize(event, 'desktop')"></div>
                        </div>
                    </div>
                </div>
                
                <div style="flex: 1; max-width: 300px;">
                    <div style="font-size: 13px; font-weight: 600; margin-bottom: 8px;">📱 Móvil</div>
                    <div class="visual-preview" id="v-preview-mobile">
                        <img id="v-img-mobile" src="data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=" alt="Preview Mobile">
                        <div class="visual-overlay" id="v-overlay-mobile" style="top: 79.5%; left: 0%; width: 100%; height: 11.7%;">
                            <div class="visual-resizer" onmousedown="initResize(event, 'mobile')"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- HIDDEN INPUTS INSTEAD OF VISIBLE -->
            <div style="display:none;">
                <input type="text" id="btn_top" name="btn_top" value="66.6">
                <input type="text" id="btn_left" name="btn_left" value="5.3">
                <input type="text" id="btn_width" name="btn_width" value="25.7">
                <input type="text" id="btn_height" name="btn_height" value="7.4">
                
                <input type="text" id="btn_m_top" name="btn_m_top" value="79.5">
                <input type="text" id="btn_m_left" name="btn_m_left" value="0">
                <input type="text" id="btn_m_width" name="btn_m_width" value="100">
                <input type="text" id="btn_m_height" name="btn_m_height" value="11.7">
            </div>
        </div>

        <div class="section-title">Posición de los videos sobre desktop2 (%)</div>
        <div class="card">
            <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 18px;">Posición de cada video superpuesto en la imagen desktop2. Solo aplica en PC.</p>
            <div style="font-size: 12px; font-weight: 600; color: var(--accent); margin-bottom: 8px;">Video 1</div>
            <div class="form-grid cols-4">
                <div class="form-group"><label>Top</label><input type="text" name="v1_top" value="3.9"></div>
                <div class="form-group"><label>Left</label><input type="text" name="v1_left" value="73.1"></div>
                <div class="form-group"><label>Width</label><input type="text" name="v1_w" value="25.2"></div>
                <div class="form-group"><label>Height</label><input type="text" name="v1_h" value="20.4"></div>
            </div>
            <div style="font-size: 12px; font-weight: 600; color: var(--accent); margin: 14px 0 8px;">Video 2</div>
            <div class="form-grid cols-4">
                <div class="form-group"><label>Top</label><input type="text" name="v2_top" value="30.4"></div>
                <div class="form-group"><label>Left</label><input type="text" name="v2_left" value="73.2"></div>
                <div class="form-group"><label>Width</label><input type="text" name="v2_w" value="25.2"></div>
                <div class="form-group"><label>Height</label><input type="text" name="v2_h" value="20.4"></div>
            </div>
            <div style="font-size: 12px; font-weight: 600; color: var(--accent); margin: 14px 0 8px;">Video 3</div>
            <div class="form-grid cols-4">
                <div class="form-group"><label>Top</label><input type="text" name="v3_top" value="59.7"></div>
                <div class="form-group"><label>Left</label><input type="text" name="v3_left" value="73.2"></div>
                <div class="form-group"><label>Width</label><input type="text" name="v3_w" value="25.2"></div>
                <div class="form-group"><label>Height</label><input type="text" name="v3_h" value="20.4"></div>
            </div>
        </div>

        <button type="submit" name="generar" class="btn-generate">🚀 Generar Landing</button>

    </form>

    <?php if (!empty($landings_existentes)): ?>
    <div style="margin-top: 40px;">
        <div class="section-title">Landings existentes</div>
        <div class="landings-list">
            <?php foreach ($landings_existentes as $l): ?>
            <div class="landing-card">
                <span class="name">📄 <?= htmlspecialchars($l) ?></span>
                <a href="landings/<?= htmlspecialchars($l) ?>/" target="_blank">Abrir ↗</a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

</div>

<script>
function previewFile(input, zoneId) {
    const zone = document.getElementById(zoneId);
    const filenameEl = zone.querySelector('.filename');
    const previewEl = zone.querySelector('.preview-img');
    
    if (input.files && input.files[0]) {
        const file = input.files[0];
        filenameEl.textContent = '✓ ' + file.name;
        filenameEl.style.display = 'block';
        zone.classList.add('has-file');
        zone.querySelector('.icon').style.display = 'none';
        zone.querySelector('.label').style.display = 'none';

        const reader = new FileReader();
        reader.onload = function(e) {
            previewEl.src = e.target.result;
            previewEl.style.display = 'block';
            
            // Update visual preview if desktop or mobile
            if (zoneId === 'zone-desktop') {
                document.getElementById('v-img-desktop').src = e.target.result;
                document.getElementById('v-img-desktop').style.opacity = '1';
            }
            if (zoneId === 'zone-mobile') {
                document.getElementById('v-img-mobile').src = e.target.result;
                document.getElementById('v-img-mobile').style.opacity = '1';
            }
        };
        reader.readAsDataURL(file);
    }
}

// Visual Drag and Drop Logic
let isDragging = false;
let isResizing = false;
let currentMode = ''; // 'desktop' or 'mobile'
let startX, startY, startLeft, startTop, startWidth, startHeight;

document.querySelectorAll('.visual-overlay').forEach(overlay => {
    overlay.addEventListener('mousedown', function(e) {
        if(e.target.classList.contains('visual-resizer')) return;
        isDragging = true;
        currentMode = this.id.includes('desktop') ? 'desktop' : 'mobile';
        startX = e.clientX;
        startY = e.clientY;
        startLeft = parseFloat(this.style.left) || 0;
        startTop = parseFloat(this.style.top) || 0;
        e.preventDefault();
    });
});

function initResize(e, mode) {
    isResizing = true;
    currentMode = mode;
    startX = e.clientX;
    startY = e.clientY;
    const overlay = document.getElementById('v-overlay-' + mode);
    startWidth = parseFloat(overlay.style.width) || 10;
    startHeight = parseFloat(overlay.style.height) || 10;
    e.stopPropagation();
    e.preventDefault();
}

document.addEventListener('mousemove', function(e) {
    if (!isDragging && !isResizing) return;
    
    const mode = currentMode;
    const previewBox = document.getElementById('v-preview-' + mode);
    const overlay = document.getElementById('v-overlay-' + mode);
    const rect = previewBox.getBoundingClientRect();
    
    const dx = e.clientX - startX;
    const dy = e.clientY - startY;
    
    const dxPercent = (dx / rect.width) * 100;
    const dyPercent = (dy / rect.height) * 100;
    
    if (isDragging) {
        let newLeft = startLeft + dxPercent;
        let newTop = startTop + dyPercent;
        
        // Boundaries
        newLeft = Math.max(0, Math.min(newLeft, 100 - parseFloat(overlay.style.width)));
        newTop = Math.max(0, Math.min(newTop, 100 - parseFloat(overlay.style.height)));
        
        overlay.style.left = newLeft + '%';
        overlay.style.top = newTop + '%';
        
        if(mode === 'desktop') {
            document.getElementById('btn_left').value = newLeft.toFixed(2);
            document.getElementById('btn_top').value = newTop.toFixed(2);
        } else {
            document.getElementById('btn_m_left').value = newLeft.toFixed(2);
            document.getElementById('btn_m_top').value = newTop.toFixed(2);
        }
    } else if (isResizing) {
        let newWidth = startWidth + dxPercent;
        let newHeight = startHeight + dyPercent;
        
        // Boundaries
        newWidth = Math.max(2, Math.min(newWidth, 100 - parseFloat(overlay.style.left)));
        newHeight = Math.max(2, Math.min(newHeight, 100 - parseFloat(overlay.style.top)));
        
        overlay.style.width = newWidth + '%';
        overlay.style.height = newHeight + '%';
        
        if(mode === 'desktop') {
            document.getElementById('btn_width').value = newWidth.toFixed(2);
            document.getElementById('btn_height').value = newHeight.toFixed(2);
        } else {
            document.getElementById('btn_m_width').value = newWidth.toFixed(2);
            document.getElementById('btn_m_height').value = newHeight.toFixed(2);
        }
    }
});

document.addEventListener('mouseup', function() {
    isDragging = false;
    isResizing = false;
});
</script>

</body>
</html>
