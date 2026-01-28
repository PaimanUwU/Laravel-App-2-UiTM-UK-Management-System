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
        Schema::create('medical_checkups', function (Blueprint $table) {
            $table->foreignId('appt_ID')->primary()->constrained('appointments', 'appt_ID')->onDelete('cascade');
            $table->text('checkup_symptom')->nullable();
            $table->text('checkup_test')->nullable();
            $table->text('checkup_finding')->nullable();
            $table->text('checkup_treatment')->nullable();
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
