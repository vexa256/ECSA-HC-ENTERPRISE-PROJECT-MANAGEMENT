<div class="page">
    <div class="page-wrapper">
        <div class="container-xl">
            <!-- Page title -->
            <div class="page-header d-print-none">
                <div class="row align-items-center">
                    <div class="col">
                        <h2 class="page-title">
                            Appraisal Generation
                        </h2>
                    </div>
                </div>
            </div>
            <!-- Page body -->
            <div class="page-body">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Generate Appraisal Records</h3>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('appraisal_appraisal_generation.generateAppraisals') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label required">Select Appraisal Cycle</label>
                                <select class="form-select" name="cycle_id" required>
                                    <option value="">Choose a cycle...</option>
                                    @foreach ($cycles as $cycle)
                                        <option value="{{ $cycle->id }}">
                                            {{ $cycle->cycle_name }} ({{ $cycle->start_date }} to
                                            {{ $cycle->end_date }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label required">Select Form Type</label>
                                <select class="form-select" name="form_type_id" required>
                                    <option value="">Choose a form type...</option>
                                    @foreach ($formTypes as $formType)
                                        <option value="{{ $formType->id }}">
                                            {{ $formType->form_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-footer">
                                <button type="submit" class="btn btn-primary">Generate Appraisals</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            if (this.checkValidity()) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This will generate appraisal records for all employees. This action cannot be undone.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, generate appraisals!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.submit();
                    }
                });
            } else {
                e.stopPropagation();
                this.classList.add('was-validated');
            }
        });

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
</script>

<style>
    :root {
        --primary-color: #467fcf;
        --primary-hover-color: #316cbe;
    }

    .btn-primary {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }

    .btn-primary:hover {
        background-color: var(--primary-hover-color);
        border-color: var(--primary-hover-color);
    }

    .card {
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12), 0 1px 2px rgba(0, 0, 0, 0.24);
        transition: all 0.3s cubic-bezier(.25, .8, .25, 1);
    }

    .card:hover {
        box-shadow: 0 14px 28px rgba(0, 0, 0, 0.25), 0 10px 10px rgba(0, 0, 0, 0.22);
    }

    .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.25rem rgba(70, 127, 207, 0.25);
    }
</style>
