<div class="page">
    <div class="page-wrapper">
        <div class="container-xl">
            <div class="page-header d-print-none">
                <div class="row align-items-center">
                    <div class="col">
                        <h2 class="page-title">
                            Appraisal Form Types
                        </h2>
                    </div>
                    <div class="col-auto ms-auto d-print-none">
                        <div class="btn-list">
                            <a href="{{ route('appraisal_form_types.index', ['action' => 'create']) }}"
                                class="btn btn-primary d-none d-sm-inline-block">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"
                                    viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <line x1="12" y1="5" x2="12" y2="19" />
                                    <line x1="5" y1="12" x2="19" y2="12" />
                                </svg>
                                Create New Form Type
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
                                            <th>Form Name</th>
                                            <th>Description</th>
                                            <th class="w-1"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($formTypes as $formType)
                                            <tr>
                                                <td>{{ $formType->form_name }}</td>
                                                <td class="text-muted">{{ Str::limit($formType->description, 50) }}</td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a href="{{ route('appraisal_form_types.index', ['action' => 'edit', 'id' => $formType->id]) }}"
                                                            class="btn btn-sm btn-outline-secondary">Edit</a>
                                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                                            onclick="confirmDelete({{ $formType->id }})">Delete</button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @elseif($viewAction == 'create' || $viewAction == 'edit')
                            <form id="formTypeForm"
                                action="{{ $viewAction == 'create' ? route('appraisal_form_types.store') : route('appraisal_form_types.update', $formType->id ?? '') }}"
                                method="POST">
                                @csrf
                                @if ($viewAction == 'edit')
                                    @method('PUT')
                                @endif
                                <div class="mb-3">
                                    <label class="form-label required">Form Name</label>
                                    <input type="text" class="form-control" name="form_name" required
                                        value="{{ $formType->form_name ?? old('form_name') }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" name="description" rows="4">{{ $formType->description ?? old('description') }}</textarea>
                                </div>
                                <div class="form-footer">
                                    <button type="submit" class="btn btn-primary">Save Form Type</button>
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
        const form = document.getElementById('formTypeForm');
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
                confirmButtonColor: '#206bc4'
            });
        }
    });

    function confirmDelete(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#206bc4',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `{{ route('appraisal_form_types.destroy', '') }}/${id}`;
                form.innerHTML = `@csrf @method('DELETE')`;
                document.body.appendChild(form);
                form.submit();
            }
        });
    }
</script>
