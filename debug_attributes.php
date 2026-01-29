<?php

use App\Models\Appointment;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$appt = Appointment::first();

if (!$appt) {
  echo "No appointments found.\n";
  exit;
}

echo "Attributes keys:\n";
print_r(array_keys($appt->getAttributes()));

echo "\nPrimary Key: " . $appt->getKeyName() . "\n";
echo "ID Value: " . $appt->getKey() . "\n";
