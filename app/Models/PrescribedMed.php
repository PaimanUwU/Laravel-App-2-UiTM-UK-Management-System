<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrescribedMed extends Model
{
  use HasFactory;

  protected $table = 'prescribed_meds';
  protected $primaryKey = 'prescribe_id';

  protected $fillable = [
    'amount',
    'dosage',
    'appt_id',
    'meds_id',
  ];

  public function appointment()
  {
    return $this->belongsTo(Appointment::class, 'appt_id');
  }

  public function medication()
  {
    return $this->belongsTo(Medication::class, 'meds_id');
  }
}
