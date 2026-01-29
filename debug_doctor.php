<?php

use App\Models\Doctor;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$doc = Doctor::first();

if (!$doc) {
  echo "No doctor found.\n";
  exit;
}

echo "Attributes keys:\n";
print_r(array_keys($doc->getAttributes()));
