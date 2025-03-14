<div class="page">
    <div class="page-wrapper">
        <div class="container-xl">
            <!-- Page title -->
            <div class="page-header d-print-none">
                <div class="row align-items-center">
                    <div class="col">
                        <h2 class="page-title">
                            Appraisal Cycle Initiation
                        </h2>
                    </div>
                </div>
            </div>
            <!-- Page body -->
            <div class="page-body">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Draft Appraisal Cycles</h3>
                    </div>
                    <div class="card-body">
                        @if ($cycles->isEmpty())
                            <div class="empty">
                                <div class="empty-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="icon icon-tabler icon-tabler-clipboard-x" width="24" height="24"
                                        viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <path
                                            d="M9 5h-2a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-12a2 2 0 0 0 -2 -2h-2">
                                        </path>
                                        <rect x="9" y="3" width="6" height="4" rx="2"></rect>
                                        <path d="M10 12l4 4m0 -4l-4 4"></path>
                                    </svg>
                                </div>
                                <p class="empty-title">No draft cycles available</p>
                                <p class="empty-subtitle text-muted">
                                    There are no appraisal cycles in draft status ready for initiation.
                                </p>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-vcenter card-table">
                                    <thead>
                                        <tr>
                                            <th>Cycle Name</th>
                                            <th>Start Date</th>
                                            <th>End Date</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($cycles as $cycle)
                                            <tr>
                                                <td>{{ $cycle->cycle_name }}</td>
                                                <td>{{ $cycle->start_date }}</td>
                                                <td>{{ $cycle->end_date }}</td>
                                                <td>
                                                    <form
                                                        action="{{ route('appraisal_cycle_initiation.initiateCycle') }}"
                                                        method="POST"
                                                        onsubmit="return confirm('Are you sure you want to initiate this appraisal cycle?');">
                                                        @csrf
                                                        <input type="hidden" name="cycle_id"
                                                            value="{{ $cycle->id }}">
                                                        <button type="submit" class="btn btn-primary btn-sm">
                                                            Initiate Cycle
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
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

    .empty-icon {
        font-size: 3rem;
        color: #adb5bd;
    }

    .empty-title {
        font-size: 1.25rem;
        font-weight: 600;
        margin-top: 1rem;
    }

    .empty-subtitle {
        max-width: 300px;
        margin: 0 auto;
    }
</style>
