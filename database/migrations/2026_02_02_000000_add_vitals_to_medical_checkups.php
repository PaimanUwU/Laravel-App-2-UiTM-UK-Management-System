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
    Schema::table('medical_checkups', function (Blueprint $table) {
      $table->string('vital_bp')->nullable();
      $table->integer('vital_heart_rate')->nullable();
      $table->decimal('vital_weight', 8, 2)->nullable();
      $table->decimal('vital_height', 8, 2)->nullable();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('medical_checkups', function (Blueprint $table) {
      $table->dropColumn(['vital_bp', 'vital_heart_rate', 'vital_weight', 'vital_height']);
    });
  }
};
