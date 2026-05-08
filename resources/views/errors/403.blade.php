@extends('layouts.app')

@section('konten')
    <div class="page-403-premium">

        <div class="area">
            <ul class="circles">
                <li></li>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
            </ul>
        </div>
        <div class="noise-bg"></div>

        <div class="container d-flex justify-content-center align-items-center min-vh-100">
            <!-- Menambahkan atribut data-tilt untuk efek 3D (butuh JS kecil di bawah) -->
            <div class="card-403-premium text-center" data-tilt data-tilt-max="5" data-tilt-speed="400"
                data-tilt-perspective="1000">

                <!-- ILLUSTRATION AREA (Menggantikan Lottie) -->
                <div class="illustration-wrapper mt-2 mb-4">
                    {{-- Anda bisa mengganti SVG ini dengan tag <img> jika punya file gambar premium sendiri --}}
                    {{-- Contoh: <img src="{{ asset('assets/img/forbidden-pro.svg') }}" alt="Access Denied" class="img-fluid main-img"> --}}

                    {{-- SVG Ilustrasi Penjaga Gerbang Digital (Inline untuk kemudahan) --}}
                    <svg class="forbidden-svg" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <linearGradient id="grad1" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" style="stop-color:#FF3B6B;stop-opacity:1" />
                                <stop offset="100%" style="stop-color:#FF8E53;stop-opacity:1" />
                            </linearGradient>
                            <filter id="glow">
                                <feGaussianBlur stdDeviation="3.5" result="coloredBlur" />
                                <feMerge>
                                    <feMergeNode in="coloredBlur" />
                                    <feMergeNode in="SourceGraphic" />
                                </feMerge>
                            </filter>
                        </defs>
                        <!-- Shield Background -->
                        <path
                            d="M256,0C114.8,0,0,114.8,0,256s114.8,256,256,256s256-114.8,256-256S397.2,0,256,0z M256,472 c-119.1,0-216-96.9-216-216S136.9,40,256,40s216,96.9,216,216S375.1,472,256,472z"
                            fill="rgba(255,255,255,0.1)" />
                        <!-- Hand / Stop Sign -->
                        <path
                            d="M386.7,192h-32c-8.8,0-16,7.2-16,16v16h-48v-80c0-17.7-14.3-32-32-32s-32,14.3-32,32v80h-48v-16 c0-8.8-7.2-16-16-16h-32c-8.8,0-16,7.2-16,16v160c0,8.8,7.2,16,16,16h32c8.8,0,16-7.2,16-16v-16h48v48c0,17.7,14.3,32,32,32 s32-14.3,32-32v-48h48v48c0,8.8,7.2,16,16,16h32c8.8,0,16-7.2,16-16V208C402.7,199.2,395.5,192,386.7,192z"
                            fill="url(#grad1)" filter="url(#glow)" />
                        <!-- Neon Circle -->
                        <circle cx="256" cy="256" r="230" stroke="url(#grad1)" stroke-width="10" fill="none"
                            stroke-dasharray="20 15" filter="url(#glow)">
                            <animateTransform attributeName="transform" attributeType="XML" type="rotate" from="0 256 256"
                                to="360 256 256" dur="20s" repeatCount="indefinite" />
                        </circle>
                    </svg>
                </div>

                <!-- TEXT CONTENT -->
                <div class="content-text px-3">
                    <!-- Code -->
                    <h1 class="code-premium">
                        4<span class="zero-rotate">0</span>3
                    </h1>

                    <!-- Title -->
                    <h2 class="title-premium">Access Forbidden</h2>

                    <!-- Description -->
                    <p class="desc-premium">
                        Sorry, your account doesn't have the necessary permissions to view this page.
                        This is a restricted secure area.
                    </p>
                </div>

                <!-- ACTION BUTTON -->
                <div class="mt-5 mb-2">
                    <a href="{{ route('dashboard') }}" class="btn-premium-action">
                        <i class="fas fa-arrow-left me-2"></i> Back to dashboard
                    </a>
                </div>

            </div>
        </div>
    </div>
@endsection


@push('style')
    {{-- Menambahkan Font & FontAwesome untuk icon --}}
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        /* === RESET & FONT === */
        .page-403-premium {
            padding-top: 30px;
            padding-bottom: 30px;
            font-family: 'Poppins', sans-serif;
            position: relative;
            min-height: 100vh;
            width: 100%;
            overflow: hidden;
            /* Premium Dark Gradient */
            background: #0f172a;
            /* Fallback */
            /* background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); */
        }

        /* === PREMIUM BACKGROUND EFFECT (Animated Circles & Noise) === */
        .noise-bg {
            position: absolute;
            width: 100%;
            height: 100%;
            background-image: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAMAAAAp4uhoAAAAVFBMVEUAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAnU8SRAAAAEXRSTlMAA0Se489679PQ0NDQ0NDQ54504gAAAA9JREFUeNo9zUESADEIA0Fm/v+ne4tVsS6mAmS6m4uLi4uLi4uLi4uLi4uLi4uLi4uLi4uLi4uLi7sNcL4AnXjN44YAAAAASUVORK54504g==');
            opacity: 0.03;
            pointer-events: none;
            z-index: 1;
        }

        .area {
            position: absolute;
            width: 100%;
            height: 100vh;
            z-index: 0;
        }

        .circles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            margin: 0;
            padding: 0;
        }

        .circles li {
            position: absolute;
            display: block;
            list-style: none;
            width: 20px;
            height: 20px;
            background: rgba(255, 255, 255, 0.05);
            animation: animate 25s linear infinite;
            bottom: -150px;
            border-radius: 50%;
        }

        .circles li:nth-child(1) {
            left: 25%;
            width: 80px;
            height: 80px;
            animation-delay: 0s;
        }

        .circles li:nth-child(2) {
            left: 10%;
            width: 20px;
            height: 20px;
            animation-delay: 2s;
            animation-duration: 12s;
        }

        .circles li:nth-child(3) {
            left: 70%;
            width: 20px;
            height: 20px;
            animation-delay: 4s;
        }

        .circles li:nth-child(4) {
            left: 40%;
            width: 60px;
            height: 60px;
            animation-delay: 0s;
            animation-duration: 18s;
        }

        .circles li:nth-child(5) {
            left: 65%;
            width: 20px;
            height: 20px;
            animation-delay: 0s;
        }

        .circles li:nth-child(6) {
            left: 75%;
            width: 110px;
            height: 110px;
            animation-delay: 3s;
        }

        .circles li:nth-child(7) {
            left: 35%;
            width: 150px;
            height: 150px;
            animation-delay: 7s;
        }

        .circles li:nth-child(8) {
            left: 50%;
            width: 25px;
            height: 25px;
            animation-delay: 15s;
            animation-duration: 45s;
        }

        .circles li:nth-child(9) {
            left: 20%;
            width: 15px;
            height: 15px;
            animation-delay: 2s;
            animation-duration: 35s;
        }

        .circles li:nth-child(10) {
            left: 85%;
            width: 150px;
            height: 150px;
            animation-delay: 0s;
            animation-duration: 11s;
        }

        @keyframes animate {
            0% {
                transform: translateY(0) rotate(0deg);
                opacity: 1;
                border-radius: 50%;
            }

            100% {
                transform: translateY(-1000px) rotate(720deg);
                opacity: 0;
                border-radius: 20%;
            }
        }

        /* === CARD GLASSMORPHISM PRO === */
        .card-403-premium {
            position: relative;
            z-index: 10;
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);

            padding: 60px 40px;
            border-radius: 30px;

            /* Border tipas untuk efek glass */
            border: 1px solid rgba(255, 255, 255, 0.08);

            /* Soft Shadow premium */
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            color: #fff;

            max-width: 520px;
            width: 90%;
            /* Responsive */

            /* Masuk animation */
            animation: premiumEntry 0.8s cubic-bezier(0.22, 1, 0.36, 1);

            /* Memastikan tilt bekerja dengan baik */
            transform-style: preserve-3d;
            transition: box-shadow 0.3s ease;
        }

        .card-403-premium:hover {
            box-shadow: 0 35px 60px -10px rgba(105, 108, 255, 0.2);
        }

        /* === ILLUSTRATION CSS === */
        .illustration-wrapper {
            width: 100%;
            display: flex;
            justify-content: center;
            transform: translateZ(50px);
            /* Efek kedalaman saat tilt */
        }

        .forbidden-svg {
            width: 200px;
            height: 200px;
            filter: drop-shadow(0 0 15px rgba(255, 59, 107, 0.3));
        }

        /* === TEXT PRO STYLE === */
        .content-text {
            transform: translateZ(30px);
            /* Efek kedalaman saat tilt */
        }

        .code-premium {
            font-size: 110px;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 15px;
            background: linear-gradient(135deg, #fff 30%, #a1a1aa 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -5px;
            position: relative;
        }

        /* Animasi rotasi pada angka 0 */
        .zero-rotate {
            display: inline-block;
            animation: rotateZero 6s linear infinite;
            background: linear-gradient(135deg, #FF3B6B 0%, #FF8E53 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .title-premium {
            font-weight: 700;
            font-size: 28px;
            margin-bottom: 15px;
            color: #fff;
            letter-spacing: 0.5px;
        }

        .desc-premium {
            color: #94a3b8;
            font-weight: 300;
            font-size: 15px;
            line-height: 1.8;
            margin-bottom: 0;
        }

        /* === PREMIUM BUTTON === */
        .btn-premium-action {
            display: inline-block;
            padding: 14px 35px;
            background: linear-gradient(135deg, #696cff 0%, #8f94fb 100%);
            color: white;
            text-decoration: none;
            border-radius: 15px;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.4s ease;
            box-shadow: 0 10px 20px -5px rgba(105, 108, 255, 0.4);
            border: none;
            position: relative;
            overflow: hidden;
            transform: translateZ(40px);
            /* Efek kedalaman */
        }

        .btn-premium-action:hover {
            transform: translateZ(40px) translateY(-3px);
            box-shadow: 0 15px 25px -5px rgba(105, 108, 255, 0.5);
            color: white;
        }

        /* Efek kilau pada hover */
        .btn-premium-action::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -60%;
            width: 20%;
            height: 200%;
            background: rgba(255, 255, 255, 0.2);
            transform: rotate(30deg);
            transition: none;
        }

        .btn-premium-action:hover::after {
            left: 150%;
            transition: all 0.7s ease-in-out;
        }

        /* === ANIMATIONS === */
        @keyframes premiumEntry {
            from {
                opacity: 0;
                transform: translateY(40px) scale(0.95);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes rotateZero {
            0% {
                transform: rotateY(0deg);
            }

            20% {
                transform: rotateY(360deg);
            }

            100% {
                transform: rotateY(360deg);
            }
        }

        /* Responsive Adjustment */
        @media (max-width: 576px) {
            .card-403-premium {
                padding: 40px 20px;
            }

            .code-premium {
                font-size: 80px;
            }

            .title-premium {
                font-size: 22px;
            }

            .forbidden-svg {
                width: 150px;
                height: 150px;
            }
        }
    </style>
@endpush

@push('scripts')
    {{-- VanilaTilt.js untuk efek 3D Parallax pada kartu (Sangat direkomendasikan untuk kesan Premium) --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/vanilla-tilt/1.7.2/vanilla-tilt.min.js"></script>
    <script>
        // Inisialisasi Tilt jika library dimuat
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof VanillaTilt !== 'undefined') {
                VanillaTilt.init(document.querySelectorAll(".card-403-premium"), {
                    max: 5,
                    speed: 400,
                    glare: true,
                    "max-glare": 0.1, // Glare super halus
                });
            }
        });
    </script>
@endpush
