<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalCertificate extends Model
{
  use HasFactory;

  protected $table = 'medical_certificates';
  protected $primaryKey = 'MC_ID';

  protected $fillable = [
    'MC_ID',
    'MC_date_start',
    'MC_date_end',
    'appt_ID',
  ];

  public function appointment()
  {
    return $this->belongsTo(Appointment::class, 'appt_ID');
  }
}
