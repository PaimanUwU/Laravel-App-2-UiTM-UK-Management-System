<?php

use App\Models\Doctor;

if (!function_exists('getCurrentDoctor')) {
    /**
     * Get the current authenticated user's doctor profile with fallback for admins
     * 
     * @return Doctor|null
     */
    function getCurrentDoctor()
    {
        $user = auth()->user();
        
        if (!$user) {
            return null;
        }
        
        // If user is admin, return null (no doctor profile)
        if ($user->hasRole('system_admin')) {
            return null;
        }
        
        // Try to get the doctor profile
        return $user->doctor;
    }
}

if (!function_exists('requireDoctor')) {
    /**
     * Get current doctor or throw exception for non-doctor users
     * 
     * @return Doctor
     * @throws \Exception
     */
    function requireDoctor()
    {
        $doctor = getCurrentDoctor();
        
        if (!$doctor) {
            throw new \Exception('This action requires a doctor profile. Admin users cannot perform doctor-specific actions.');
        }
        
        return $doctor;
    }
}

if (!function_exists('hasDoctorProfile')) {
    /**
     * Check if current user has a doctor profile
     * 
     * @return bool
     */
    function hasDoctorProfile()
    {
        return getCurrentDoctor() !== null;
    }
}
