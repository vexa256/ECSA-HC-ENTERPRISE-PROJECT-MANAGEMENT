<style>
    /* iOS-style card */
    .ios-card {
        background-color: #ffffff;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }

    /* iOS-style header */
    .ios-header {
        font-size: 1.75rem;
        font-weight: 700;
        margin-bottom: 1rem;
    }

    /* iOS-style list */
    .ios-list {
        background-color: #ffffff;
        border-radius: 10px;
        overflow: hidden;
    }

    .ios-list-item {
        padding: 1rem;
        border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .ios-list-item:last-child {
        border-bottom: none;
    }

    .ios-list-item:active {
        background-color: #e5e5ea;
    }

    /* iOS-style button */
    .ios-button {
        background-color: #007aff;
        color: white;
        border-radius: 6px;
        font-weight: 600;
        padding: 0.75rem 1.5rem;
        transition: all 0.2s ease;
        border: none;
        outline: none;
        cursor: pointer;
        text-align: center;
        width: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .ios-button:active {
        opacity: 0.8;
        transform: scale(0.98);
    }

    /* Radio button styling */
    .ios-radio {
        appearance: none;
        width: 20px;
        height: 20px;
        border: 2px solid #d1d5db;
        border-radius: 50%;
        margin-right: 12px;
        position: relative;
        transition: all 0.2s ease;
    }

    .ios-radio:checked {
        border-color: #007aff;
        background-color: #ffffff;
    }

    .ios-radio:checked::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background-color: #007aff;
    }

    /* Status badges */
    .ios-badge {
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .ios-badge-pending {
        background-color: #e5e5ea;
        color: #8e8e93;
    }

    .ios-badge-in-progress {
        background-color: #ffcc00;
        color: #856404;
    }

    .ios-badge-completed {
        background-color: #34c759;
        color: #155724;
    }

    /* Timeline type badges */
    .ios-timeline-type {
        font-size: 0.75rem;
        padding: 0.125rem 0.375rem;
        border-radius: 4px;
        background-color: #e5e5ea;
        color: #8e8e93;
        margin-right: 0.5rem;
    }

    /* Info card */
    .ios-info-card {
        background-color: rgba(0, 122, 255, 0.1);
        border-radius: 10px;
        padding: 1rem;
        margin-top: 1.5rem;
    }

    .ios-info-title {
        color: #0056b3;
        font-weight: 600;
        margin-bottom: 0.25rem;
    }

    .ios-info-text {
        color: #0069d9;
        font-size: 0.875rem;
    }

    /* Selected cluster pill */
    .ios-selected-pill {
        display: inline-flex;
        align-items: center;
        background-color: #e5e5ea;
        border-radius: 16px;
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
        font-weight: 500;
        color: #3a3a3c;
        margin-bottom: 1rem;
    }
</style>

<div class="ios-card p-4 mb-6">
    <h2 class="ios-header">Select Timeline</h2>

    <div class="ios-selected-pill">
        <i class="iconify mr-1" data-icon="heroicons-solid:collection"></i>
        All Clusters (Combined View)
    </div>

    <p class="text-gray-600 mb-4">
        Please select a reporting timeline to view combined performance indicators and metrics across all clusters.
    </p>
</div>

@if(isset($Timelines) && count($Timelines) > 0)
<form action="{{ route('V2_ALL_performance.timeline.selection.process') }}" method="POST">
    @csrf
    <div class="ios-list mb-6">
        @foreach($Timelines as $timeline)
            <label class="ios-list-item cursor-pointer">
                <div class="flex items-center">
                    <input type="radio" name="timeline_id" value="{{ $timeline->ReportingID }}" class="ios-radio" required>
                    <div>
                        <div class="font-semibold">{{ $timeline->ReportName }}</div>
                        <div class="flex items-center mt-1">
                            <span class="ios-timeline-type">{{ $timeline->Type }}</span>
                            <span class="text-sm text-gray-500">Year: {{ $timeline->Year }}</span>
                        </div>
                        <div class="text-sm text-gray-500 mt-1">
                            Closing Date: {{ \Carbon\Carbon::parse($timeline->ClosingDate)->format('M d, Y') }}
                        </div>
                    </div>
                </div>
                <div class="flex items-center">
                    @if($timeline->status == 'Pending')
                        <span class="ios-badge ios-badge-pending mr-2">{{ $timeline->status }}</span>
                    @elseif($timeline->status == 'In Progress')
                        <span class="ios-badge ios-badge-in-progress mr-2">{{ $timeline->status }}</span>
                    @elseif($timeline->status == 'Completed')
                        <span class="ios-badge ios-badge-completed mr-2">{{ $timeline->status }}</span>
                    @endif
                    <i class="iconify text-gray-400" data-icon="heroicons-solid:chevron-right"></i>
                </div>
            </label>
        @endforeach
    </div>

    <button type="submit" class="ios-button">
        Continue to Dashboard
        <i class="iconify ml-1" data-icon="heroicons-solid:chart-bar"></i>
    </button>
</form>

@else
    <div class="ios-card p-6 text-center">
        <i class="iconify text-gray-400 text-6xl mb-4" data-icon="heroicons-solid:calendar"></i>
        <h3 class="text-lg font-semibold mb-2">No Timelines Available</h3>
        <p class="text-gray-600">
            There are no reporting timelines available in the system. Please contact the administrator.
        </p>
    </div>
@endif

<!-- iOS-style info card -->
<div class="ios-info-card" style="display: none">
    <div class="flex items-start">
        <i class="iconify text-blue-500 mr-3 text-xl flex-shrink-0" data-icon="heroicons-solid:information-circle"></i>
        <div>
            <h3 class="ios-info-title">About Combined Performance View</h3>
            <p class="ios-info-text">
                This view shows aggregated performance data across all clusters, providing a comprehensive overview of the entire organization's performance during the selected reporting period.
            </p>
        </div>
    </div>
</div>

<!-- Include Iconify library -->
<script src="https://code.iconify.design/2/2.2.1/iconify.min.js"></script>

<!-- JavaScript for iOS-like interactions -->
<script>
    // Add touch feedback to list items
    document.addEventListener('DOMContentLoaded', function() {
        const listItems = document.querySelectorAll('.ios-list-item');

        listItems.forEach(item => {
            item.addEventListener('touchstart', function() {
                this.style.backgroundColor = '#e5e5ea';
            });

            item.addEventListener('touchend', function() {
                this.style.backgroundColor = '#ffffff';
            });
        });

        // Add ripple effect to button
        const button = document.querySelector('.ios-button');

        button.addEventListener('touchstart', function() {
            this.style.opacity = '0.8';
            this.style.transform = 'scale(0.98)';
        });

        button.addEventListener('touchend', function() {
            this.style.opacity = '1';
            this.style.transform = 'scale(1)';
        });
    });
</script>
