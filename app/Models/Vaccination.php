<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vaccination extends Model
{
  use HasFactory;

  protected $table = 'vaccinations';
  protected $primaryKey = 'appt_id';
  public $incrementing = false;

  protected $fillable = [
    'appt_id',
    'vacc_for',
    'vacc_exp_date',
    'vacc_desc',
  ];

  public function appointment()
  {
    return $this->belongsTo(Appointment::class, 'appt_id');
  }
}
