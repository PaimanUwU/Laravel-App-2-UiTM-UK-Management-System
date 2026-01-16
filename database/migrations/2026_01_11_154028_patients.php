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
        Schema::create('patients', function (Blueprint $table) {
            $table->id('patient_ID');
            $table->string('patient_name');
            $table->char('patient_gender', 1);
            $table->date('patient_DOB')->nullable();
            $table->string('patient_HP', 11)->nullable();
            $table->string('patient_email')->nullable();
            $table->text('patient_meds_history')->nullable();
            $table->string('patient_type', 10); // STUDENT or STAFF
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
