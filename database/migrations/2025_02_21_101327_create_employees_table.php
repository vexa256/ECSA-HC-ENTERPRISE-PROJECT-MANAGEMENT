<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            // Primary key
            $table->bigIncrements('EmployeeID');

            // Link to the existing users table
            $table->unsignedBigInteger('UserID');

            // Personal Information
            $table->string('FirstName', 255);
            $table->string('LastName', 255);
            $table->date('DateOfBirth')->nullable();
            $table->enum('Gender', ['Male', 'Female', 'Other'])->default('Other');
            $table->enum('MaritalStatus', ['Single', 'Married', 'Divorced', 'Widowed'])->default('Single');
            $table->string('Nationality', 100)->nullable();

            // HR / Job Details
            $table->enum('EmploymentStatus', ['Active', 'Inactive', 'Terminated', 'On Leave'])->default('Active');
            $table->enum('EmploymentType', ['International', 'Local'])->default('Local');
            $table->unsignedBigInteger('HR_Position_ID')->nullable(); // Reference to positions table if needed
            $table->string('JobTitle', 255);
            $table->string('Department', 255)->nullable();
            $table->date('DateJoined');
            $table->date('ContractStartDate')->nullable();
            $table->date('ContractEndDate')->nullable();
            $table->enum('EmployeeCategory', ['Full-Time', 'Part-Time', 'Contractor', 'Intern'])->default('Full-Time');
            $table->unsignedInteger('ProbationPeriodMonths')->default(0);

            // Financial Information
            $table->decimal('BasicSalaryPerMonth', 15, 2)->nullable();
            $table->enum('SalaryCurrency', ['USD', 'EURO', 'BRISTISH CURRENCY', 'TSH'])->default('USD');
            $table->enum('PaymentFrequency', ['Monthly'])->default('Monthly');
            $table->string('BankAccountNumber', 50)->nullable();
            $table->string('BankName', 255)->nullable();
            $table->string('BranchCode', 50)->nullable();
            $table->string('TaxID', 100)->nullable();
            $table->enum('BenefitsEligibility', ['Yes', 'No'])->default('No');
            $table->enum('PensionPlan', ['Yes', 'No'])->default('No'); // Gratuity plan enrollment

            // Supervisor / Organizational Hierarchy
            $table->unsignedBigInteger('SupervisorEmployeeID')->nullable();

            // Timestamps
            $table->timestamps();

            // Unique constraint and foreign keys
            // $table->unique('UserID');
            // $table->foreign('UserID')
            //     ->references('id')->on('users')
            //     ->onDelete('cascade')
            //     ->onUpdate('cascade');
            // $table->foreign('SupervisorEmployeeID')
            //     ->references('EmployeeID')->on('employees')
            //     ->onDelete('set null')
            //     ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('employees');
    }
}