<?php
use App\Http\Controllers\Appraisal_AppraisalCyclesController;
use App\Http\Controllers\Appraisal_AppraisalGenerationController;
use App\Http\Controllers\Appraisal_CycleInitiationController;
use App\Http\Controllers\Appraisal_FormTypesController;
use App\Http\Controllers\Appraisal_PerformanceFactorsController;
use App\Http\Controllers\Appraisal_PositionsController;
use App\Http\Controllers\Appraisal_RatingScalesController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {

    Route::prefix('appraisal/positions')
        ->name('appraisal_positions.')
        ->group(function () {
            // GET: Display the list, create, or edit form based on query parameters.
            Route::get('/', [Appraisal_PositionsController::class, 'index'])
                ->name('index');

            // POST: Store a new position.
            Route::post('/', [Appraisal_PositionsController::class, 'store'])
                ->name('store');

            // PUT/PATCH: Update an existing position by ID.
            Route::match(['put', 'patch'], '/{id}', [Appraisal_PositionsController::class, 'update'])
                ->name('update');

            // DELETE: Delete a position by ID.
            Route::delete('/{id}', [Appraisal_PositionsController::class, 'destroy'])
                ->name('destroy');
        });

    Route::prefix('appraisal/form_types')
        ->name('appraisal_form_types.')
        ->group(function () {
            // GET: Display list, create form, or edit form based on query parameters.
            Route::get('/', [Appraisal_FormTypesController::class, 'index'])
                ->name('index');

            // POST: Store a new form type.
            Route::post('/', [Appraisal_FormTypesController::class, 'store'])
                ->name('store');

            // PUT/PATCH: Update an existing form type by ID.
            Route::match(['put', 'patch'], '/{id}', [Appraisal_FormTypesController::class, 'update'])
                ->name('update');

            // DELETE: Delete a form type by ID.
            Route::delete('/{id}', [Appraisal_FormTypesController::class, 'destroy'])
                ->name('destroy');
        });

    Route::prefix('appraisal/performance_factors')
        ->name('appraisal_performance_factors.')
        ->group(function () {
            // GET: Display list, create form, or edit form based on query parameters.
            Route::get('/', [Appraisal_PerformanceFactorsController::class, 'index'])
                ->name('index');

            // POST: Store a new performance factor.
            Route::post('/', [Appraisal_PerformanceFactorsController::class, 'store'])
                ->name('store');

            // PUT/PATCH: Update an existing performance factor by ID.
            Route::match(['put', 'patch'], '/{id}', [Appraisal_PerformanceFactorsController::class, 'update'])
                ->name('update');

            // DELETE: Delete a performance factor by ID.
            Route::delete('/{id}', [Appraisal_PerformanceFactorsController::class, 'destroy'])
                ->name('destroy');
        });

    Route::prefix('appraisal/rating_scales')
        ->name('appraisal_rating_scales.')
        ->group(function () {
            // GET: Display list, create form, or edit form based on query parameters.
            Route::get('/', [Appraisal_RatingScalesController::class, 'index'])
                ->name('index');

            // POST: Store a new rating scale.
            Route::post('/', [Appraisal_RatingScalesController::class, 'store'])
                ->name('store');

            // PUT/PATCH: Update an existing rating scale by ID.
            Route::match(['put', 'patch'], '/{id}', [Appraisal_RatingScalesController::class, 'update'])
                ->name('update');

            // DELETE: Delete a rating scale by ID.
            Route::delete('/{id}', [Appraisal_RatingScalesController::class, 'destroy'])
                ->name('destroy');
        });

    Route::prefix('appraisal/appraisal_cycles')
        ->name('appraisal_appraisal_cycles.')
        ->group(function () {
            // GET: Display list, create form, or edit form based on query parameters.
            Route::get('/', [Appraisal_AppraisalCyclesController::class, 'index'])
                ->name('index');

            // POST: Store a new appraisal cycle.
            Route::post('/', [Appraisal_AppraisalCyclesController::class, 'store'])
                ->name('store');

            // PUT/PATCH: Update an existing appraisal cycle by ID.
            Route::match(['put', 'patch'], '/{id}', [Appraisal_AppraisalCyclesController::class, 'update'])
                ->name('update');

            // DELETE: Delete an appraisal cycle by ID.
            Route::delete('/{id}', [Appraisal_AppraisalCyclesController::class, 'destroy'])
                ->name('destroy');
        });

    Route::prefix('appraisal/cycle_initiation')
        ->name('appraisal_cycle_initiation.')
        ->group(function () {
            // GET: Display the list of appraisal cycles in "Draft" status.
            Route::get('/', [Appraisal_CycleInitiationController::class, 'index'])
                ->name('index');

            // POST: Initiate a specific appraisal cycle by updating its status to "Open".
            Route::post('/initiate', [Appraisal_CycleInitiationController::class, 'initiateCycle'])
                ->name('initiateCycle');
        });

    Route::prefix('appraisal/appraisal_generation')
        ->name('appraisal_appraisal_generation.')
        ->group(function () {
            // GET: Display the appraisal generation options.
            Route::get('/', [Appraisal_AppraisalGenerationController::class, 'index'])
                ->name('index');

            // POST: Generate appraisal records based on HR input.
            Route::post('/generate', [Appraisal_AppraisalGenerationController::class, 'generateAppraisals'])
                ->name('generateAppraisals');
        });

});