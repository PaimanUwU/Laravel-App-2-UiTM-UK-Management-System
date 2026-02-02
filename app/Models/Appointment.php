<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\MedicalCheckup;
use App\Models\PrescribedMed;

class Appointment extends Model
{
    use HasFactory;

    protected $table = "APPOINTMENTS"; // Match Oracle table name
    protected $primaryKey = "appt_id";
    public $timestamps = false; // If your table lacks created_at/updated_at

    protected $fillable = [
        "appt_date",
        "appt_time",
        "appt_status",
        "appt_payment",
        "appt_note",
        "patient_id",
        "doctor_id",
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class, "patient_id", "patient_id");
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, "doctor_id", "doctor_id");
    }

    public function medicalCheckup()
    {
        return $this->hasOne(MedicalCheckup::class, 'appt_id', 'appt_id');
    }

    public function prescribedMeds()
    {
        return $this->hasMany(PrescribedMed::class, 'appt_id', 'appt_id');
    }

    public function medicalCertificate()
    {
        return $this->hasOne(MedicalCertificate::class, 'appt_id', 'appt_id');
    }
}
