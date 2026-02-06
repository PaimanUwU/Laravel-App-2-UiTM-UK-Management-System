<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalCheckup extends Model
{
  use HasFactory;

  protected $table = 'MEDICAL_CHECKUPS';
  protected $primaryKey = 'appt_ID';
  public $incrementing = false; // Primary key is foreign key

  protected $fillable = [
    'appt_ID',
    'checkup_symptom',
    'checkup_test',
    'checkup_finding',
    'checkup_treatment',
    'vital_bp',
    'vital_heart_rate',
    'vital_weight',
    'vital_height',
  ];

  public function appointment()
  {
    return $this->belongsTo(Appointment::class, 'appt_ID');
  }
}
