<div class="page">
    <div class="page-wrapper">
        <div class="container-xl">
            <!-- Page title -->
            <div class="page-header d-print-none">
                <div class="row g-2 align-items-center">
                    <div class="col">
                        <h2 class="page-title">
                            Appraisal Cycles
                        </h2>
                    </div>
                    <div class="col-12 col-md-auto ms-auto d-print-none">
                        <div class="btn-list">
                            @if ($viewAction != 'list')
                                <a href="{{ route('appraisal_appraisal_cycles.index') }}" class="btn btn-ghost-primary">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"
                                        viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <line x1="5" y1="12" x2="19" y2="12" />
                                        <line x1="5" y1="12" x2="11" y2="18" />
                                        <line x1="5" y1="12" x2="11" y2="6" />
                                    </svg>
                                    Back to List
                                </a>
                            @endif
                            @if ($viewAction == 'list')
                                <a href="{{ route('appraisal_appraisal_cycles.index', ['action' => 'create']) }}"
                                    class="btn btn-primary">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"
                                        viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <line x1="12" y1="5" x2="12" y2="19" />
                                        <line x1="5" y1="12" x2="19" y2="12" />
                                    </svg>
                                    Create New Cycle
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
                                            <th>Cycle Name</th>
                                            <th>Start Date</th>
                                            <th>End Date</th>
                                            <th>Status</th>
                                            <th class="w-1"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($cycles as $cycle)
                                            <tr>
                                                <td>{{ $cycle->cycle_name }}</td>
                                                <td>{{ $cycle->start_date }}</td>
                                                <td>{{ $cycle->end_date }}</td>
                                                <td>
                                                    <span
                                                        class="badge bg-{{ $cycle->status == 'Open' ? 'success' : ($cycle->status == 'Closed' ? 'danger' : ($cycle->status == 'Draft' ? 'warning' : 'secondary')) }}">
                                                        {{ $cycle->status }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a href="{{ route('appraisal_appraisal_cycles.index', ['action' => 'edit', 'id' => $cycle->id]) }}"
                                                            class="btn btn-sm btn-ghost-primary">Edit</a>
                                                        <button type="button" class="btn btn-sm btn-ghost-danger"
                                                            onclick="confirmDelete({{ $cycle->id }})">Delete</button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @elseif($viewAction == 'create' || $viewAction == 'edit')
                            <form id="cycleForm"
                                action="{{ $viewAction == 'create' ? route('appraisal_appraisal_cycles.store') : route('appraisal_appraisal_cycles.update', $cycle->id ?? '') }}"
                                method="POST">
                                @csrf
                                @if ($viewAction == 'edit')
                                    @method('PUT')
                                @endif
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required">Cycle Name</label>
                                        <input type="text" class="form-control" name="cycle_name" required
                                            value="{{ $cycle->cycle_name ?? old('cycle_name') }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required">Status</label>
                                        <select class="form-select" name="status" required>
                                            @foreach (['Open', 'Closed', 'Draft', 'Archived'] as $status)
                                                <option value="{{ $status }}"
                                                    {{ ($cycle->status ?? old('status')) == $status ? 'selected' : '' }}>
                                                    {{ $status }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required">Start Date</label>
                                        <input type="date" class="form-control" name="start_date" required
                                            value="{{ $cycle->start_date ?? old('start_date') }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required">End Date</label>
                                        <input type="date" class="form-control" name="end_date" required
                                            value="{{ $cycle->end_date ?? old('end_date') }}">
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label class="form-label">Description</label>
                                        <textarea class="form-control" name="description" rows="4">{{ $cycle->description ?? old('description') }}</textarea>
                                    </div>
                                </div>
                                <div class="form-footer">
                                    <button type="submit" class="btn btn-primary">Save Cycle</button>
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
        const form = document.getElementById('cycleForm');
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
                form.action = `{{ route('appraisal_appraisal_cycles.destroy', '') }}/${id}`;
                form.innerHTML = `@csrf @method('DELETE')`;
                document.body.appendChild(form);
                form.submit();
            }
        });
    }
</script>

<style>
    :root {
        --primary-color: #6366f1;
        --primary-hover-color: #4f46e5;
    }

    .btn-primary {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }

    .btn-primary:hover {
        background-color: var(--primary-hover-color);
        border-color: var(--primary-hover-color);
    }

    .btn-ghost-primary {
        color: var(--primary-color);
    }

    .btn-ghost-primary:hover {
        color: var(--primary-hover-color);
    }

    .form-control:focus,
    .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.25rem rgba(99, 102, 241, 0.25);
    }

    .table-striped tbody tr:nth-of-type(odd) {
        background-color: rgba(0, 0, 0, 0.02);
    }

    .card {
        border: none;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        transition: box-shadow 0.3s ease-in-out;
    }

    .card:hover {
        box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175);
    }

    .badge {
        font-weight: 500;
        padding: 0.35em 0.65em;
    }
</style>
