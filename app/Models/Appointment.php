<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
  use HasFactory;

  protected $table = 'appointments';
  protected $primaryKey = 'appt_ID';

  protected $fillable = [
    'appt_date',
    'appt_time',
    'appt_status',
    'appt_payment',
    'appt_note',
    'patient_ID',
    'doctor_ID',
  ];

  public function patient()
  {
    return $this->belongsTo(Patient::class, 'patient_ID');
  }

  public function doctor()
  {
    return $this->belongsTo(Doctor::class, 'doctor_ID');
  }
}
