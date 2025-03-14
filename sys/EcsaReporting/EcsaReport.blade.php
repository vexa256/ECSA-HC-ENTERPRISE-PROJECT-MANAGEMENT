{{-- Error Modal: This modal is shown automatically if there are errors --}}
@if($errors->any() || session('error'))
    <dialog id="errorModal" class="modal modal-open">
        <div class="modal-box">
            <h3 class="font-bold text-lg">Error</h3>
            <ul class="list-disc ml-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
                @if(session('error'))
                    <li>{{ session('error') }}</li>
                @endif
            </ul>
            <div class="modal-action">
                <button class="btn" onclick="document.getElementById('errorModal').close()">Close</button>
            </div>
        </div>
    </dialog>
@endif

<div class="min-h-screen bg-base-200">
    <!-- Elegant Header with Subtle Gradient -->
    <header class="bg-gradient-to-r from-base-300 to-base-200 py-4 px-6 shadow-sm animate__animated animate__fadeIn">
        <div class="container mx-auto">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <div>
                    <h3 class="text-base font-bold tracking-tight">Performance Indicator Reporting | Report: {{ $timelineName }} |

                        <div class="badge badge-{{ $timelineStatus === 'In Progress' ? 'success' : ($timelineStatus === 'Not Started' ? 'warning' : 'error') }}">
                            {{ $timelineStatus }}
                        </div>
                    </h3>

                    <div class="mt-1 flex flex-wrap gap-1">
                        <span class="alert alert-warning text-xs">
                            SO: {{ $objectiveName }} | Cluster: {{ $clusterName }}
                          </span>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('Ecsa_SelectStrategicObjective', [
                        'UserID' => $UserID,
                        'ClusterID' => $ClusterID,
                        'ReportingID' => $ReportingID
                    ]) }}" class="btn btn-sm btn-ghost flex items-center gap-2">
                        <i class="iconify" data-icon="tabler:arrow-left"></i>
                        Go Back
                    </a>

                    <!-- Analytics button removed as requested -->
                </div>
            </div>
        </div>
    </header>

    <!-- Progress Overview Card -->
    <div class="container mx-auto px-4 py-6">
        <div class="card mb-6 animate__animated animate__fadeInUp animate__delay-1s overflow-hidden">
            <div class="card-body p-0">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-0">
                    <!-- Progress Circle Section -->
                    <div class="bg-gradient-to-br from-primary to-primary-focus p-4 flex items-center">
                        <div class="flex items-center gap-4">
                            <div class="relative">
                                <div class="radial-progress text-red-800"
                                     style="--value:{{ $progressPercentage }}; --size:4.5rem; --thickness: 0.5rem;">
                                    <span class="text-xl font-bold">{{ number_format($progressPercentage, 0) }}%</span>
                                </div>
                                <div class="absolute -top-1 -right-1 size-4 bg-accent rounded-full shadow-lg animate__animated animate__pulse animate__infinite"></div>
                            </div>
                            <div>
                                <h2 class="font-bold text-blue-900">Reporting Progress</h2>
                                <p class="text-sm text-blue-900">{{ $reportedIndicators }} of {{ $totalIndicators }} indicators reported</p>
                            </div>
                        </div>
                    </div>

                    <!-- Stats Section -->
                    <div class="bg-base-100 p-4 flex items-center">
                        <div class="grid grid-cols-2 w-full gap-2">
                            <div class="flex flex-col items-center justify-center p-2 bg-base-200/50 rounded-lg">
                                <span class="text-xs font-medium text-primary-focus uppercase">Reported</span>
                                <span class="text-3xl font-bold text-success">{{ $reportedIndicators }}</span>
                                <span class="text-xs text-base-content/70">Completed</span>
                            </div>
                            <div class="flex flex-col items-center justify-center p-2 bg-base-200/50 rounded-lg">
                                <span class="text-xs font-medium text-primary-focus uppercase">Pending</span>
                                <span class="text-3xl font-bold text-warning">{{ $totalIndicators - $reportedIndicators }}</span>
                                <span class="text-xs text-base-content/70">Awaiting</span>
                            </div>
                        </div>
                    </div>

                    <!-- Actions Section -->
                    <div class="bg-base-100 p-4 flex items-center justify-center border-l border-base-200">
                        <div class="flex gap-2">
                            <button id="bulkActionBtn" class="btn btn-sm btn-error gap-1 shadow-md" disabled>
                                <i class="iconify" data-icon="tabler:ban"></i>
                                Not Applicable
                            </button>
                            <div class="dropdown dropdown-end">
                                <div tabindex="0" role="button" class="btn btn-sm btn-primary shadow-md">
                                    <i class="iconify" data-icon="tabler:dots-vertical"></i>
                                </div>
                                <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-box w-52">
                                    <li><a id="exportBtn"><i class="iconify" data-icon="tabler:file-export"></i> Export Data</a></li>
                                    <li><a id="printBtn"><i class="iconify" data-icon="tabler:printer"></i> Print Report</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Indicators Wizard Card -->
        <div class="card bg-base-100 shadow-md animate__animated animate__fadeInUp animate__delay-2s">
            <div class="card-body">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
                    <h2 class="card-title">Performance Indicators</h2>
                    <div class="join">
                        <div class="form-control join-item">
                            <div class="input-group input-group-sm">
                                <input type="text" id="searchIndicators" placeholder="Search indicators..."
                                       class="input input-sm input-bordered w-full md:w-auto" />

                            </div>
                        </div>
                    </div>
                </div>

                <form id="bulkNotApplicableForm" action="{{ route('MarkIndicatorsNotApplicable') }}" method="POST">
                    @csrf
                    <input type="hidden" name="UserID" value="{{ $UserID }}">
                    <input type="hidden" name="ClusterID" value="{{ $ClusterID }}">
                    <input type="hidden" name="ReportingID" value="{{ $ReportingID }}">
                    <input type="hidden" name="StrategicObjectiveID" value="{{ $StrategicObjectiveID }}">

                    @php $indicatorsPerPage = 3; @endphp

                    <!-- Wizard Navigation -->
                    <div class="tabs tabs-boxed bg-base-200 mb-4 justify-center">
                        <a class="tab tab-active" data-page="1">Page 1</a>
                        @php
                            $totalIndicators = count($indicators);
                            $totalPages = $totalIndicators > 0 ? ceil($totalIndicators / $indicatorsPerPage) : 1;
                            for ($i = 2; $i <= $totalPages; $i++) {
                                echo '<a class="tab" data-page="' . $i . '">Page ' . $i . '</a>';
                            }
                        @endphp
                    </div>

                    <!-- Wizard Pages Container -->
                    <div id="indicatorPages">
                        @for ($page = 1; $page <= $totalPages; $page++)
                            <div class="indicator-page animate__animated animate__fadeIn"
                                 data-page="{{ $page }}" style="{{ $page > 1 ? 'display: none;' : '' }}">
                                @php
                                    $startIndex = ($page - 1) * $indicatorsPerPage;
                                    $endIndex = min($startIndex + $indicatorsPerPage - 1, $totalIndicators - 1);
                                @endphp

                                @if ($startIndex <= $endIndex)
                                    @for ($i = $startIndex; $i <= $endIndex; $i++)
                                        @php $indicator = $indicators[$i]; @endphp
                                        <div class="card bg-base-200 mb-4 transition-all duration-300 hover:shadow-md">
                                            <div class="card-body p-4">
                                                <div class="flex items-start gap-3">
                                                    <label class="flex items-center h-6">
                                                        <input type="checkbox"
                                                               class="checkbox checkbox-sm indicator-checkbox"
                                                               name="IndicatorIDs[]" value="{{ $indicator->id }}"
                                                               {{ isset($existingReports[$indicator->id]) && $existingReports[$indicator->id]->Response === 'Not Applicable' ? 'disabled' : '' }}>
                                                    </label>
                                                    <div class="flex-1">
                                                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                                                            <div>
                                                                <h3 class="font-medium">{{ $indicator->Indicator_Name }}</h3>
                                                                <div class="text-sm opacity-70 font-mono">{{ $indicator->Indicator_Number }}</div>
                                                            </div>
                                                            <div class="flex items-center gap-2">
                                                                @if (isset($existingReports[$indicator->id]))
                                                                    @if ($existingReports[$indicator->id]->Response === 'Not Applicable')
                                                                        <span class="badge badge-neutral badge-sm gap-1">
                                                                            <i class="iconify" data-icon="tabler:ban"></i>
                                                                            Not Applicable
                                                                        </span>
                                                                    @else
                                                                        <span class="badge badge-success badge-sm gap-1">
                                                                            <i class="iconify" data-icon="tabler:check"></i>
                                                                            Reported
                                                                        </span>
                                                                    @endif
                                                                @else
                                                                    <span class="badge badge-warning badge-sm gap-1">
                                                                        <i class="iconify" data-icon="tabler:clock"></i>
                                                                        Pending
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div class="divider my-2"></div>
                                                        <div class="flex flex-wrap gap-2 justify-end">
                                                            <button type="button"
                                                                    class="btn btn-sm btn-primary gap-1 transition-all duration-300 hover:scale-105"
                                                                    data-indicator-id="{{ $indicator->id }}"
                                                                    data-indicator-name="{{ $indicator->Indicator_Name }}"
                                                                    data-indicator-number="{{ $indicator->Indicator_Number }}"
                                                                    data-response-type="{{ $indicator->ResponseType }}"
                                                                    data-baseline="{{ $indicator->Baseline2024 }}"
                                                                    data-needs-baseline="{{ is_null($indicator->Baseline2024) && $indicator->ResponseType === 'Number' ? 'true' : 'false' }}"
                                                                    data-existing-response="{{ isset($existingReports[$indicator->id]) ? $existingReports[$indicator->id]->Response : '' }}"
                                                                    data-existing-comment="{{ isset($existingReports[$indicator->id]) ? $existingReports[$indicator->id]->ReportingComment : '' }}"
                                                                    onclick="window.my_modal_report.showModal(); openReportModal(this)">
                                                                <i class="iconify" data-icon="tabler:edit"></i>
                                                                {{ isset($existingReports[$indicator->id]) ? 'Edit' : 'Report' }}
                                                            </button>
                                                            @if (isset($existingReports[$indicator->id]))
                                                                <button type="button"
                                                                        class="btn btn-sm btn-ghost gap-1 transition-all duration-300 hover:scale-105"
                                                                        data-indicator-id="{{ $indicator->id }}"
                                                                        data-indicator-name="{{ $indicator->Indicator_Name }}"
                                                                        data-indicator-number="{{ $indicator->Indicator_Number }}"
                                                                        data-response-type="{{ $indicator->ResponseType }}"
                                                                        data-existing-response="{{ $existingReports[$indicator->id]->Response }}"
                                                                        data-existing-comment="{{ $existingReports[$indicator->id]->ReportingComment }}"
                                                                        data-reporter-name="{{ $existingReports[$indicator->id]->reporter_name }}"
                                                                        data-reporter-email="{{ $existingReports[$indicator->id]->reporter_email }}"
                                                                        data-reported-at="{{ $existingReports[$indicator->id]->updated_at }}"
                                                                        onclick="window.my_modal_details.showModal(); openDetailsModal(this)">
                                                                    <i class="iconify" data-icon="tabler:info-circle"></i>
                                                                    Details
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endfor
                                @else
                                    <div class="alert alert-info">
                                        <i class="iconify" data-icon="tabler:info-circle"></i>
                                        No indicators found for this page.
                                    </div>
                                @endif
                                <!-- Pagination Controls -->
                                <div class="flex justify-between mt-4">
                                    <button type="button" class="btn btn-sm btn-ghost gap-1 prev-page transition-all duration-300" {{ $page == 1 ? 'disabled' : '' }}>
                                        <i class="iconify" data-icon="tabler:chevron-left"></i> Previous
                                    </button>
                                    <span class="flex items-center text-sm">Page {{ $page }} of {{ $totalPages }}</span>
                                    <button type="button" class="btn btn-sm btn-ghost gap-1 next-page transition-all duration-300" {{ $page == $totalPages ? 'disabled' : '' }}>
                                        Next <i class="iconify" data-icon="tabler:chevron-right"></i>
                                    </button>
                                </div>
                            </div>
                        @endfor
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Report Modal with Baseline Input (if missing baseline) -->
<dialog id="my_modal_report" class="modal modal-full">
    <div class="modal-box w-full h-full max-w-none rounded-none animate__animated animate__fadeIn">
        <div class="modal-header">
            <div class="flex justify-between items-center">
                <h3 class="font-bold text-lg modal-title"></h3>
                <button class="btn btn-sm btn-circle btn-ghost" onclick="my_modal_report.close()">
                    <i class="iconify" data-icon="tabler:x"></i>
                </button>
            </div>
        </div>
        <div class="modal-content">
            <form id="reportForm" action="{{ route('Ecsa_SavePerformanceReport') }}" method="POST">
                @csrf
                <input type="hidden" name="UserID" value="{{ $UserID }}">
                <input type="hidden" name="ClusterID" value="{{ $ClusterID }}">
                <input type="hidden" name="ReportingID" value="{{ $ReportingID }}">
                <input type="hidden" name="StrategicObjectiveID" value="{{ $StrategicObjectiveID }}">
                <input type="hidden" name="IndicatorID" id="modalIndicatorID">
                <input type="hidden" name="ResponseType" id="modalResponseType">
                <div class="space-y-4 max-w-3xl mx-auto">
                    <div class="card bg-base-200 animate__animated animate__fadeInUp animate__delay-1s">
                        <div class="card-body p-4">
                            <h3 id="modalIndicatorName" class="text-base font-medium"></h3>
                            <p id="modalIndicatorNumber" class="text-sm opacity-70"></p>
                            <div class="mt-2">
                                <span class="text-sm">Response Type:</span>
                                <span id="modalResponseTypeDisplay" class="badge badge-sm badge-primary ml-2"></span>
                            </div>
                        </div>
                    </div>
                    <div class="form-control animate__animated animate__fadeInUp animate__delay-2s">
                        <label class="label">
                            <span class="label-text">Response</span>
                        </label>
                        <div id="responseInputContainer"></div>
                    </div>
                    <!-- Baseline Input Container (shown only if baseline is missing) -->
                    <div id="baselineInputContainer" class="form-control animate__animated animate__fadeInUp animate__delay-2s" style="display: none;">
                        <label class="label">
                            <span class="label-text">Baseline Value (2024)</span>
                            <span class="label-text-alt text-warning">Required</span>
                        </label>
                        <input type="number" name="Baseline" id="baselineInput" class="input input-bordered" step="any" />
                        <label class="label">
                            <span class="label-text-alt text-info">This indicator is missing baseline data. Please provide the baseline value.</span>
                        </label>
                    </div>
                    <div class="form-control animate__animated animate__fadeInUp animate__delay-3s">
                        <label class="label">
                            <span class="label-text">Comment</span>
                        </label>
                        <textarea class="textarea textarea-bordered" name="Comment" rows="3" id="modalComment"></textarea>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-action">
            <button class="btn btn-sm btn-ghost" onclick="my_modal_report.close()">Cancel</button>
            @if ($timelineStatus === 'In Progress')
                <button class="btn btn-sm btn-primary" onclick="submitReportForm()">Save Report</button>
            @else
                <button class="btn btn-sm btn-error" disabled>Report Closed</button>
            @endif
        </div>
    </div>
</dialog>

<!-- Details Modal -->
<dialog id="my_modal_details" class="modal modal-full">
    <div class="modal-box w-full h-full max-w-none rounded-none animate__animated animate__fadeIn">
        <div class="modal-header">
            <div class="flex justify-between items-center">
                <h3 class="font-bold text-lg">Indicator Details</h3>
                <button class="btn btn-sm btn-circle btn-ghost" onclick="my_modal_details.close()">
                    <i class="iconify" data-icon="tabler:x"></i>
                </button>
            </div>
        </div>
        <div class="modal-content">
            <div class="max-w-3xl mx-auto">
                <div class="card bg-base-200 mb-4 animate__animated animate__fadeInUp animate__delay-1s">
                    <div class="card-body p-4">
                        <h3 id="detailsIndicatorName" class="text-base font-medium"></h3>
                        <p id="detailsIndicatorNumber" class="text-sm opacity-70"></p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div class="stats bg-base-100 shadow-sm animate__animated animate__fadeInUp animate__delay-2s">
                        <div class="stat p-3">
                            <div class="stat-title text-xs">Response Type</div>
                            <div id="detailsResponseType" class="stat-value text-base"></div>
                        </div>
                    </div>
                    <div class="stats bg-base-100 shadow-sm animate__animated animate__fadeInUp animate__delay-2s">
                        <div class="stat p-3">
                            <div class="stat-title text-xs">Response</div>
                            <div id="detailsResponse" class="stat-value text-base"></div>
                        </div>
                    </div>
                    <div class="stats bg-base-100 shadow-sm md:col-span-2 animate__animated animate__fadeInUp animate__delay-3s">
                        <div class="stat p-3">
                            <div class="stat-title text-xs">Comment</div>
                            <div id="detailsComment" class="tat-desc text-s"></div>
                        </div>
                    </div>
                    <div class="stats bg-base-100 shadow-sm animate__animated animate__fadeInUp animate__delay-4s">
                        <div class="stat p-3">
                            <div class="stat-title text-xs">Reported By</div>
                            <div id="detailsReporterName" class="stat-value text-base"></div>
                            <div id="detailsReporterEmail" class="stat-desc text-xs"></div>
                        </div>
                    </div>
                    <div class="stats bg-base-100 shadow-sm animate__animated animate__fadeInUp animate__delay-4s">
                        <div class="stat p-3">
                            <div class="stat-title text-xs">Reported At</div>
                            <div id="detailsReportedAt" class="stat-value text-base"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-action">
            <button class="btn btn-sm btn-neutral" onclick="my_modal_details.close()">Close</button>
        </div>
    </div>
</dialog>

<!-- Iconify CDN -->
<script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>

<!-- Core JS for Theme, Pagination, Checkbox Handling, Report and Details Modals -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Theme handling
    // const themeToggleBtn = document.getElementById('themeToggleBtn');
    // themeToggleBtn.addEventListener('click', function() {
    //     const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
    //     const newTheme = currentTheme === 'light' ? 'dark' : 'light';
    //     document.documentElement.setAttribute('data-theme', newTheme);
    //     localStorage.setItem('theme', newTheme);
    //     const icon = themeToggleBtn.querySelector('.iconify');
    //     icon.setAttribute('data-icon', newTheme === 'light' ? 'tabler:sun' : 'tabler:moon');
    // });
    // const savedTheme = localStorage.getItem('theme') || 'light';
    // document.documentElement.setAttribute('data-theme', savedTheme);
    // const themeIcon = themeToggleBtn.querySelector('.iconify');
    // themeIcon.setAttribute('data-icon', savedTheme === 'light' ? 'tabler:sun' : 'tabler:moon');

    // Wizard pagination
    const tabs = document.querySelectorAll('.tab');
    const pages = document.querySelectorAll('.indicator-page');
    const prevButtons = document.querySelectorAll('.prev-page');
    const nextButtons = document.querySelectorAll('.next-page');

    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const pageNum = this.getAttribute('data-page');
            showPage(pageNum);
        });
    });
    prevButtons.forEach(button => {
        button.addEventListener('click', function() {
            const currentPage = document.querySelector('.indicator-page:not([style*="display: none"])');
            if (currentPage) {
                const currentPageNum = parseInt(currentPage.getAttribute('data-page'));
                if (currentPageNum > 1) { showPage(currentPageNum - 1); }
            } else { showPage(1); }
        });
    });
    nextButtons.forEach(button => {
        button.addEventListener('click', function() {
            const currentPage = document.querySelector('.indicator-page:not([style*="display: none"])');
            if (currentPage) {
                const currentPageNum = parseInt(currentPage.getAttribute('data-page'));
                const maxPage = document.querySelectorAll('.indicator-page').length;
                if (currentPageNum < maxPage) { showPage(currentPageNum + 1); }
            } else { showPage(1); }
        });
    });
    function showPage(pageNum) {
        pageNum = parseInt(pageNum);
        tabs.forEach(tab => {
            tab.classList.toggle('tab-active', parseInt(tab.getAttribute('data-page')) === pageNum);
        });
        pages.forEach(page => {
            const pageNumber = parseInt(page.getAttribute('data-page'));
            if (pageNumber === pageNum) {
                page.style.display = '';
                page.classList.add('animate__animated', 'animate__fadeIn');
                setTimeout(() => { page.classList.remove('animate__animated', 'animate__fadeIn'); }, 1000);
            } else { page.style.display = 'none'; }
        });
        window.location.hash = `page-${pageNum}`;
    }

    // Checkbox handling
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.indicator-checkbox:not([disabled])');
    const bulkActionBtn = document.getElementById('bulkActionBtn');
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(checkbox => { checkbox.checked = this.checked; });
            updateButtonState();
        });
    }
    checkboxes.forEach(checkbox => { checkbox.addEventListener('change', updateButtonState); });
    function updateButtonState() {
        const checkedBoxes = document.querySelectorAll('.indicator-checkbox:checked');
        const allChecked = checkboxes.length > 0 && Array.from(checkboxes).every(chk => chk.checked);
        const someChecked = checkedBoxes.length > 0;
        if (selectAll) {
            selectAll.checked = allChecked;
            selectAll.indeterminate = someChecked && !allChecked;
        }
        bulkActionBtn.disabled = !someChecked;
    }
    bulkActionBtn.addEventListener('click', function() {
        if (!this.disabled && confirm('Are you sure you want to mark the selected indicators as Not Applicable?')) {
            document.getElementById('bulkNotApplicableForm').submit();
        }
    });
    const searchInput = document.getElementById('searchIndicators');
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const indicators = document.querySelectorAll('.card.bg-base-200');
        let foundAny = false;
        let foundOnPage = {};
        indicators.forEach(indicator => {
            const name = indicator.querySelector('h3').textContent.toLowerCase();
            const number = indicator.querySelector('.font-mono').textContent.toLowerCase();
            const page = indicator.closest('.indicator-page');
            const pageNum = page ? page.getAttribute('data-page') : null;
            if (name.includes(searchTerm) || number.includes(searchTerm)) {
                indicator.style.display = '';
                foundAny = true;
                if (pageNum) { foundOnPage[pageNum] = true; }
            } else { indicator.style.display = 'none'; }
        });
        if (searchTerm === '') {
            indicators.forEach(indicator => { indicator.style.display = ''; });
            showPage('1');
        } else if (foundAny) {
            const firstPageWithResults = Object.keys(foundOnPage)[0] || '1';
            showPage(firstPageWithResults);
        }
        const noResultsMsg = document.getElementById('noResultsMsg');
        if (!foundAny && searchTerm !== '') {
            if (!noResultsMsg) {
                const msg = document.createElement('div');
                msg.id = 'noResultsMsg';
                msg.className = 'alert alert-warning mt-4 animate__animated animate__fadeIn';
                msg.innerHTML = '<i class="iconify" data-icon="tabler:search-off"></i> No indicators found matching "' + searchTerm + '"';
                document.querySelector('#indicatorPages').appendChild(msg);
            }
        } else if (noResultsMsg) { noResultsMsg.remove(); }
    });
    updateButtonState();
    const buttons = document.querySelectorAll('.btn:not(.btn-circle)');
    buttons.forEach(button => { button.classList.add('transition-all', 'duration-300', 'hover:-translate-y-1'); });
});

function openReportModal(button) {
    const indicatorId = button.getAttribute('data-indicator-id');
    const indicatorName = button.getAttribute('data-indicator-name');
    const indicatorNumber = button.getAttribute('data-indicator-number');
    const responseType = button.getAttribute('data-response-type');
    const existingResponse = button.getAttribute('data-existing-response');
    const existingComment = button.getAttribute('data-existing-comment');
    const baseline = button.getAttribute('data-baseline');
    const needsBaseline = button.getAttribute('data-needs-baseline') === 'true';
    const modalTitle = document.querySelector('.modal-title');
    const modalIndicatorName = document.querySelector('#modalIndicatorName');
    const modalIndicatorNumber = document.querySelector('#modalIndicatorNumber');
    const modalIndicatorID = document.querySelector('#modalIndicatorID');
    const modalResponseType = document.querySelector('#modalResponseType');
    const modalResponseTypeDisplay = document.querySelector('#modalResponseTypeDisplay');
    const modalComment = document.querySelector('#modalComment');
    const responseInputContainer = document.querySelector('#responseInputContainer');
    const baselineInputContainer = document.getElementById('baselineInputContainer');
    const baselineInput = document.getElementById('baselineInput');

    modalTitle.textContent = `Report Indicator ${indicatorNumber}`;
    modalIndicatorName.textContent = indicatorName;
    modalIndicatorNumber.textContent = `Indicator Number: ${indicatorNumber}`;
    modalIndicatorID.value = indicatorId;
    modalResponseType.value = responseType;
    modalResponseTypeDisplay.textContent = responseType;
    modalComment.value = existingComment || '';

    // Show baseline input only if needed
    if (needsBaseline && responseType === 'Number') {
        baselineInputContainer.style.display = '';
        baselineInput.required = true;
    } else {
        baselineInputContainer.style.display = 'none';
        baselineInput.required = false;
    }

    // Clear previous response input
    responseInputContainer.innerHTML = '';
    let inputElement;
    switch (responseType) {
        case 'Text':
            inputElement = document.createElement('textarea');
            inputElement.className = 'textarea textarea-bordered w-full animate__animated animate__fadeIn';
            inputElement.name = 'Response';
            inputElement.rows = '3';
            break;
        case 'Number':
            inputElement = document.createElement('input');
            inputElement.type = 'number';
            inputElement.className = 'input input-bordered w-full animate__animated animate__fadeIn';
            inputElement.name = 'Response';
            inputElement.step = 'any';
            break;
        case 'Boolean':
        case 'Yes/No':
            inputElement = document.createElement('select');
            inputElement.className = 'select select-bordered w-full animate__animated animate__fadeIn';
            inputElement.name = 'Response';
            const options = responseType === 'Boolean' ? ['True', 'False'] : ['Yes', 'No'];
            options.forEach(option => {
                const optionElement = document.createElement('option');
                optionElement.value = option;
                optionElement.textContent = option;
                inputElement.appendChild(optionElement);
            });
            break;
        default:
            inputElement = document.createElement('input');
            inputElement.type = 'text';
            inputElement.className = 'input input-bordered w-full animate__animated animate__fadeIn';
            inputElement.name = 'Response';
    }
    if (existingResponse) { inputElement.value = existingResponse; }
    responseInputContainer.appendChild(inputElement);
}

function submitReportForm() {
    // Validate required fields before submission.
    const responseInput = document.querySelector('#responseInputContainer input, #responseInputContainer textarea, #responseInputContainer select');
    if (!responseInput || !responseInput.value.trim()) {
        alert('Please provide a response.');
        if(responseInput) responseInput.focus();
        return;
    }
    const baselineInputContainer = document.getElementById('baselineInputContainer');
    const baselineInput = document.getElementById('baselineInput');
    if (baselineInputContainer.style.display !== 'none' && (!baselineInput.value || baselineInput.value.trim() === '')) {
        alert('Please provide a baseline value.');
        baselineInput.focus();
        return;
    }
    const form = document.getElementById('reportForm');
    form.classList.add('animate__animated', 'animate__fadeOutUp');
    setTimeout(() => { form.submit(); }, 300);
}

function openDetailsModal(button) {
    const indicatorName = button.getAttribute('data-indicator-name');
    const indicatorNumber = button.getAttribute('data-indicator-number');
    const responseType = button.getAttribute('data-response-type');
    const existingResponse = button.getAttribute('data-existing-response');
    const existingComment = button.getAttribute('data-existing-comment');
    const reporterName = button.getAttribute('data-reporter-name');
    const reporterEmail = button.getAttribute('data-reporter-email');
    const reportedAt = button.getAttribute('data-reported-at');
    document.querySelector('#detailsIndicatorName').textContent = indicatorName;
    document.querySelector('#detailsIndicatorNumber').textContent = `Indicator Number: ${indicatorNumber}`;
    document.querySelector('#detailsResponseType').textContent = responseType;
    document.querySelector('#detailsResponse').textContent = existingResponse;
    document.querySelector('#detailsComment').textContent = existingComment || 'No comment provided';
    document.querySelector('#detailsReporterName').textContent = reporterName;
    document.querySelector('#detailsReporterEmail').textContent = reporterEmail;
    document.querySelector('#detailsReportedAt').textContent = new Date(reportedAt).toLocaleString();
}
</script>

<!-- Fix for modal header/footer obstruction and custom styles -->
<style>
    .modal-full .modal-box {
        max-width: 100% !important;
        width: 100% !important;
        height: 100vh !important;
        border-radius: 0 !important;
        margin: 0 !important;
        display: flex;
        flex-direction: column;
    }
    .modal-full .modal-box .modal-header {
        position: sticky;
        top: 0;
        background-color: hsl(var(--b1));
        z-index: 10;
        padding: 1rem;
        border-bottom: 1px solid hsl(var(--border));
    }
    .modal-full .modal-box .modal-content {
        flex: 1;
        overflow-y: auto;
        padding: 1rem;
        padding-bottom: 4rem;
    }
    .modal-full .modal-box .modal-action {
        position: sticky;
        bottom: 0;
        background-color: hsl(var(--b1));
        z-index: 10;
        padding: 1rem;
        border-top: 1px solid hsl(var(--border));
        width: 100%;
    }
    .indicator-page { transition: all 0.3s ease-in-out; }
    .card.bg-base-200:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1),
                    0 4px 6px -2px rgba(0,0,0,0.05);
    }
    .btn { transition: all 0.3s ease; }
    .btn:hover:not([disabled]) { transform: translateY(-2px); }
    .modal { z-index: 1000; }
</style>
