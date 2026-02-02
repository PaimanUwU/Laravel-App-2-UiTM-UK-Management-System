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
    'user_id',
    'doctor_name',
    'doctor_gender',
    'doctor_DOB',
    'doctor_HP',
    'doctor_email',
    'position_ID', // Keeping this as is for now unless I confirmed it should be changed, but user specifically mentioned supervisor_id
    'dept_ID',     // Same here
    'supervisor_id',
    'status',
  ];

  public function position()
  {
    return $this->belongsTo(Position::class, 'position_ID', 'position_ID');
  }

  public function department()
  {
    return $this->belongsTo(Department::class, 'dept_ID', 'dept_ID');
  }

  public function supervisor()
  {
    return $this->belongsTo(Doctor::class, 'supervisor_id', 'doctor_id');
  }

  public function user()
  {
    return $this->belongsTo(User::class, 'user_id');
  }

  public function supervisees()
  {
    return $this->hasMany(Doctor::class, 'supervisor_id', 'doctor_id');
  }
}
