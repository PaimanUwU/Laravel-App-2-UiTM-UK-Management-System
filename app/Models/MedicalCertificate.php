<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalCertificate extends Model
{
  use HasFactory;

  protected $table = 'MEDICAL_CERTIFICATES';
  protected $primaryKey = 'mc_ID';

  protected $fillable = [
    'mc_ID',
    'mc_date_start',
    'mc_date_end',
    'appt_ID',
  ];

  public function appointment()
  {
    return $this->belongsTo(Appointment::class, 'appt_ID');
  }
}
