<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalCertificate extends Model
{
  use HasFactory;

  protected $table = 'medical_certificates';
  protected $primaryKey = 'mc_id';

  protected $fillable = [
    'mc_id',
    'mc_date_start',
    'mc_date_end',
    'appt_id',
  ];

  public function appointment()
  {
    return $this->belongsTo(Appointment::class, 'appt_id');
  }
}
