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
  ];

  public function appointment()
  {
    return $this->belongsTo(Appointment::class, 'appt_id');
  }
}
