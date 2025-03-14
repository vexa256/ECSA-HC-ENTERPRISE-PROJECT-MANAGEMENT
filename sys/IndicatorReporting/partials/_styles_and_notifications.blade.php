<style>
    :root {
        --primary-color: #0078d4;
        --secondary-color: #50e6ff;
        --accent-color: #00b294;
        --background-color: #f0f2f5;
        --card-background: #ffffff;
        --text-primary: #252733;
        --text-secondary: #6c757d;
        --border-color: #e0e0e0;
    }

    body {
        background-color: var(--background-color);
        color: var(--text-primary);
    }

    .card {
        background-color: var(--card-background);
        border: none;
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
    }

    .card-header {
        background-color: transparent;
        border-bottom: 1px solid var(--border-color);
        padding: 1.5rem;
    }

    .card-title {
        color: var(--primary-color);
        font-weight: 600;
    }

    .btn-primary {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }

    .btn-primary:hover {
        background-color: #005a9e;
        border-color: #005a9e;
    }

    .btn-outline-primary {
        color: var(--primary-color);
        border-color: var(--primary-color);
    }

    .btn-outline-primary:hover {
        background-color: var(--primary-color);
        color: white;
    }

    .progress {
        height: 8px;
        border-radius: 4px;
    }

    .progress-bar {
        background-color: var(--accent-color);
    }

    .search-input {
        border-radius: 20px;
        padding-left: 40px;
    }

    .search-icon {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-secondary);
    }

    .modal-content {
        border-radius: 15px;
    }

    .modal-header {
        border-bottom: none;
        padding: 2rem 2rem 1rem;
    }

    .modal-body {
        padding: 2rem;
    }

    .modal-footer {
        border-top: none;
        padding: 1rem 2rem 2rem;
    }

    .table th {
        font-weight: 600;
        color: var(--text-secondary);
    }

    .animate-fade-in {
        animation: fadeIn 0.5s ease-in-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    .chart-container {
        position: relative;
        margin: auto;
        height: 300px;
        width: 100%;
    }

    .indicator-card {
        cursor: pointer;
    }

    .indicator-card .card-body {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .indicator-status {
        font-size: 0.875rem;
        padding: 0.25rem 0.5rem;
        border-radius: 20px;
    }

    .status-completed {
        background-color: #e6f7ff;
        color: #0078d4;
    }

    .status-pending {
        background-color: #fff4e5;
        color: #ff8c00;
    }

    .details-table th {
        width: 30%;
    }

    .nav-tabs .nav-link {
        border: none;
        color: var(--text-secondary);
        font-weight: 500;
        padding: 1rem 1.5rem;
    }

    .nav-tabs .nav-link.active {
        color: var(--primary-color);
        border-bottom: 2px solid var(--primary-color);
    }

    .stat-card {
        border-radius: 15px;
        overflow: hidden;
    }

    .stat-card-body {
        padding: 1.5rem;
    }

    .stat-card-icon {
        font-size: 2rem;
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .stat-card-title {
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--text-secondary);
    }

    .stat-card-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-primary);
    }

    .card-subtitle {
        font-size: 0.875rem;
        font-weight: 600;
    }

    .card-text {
        font-size: 0.9rem;
    }

    .collapsed {
        transition: height 0.3s ease-out;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@if (session('success'))
    <script>
        Swal.fire({
            title: 'Success',
            text: '{{ session('
                success ') }}',
            icon: 'success',
            confirmButtonText: 'OK'
        });
    </script>
@endif

@if (session('error'))
    <script>
        Swal.fire({
            title: 'Error',
            text: '{{ session('
                error ') }}',
            icon: 'error',
            confirmButtonText: 'OK'
        });
    </script>
@endif
