<div class="container-xl">
    <div class="page-header d-print-none">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">
                    HR Positions Management
                </h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="{{ route('appraisal_positions.index', ['action' => 'create']) }}"
                        class="btn btn-primary d-none d-sm-inline-block">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"
                            viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <line x1="12" y1="5" x2="12" y2="19" />
                            <line x1="5" y1="12" x2="19" y2="12" />
                        </svg>
                        Create New Position
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="card">
            <div class="card-body">
                @if ($viewAction == 'list')
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead>
                                <tr>
                                    <th>Position Name</th>
                                    <th>Supervisory</th>
                                    <th>Description</th>
                                    <th class="w-1"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($positions as $position)
                                    <tr>
                                        <td>{{ $position->position_name }}</td>
                                        <td>{{ $position->is_supervisory ? 'Yes' : 'No' }}</td>
                                        <td>{{ Str::limit($position->description, 50) }}</td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="{{ route('appraisal_positions.index', ['action' => 'edit', 'id' => $position->id]) }}"
                                                    class="btn btn-sm btn-outline-secondary">Edit</a>
                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                    onclick="confirmDelete({{ $position->id }})">Delete</button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @elseif($viewAction == 'create' || $viewAction == 'edit')
                    <form id="positionForm"
                        action="{{ $viewAction == 'create' ? route('appraisal_positions.store') : route('appraisal_positions.update', $position->id ?? '') }}"
                        method="POST">
                        @csrf
                        @if ($viewAction == 'edit')
                            @method('PUT')
                        @endif
                        <div class="mb-3">
                            <label class="form-label required">Position Name</label>
                            <input type="text" class="form-control" name="position_name" required
                                value="{{ $position->position_name ?? old('position_name') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Is Supervisory</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="is_supervisory"
                                    id="is_supervisory_true" value="1"
                                    {{ ($position->is_supervisory ?? old('is_supervisory')) == 1 ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_supervisory_true">
                                    Yes
                                </label>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="description" rows="4">{{ $position->description ?? old('description') }}</textarea>
                            </div>
                            <div class="form-footer">
                                <button type="submit" class="btn btn-primary">Save</button>
                            </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle form submission
        const form = document.getElementById('positionForm');
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

        // Handle notifications
        const notification = @json(session('notification'));
        if (notification) {
            Swal.fire({
                icon: notification.type,
                title: notification.type === 'success' ? 'Success!' : 'Error!',
                text: notification.message,
                confirmButtonText: 'OK'
            });
        }
    });

    function confirmDelete(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `{{ route('appraisal_positions.destroy', '') }}/${id}`;
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
        background-color: #1a73e8;
        border-color: #1a73e8;
    }

    .btn-primary:hover {
        background-color: #1765cc;
        border-color: #1765cc;
    }

    .table {
        --bs-table-hover-bg: rgba(0, 0, 0, 0.04);
    }

    .form-switch .form-check-input:checked {
        background-color: #1a73e8;
        border-color: #1a73e8;
    }

    .form-control:focus {
        border-color: #1a73e8;
        box-shadow: 0 0 0 0.2rem rgba(26, 115, 232, 0.25);
    }
</style>
