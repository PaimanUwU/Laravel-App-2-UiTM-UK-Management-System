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
        Schema::create('prescribed_meds', function (Blueprint $table) {
            $table->id('prescribe_ID');
            $table->string('amount', 100)->nullable();
            $table->string('dosage')->nullable();
            $table->foreignId('appt_ID')->constrained('appointments', 'appt_ID')->onDelete('cascade');
            $table->foreignId('meds_ID')->constrained('medications', 'meds_ID')->onDelete('cascade');
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
