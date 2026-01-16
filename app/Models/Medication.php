<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medication extends Model
{
  use HasFactory;

  protected $table = 'medications';
  protected $primaryKey = 'meds_ID';

  protected $fillable = [
    'meds_name',
    'meds_type',
  ];
}
