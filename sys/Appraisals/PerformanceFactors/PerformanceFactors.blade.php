<div class="page">
    <div class="page-wrapper">
        <div class="container-xl">
            <!-- Page title -->
            <div class="page-header d-print-none">
                <div class="row align-items-center">
                    <div class="col">
                        <h2 class="page-title">Performance Factors Management</h2>
                    </div>
                    <div class="col-auto ms-auto d-print-none">
                        <div class="btn-list">
                            @if ($viewAction != 'list')
                                <a href="{{ route('appraisal_performance_factors.index') }}"
                                    class="btn btn-outline-primary">
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
                                <a href="{{ route('appraisal_performance_factors.index', ['action' => 'create']) }}"
                                    class="btn btn-primary d-none d-sm-inline-block">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"
                                        viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <line x1="12" y1="5" x2="12" y2="19" />
                                        <line x1="5" y1="12" x2="19" y2="12" />
                                    </svg>
                                    Create New Factor
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
                                <table class="table table-vcenter card-table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Category</th>
                                            <th>Description</th>
                                            <th>Supervisory</th>
                                            <th class="w-1"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($factors as $factor)
                                            <tr>
                                                <td>{{ $factor->factor_category }}</td>
                                                <td>{{ Str::limit($factor->factor_description, 50) }}</td>
                                                <td>{{ $factor->is_supervisory_factor ? 'Yes' : 'No' }}</td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a href="{{ route('appraisal_performance_factors.index', ['action' => 'edit', 'id' => $factor->id]) }}"
                                                            class="btn btn-sm btn-outline-secondary">Edit</a>
                                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                                            onclick="confirmDelete({{ $factor->id }})">Delete</button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @elseif($viewAction == 'create' || $viewAction == 'edit')
                            <form id="factorForm"
                                action="{{ $viewAction == 'create' ? route('appraisal_performance_factors.store') : route('appraisal_performance_factors.update', $factor->id ?? '') }}"
                                method="POST">
                                @csrf
                                @if ($viewAction == 'edit')
                                    @method('PUT')
                                @endif
                                <div class="mb-3">
                                    <label class="form-label required">Factor Category</label>
                                    <input type="text" class="form-control" name="factor_category" required
                                        value="{{ $factor->factor_category ?? old('factor_category') }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label required">Factor Description</label>
                                    <textarea class="form-control" name="factor_description" rows="4" required>{{ $factor->factor_description ?? old('factor_description') }}</textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label d-block">Is Supervisory Factor</label>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="is_supervisory_factor"
                                            id="supervisory_yes" value="1"
                                            {{ $factor->is_supervisory_factor ?? old('is_supervisory_factor') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="supervisory_yes">Yes</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="is_supervisory_factor"
                                            id="supervisory_no" value="0"
                                            {{ ($factor->is_supervisory_factor ?? old('is_supervisory_factor')) === false ? 'checked' : '' }}>
                                        <label class="form-check-label" for="supervisory_no">No</label>
                                    </div>
                                </div>
                                <div class="form-footer">
                                    <button type="submit" class="btn btn-primary">Save Factor</button>
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
        const form = document.getElementById('factorForm');
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
                form.action = `{{ route('appraisal_performance_factors.destroy', '') }}/${id}`;
                form.innerHTML = `@csrf @method('DELETE')`;
                document.body.appendChild(form);
                form.submit();
            }
        });
    }
</script>

<style>
    .card {
        border: none;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        transition: box-shadow 0.3s ease-in-out;
    }

    .card:hover {
        box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175);
    }

    .btn-primary {
        background-color: #3b7ddd;
        border-color: #3b7ddd;
    }

    .btn-primary:hover {
        background-color: #2d62b9;
        border-color: #2d62b9;
    }

    .table-striped tbody tr:nth-of-type(odd) {
        background-color: rgba(0, 0, 0, 0.02);
    }

    .form-check-input:checked {
        background-color: #3b7ddd;
        border-color: #3b7ddd;
    }

    .form-control:focus {
        border-color: #3b7ddd;
        box-shadow: 0 0 0 0.2rem rgba(59, 125, 221, 0.25);
    }
</style>
