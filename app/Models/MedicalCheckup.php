<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalCheckup extends Model
{
  use HasFactory;

  protected $table = 'medical_checkups';
  protected $primaryKey = 'appt_id';
  public $incrementing = false; // Primary key is foreign key

  protected $fillable = [
    'appt_id',
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
    return $this->belongsTo(Appointment::class, 'appt_id');
  }
}
