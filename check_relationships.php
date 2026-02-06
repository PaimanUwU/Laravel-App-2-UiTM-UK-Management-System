#!/usr/bin/env php
<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Doctor;
use App\Models\Position;
use App\Models\Department;

echo "=== Diagnostic Check ===\n\n";

// Check if doctors exist
$doctorCount = Doctor::count();
echo "Total Doctors: $doctorCount\n";

if ($doctorCount > 0) {
  $doctor = Doctor::first();
  echo "\nFirst Doctor:\n";
  echo "  ID: {$doctor->doctor_ID}\n";
  echo "  Name: {$doctor->doctor_name}\n";
  echo "  Position ID (raw): {$doctor->position_ID}\n";
  echo "  Dept ID (raw): {$doctor->dept_ID}\n";

  // Try to load relationships
  $doctor->load(['position', 'department']);

  echo "\nRelationships:\n";
  echo "  Position loaded: " . ($doctor->position ? 'YES' : 'NO') . "\n";
  echo "  Department loaded: " . ($doctor->department ? 'YES' : 'NO') . "\n";

  if ($doctor->position) {
    echo "  Position Name: {$doctor->position->position_name}\n";
  } else {
    echo "  Position is NULL - checking why...\n";
    $posCount = Position::count();
    echo "  Total Positions in DB: $posCount\n";
    if ($posCount > 0) {
      $pos = Position::find($doctor->position_ID);
      echo "  Can find position by ID? " . ($pos ? "YES" : "NO") . "\n";
    }
  }

  if ($doctor->department) {
    echo "  Department Name: {$doctor->department->dept_name}\n";
  } else {
    echo "  Department is NULL - checking why...\n";
    $deptCount = Department::count();
    echo "  Total Departments in DB: $deptCount\n";
    if ($deptCount > 0) {
      $dept = Department::find($doctor->dept_ID);
      echo "  Can find department by ID? " . ($dept ? "YES" : "NO") . "\n";
    }
  }
}

echo "\n=== End Diagnostic ===\n";
