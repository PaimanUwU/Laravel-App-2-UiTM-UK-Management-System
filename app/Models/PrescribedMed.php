<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrescribedMed extends Model
{
  use HasFactory;

  protected $table = 'PRESCRIBED_MEDS';
  protected $primaryKey = 'prescribe_ID';

  protected $fillable = [
    'amount',
    'dosage',
    'appt_ID',
    'meds_ID',
  ];

  public function appointment()
  {
    return $this->belongsTo(Appointment::class, 'appt_ID');
  }

  public function medication()
  {
    return $this->belongsTo(Medication::class, 'meds_ID');
  }
}
