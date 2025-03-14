<div class="sticky-top">
    <header class="navbar navbar-expand-md sticky-top d-print-none">
        <div class="container-xl">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu"
                aria-controls="navbar-menu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="navbar-brand navbar-brand-autodark d-none-navbar-horizontal pe-0 pe-md-3">
                <a href="/">
                    <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSprutZGJpPgDTYg_gFf3qAxKeriNs6Wma7_w&s"
                        alt="Logo" width="110" height="32" class="navbar-brand-image">
                </a>
            </div>
            <div class="navbar-nav flex-row order-md-last">
                <div class="nav-item d-none d-md-flex me-3">
                    <div class="btn-list">
                        <a href="#" class="btn btn-5" data-bs-toggle="modal" data-bs-target="#hrActionsModal">
                            <!-- Replacing the brand-github SVG with FontAwesome -->
                            <i class="fa-brands fa-graph icon icon-2"></i>
                            HR Actions
                        </a>
                        {{-- <a href="#" class="btn btn-6" target="_blank" rel="noreferrer">
                            <!-- Replacing the heart SVG with FontAwesome -->
                            <i class="fa-solid fa-heart text-pink icon icon-2"></i>
                            MPA Dashboard
                        </a> --}}
                    </div>
                </div>
                <div class="d-none d-md-flex">


                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown"
                            aria-label="Open user menu">
                            <span class="avatar avatar-sm"
                                style="background-image: url('https://www.svgrepo.com/show/286578/users-young.svg')"></span>
                            <div class="d-none d-xl-block ps-2">
                                <div>{{ Auth::user()->name }}</div>
                                <div class="mt-1 small text-secondary">
                                    {{ Auth::user()->JobTitle }}
                                </div>
                            </div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item">Logout</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
    @include('nav.nav')
</div>



<!-- START: HR Actions Modal -->
<div class="modal fade" id="hrActionsModal" tabindex="-1" aria-labelledby="hrActionsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="hrActionsModalLabel">HR Actions Dashboard</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>

            <!-- Modal Body: Menu Items -->
            <div class="modal-body p-0">
                <div class="row g-0">
                    @php
                        $menuItems = [
                            [
                                'icon' => 'fa-solid fa-users',
                                'title' => 'User Management',
                                'description' => 'Manage employee accounts and roles',
                                'color' => 'primary',
                                // Route not defined yet.
                            ],
                            [
                                'icon' => 'fa-solid fa-briefcase',
                                'title' => 'Position Management',
                                'description' => 'Manage job positions and hierarchies',
                                'route' => 'appraisal_positions.index',
                                'color' => 'success',
                            ],
                            [
                                'icon' => 'fa-solid fa-briefcase',
                                'title' => 'Appraisal Forms',
                                'description' => 'Manage appraisal form types',
                                'route' => 'appraisal_form_types.index',
                                'color' => 'success',
                            ],
                            [
                                'icon' => 'fa-solid fa-calendar',
                                'title' => 'Appraisal Cycle Management',
                                'description' => 'Set up and manage appraisal periods',
                                'route' => 'appraisal_appraisal_cycles.index',
                                'color' => 'info',
                            ],
                            [
                                'icon' => 'fa-solid fa-star',
                                'title' => 'Manage Performance Factors',
                                'description' => 'Define and update performance criteria',
                                'route' => 'appraisal_performance_factors.index',
                                'color' => 'warning',
                            ],
                            [
                                'icon' => 'fa-solid fa-chart-bar',
                                'title' => 'Manage Rating Scales',
                                'description' => 'Customize rating scales for evaluations',
                                'route' => 'appraisal_rating_scales.index',
                                'color' => 'danger',
                            ],
                            [
                                'icon' => 'fa-solid fa-sync',
                                'title' => 'Initiate Appraisal Cycle',
                                'description' => 'Initiate appraisal cycles when ready',
                                'route' => 'appraisal_cycle_initiation.index',
                                'color' => 'secondary',
                            ],
                            [
                                'icon' => 'fa-solid fa-clipboard',
                                'title' => 'Launch/View Performance Appraisals',
                                'description' => 'Start and monitor performance reviews',
                                'color' => 'info',
                                // Route not defined yet.
                            ],
                            [
                                'icon' => 'fa-solid fa-check-square',
                                'title' => 'Sign-off Management',
                                'description' => 'Manage approval processes for appraisals',
                                'color' => 'success',
                                // Route not defined yet.
                            ],
                            [
                                'icon' => 'fa-solid fa-chart-pie',
                                'title' => 'Staff Management',
                                'description' => 'Manage the staff database',
                                'color' => 'primary',
                                'route' => 'staff_management.index',
                                // Route not defined yet.
                            ],
                            [
                                'icon' => 'fa-solid fa-chart-pie',
                                'title' => 'Reports & Analytics',
                                'description' => 'Generate and view performance insights',
                                'color' => 'primary',
                                // Route not defined yet.
                            ],
                            // Additional functionalities from our plan:
                            [
                                'icon' => 'fa-solid fa-gears',
                                'title' => 'Appraisal Generation',
                                'route' => 'appraisal_appraisal_generation.index',
                                'description' => 'Automatically generate appraisal header records',
                                'color' => 'info',
                                // Route not defined yet.
                            ],
                            [
                                'icon' => 'fa-solid fa-file-signature',
                                'title' => 'Enter Appraisal Form',
                                'description' => 'Data entry for performance appraisals',
                                'color' => 'info',
                                // Route not defined yet.
                            ],
                            [
                                'icon' => 'fa-solid fa-comments',
                                'title' => '360° Feedback Form',
                                'description' => 'Submit and view 360-degree feedback',
                                'color' => 'warning',
                                // Route not defined yet.
                            ],
                            [
                                'icon' => 'fa-solid fa-paper-plane',
                                'title' => 'Submit Appraisal',
                                'description' => 'Submit appraisal for review',
                                'color' => 'success',
                                // Route not defined yet.
                            ],
                            [
                                'icon' => 'fa-solid fa-lock',
                                'title' => 'Finalize Appraisal',
                                'description' => 'Finalize appraisals after all sign-offs',
                                'color' => 'danger',
                                // Route not defined yet.
                            ],
                            [
                                'icon' => 'fa-solid fa-book',
                                'title' => 'Development Plans',
                                'description' => 'Set and review development objectives',
                                'color' => 'primary',
                                // Route not defined yet.
                            ],
                            [
                                'icon' => 'fa-solid fa-chart-line',
                                'title' => 'Performance Plans',
                                'description' => 'Plan next-cycle performance objectives',
                                'color' => 'secondary',
                                // Route not defined yet.
                            ],
                            [
                                'icon' => 'fa-solid fa-database',
                                'title' => 'Bulk Operations',
                                'description' => 'Mass CRUD operations for configuration data',
                                'color' => 'warning',
                                // Route not defined yet.
                            ],
                            [
                                'icon' => 'fa-solid fa-bell',
                                'title' => 'Automated Notifications',
                                'description' => 'Manage scheduled and escalation notifications',
                                'color' => 'info',
                                // Route not defined yet.
                            ],
                        ];

                    @endphp

                    @foreach ($menuItems as $item)
                        <div class="col-md-4 p-3">
                            <a href="{{ isset($item['route']) ? route($item['route']) : '#' }}"
                                class="card h-100 text-decoration-none text-dark hover-shadow">
                                <div class="card-body d-flex flex-column">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="icon-circle bg-{{ $item['color'] }} text-white me-3">
                                            <i class="{{ $item['icon'] }} fa-fw"></i>
                                        </div>
                                        <h6 class="card-title mb-0">{{ $item['title'] }}</h6>
                                    </div>
                                    <p class="card-text text-muted small flex-grow-1">{{ $item['description'] }}</p>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
    .modal-dialog-scrollable .modal-content {
        max-height: 90vh;
    }

    .icon-circle {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }

    .hover-shadow:hover {
        box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .15) !important;
        transition: box-shadow .3s ease-in-out;
    }

    .card-body {
        display: flex;
        flex-direction: column;he
    }

    .card-title {
        font-size: 1rem;
        line-height: 1.2;
    }
</style>
