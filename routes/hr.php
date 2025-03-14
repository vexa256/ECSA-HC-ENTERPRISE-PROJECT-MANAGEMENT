<?php
use App\Http\Controllers\EmployeeManagementController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {

    Route::prefix('employee_management')
        ->name('staff_management.')
        ->group(function () {
            // Display a list of employees.
            Route::get('/', [EmployeeManagementController::class, 'index'])
                ->name('index');

            // Show the form for creating a new employee.
            Route::get('/create', [EmployeeManagementController::class, 'create'])
                ->name('create');

            // Store a newly created employee.
            Route::post('/', [EmployeeManagementController::class, 'store'])
                ->name('store');

            // Show the form for editing an existing employee.
            Route::get('/{id}/edit', [EmployeeManagementController::class, 'edit'])
                ->name('edit');

            // Update an existing employee.
            Route::match(['put', 'patch'], '/{id}', [EmployeeManagementController::class, 'update'])
                ->name('update');

            // Delete an employee.
            Route::delete('/{id}', [EmployeeManagementController::class, 'destroy'])
                ->name('destroy');
        });

});