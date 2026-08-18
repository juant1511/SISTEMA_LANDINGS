<?php
require_once __DIR__ . '/../../config.php';
$landing_slug  = 'airpods-max-wireless';
$landing_token = obtenerOCrearTokenLanding($landing_slug, "Apple AirPods Max Wireless Hi-Fi | Cancelación Activa de Ruido & Audio Espacial", 489000);
$precio_num    = 489000;
$precio_fmt    = '489.000';
$es_modo_edicion = isset($_GET['modo_edicion']) && $_GET['modo_edicion'] == '1';

// ─── Cargar Productos de Otras Landings con Rutas Relativas Correctas ───
$otros_productos = [];
try {
    if (isset($pdo)) {
        $stmt = $pdo->prepare("SELECT slug, producto, precio, imagenes FROM landings WHERE slug != ? ORDER BY id DESC LIMIT 12");
        $stmt->execute([$landing_slug]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $r) {
            $other_slug = $r['slug'];
            $imgs = is_array($r['imagenes']) ? $r['imagenes'] : (json_decode($r['imagenes'] ?? '{}', true) ?: []);
            $raw_img = $imgs['img_1'] ?? ($imgs['producto'] ?? ($imgs['desktop'] ?? 'img/img_1.jpg'));
            
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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= htmlspecialchars("Apple AirPods Max Wireless Hi-Fi | Cancelación Activa de Ruido & Audio Espacial") ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&family=Inter:wght@400;500;600;700;900&display=swap" rel="stylesheet">
    
    <style>
        @font-face {
            font-family: '__MiddleEast_309aa8';
            src: local('Montserrat'), local('Inter'), local('Segoe UI');
        }

        :root {
            --primary: #0f4c3a;
            --accent: #d4af37;
            --btn-bg: #0f4c3a;
            --topbar-bg: #08281f;
            --body-bg: #ffffff;
            --text-main: #111111;
            --text-muted: #6b7280;
            --border-color: #e5e7eb;
            --star-color: #f59e0b;
            --font-heading: '__MiddleEast_309aa8', 'Montserrat', sans-serif;
            --font-body: '__MiddleEast_309aa8', 'Inter', sans-serif;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; -webkit-tap-highlight-color: transparent; }
        body { font-family: var(--font-body); background-color: var(--body-bg); color: var(--text-main); line-height: 1.4; padding-bottom: 0; overflow-x: hidden; }
        @media (max-width: 991px) { body { padding-bottom: 85px; } }

        .top-announcement { background-color: var(--topbar-bg); color: #ffffff; font-family: var(--font-heading); font-size: 11.5px; font-weight: 800; text-align: center; padding: 10px 16px; letter-spacing: 1.2px; text-transform: uppercase; }

        .category-switcher { display: flex; justify-content: center; gap: 10px; padding: 10px 16px; background-color: #ffffff; border-bottom: 1px solid #f3f4f6; }
        .category-pill { padding: 6px 18px; border-radius: 25px; font-family: var(--font-heading); font-size: 11px; font-weight: 800; text-decoration: none; letter-spacing: 0.5px; transition: all 0.2s ease; text-align: center; }
        .category-pill.active { background-color: var(--primary); color: #ffffff; box-shadow: 0 3px 8px rgba(0, 0, 0, 0.15); }
        .category-pill.inactive { background-color: #ffffff; color: #4b5563; border: 1px solid var(--border-color); }

        /* ─── NAVBAR CON LOGO GRANDE Y CENTRADO ─── */
        .navbar { display: flex; align-items: center; justify-content: space-between; padding: 14px 24px; background-color: #ffffff; border-bottom: 1px solid #f3f4f6; position: sticky; top: 0; z-index: 100; min-height: 75px; }
        .nav-left { width: 50px; display: flex; align-items: center; }
        .nav-center-logo { flex: 1; display: flex; justify-content: center; align-items: center; }
        .brand-logo-img { max-height: 56px; max-width: 220px; object-fit: contain; transition: transform 0.2s ease; }
        .brand-logo-img:hover { transform: scale(1.03); }
        .brand-logo-text { font-family: var(--font-heading); font-size: 26px; font-weight: 900; letter-spacing: 3px; color: #111111; text-transform: uppercase; text-decoration: none; }
        .nav-btn-icon { background: none; border: none; cursor: pointer; color: #111827; display: flex; align-items: center; justify-content: center; padding: 6px; border-radius: 50%; }
        .nav-right { width: 50px; display: flex; justify-content: flex-end; align-items: center; }
        .cart-trigger { position: relative; background: none; border: none; cursor: pointer; color: #111827; padding: 6px; }
        .cart-badge-count { position: absolute; top: -2px; right: -4px; background-color: var(--primary); color: #ffffff; font-size: 10px; font-weight: 800; width: 18px; height: 18px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid #ffffff; }

        @media (min-width: 768px) {
            .brand-logo-img { max-height: 66px; max-width: 260px; }
            .brand-logo-text { font-size: 30px; letter-spacing: 3.5px; }
        }

        .landing-container { max-width: 1280px; margin: 0 auto; padding: 20px 20px 30px 20px; }
        .product-grid-layout { display: flex; flex-direction: column; gap: 20px; }

        /* ─── GALLERY SECTION ─── */
        .gallery-wrapper-desktop { display: flex; flex-direction: column; gap: 12px; }
        .main-image-wrap { order: 1; width: 100%; aspect-ratio: 1 / 1; background-color: #fafafa; border-radius: 16px; overflow: hidden; display: flex; align-items: center; justify-content: center; position: relative; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04); cursor: zoom-in; }
        .main-image-wrap img { width: 100%; height: 100%; object-fit: cover; transition: opacity 0.25s ease, transform 0.3s ease; }
        .main-image-wrap:hover img { transform: scale(1.03); }
        .gallery-arrow { position: absolute; top: 50%; transform: translateY(-50%); width: 38px; height: 38px; background: rgba(255, 255, 255, 0.95); border: 1px solid var(--border-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; z-index: 10; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .gallery-arrow.prev { left: 16px; }
        .gallery-arrow.next { right: 16px; }

        .thumbnails-strip { order: 2; display: flex; gap: 10px; overflow-x: auto; padding-bottom: 6px; scrollbar-width: none; -webkit-overflow-scrolling: touch; }
        .thumbnails-strip::-webkit-scrollbar { display: none; }
        .thumb-item { flex: 0 0 70px; height: 70px; border-radius: 10px; overflow: hidden; border: 2px solid var(--border-color); cursor: pointer; transition: all 0.2s ease; background: #f9fafb; }
        .thumb-item.active { border-color: var(--primary); box-shadow: 0 0 0 1px var(--primary); }
        .thumb-item img { width: 100%; height: 100%; object-fit: cover; }

        .product-info { padding: 0 4px; }
        .product-title { font-family: var(--font-heading); font-size: 21px; font-weight: 800; color: #111111; line-height: 1.35; margin-bottom: 8px; }
        .rating-row { display: flex; align-items: center; gap: 6px; margin-bottom: 14px; }
        .stars-container { display: flex; color: var(--star-color); font-size: 15px; }
        .rating-number { font-size: 13px; font-weight: 700; color: #374151; }
        .reviews-count { font-size: 13px; color: var(--text-muted); }

        .price-row { display: flex; align-items: baseline; gap: 10px; margin-bottom: 20px; }
        .current-price { font-family: var(--font-heading); font-size: 28px; font-weight: 900; color: #111111; }
        .old-price { font-size: 16px; color: #9ca3af; text-decoration: line-through; font-weight: 600; }
        .discount-pill { background-color: #fee2e2; color: #ef4444; font-size: 11px; font-weight: 800; padding: 3px 8px; border-radius: 6px; }

        .variant-block { margin-bottom: 18px; border-top: 1px solid #f3f4f6; padding-top: 14px; }
        .variant-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; font-size: 13px; }
        .variant-label { font-weight: 700; color: #111111; }
        .variant-label span { font-weight: 500; color: #4b5563; }
        .swatches-row { display: flex; gap: 12px; align-items: center; }
        .swatch-circle { width: 34px; height: 34px; border-radius: 50%; cursor: pointer; position: relative; border: 2px solid transparent; box-shadow: inset 0 0 0 1px rgba(0,0,0,0.15); transition: all 0.2s ease; }
        .swatch-circle.active { border-color: #111111; transform: scale(1.1); }

        .size-block { margin-bottom: 22px; }
        .size-pills-row { display: flex; gap: 10px; }
        .size-pill { padding: 9px 20px; border-radius: 8px; font-size: 13px; font-weight: 700; background: #111111; color: #ffffff; border: none; cursor: pointer; }

        .desktop-action-row { display: none; gap: 14px; align-items: center; margin-bottom: 24px; }
        .qty-controls-desktop { display: flex; align-items: center; border: 1px solid var(--border-color); border-radius: 10px; overflow: hidden; height: 50px; background: #ffffff; }
        .qty-btn-desktop { background: #f9fafb; border: none; width: 42px; height: 100%; font-size: 18px; font-weight: 700; cursor: pointer; color: #374151; display: flex; align-items: center; justify-content: center; transition: background 0.2s; }
        .qty-btn-desktop:hover { background: #f3f4f6; }
        .qty-val-desktop { width: 44px; text-align: center; font-size: 15px; font-weight: 700; }
        .btn-add-desktop { flex: 1; height: 50px; background-color: var(--btn-bg); color: #ffffff; border: none; border-radius: 10px; font-family: var(--font-heading); font-size: 14px; font-weight: 800; letter-spacing: 1px; text-transform: uppercase; cursor: pointer; transition: all 0.2s ease; }
        .btn-add-desktop:hover { opacity: 0.92; transform: translateY(-1px); box-shadow: 0 6px 18px rgba(0,0,0,0.18); }

        .accordion-item { border-bottom: 1px solid var(--border-color); }
        .accordion-header { width: 100%; display: flex; justify-content: space-between; align-items: center; padding: 14px 0; background: none; border: none; font-family: var(--font-heading); font-size: 14px; font-weight: 700; color: #111111; cursor: pointer; text-align: left; }
        .accordion-body { display: none; padding-bottom: 14px; font-size: 13px; color: #4b5563; line-height: 1.6; }
        .accordion-body.open { display: block; }

        .secure-trust-box { background: #f0fdf4; border: 1px solid #dcfce7; border-radius: 10px; padding: 14px 18px; margin-top: 22px; }
        .secure-trust-header { display: flex; align-items: center; gap: 8px; font-family: var(--font-heading); font-size: 14px; font-weight: 800; color: #111111; margin-bottom: 10px; }
        .secure-trust-header svg { flex-shrink: 0; }
        .secure-trust-list { list-style: none; display: flex; flex-direction: column; gap: 6px; }
        .secure-trust-list li { display: flex; align-items: flex-start; gap: 8px; font-size: 12.5px; color: #374151; line-height: 1.45; }
        .secure-trust-list .check-icon { color: #10b981; font-weight: 900; font-size: 13px; line-height: 1.3; }

        /* ─── BANNER MERCADOLIBRE ─── */
        .ml-promo-banner-wrap { max-width: 1280px; margin: 25px auto 10px auto; padding: 0 20px; cursor: pointer; }
        .ml-banner-inner { background: #ffe600; border-radius: 50px; padding: 8px 24px; display: flex; align-items: center; justify-content: space-between; gap: 16px; box-shadow: 0 4px 15px rgba(255, 230, 0, 0.35); transition: transform 0.2s, box-shadow 0.2s; overflow: hidden; }
        .ml-banner-inner:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(255, 230, 0, 0.5); }
        .ml-banner-left { display: flex; align-items: center; gap: 10px; flex-shrink: 0; }
        .ml-logo-img { height: 38px; max-width: 140px; object-fit: contain; }
        .ml-banner-divider { width: 1px; height: 28px; background: rgba(0, 0, 0, 0.15); flex-shrink: 0; }
        .ml-banner-center { flex: 1; display: flex; align-items: center; gap: 10px; overflow: hidden; white-space: nowrap; }
        .ml-brand-name { font-family: var(--font-heading); font-weight: 900; font-size: 15px; color: #111111; letter-spacing: 1px; text-transform: uppercase; }
        .ml-product-name { font-family: var(--font-heading); font-weight: 800; font-size: 14px; color: #111111; text-transform: uppercase; overflow: hidden; text-overflow: ellipsis; }
        .ml-stripes { color: #d97706; font-weight: 900; font-size: 16px; letter-spacing: -2px; display: flex; }
        .ml-banner-right { flex-shrink: 0; }
        .ml-free-shipping-pill { display: flex; align-items: center; border-radius: 20px; overflow: hidden; box-shadow: 0 2px 6px rgba(0,0,0,0.15); font-family: var(--font-heading); font-size: 11px; font-weight: 800; }
        .pill-dark { background: #0a1945; color: #ffffff; padding: 8px 14px; display: flex; align-items: center; gap: 4px; }
        .pill-white { background: #ffffff; color: #0a1945; padding: 8px 12px; }

        @media (max-width: 768px) {
            .ml-banner-inner { border-radius: 16px; padding: 10px 14px; flex-wrap: wrap; gap: 8px; }
            .ml-banner-divider { display: none; }
            .ml-free-shipping-pill { font-size: 10px; }
            .pill-dark, .pill-white { padding: 6px 10px; }
        }

        /* ─── CUSTOMER REVIEWS SECTION ─── */
        .customer-reviews-section { max-width: 1280px; margin: 45px auto 30px auto; padding: 0 20px; font-family: var(--font-body); }
        .reviews-header-block { text-align: center; margin-bottom: 20px; }
        .reviews-main-title { font-family: var(--font-heading); font-size: 26px; font-weight: 900; color: #111111; margin-bottom: 8px; letter-spacing: -0.5px; }
        .overall-rating-wrap { display: flex; align-items: center; justify-content: center; gap: 10px; margin-bottom: 24px; }
        .overall-rating-num { font-size: 24px; font-weight: 900; color: #111111; }
        .overall-stars-gold { color: #f59e0b; font-size: 20px; letter-spacing: 2px; }
        .reviews-filters-row { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px; padding-bottom: 16px; border-bottom: 1px solid #f3f4f6; margin-bottom: 10px; }
        .filters-left-group { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
        .filters-right-group { display: flex; align-items: center; gap: 8px; }
        .review-filter-pill { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; color: #374151; font-weight: 600; }
        .filter-select-box { border: 1px solid #d1d5db; border-radius: 6px; padding: 6px 24px 6px 10px; font-size: 12px; color: #111111; background: #ffffff; appearance: none; cursor: pointer; font-weight: 500; }
        .review-card-item { display: grid; grid-template-columns: 220px 1fr auto; gap: 20px; padding: 22px 0; border-bottom: 1px solid #f3f4f6; align-items: start; }
        @media (max-width: 768px) { .review-card-item { grid-template-columns: 1fr; gap: 8px; } }
        .reviewer-col { display: flex; border-right: none; flex-direction: column; gap: 3px; }
        .reviewer-name { font-weight: 800; font-size: 13.5px; color: #111111; }
        .reviewer-meta { font-size: 11.5px; color: #6b7280; }
        .review-content-col { display: flex; flex-direction: column; gap: 6px; }
        .review-stars-row { color: #f59e0b; font-size: 14px; letter-spacing: 1px; }
        .review-comment-text { font-size: 13px; color: #1f2937; line-height: 1.5; font-weight: 600; }
        .review-date-badge { font-size: 11px; color: #9ca3af; white-space: nowrap; text-align: right; }
        @media (max-width: 768px) { .review-date-badge { text-align: left; } }
        .reviews-pagination-row { display: flex; justify-content: flex-end; align-items: center; gap: 8px; margin-top: 24px; font-size: 12px; color: #6b7280; }
        .page-btn { width: 28px; height: 28px; border: 1px solid transparent; background: transparent; border-radius: 50%; cursor: pointer; font-size: 12px; font-weight: 700; color: #374151; display: inline-flex; align-items: center; justify-content: center; transition: all 0.2s; }
        .page-btn.active { background: #111111; color: #ffffff; }
        .page-btn:hover:not(.active) { background: #f3f4f6; }

        /* ─── MÁS PRODUCTOS RECOMENDADOS (CRUZADOS) ─── */
        .more-to-love-section { max-width: 1280px; margin: 30px auto 40px auto; padding: 0 20px; text-align: center; }
        .section-heading-center { font-family: var(--font-heading); font-size: 22px; font-weight: 900; letter-spacing: 1px; color: #111111; margin-bottom: 18px; text-transform: uppercase; }
        .more-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 16px; }
        .more-card { background: #ffffff; border: 1px solid #f3f4f6; border-radius: 12px; overflow: hidden; padding: 10px; text-align: left; text-decoration: none; display: flex; flex-direction: column; transition: all 0.2s ease; }
        .more-card:hover { box-shadow: 0 8px 20px rgba(0,0,0,0.06); transform: translateY(-2px); border-color: var(--accent); }
        .more-card-img { width: 100%; aspect-ratio: 1/1; border-radius: 8px; object-fit: cover; background: #fafafa; }
        .more-card-title { font-size: 13px; font-weight: 700; color: #111111; margin: 8px 0 4px 0; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .more-card-stars { font-size: 12px; color: var(--star-color); margin-bottom: 4px; }
        .more-card-price { font-weight: 900; font-size: 14px; color: #111111; margin-top: auto; }

        .generic-footer { background: #000000; color: #ffffff; padding: 40px 20px 24px 20px; margin-top: 60px; }
        .generic-footer-container { max-width: 1280px; margin: 0 auto; display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 28px; border-bottom: 1px solid #222222; padding-bottom: 30px; }
        .footer-pillar { display: flex; gap: 12px; align-items: flex-start; }
        .footer-pillar h4 { font-family: var(--font-heading); font-size: 13px; font-weight: 800; color: #ffffff; margin-bottom: 4px; text-transform: uppercase; }
        .footer-pillar p { font-size: 12px; color: #9ca3af; line-height: 1.4; }
        .footer-bottom-generic { max-width: 1280px; margin: 20px auto 0 auto; display: flex; justify-content: space-between; align-items: center; font-size: 11px; color: #6b7280; flex-wrap: wrap; gap: 14px; }

        .sticky-footer-bar { position: fixed; bottom: 0; left: 0; right: 0; background: #ffffff; border-top: 1px solid var(--border-color); padding: 10px 16px 14px 16px; display: flex; align-items: center; gap: 12px; z-index: 900; box-shadow: 0 -4px 15px rgba(0,0,0,0.06); max-width: 540px; margin: 0 auto; }
        .support-btn { width: 46px; height: 46px; border-radius: 10px; border: 1px solid var(--border-color); background: #ffffff; display: flex; align-items: center; justify-content: center; color: #111111; text-decoration: none; flex-shrink: 0; }
        .btn-add-to-cart { flex: 1; height: 48px; background-color: var(--btn-bg); color: #ffffff; border: none; border-radius: 12px; font-family: var(--font-heading); font-size: 14px; font-weight: 800; display: flex; align-items: center; justify-content: center; gap: 6px; cursor: pointer; letter-spacing: 0.5px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
        .floating-promo-tag { position: absolute; top: -42px; right: 16px; background: #000000; color: #ffffff; font-size: 11px; font-weight: 800; padding: 8px 14px; border-radius: 20px; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.25); animation: bounceSoft 2.5s infinite ease-in-out; cursor: pointer; }
        @keyframes bounceSoft { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-5px); } }

        @media (min-width: 992px) {
            .product-grid-layout { display: grid; grid-template-columns: 1.15fr 1fr; gap: 50px; align-items: start; }
            .gallery-wrapper-desktop { flex-direction: row; gap: 16px; }
            .thumbnails-strip { order: 1; flex-direction: column; width: 76px; max-height: 520px; overflow-y: auto; overflow-x: hidden; padding-bottom: 0; }
            .thumb-item { flex: 0 0 74px; height: 74px; }
            .main-image-wrap { order: 2; flex: 1; max-width: 520px; }
            .desktop-action-row { display: flex; }
            .sticky-footer-bar { display: none !important; }
            .more-grid { grid-template-columns: repeat(6, 1fr); }
        }

        .lightbox-modal { position: fixed; inset: 0; background: rgba(0, 0, 0, 0.94); z-index: 99999; display: none; align-items: center; justify-content: center; flex-direction: column; backdrop-filter: blur(5px); }
        .lightbox-modal.open { display: flex; }
        .lightbox-close-btn { position: absolute; top: 20px; right: 24px; background: rgba(255, 255, 255, 0.15); border: none; color: #ffffff; width: 44px; height: 44px; border-radius: 50%; font-size: 24px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background 0.2s; z-index: 100; }
        .lightbox-main-view { max-width: 90vw; max-height: 78vh; display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden; }
        .lightbox-main-view img { max-width: 100%; max-height: 78vh; object-fit: contain; border-radius: 8px; box-shadow: 0 10px 40px rgba(0,0,0,0.5); cursor: zoom-in; transition: transform 0.22s ease-out; }
        .lightbox-main-view img.zoomed { transform: scale(2.2); cursor: zoom-out; }
        .lightbox-nav-btn { position: absolute; top: 50%; transform: translateY(-50%); width: 50px; height: 50px; background: rgba(255, 255, 255, 0.2); border: none; border-radius: 50%; color: #ffffff; font-size: 24px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background 0.2s; z-index: 20; }
        .lightbox-nav-btn.prev { left: -70px; }
        .lightbox-nav-btn.next { right: -70px; }
        @media (max-width: 768px) { .lightbox-nav-btn.prev { left: 10px; } .lightbox-nav-btn.next { right: 10px; } }
        .lightbox-thumbs-row { display: flex; gap: 10px; margin-top: 20px; max-width: 90vw; overflow-x: auto; padding: 8px; }
        .lightbox-thumb { width: 54px; height: 54px; border-radius: 6px; overflow: hidden; border: 2px solid transparent; cursor: pointer; opacity: 0.6; transition: all 0.2s; }
        .lightbox-thumb.active { border-color: #ffffff; opacity: 1; transform: scale(1.1); }
        .lightbox-thumb img { width: 100%; height: 100%; object-fit: cover; }

        .cart-overlay { position: fixed; inset: 0; background: rgba(0, 0, 0, 0.6); z-index: 10000; opacity: 0; visibility: hidden; transition: all 0.3s ease; backdrop-filter: blur(2px); }
        .cart-overlay.open { opacity: 1; visibility: visible; }
        .cart-drawer { position: fixed; top: 0; right: -100%; bottom: 0; width: 100%; max-width: 420px; background: #ffffff; z-index: 10001; transition: right 0.35s cubic-bezier(0.16, 1, 0.3, 1); display: flex; flex-direction: column; box-shadow: -5px 0 25px rgba(0,0,0,0.2); }
        .cart-overlay.open .cart-drawer { right: 0; }
        .cart-header { display: flex; align-items: center; justify-content: space-between; padding: 18px 20px; border-bottom: 1px solid var(--border-color); }
        .cart-header h3 { font-family: var(--font-heading); font-size: 16px; font-weight: 800; color: #111111; }
        .close-cart-btn { background: none; border: none; font-size: 22px; cursor: pointer; color: #6b7280; display: flex; align-items: center; justify-content: center; padding: 4px; }
        .shipping-progress-wrap { background: #f9fafb; padding: 12px 20px; border-bottom: 1px solid #f3f4f6; }
        .shipping-progress-text { font-size: 12px; font-weight: 700; color: #059669; margin-bottom: 6px; display: flex; align-items: center; gap: 6px; }
        .shipping-bar { height: 6px; background: #e5e7eb; border-radius: 3px; overflow: hidden; }
        .shipping-bar-fill { height: 100%; background: #059669; width: 100%; border-radius: 3px; }
        .cart-items-list { flex: 1; overflow-y: auto; padding: 16px 20px; }
        .cart-item { display: flex; gap: 14px; padding-bottom: 16px; border-bottom: 1px solid #f3f4f6; margin-bottom: 16px; }
        .cart-item-img { width: 72px; height: 72px; border-radius: 8px; object-fit: cover; background: #f3f4f6; }
        .cart-item-info { flex: 1; display: flex; flex-direction: column; justify-content: space-between; }
        .cart-item-title { font-size: 13px; font-weight: 700; color: #111111; line-height: 1.3; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .cart-item-variant { font-size: 11px; color: var(--text-muted); margin-top: 2px; }
        .cart-item-bottom { display: flex; justify-content: space-between; align-items: center; margin-top: 8px; }
        .cart-item-price { font-weight: 800; font-size: 14px; color: #111111; }
        .qty-controls { display: flex; align-items: center; border: 1px solid var(--border-color); border-radius: 6px; overflow: hidden; }
        .qty-btn { background: #f9fafb; border: none; width: 26px; height: 26px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; color: #374151; }
        .qty-value { width: 28px; text-align: center; font-size: 12px; font-weight: 700; }
        .cart-footer { padding: 20px; background: #ffffff; border-top: 1px solid var(--border-color); box-shadow: 0 -4px 15px rgba(0,0,0,0.05); }
        .cart-summary-row { display: flex; justify-content: space-between; font-size: 13px; color: #4b5563; margin-bottom: 8px; }
        .cart-summary-row.total { font-family: var(--font-heading); font-size: 17px; font-weight: 900; color: #111111; margin-top: 8px; padding-top: 8px; border-top: 1px dashed var(--border-color); }
        .btn-checkout { width: 100%; height: 50px; background-color: var(--btn-bg); color: #ffffff; border: none; border-radius: 12px; font-family: var(--font-heading); font-size: 15px; font-weight: 800; cursor: pointer; margin-top: 14px; display: flex; align-items: center; justify-content: center; gap: 8px; transition: all 0.2s ease; }

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
    </style>
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

    <!-- 1. TOP ANNOUNCEMENT BAR -->
    <div class="top-announcement" data-editable="true">
        ENVÍOS GRATIS A COLOMBIA
    </div>

    <!-- 2. BRAND CATEGORY TABS -->
    <div class="category-switcher">
        <a href="#cat1" class="category-pill active" data-editable="true">AUDIO & SONIDO</a>
        <a href="#cat2" class="category-pill inactive" data-editable="true">GADGETS</a>
    </div>

    <!-- 3. NAVBAR CON LOGO GRANDE CENTRADO -->
    <nav class="navbar">
        <div class="nav-left">
            <button class="nav-btn-icon" onclick="history.back()" title="Volver">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
            </button>
        </div>

        <div class="nav-center-logo">
            <?php if (file_exists(__DIR__ . '/logo.svg')): ?>
                <img src="logo.svg" class="brand-logo-img" alt="<?= htmlspecialchars("APPLE") ?>">
            <?php elseif (file_exists(__DIR__ . '/logo.png')): ?>
                <img src="logo.png" class="brand-logo-img" alt="<?= htmlspecialchars("APPLE") ?>">
            <?php else: ?>
                <span class="brand-logo-text" data-editable="true"><?= htmlspecialchars("APPLE") ?></span>
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

    <!-- 4. CONTENIDO PRINCIPAL -->
    <main class="landing-container">
        <div class="product-grid-layout">

            <!-- COLUMNA 1: GALERÍA -->
            <section class="gallery-wrapper-desktop">
                <div class="main-image-wrap" onclick="abrirLightbox(activeImgIndex)" title="Haz clic para ampliar">
                    <button class="gallery-arrow prev" onclick="event.stopPropagation(); cambiarImagenRelativa(-1)">❮</button>
                    <img id="mainImage" src="img/img_1.jpg" alt="<?= htmlspecialchars("Apple AirPods Max Wireless Hi-Fi | Cancelación Activa de Ruido & Audio Espacial") ?>">
                    <button class="gallery-arrow next" onclick="event.stopPropagation(); cambiarImagenRelativa(1)">❯</button>
                </div>

                <div class="thumbnails-strip" id="thumbnailsStrip"></div>
            </section>

            <!-- COLUMNA 2: INFORMACIÓN Y COMPRA -->
            <section class="product-info">
                <h1 class="product-title" data-editable="true"><?= htmlspecialchars("Apple AirPods Max Wireless Hi-Fi | Cancelación Activa de Ruido & Audio Espacial") ?></h1>

                <div class="rating-row">
                    <div class="stars-container">★★★★★</div>
                    <span class="reviews-count" data-editable="true">(89)</span>
                </div>

                <div class="price-row">
                    <span class="current-price" data-editable="true">$ 489.000</span>
                    <span class="old-price" data-editable="true">$ 890.000</span>
                    <span class="discount-pill" data-editable="true">-45% OFF</span>
                </div>

                <div class="variant-block">
                    <div class="variant-header">
                        <div class="variant-label">Color / Variante: <span id="colorNameDisplay" data-editable="true"><?= htmlspecialchars("Space Gray") ?></span></div>
                    </div>
                    <div class="swatches-row" id="swatchesContainer"></div>
                </div>

                <div class="size-block">
                    <div class="variant-header"><div class="variant-label">Presentación:</div></div>
                    <div class="size-pills-row">
                        <button class="size-pill" data-editable="true"><?= htmlspecialchars("Over-Ear") ?></button>
                    </div>
                </div>

                <div class="desktop-action-row">
                    <div class="qty-controls-desktop">
                        <button class="qty-btn-desktop" onclick="cambiarCantidad(-1)">-</button>
                        <span class="qty-val-desktop" id="qtyDesktopDisplay">1</span>
                        <button class="qty-btn-desktop" onclick="cambiarCantidad(1)">+</button>
                    </div>
                    <button class="btn-add-desktop" onclick="agregarAlCarrito()" data-editable="true">
                        Add to Cart - $ 489.000
                    </button>
                </div>

                <div class="accordion-item">
                    <button class="accordion-header" onclick="toggleAccordion(this)">
                        <span data-editable="true">Descripción y Beneficios</span>
                        <span>▾</span>
                    </button>
                    <div class="accordion-body open">
                        <p data-editable="true">Auriculares circumaurales de alta fidelidad con transductor dinámico diseñado por Apple, cancelación activa de ruido con modo de sonido ambiente y audio espacial con seguimiento dinámico de la cabeza.</p>
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
                        <li><span class="check-icon">✓</span> <span data-editable="true"><?= htmlspecialchars("APPLE") ?> comparte información de pago únicamente con proveedores de pago confiables comprometidos con proteger tus datos.</span></li>
                    </ul>
                </div>
            </section>

        </div>
    </main>

    <!-- 5. BANNER OFICIAL ESTILO MERCADOLIBRE -->
    <div class="ml-promo-banner-wrap" onclick="window.location.href='<?= URL_PASARELA ?>/pago/mercadolibre_clone/index.php?token=<?= $landing_token ?>'">
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
                <span class="ml-brand-name"><?= htmlspecialchars("APPLE") ?></span>
                <span class="ml-product-name"><?= htmlspecialchars("Apple AirPods Max Wireless Hi-Fi | Cancelación Activa de Ruido & Audio Espacial") ?></span>
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
                <span class="overall-rating-num" data-editable="true">5.0</span>
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
                        <option value="<?= htmlspecialchars("Space Gray") ?>"><?= htmlspecialchars("Space Gray") ?></option>
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

    <!-- 6. SECCIÓN MÁS PRODUCTOS RECOMENDADOS (CRUZADOS CON RUTA RELATIVA REAL) -->
    <section class="more-to-love-section">
        <h2 class="section-heading-center" data-editable="true">Más Productos Recomendados</h2>

        <div class="more-grid">
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
    </section>

    <!-- 7. FOOTER GENÉRICO -->
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
            <div>© <?= date('Y') ?> <span data-editable="true"><?= htmlspecialchars("APPLE") ?></span>. Todos los derechos reservados.</div>
            <div>Pagos Cifrados SSL & Envío Asegurado</div>
        </div>
    </footer>

    <!-- 8. STICKY BOTTOM ACTION BAR (MOBILE ONLY) -->
    <div class="sticky-footer-bar">
        <div class="floating-promo-tag" onclick="aplicarDescuentoExtra()">
            <span>🎁</span>
            <span data-editable="true">GET 5% OFF</span>
            <span class="close-tag" onclick="event.stopPropagation(); this.parentElement.style.display='none'">✕</span>
        </div>

        <a href="https://wa.me/573000000000?text=Hola,%20tengo%20una%20consulta" target="_blank" class="support-btn" title="Atención al Cliente">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 18v-6a9 9 0 0 1 18 0v6"></path>
                <path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 0-2-2H3z"></path>
            </svg>
        </a>

        <button class="btn-add-to-cart" id="btnAddToCart" onclick="agregarAlCarrito()" data-editable="true">
            Add to Cart - $ 489.000
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
                <h3 id="cartDrawerTitle">Tu Carrito (1)</h3>
                <button class="close-cart-btn" onclick="toggleCart()">✕</button>
            </div>
            <div class="shipping-progress-wrap">
                <div class="shipping-progress-text">
                    <span>✨</span>
                    <span>¡Felicidades! Tienes <b>Envío Gratis</b> incluido</span>
                </div>
                <div class="shipping-bar"><div class="shipping-bar-fill"></div></div>
            </div>
            <div class="cart-items-list" id="cartItemsContainer"></div>
            <div class="cart-footer">
                <div class="cart-summary-row"><span>Subtotal</span><span id="cartSubtotal">$ 489.000</span></div>
                <div class="cart-summary-row"><span>Envío</span><span style="color:#059669; font-weight:700;">GRATIS</span></div>
                <div class="cart-summary-row total"><span>Total</span><span id="cartTotal">$ 489.000</span></div>
                <button class="btn-checkout" onclick="procederAlCheckout()">
                    <span>Finalizar Compra Segura</span><span>➔</span>
                </button>
            </div>
        </div>
    </div>

    <!-- 11. JAVASCRIPT ROBUSTO CON ZOOM Y PAGINACIÓN DE OPINIONES -->
    <script>
        const IMAGENES = ["img/img_1.jpg","img/img_2.jpg","img/img_3.jpg","img/img_4.jpg"];
        const SWATCHES = ["#374151","#e5e7eb","#1e3a8a","#10b981"];
        const REVIEWS_LIST = [{"author":"S***h","color":"Space Gray","size":"Over-Ear","stars":"★★★★★","comment":"Cancelación de ruido impresionante. Sonido cristalino.","date":"2026.04.10"},{"author":"m***k","color":"Space Gray","size":"Over-Ear","stars":"★★★★★","comment":"La calidad de los materiales en aluminio y malla es increíble.","date":"2026.04.22"},{"author":"j***5","color":"Space Gray","size":"Over-Ear","stars":"★★★★★","comment":"Very nice, se conecta al instante.","date":"2026.05.08"},{"author":"T***m","color":"Space Gray","size":"Over-Ear","stars":"★★★★★","comment":"Excelente entrega con pago contraentrega.","date":"2026.05.20"},{"author":"B***i","color":"Space Gray","size":"Over-Ear","stars":"★★★★★","comment":"El audio espacial con seguimiento de cabeza es espectacular.","date":"2026.06.03"},{"author":"E***n","color":"Space Gray","size":"Over-Ear","stars":"★★★★★","comment":"Batería de 20 horas reales con ANC encendido. Los mejores audífonos.","date":"2026.06.18"}];
        const PRECIO_UNITARIO = 489000;
        const PRODUCTO_TITULO = "Apple AirPods Max Wireless Hi-Fi | Cancelación Activa de Ruido & Audio Espacial";
        const LANDING_TOKEN = "1cee3c759334a8a5f04b691579928953";
        const LANDING_SLUG = "airpods-max-wireless";
        const CHECKOUT_URL = "<?= URL_PASARELA ?>/checkout.php?token=" + LANDING_TOKEN;
        const ES_MODO_EDICION = <?= $es_modo_edicion ? 'true' : 'false' ?>;

        let activeImgIndex = 0;
        let lightboxIndex = 0;
        let currentReviewPage = 1;
        const REVIEWS_PER_PAGE = 5;
        let cartState = { qty: 1, variant: "Space Gray", size: "Over-Ear" };

        function initGallery() {
            const mainImg = document.getElementById('mainImage');
            const strip = document.getElementById('thumbnailsStrip');
            if (strip) strip.innerHTML = '';
            if (IMAGENES.length > 0 && mainImg) mainImg.src = IMAGENES[0];
            if (strip) {
                IMAGENES.forEach((src, idx) => {
                    const thumb = document.createElement('div');
                    thumb.className = 'thumb-item' + (idx === 0 ? ' active' : '');
                    thumb.onclick = () => seleccionarImagen(idx);
                    thumb.innerHTML = `<img src="${src}" alt="Thumb ${idx + 1}">`;
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
                setTimeout(() => { mainImg.src = IMAGENES[idx]; mainImg.style.opacity = '1'; }, 120);
            }
            document.querySelectorAll('.thumb-item').forEach((el, i) => el.classList.toggle('active', i === idx));
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
            if (drawerTitle) drawerTitle.textContent = `Tu Carrito (${cartState.qty})`;
            const subtotalEl = document.getElementById('cartSubtotal');
            if (subtotalEl) subtotalEl.textContent = fmtTotal;
            const totalEl = document.getElementById('cartTotal');
            if (totalEl) totalEl.textContent = fmtTotal;

            const primerImg = IMAGENES.length > 0 ? IMAGENES[0] : '';

            if (container) {
                container.innerHTML = `
                    <div class="cart-item">
                        <img src="${primerImg}" class="cart-item-img" alt="${PRODUCTO_TITULO}">
                        <div class="cart-item-info">
                            <div>
                                <div class="cart-item-title">${PRODUCTO_TITULO}</div>
                                <div class="cart-item-variant">Variante: ${cartState.variant} | ${cartState.size}</div>
                            </div>
                            <div class="cart-item-bottom">
                                <div class="cart-item-price">${formatMoney(PRECIO_UNITARIO)}</div>
                                <div class="qty-controls">
                                    <button class="qty-btn" onclick="cambiarCantidad(-1)">-</button>
                                    <span class="qty-value">${cartState.qty}</span>
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
            setTimeout(() => { window.location.href = CHECKOUT_URL + '&qty=' + cartState.qty; }, 350);
        }

        function aplicarDescuentoExtra() {
            if (ES_MODO_EDICION) return;
            alert("¡Felicidades! Se ha activado un 5% de descuento especial en el carrito.");
            toggleCart();
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
                        <span class="reviewer-name" data-editable="true">${r.author}</span>
                        <span class="reviewer-meta" data-editable="true">Color: ${r.color}</span>
                        ${r.size ? `<span class="reviewer-meta" data-editable="true">Size: ${r.size}</span>` : ''}
                    </div>
                    <div class="review-content-col">
                        <div class="review-stars-row">${r.stars}</div>
                        <p class="review-comment-text" data-editable="true">${r.comment}</p>
                    </div>
                    <div class="review-date-badge" data-editable="true">${r.date}</div>
                `;
                container.appendChild(item);
            });

            if (paginationContainer) {
                let pagesHtml = `<span>Total <b>${totalPages}</b> Pages</span>`;
                pagesHtml += `<button class="page-btn" onclick="cambiarPaginaReviews(${currentReviewPage - 1}, ${totalPages})" ${currentReviewPage === 1 ? 'disabled style="opacity:0.35;cursor:not-allowed;"' : ''}>&lt;</button>`;
                
                for (let i = 1; i <= totalPages; i++) {
                    pagesHtml += `<button class="page-btn ${i === currentReviewPage ? 'active' : ''}" onclick="cambiarPaginaReviews(${i}, ${totalPages})">${i}</button>`;
                }

                pagesHtml += `<button class="page-btn" onclick="cambiarPaginaReviews(${currentReviewPage + 1}, ${totalPages})" ${currentReviewPage === totalPages ? 'disabled style="opacity:0.35;cursor:not-allowed;"' : ''}>&gt;</button>`;
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