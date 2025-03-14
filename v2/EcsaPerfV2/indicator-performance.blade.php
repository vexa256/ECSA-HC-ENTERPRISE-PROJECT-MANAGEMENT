<style>
    /* Premium iOS 2025 Design System */
    :root {
        /* Typography */
        --font-primary: -apple-system, BlinkMacSystemFont, 'SF Pro Text', 'SF Pro Display', 'Helvetica Neue', sans-serif;

        /* Semantic Colors */
        --color-background-primary: #ffffff;
        --color-background-secondary: #f5f7fa;
        --color-background-tertiary: #edf0f5;

        --color-fill-primary: rgba(120, 120, 128, 0.2);
        --color-fill-secondary: rgba(120, 120, 128, 0.16);
        --color-fill-tertiary: rgba(120, 120, 128, 0.12);

        --color-label-primary: #1c2033;
        --color-label-secondary: rgba(28, 32, 51, 0.7);
        --color-label-tertiary: rgba(28, 32, 51, 0.4);

        --color-separator: rgba(60, 60, 67, 0.1);

        /* Premium Gradients */
        --gradient-blue: linear-gradient(135deg, #0a84ff 0%, #0055ff 100%);
        --gradient-green: linear-gradient(135deg, #34c759 0%, #30b650 100%);
        --gradient-orange: linear-gradient(135deg, #ff9500 0%, #ff7d00 100%);
        --gradient-red: linear-gradient(135deg, #ff3b30 0%, #e0302a 100%);
        --gradient-yellow: linear-gradient(135deg, #ffcc00 0%, #ffb700 100%);
        --gradient-purple: linear-gradient(135deg, #af52de 0%, #9f3fd0 100%);
        --gradient-teal: linear-gradient(135deg, #5ac8fa 0%, #32ade1 100%);
        --gradient-gray: linear-gradient(135deg, #8e8e93 0%, #7a7a80 100%);

        /* Functional Colors */
        --color-blue: #0a84ff;
        --color-green: #34c759;
        --color-orange: #ff9500;
        --color-red: #ff3b30;
        --color-yellow: #ffcc00;
        --color-purple: #af52de;
        --color-teal: #5ac8fa;
        --color-gray: #8e8e93;

        /* Status Colors */
        --status-not-performing: var(--color-red);
        --status-in-progress: var(--color-yellow);
        --status-on-track: var(--color-green);
        --status-met: #30d158; /* Slightly darker green */
        --status-over-achieved: var(--color-purple);
        --status-qualitative: var(--color-teal);
        --status-not-available: var(--color-gray);

        /* Spacing */
        --spacing-xs: 4px;
        --spacing-sm: 8px;
        --spacing-md: 16px;
        --spacing-lg: 24px;
        --spacing-xl: 32px;
        --spacing-xxl: 48px;

        /* Radius */
        --radius-sm: 8px;
        --radius-md: 12px;
        --radius-lg: 16px;
        --radius-xl: 24px;

        /* Shadows */
        --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.05);
        --shadow-md: 0 4px 16px rgba(0, 0, 0, 0.08);
        --shadow-lg: 0 8px 24px rgba(0, 0, 0, 0.12);
        --shadow-xl: 0 12px 32px rgba(0, 0, 0, 0.16);

        /* Premium Shadows */
        --shadow-premium-sm: 0 2px 6px rgba(0, 0, 0, 0.04), 0 4px 12px rgba(0, 0, 0, 0.02);
        --shadow-premium-md: 0 4px 12px rgba(0, 0, 0, 0.04), 0 8px 24px rgba(0, 0, 0, 0.03);
        --shadow-premium-lg: 0 8px 24px rgba(0, 0, 0, 0.04), 0 16px 32px rgba(0, 0, 0, 0.04);
    }

    /* Dark Mode Support */
    @media (prefers-color-scheme: dark) {
        :root {
            --color-background-primary: #121219;
            --color-background-secondary: #1c1c24;
            --color-background-tertiary: #2c2c36;

            --color-fill-primary: rgba(120, 120, 128, 0.36);
            --color-fill-secondary: rgba(120, 120, 128, 0.32);
            --color-fill-tertiary: rgba(120, 120, 128, 0.24);

            --color-label-primary: #ffffff;
            --color-label-secondary: rgba(235, 235, 245, 0.7);
            --color-label-tertiary: rgba(235, 235, 245, 0.4);

            --color-separator: rgba(84, 84, 88, 0.6);
        }
    }

    /* Base Styles */
    body {
        font-family: var(--font-primary);
        color: var(--color-label-primary);
        background-color: var(--color-background-secondary);
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }

    /* Premium iOS Card */
    .ios-card {
        background-color: var(--color-background-primary);
        border-radius: var(--radius-md);
        box-shadow: var(--shadow-premium-md);
        overflow: hidden;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        border: 1px solid rgba(0, 0, 0, 0.03);
    }

    .ios-card:active {
        transform: scale(0.98);
        box-shadow: var(--shadow-premium-sm);
    }

    /* iOS Typography */
    .ios-header {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: var(--spacing-md);
        color: var(--color-label-primary);
        line-height: 1.2;
    }

    .ios-subheader {
        font-size: 20px;
        font-weight: 600;
        margin-bottom: var(--spacing-sm);
        color: var(--color-label-primary);
        line-height: 1.3;
    }

    .ios-text {
        font-size: 16px;
        line-height: 1.4;
        color: var(--color-label-secondary);
    }

    .ios-text-small {
        font-size: 14px;
        line-height: 1.4;
        color: var(--color-label-secondary);
    }

    .ios-text-xs {
        font-size: 12px;
        line-height: 1.4;
        color: var(--color-label-tertiary);
    }

    /* iOS Button */
    .ios-button {
        background: var(--gradient-blue);
        color: white;
        border-radius: var(--radius-sm);
        font-weight: 600;
        font-size: 16px;
        padding: var(--spacing-sm) var(--spacing-lg);
        transition: all 0.2s ease;
        border: none;
        outline: none;
        cursor: pointer;
        text-align: center;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 44px;
        box-shadow: var(--shadow-premium-sm);
    }

    .ios-button:active {
        opacity: 0.9;
        transform: scale(0.98);
        box-shadow: var(--shadow-sm);
    }

    .ios-button-secondary {
        background: var(--color-background-tertiary);
        color: var(--color-label-primary);
    }

    /* Status Badges */
    .ios-badge {
        padding: var(--spacing-xs) var(--spacing-sm);
        border-radius: var(--radius-sm);
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        display: inline-block;
        box-shadow: var(--shadow-sm);
    }

    .status-not-performing {
        background: var(--gradient-red);
        color: white;
    }

    .status-in-progress {
        background: var(--gradient-yellow);
        color: #856404;
    }

    .status-on-track {
        background: var(--gradient-green);
        color: white;
    }

    .status-met {
        background: var(--gradient-green);
        color: white;
    }

    .status-over-achieved {
        background: var(--gradient-purple);
        color: white;
    }

    .status-qualitative {
        background: var(--gradient-teal);
        color: white;
    }

    .status-not-available {
        background: var(--gradient-gray);
        color: white;
    }

    /* Progress Bar */
    .ios-progress-container {
        height: 8px;
        background-color: var(--color-background-tertiary);
        border-radius: 4px;
        overflow: hidden;
        margin: var(--spacing-sm) 0;
    }

    .ios-progress-bar {
        height: 100%;
        border-radius: 4px;
        transition: width 0.5s ease;
    }

    .ios-progress-bar-red {
        background: var(--gradient-red);
    }

    .ios-progress-bar-yellow {
        background: var(--gradient-yellow);
    }

    .ios-progress-bar-light-green {
        background: var(--gradient-green);
    }

    .ios-progress-bar-dark-green {
        background: var(--gradient-green);
    }

    .ios-progress-bar-purple {
        background: var(--gradient-purple);
    }

    .ios-progress-bar-blue {
        background: var(--gradient-teal);
    }

    .ios-progress-bar-gray {
        background: var(--gradient-gray);
    }

    /* Summary Cards */
    .ios-summary-card {
        border-radius: var(--radius-md);
        padding: var(--spacing-md);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        height: 100%;
        transition: transform 0.2s ease;
        position: relative;
        overflow: hidden;
    }

    .ios-summary-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 100%);
        z-index: 1;
    }

    .ios-summary-card > * {
        position: relative;
        z-index: 2;
    }

    .ios-summary-card:active {
        transform: scale(0.98);
    }

    .ios-summary-card-title {
        font-size: 14px;
        font-weight: 500;
        margin-bottom: var(--spacing-sm);
        color: var(--color-label-secondary);
    }

    .ios-summary-card-value {
        font-size: 32px;
        font-weight: 700;
        margin-bottom: var(--spacing-xs);
        color: var(--color-label-primary);
        background: linear-gradient(135deg, #1c2033 0%, #3a3f5c 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    @media (prefers-color-scheme: dark) {
        .ios-summary-card-value {
            background: linear-gradient(135deg, #ffffff 0%, #d0d0d0 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    }

    .ios-summary-card-subtitle {
        font-size: 12px;
        color: var(--color-label-tertiary);
    }

    /* Insights Section */
    .ios-insights-card {
        background-color: rgba(10, 132, 255, 0.08);
        border-left: 4px solid var(--color-blue);
    }

    .ios-recommendations-card {
        background-color: rgba(52, 199, 89, 0.08);
        border-left: 4px solid var(--color-green);
    }

    .ios-critical-card {
        background-color: rgba(255, 59, 48, 0.08);
        border-left: 4px solid var(--color-red);
    }

    /* Accordion */
    .ios-accordion {
        border: 1px solid var(--color-separator);
        border-radius: var(--radius-md);
        margin-bottom: var(--spacing-md);
        overflow: hidden;
        box-shadow: var(--shadow-premium-sm);
    }

    .ios-accordion-header {
        padding: var(--spacing-md);
        background-color: var(--color-background-primary);
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid var(--color-separator);
        transition: background-color 0.2s ease;
    }

    .ios-accordion-header:active {
        background-color: var(--color-background-secondary);
    }

    .ios-accordion-content {
        padding: 0;
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease, padding 0.3s ease;
    }

    .ios-accordion-content.active {
        max-height: 2000px;
        padding: var(--spacing-md);
    }

    /* Strategic Objective Section */
    .ios-strategic-objective {
        margin-bottom: var(--spacing-lg);
        padding-bottom: var(--spacing-lg);
        border-bottom: 1px solid var(--color-separator);
    }

    .ios-strategic-objective:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }

    /* Indicator Table */
    .ios-table {
        width: 100%;
        border-collapse: collapse;
    }

    .ios-table th {
        background-color: var(--color-background-secondary);
        padding: var(--spacing-sm);
        text-align: left;
        font-weight: 600;
        border-bottom: 1px solid var(--color-separator);
        color: var(--color-label-secondary);
        font-size: 14px;
    }

    .ios-table td {
        padding: var(--spacing-sm);
        border-bottom: 1px solid var(--color-separator);
        font-size: 15px;
        color: var(--color-label-primary);
    }

    .ios-table tr:last-child td {
        border-bottom: none;
    }

    /* Chart Container */
    .ios-chart-container {
        height: 300px;
        margin-bottom: var(--spacing-lg);
        border-radius: var(--radius-md);
        overflow: hidden;
        background-color: var(--color-background-primary);
        padding: var(--spacing-md);
        box-shadow: var(--shadow-premium-sm);
    }

    /* Selected Cluster and Timeline Pill */
    .ios-selected-pill {
        display: inline-flex;
        align-items: center;
        background: linear-gradient(135deg, rgba(10, 132, 255, 0.1) 0%, rgba(0, 85, 255, 0.1) 100%);
        border-radius: 16px;
        padding: var(--spacing-xs) var(--spacing-md);
        font-size: 14px;
        font-weight: 500;
        color: var(--color-blue);
        margin-right: var(--spacing-sm);
        margin-bottom: var(--spacing-sm);
        box-shadow: var(--shadow-sm);
        border: 1px solid rgba(10, 132, 255, 0.2);
    }

    /* Responsive Grid */
    .ios-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: var(--spacing-md);
    }

    @media (min-width: 640px) {
        .ios-grid-2 {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (min-width: 768px) {
        .ios-grid-3 {
            grid-template-columns: repeat(3, 1fr);
        }

        .ios-grid-4 {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (min-width: 1024px) {
        .ios-grid-4 {
            grid-template-columns: repeat(4, 1fr);
        }
    }

    /* Tab Navigation */
    .ios-tabs {
        display: flex;
        border-bottom: 1px solid var(--color-separator);
        margin-bottom: var(--spacing-lg);
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none; /* Firefox */
        -ms-overflow-style: none; /* IE and Edge */
    }

    .ios-tabs::-webkit-scrollbar {
        display: none; /* Chrome, Safari, Opera */
    }

    .ios-tab {
        padding: var(--spacing-sm) var(--spacing-md);
        font-weight: 500;
        color: var(--color-label-secondary);
        cursor: pointer;
        white-space: nowrap;
        border-bottom: 2px solid transparent;
        transition: color 0.2s ease, border-color 0.2s ease;
    }

    .ios-tab.active {
        color: var(--color-blue);
        border-bottom-color: var(--color-blue);
    }

    .ios-tab-content {
        display: none;
    }

    .ios-tab-content.active {
        display: block;
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    /* Error Message */
    .ios-error {
        background-color: rgba(255, 59, 48, 0.08);
        border-left: 4px solid var(--color-red);
        padding: var(--spacing-md);
        margin-bottom: var(--spacing-lg);
        border-radius: var(--radius-sm);
        box-shadow: var(--shadow-premium-sm);
    }

    /* Empty State */
    .ios-empty-state {
        text-align: center;
        padding: var(--spacing-xl) var(--spacing-md);
    }

    .ios-empty-state-icon {
        font-size: 64px;
        color: var(--color-label-tertiary);
        margin-bottom: var(--spacing-md);
    }

    .ios-empty-state-title {
        font-size: 20px;
        font-weight: 600;
        margin-bottom: var(--spacing-sm);
        color: var(--color-label-primary);
    }

    .ios-empty-state-text {
        font-size: 16px;
        color: var(--color-label-secondary);
        max-width: 400px;
        margin: 0 auto;
    }

    /* Over-achieved indicator */
    .over-achieved-indicator {
        position: relative;
        overflow: hidden;
    }

    .over-achieved-indicator::after {
        content: '★';
        position: absolute;
        top: -2px;
        right: -2px;
        background: var(--gradient-purple);
        color: white;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        box-shadow: var(--shadow-sm);
    }

    /* Dashboard header with gradient background */
    .dashboard-header {
        background: linear-gradient(135deg, #f5f7fa 0%, #e4e7f0 100%);
        border-radius: var(--radius-md);
        padding: var(--spacing-lg);
        margin-bottom: var(--spacing-lg);
        box-shadow: var(--shadow-premium-md);
        position: relative;
        overflow: hidden;
    }

    @media (prefers-color-scheme: dark) {
        .dashboard-header {
            background: linear-gradient(135deg, #1c1c24 0%, #2c2c36 100%);
        }
    }

    .dashboard-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: radial-gradient(circle at top right, rgba(10, 132, 255, 0.1) 0%, rgba(10, 132, 255, 0) 70%);
    }

    .dashboard-header-content {
        position: relative;
        z-index: 1;
    }

    /* Colorful summary cards */
    .summary-card-blue {
        background: linear-gradient(135deg, rgba(10, 132, 255, 0.1) 0%, rgba(0, 85, 255, 0.05) 100%);
        border: 1px solid rgba(10, 132, 255, 0.2);
    }

    .summary-card-green {
        background: linear-gradient(135deg, rgba(52, 199, 89, 0.1) 0%, rgba(48, 182, 80, 0.05) 100%);
        border: 1px solid rgba(52, 199, 89, 0.2);
    }

    .summary-card-yellow {
        background: linear-gradient(135deg, rgba(255, 204, 0, 0.1) 0%, rgba(255, 183, 0, 0.05) 100%);
        border: 1px solid rgba(255, 204, 0, 0.2);
    }

    .summary-card-teal {
        background: linear-gradient(135deg, rgba(90, 200, 250, 0.1) 0%, rgba(50, 173, 225, 0.05) 100%);
        border: 1px solid rgba(90, 200, 250, 0.2);
    }
</style>

<div class="dashboard-header">
    <div class="dashboard-header-content">
        <h2 class="ios-header">Performance Dashboard</h2>

        <div class="flex flex-wrap mb-4">
            @if(isset($Cluster) && $Cluster && isset($Cluster->Cluster_Name) && !empty($Cluster->Cluster_Name))
                <div class="ios-selected-pill">
                    <i class="iconify mr-1" data-icon="heroicons-solid:collection"></i>
                    Cluster: {{ $Cluster->Cluster_Name }}
                </div>
            @else
                <div class="ios-selected-pill" style="background: linear-gradient(135deg, rgba(255, 59, 48, 0.1) 0%, rgba(224, 48, 42, 0.05) 100%); color: var(--color-red); border-color: rgba(255, 59, 48, 0.2);">
                    <i class="iconify mr-1" data-icon="heroicons-solid:exclamation-circle"></i>
                    Cluster: Not Selected
                </div>
            @endif

            @if(isset($Timeline) && $Timeline && isset($Timeline->ReportName) && isset($Timeline->Year))
                <div class="ios-selected-pill">
                    <i class="iconify mr-1" data-icon="heroicons-solid:calendar"></i>
                    Timeline: {{ $Timeline->ReportName }} ({{ $Timeline->Year }})
                </div>
            @else
                <div class="ios-selected-pill" style="background: linear-gradient(135deg, rgba(255, 59, 48, 0.1) 0%, rgba(224, 48, 42, 0.05) 100%); color: var(--color-red); border-color: rgba(255, 59, 48, 0.2);">
                    <i class="iconify mr-1" data-icon="heroicons-solid:exclamation-circle"></i>
                    Timeline: Not Selected
                </div>
            @endif
        </div>

        @if(isset($Error))
            <div class="ios-error">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="iconify text-red-500" data-icon="heroicons-solid:exclamation"></i>
                    </div>
                    <div class="ml-3">
                        <p class="ios-text">{{ $Error }}</p>
                    </div>
                </div>
            </div>
        @endif

        <div class="ios-tabs">
            <div class="ios-tab active" data-tab="overview">Overview</div>
            <div class="ios-tab" data-tab="indicators">Indicators</div>
            <div class="ios-tab" data-tab="insights">Insights & Recommendations</div>
            <div class="ios-tab" data-tab="strategic">Strategic Objectives</div>
        </div>
    </div>
</div>

@php
    // Enhanced data validation and processing functions

    /**
     * Safely get a value from an object or array with proper null checking
     * @param mixed $obj The object or array
     * @param string $key The key to access
     * @param mixed $default The default value if key doesn't exist
     * @return mixed The value or default
     */
    function safeGet($obj, $key, $default = null) {
        if (is_object($obj)) {
            return isset($obj->$key) ? $obj->$key : $default;
        } elseif (is_array($obj)) {
            return isset($obj[$key]) ? $obj[$key] : $default;
        }
        return $default;
    }

    /**
     * Safely convert a value to float with validation
     * @param mixed $value The value to convert
     * @param float $default The default value if conversion fails
     * @return float The converted value or default
     */
    function safeFloat($value, $default = 0.0) {
        if (is_numeric($value)) {
            return (float)$value;
        }
        return $default;
    }

    /**
     * Calculate percentage safely with division by zero protection
     * @param float $numerator The numerator
     * @param float $denominator The denominator
     * @param float $default The default value if denominator is zero
     * @return float The calculated percentage or default
     */
    function safePercentage($numerator, $denominator, $default = 0.0) {
        $numerator = safeFloat($numerator);
        $denominator = safeFloat($denominator);

        if ($denominator == 0) {
            return $default;
        }

        return ($numerator / $denominator) * 100;
    }

    /**
     * Determine performance category based on percentage
     * @param float $percentage The performance percentage
     * @param bool $isQualitative Whether the indicator is qualitative
     * @param bool $hasData Whether data is available
     * @return string The performance category
     */
    function getPerformanceCategory($percentage, $isQualitative = false, $hasData = true) {
        if (!$hasData) {
            return 'Not Available';
        }

        if ($isQualitative) {
            return 'Qualitative';
        }

        if ($percentage === null) {
            return 'Not Available';
        }

        if ($percentage > 100) {
            return 'Over Achieved';
        } elseif ($percentage >= 90) {
            return 'Met';
        } elseif ($percentage >= 50) {
            return 'On Track';
        } elseif ($percentage >= 10) {
            return 'In Progress';
        } else {
            return 'Not Performing';
        }
    }

    /**
     * Parse target year range into start and end years
     * @param string $targetYearRange The target year range in YYYY-YYYY format
     * @return array Associative array with 'start' and 'end' years
     */
    function parseTargetYearRange($targetYearRange) {
        if (empty($targetYearRange) || !preg_match('/^\d{4}-\d{4}$/', $targetYearRange)) {
            return ['start' => null, 'end' => null];
        }

        $years = explode('-', $targetYearRange);
        return [
            'start' => (int)$years[0],
            'end' => (int)$years[1]
        ];
    }

    /**
     * Check if a report year falls within a target year range
     * @param int $reportYear The report year
     * @param string $targetYearRange The target year range in YYYY-YYYY format
     * @return bool True if the report year is within the range
     */
    function isYearInTargetRange($reportYear, $targetYearRange) {
        $range = parseTargetYearRange($targetYearRange);

        if ($range['start'] === null || $range['end'] === null) {
            return false;
        }

        return ($reportYear >= $range['start'] && $reportYear <= $range['end']);
    }

    /**
     * Format target year range for display
     * @param string $targetYearRange The target year range in YYYY-YYYY format
     * @return string Formatted target year range for display
     */
    function formatTargetYearRange($targetYearRange) {
        $range = parseTargetYearRange($targetYearRange);

        if ($range['start'] === null || $range['end'] === null) {
            return $targetYearRange;
        }

        return $range['start'] . '-' . $range['end'];
    }

    /**
     * Fix strategic objective mapping in performance data
     * @param array $data The performance data
     * @return array The fixed data
     */
    function fixStrategicObjectiveMapping($data) {
        if (!isset($data) || !is_array($data)) {
            return [];
        }

        $fixedData = [];

        foreach ($data as $item) {
            // Skip invalid items
            if (!isset($item['indicator']) || !isset($item['strategicObjective'])) {
                continue;
            }

            // Fix strategic objective mapping
            if (isset($item['strategicObjective']->SO_ID)) {
                // Ensure SO_ID is used as the key for strategic objectives
                $item['strategicObjective']->SO_ID = trim($item['strategicObjective']->SO_ID);
            }

            $fixedData[] = $item;
        }

        return $fixedData;
    }

    /**
     * Validate and normalize performance data
     * @param array $data The performance data
     * @return array The validated and normalized data
     */
    function validatePerformanceData($data) {
        $data = fixStrategicObjectiveMapping($data);

        if (!isset($data) || !is_array($data)) {
            return [];
        }

        $validatedData = [];

        foreach ($data as $item) {
            // Skip invalid items
            if (!isset($item['indicator']) || !isset($item['strategicObjective'])) {
                continue;
            }

            // Ensure score object exists
            if (!isset($item['score']) || !is_array($item['score'])) {
                $item['score'] = [
                    'has_target' => false,
                    'has_performance' => false,
                    'percentage' => null,
                    'raw_value' => null,
                    'category' => 'Not Available',
                    'error' => null,
                    'target_year_range' => null
                ];
            }

            // Validate strategic objective
            if (!isset($item['strategicObjective']->SO_ID) || empty($item['strategicObjective']->SO_ID)) {
                if (isset($item['indicator']->SO_ID) && !empty($item['indicator']->SO_ID)) {
                    // Use indicator's SO_ID if available
                    $soID = trim($item['indicator']->SO_ID);

                    // Create a strategic objective object if it doesn't exist
                    if (!is_object($item['strategicObjective'])) {
                        $item['strategicObjective'] = (object)[];
                    }

                    $item['strategicObjective']->SO_ID = $soID;
                    $item['strategicObjective']->SO_Name = $soID;
                    $item['strategicObjective']->Description = '';
                } else {
                    // Default to Unknown if no SO_ID is available
                    $item['strategicObjective']->SO_ID = 'Unknown';
                    $item['strategicObjective']->SO_Name = 'Unknown Strategic Objective';
                    $item['strategicObjective']->Description = '';
                }
            }

            // Recalculate percentage and category for accuracy
            if ($item['score']['has_target'] && $item['score']['has_performance']) {
                $targetValue = safeFloat(safeGet($item['target'], 'Target_Value'));
                $actualValue = safeFloat($item['score']['raw_value']);

                if ($targetValue > 0) {
                    $item['score']['percentage'] = ($actualValue / $targetValue) * 100;
                    $item['score']['category'] = getPerformanceCategory($item['score']['percentage']);
                }
            }

            // Ensure target_year_range is set if available
            if (!isset($item['score']['target_year_range']) && isset($item['target']) && isset($item['target']->Target_Year)) {
                $item['score']['target_year_range'] = $item['target']->Target_Year;
            }

            $validatedData[] = $item;
        }

        return $validatedData;
    }

    /**
     * Group indicators by strategic objective with validation
     * @param array $data The performance data
     * @return array The grouped data
     */
    function groupIndicatorsByObjective($data) {
        $validatedData = validatePerformanceData($data);
        $grouped = [];

        foreach ($validatedData as $item) {
            // Get the strategic objective ID directly from the indicator if available
            $soID = null;

            // First try to get from strategicObjective object
            if (isset($item['strategicObjective']) && !empty($item['strategicObjective'])) {
                if (isset($item['strategicObjective']->SO_ID) && !empty($item['strategicObjective']->SO_ID)) {
                    $soID = trim($item['strategicObjective']->SO_ID);
                } elseif (isset($item['strategicObjective']->StrategicObjectiveID) && !empty($item['strategicObjective']->StrategicObjectiveID)) {
                    $soID = trim($item['strategicObjective']->StrategicObjectiveID);
                }
            }

            // If not found in strategicObjective, try to get from indicator
            if (empty($soID) && isset($item['indicator']) && !empty($item['indicator'])) {
                if (isset($item['indicator']->SO_ID) && !empty($item['indicator']->SO_ID)) {
                    $soID = trim($item['indicator']->SO_ID);
                }
            }

            // Default to Unknown if still not found
            if (empty($soID)) {
                $soID = 'Unknown';
            }

            // Get name and description
            $soName = 'Unknown Strategic Objective';
            $soDesc = '';

            if (isset($item['strategicObjective'])) {
                $soName = safeGet($item['strategicObjective'], 'SO_Name', $soID);
                $soDesc = safeGet($item['strategicObjective'], 'Description', '');
            }

            // Initialize group if not exists
            if (!isset($grouped[$soID])) {
                $grouped[$soID] = [
                    'name' => $soName,
                    'description' => $soDesc,
                    'indicators' => []
                ];
            }

            $grouped[$soID]['indicators'][] = $item;
        }

        return $grouped;
    }

    /**
     * Fix strategic objectives in performance summary
     * @param array $summary The performance summary
     * @return array The fixed summary
     */
    function fixStrategicObjectivesSummary($summary) {
        if (!isset($summary) || !is_array($summary) || !isset($summary['strategic_objectives'])) {
            return $summary;
        }

        $fixedStrategicObjectives = [];

        foreach ($summary['strategic_objectives'] as $soID => $so) {
            $soID = trim($soID); // Ensure no whitespace

            if ($soID === 'Unknown' && isset($so['name']) && preg_match('/^SO\d+$/', $so['name'])) {
                // If the key is 'Unknown' but the name is a valid SO ID (like 'SO1'), use the name as the key
                $fixedStrategicObjectives[$so['name']] = $so;
            } else {
                $fixedStrategicObjectives[$soID] = $so;
            }
        }

        $summary['strategic_objectives'] = $fixedStrategicObjectives;
        return $summary;
    }

    /**
     * Validate and normalize performance summary
     * @param array $summary The performance summary
     * @return array The validated and normalized summary
     */
    function validatePerformanceSummary($summary) {
        $summary = fixStrategicObjectivesSummary($summary);

        if (!isset($summary) || !is_array($summary)) {
            return [
                'overall_score' => 0,
                'overall_category' => 'Not Available',
                'total_indicators' => 0,
                'indicators_with_targets' => 0,
                'indicators_with_data' => 0,
                'category_counts' => [
                    'Not Performing' => 0,
                    'In Progress' => 0,
                    'On Track' => 0,
                    'Met' => 0,
                    'Over Achieved' => 0,
                    'Qualitative' => 0,
                    'Not Available' => 0
                ],
                'strategic_objectives' => []
            ];
        }

        // Ensure all required fields exist
        $summary['overall_score'] = safeGet($summary, 'overall_score', 0);
        $summary['overall_category'] = safeGet($summary, 'overall_category', 'Not Available');
        $summary['total_indicators'] = safeGet($summary, 'total_indicators', 0);
        $summary['indicators_with_targets'] = safeGet($summary, 'indicators_with_targets', 0);
        $summary['indicators_with_data'] = safeGet($summary, 'indicators_with_data', 0);

        // Ensure category counts exist
        if (!isset($summary['category_counts']) || !is_array($summary['category_counts'])) {
            $summary['category_counts'] = [];
        }

        $categories = [
            'Not Performing', 'In Progress', 'On Track', 'Met',
            'Over Achieved', 'Qualitative', 'Not Available'
        ];

        foreach ($categories as $category) {
            if (!isset($summary['category_counts'][$category])) {
                $summary['category_counts'][$category] = 0;
            }
        }

        // Validate strategic objectives
        if (!isset($summary['strategic_objectives']) || !is_array($summary['strategic_objectives'])) {
            $summary['strategic_objectives'] = [];
        }

        foreach ($summary['strategic_objectives'] as $soID => $so) {
            $summary['strategic_objectives'][$soID]['name'] = safeGet($so, 'name', 'Unknown');
            $summary['strategic_objectives'][$soID]['description'] = safeGet($so, 'description', '');
            $summary['strategic_objectives'][$soID]['indicators'] = safeGet($so, 'indicators', 0);
            $summary['strategic_objectives'][$soID]['average_score'] = safeGet($so, 'average_score');
        }

        return $summary;
    }

    // Apply validation to the data
    $validatedPerformanceSummary = isset($PerformanceSummary) ? validatePerformanceSummary($PerformanceSummary) : null;
    $groupedIndicators = isset($PerformanceData) ? groupIndicatorsByObjective($PerformanceData) : [];

    // Debug output to verify strategic objectives mapping
    if (isset($validatedPerformanceSummary) && isset($validatedPerformanceSummary['strategic_objectives'])) {
        // Uncomment to debug
        // dd($validatedPerformanceSummary['strategic_objectives']);
    }

    // Debug grouped indicators
    if (!empty($groupedIndicators)) {
        // Uncomment to debug
        // dd(array_keys($groupedIndicators));
    }
@endphp

@if(isset($validatedPerformanceSummary) && !isset($Error))
    <!-- Overview Tab Content -->
    <div class="ios-tab-content active" id="overview-content">
        <!-- Performance Summary Cards -->
        <div class="ios-grid ios-grid-4 mb-6">
            <div class="ios-card">
                <div class="ios-summary-card summary-card-blue">
                    <div class="ios-summary-card-title">Overall Score</div>
                    <div class="ios-summary-card-value">{{ number_format($validatedPerformanceSummary['overall_score'], 1) }}%</div>
                    <div class="ios-summary-card-subtitle">{{ $validatedPerformanceSummary['overall_category'] }}</div>
                </div>
            </div>

            <div class="ios-card">
                <div class="ios-summary-card summary-card-green">
                    <div class="ios-summary-card-title">Total Indicators</div>
                    <div class="ios-summary-card-value">{{ $validatedPerformanceSummary['total_indicators'] }}</div>
                    <div class="ios-summary-card-subtitle">Assigned to this cluster</div>
                </div>
            </div>

            <div class="ios-card">
                <div class="ios-summary-card summary-card-yellow">
                    <div class="ios-summary-card-title">Indicators with Targets</div>
                    <div class="ios-summary-card-value">{{ $validatedPerformanceSummary['indicators_with_targets'] }}</div>
                    <div class="ios-summary-card-subtitle">
                        {{ $validatedPerformanceSummary['total_indicators'] > 0
                            ? number_format(safePercentage($validatedPerformanceSummary['indicators_with_targets'], $validatedPerformanceSummary['total_indicators']), 1) . '%'
                            : '0%' }}
                    </div>
                </div>
            </div>

            <div class="ios-card">
                <div class="ios-summary-card summary-card-teal">
                    <div class="ios-summary-card-title">Indicators with Data</div>
                    <div class="ios-summary-card-value">{{ $validatedPerformanceSummary['indicators_with_data'] }}</div>
                    <div class="ios-summary-card-subtitle">
                        {{ $validatedPerformanceSummary['total_indicators'] > 0
                            ? number_format(safePercentage($validatedPerformanceSummary['indicators_with_data'], $validatedPerformanceSummary['total_indicators']), 1) . '%'
                            : '0%' }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance by Category -->
        <div class="ios-card p-4 mb-6">
            <h3 class="ios-subheader">Performance by Category</h3>

            <div class="ios-grid ios-grid-2 mb-4">
                <div>
                    <div class="ios-chart-container" id="categoryChart">
                        <!-- Chart will be rendered here -->
                    </div>
                </div>

                <div>
                    <table class="ios-table">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Count</th>
                                <th>Percentage</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $totalWithData = $validatedPerformanceSummary['indicators_with_data'] > 0 ? $validatedPerformanceSummary['indicators_with_data'] : 1;
                            @endphp

                            <tr>
                                <td>
                                    <span class="ios-badge status-not-performing">Not Performing</span>
                                </td>
                                <td>{{ $validatedPerformanceSummary['category_counts']['Not Performing'] }}</td>
                                <td>{{ number_format(safePercentage($validatedPerformanceSummary['category_counts']['Not Performing'], $totalWithData), 1) }}%</td>
                            </tr>
                            <tr>
                                <td>
                                    <span class="ios-badge status-in-progress">In Progress</span>
                                </td>
                                <td>{{ $validatedPerformanceSummary['category_counts']['In Progress'] }}</td>
                                <td>{{ number_format(safePercentage($validatedPerformanceSummary['category_counts']['In Progress'], $totalWithData), 1) }}%</td>
                            </tr>
                            <tr>
                                <td>
                                    <span class="ios-badge status-on-track">On Track</span>
                                </td>
                                <td>{{ $validatedPerformanceSummary['category_counts']['On Track'] }}</td>
                                <td>{{ number_format(safePercentage($validatedPerformanceSummary['category_counts']['On Track'], $totalWithData), 1) }}%</td>
                            </tr>
                            <tr>
                                <td>
                                    <span class="ios-badge status-met">Met</span>
                                </td>
                                <td>{{ $validatedPerformanceSummary['category_counts']['Met'] }}</td>
                                <td>{{ number_format(safePercentage($validatedPerformanceSummary['category_counts']['Met'], $totalWithData), 1) }}%</td>
                            </tr>
                            <tr>
                                <td>
                                    <span class="ios-badge status-over-achieved">Over Achieved</span>
                                </td>
                                <td>{{ $validatedPerformanceSummary['category_counts']['Over Achieved'] }}</td>
                                <td>{{ number_format(safePercentage($validatedPerformanceSummary['category_counts']['Over Achieved'], $totalWithData), 1) }}%</td>
                            </tr>
                            <tr>
                                <td>
                                    <span class="ios-badge status-qualitative">Qualitative</span>
                                </td>
                                <td>{{ $validatedPerformanceSummary['category_counts']['Qualitative'] }}</td>
                                <td>{{ number_format(safePercentage($validatedPerformanceSummary['category_counts']['Qualitative'], $totalWithData), 1) }}%</td>
                            </tr>
                            <tr>
                                <td>
                                    <span class="ios-badge status-not-available">Not Available (% of all Indicators)</span>
                                </td>
                                <td>{{ $validatedPerformanceSummary['category_counts']['Not Available'] }}</td>
                                <td>{{ number_format(safePercentage($validatedPerformanceSummary['category_counts']['Not Available'], $validatedPerformanceSummary['total_indicators']), 1) }}%</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Strategic Objectives Summary -->
        <div class="ios-card p-4 mb-6">
            <h3 class="ios-subheader">Strategic Objectives Performance</h3>

            <div class="ios-chart-container" id="strategicObjectivesChart">
                <!-- Chart will be rendered here -->
            </div>

            <div class="mt-4">
                <table class="ios-table">
                    <thead>
                        <tr>
                            <th>Strategic Objective</th>
                            <th>Indicators</th>
                            <th>Average Score</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($validatedPerformanceSummary['strategic_objectives'] as $soID => $so)
                            <tr>
                                <td>
                                    <div class="font-medium">{{ $soID }}</div>
                                    <div class="ios-text-small">{{ Str::limit($so['name'], 50) }}</div>
                                </td>
                                <td>{{ $so['indicators'] }}</td>
                                <td>
                                    @if($so['average_score'] !== null)
                                        {{ number_format($so['average_score'], 1) }}%
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>
                                    @if($so['average_score'] !== null)
                                        @if($so['average_score'] < 10)
                                            <span class="ios-badge status-not-performing">Not Performing</span>
                                        @elseif($so['average_score'] < 50)
                                            <span class="ios-badge status-in-progress">In Progress</span>
                                        @elseif($so['average_score'] < 90)
                                            <span class="ios-badge status-on-track">On Track</span>
                                        @elseif($so['average_score'] <= 100)
                                            <span class="ios-badge status-met">Met</span>
                                        @else
                                            <span class="ios-badge status-over-achieved">Over Achieved</span>
                                        @endif
                                    @else
                                        <span class="ios-badge status-not-available">Not Available</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Critical Indicators -->
        @if(isset($Insights) && !empty($Insights['critical_indicators']))
            <div class="ios-card p-4 mb-6">
                <h3 class="ios-subheader">Critical Indicators Requiring Attention</h3>

                <table class="ios-table">
                    <thead>
                        <tr>
                            <th>Indicator</th>
                            <th>Strategic Objective</th>
                            <th>Target Range</th>
                            <th>Score</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($Insights['critical_indicators'] as $indicator)
                            <tr>
                                <td>
                                    <div class="font-medium">{{ safeGet($indicator, 'indicator_number', 'N/A') }}</div>
                                    <div class="ios-text-small">{{ safeGet($indicator, 'indicator_name', 'Unknown Indicator') }}</div>
                                </td>
                                <td>{{ safeGet($indicator, 'strategic_objective', 'Unknown') }}</td>
                                <td>{{ safeGet($indicator, 'target_year_range', 'Not set') }}</td>
                                <td>
                                    <div class="flex items-center">
                                        <span class="ios-badge status-not-performing mr-2">{{ number_format(safeFloat(safeGet($indicator, 'score', 0)), 1) }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <!-- Action Buttons -->
        <div class="flex flex-wrap gap-4 mb-6">
            <a href="{{ route('performance.report', ['cluster_id' => $ClusterID ?? 0, 'timeline_id' => $TimelineID ?? 0]) }}" class="ios-button">
                <i class="iconify mr-1" data-icon="heroicons-solid:document-report"></i>
                Generate Report
            </a>

            <a href="{{ route('performance.timeline.selection', ['cluster_id' => $ClusterID ?? 0]) }}" class="ios-button ios-button-secondary">
                <i class="iconify mr-1" data-icon="heroicons-solid:calendar"></i>
                Change Timeline
            </a>

            <a href="{{ route('performance.cluster.selection') }}" class="ios-button ios-button-secondary">
                <i class="iconify mr-1" data-icon="heroicons-solid:collection"></i>
                Change Cluster
            </a>
        </div>
    </div>

    <!-- Indicators Tab Content -->
    <div class="ios-tab-content" id="indicators-content">
        @if(count($groupedIndicators) > 0)
            @foreach($groupedIndicators as $soID => $objective)
                <div class="ios-accordion mb-4">
                    <div class="ios-accordion-header" data-accordion="{{ $soID }}">
                        <div>
                            <span class="font-semibold">{{ $soID }}:</span> {{ $objective['name'] }}
                        </div>
                        <i class="iconify" data-icon="heroicons-solid:chevron-down"></i>
                    </div>
                    <div class="ios-accordion-content" id="accordion-{{ $soID }}">
                        <div class="ios-text-small mb-4">{{ $objective['description'] }}</div>

                        <table class="ios-table">
                            <thead>
                                <tr>
                                    <th>Indicator</th>
                                    <th>Target</th>
                                    <th>Performance</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($objective['indicators'] as $data)
                                    @php
                                        // Enhanced indicator data validation
                                        $indicatorNumber = safeGet($data['indicator'], 'Indicator_Number', 'N/A');
                                        $indicatorName = safeGet($data['indicator'], 'Indicator_Name', 'Unknown Indicator');

                                        $hasTarget = safeGet($data['score'], 'has_target', false);
                                        $hasPerformance = safeGet($data['score'], 'has_performance', false);

                                        $targetValue = $hasTarget ? safeGet($data['target'], 'Target_Value', 'N/A') : null;
                                        $targetYearRange = $hasTarget ? safeGet($data['target'], 'Target_Year', 'N/A') : null;

                                        $rawValue = $hasPerformance ? safeGet($data['score'], 'raw_value', 'N/A') : null;
                                        $comment = $hasPerformance ? safeGet($data['score'], 'comment', '') : '';

                                        $percentage = safeGet($data['score'], 'percentage');
                                        $category = safeGet($data['score'], 'category', 'Not Available');
                                        $error = safeGet($data['score'], 'error');

                                        // Recalculate percentage and determine if over-achieved
                                        $isOverAchieved = false;
                                        if ($hasTarget && $hasPerformance) {
                                            $targetValueFloat = safeFloat($targetValue);
                                            $actualValueFloat = safeFloat($rawValue);

                                            if ($targetValueFloat > 0) {
                                                $calculatedPercentage = ($actualValueFloat / $targetValueFloat) * 100;
                                                $isOverAchieved = $calculatedPercentage > 100;

                                                // Update percentage for accuracy
                                                $percentage = $calculatedPercentage;

                                                // Update category if over-achieved
                                                if ($isOverAchieved) {
                                                    $category = 'Over Achieved';
                                                }
                                            }
                                        }
                                    @endphp

                                    <tr>
                                        <td>
                                            <div class="font-medium">{{ $indicatorNumber }}</div>
                                            <div class="ios-text-small">{{ $indicatorName }}</div>
                                        </td>
                                        <td>
                                            @if($hasTarget)
                                                {{ $targetValue }}
                                                <div class="ios-text-xs">Target Range: {{ $targetYearRange }}</div>
                                            @else
                                                <span class="text-red-500">No target set</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($hasPerformance)
                                                <div>{{ $rawValue }}</div>
                                                @if(!empty($comment))
                                                    <div class="ios-text-xs">{{ Str::limit($comment, 50) }}</div>
                                                @endif
                                            @else
                                                <span class="text-red-500">No data reported</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($isOverAchieved)
                                                <span class="ios-badge status-over-achieved">Over Achieved</span>
                                                <div class="ios-progress-container mt-1">
                                                    <div class="ios-progress-bar ios-progress-bar-purple" style="width: 100%"></div>
                                                </div>
                                                <div class="ios-text-xs text-right">{{ number_format($percentage, 1) }}%</div>
                                            @elseif($category == 'Not Performing')
                                                <span class="ios-badge status-not-performing">{{ $category }}</span>
                                                @if($percentage !== null)
                                                    <div class="ios-progress-container mt-1">
                                                        <div class="ios-progress-bar ios-progress-bar-red" style="width: {{ min(100, max(5, $percentage)) }}%"></div>
                                                    </div>
                                                    <div class="ios-text-xs text-right">{{ number_format($percentage, 1) }}%</div>
                                                @endif
                                            @elseif($category == 'In Progress')
                                                <span class="ios-badge status-in-progress">{{ $category }}</span>
                                                @if($percentage !== null)
                                                    <div class="ios-progress-container mt-1">
                                                        <div class="ios-progress-bar ios-progress-bar-yellow" style="width: {{ min(100, max(5, $percentage)) }}%"></div>
                                                    </div>
                                                    <div class="ios-text-xs text-right">{{ number_format($percentage, 1) }}%</div>
                                                @endif
                                            @elseif($category == 'On Track')
                                                <span class="ios-badge status-on-track">{{ $category }}</span>
                                                @if($percentage !== null)
                                                    <div class="ios-progress-container mt-1">
                                                        <div class="ios-progress-bar ios-progress-bar-light-green" style="width: {{ min(100, max(5, $percentage)) }}%"></div>
                                                    </div>
                                                    <div class="ios-text-xs text-right">{{ number_format($percentage, 1) }}%</div>
                                                @endif
                                            @elseif($category == 'Met')
                                                <span class="ios-badge status-met">{{ $category }}</span>
                                                @if($percentage !== null)
                                                    <div class="ios-progress-container mt-1">
                                                        <div class="ios-progress-bar ios-progress-bar-dark-green" style="width: {{ min(100, max(5, $percentage)) }}%"></div>
                                                    </div>
                                                    <div class="ios-text-xs text-right">{{ number_format($percentage, 1) }}%</div>
                                                @endif
                                            @elseif($category == 'Qualitative')
                                                <span class="ios-badge status-qualitative">{{ $category }}</span>
                                            @else
                                                <span class="ios-badge status-not-available">{{ $category }}</span>
                                            @endif

                                            @if($error)
                                                <div class="ios-text-xs text-red-500 mt-1">{{ $error }}</div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        @else
            <div class="ios-card ios-empty-state">
                <i class="iconify ios-empty-state-icon" data-icon="heroicons-solid:chart-bar"></i>
                <h3 class="ios-empty-state-title">No Indicators Available</h3>
                <p class="ios-empty-state-text">
                    There are no performance indicators available for this cluster and timeline.
                </p>
            </div>
        @endif
    </div>

    <!-- Insights Tab Content -->
    <div class="ios-tab-content" id="insights-content">
        @if(isset($Insights) && !empty($Insights))
            <div class="ios-grid ios-grid-2 gap-6">
                <!-- Observations -->
                <div class="ios-card p-4 ios-insights-card">
                    <h3 class="ios-subheader">Key Observations</h3>
                    <ul class="list-disc pl-5 space-y-2">
                        @foreach(safeGet($Insights, 'observations', []) as $observation)
                            <li class="ios-text">{{ $observation }}</li>
                        @endforeach
                    </ul>
                </div>

                <!-- Recommendations -->
                <div class="ios-card p-4 ios-recommendations-card">
                    <h3 class="ios-subheader">Recommendations</h3>
                    <ul class="list-disc pl-5 space-y-2">
                        @foreach(safeGet($Insights, 'recommendations', []) as $recommendation)
                            <li class="ios-text">{{ $recommendation }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <!-- Trends -->
            <div class="ios-card p-4 mt-6">
                <h3 class="ios-subheader">Performance Trends</h3>
                @if(!empty(safeGet($Insights, 'trends', [])))
                    <ul class="list-disc pl-5 space-y-2">
                        @foreach(safeGet($Insights, 'trends', []) as $trend)
                            <li class="ios-text">{{ $trend }}</li>
                        @endforeach
                    </ul>
                @else
                    <p class="ios-text">No trend data available for this reporting period.</p>
                @endif
            </div>

            <!-- Critical Indicators -->
            @if(!empty(safeGet($Insights, 'critical_indicators', [])))
                <div class="ios-card p-4 mt-6 ios-critical-card">
                    <h3 class="ios-subheader">Critical Indicators Requiring Attention</h3>
                    <table class="ios-table">
                        <thead>
                            <tr>
                                <th>Indicator</th>
                                <th>Strategic Objective</th>
                                <th>Target Range</th>
                                <th>Score</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(safeGet($Insights, 'critical_indicators', []) as $indicator)
                                <tr>
                                    <td>
                                        <div class="font-medium">{{ safeGet($indicator, 'indicator_number', 'N/A') }}</div>
                                        <div class="ios-text-small">{{ safeGet($indicator, 'indicator_name', 'Unknown Indicator') }}</div>
                                    </td>
                                    <td>{{ safeGet($indicator, 'strategic_objective', 'Unknown') }}</td>
                                    <td>{{ safeGet($indicator, 'target_year_range', 'Not set') }}</td>
                                    <td>
                                        <div class="flex items-center">
                                            <span class="ios-badge status-not-performing mr-2">{{ number_format(safeFloat(safeGet($indicator, 'score', 0)), 1) }}%</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        @else
            <div class="ios-card ios-empty-state">
                <i class="iconify ios-empty-state-icon" data-icon="heroicons-solid:light-bulb"></i>
                <h3 class="ios-empty-state-title">No Insights Available</h3>
                <p class="ios-empty-state-text">
                    There are no insights available for this cluster and timeline.
                </p>
            </div>
        @endif
    </div>

    <!-- Strategic Objectives Tab Content -->
    <div class="ios-tab-content" id="strategic-content">
        @if(isset($validatedPerformanceSummary['strategic_objectives']) && count($validatedPerformanceSummary['strategic_objectives']) > 0)
            @foreach($validatedPerformanceSummary['strategic_objectives'] as $soID => $so)
                <div class="ios-card p-4 mb-6 ios-strategic-objective">
                    <h3 class="ios-subheader">{{ $soID }}: {{ $so['name'] }}</h3>
                    <p class="ios-text mb-4">{{ $so['description'] }}</p>

                    <div class="flex flex-wrap items-center mb-4">
                        <div class="mr-6 mb-2">
                            <span class="ios-text-small">Indicators:</span>
                            <span class="font-semibold ml-1">{{ $so['indicators'] }}</span>
                        </div>

                        <div class="mr-6 mb-2">
                            <span class="ios-text-small">Average Score:</span>
                            <span class="font-semibold ml-1">
                                @if($so['average_score'] !== null)
                                    {{ number_format($so['average_score'], 1) }}%
                                @else
                                    N/A
                                @endif
                            </span>
                        </div>

                        <div class="mb-2">
                            <span class="ios-text-small">Status:</span>
                            @if($so['average_score'] !== null)
                                @if($so['average_score'] < 10)
                                    <span class="ios-badge status-not-performing ml-1">Not Performing</span>
                                @elseif($so['average_score'] < 50)
                                    <span class="ios-badge status-in-progress ml-1">In Progress</span>
                                @elseif($so['average_score'] < 90)
                                    <span class="ios-badge status-on-track ml-1">On Track</span>
                                @elseif($so['average_score'] <= 100)
                                    <span class="ios-badge status-met ml-1">Met</span>
                                @else
                                    <span class="ios-badge status-over-achieved ml-1">Over Achieved</span>
                                @endif
                            @else
                                <span class="ios-badge status-not-available ml-1">Not Available</span>
                            @endif
                        </div>
                    </div>

                    @if($so['average_score'] !== null)
                        <div class="ios-progress-container">
                            @if($so['average_score'] < 10)
                                <div class="ios-progress-bar ios-progress-bar-red" style="width: {{ min(100, max(5, $so['average_score'])) }}%"></div>
                            @elseif($so['average_score'] < 50)
                                <div class="ios-progress-bar ios-progress-bar-yellow" style="width: {{ min(100, max(5, $so['average_score'])) }}%"></div>
                            @elseif($so['average_score'] < 90)
                                <div class="ios-progress-bar ios-progress-bar-light-green" style="width: {{ min(100, max(5, $so['average_score'])) }}%"></div>
                            @elseif($so['average_score'] <= 100)
                                <div class="ios-progress-bar ios-progress-bar-dark-green" style="width: {{ min(100, max(5, $so['average_score'])) }}%"></div>
                            @else
                                <div class="ios-progress-bar ios-progress-bar-purple" style="width: 100%"></div>
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
        @else
            <div class="ios-card ios-empty-state">
                <i class="iconify ios-empty-state-icon" data-icon="heroicons-solid:template"></i>
                <h3 class="ios-empty-state-title">No Strategic Objectives Available</h3>
                <p class="ios-empty-state-text">
                    There are no strategic objectives available for this cluster and timeline.
                </p>
            </div>
        @endif
    </div>
@else
    <div class="ios-card ios-empty-state">
        <i class="iconify ios-empty-state-icon" data-icon="heroicons-solid:emoji-sad"></i>
        <h3 class="ios-empty-state-title">No Performance Data Available</h3>
        <p class="ios-empty-state-text">
            There is no performance data available for this cluster and timeline.
        </p>
    </div>
@endif

<!-- Include Iconify library -->
<script src="https://code.iconify.design/2/2.2.1/iconify.min.js"></script>
<!-- Include ApexCharts -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Check for reduced motion preference
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        // Tab navigation
        const tabs = document.querySelectorAll('.ios-tab');
        const tabContents = document.querySelectorAll('.ios-tab-content');

        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                const tabId = this.getAttribute('data-tab');

                // Remove active class from all tabs and contents
                tabs.forEach(t => t.classList.remove('active'));
                tabContents.forEach(c => c.classList.remove('active'));

                // Add active class to clicked tab and corresponding content
                this.classList.add('active');
                document.getElementById(tabId + '-content').classList.add('active');
            });
        });

        // Accordion functionality
        const accordionHeaders = document.querySelectorAll('.ios-accordion-header');

        accordionHeaders.forEach(header => {
            header.addEventListener('click', function() {
                const accordionId = this.getAttribute('data-accordion');
                const content = document.getElementById('accordion-' + accordionId);

                // Toggle active class
                content.classList.toggle('active');

                // Toggle icon rotation
                const icon = this.querySelector('.iconify');
                if (content.classList.contains('active')) {
                    icon.style.transform = 'rotate(180deg)';
                } else {
                    icon.style.transform = 'rotate(0deg)';
                }
            });
        });

        // Add haptic feedback for iOS devices
        function addHapticFeedback(elements, intensity = 'medium') {
            if (!window.navigator.vibrate) return; // Not supported

            elements.forEach(el => {
                el.addEventListener('click', () => {
                    switch(intensity) {
                        case 'light':
                            window.navigator.vibrate(10);
                            break;
                        case 'medium':
                            window.navigator.vibrate(15);
                            break;
                        case 'heavy':
                            window.navigator.vibrate(25);
                            break;
                    }
                });
            });
        }

        // Add haptic feedback to interactive elements
        addHapticFeedback(document.querySelectorAll('.ios-button'), 'medium');
        addHapticFeedback(document.querySelectorAll('.ios-tab'), 'light');
        addHapticFeedback(document.querySelectorAll('.ios-accordion-header'), 'light');

        // Safely get data for charts
        function safeGetChartData(obj, key, defaultValue = []) {
            if (!obj || typeof obj !== 'object') return defaultValue;
            return obj[key] !== undefined ? obj[key] : defaultValue;
        }

        // Render charts if data is available
        @if(isset($validatedPerformanceSummary) && !isset($Error))
            // Category Chart
            const categoryChartEl = document.getElementById('categoryChart');
            if (categoryChartEl) {
                try {
                    const categoryCounts = @json($validatedPerformanceSummary['category_counts'] ?? []);

                    const categoryData = [
                        safeGetChartData(categoryCounts, 'Not Performing', 0),
                        safeGetChartData(categoryCounts, 'In Progress', 0),
                        safeGetChartData(categoryCounts, 'On Track', 0),
                        safeGetChartData(categoryCounts, 'Met', 0),
                        safeGetChartData(categoryCounts, 'Over Achieved', 0),
                        safeGetChartData(categoryCounts, 'Qualitative', 0),
                        safeGetChartData(categoryCounts, 'Not Available', 0)
                    ];

                    const categoryLabels = [
                        'Not Performing',
                        'In Progress',
                        'On Track',
                        'Met',
                        'Over Achieved',
                        'Qualitative',
                        'Not Available'
                    ];

                    const categoryColors = [
                        '#ff3b30', // Not Performing
                        '#ffcc00', // In Progress
                        '#34c759', // On Track
                        '#30d158', // Met
                        '#af52de', // Over Achieved
                        '#5ac8fa', // Qualitative
                        '#8e8e93'  // Not Available
                    ];

                    const categoryChartOptions = {
                        series: categoryData,
                        chart: {
                            id: 'categoryChart',
                            type: 'donut',
                            height: 300,
                            fontFamily: '-apple-system, BlinkMacSystemFont, "SF Pro Text", "SF Pro Display", "Helvetica Neue", sans-serif',
                            animations: {
                                enabled: !prefersReducedMotion
                            },
                            dropShadow: {
                                enabled: true,
                                top: 2,
                                left: 2,
                                blur: 4,
                                opacity: 0.1
                            }
                        },
                        labels: categoryLabels,
                        colors: categoryColors,
                        legend: {
                            position: 'right',
                            fontFamily: '-apple-system, BlinkMacSystemFont, "SF Pro Text", "SF Pro Display", "Helvetica Neue", sans-serif',
                            fontSize: '13px',
                            markers: {
                                width: 12,
                                height: 12,
                                radius: 6
                            }
                        },
                        tooltip: {
                            style: {
                                fontSize: '13px',
                                fontFamily: '-apple-system, BlinkMacSystemFont, "SF Pro Text", "SF Pro Display", "Helvetica Neue", sans-serif'
                            }
                        },
                        stroke: {
                            width: 0
                        },
                        dataLabels: {
                            enabled: false
                        },
                        responsive: [{
                            breakpoint: 480,
                            options: {
                                chart: {
                                    height: 250
                                },
                                legend: {
                                    position: 'bottom'
                                }
                            }
                        }],
                        plotOptions: {
                            pie: {
                                donut: {
                                    size: '55%',
                                    labels: {
                                        show: true,
                                        total: {
                                            show: true,
                                            label: 'Total',
                                            formatter: function (w) {
                                                return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    };

                    const categoryChart = new ApexCharts(categoryChartEl, categoryChartOptions);
                    categoryChart.render();
                } catch (error) {
                    console.error('Error rendering category chart:', error);
                    categoryChartEl.innerHTML = '<div class="ios-empty-state"><p>Error rendering chart</p></div>';
                }
            }

            // Strategic Objectives Chart
            const soChartEl = document.getElementById('strategicObjectivesChart');
            if (soChartEl) {
                try {
                    const soLabels = [];
                    const soData = [];
                    const soColors = [];

                    const strategicObjectives = @json($validatedPerformanceSummary['strategic_objectives'] ?? []);

                    // Process strategic objectives data safely
                    Object.entries(strategicObjectives).forEach(([soID, so]) => {
                        if (so.average_score !== null && so.average_score !== undefined) {
                            soLabels.push(soID);
                            soData.push(parseFloat(so.average_score));

                            if (so.average_score < 10) {
                                soColors.push('#ff3b30'); // Not Performing
                            } else if (so.average_score < 50) {
                                soColors.push('#ffcc00'); // In Progress
                            } else if (so.average_score < 90) {
                                soColors.push('#34c759'); // On Track
                            } else if (so.average_score <= 100) {
                                soColors.push('#30d158'); // Met
                            } else {
                                soColors.push('#af52de'); // Over Achieved
                            }
                        }
                    });

                    const soChartOptions = {
                        series: [{
                            name: 'Average Score (%)',
                            data: soData
                        }],
                        chart: {
                            id: 'strategicObjectivesChart',
                            type: 'bar',
                            height: 300,
                            fontFamily: '-apple-system, BlinkMacSystemFont, "SF Pro Text", "SF Pro Display", "Helvetica Neue", sans-serif',
                            toolbar: {
                                show: false
                            },
                            animations: {
                                enabled: !prefersReducedMotion
                            },
                            dropShadow: {
                                enabled: true,
                                top: 2,
                                left: 2,
                                blur: 4,
                                opacity: 0.1
                            }
                        },
                        colors: soColors,
                        plotOptions: {
                            bar: {
                                distributed: true,
                                borderRadius: 8,
                                dataLabels: {
                                    position: 'top'
                                }
                            }
                        },
                        dataLabels: {
                            enabled: true,
                            formatter: function(val) {
                                return val.toFixed(1) + '%';
                            },
                            offsetY: -20,
                            style: {
                                fontSize: '12px',
                                fontFamily: '-apple-system, BlinkMacSystemFont, "SF Pro Text", "SF Pro Display", "Helvetica Neue", sans-serif',
                                colors: ["#333"]
                            }
                        },
                        xaxis: {
                            categories: soLabels,
                            labels: {
                                style: {
                                    fontSize: '12px',
                                    fontFamily: '-apple-system, BlinkMacSystemFont, "SF Pro Text", "SF Pro Display", "Helvetica Neue", sans-serif'
                                }
                            },
                            title: {
                                text: 'Strategic Objectives',
                                style: {
                                    fontSize: '13px',
                                    fontFamily: '-apple-system, BlinkMacSystemFont, "SF Pro Text", "SF Pro Display", "Helvetica Neue", sans-serif'
                                }
                            }
                        },
                        yaxis: {
                            max: 120, // Increased to accommodate over-achievement
                            title: {
                                text: 'Score (%)',
                                style: {
                                    fontSize: '13px',
                                    fontFamily: '-apple-system, BlinkMacSystemFont, "SF Pro Text", "SF Pro Display", "Helvetica Neue", sans-serif'
                                }
                            },
                            labels: {
                                style: {
                                    fontSize: '12px',
                                    fontFamily: '-apple-system, BlinkMacSystemFont, "SF Pro Text", "SF Pro Display", "Helvetica Neue", sans-serif'
                                }
                            }
                        },
                        tooltip: {
                            y: {
                                formatter: function(val) {
                                    return val.toFixed(1) + '%';
                                }
                            },
                            style: {
                                fontSize: '13px',
                                fontFamily: '-apple-system, BlinkMacSystemFont, "SF Pro Text", "SF Pro Display", "Helvetica Neue", sans-serif'
                            }
                        },
                        legend: {
                            show: false
                        },
                        responsive: [{
                            breakpoint: 480,
                            options: {
                                dataLabels: {
                                    enabled: false
                                }
                            }
                        }],
                        annotations: {
                            yaxis: [{
                                y: 100,
                                borderColor: '#8e8e93',
                                borderWidth: 1,
                                strokeDashArray: 5,
                                label: {
                                    text: 'Target (100%)',
                                    position: 'left',
                                    style: {
                                        color: '#8e8e93',
                                        fontSize: '11px',
                                        fontFamily: '-apple-system, BlinkMacSystemFont, "SF Pro Text", "SF Pro Display", "Helvetica Neue", sans-serif'
                                    }
                                }
                            }]
                        }
                    };

                    const soChart = new ApexCharts(soChartEl, soChartOptions);
                    soChart.render();
                } catch (error) {
                    console.error('Error rendering strategic objectives chart:', error);
                    soChartEl.innerHTML = '<div class="ios-empty-state"><p>Error rendering chart</p></div>';
                }
            }
        @endif

        if (prefersReducedMotion) {
            // Disable animations
            document.documentElement.style.setProperty('--transition-duration', '0s');

            // Remove animations from tab content
            document.querySelectorAll('.ios-tab-content').forEach(content => {
                content.style.animation = 'none';
            });
        }

        // Support for dark mode toggle
        const darkModeToggle = document.getElementById('dark-mode-toggle');
        if (darkModeToggle) {
            darkModeToggle.addEventListener('click', () => {
                document.documentElement.classList.toggle('dark');

                // Refresh charts when dark mode changes
                if (window.ApexCharts) {
                    try {
                        ApexCharts.exec('categoryChart', 'updateOptions', {
                            theme: {
                                mode: document.documentElement.classList.contains('dark') ? 'dark' : 'light'
                            }
                        });

                        ApexCharts.exec('strategicObjectivesChart', 'updateOptions', {
                            theme: {
                                mode: document.documentElement.classList.contains('dark') ? 'dark' : 'light'
                            }
                        });
                    } catch (error) {
                        console.error('Error updating chart theme:', error);
                    }
                }
            });
        }

        // Check if system dark mode changes
        const darkModeMediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
        darkModeMediaQuery.addEventListener('change', () => {
            // Refresh charts when system dark mode changes
            if (window.ApexCharts) {
                try {
                    ApexCharts.exec('categoryChart', 'updateOptions', {
                        theme: {
                            mode: darkModeMediaQuery.matches ? 'dark' : 'light'
                        }
                    });

                    ApexCharts.exec('strategicObjectivesChart', 'updateOptions', {
                        theme: {
                            mode: darkModeMediaQuery.matches ? 'dark' : 'light'
                        }
                    });
                } catch (error) {
                    console.error('Error updating chart theme:', error);
                }
            }
        });
    });
</script>
