<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
  use HasFactory;

  protected $table = 'doctors';
  protected $primaryKey = 'doctor_id';

  protected $fillable = [
    'doctor_name',
    'doctor_gender',
    'doctor_dob',
    'doctor_hp',
    'doctor_email',
    'position_id',
    'dept_id',
    'supervisor_id',
    'status',
  ];

  public function position()
  {
    return $this->belongsTo(Position::class, 'position_id');
  }

  public function department()
  {
    return $this->belongsTo(Department::class, 'dept_id');
  }

  public function supervisor()
  {
    return $this->belongsTo(Doctor::class, 'supervisor_id', 'doctor_id');
  }

  public function user()
  {
    return $this->belongsTo(User::class, 'user_id');
  }
}
