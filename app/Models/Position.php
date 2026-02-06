<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
  use HasFactory;

  protected $table = 'POSITIONS';
  protected $primaryKey = 'position_id';

  protected $fillable = [
    'position_name',
    'position_desc',
  ];
}
