<div class="page">
    <div class="page-wrapper">
        <div class="container-xl">
            <!-- Page title -->
            <div class="page-header d-print-none">
                <div class="row g-2 align-items-center">
                    <div class="col">
                        <h2 class="page-title">
                            Rating Scales Management
                        </h2>
                    </div>
                    <div class="col-12 col-md-auto ms-auto d-print-none">
                        <div class="d-flex">
                            @if ($viewAction != 'list')
                                <a href="{{ route('appraisal_rating_scales.index') }}"
                                    class="btn btn-outline-primary me-2">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="icon icon-tabler icon-tabler-arrow-left" width="24" height="24"
                                        viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <line x1="5" y1="12" x2="19" y2="12"></line>
                                        <line x1="5" y1="12" x2="11" y2="18"></line>
                                        <line x1="5" y1="12" x2="11" y2="6"></line>
                                    </svg>
                                    Back to List
                                </a>
                            @endif
                            @if ($viewAction == 'list')
                                <a href="{{ route('appraisal_rating_scales.index', ['action' => 'create']) }}"
                                    class="btn btn-primary">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"
                                        viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <line x1="12" y1="5" x2="12" y2="19" />
                                        <line x1="5" y1="12" x2="19" y2="12" />
                                    </svg>
                                    Create New Scale
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <!-- Page body -->
            <div class="page-body">
                <div class="card">
                    <div class="card-body">
                        @if ($viewAction == 'list')
                            <div class="table-responsive">
                                <table class="table table-vcenter card-table">
                                    <thead>
                                        <tr>
                                            <th>Scale Name</th>
                                            <th>Scale Code</th>
                                            <th>Scale Value</th>
                                            <th>Description</th>
                                            <th class="w-1"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($scales as $scale)
                                            <tr>
                                                <td>{{ $scale->scale_name }}</td>
                                                <td>{{ $scale->scale_code }}</td>
                                                <td>{{ $scale->scale_value }}</td>
                                                <td>{{ Str::limit($scale->scale_description, 50) }}</td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a href="{{ route('appraisal_rating_scales.index', ['action' => 'edit', 'id' => $scale->id]) }}"
                                                            class="btn btn-sm btn-outline-secondary">Edit</a>
                                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                                            onclick="confirmDelete({{ $scale->id }})">Delete</button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @elseif($viewAction == 'create' || $viewAction == 'edit')
                            <form id="scaleForm"
                                action="{{ $viewAction == 'create' ? route('appraisal_rating_scales.store') : route('appraisal_rating_scales.update', $scale->id ?? '') }}"
                                method="POST">
                                @csrf
                                @if ($viewAction == 'edit')
                                    @method('PUT')
                                @endif
                                <div class="mb-3">
                                    <label class="form-label required">Scale Name</label>
                                    <input type="text" class="form-control" name="scale_name" required
                                        value="{{ $scale->scale_name ?? old('scale_name') }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label required">Scale Code</label>
                                    <input type="text" class="form-control" name="scale_code" required
                                        value="{{ $scale->scale_code ?? old('scale_code') }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Scale Value</label>
                                    <input type="text" class="form-control" name="scale_value"
                                        value="{{ $scale->scale_value ?? old('scale_value') }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Scale Description</label>
                                    <textarea class="form-control" name="scale_description" rows="4">{{ $scale->scale_description ?? old('scale_description') }}</textarea>
                                </div>
                                <div class="form-footer">
                                    <button type="submit" class="btn btn-primary">Save Scale</button>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('scaleForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                if (this.checkValidity()) {
                    this.submit();
                } else {
                    e.stopPropagation();
                    this.classList.add('was-validated');
                }
            });
        }

        const notification = @json(session('notification'));
        if (notification) {
            Swal.fire({
                icon: notification.type,
                title: notification.type === 'success' ? 'Success!' : 'Error!',
                text: notification.message,
                confirmButtonText: 'OK',
                customClass: {
                    confirmButton: 'btn btn-primary'
                },
                buttonsStyling: false
            });
        }
    });

    function confirmDelete(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel',
            customClass: {
                confirmButton: 'btn btn-danger',
                cancelButton: 'btn btn-secondary'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `{{ route('appraisal_rating_scales.destroy', '') }}/${id}`;
                form.innerHTML = `@csrf @method('DELETE')`;
                document.body.appendChild(form);
                form.submit();
            }
        });
    }
</script>

<style>
    .card {
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12), 0 1px 2px rgba(0, 0, 0, 0.24);
        transition: all 0.3s cubic-bezier(.25, .8, .25, 1);
    }

    .card:hover {
        box-shadow: 0 14px 28px rgba(0, 0, 0, 0.25), 0 10px 10px rgba(0, 0, 0, 0.22);
    }

    .btn-primary {
        background-color: #467fd0;
        border-color: #467fd0;
    }

    .btn-primary:hover {
        background-color: #2b5394;
        border-color: #2b5394;
    }

    .table-vcenter td,
    .table-vcenter th {
        vertical-align: middle;
    }

    .form-control:focus {
        border-color: #467fd0;
        box-shadow: 0 0 0 0.2rem rgba(70, 127, 208, 0.25);
    }
</style>
