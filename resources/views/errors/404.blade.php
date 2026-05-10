@extends('layouts.app') {{-- use original site layout --}}

@section('title', 'Page Not Found')

@section('konten')
    <div class="page-404-funny">
        <div class="container d-flex flex-column justify-content-center align-items-center text-center min-vh-100">

            <!-- Cartoon Character -->
            <div class="cartoon mb-4">
                <svg viewBox="0 0 200 200" width="180">
                    <!-- Head -->
                    <circle cx="100" cy="80" r="40" fill="#FCA5A5" />

                    <!-- Eyes -->
                    <circle class="eye" cx="85" cy="75" r="6" fill="#333" />
                    <circle class="eye" cx="115" cy="75" r="6" fill="#333" />

                    <!-- Confused Mouth -->
                    <path d="M80 100 Q100 90 120 100" stroke="#333" stroke-width="3" fill="none" />

                    <!-- Body -->
                    <rect x="70" y="120" width="60" height="50" rx="12" fill="#EF4444" />

                    <!-- Question Mark -->
                    <text x="100" y="155" text-anchor="middle" font-size="20" fill="#fff">?</text>
                </svg>
            </div>

            <!-- Code -->
            <h1 class="code">404</h1>

            <!-- Title -->
            <h3 class="title">Oops! Page Not Found 🤔</h3>

            <!-- Description -->
            <p class="desc">
                Hmm... the page you’re looking for doesn’t exist,<br>
                got lost, or maybe never existed at all.
            </p>

            <!-- Buttons -->
            <div class="d-flex gap-2 flex-wrap justify-content-center">
                <a href="{{ route('dashboard') }}" class="btn-back">
                    ← Go Back
                </a>
            </div>

        </div>
    </div>
@endsection

@push('style')
    <style>
        .page-404-funny {
            background: #f9fafb;
            font-family: 'Poppins', sans-serif;
        }

        /* Floating */
        .cartoon svg {
            animation: float 3s ease-in-out infinite;
        }

        /* Float animation */
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

        /* Shake effect for confusion */
        .cartoon {
            animation: shake 5s infinite;
        }

        @keyframes shake {

            0%,
            100% {
                transform: rotate(0deg);
            }

            20% {
                transform: rotate(2deg);
            }

            40% {
                transform: rotate(-2deg);
            }

            60% {
                transform: rotate(1deg);
            }

            80% {
                transform: rotate(-1deg);
            }
        }

        /* Text */
        .code {
            font-size: 80px;
            font-weight: 800;
            color: #EF4444;
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

        /* Buttons */
        .btn-back {
            padding: 10px 20px;
            border: 1px solid #6366F1;
            color: #6366F1;
            border-radius: 8px;
            text-decoration: none;
            transition: 0.3s;
        }

        .btn-back:hover {
            background: #6366F1;
            color: white;
        }

        .btn-home {
            padding: 10px 20px;
            background: #6366F1;
            color: white;
            border-radius: 8px;
            text-decoration: none;
            transition: 0.3s;
        }

        .btn-home:hover {
            background: #4f46e5;
            transform: translateY(-2px);
        }
    </style>
@endpush
