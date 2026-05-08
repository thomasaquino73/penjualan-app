@extends('layouts.app') {{-- pakai layout situs asli --}}

@section('title', 'Halaman Tidak Ditemukan')

@section('konten')
    <div class="container text-center" style="padding: 80px 20px;">
        <h1 class="display-1 text-danger">404</h1>
        <h2 class="mb-3">Halaman Tidak Ditemukan</h2>
        <p class="mb-4">
            Maaf, halaman yang Anda cari tidak tersedia atau telah dihapus.
        </p>
        <div>
            {{-- Tombol kembali ke halaman sebelumnya --}}
            <a href="{{ url()->previous() }}" class="btn btn-outline-primary me-2">
                ← Kembali
            </a>

            {{-- Tombol ke beranda --}}
            <a href="{{ url('/') }}" class="btn btn-primary">
                Beranda
            </a>
        </div>
    </div>
@endsection
