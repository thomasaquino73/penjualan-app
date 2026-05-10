@extends('layouts.app')
@section('konten')
  <div class="container-xxl flex-grow-1 container-p-y">
        <h4><span class="text-muted fw-light">
                @foreach ($breadcrumb as $key => $item)
                    @if (!empty($item['url']))
                        <a href="{{ $item['url'] }}">{{ $item['label'] }}</a>
                    @else
                        {{ $item['label'] }}
                    @endif

                    @if (!$loop->last)
                        /
                    @endif
                @endforeach
            </span>
        </h4>
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h5 class="card-title mb-0">{{ $title }}</h5>
                <div class="card-header-elements ms-auto">

                </div>
            </div>
            <div class="card-datatable table-responsive" style="padding: 20px">

                <form method="POST" action="{{ route('galeri.store') }}" class="py-2" id="postForm"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Foto</label>
                                    <input type="file" name="photo_filename" id="photo_filename" class="form-control">
                                    <span class="error text-danger" id="photo_filenameError"></span>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Caption</label>
                                    <input type="text" name="caption" id="caption" class="form-control">
                                    <span class="error text-danger" id="captionError"></span>
                                </div>
                                
                                <div class="col-md-3">
                                    <label class="form-label">Hastag</label>
                                    <input type="text" name="keyword" id="keyword" class="form-control"
                                        value="{{ old('keyword') }}" placeholder="Enter the Hastag...">
                                    <span class="error text-danger" id="keywordError"></span>
                                </div>
                             
                                <div class="col-md-12">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" id="description" cols="30" rows="3" class="form-control"></textarea>
                                    <span class="error text-danger" id="descriptionError"></span>
                                </div>
                         
                            </div>

                        </div>
                    </div>

                    <div class="card-footer d-flex justify-content-end gap-2">
                        <button type="submit" id="savedata" class="btn btn-primary" data-save-and-new="false">
                            <i class="fa fa-upload me-1"></i> Save and Close
                        </button>

                        <button type="submit" id="savedatamore" class="btn btn-success" data-save-and-new="true">
                            <i class="fa fa-plus-circle me-1"></i> Save and Create New
                        </button>
                        <a href="{{ route('galeri.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
