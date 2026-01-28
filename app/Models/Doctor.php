<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
  use HasFactory;

  protected $table = 'doctors';
  protected $primaryKey = 'doctor_ID';

  protected $fillable = [
    'doctor_name',
    'doctor_gender',
    'doctor_DOB',
    'doctor_HP',
    'doctor_email',
    'position_ID',
    'dept_ID',
    'supervisor_ID',
    'status',
  ];

  public function position()
  {
    return $this->belongsTo(Position::class, 'position_ID');
  }

  public function department()
  {
    return $this->belongsTo(Department::class, 'dept_ID');
  }

  public function supervisor()
  {
    return $this->belongsTo(Doctor::class, 'supervisor_ID', 'doctor_ID');
  }
}
