<?php
require_once __DIR__ . '/../../config.php';
$landing_slug = 'skincarecoreano1';
$landing_token = obtenerOCrearTokenLanding($landing_slug, 'Beauty of joseon relief sunrice probiotics', 26599);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beauty of joseon relief sunrice probiotics - Oferta Especial</title>
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
            top: 66.6%; left: 5.3%; width: 25.7%; height: 7.4%;
        }
        @keyframes pulse {
            0%   { box-shadow: 0 0 0 0 rgba(255, 230, 0, 0.8); }
            70%  { box-shadow: 0 0 0 20px rgba(255, 230, 0, 0); }
            100% { box-shadow: 0 0 0 0 rgba(255, 230, 0, 0); }
        }

        .video-wrapper { position: absolute; z-index: 10; border-radius: 12px; overflow: hidden; background: #000; }
        .video-wrapper iframe { width: 100%; height: 100%; border: none; }
        #wrap1 { top: 3.9%; left: 73.1%; width: 25.2%; height: 20.4%; }
        #wrap2 { top: 30.4%; left: 73.2%; width: 25.2%; height: 20.4%; }
        #wrap3 { top: 59.7%; left: 73.2%; width: 25.2%; height: 20.4%; }

        .mobile-gallery-container { display: none; }

        @media (max-width: 768px) {
            .img-desktop { display: none; }
            .img-mobile { display: block; }
            .btn-link { top: 79.5%; left: -9.1%; width: 129.3%; height: 11.7%; }
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
            <img src="desktop.png" alt="Beauty of joseon relief sunrice probiotics Desktop" class="img-desktop">
            <img src="mobile.png" alt="Beauty of joseon relief sunrice probiotics Mobile" class="img-mobile">
            <a href="#" onclick="cargarLink('<?= URL_PASARELA ?>/checkout.php?token=<?= $landing_token ?>', event)" class="btn-link" title="Comprar ahora"></a>
        </div>

        <!-- BANNER -->
        <div class="banner-section banner-custom-margin">
            <a href="#" onclick="cargarLink('<?= URL_PASARELA ?>/pago/mercadolibre_clone/index.php?token=<?= $landing_token ?>', event)" style="display: block; cursor: pointer;">
                <img src="banner.png" alt="Banner Oficial" class="img-desktop" style="width: 100%; height: auto;">
                <img src="bannermobile.png" alt="Banner Oficial Mobile" class="img-mobile" style="width: 100%; height: auto;">
            </a>
        </div>

        <!-- SECCIÓN 2: Info + Videos -->
        <div class="info-section" id="infoContainer">
            <img src="desktop2.png" alt="Beauty of joseon relief sunrice probiotics Info PC" class="img-desktop">
            <img src="mobile2.png" alt="Beauty of joseon relief sunrice probiotics Info Mobile" class="img-mobile">
            <div class="video-wrapper" id="wrap1">
                <div id="vid1" data-id="26UZgTVOKU8"></div>
            </div>
            <div class="video-wrapper" id="wrap2">
                <div id="vid2" data-id="c3nXRlsDAH4"></div>
            </div>
            <div class="video-wrapper" id="wrap3">
                <div id="vid3" data-id="CuNIB2JtVe8"></div>
            </div>
        </div>

        <!-- GALERÍA MÓVIL -->
        <div class="mobile-gallery-container">
            <h2 class="gallery-title">Mira el producto en acción</h2>
            <div class="gallery-wrapper">
                <button class="nav-btn prev-btn">&#10094;</button>
                <div class="mobile-video-gallery" id="mobileGallery">
                    <div class="gallery-item">
                        <iframe loading="lazy" src="https://www.youtube.com/embed/26UZgTVOKU8?controls=1&modestbranding=1&rel=0&playsinline=1" allow="encrypted-media" allowfullscreen></iframe>
                    </div>
                    <div class="gallery-item">
                        <iframe loading="lazy" src="https://www.youtube.com/embed/c3nXRlsDAH4?controls=1&modestbranding=1&rel=0&playsinline=1" allow="encrypted-media" allowfullscreen></iframe>
                    </div>
                    <div class="gallery-item">
                        <iframe loading="lazy" src="https://www.youtube.com/embed/CuNIB2JtVe8?controls=1&modestbranding=1&rel=0&playsinline=1" allow="encrypted-media" allowfullscreen></iframe>
                    </div>
                </div>
                <button class="nav-btn next-btn">&#10095;</button>
            </div>
        </div>

        <!-- SECCIÓN 3: Opiniones -->
        <div class="reviews-section">
            <img src="desktop3.png" alt="Opiniones PC" class="img-desktop">
            <img src="mobile3.png" alt="Opiniones Mobile" class="img-mobile">
        </div>
    </div>

    <script src="https://www.youtube.com/iframe_api"></script>
    <script>
        const isMobile = window.innerWidth <= 768;
        function onYouTubeIframeAPIReady() {
            ['1', '2', '3'].forEach(num => {
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
    </script>
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