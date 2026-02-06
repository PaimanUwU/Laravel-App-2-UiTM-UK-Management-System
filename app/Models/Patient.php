<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Appointment;

class Patient extends Model
{
  use HasFactory;

  protected $table = 'PATIENTS';
  protected $primaryKey = 'patient_id';

  protected $fillable = [
    'user_id',
    'patient_name',
    'patient_gender',
    'patient_DOB',
    'patient_HP',
    'patient_email',
    'patient_type',
    'patient_meds_history',
    'student_id',
    'ic_number',
    'phone',
    'address',
    'date_of_birth',
    'gender',
  ];

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function appointments()
  {
    return $this->hasMany(Appointment::class, 'patient_id', 'patient_id');
  }
}
