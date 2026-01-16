<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
  use HasFactory;

  protected $table = 'departments';
  protected $primaryKey = 'dept_ID';

  protected $fillable = [
    'dept_name',
    'dept_HP',
    'dept_email',
  ];
}
