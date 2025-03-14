<?php

use App\Http\Controllers\ClusterPerformanceBreakdownController;
use App\Http\Controllers\Clusters;
use App\Http\Controllers\CrudController;
use App\Http\Controllers\EcsahcTimelines;
use App\Http\Controllers\EcsaIndicatorPerformanceController;
use App\Http\Controllers\EcsaReportingController;
use App\Http\Controllers\EntitiesController;
use App\Http\Controllers\IndicatorReportController;
use App\Http\Controllers\IndicatorsController;
use App\Http\Controllers\MpaIndicatorsController;
use App\Http\Controllers\MpaReportingCompleteness;
use App\Http\Controllers\MpaRRFController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RRFReportController;
use App\Http\Controllers\StrategicObjectivePerfomance;
use App\Http\Controllers\UserAccountController;
use App\Http\Controllers\UsersController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {

    Route::get('rrf/report/select', [RRFReportController::class, 'selectReport'])
        ->name('rrf.report.selectReport');

    Route::match(['get', 'post'], 'rrf/report/select-year', [RRFReportController::class, 'selectYear'])
        ->name('rrf.report.selectYear');

    Route::post('rrf/report/dashboard', [RRFReportController::class, 'dashboard'])
        ->name('rrf.report.dashboard');

    Route::post('rrf/report/export', [RRFReportController::class, 'exportExcel'])
        ->name('rrf.report.exportExcel');

    //
    //
    //
    //
    //
    //
    //
    //

    Route::get('/mpa/reports/completeness/select-year', [MpaReportingCompleteness::class, 'index'])->name('mpa.reports.completeness.select_year');

    Route::get('/mpa/reports/completeness', [MpaReportingCompleteness::class, 'index'])->name('mpa.reports.completeness.index');

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
    //
    //

    Route::get('/entity/select', [IndicatorReportController::class, 'selectEntity'])
        ->name('entity.select');

    Route::get('/reporting/period/select', [IndicatorReportController::class, 'selectReportingPeriod'])
        ->name('reporting.period.select');

    Route::get('/indicators/show', [IndicatorReportController::class, 'showIndicators'])
        ->name('indicator.show');

    Route::post('/reports/submit', [IndicatorReportController::class, 'submitReports'])
        ->name('indicator.submit');

    Route::get('/report/summary/{entityID}/{year}/{reportingPeriod}', [IndicatorReportController::class, 'showReportSummary'])
        ->name('indicator.report.summary');

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

    Route::get('/Ecsa_CP_selectYear', [ClusterPerformanceBreakdownController::class, 'Ecsa_CP_selectYear'])
        ->name('Ecsa_CP_selectYear');

    Route::get('/Ecsa_CP_selectReport', [ClusterPerformanceBreakdownController::class, 'Ecsa_CP_selectReport'])
        ->name('Ecsa_CP_selectReport');

    Route::get('/Ecsa_CP_showPerformance', [ClusterPerformanceBreakdownController::class, 'Ecsa_CP_showPerformance'])
        ->name('Ecsa_CP_showPerformance');

    Route::get('/Ecsa_CP_exportCsv', [ClusterPerformanceBreakdownController::class, 'Ecsa_CP_exportCsv'])
        ->name('Ecsa_CP_exportCsv');

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
    //

    Route::get('/Ecsa_SO_selectYear', [StrategicObjectivePerfomance::class, 'Ecsa_SO_selectYear'])
        ->name('Ecsa_SO_selectYear');

    Route::get('/Ecsa_SO_selectReport', [StrategicObjectivePerfomance::class, 'Ecsa_SO_selectReport'])
        ->name('Ecsa_SO_selectReport');

    Route::get('/Ecsa_SO_showPerformance', [StrategicObjectivePerfomance::class, 'Ecsa_SO_showPerformance'])
        ->name('Ecsa_SO_showPerformance');

    Route::get('/Ecsa_SO_exportCsv', [StrategicObjectivePerfomance::class, 'Ecsa_SO_exportCsv'])
        ->name('Ecsa_SO_exportCsv');

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
    //
    //

    Route::get('/Reportselectcluster', [EcsaIndicatorPerformanceController::class, 'selectCluster'])->name('Reportselectcluster');

    // Route::get('/', [EcsaIndicatorPerformanceController::class, 'selectCluster'])->name('dashboard');

    Route::get('/dashboard', [EcsaIndicatorPerformanceController::class, 'selectCluster'])->name('home');

    Route::post('/select-year', [EcsaIndicatorPerformanceController::class, 'selectYear'])->name('select-year');
    Route::post('/select-report', [EcsaIndicatorPerformanceController::class, 'selectReport'])->name('select-report');
    Route::post('/performance-overview', [EcsaIndicatorPerformanceController::class, 'showPerformance'])->name('performance-overview');
    Route::get('/export-csv', [EcsaIndicatorPerformanceController::class, 'exportCsv'])->name('export-csv');
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

    Route::get('/dashboard', [EcsaReportingController::class, 'SelectUser'])->name('dashboard');
    Route::get('/', [EcsaReportingController::class, 'SelectUser'])->name('home');

// Grouping under an 'ecsa' URI prefix for clarity.
    Route::prefix('ecsa')->group(function () {

        // GET route for selecting a user.
        Route::get('select-user', [EcsaReportingController::class, 'SelectUser'])
            ->name('Ecsa_SelectUser');

        // Route::get('/', [EcsaReportingController::class, 'SelectUser']);

        // POST route for selecting a cluster.
        Route::any('select-cluster', [EcsaReportingController::class, 'SelectCluster'])
            ->name('Ecsa_SelectCluster');

        // POST route for selecting a timeline.
        Route::any('select-timeline', [EcsaReportingController::class, 'SelectTimeline'])
            ->name('Ecsa_SelectTimeline');

        // POST route for selecting a strategic objective.
        Route::any('select-strategic-objective', [EcsaReportingController::class, 'SelectStrategicObjective'])
            ->name('Ecsa_SelectStrategicObjective');

        // POST route for reporting performance indicators.
        Route::any('report-performance-indicators', [EcsaReportingController::class, 'ReportPerformanceIndicators'])
            ->name('Ecsa_ReportPerformanceIndicators');

        // POST route for saving the performance report.
        Route::any('save-performance-report', [EcsaReportingController::class, 'SavePerformanceReport'])
            ->name('Ecsa_SavePerformanceReport');

        // POST route for getting the reporting summary.
        Route::any('get-reporting-summary', [EcsaReportingController::class, 'GetReportingSummary'])
            ->name('Ecsa_GetReportingSummary');
    });

    // Route::any('/select-user', [EcsaReportingController::class, 'SelectUser'])->name('SelectUser');

    // Route::any('/select-cluster', [EcsaReportingController::class, 'SelectCluster'])->name('SelectCluster');

    // Route::any('/select-timeline', [EcsaReportingController::class, 'SelectTimeline'])->name('SelectTimeline');

    // Route::any('/select-strategic-objective', [EcsaReportingController::class, 'SelectStrategicObjective'])->name('SelectStrategicObjective');

    // Route::any('/report-performance-indicators', [EcsaReportingController::class, 'ReportPerformanceIndicators'])->name('ReportPerformanceIndicators');

    // Route::any('/save-performance-report', [EcsaReportingController::class, 'SavePerformanceReport'])->name('SavePerformanceReport');

    // New routes
    Route::post('/mark-indicators-not-applicable', [EcsaReportingController::class, 'MarkIndicatorsNotApplicable'])->name('MarkIndicatorsNotApplicable');

    Route::get('/get-reporting-summary', [EcsaReportingController::class, 'GetReportingSummary'])->name('GetReportingSummary');

    //
    //
    //
    //
    //
    //
    //

    Route::get('/MgtEcsaTimelinesStatus', [EcsahcTimelines::class, 'MgtEcsaTimelinesStatus'])
        ->name('MgtEcsaTimelinesStatus');

    Route::get('/MgtMpaTimelinesStatus', [EcsahcTimelines::class, 'MgtMpaTimelinesStatus'])
        ->name('MgtMpaTimelinesStatus');

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

    // Display all RRF Indicators
    Route::get('/mpa-rrf-indicators', [MpaRRFController::class, 'ShowRRFIndicators'])
        ->name('mpaRRF.ShowRRFIndicators');

    // Create / Store a new RRF Indicator
    Route::post('/mpa-rrf-indicators/store', [MpaRRFController::class, 'StoreRRFIndicator'])
        ->name('mpaRRF.StoreRRFIndicator');

    // Update an RRF Indicator
    Route::put('/mpa-rrf-indicators/update', [MpaRRFController::class, 'UpdateRRFIndicator'])
        ->name('mpaRRF.UpdateRRFIndicator');

    // Delete an RRF Indicator
    Route::delete('/mpa-rrf-indicators/delete', [MpaRRFController::class, 'DeleteRRFIndicator'])
        ->name('mpaRRF.DeleteRRFIndicator');

//
//
//
//
//
//
//
//

// Step 1: Display entity selection
    Route::get('/mpa-indicators/select-entity', [MpaIndicatorsController::class, 'SelectEntity'])
        ->name('mpaIndicators.SelectEntity');

// Step 2: Show indicators for chosen entity
    Route::get('/mpa-indicators/entity-indicators', [MpaIndicatorsController::class, 'ShowEntityIndicators'])
        ->name('mpaIndicators.ShowEntityIndicators');

// Step 3: CRUD
    Route::post('/mpa-indicators/store', [MpaIndicatorsController::class, 'StoreIndicator'])
        ->name('mpaIndicators.StoreIndicator');

    Route::put('/mpa-indicators/update', [MpaIndicatorsController::class, 'UpdateIndicator'])
        ->name('mpaIndicators.UpdateIndicator');

    Route::delete('/mpa-indicators/delete', [MpaIndicatorsController::class, 'DeleteIndicator'])
        ->name('mpaIndicators.DeleteIndicator');

    //
    //
    //
    //
    //

    Route::any('/UpdateEcsahcIndicators', [IndicatorsController::class, 'UpdateEcsahcIndicators'])->name('UpdateEcsahcIndicators');

    Route::any('/DeleteEcsahcIndicators', [IndicatorsController::class, 'DeleteEcsahcIndicators'])->name('DeleteEcsahcIndicators');

    Route::post('/AddEcsahcIndicators', [IndicatorsController::class, 'AddEcsahcIndicators'])->name('AddEcsahcIndicators');

    // Mass Insert Route
    Route::any('/MgtEcsaIndicators', [IndicatorsController::class, 'MgtEcsaIndicators'])
        ->name('MgtEcsaIndicators');

    Route::get('/SelectSo', [IndicatorsController::class, 'SelectSo'])->name('SelectSo');

    Route::get('/MgtSO', [IndicatorsController::class, 'MgtSO'])->name('MgtSO');
    Route::get('/MgtEcsaUsers', [UsersController::class, 'MgtEcsaUsers'])->name('MgtEcsaUsers');

    Route::get('/MgtMpaUsers', [UsersController::class, 'MgtMpaUsers'])->name('MgtMpaUsers');

    //

    Route::post('/MassInsert', [CrudController::class, 'MassInsert'])->name('MassInsert');

// Mass Update Route
    Route::put('/MassUpdate', [CrudController::class, 'MassUpdate'])->name('MassUpdate');

// Mass Delete Route
    Route::delete('/MassDelete', [CrudController::class, 'MassDelete'])->name('MassDelete');

// Get Table Columns Route

    // Route::delete('/delete/{id}/{tableName}', [CrudController::class, 'deleteData'])
    //     ->name('delete');
    // Route::post('/insert', [CrudController::class, 'massInsert'])->name('insert');
    // Route::put('/update', [CrudController::class, 'massUpdate'])->name('update');

    Route::get('/MgtMpaTimelines', [EcsahcTimelines::class, 'MgtMpaTimelines'])->name('MgtMpaTimelines');

    Route::get('/MgtEcsaTimelines', [EcsahcTimelines::class, 'MgtEcsaTimelines'])->name('MgtEcsaTimelines');

    Route::get('/MgtClusters', [Clusters::class, 'MgtClusters'])->name('MgtClusters');

    Route::get('/MgtEntities', [EntitiesController::class, 'MgtEntities'])->name('MgtEntities');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth'])->group(function () {
    Route::post('/update-account', [UserAccountController::class, 'updateAccount'])->name('user.update-account');
    // Route::post('/logout', [UserAccountController::class, 'logout'])->name('user.logout');
});
require __DIR__ . '/auth.php';
require __DIR__ . '/appraisal.php';
require __DIR__ . '/hr.php';
require __DIR__ . '/v2reports.php';