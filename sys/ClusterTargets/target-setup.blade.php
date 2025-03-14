@php

// dd($indicators);
    // Define the valid target timeframe – the lower bound for comparison.
    $validStartYear = 2024; // Hard-coded starting year
    $validEndYear = $validStartYear + 3; // 3 years ahead from starting year

    // Helper function: returns true if the range matches "YYYY-YYYY" and the end year is exactly one greater than the start.
    $isValidRange = function($range) use ($validStartYear) {
        if (!preg_match('/^\d{4}-\d{4}$/', $range)) {
            return false;
        }
        $parts = explode('-', $range);
        $start = (int)$parts[0];
        $end   = (int)$parts[1];
        if (($end - $start) !== 1) {
            return false;
        }
        return true;
    };

    // Prepare Strategic Objective data arrays from the $indicators collection.
    $soCategories = [];
    $soWithTargets = [];
    $soWithoutTargets = [];

    foreach ($indicators as $objective => $indicatorGroup) {
        $totalForSO = count($indicatorGroup);
        $withTargetForSO = 0;
        foreach ($indicatorGroup as $indicator) {
            if ($existingTargets->has($indicator->id)) {
                $targets = $existingTargets[$indicator->id];

                // Filter targets: only consider those with a valid two-year range format and whose starting year is >= validStartYear.
                $validTargets = $targets->filter(function ($target) use ($validStartYear, $isValidRange) {
                    if (!$isValidRange($target->Target_Year)) {
                        return false;
                    }
                    $parts = explode('-', $target->Target_Year);
                    $startYear = (int)$parts[0];
                    return $startYear >= $validStartYear;
                });

                // Filter for legacy targets: valid ranges with a starting year below validStartYear.
                $legacyTargets = $targets->filter(function ($target) use ($validStartYear, $isValidRange) {
                    if (!$isValidRange($target->Target_Year)) {
                        return false;
                    }
                    $parts = explode('-', $target->Target_Year);
                    $startYear = (int)$parts[0];
                    return $startYear < $validStartYear;
                });

                // An indicator is considered to have a valid target if it has at least 1 valid target.
                if ($validTargets->count() >= 1) {
                    $withTargetForSO++;
                }
            }
        }
        $withoutTargetForSO = $totalForSO - $withTargetForSO;
        $soCategories[] = $objective;
        $soWithTargets[] = $withTargetForSO;
        $soWithoutTargets[] = $withoutTargetForSO;
    }

    // Prepare colors for charts.
    $colors = [
        'primary' => '#007aff', // Apple Blue
        'success' => '#34c759', // Apple Green
        'warning' => '#ff9500', // Apple Orange
        'danger'  => '#ff3b30', // Apple Red
        'info'    => '#5ac8fa', // Apple Light Blue
    ];

    // Prepare targets data for JavaScript.
    $targetsDataJson = json_encode(
        $existingTargets
            ->map(function ($targets) use ($isValidRange) {
                return $targets
                    ->filter(function ($t) use ($isValidRange) {
                        return $isValidRange($t->Target_Year);
                    })
                    ->map(function ($t) {
                        return ['year' => $t->Target_Year, 'value' => $t->Target_Value, 'id' => $t->id];
                    })
                    ->values();
            })
            ->all()
    );

    // Prepare indicator data for JavaScript.
    $indicatorsData = [];
    foreach ($indicators as $objective => $indicatorGroup) {
        foreach ($indicatorGroup as $indicator) {
            $targets = $existingTargets->has($indicator->id) ? $existingTargets[$indicator->id] : collect([]);
            $validTargets = $targets->filter(function ($target) use ($validStartYear, $isValidRange) {
                if (!$isValidRange($target->Target_Year)) {
                    return false;
                }
                $parts = explode('-', $target->Target_Year);
                $startYear = (int)$parts[0];
                return $startYear >= $validStartYear;
            });
            $legacyTargets = $targets->filter(function ($target) use ($validStartYear, $isValidRange) {
                if (!$isValidRange($target->Target_Year)) {
                    return false;
                }
                $parts = explode('-', $target->Target_Year);
                $startYear = (int)$parts[0];
                return $startYear < $validStartYear;
            });

            $indicatorsData[$indicator->id] = [
                'id'             => $indicator->id,
                'name'           => $indicator->Indicator_Name,
                'number'         => $indicator->Indicator_Number,
                'responseType'   => $indicator->ResponseType,
                'objective'      => $objective,
                'hasTargets'     => $validTargets->count() > 0,
                'hasValidTargets'=> $validTargets->count() >= 1,
                'validTargets'   => $validTargets->map(function ($t) {
                                        return [
                                            'id'    => $t->id,
                                            'year'  => $t->Target_Year,
                                            'value' => $t->Target_Value,
                                        ];
                                    })->values()->toArray(),
                'legacyTargets'  => $legacyTargets->map(function ($t) {
                                        return [
                                            'id'    => $t->id,
                                            'year'  => $t->Target_Year,
                                            'value' => $t->Target_Value,
                                        ];
                                    })->values()->toArray(),
                'allTargets'     => $targets->filter(function ($t) use ($isValidRange) {
                                        return $isValidRange($t->Target_Year);
                                    })->map(function ($t) use ($validStartYear) {
                                        $parts = explode('-', $t->Target_Year);
                                        $startYear = (int)$parts[0];
                                        return [
                                            'id'       => $t->id,
                                            'year'     => $t->Target_Year,
                                            'value'    => $t->Target_Value,
                                            'isLegacy' => $startYear < $validStartYear,
                                        ];
                                    })->values()->toArray(),
            ];
        }
    }
    $indicatorsDataJson = json_encode($indicatorsData);

    // Update soData based on new criteria.
    $soData = [];
    foreach ($indicators as $objective => $indicatorGroup) {
        $totalForSO = count($indicatorGroup);
        $withTargetForSO = 0;
        $withoutTargetForSO = 0;
        $indicatorsWithTargets = [];
        $indicatorsWithoutTargets = [];

        foreach ($indicatorGroup as $indicator) {
            if ($existingTargets->has($indicator->id)) {
                $targets = $existingTargets[$indicator->id];
                $validTargets = $targets->filter(function ($target) use ($validStartYear, $isValidRange) {
                    if (!$isValidRange($target->Target_Year)) {
                        return false;
                    }
                    $parts = explode('-', $target->Target_Year);
                    $startYear = (int)$parts[0];
                    return $startYear >= $validStartYear;
                });
                $legacyTargets = $targets->filter(function ($target) use ($validStartYear, $isValidRange) {
                    if (!$isValidRange($target->Target_Year)) {
                        return false;
                    }
                    $parts = explode('-', $target->Target_Year);
                    $startYear = (int)$parts[0];
                    return $startYear < $validStartYear;
                });

                if ($validTargets->count() >= 1) {
                    $withTargetForSO++;
                    $indicatorsWithTargets[] = [
                        'id'            => $indicator->id,
                        'number'        => $indicator->Indicator_Number,
                        'name'          => $indicator->Indicator_Name,
                        'validTargets'  => $validTargets->map(function ($t) {
                                                return [
                                                    'id'    => $t->id,
                                                    'year'  => $t->Target_Year,
                                                    'value' => $t->Target_Value,
                                                ];
                                            })->values()->toArray(),
                        'legacyTargets' => $legacyTargets->map(function ($t) {
                                                return [
                                                    'id'    => $t->id,
                                                    'year'  => $t->Target_Year,
                                                    'value' => $t->Target_Value,
                                                ];
                                            })->values()->toArray(),
                    ];
                } else {
                    $withoutTargetForSO++;
                    $indicatorsWithoutTargets[] = [
                        'id'            => $indicator->id,
                        'number'        => $indicator->Indicator_Number,
                        'name'          => $indicator->Indicator_Name,
                        'validTargets'  => $validTargets->map(function ($t) {
                                                return [
                                                    'id'    => $t->id,
                                                    'year'  => $t->Target_Year,
                                                    'value' => $t->Target_Value,
                                                ];
                                            })->values()->toArray(),
                        'legacyTargets' => $legacyTargets->map(function ($t) {
                                                return [
                                                    'id'    => $t->id,
                                                    'year'  => $t->Target_Year,
                                                    'value' => $t->Target_Value,
                                                ];
                                            })->values()->toArray(),
                        'reason'        => $validTargets->count() > 0
                                                ? 'Needs ' . (1 - $validTargets->count()) . ' more target (two-year range) from ' . $validStartYear . ' onwards'
                                                : 'Needs at least 1 target (two-year range) from ' . $validStartYear . ' onwards',
                    ];
                }
            } else {
                $withoutTargetForSO++;
                $indicatorsWithoutTargets[] = [
                    'id'            => $indicator->id,
                    'number'        => $indicator->Indicator_Number,
                    'name'          => $indicator->Indicator_Name,
                    'validTargets'  => [],
                    'legacyTargets' => [],
                    'reason'        => 'No targets set',
                ];
            }
        }

        $percentComplete = $totalForSO > 0 ? round(($withTargetForSO / $totalForSO) * 100) : 0;
        $soData[$objective] = [
            'objective'                => $objective,
            'total'                    => $totalForSO,
            'withTarget'               => $withTargetForSO,
            'withoutTarget'            => $withoutTargetForSO,
            'percentComplete'          => $percentComplete,
            'indicatorsWithTargets'    => $indicatorsWithTargets,
            'indicatorsWithoutTargets' => $indicatorsWithoutTargets,
            'validStartYear'           => $validStartYear,
            'validEndYear'             => $validEndYear,
        ];
    }
    $soDataJson = json_encode($soData);
@endphp

<div class="flex flex-col min-h-screen font-sans bg-gray-100">
    <!-- Sticky Header -->
    <div class="sticky top-0 bg-white shadow-md navbar">
        <div class="flex-1">
            <a class="text-xl text-gray-800 normal-case btn btn-ghost">
                <i class="mr-2 iconify" data-icon="lucide:home"></i>
                {{ $cluster->Cluster_Name ?? 'Unknown Cluster' }}
            </a>
        </div>
        <div class="flex-none">
            <button class="text-gray-800 rounded-full btn btn-outline btn-sm" onclick="window.history.back()">
                <i class="mr-1 iconify" data-icon="lucide:arrow-left"></i>
                Back
            </button>
        </div>
    </div>

    <!-- Analytics Summary Section (Premium Cards) -->
    <div class="grid grid-cols-1 gap-6 p-6 md:grid-cols-3">
        @php
            $totalIndicators = $indicators->flatten()->count();
            $withTarget = 0;
            foreach ($indicators->flatten() as $indicator) {
                if ($existingTargets->has($indicator->id)) {
                    $validTargets = $existingTargets[$indicator->id]->filter(function ($target) use ($validStartYear, $isValidRange) {
                        if (!$isValidRange($target->Target_Year)) {
                            return false;
                        }
                        $parts = explode('-', $target->Target_Year);
                        $startYear = (int)$parts[0];
                        return $startYear >= $validStartYear;
                    });
                    if ($validTargets->count() >= 1) {
                        $withTarget++;
                    }
                }
            }
            $withoutTarget = $totalIndicators - $withTarget;
            $completionPercentage = $totalIndicators > 0 ? round(($withTarget / $totalIndicators) * 100) : 0;
        @endphp
        <div class="overflow-hidden bg-white border border-gray-200 rounded-lg shadow-lg card">
            <div class="p-6 card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800 card-title">Total Indicators</h2>
                        <p class="mt-2 text-4xl font-bold text-primary">{{ $totalIndicators }}</p>
                    </div>
                    <div class="bg-gray-200 radial-progress" style="--value:100; --size:4rem; --thickness: 0.5rem;">
                        <i class="text-2xl iconify text-primary" data-icon="lucide:layers"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="overflow-hidden bg-white border border-gray-200 rounded-lg shadow-lg card">
            <div class="p-6 card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800 card-title">With Valid Targets</h2>
                        <p class="mt-2 text-4xl font-bold text-success">{{ $withTarget }}</p>
                    </div>
                    <div class="bg-gray-200 radial-progress text-success" style="--value:{{ $completionPercentage }}; --size:4rem; --thickness: 0.5rem;">
                        <i class="text-2xl iconify" data-icon="lucide:check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="overflow-hidden bg-white border border-gray-200 rounded-lg shadow-lg card">
            <div class="p-6 card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800 card-title">Need More Targets</h2>
                        <p class="mt-2 text-4xl font-bold text-warning">{{ $withoutTarget }}</p>
                    </div>
                    <div class="bg-gray-200 radial-progress text-warning" style="--value:{{ 100 - $completionPercentage }}; --size:4rem; --thickness: 0.5rem;">
                        <i class="text-2xl iconify" data-icon="lucide:x-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Target Timeframe Info Alert -->
    <div class="px-6 mb-2">
        <div class="text-blue-800 bg-blue-100 rounded-lg shadow-lg alert">
            <div>
                <i class="text-xl iconify" data-icon="lucide:info"></i>
                <div>
                    <h3 class="font-bold">Target Timeframe: {{ $validStartYear }} and beyond</h3>
                    <div class="text-xs">
                        Each indicator requires at least 1 valid target (as a two-year range, e.g. 2024-2025) from {{ $validStartYear }} onwards.
                        Any target not matching the valid format is ignored.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Strategic Objective Metrics with Premium Graph -->
    <div class="p-6">
        <div class="overflow-hidden bg-white border border-gray-200 rounded-lg shadow-lg card">
            <div class="p-6 card-body">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-gray-800 card-title">Strategic Objective Metrics</h2>
                    <div class="flex gap-2">
                        <button id="viewAllSOBtn" class="text-gray-800 rounded-full btn btn-outline btn-sm btn-primary">
                            <i class="mr-1 iconify" data-icon="lucide:grid"></i>
                            View All SOs
                        </button>
                        <button id="explainSOBtn" class="text-gray-800 rounded-full btn btn-outline btn-sm btn-primary">
                            <i class="mr-1 iconify" data-icon="lucide:sparkles"></i>
                            Explain Graph
                        </button>
                    </div>
                </div>
                <div id="soChart" class="w-full h-80"></div>
            </div>
        </div>
    </div>

    <!-- Main Content: Tabs & Indicator Cards -->
    <div class="flex-1 p-6 overflow-auto">
        <!-- Tabs -->
        <div class="p-1 mb-6 bg-gray-200 rounded-lg tabs tabs-boxed">
            <button class="text-gray-800 transition-all duration-300 rounded-md tab tab-active" data-objective="all">
                <i class="mr-1 iconify" data-icon="lucide:layers"></i>
                All
            </button>
            @foreach ($strategicObjectives as $objective)
                <button class="text-gray-800 transition-all duration-300 rounded-md tab" data-objective="{{ $objective }}">
                    <i class="mr-1 iconify" data-icon="lucide:tag"></i>
                    {{ $objective }}
                </button>
            @endforeach
        </div>

        <!-- Indicator Cards -->
        <div id="indicatorsGrid" class="grid grid-cols-1 gap-6 md:grid-cols-2">
            @foreach ($indicators as $objective => $indicatorGroup)
                @foreach ($indicatorGroup as $indicator)
                    <div class="transition-all duration-300 bg-white border border-gray-200 rounded-lg shadow-lg card" data-objective="{{ $objective }}">
                        <div class="p-4 card-body">
                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-800">Indicator {{ $indicator->Indicator_Number }}</h3>
                                    <p class="text-sm text-gray-600">{{ $indicator->Indicator_Name }}</p>
                                    <p class="flex items-center mt-1 text-xs text-gray-500">
                                        <i class="mr-1 iconify" data-icon="lucide:info"></i>
                                        {{ $indicator->ResponseType }}
                                    </p>
                                </div>
                                <span class="px-3 text-gray-800 rounded-full badge badge-outline">{{ $objective }}</span>
                            </div>

                            <!-- Existing Targets Display -->
                            <div class="mb-4 space-y-2">
                                @if ($existingTargets->has($indicator->id))
                                    @foreach ($existingTargets[$indicator->id] as $target)
                                        @php
                                            if (!preg_match('/^\d{4}-\d{4}$/', $target->Target_Year)) {
                                                continue;
                                            }
                                            $parts = explode('-', $target->Target_Year);
                                            $startYear = (int)$parts[0];
                                            $isLegacy = $startYear < $validStartYear;
                                        @endphp
                                        <div class="flex items-center justify-between text-sm px-3 py-2 {{ $isLegacy ? 'bg-gray-100' : 'bg-gray-200' }} rounded-lg transition-all duration-300">
                                            <span class="font-medium">{{ $target->Target_Year }}:</span>
                                            <span class="font-medium">{{ $target->Target_Value }}</span>
                                            <div class="flex gap-1">
                                                @if ($isLegacy)
                                                    <span class="text-gray-800 badge badge-sm badge-ghost">Legacy</span>
                                                @else
                                                @if(Auth::user()->AccountRole == 'Admin')
    <button class="btn btn-ghost btn-sm btn-circle edit-target-btn" data-indicator-id="{{ $indicator->id }}" data-target-id="{{ $target->id }}">
        <i class="iconify" data-icon="lucide:edit"></i>
    </button>
@endif
@if(Auth::user()->AccountRole == 'Admin')
<button class="btn btn-ghost btn-sm btn-circle text-error delete-target-btn" data-target-id="{{ $target->id }}">
    <i class="iconify" data-icon="lucide:trash-2"></i>
</button>
@endif
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="p-3 text-sm text-center text-gray-500 bg-gray-200 rounded-lg">No targets configured</div>
                                @endif
                            </div>

                            @if(Auth::user()->AccountRole == 'Admin')
                            <!-- Set Target Button -->
                            <button class="w-full transition-all duration-300 rounded-lg btn btn-primary btn-sm set-target-btn" data-indicator-id="{{ $indicator->id }}">
                                <i class="mr-1 iconify" data-icon="lucide:plus-circle"></i>
                                Set Target
                            </button>
                        @endif
                        </div>
                    </div>
                @endforeach
            @endforeach
        </div>
    </div>

    <!-- Full Screen Target Graph Modal -->
    <dialog id="targetGraphModal" class="modal">
        <div class="flex flex-col w-full h-full max-w-full p-0 bg-white modal-box">
            <!-- Modal Header (Fixed) -->
            <div class="sticky top-0 z-10 flex items-center justify-between px-6 py-4 bg-gray-100 shadow-md modal-header dark:bg-gray-200">
                <h3 class="text-lg font-bold text-gray-800" id="graphModalTitle">Targets for Indicator</h3>
                <button class="btn btn-circle btn-ghost" id="closeTargetGraphBtn">
                    <i class="text-2xl iconify" data-icon="lucide:x"></i>
                </button>
            </div>
            <!-- Modal Body (Scrollable) -->
            <div class="flex-1 overflow-y-auto">
                <div class="p-6">
                    <div class="mb-4 text-blue-800 bg-blue-100 rounded-lg alert">
                        <div>
                            <i class="text-xl iconify" data-icon="lucide:info"></i>
                            <div>
                                <h3 class="font-bold">Valid Target Ranges (e.g. 2024-2025)</h3>
                                <div class="text-xs">
                                    Each indicator requires at least 1 valid target (a two-year range) from {{ $validStartYear }} onwards.
                                    Any target not matching the valid format is ignored.
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="targetLineChart" class="w-full mb-6 h-80"></div>
                    <div class="mt-4">
                        <h4 class="mb-2 font-semibold text-gray-800">Target Status</h4>
                        <div id="targetStatusInfo" class="text-sm text-gray-800"></div>
                    </div>
                </div>
            </div>
            <!-- Modal Footer (Fixed) -->
            <div class="sticky bottom-0 z-10 px-6 py-4 bg-gray-100 shadow-md modal-footer dark:bg-gray-200">
                <form id="targetGraphForm" method="POST" action="" class="w-full space-y-4">
                    @csrf
                    <div id="methodOverride"></div>
                    <input type="hidden" name="ClusterID" value="{{ $cluster->ClusterID }}">
                    <input type="hidden" id="graphIndicatorID" name="IndicatorID">
                    <input type="hidden" id="graphResponseType" name="ResponseType">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div class="form-control">
                            <label class="label">
                                <span class="font-medium text-gray-800 label-text">Target Range</span>
                            </label>
                            <select name="Target_Year" class="text-gray-800 rounded-lg select select-bordered" required id="graphTargetYear">
                                @foreach($validRanges as $range)
                                    <option value="{{ $range }}">{{ $range }}</option>
                                @endforeach
                            </select>
                        </div>
                        <!-- Target Value input rendered dynamically based on response type -->
                        <div class="form-control" id="graphTargetValueContainer">
                            <!-- Content injected via JavaScript -->
                        </div>
                    </div>
                    <div class="flex justify-end gap-4 mt-6">
                        <button type="button" class="text-gray-800 rounded-lg btn btn-outline" id="cancelTargetBtn">Cancel</button>
                        <button type="submit" class="text-gray-800 rounded-lg btn btn-primary" id="graphSubmitButton">Save Target</button>
                    </div>
                </form>
            </div>
        </div>
    </dialog>

    <!-- Delete Target Confirmation Modal -->
    <dialog id="deleteTargetModal" class="modal">
        <div class="p-6 bg-white modal-box rounded-xl">
            <h3 class="mb-4 text-lg font-bold text-gray-800">Confirm Delete</h3>
            <p class="text-gray-800">Are you sure you want to delete this target? This action cannot be undone.</p>
            <div class="mt-6 modal-action">
                <form method="POST" id="deleteTargetForm">
                    @csrf
                    @method('DELETE')
                    <div class="flex gap-4">
                        <button type="button" class="text-gray-800 btn btn-outline" id="cancelDeleteBtn">Cancel</button>
                        <button type="submit" class="text-gray-800 btn btn-error">Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </dialog>

    <!-- Full Screen Strategic Objectives Modal -->
    <dialog id="allSOModal" class="modal">
        <div class="flex flex-col w-full h-full max-w-full p-0 bg-white modal-box">
            <!-- Modal Header -->
            <div class="sticky top-0 z-10 flex items-center justify-between px-6 py-4 bg-gray-100 shadow-md modal-header dark:bg-gray-200">
                <h3 class="text-lg font-bold text-gray-800">All Strategic Objectives</h3>
                <button class="btn btn-circle btn-ghost" id="closeAllSOBtn">
                    <i class="text-2xl iconify" data-icon="lucide:x"></i>
                </button>
            </div>
            <!-- Modal Body (Scrollable) -->
            <div class="flex-1 overflow-y-auto">
                <div class="p-6">
                    <!-- Target Timeframe Info Alert -->
                    <div class="mb-6 text-blue-800 bg-blue-100 rounded-lg shadow-lg alert">
                        <div>
                            <i class="text-xl iconify" data-icon="lucide:info"></i>
                            <div>
                                <h3 class="font-bold">Target Timeframe: {{ $validStartYear }} - {{ $validEndYear }}</h3>
                                <div class="text-xs">
                                    Each indicator requires at least 1 valid target (a two-year range) within this timeframe for a strategic objective to be considered complete.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Search and Filter -->
                    <div class="sticky top-0 z-10 py-2 mb-6 bg-white">
                        <div class="relative">
                            <input type="text" id="soSearchInput" placeholder="Search strategic objectives..." class="w-full pl-10 text-gray-800 input input-bordered rounded-xl">
                            <i class="absolute text-gray-400 transform -translate-y-1/2 iconify left-3 top-1/2" data-icon="lucide:search"></i>
                        </div>
                    </div>

                    <!-- Grid of SO Cards -->
                    <div id="soCardsGrid" class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                        @foreach ($indicators as $objective => $indicatorGroup)
                            @php
                                $totalForSO = count($indicatorGroup);
                                $withTargetForSO = 0;
                                foreach ($indicatorGroup as $indicator) {
                                    if ($existingTargets->has($indicator->id)) {
                                        $validTargets = $existingTargets[$indicator->id]->filter(function ($target) use ($validStartYear, $isValidRange) {
                                            if (!$isValidRange($target->Target_Year)) {
                                                return false;
                                            }
                                            $parts = explode('-', $target->Target_Year);
                                            $startYear = (int)$parts[0];
                                            return $startYear >= $validStartYear;
                                        });
                                        if ($validTargets->count() >= 1) {
                                            $withTargetForSO++;
                                        }
                                    }
                                }
                                $withoutTargetForSO = $totalForSO - $withTargetForSO;
                                $percentComplete = $totalForSO > 0 ? round(($withTargetForSO / $totalForSO) * 100) : 0;
                            @endphp
                            <div class="overflow-hidden transition-all duration-300 bg-white border border-gray-200 rounded-lg shadow-lg so-card card hover:shadow-2xl" data-so-name="{{ $objective }}">
                                <div class="p-5 card-body">
                                    <div class="flex items-center justify-between mb-3">
                                        <h3 class="text-base font-semibold text-gray-800 card-title">
                                            <i class="mr-2 iconify" data-icon="lucide:tag"></i>
                                            {{ $objective }}
                                        </h3>
                                        <button class="text-gray-800 explain-so-detail-btn btn btn-ghost btn-sm btn-circle" data-objective="{{ $objective }}">
                                            <i class="iconify text-primary" data-icon="lucide:sparkles"></i>
                                        </button>
                                    </div>
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="radial-progress text-primary" style="--value:{{ $percentComplete }}; --size:5rem; --thickness: 0.5rem;">
                                            <span class="text-sm font-bold text-gray-800">{{ $percentComplete }}%</span>
                                        </div>
                                        <div class="space-y-1">
                                            <div class="flex items-center justify-between gap-2">
                                                <span class="text-xs text-gray-500">Total:</span>
                                                <span class="text-gray-800 badge badge-sm">{{ $totalForSO }}</span>
                                            </div>
                                            <div class="flex items-center justify-between gap-2">
                                                <span class="text-xs text-gray-500">With Target:</span>
                                                <span class="text-gray-800 badge badge-sm badge-success">{{ $withTargetForSO }}</span>
                                            </div>
                                            <div class="flex items-center justify-between gap-2">
                                                <span class="text-xs text-gray-500">Without:</span>
                                                <span class="text-gray-800 badge badge-sm badge-warning">{{ $withoutTargetForSO }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <div class="w-full h-2 bg-gray-200 rounded-full">
                                            <div class="h-2 rounded-full bg-primary" style="width: {{ $percentComplete }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </dialog>

    <!-- AI Explanation Modal -->
    <dialog id="aiExplanationModal" class="modal">
        <div class="modal-box bg-white max-w-3xl p-0 rounded-2xl overflow-hidden shadow-2xl border border-gray-200 flex flex-col h-[90vh]">
            <div class="sticky top-0 z-10 flex items-center justify-between px-6 py-4 bg-gray-100 shadow-md modal-header dark:bg-gray-200">
                <h3 class="text-lg font-bold text-gray-800" id="aiModalTitle">Graph Explanation</h3>
                <button class="btn btn-circle btn-ghost" id="closeAIExplanationBtn">
                    <i class="text-2xl iconify" data-icon="lucide:x"></i>
                </button>
            </div>
            <div class="flex-1 overflow-y-auto">
                <div class="p-6">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="flex items-center justify-center w-12 h-12 rounded-full bg-primary/10">
                            <i class="text-3xl iconify text-primary" data-icon="lucide:sparkles"></i>
                        </div>
                        <div>
                            <h4 class="text-lg font-semibold text-gray-800">AI Insights</h4>
                            <p class="text-sm text-gray-500">Data interpretation and analysis</p>
                        </div>
                    </div>
                    <div id="aiExplanationContent" class="p-6 prose text-gray-800 bg-gray-100 border border-gray-200 max-w-none dark:bg-gray-200 rounded-xl">
                        <div class="animate-pulse">
                            <div class="w-3/4 h-4 mb-4 bg-gray-200 rounded"></div>
                            <div class="w-1/2 h-4 mb-4 bg-gray-200 rounded"></div>
                            <div class="w-5/6 h-4 mb-4 bg-gray-200 rounded"></div>
                            <div class="w-2/3 h-4 mb-4 bg-gray-200 rounded"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </dialog>

    <!-- Notification Toast -->
    <div class="toast toast-top toast-end">
        @if (session('notifications'))
            <div class="alert alert-{{ session('notifications.type') }} shadow-lg text-gray-800">
                <span>{{ session('notifications.message') }}</span>
            </div>
        @endif
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    function safeJSONParse(jsonString, fallback = {}) {
        try {
            return JSON.parse(jsonString);
        } catch (error) {
            console.error('Error parsing JSON:', error);
            return fallback;
        }
    }
    const targetsData = safeJSONParse('{!! addslashes($targetsDataJson) !!}', {});
    const indicatorsData = safeJSONParse('{!! addslashes($indicatorsDataJson) !!}', {});
    const soData = safeJSONParse('{!! addslashes($soDataJson) !!}', {});

    const soCategories = {!! json_encode($soCategories, JSON_HEX_APOS | JSON_HEX_QUOT) !!};
    const soWithTargets = {!! json_encode($soWithTargets, JSON_HEX_APOS | JSON_HEX_QUOT) !!};
    const soWithoutTargets = {!! json_encode($soWithoutTargets, JSON_HEX_APOS | JSON_HEX_QUOT) !!};

    const validStartYear = {{ $validStartYear }};
    const validEndYear = {{ $validEndYear }};

    const colors = {
        primary: '#007aff',
        success: '#34c759',
        warning: '#ff9500',
        danger: '#ff3b30',
        info: '#5ac8fa',
        background: '#ffffff',
        backgroundDark: '#1e293b',
        text: '#1e293b',
        textDark: '#f8fafc'
    };

    const prefersDarkMode = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    if (prefersDarkMode) { document.documentElement.classList.add('dark'); }

    function filterIndicators(objective) {
        const tabs = document.querySelectorAll('.tabs .tab');
        tabs.forEach(tab => { tab.classList.toggle('tab-active', tab.dataset.objective === objective); });
        const cards = document.querySelectorAll('#indicatorsGrid [data-objective]');
        cards.forEach(card => {
            if (objective === 'all' || card.dataset.objective === objective) {
                card.style.display = 'block';
                card.classList.remove('opacity-0', 'scale-95');
                card.classList.add('opacity-100', 'scale-100');
            } else {
                card.classList.remove('opacity-100', 'scale-100');
                card.classList.add('opacity-0', 'scale-95');
                setTimeout(() => card.style.display = 'none', 300);
            }
        });
    }

    function getChartTheme() {
        const isDark = document.documentElement.classList.contains('dark');
        return { mode: isDark ? 'dark' : 'light', palette: 'palette1', monochrome: { enabled: false } };
    }

    function initMainChart() {
        var soChartOptions = {
            chart: {
                type: 'bar',
                height: 350,
                stacked: true,
                toolbar: { show: true, tools: { download: true, selection: false, zoom: false, zoomin: false, zoomout: false, pan: false, reset: false } },
                animations: { enabled: true, easing: 'easeinout', speed: 800, dynamicAnimation: { enabled: true, speed: 350 } }
            },
            plotOptions: { bar: { horizontal: false, borderRadius: 8, columnWidth: '60%', dataLabels: { position: 'top' } } },
            theme: getChartTheme(),
            stroke: { width: 2, colors: ['transparent'] },
            dataLabels: { enabled: false },
            series: [{ name: 'With Valid Targets', data: soWithTargets }, { name: 'Need More Targets', data: soWithoutTargets }],
            xaxis: { categories: soCategories, labels: { style: { fontSize: '12px', fontFamily: 'inherit' } } },
            yaxis: { title: { text: 'Number of Indicators', style: { fontSize: '14px', fontFamily: 'inherit' } } },
            fill: { opacity: 1, colors: [colors.success, colors.warning] },
            tooltip: { y: { formatter: function(val) { return val + " indicators"; } } },
            title: { text: 'Target Setting Status by Strategic Objective', align: 'left', style: { fontSize: '16px', fontWeight: 600, fontFamily: 'inherit' } },
            legend: { position: 'top', fontSize: '14px', fontFamily: 'inherit', labels: { colors: document.documentElement.classList.contains('dark') ? colors.textDark : colors.text } },
            grid: { borderColor: document.documentElement.classList.contains('dark') ? '#334155' : '#e2e8f0', strokeDashArray: 5 }
        };
        if (window.soChart && typeof window.soChart.destroy === 'function') { window.soChart.destroy(); }
        window.soChart = new ApexCharts(document.querySelector("#soChart"), soChartOptions);
        window.soChart.render();
    }

    const targetGraphModal = document.getElementById('targetGraphModal');
    const deleteTargetModal = document.getElementById('deleteTargetModal');
    const aiExplanationModal = document.getElementById('aiExplanationModal');
    const allSOModal = document.getElementById('allSOModal');
    let lineChart = null;

    function openTargetGraphModal(mode, indicatorId, targetId = null) {
        try {
            const indicator = indicatorsData[indicatorId];
            if (!indicator) { console.error('Indicator not found:', indicatorId); return; }
            document.getElementById('graphIndicatorID').value = indicator.id;
            document.getElementById('graphResponseType').value = indicator.responseType;
            const form = document.getElementById('targetGraphForm');
            const methodOverrideDiv = document.getElementById('methodOverride');
            const targetValueContainer = document.getElementById('graphTargetValueContainer');
            switch (indicator.responseType) {
                case 'Number':
                    targetValueContainer.innerHTML = '<label class="label"><span class="font-medium text-gray-800 label-text">Target Value</span></label><input type="number" name="Target_Value" class="text-gray-800 rounded-lg input input-bordered" min="0" required id="graphTargetValue">';
                    break;
                case 'Boolean':
                    targetValueContainer.innerHTML = '<label class="label"><span class="font-medium text-gray-800 label-text">Target Value</span></label><select name="Target_Value" class="text-gray-800 rounded-lg select select-bordered" required id="graphTargetValue"><option value="true">True</option><option value="false">False</option></select>';
                    break;
                case 'Yes/No':
                    targetValueContainer.innerHTML = '<label class="label"><span class="font-medium text-gray-800 label-text">Target Value</span></label><select name="Target_Value" class="text-gray-800 rounded-lg select select-bordered" required id="graphTargetValue"><option value="Yes">Yes</option><option value="No">No</option></select>';
                    break;
                case 'Text':
                default:
                    targetValueContainer.innerHTML = '<label class="label"><span class="font-medium text-gray-800 label-text">Target Value</span></label><input type="text" name="Target_Value" class="text-gray-800 rounded-lg input input-bordered" required id="graphTargetValue">';
                    break;
            }
            if (mode === 'edit') {
                const targetToEdit = indicator.allTargets.find(t => t.id == targetId);
                if (!targetToEdit) { console.error('Target not found for edit mode'); return; }
                if (targetToEdit.isLegacy) {
                    alert('Legacy targets cannot be edited. Please create new targets within the valid timeframe.');
                    return;
                }
                form.action = `{{ url('/targets') }}/${targetId}`;
                methodOverrideDiv.innerHTML = `<input type="hidden" name="_method" value="PUT">`;
                document.getElementById('graphTargetYear').value = targetToEdit.year;
                document.getElementById('graphTargetValue').value = targetToEdit.value;
                document.getElementById('graphSubmitButton').innerText = 'Update Target';
            } else {
                form.action = `{{ route('targets.store') }}`;
                methodOverrideDiv.innerHTML = ``;
                document.getElementById('graphTargetYear').selectedIndex = 0;
                document.getElementById('graphTargetValue').value = '';
                document.getElementById('graphSubmitButton').innerText = 'Save Target';
            }
            document.getElementById('graphModalTitle').innerText = `Targets for: ${indicator.name}`;
            const allTargets = [...indicator.allTargets].sort((a, b) => parseInt(a.year.split('-')[0]) - parseInt(b.year.split('-')[0]));
            updateTargetStatusInfo(indicator);
            if (lineChart) { lineChart.destroy(); }
            const validTargets = allTargets.filter(t => !t.isLegacy);
            const legacyTargets = allTargets.filter(t => t.isLegacy);
            lineChart = new ApexCharts(document.querySelector("#targetLineChart"), {
                chart: { type: 'line', height: 320, animations: { enabled: true, easing: 'easeinout', speed: 800 }, toolbar: { show: true, tools: { download: true } } },
                theme: getChartTheme(),
                series: [{ name: 'Valid Targets', data: validTargets.map(item => item.value) }, { name: 'Legacy Targets', data: legacyTargets.map(item => item.value) }],
                xaxis: {
                    categories: allTargets.map(item => item.year),
                    title: { text: 'Target Range', style: { fontSize: '14px', fontFamily: 'inherit' } },
                    labels: { style: { fontSize: '12px', fontFamily: 'inherit' } }
                },
                yaxis: { title: { text: 'Target Value', style: { fontSize: '14px', fontFamily: 'inherit' } }, labels: { style: { fontSize: '12px', fontFamily: 'inherit' } } },
                title: { text: `Target Trend: ${indicator.name}`, align: 'left', style: { fontSize: '16px', fontWeight: 600, fontFamily: 'inherit' } },
                colors: [colors.primary, colors.warning],
                stroke: { curve: 'smooth', width: 3 },
                markers: { size: 6, strokeWidth: 0, hover: { size: 8 } },
                grid: { borderColor: document.documentElement.classList.contains('dark') ? '#334155' : '#e2e8f0', strokeDashArray: 5 },
                tooltip: { x: { format: 'yyyy' } },
                annotations: {
                    xaxis: [{
                        x: validStartYear,
                        borderColor: colors.info,
                        label: { text: 'Valid Start', style: { color: '#fff', background: colors.info } }
                    }]
                }
            });
            lineChart.render();
            targetGraphModal.showModal();
        } catch (error) {
            console.error('Error opening target graph modal:', error);
            alert('An error occurred while opening the target graph. Please try again.');
        }
    }

    function updateTargetStatusInfo(indicator) {
        const validTargets = indicator.validTargets || [];
        const legacyTargets = indicator.legacyTargets || [];
        const hasValidTargets = indicator.hasValidTargets;
        let statusHtml = '';
        if (hasValidTargets) {
            statusHtml = `
            <div class="mb-3 text-green-800 bg-green-100 rounded-lg alert">
                <i class="iconify" data-icon="lucide:check-circle"></i>
                <span>This indicator has ${validTargets.length} valid target (two-year range) from 2024 onwards (minimum of 1 required).</span>
            </div>
            `;
        } else {
            const neededTargets = Math.max(0, 1 - validTargets.length);
            statusHtml = `
            <div class="mb-3 text-yellow-800 bg-yellow-100 rounded-lg alert">
                <i class="iconify" data-icon="lucide:alert-triangle"></i>
                <span>This indicator needs ${neededTargets} more target (two-year range) from 2024 onwards. Currently has ${validTargets.length} valid target.</span>
            </div>
            `;
        }
        if (legacyTargets.length > 0) {
            statusHtml += `
            <div class="mb-3 text-blue-800 bg-blue-100 rounded-lg alert">
                <i class="iconify" data-icon="lucide:info"></i>
                <span>This indicator has ${legacyTargets.length} legacy target (two-year range) from before 2024. Legacy targets cannot be edited or deleted and are not counted.</span>
            </div>
            `;
        }
        document.getElementById('targetStatusInfo').innerHTML = statusHtml;
    }

    function closeTargetGraphModal() { targetGraphModal.close(); }
    function openDeleteTargetModal(targetId) {
        const deleteForm = document.getElementById('deleteTargetForm');
        deleteForm.action = `{{ url('/targets') }}/${targetId}`;
        deleteTargetModal.showModal();
    }
    function closeDeleteTargetModal() { deleteTargetModal.close(); }
    let previousModalId = null;
    function filterSOCards() {
        const searchTerm = document.getElementById('soSearchInput').value.toLowerCase();
        const cards = document.querySelectorAll('.so-card');
        cards.forEach(card => {
            const soName = card.dataset.soName.toLowerCase();
            card.style.display = soName.includes(searchTerm) ? 'block' : 'none';
        });
    }
    function openAIExplanationModal(title, objective) {
        try {
            const aiModalTitle = document.getElementById('aiModalTitle');
            const aiExplanationContent = document.getElementById('aiExplanationContent');
            aiModalTitle.textContent = title;
            aiExplanationContent.innerHTML =
                '<div class="animate-pulse"><div class="w-3/4 h-4 mb-4 bg-gray-200 rounded"></div><div class="w-1/2 h-4 mb-4 bg-gray-200 rounded"></div><div class="w-5/6 h-4 mb-4 bg-gray-200 rounded"></div><div class="w-2/3 h-4 mb-4 bg-gray-200 rounded"></div></div>';
            aiExplanationModal.showModal();
            if (objective) {
                generateSOSpecificInsights(objective);
            } else {
                generateOverallInsights();
            }
        } catch (error) {
            console.error('Error opening AI explanation modal:', error);
        }
    }
    function closeAIExplanationModal() {
        aiExplanationModal.close();
        if (previousModalId) {
            document.getElementById(previousModalId).showModal();
            previousModalId = null;
        }
    }
    function generateOverallInsights() {
        try {
            setTimeout(() => {
                const soDataAnalysis = Object.values(soData);
                let totalIndicators = {{ $indicators->flatten()->count() }};
                let withTarget = 0;
                soDataAnalysis.forEach(so => { withTarget += so.withTarget; });
                let withoutTarget = totalIndicators - withTarget;
                let percentComplete = Math.round((withTarget / totalIndicators) * 100);
                soDataAnalysis.sort((a, b) => b.percentComplete - a.percentComplete);
                const bestSO = soDataAnalysis[0];
                const worstSO = soDataAnalysis[soDataAnalysis.length - 1];
                const top5 = soDataAnalysis.slice(0, 5);
                const bottom5 = [...soDataAnalysis].sort((a, b) => a.percentComplete - b.percentComplete).slice(0, 5);
                let insights = `
                <h3 class="mb-4 text-lg font-semibold text-gray-800">Target Setting Analysis for {{ $cluster->Cluster_Name }}</h3>
                <div class="p-4 mb-6 text-gray-800 border bg-info/10 rounded-xl border-info/20">
                    <p class="mb-2 font-medium text-info">About This Analysis</p>
                    <p class="text-sm">This analysis focuses on <strong>target setting progress</strong> using two-year ranges. A strategic objective is considered complete when:</p>
                    <ul class="pl-5 mt-2 text-sm list-disc">
                        <li>Each indicator has <strong>at least 1 valid target</strong> (as a two-year range starting from 2024)</li>
                        <li>Only targets with a starting year from 2024 and later are counted as valid</li>
                        <li>Any target not matching the valid format is ignored or considered legacy</li>
                    </ul>
                </div>
                <div class="p-4 mb-6 text-gray-800 bg-base-200/50 rounded-xl">
                    <div class="flex flex-col items-center justify-between gap-4 md:flex-row">
                        <div class="text-center">
                            <div class="text-4xl font-bold text-primary">${percentComplete}%</div>
                            <div class="text-sm text-gray-500">Target Setting Progress</div>
                        </div>
                        <div class="text-center">
                            <div class="text-4xl font-bold text-success">${withTarget}</div>
                            <div class="text-sm text-gray-500">With Valid Targets</div>
                        </div>
                        <div class="text-center">
                            <div class="text-4xl font-bold text-warning">${withoutTarget}</div>
                            <div class="text-sm text-gray-500">Need More Targets</div>
                        </div>
                        <div class="text-center">
                            <div class="text-4xl font-bold text-info">${soDataAnalysis.length}</div>
                            <div class="text-sm text-gray-500">Strategic Objectives</div>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-6 mb-6 md:grid-cols-2">
                    <div class="p-4 text-gray-800 border bg-success/10 rounded-xl border-success/20">
                        <h4 class="mb-3 font-semibold text-success">Indicators with Valid Targets</h4>
                        <div class="pr-2 overflow-y-auto max-h-60">
                            ${top5.map(so => `
                                <li class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-gray-800">${so.objective}</span>
                                    <span class="text-gray-800 badge badge-success">${so.percentComplete}%</span>
                                </li>
                            `).join('')}
                        </div>
                    </div>
                    <div class="p-4 text-gray-800 border bg-warning/10 rounded-xl border-warning/20">
                        <h4 class="mb-3 font-semibold text-warning">Needs Target Setting Attention</h4>
                        <div class="pr-2 overflow-y-auto max-h-60">
                            ${bottom5.map(so => `
                                <li class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-gray-800">${so.objective}</span>
                                    <span class="text-gray-800 badge badge-warning">${so.percentComplete}%</span>
                                </li>
                            `).join('')}
                        </div>
                    </div>
                </div>
                <h4 class="mt-5 mb-2 font-semibold text-gray-800">Strategic Recommendations:</h4>
                <ul class="pl-5 mb-4 space-y-2 text-gray-800 list-disc">
                    <li>Prioritize setting at least 1 valid target (two-year range) from 2024 onwards for indicators in ${worstSO.objective}</li>
                    <li>Review any legacy or invalid targets and consider setting a new valid target from 2024 onwards</li>
                    <li>Consider replicating the target-setting approach from ${bestSO.objective} to other areas</li>
                </ul>
                <div class="p-4 mt-5 text-gray-800 border rounded-lg bg-primary/10 border-primary/20">
                    <p class="font-medium text-primary">This analysis focuses on target setting completeness using two-year ranges. Valid target setting is essential for effective monitoring and evaluation.</p>
                </div>
                `;
                document.getElementById('aiExplanationContent').innerHTML = insights;
            }, 1500);
        } catch (error) {
            console.error('Error generating overall insights:', error);
            document.getElementById('aiExplanationContent').innerHTML = '<p class="text-danger">An error occurred while generating insights. Please try again.</p>';
        }
    }

    function generateSOSpecificInsights(objective) {
        try {
            setTimeout(() => {
                const soInfo = soData[objective];
                if (!soInfo) { console.error('Strategic objective not found:', objective); return; }
                let detailedAnalysis = `
                <h3 class="mb-4 text-lg font-semibold text-gray-800">${objective} - Target Setting Analysis</h3>
                <div class="p-4 mb-6 text-gray-800 border bg-info/10 rounded-xl border-info/20">
                    <p class="mb-2 font-medium text-info">About This Analysis</p>
                    <p class="text-sm">This analysis focuses on <strong>target setting progress</strong> using two-year ranges. An indicator is considered complete when:</p>
                    <ul class="pl-5 mt-2 text-sm list-disc">
                        <li>It has <strong>at least 1 valid target</strong> (as a two-year range starting from 2024)</li>
                        <li>Only targets with a starting year from 2024 and later are counted as valid</li>
                        <li>Any target not matching the valid format is ignored or considered legacy</li>
                    </ul>
                </div>
                <div class="p-4 mb-6 text-gray-800 bg-base-200/50 rounded-xl">
                    <div class="flex flex-col items-center justify-between gap-4 md:flex-row">
                        <div class="text-center">
                            <div class="text-4xl font-bold text-primary">${soInfo.percentComplete}%</div>
                            <div class="text-sm text-gray-500">Target Setting Progress</div>
                        </div>
                        <div class="text-center">
                            <div class="text-4xl font-bold text-success">${soInfo.withTarget}</div>
                            <div class="text-sm text-gray-500">With Valid Targets</div>
                        </div>
                        <div class="text-center">
                            <div class="text-4xl font-bold text-warning">${soInfo.withoutTarget}</div>
                            <div class="text-sm text-gray-500">Need More Targets</div>
                        </div>
                        <div class="text-center">
                            <div class="text-4xl font-bold text-info">${soInfo.total}</div>
                            <div class="text-sm text-gray-500">Total Indicators</div>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-6 mb-6 md:grid-cols-2">
                    <div class="p-4 text-gray-800 border bg-success/10 rounded-xl border-success/20">
                        <h4 class="mb-3 font-semibold text-success">Indicators with Valid Targets</h4>
                        <div class="pr-2 overflow-y-auto max-h-60">
                            ${soInfo.indicatorsWithTargets.length > 0 ? `
                                <ul class="space-y-2">
                                    ${soInfo.indicatorsWithTargets.map(indicator => {
                                        let validRanges = indicator.validTargets.map(t => t.year).join(', ');
                                        let legacyRanges = indicator.legacyTargets.length > 0 ?
                                            `<div class="mt-1 text-xs text-gray-500">Legacy ranges (before 2024): ${indicator.legacyTargets.map(t => t.year).join(', ')}</div>` : '';
                                        return `
                                            <li class="text-sm text-gray-800">
                                                <div class="font-medium">${indicator.number}</div>
                                                <div class="text-xs text-gray-600">${indicator.name}</div>
                                                <div class="mt-1 text-xs text-success">Valid ranges (2024+): ${validRanges}</div>
                                                ${legacyRanges}
                                            </li>
                                        `;
                                    }).join('')}
                                </ul>
                            ` : `<p class="text-sm text-gray-500">No indicators have valid targets set for this objective</p>`}
                        </div>
                    </div>
                    <div class="p-4 text-gray-800 border bg-warning/10 rounded-xl border-warning/20">
                        <h4 class="mb-3 font-semibold text-warning">Indicators Needing More Targets</h4>
                        <div class="pr-2 overflow-y-auto max-h-60">
                            ${soInfo.indicatorsWithoutTargets.length > 0 ? `
                                <ul class="space-y-2">
                                    ${soInfo.indicatorsWithoutTargets.map(indicator => {
                                        let validTargetsHtml = '';
                                        let legacyTargetsHtml = '';
                                        if (indicator.validTargets && indicator.validTargets.length > 0) {
                                            validTargetsHtml = `
                                                <div class="mt-1 text-xs text-gray-500">
                                                    Current valid ranges (2024+): ${indicator.validTargets.map(t => t.year).join(', ')}
                                                </div>
                                            `;
                                        }
                                        if (indicator.legacyTargets && indicator.legacyTargets.length > 0) {
                                            legacyTargetsHtml = `
                                                <div class="mt-1 text-xs text-gray-500">
                                                    Legacy ranges (before 2024): ${indicator.legacyTargets.map(t => t.year).join(', ')}
                                                </div>
                                            `;
                                        }
                                        return `
                                            <li class="text-sm text-gray-800">
                                                <div class="font-medium">${indicator.number}</div>
                                                <div class="text-xs text-gray-600">${indicator.name}</div>
                                                ${validTargetsHtml}
                                                ${legacyTargetsHtml}
                                                <div class="mt-1 text-xs text-warning">
                                                    <i class="mr-1 iconify" data-icon="lucide:alert-triangle"></i>
                                                    ${indicator.reason}
                                                </div>
                                            </li>
                                        `;
                                    }).join('')}
                                </ul>
                            ` : `<p class="text-sm text-gray-500">All indicators have valid targets set for this objective - Great job!</p>`}
                        </div>
                    </div>
                </div>
                <div class="p-4 mt-5 text-gray-800 border rounded-lg bg-primary/10 border-primary/20">
                    <p class="font-medium text-primary">This analysis focuses on target setting completeness using two-year ranges. Valid target setting is essential for effective monitoring and evaluation.</p>
                </div>
                `;
                document.getElementById('aiExplanationContent').innerHTML = detailedAnalysis;
            }, 1200);
        } catch (error) {
            console.error('Error generating SO specific insights:', error);
            document.getElementById('aiExplanationContent').innerHTML = '<p class="text-danger">An error occurred while generating insights. Please try again.</p>';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        try {
            initMainChart();
            document.addEventListener('click', function(e) {
                if (e.target.closest('.set-target-btn')) {
                    const btn = e.target.closest('.set-target-btn');
                    openTargetGraphModal('create', btn.dataset.indicatorId);
                }
                if (e.target.closest('.edit-target-btn')) {
                    const btn = e.target.closest('.edit-target-btn');
                    openTargetGraphModal('edit', btn.dataset.indicatorId, btn.dataset.targetId);
                }
                if (e.target.closest('.delete-target-btn')) {
                    const btn = e.target.closest('.delete-target-btn');
                    openDeleteTargetModal(btn.dataset.targetId);
                }
                if (e.target.closest('.tabs .tab')) {
                    const tab = e.target.closest('.tab');
                    filterIndicators(tab.dataset.objective);
                }
                if (e.target.closest('.explain-so-detail-btn')) {
                    const btn = e.target.closest('.explain-so-detail-btn');
                    const objective = btn.dataset.objective;
                    if (allSOModal.open) { previousModalId = 'allSOModal'; allSOModal.close(); }
                    openAIExplanationModal(`${objective} Analysis`, objective);
                }
            });
            document.getElementById('viewAllSOBtn').addEventListener('click', function() {
                allSOModal.showModal();
            });
            document.getElementById('explainSOBtn').addEventListener('click', function() {
                openAIExplanationModal('Strategic Objective Metrics Analysis', null);
            });
            document.getElementById('closeTargetGraphBtn').addEventListener('click', closeTargetGraphModal);
            document.getElementById('cancelTargetBtn').addEventListener('click', closeTargetGraphModal);
            document.getElementById('closeAllSOBtn').addEventListener('click', function() { allSOModal.close(); });
            document.getElementById('closeAIExplanationBtn').addEventListener('click', closeAIExplanationModal);
            document.getElementById('cancelDeleteBtn').addEventListener('click', closeDeleteTargetModal);
            document.getElementById('soSearchInput').addEventListener('input', filterSOCards);
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
                if (e.matches) { document.documentElement.classList.add('dark'); }
                else { document.documentElement.classList.remove('dark'); }
                initMainChart();
            });
        } catch (error) {
            console.error('Error initializing application:', error);
            alert('An error occurred while initializing the application. Please refresh the page and try again.');
        }
    });
</script>
