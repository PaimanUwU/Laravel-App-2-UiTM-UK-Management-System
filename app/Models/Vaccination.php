<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vaccination extends Model
{
  use HasFactory;

  protected $table = 'VACCINATIONS';
  protected $primaryKey = 'appt_ID';
  public $incrementing = false;

  protected $fillable = [
    'appt_ID',
    'vacc_for',
    'vacc_exp_date',
    'vacc_desc',
  ];

  public function appointment()
  {
    return $this->belongsTo(Appointment::class, 'appt_ID');
  }
}
