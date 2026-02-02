<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, \Spatie\Permission\Traits\HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => 'string',
        ];
    }

    /**
     * Boot the model and assign default role.
     */
    protected static function boot()
    {
        parent::boot();

        // Automatically assign the patient role and create patient profile for newly created users
        static::created(function ($user) {
            // Only assign if the user doesn't have any roles yet
            if (!$user->roles()->exists()) {
                $user->assignRole('patient');
                
                // Create patient profile automatically
                \App\Models\Patient::create([
                    'user_id' => $user->id,
                    'patient_name' => $user->name,
                    'patient_email' => $user->email,
                    'patient_type' => 'STUDENT', // Default type, can be adjusted
                    'patient_gender' => 'OTHER', // Default, can be updated in profile
                    'patient_dob' => now()->subYears(20)->format('Y-m-d'), // Default DOB
                    'patient_hp' => '', // Empty, to be filled later
                    'patient_meds_history' => '', // Empty, to be filled later
                    'student_id' => '', // Empty, to be filled later
                    'ic_number' => '', // Empty, to be filled later
                    'phone' => '', // Empty, to be filled later
                    'address' => '', // Empty, to be filled later
                    'date_of_birth' => now()->subYears(20)->format('Y-m-d'), // Default DOB
                    'gender' => 'OTHER', // Default, can be updated in profile
                ]);
            }
        });
    }

    public function doctor()
    {
        return $this->hasOne(Doctor::class, 'user_id');
    }

    public function patient()
    {
        return $this->hasOne(Patient::class, 'user_id');
    }

    /**
     * Scope a query to only include active users.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include inactive users.
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    /**
     * Check if the user is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if the user is inactive.
     */
    public function isInactive(): bool
    {
        return $this->status === 'inactive';
    }
}
