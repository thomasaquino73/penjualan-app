@extends('layouts.app') {{-- use original site layout --}}

@section('title', 'Page Not Found')

@section('konten')
    <div class="container text-center" style="padding: 80px 20px;">
        <h1 class="display-1 text-danger">404</h1>
        <h2 class="mb-3">Page Not Found</h2>
        <p class="mb-4">
            Sorry, the page you are looking for is not available or has been removed.
        </p>
        <div>
            {{-- Back button --}}
            <a href="{{ url()->previous() }}" class="btn btn-outline-primary me-2">
                ← Go Back
            </a>

            {{-- Home button --}}
            <a href="{{ url('/') }}" class="btn btn-primary">
                Home
            </a>
        </div>
    </div>
@endsection
