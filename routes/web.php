<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get("/", function () {
    return view("welcome");
});

Route::get("/flux-test", function () {
    return view("flux-test");
});

Route::get("/dashboard", function () {
    return view("dashboard");
})
    ->middleware(["auth", "verified"])
    ->name("dashboard");

Route::middleware(["auth", "verified", "role:system_admin"])
    ->prefix("admin")
    ->name("admin.")
    ->group(function () {
        // User Management
        \Livewire\Volt\Volt::route("/users", "admin.user-index")->name(
            "users.index",
        );
        \Livewire\Volt\Volt::route("/users/create", "admin.user-form")->name(
            "users.create",
        );
        \Livewire\Volt\Volt::route(
            "/users/{user}/edit",
            "admin.user-form",
        )->name("users.edit");
        \Livewire\Volt\Volt::route("/settings", "admin.settings-manager")->name(
            "settings",
        );
        \Livewire\Volt\Volt::route(
            "/audit-logs",
            "admin.audit-log-viewer",
        )->name("audit-logs");
    });

Route::middleware(["auth", "verified"])->group(function () {
    // Inventory - Accessible by Admin, Head Office, Staff
    Route::middleware(["role:system_admin|head_office|staff"])
        ->prefix("inventory")
        ->name("inventory.")
        ->group(function () {
            \Livewire\Volt\Volt::route("/", "inventory.medication-index")->name(
                "mex.index",
            );
        });

    // Patient Management - Accessible by Admin, Doctor, Staff
    Route::middleware(["role:system_admin|doctor|staff"])
        ->prefix("patients")
        ->name("patients.")
        ->group(function () {
            \Livewire\Volt\Volt::route("/", "patient.patient-index")->name(
                "index",
            );
            \Livewire\Volt\Volt::route("/create", "patient.patient-form")->name(
                "create",
            );

            \Livewire\Volt\Volt::route(
                "/{patient:patient_ID}/edit",
                "patient.patient-form",
            )->name("edit");
        });

    // Patient Profile - Accessible by Auth (Gate check in component)
    Route::prefix("patients")
        ->name("patients.")
        ->group(function () {
            \Livewire\Volt\Volt::route(
                "/{patient:patient_ID}",
                "patient.patient-profile",
            )->name("show");
        });

    // Appointment Management
    Route::middleware(["role:system_admin|doctor|staff"])
        ->prefix("appointments")
        ->name("appointments.")
        ->group(function () {
            \Livewire\Volt\Volt::route("/", "appointment.calendar")->name(
                "index",
            );
            \Livewire\Volt\Volt::route(
                "/create",
                "appointment.appointment-form",
            )->name("create");
            \Livewire\Volt\Volt::route(
                "/{appointment:appt_ID}/edit",
                "appointment.appointment-form",
            )->name("edit");
            \Livewire\Volt\Volt::route(
                "/queue",
                "appointment.today-queue",
            )->name("queue");
        });

    // Consultation
    Route::middleware(["role:doctor", "doctor.profile"])
        ->prefix("consultation")
        ->name("consultation.")
        ->group(function () {
            \Livewire\Volt\Volt::route(
                "/session/{appointment}",
                "consultation.consultation-wizard",
            )->name("wizard");
        });

    // Doctor Portal
    Route::middleware(["role:doctor", "doctor.profile"])
        ->prefix("doctor")
        ->name("doctor.")
        ->group(function () {
            \Livewire\Volt\Volt::route(
                "/dashboard",
                "doctor.doctor-dashboard",
            )->name("dashboard");
        });

    // Doctor Index
    Route::middleware(["role:system_admin|doctor"])
        ->prefix("doctors")
        ->name("doctors.")
        ->group(function () {
            \Livewire\Volt\Volt::route("/", "doctor.doctor-index")->name(
                "index",
            );
        });

    // Head Office
    Route::middleware(["role:head_office"])
        ->prefix("ho")
        ->name("ho.")
        ->group(function () {
            \Livewire\Volt\Volt::route(
                "/analytics",
                "head-office.analytics-dashboard",
            )->name("analytics");
        });

    // Shared Consultation View (Accessible by authorized users)
    \Livewire\Volt\Volt::route(
        "/consultations/{appointment:appt_id}",
        "consultation.view",
    )->name("consultations.view");
});

Route::middleware("auth")->group(function () {
    Route::get("/profile", [ProfileController::class, "edit"])->name(
        "profile.edit",
    );
    Route::patch("/profile", [ProfileController::class, "update"])->name(
        "profile.update",
    );
    Route::delete("/profile", [ProfileController::class, "destroy"])->name(
        "profile.destroy",
    );

    // Profile Detail Edit Page
    // \Livewire\Volt\Volt::route('/profile/edit', 'profile.profile-edit')->name('profile.detail');
});

require __DIR__ . "/auth.php";
