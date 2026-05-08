@extends('layouts.app')

@section('title', 'Notifications')

@section('konten')
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Your Notifications</h4>

        </div>

        {{-- TABS --}}
        <ul class="nav nav-tabs mb-3" id="notifTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="unread-tab" data-bs-toggle="tab" data-bs-target="#unread-tab-pane"
                    type="button" role="tab">
                    Unread ({{ $unreadNotifications->total() }})
                </button>
            </li>

            <li class="nav-item" role="presentation">
                <button class="nav-link" id="read-tab" data-bs-toggle="tab" data-bs-target="#read-tab-pane" type="button"
                    role="tab">
                    Read ({{ $readNotifications->total() }})
                </button>
            </li>
        </ul>

        <div class="tab-content">

            {{-- ==================== UNREAD TAB ==================== --}}
            <div class="tab-pane fade show active" id="unread-tab-pane" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="text-secondary mb-2">Unread Notifications</h6>

                    @if ($unreadNotifications->count() > 0)
                        <button id="mark-all" class="btn btn-sm btn-outline-primary">Mark All as Read</button>
                    @endif
                </div>
                <ul class="list-group mb-4" id="unread-list">
                    @forelse ($unreadNotifications as $notification)
                        <li class="list-group-item list-group-item-action notification-item"
                            data-id="{{ $notification->id }}" data-link="{{ $notification->data['link'] ?? '#' }}">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 me-3">
                                    <img src="{{ asset($notification->data['avatar']) ?? asset('assets/img/avatars/1.png') }}"
                                        class="rounded-circle" width="40" height="40">
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">{{ $notification->data['title'] ?? 'No Title' }}</h6>
                                    <p class="mb-0">{{ $notification->data['messages'] ?? '' }}</p>
                                    <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                </div>
                            </div>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">No unread notifications</li>
                    @endforelse
                </ul>

                {{-- Pagination Unread --}}
                <div class="mt-2 mb-4">
                    {{ $unreadNotifications->onEachSide(1)->links('pagination::bootstrap-5') }}
                </div>
            </div>


            {{-- ==================== READ TAB ==================== --}}
            <div class="tab-pane fade" id="read-tab-pane" role="tabpanel">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="text-secondary mb-2">Read Notifications</h6>

                    @if ($readNotifications->count() > 0)
                        <button id="delete-read" class="btn btn-sm btn-outline-danger">
                            <i class="ti ti-trash"></i> Delete All
                        </button>
                    @endif
                </div>

                <ul class="list-group" id="read-list">
                    @forelse ($readNotifications as $notification)
                        <li class="list-group-item bg-light read-item" data-id="{{ $notification->id }}">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 me-3">
                                        <img src="{{ asset($notification->data['avatar']) ?? asset('assets/img/avatars/1.png') }}"
                                            class="rounded-circle" width="40" height="40">
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 text-muted">{{ $notification->data['title'] ?? 'No Title' }}</h6>
                                        <p class="mb-0 text-muted">{{ $notification->data['messages'] ?? '' }}</p>
                                        <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                    </div>
                                </div>
                            </div>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">No read notifications</li>
                    @endforelse
                </ul>

                {{-- Pagination Read --}}
                <div class="mt-3">
                    {{ $readNotifications->onEachSide(1)->links('pagination::bootstrap-5') }}
                </div>
            </div>

        </div>
    </div>
@endsection


@push('style')
    <style>
        .notification-item {
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        .notification-item:hover {
            background-color: #f8f9fa;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // === MARK SINGLE AS READ + LINK ===
            document.querySelectorAll('.notification-item').forEach(item => {
                item.addEventListener('click', function(e) {
                    if (e.target.closest('.btn-delete')) return; // ignore delete btn

                    const id = this.dataset.id;
                    const link = this.dataset.link;

                    fetch("{{ route('notifications.markAsRead') }}", {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                id
                            })
                        })
                        .then(r => r.json())
                        .then(data => {
                            if (data.success) {
                                this.classList.add('bg-light');
                                setTimeout(() => window.location.href = link, 200);
                            }
                        });
                });
            });

            // === MARK ALL AS READ ===
            document.getElementById('mark-all').addEventListener('click', function() {
                fetch("{{ route('notifications.markAllAsRead') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(() => {
                        toastr.success('All notifications marked as read.');
                        location.reload(); // reload supaya struktur bersih dan tombol tetap muncul
                    });
            });

            // === DELETE SINGLE NOTIFICATION ===
            // document.querySelectorAll('.btn-delete').forEach(btn => {
            //     btn.addEventListener('click', function(e) {
            //         e.stopPropagation();
            //         const item = this.closest('.read-item');
            //         const id = item.dataset.id;

            //         fetch(`/notifications/${id}`, {
            //                 method: 'DELETE',
            //                 headers: {
            //                     'X-CSRF-TOKEN': '{{ csrf_token() }}'
            //                 }
            //             })
            //             .then(res => res.json())
            //             .then(() => {
            //                 toastr.success('Notification deleted.');
            //                 item.remove();

            //                 const readList = document.getElementById('read-list');
            //                 if (readList.querySelectorAll('.read-item').length === 0) {
            //                     readList.innerHTML =
            //                         '<li class="list-group-item text-muted">No read notifications</li>';
            //                     const deleteReadBtn = document.getElementById('delete-read');
            //                     if (deleteReadBtn) deleteReadBtn.remove();
            //                 }
            //             });
            //     });
            // });



        });

        // === DELETE ALL READ ===
        $('body').on('click', '#delete-read', function() {
            const token = $("meta[name='csrf-token']").attr("content");
            Swal.fire({
                title: 'Delete all read notifications?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete them!',
                cancelButtonText: 'Cancel',
                customClass: {
                    confirmButton: 'btn btn-danger me-3 waves-effect waves-light',
                    cancelButton: 'btn btn-label-secondary waves-effect waves-light'
                },
                buttonsStyling: false
            }).then(result => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('notifications.deleteRead') }}",
                        type: "DELETE",
                        headers: {
                            'X-CSRF-TOKEN': token
                        },
                        success: function(response) {
                            if (response.success) {
                                $('#read-list .read-item').fadeOut(400, function() {
                                    $(this).remove();
                                    if ($('#read-list .read-item')
                                        .length === 0) {
                                        $('#read-list').html(
                                            '<li class="list-group-item text-muted">No read notifications</li>'
                                        );
                                        $('#delete-read').remove();
                                    }
                                });
                                toastr.success('All read notifications deleted.');
                            }
                        },
                        error: function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Failed to delete',
                                text: 'An error occurred. Please try again later.'
                            });
                        }
                    });
                }
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // --- Detect Query Params ---
            const params = new URLSearchParams(window.location.search);

            // Jika ada read_page → buka tab READ
            if (params.has('read_page')) {
                const readTabTrigger = document.querySelector('#read-tab');
                const tab = new bootstrap.Tab(readTabTrigger);
                tab.show();
            }

            // Jika ada unread_page → buka tab UNREAD
            if (params.has('unread_page')) {
                const unreadTabTrigger = document.querySelector('#unread-tab');
                const tab = new bootstrap.Tab(unreadTabTrigger);
                tab.show();
            }

        });
    </script>
@endpush
