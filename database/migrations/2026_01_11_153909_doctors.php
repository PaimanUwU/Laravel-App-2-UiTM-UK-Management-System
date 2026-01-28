<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // database/migrations/2026_01_11_000003_create_doctors_table.php
        Schema::create('doctors', function (Blueprint $table) {
            $table->id('doctor_ID');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('doctor_name')->nullable();
            $table->string('doctor_gender')->nullable();
            $table->date('doctor_DOB')->nullable();
            $table->string('doctor_HP')->nullable();
            $table->string('doctor_email')->nullable();
            $table->foreignId('position_ID')->constrained('positions', 'position_ID');
            $table->foreignId('dept_ID')->constrained('departments', 'dept_ID');
            $table->foreignId('supervisor_ID')->nullable()->constrained('doctors', 'doctor_ID');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
