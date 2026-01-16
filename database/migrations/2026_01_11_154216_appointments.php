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
       Schema::create('appointments', function (Blueprint $table) {
            $table->id('appt_ID');
            $table->date('appt_date');
            $table->string('appt_time', 10)->nullable();
            $table->string('appt_status', 100);
            $table->decimal('appt_payment', 10, 2)->default(0.00);
            $table->string('appt_note')->nullable();
            
            $table->foreignId('patient_ID')->constrained('patients', 'patient_ID');
            $table->foreignId('doctor_ID')->constrained('doctors', 'doctor_ID');
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
