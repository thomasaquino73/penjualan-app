@extends('layouts.app')
@section('title', $title)
@section('konten')
    <div class="container-xxl flex-grow-1 container-p-y">

        <div class="row">

            {{-- Foto Kendaraan --}}
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">

                        @php
                            $foto = $kendaraan->foto ? asset($kendaraan->foto) : asset('image/no-images.jpg');
                        @endphp

                        <img src="{{ $foto }}" class="img-fluid rounded mb-3"
                            style="max-height:250px; object-fit:cover;">

                        <h5 class="mb-1">{{ strtoupper($kendaraan->plat_nomor) }}</h5>

                        <span class="badge bg-success">
                            {{ $kendaraan->status == 1 ? 'Active' : 'Deleted' }}
                        </span>

                    </div>
                </div>
            </div>


            {{-- Informasi Kendaraan --}}
            <div class="col-md-8">

                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Informasi Kendaraan</h5>
                    </div>

                    <div class="card-body">

                        <table class="table table-borderless">
                            <tr>
                                <th width="200">Merk Kendaraan</th>
                                <td>: {{ $kendaraan->merk }}</td>
                            </tr>

                            <tr>
                                <th>Tipe</th>
                                <td>: {{ $kendaraan->tipe }}</td>
                            </tr>

                            <tr>
                                <th>Plat Nomor</th>
                                <td>
                                    : <span class="badge bg-primary fs-6">
                                        {{ strtoupper($kendaraan->plat_nomor) }}
                                    </span>
                                </td>
                            </tr>

                            <tr>
                                <th>Warna</th>
                                <td>: {{ $kendaraan->warna }}</td>
                            </tr>

                            <tr>
                                <th>Pemilik</th>
                                <td>: {{ $kendaraan->pemilik }}</td>
                            </tr>
                        </table>

                    </div>
                </div>


                {{-- Informasi Pencatatan --}}
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Informasi Sistem</h5>
                    </div>

                    <div class="card-body">

                        <table class="table table-borderless">

                            <tr>
                                <th width="200">Dibuat Pada</th>
                                <td>
                                    : {{ $kendaraan->created_at->format('d M Y H:i') }}
                                </td>
                            </tr>

                            <tr>
                                <th>Diupdate Pada</th>
                                <td>
                                    : {{ $kendaraan->updated_at->format('d M Y H:i') }}
                                </td>
                            </tr>

                        </table>

                    </div>
                </div>

            </div>

        </div>

    </div>
@endsection
