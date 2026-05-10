@extends('layouts.app')

@section('konten')
    <div class="page-403-funny">
        <div class="container d-flex flex-column justify-content-center align-items-center text-center min-vh-100">

            <!-- Cartoon Character -->
            <div class="cartoon mb-4">
                <svg viewBox="0 0 200 200" width="180">
                    <!-- Head -->
                    <circle cx="100" cy="80" r="40" fill="#FFD166" />

                    <!-- Eyes (kedip animasi) -->
                    <circle class="eye" cx="85" cy="75" r="6" fill="#333" />
                    <circle class="eye" cx="115" cy="75" r="6" fill="#333" />

                    <!-- Mouth (sedih lucu) -->
                    <path d="M80 100 Q100 115 120 100" stroke="#333" stroke-width="3" fill="none" />

                    <!-- Body -->
                    <rect x="70" y="120" width="60" height="50" rx="12" fill="#6366F1" />

                    <!-- Stop Hand -->
                    <g class="hand">
                        <rect x="40" y="120" width="20" height="40" rx="10" fill="#FFD166" />
                        <text x="50" y="145" text-anchor="middle" font-size="14">✋</text>
                    </g>
                </svg>
            </div>

            <!-- Code -->
            <h1 class="code">403</h1>

            <!-- Title -->
            <h3 class="title">Oops! Access Denied 🚫</h3>

            <!-- Description -->
            <p class="desc">
                Hey... you’re not allowed to enter this area.<br>
                This place is off-limits! 😅
            </p>

            <!-- Button -->
            <a href="{{ route('dashboard') }}" class="btn-back">
                ← Take me back
            </a>

        </div>
    </div>
@endsection

@push('style')
    <style>
        .page-403-funny {
            background: #f3f4f6;
            font-family: 'Poppins', sans-serif;
        }

        /* Floating character */
        .cartoon svg {
            animation: float 3s ease-in-out infinite;
        }

        /* Floating animation */
        @keyframes float {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-12px);
            }
        }

        /* Eye blink */
        .eye {
            animation: blink 4s infinite;
        }

        @keyframes blink {

            0%,
            90%,
            100% {
                transform: scaleY(1);
            }

            95% {
                transform: scaleY(0.1);
            }
        }

        /* Hand wave */
        .hand {
            transform-origin: top center;
            animation: wave 2s infinite;
        }

        @keyframes wave {

            0%,
            100% {
                transform: rotate(0deg);
            }

            50% {
                transform: rotate(-15deg);
            }
        }

        /* Text */
        .code {
            font-size: 80px;
            font-weight: 800;
            color: #6366F1;
            margin-bottom: 10px;
        }

        .title {
            font-weight: 600;
            margin-bottom: 10px;
        }

        .desc {
            color: #6b7280;
            margin-bottom: 25px;
        }

        /* Button */
        .btn-back {
            padding: 10px 25px;
            background: #6366F1;
            color: white;
            border-radius: 8px;
            text-decoration: none;
            transition: 0.3s;
        }

        .btn-back:hover {
            background: #4f46e5;
            color: white;
            transform: translateY(-2px);
        }
    </style>
@endpush
