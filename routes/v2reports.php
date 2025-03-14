<?php
use App\Http\Controllers\ClusterTargetController;
use App\Http\Controllers\CRFScoreBoardController;
use App\Http\Controllers\RRFSmartController;
use App\Http\Controllers\V2_ALL_PerformanceDashboardController;
use App\Http\Controllers\V2_PerformanceDashboardController;
use Illuminate\Support\Facades\Route;

// Indicator Performance Report Routes
Route::middleware(['auth'])->group(function () {
    // Cluster Selection
    Route::get('/indicator-performance/select-cluster', [
        'as'   => 'indicator.select.cluster',
        'uses' => 'App\Http\Controllers\V2\IndicatorPerformanceController@selectCluster',
    ]);

    // Timeline Selection
    Route::get('/indicator-performance/select-timeline', [
        'as'   => 'indicator.select.timeline',
        'uses' => 'App\Http\Controllers\V2\IndicatorPerformanceController@selectTimeline',
    ]);

    // Generate Report
    Route::get('/indicator-performance/report', [
        'as'   => 'indicator.report',
        'uses' => 'App\Http\Controllers\V2\IndicatorPerformanceController@generateReport',
    ]);

    // Filter Report
    Route::post('/indicator-performance/filter', [
        'as'   => 'indicator.filter',
        'uses' => 'App\Http\Controllers\V2\IndicatorPerformanceController@filterReport',
    ]);

    // Export Report
    Route::get('/indicator-performance/export', [
        'as'   => 'indicator.export',
        'uses' => 'App\Http\Controllers\V2\IndicatorPerformanceController@exportReport',
    ]);
//
//
//
//
//
//
//
//
//
//
//

    // Add this route definition:
    Route::get('/crf-scoreboard', [CRFScoreBoardController::class, 'showCRFScoreboard'])
        ->name('crf.scoreboard');

    Route::get('/crf/export-excel', [CRFScoreBoardController::class, 'exportCRFScoreboardExcel'])
        ->name('crf.export.excel');

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

    Route::get('/rrf/scoreboard', [RRFSmartController::class, 'showSmartRRFScoreboard'])
        ->name('rrf.scoreboard');

    Route::get('/rrf/scoreboard/export', [RRFSmartController::class, 'exportSmartRRFScoreboardExcel'])
        ->name('rrf.scoreboard.export');

//
//
//
//
    Route::prefix('targets')->name('targets.')->group(function () {
        // Display the cluster selection view for target management.
        Route::get('/', [ClusterTargetController::class, 'index'])->name('index');

        // Display the target management form for a selected cluster.
        // (This route expects a query parameter or form submission with ClusterID.)
        Route::get('/setup', [ClusterTargetController::class, 'showTargetForm'])->name('setup');

        // Store a new target.
        Route::post('/', [ClusterTargetController::class, 'saveTarget'])->name('store');

        // Update an existing target.
        Route::put('/{target}', [ClusterTargetController::class, 'updateTarget'])->name('update');

        // Delete an existing target.
        Route::delete('/{target}', [ClusterTargetController::class, 'delete'])->name('destroy');
    });
//
//
//
//
//
//
//

// // Route to show the form for selecting a cluster and timeline
//     Route::get('/cluster-reporting/select', [EcsaV2ReportingController::class, 'selectClusterAndTimeline'])
//         ->name('cluster.reporting.select');

// // Route to handle the submission of the cluster and timeline selection form
//     Route::post('/cluster-reporting/select', [EcsaV2ReportingController::class, 'handleClusterAndTimelineSelection'])
//         ->name('cluster.reporting.handleSelection');

// // Route to show the form for entering performance data and tracking progress
//     Route::get('/cluster-reporting/enter-data', [EcsaV2ReportingController::class, 'enterPerformanceData'])
//         ->name('cluster.reporting.enterData');

// // Route to handle the submission of performance data
//     Route::post('/cluster-reporting/submit-data', [EcsaV2ReportingController::class, 'submitPerformanceData'])
//         ->name('cluster.reporting.submitData');

// Cluster Selection (remains the same)
    Route::get('/performance/cluster', [V2_PerformanceDashboardController::class, 'showClusterSelection'])
        ->name('performance.cluster.selection');
    Route::post('/performance/cluster', [V2_PerformanceDashboardController::class, 'processClusterSelection'])
        ->name('performance.cluster.process');

// Timeline Selection: Pass the cluster_id via the URL
    Route::get('/performance/timeline/{cluster_id}', [V2_PerformanceDashboardController::class, 'showTimelineSelection'])
        ->name('performance.timeline.selection');
    Route::post('/performance/timeline/{cluster_id}', [V2_PerformanceDashboardController::class, 'processTimelineSelection'])
        ->name('performance.timeline.process');

// Performance Dashboard: Both cluster_id and timeline_id in the URL
    Route::get('/performance/dashboard/{cluster_id}/{timeline_id}', [V2_PerformanceDashboardController::class, 'showPerformanceDashboard'])
        ->name('performance.dashboard');

// Generate Report: Both cluster_id and timeline_id in the URL
    Route::get('/performance/report/{cluster_id}/{timeline_id}', [V2_PerformanceDashboardController::class, 'generatePerformanceReport'])
        ->name('performance.report');

    //
    //
    //
    //
    //
    //
    //
    // // Cluster selection routes
    Route::get('/V2_ALL_performance/cluster-selection', [V2_ALL_PerformanceDashboardController::class, 'showClusterSelection'])
        ->name('V2_ALL_performance.cluster.selection');

    Route::post('/V2_ALL_performance/cluster-selection', [V2_ALL_PerformanceDashboardController::class, 'processClusterSelection'])
        ->name('V2_ALL_performance.cluster.selection.process');

// Timeline selection routes
    Route::get('/V2_ALL_performance/timeline-selection', [V2_ALL_PerformanceDashboardController::class, 'showTimelineSelection'])
        ->name('V2_ALL_performance.timeline.selection');

    Route::post('/V2_ALL_performance/timeline-selection', [V2_ALL_PerformanceDashboardController::class, 'processTimelineSelection'])
        ->name('V2_ALL_performance.timeline.selection.process');

// Dashboard display route
    Route::get('/V2_ALL_performance/dashboard/{timeline_id}', [V2_ALL_PerformanceDashboardController::class, 'showPerformanceDashboard'])
        ->name('V2_ALL_performance.dashboard');

// Report generation route
    Route::get('/V2_ALL_performance/report/{timeline_id}', [V2_ALL_PerformanceDashboardController::class, 'generatePerformanceReport'])
        ->name('V2_ALL_performance.report.generate');

});