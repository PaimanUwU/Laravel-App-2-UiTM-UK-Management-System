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
        Schema::create('patients', function (Blueprint $table) {
            $table->id('patient_ID');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('patient_name')->nullable();
            $table->string('patient_gender')->nullable();
            $table->date('patient_DOB')->nullable();
            $table->string('patient_HP')->nullable();
            $table->string('patient_email')->nullable();
            $table->string('patient_type')->nullable();
            $table->text('patient_meds_history')->nullable();
            $table->string('student_id')->nullable();
            $table->string('ic_number')->nullable()->unique();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender')->nullable();
            $table->timestamps();
            $table->softDeletes();
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
