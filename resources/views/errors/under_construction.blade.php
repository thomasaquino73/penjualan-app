@section('title', 'Under Construction')
@include('part.header')
<!--Under Maintenance -->
<div class="container-xxl container-p-y text-center">
    <div class="misc-wrapper">
        <h2 class="mb-1 mx-2">Under Construction!</h2>
        <p class="mb-4 mx-2">Sorry for the inconvenience but we're performing some maintenance at the moment</p>
        <a href="{{ route('dashboard') }}" class="btn btn-primary mb-4">Back to home</a>
        <div class="mt-4">
            <img src="{{ asset('') }}assets/img/illustrations/page-misc-under-maintenance.png"
                alt="page-misc-under-maintenance" width="550" class="img-fluid" />
        </div>
    </div>
</div>
<div class="container-fluid misc-bg-wrapper misc-under-maintenance-bg-wrapper">
    <img src="{{ asset('') }}assets/img/illustrations/bg-shape-image-light.png" alt="page-misc-under-maintenance"
        data-app-light-img="illustrations/bg-shape-image-light.png"
        data-app-dark-img="illustrations/bg-shape-image-dark.png" />
</div>
<!-- /Under Maintenance -->
