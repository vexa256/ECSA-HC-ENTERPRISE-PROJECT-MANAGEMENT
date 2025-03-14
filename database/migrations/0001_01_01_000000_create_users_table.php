<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->string('EntityID', 255)->nullable();
            $table->string('ClusterID')->nullable();
            $table->enum('UserType', ['MPA', 'ECSA-HC'])->default('ECSA-HC');
            $table->string('UserCode', 255)->unique()->nullable();
            $table->string('Phone', 20)->nullable();
            $table->string('Nationality', 100)->nullable();
            $table->string('PhoneNumber', 20)->nullable();
            $table->text('Address')->nullable();
            $table->string('ParentOrganization', 255)->nullable();
            $table->enum('Sex', ['Male', 'Female'])->nullable();
            $table->string('JobTitle', 255)->nullable();
            $table->enum('AccountRole', ['Admin', 'User', 'Cluster Head'])->default('User');
            $table->string('UserID', 255)->unique()->nullable();
            //
            //
            //
            //

            $table->string('HR_first_name')->nullable()->after('name');
            $table->string('HR_last_name')->nullable()->after('HR_first_name');
            $table->unsignedBigInteger('HR_position_id')->nullable()->after('HR_last_name');
            $table->unsignedBigInteger('HR_supervisor_id')->nullable()->after('HR_position_id');

            // Example: an enum or string for role. Adjust the default as needed.
            $table->enum('HR_role', ['Supervisor', 'Non-Supervisor', 'Admin'])
                ->default('Non-Supervisor')
                ->after('HR_supervisor_id');

            //
            //
            //

            $table->date('HR_hire_date')->nullable()->after('HR_role');
            $table->string('HR_department')->nullable()->after('HR_hire_date');

            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};