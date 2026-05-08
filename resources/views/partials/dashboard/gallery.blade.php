<style>
    .gallery-section {
        padding: 60px 0;
    }

    .gallery-title {
        font-weight: 700;
        margin-bottom: 40px;
    }

    .gallery-card {
        border: none;
        border-radius: 12px;
        overflow: hidden;
        transition: all .3s ease;
        background: #fff;
    }

    .gallery-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 12px 25px rgba(0, 0, 0, 0.15);
    }

    .gallery-img {
        height: 230px;
        object-fit: cover;
        transition: transform .4s ease;
    }

    .gallery-card:hover .gallery-img {
        transform: scale(1.05);
    }

    .gallery-caption {
        font-weight: 600;
        margin-bottom: 8px;
    }

    .gallery-desc {
        font-size: 14px;
        color: #6c757d;
    }

    .description-text {
        transition: all .3s ease;
    }
</style>
<div class="row g-4">
    <div class="row mb-4">
        <div class="col-md-4">
            <form method="GET" action="{{ route('dashboard') }}">
                <div class="input-group input-group-merge">
                    <span class="input-group-text" id="basic-addon-search31">Kategori</span>
                    <select name="kategori" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Semua Kategori --</option>

                        @foreach ($kategoris as $kategori)
                            <option value="{{ $kategori->id }}"
                                {{ request('kategori') == $kategori->id ? 'selected' : '' }}>
                                {{ $kategori->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
    </div>
    @forelse($galleries as $galeri)
        @php
            $limit = 60;
            $desc = $galeri->deskripsi;
            $short = Str::limit($desc, $limit);
        @endphp

        <div class="col-lg-3 col-md-6 col-sm-12">

            <div class="card gallery-card h-100">

                <img src="{{ asset($galeri->photo_thumbnail) }}" class="card-img-top gallery-img"
                    alt="{{ $galeri->alias }}">

                <div class="card-body">

                    <h5 class="gallery-caption">
                        {{ $galeri->caption }} | <small class="text-muted">{{ $galeri->kategori_names }}</small>
                    </h5>

                    <p class="gallery-desc card-text">
                        @php
                            $descLimit = 60;
                            $description = $galeri->description;
                        @endphp

                    <p class="card-text text-muted small description-text" data-full="{{ $description }}">
                        {{ Str::limit($description, $descLimit) }}
                        @if (strlen($description) > $descLimit)
                            <span class="show-more-link text-primary" style="cursor:pointer">Show More</span>
                        @endif
                    </p>

                    @php
                        $keywords = explode(',', $galeri->keyword);
                    @endphp

                    @foreach ($keywords as $keyword)
                        <span class="badge bg-primary">#{{ trim($keyword) }}</span>
                    @endforeach
                    </p>

                </div>

            </div>

        </div>

    @empty

        <div class="col-12 text-center">
            <p class="text-muted">Belum ada galeri foto.</p>
        </div>
    @endforelse

</div>


{{-- Pagination --}}
<div class="mt-4">
    {{ $galleries->links() }}
</div>
