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
        Schema::create('vaccinations', function (Blueprint $table) {
            $table->foreignId('appt_ID')->primary()->constrained('appointments', 'appt_ID')->onDelete('cascade');
            $table->string('vacc_for')->nullable();
            $table->date('vacc_exp_date')->nullable();
            $table->text('vacc_desc')->nullable();
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
