<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
  use HasFactory;

  protected $table = 'patients';
  protected $primaryKey = 'patient_ID';

  protected $fillable = [
    'patient_name',
    'patient_gender',
    'patient_DOB',
    'patient_HP',
    'patient_email',
    'patient_meds_history',
    'patient_type',
  ];
}
