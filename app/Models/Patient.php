<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
  use HasFactory;

  protected $table = 'patients';
  protected $primaryKey = 'patient_id';

  protected $fillable = [
    'user_id',
    'patient_name',
    'patient_gender',
    'patient_dob',
    'patient_hp',
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
}
