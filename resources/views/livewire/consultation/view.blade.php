<?php

use Livewire\Volt\Component;
use App\Models\Appointment;

new class extends Component {
    public Appointment $appointment;

    public function mount(Appointment $appointment): void
    {
        $this->appointment = $appointment->load(['medicalCheckup', 'doctor', 'prescribedMeds.medication', 'medicalCertificate', 'patient']);
    }
};

?>

<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-6">
            <flux:button variant="ghost" icon="chevron-left" href="{{ url()->previous() }}">
                Back
            </flux:button>
        </div>

        <section class="flex gap-1 items-start max-md:flex-col">
            <div class="flex-1 self-stretch overflow-y-auto">
                {{-- Card Container --}}
                <div class="w-full bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-8 shadow-sm">
                    {{-- Consultation Information Section --}}
                    <div class="space-y-6">
                        <div class="flex justify-between items-start">
                            <div>
                                <flux:heading size="xl" class="text-xl font-bold text-zinc-900 dark:text-zinc-100">Consultation Report</flux:heading>
                                <flux:subheading class="text-zinc-600 dark:text-zinc-400 mt-1">
                                    Ref: #{{ $appointment->appt_id }}
                                </flux:subheading>
                            </div>
                            <flux:button icon="printer" variant="subtle" onclick="window.print()">Print</flux:button>
                        </div>

                        <flux:separator />

                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-8">
                        {{-- Appointment Date --}}
                        <div class="mb-4 sm:col-span-1">
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Date & Time') }}</dt>
                            <dd class="mt-1 text-sm text-zinc-900 dark:text-zinc-300">
                                {{ $appointment->appt_date instanceof \Carbon\Carbon ? $appointment->appt_date->format('M d, Y') : \Carbon\Carbon::parse($appointment->appt_date)->format('M d, Y') }} at {{ $appointment->appt_time }}
                            </dd>
                        </div>

                        {{-- Status --}}
                        <div class="mb-4 sm:col-span-1">
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Status') }}</dt>
                            <dd class="mt-1 text-sm">
                                <flux:badge :variant="[
                                    'completed' => 'success',
                                    'pending' => 'warning',
                                    'cancelled' => 'danger',
                                    ][strtolower($appointment->appt_status)] ?? 'neutral'" :color="[
                                    'completed' => 'green',
                                    'pending' => 'orange',
                                    'cancelled' => 'red',
                                    ][strtolower($appointment->appt_status)] ?? 'gray'">
                                    {{ ucfirst($appointment->appt_status) }}
                                </flux:badge>
                            </dd>
                        </div>

                        {{-- Attending Doctor --}}
                        <div class="mb-4 sm:col-span-1">
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Attending Doctor') }}</dt>
                            <dd class="mt-1 text-sm text-zinc-900 dark:text-zinc-300">
                                @if($appointment->doctor)
                                    <div class="flex flex-col">
                                        <span class="font-medium">Dr. {{ $appointment->doctor->doctor_name }}</span>
                                        <span class="text-xs text-zinc-500">{{ $appointment->doctor->specialization ?? 'General Practitioner' }}</span>
                                    </div>
                                @else
                                    <span class="text-zinc-400">Unknown Doctor</span>
                                @endif
                            </dd>
                        </div>

                        {{-- Appointment ID --}}
                        <div class="mb-4 sm:col-span-1">
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Appointment ID') }}</dt>
                            <dd class="mt-1 text-sm text-zinc-900 dark:text-zinc-300">#{{ $appointment->appt_id }}</dd>
                        </div>
                    </dl>

                    <flux:separator />

                    @if($appointment->medicalCheckup)
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-8">
                            {{-- Symptoms --}}
                            <div class="mb-4 sm:col-span-2">
                                <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Symptoms & Complaints') }}</dt>
                                <dd class="mt-1 text-sm text-zinc-900 dark:text-zinc-300 whitespace-pre-line">
                                    {{ $appointment->medicalCheckup->checkup_symptom ?? 'N/A' }}
                                </dd>
                            </div>

                            {{-- Findings --}}
                            <div class="mb-4 sm:col-span-1">
                                <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Clinical Findings') }}</dt>
                                <dd class="mt-1 text-sm text-zinc-900 dark:text-zinc-300 whitespace-pre-line">
                                    {{ $appointment->medicalCheckup->checkup_finding ?? 'N/A' }}
                                </dd>
                            </div>

                            {{-- Treatment --}}
                            <div class="mb-4 sm:col-span-1">
                                <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Treatment / Remarks') }}</dt>
                                <dd class="mt-1 text-sm text-zinc-900 dark:text-zinc-300 whitespace-pre-line">
                                    {{ $appointment->medicalCheckup->checkup_treatment ?? 'N/A' }}
                                </dd>
                            </div>

                            {{-- Vitals Section --}}
                            <div class="mb-4 sm:col-span-2">
                                <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-2">{{ __('Vitals') }}</dt>
                                <dd class="grid grid-cols-2 sm:grid-cols-4 gap-4 bg-zinc-50 dark:bg-zinc-800/50 p-4 rounded-lg">
                                    <div>
                                        <div class="text-xs text-zinc-500 dark:text-zinc-400">Blood Pressure</div>
                                        <div class="text-sm font-semibold">{{ $appointment->medicalCheckup->vital_bp ?? '-' }}</div>
                                    </div>
                                    <div>
                                        <div class="text-xs text-zinc-500 dark:text-zinc-400">Heart Rate</div>
                                        <div class="text-sm font-semibold">{{ $appointment->medicalCheckup->vital_heart_rate ? $appointment->medicalCheckup->vital_heart_rate . ' bpm' : '-' }}</div>
                                    </div>
                                    <div>
                                        <div class="text-xs text-zinc-500 dark:text-zinc-400">Weight</div>
                                        <div class="text-sm font-semibold">{{ $appointment->medicalCheckup->vital_weight ? $appointment->medicalCheckup->vital_weight . ' kg' : '-' }}</div>
                                    </div>
                                    <div>
                                        <div class="text-xs text-zinc-500 dark:text-zinc-400">Height</div>
                                        <div class="text-sm font-semibold">{{ $appointment->medicalCheckup->vital_height ? $appointment->medicalCheckup->vital_height . ' cm' : '-' }}</div>
                                    </div>
                                </dd>
                            </div>
                        </div>
                    @else
                        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-800 p-4 rounded-md">
                            <p class="text-sm text-amber-600 dark:text-amber-400 italic">No detailed clinical data recorded for this consultation.</p>
                        </div>
                    @endif

                    <flux:separator />

                    {{-- Prescriptions --}}
                    <div>
                        <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-3">{{ __('Prescribed Medications') }}</dt>
                        <dd>
                            @if($appointment->prescribedMeds->isNotEmpty())
                                <div class="overflow-hidden border border-zinc-200 dark:border-zinc-700 rounded-lg">
                                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                                        <thead class="bg-zinc-50 dark:bg-zinc-800/50">
                                            <tr>
                                                <th class="px-4 py-4 text-left text-xs font-medium text-zinc-500 uppercase">Medication</th>
                                                <th class="px-4 py-4 text-left text-xs font-medium text-zinc-500 uppercase">Amount</th>
                                                <th class="px-4 py-4 text-left text-xs font-medium text-zinc-500 uppercase">Dosage</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700 bg-white dark:bg-zinc-900">
                                            @foreach($appointment->prescribedMeds as $med)
                                                <tr>
                                                    <td class="px-4 py-4 text-sm text-zinc-900 dark:text-zinc-100">{{ $med->medication->meds_name }}</td>
                                                    <td class="px-4 py-4 text-sm text-zinc-600 dark:text-zinc-400 text-center">{{ $med->amount }}</td>
                                                    <td class="px-4 py-4 text-sm text-zinc-600 dark:text-zinc-400">{{ $med->dosage }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-sm text-zinc-400 italic">No medications prescribed.</p>
                            @endif
                        </dd>
                    </div>

                    <flux:separator />

                    {{-- MC Section --}}
                    @if($appointment->medicalCertificate)
                        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 p-4 rounded-md">
                            <div class="flex items-center gap-2 mb-2 text-blue-700 dark:text-blue-400">
                                <flux:icon.document-text variant="mini" />
                                <h4 class="font-medium text-sm">Medical Certificate Issued</h4>
                            </div>
                            <p class="text-sm text-blue-600 dark:text-blue-300">
                                Valid from <strong>{{ $appointment->medicalCertificate->mc_date_start instanceof \Carbon\Carbon ? $appointment->medicalCertificate->mc_date_start->format('M d, Y') : $appointment->medicalCertificate->mc_date_start }}</strong>
                                to <strong>{{ $appointment->medicalCertificate->mc_date_end instanceof \Carbon\Carbon ? $appointment->medicalCertificate->mc_date_end->format('M d, Y') : $appointment->medicalCertificate->mc_date_end }}</strong>.
                                (@php
                                    $startDate = $appointment->medicalCertificate->mc_date_start instanceof \Carbon\Carbon ? $appointment->medicalCertificate->mc_date_start : \Carbon\Carbon::parse($appointment->medicalCertificate->mc_date_start);
                                    $endDate = $appointment->medicalCertificate->mc_date_end instanceof \Carbon\Carbon ? $appointment->medicalCertificate->mc_date_end : \Carbon\Carbon::parse($appointment->medicalCertificate->mc_date_end);
                                    $days = $startDate->diffInDays($endDate) + 1;
                                @endphp {{ $days }} days)
                            </p>
                        </div>
                        <flux:separator />
                    @endif

                    <div class="flex flex-col gap-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-8">
                            {{-- Created At --}}
                            @if($appointment->created_at)
                            <div class="mb-4 sm:col-span-1">
                                <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Record Created') }}</dt>
                                <dd class="mt-1 text-sm text-zinc-900 dark:text-zinc-300">{{ $appointment->created_at instanceof \Carbon\Carbon ? $appointment->created_at->format('Y-m-d H:i') : \Carbon\Carbon::parse($appointment->created_at)->format('Y-m-d H:i') }}</dd>
                            </div>
                            @endif
                            {{-- Last Updated --}}
                            @if($appointment->updated_at)
                            <div class="mb-4 sm:col-span-1">
                                <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Last Updated') }}</dt>
                                <dd class="mt-1 text-sm text-zinc-900 dark:text-zinc-300">{{ $appointment->updated_at instanceof \Carbon\Carbon ? $appointment->updated_at->format('Y-m-d H:i') : \Carbon\Carbon::parse($appointment->updated_at)->format('Y-m-d H:i') }}</dd>
                            </div>
                            @endif
                        </div>

                        <div class="bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200 dark:border-zinc-700 p-4 rounded-md flex flex-col gap-4">
                            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Patient Information</h3>
                            <flux:separator />
                            <div class="grid grid-cols-1 sm:grid-cols-5 gap-4">
                                <div class="flex flex-col col-span-1">
                                    <h4 class="text-xs font-medium text-zinc-500 dark:text-zinc-400">ID</h4>
                                    <p class="text-sm text-zinc-900 dark:text-zinc-300">{{ $appointment->patient->student_id ?? $appointment->patient->ic_number }}</p>
                                </div>
                                <div class="flex flex-col col-span-2">
                                    <h4 class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Name</h4>
                                    <p class="text-sm text-zinc-900 dark:text-zinc-300">{{ $appointment->patient->patient_name }}</p>
                                </div>
                                <div class="flex flex-col col-span-2">
                                    <h4 class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Email</h4>
                                    <p class="text-sm text-zinc-900 dark:text-zinc-300">{{ $appointment->patient->patient_email }}</p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Footer spacer/padding if needed --}}
                <div class="mt-8"></div>
            </div>
        </div>
    </section>
    </div>
</div>
