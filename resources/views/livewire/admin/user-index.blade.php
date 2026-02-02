<?php

use Livewire\Volt\Component;
use App\Models\User;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $role = '';
    public $status = 'active';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function toggleStatus(User $user)
    {
        $newStatus = $user->status === 'active' ? 'inactive' : 'active';
        $user->update(['status' => $newStatus]);

        $action = $newStatus === 'active' ? 'activate_user' : 'deactivate_user';
        $description = ($newStatus === 'active' ? 'Activated' : 'Deactivated') . " user: {$user->email}";

        // Log action
        \App\Models\AuditLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        session()->flash('success', 'User ' . ($newStatus === 'active' ? 'activated' : 'deactivated') . ' successfully.');
        $this->dispatch('$refresh');
    }

    public function deleteUser(User $user)
    {
        // Don't allow deleting yourself
        if ($user->id === auth()->id()) {
            session()->flash('error', 'You cannot delete your own account.');
            return;
        }

        try {
            // Debug: Log what we're trying to delete
            \Log::info("Attempting to delete user: {$user->email} (ID: {$user->id})");
            
            // Check if user has related records that would cause constraint violations
            $hasRelatedRecords = false;
            $relatedInfo = [];

            // Check for patient records
            if ($user->patient) {
                $hasRelatedRecords = true;
                $relatedInfo[] = 'Patient profile';
                \Log::info("User has patient profile");
            }

            // Check for doctor records
            if ($user->doctor) {
                $hasRelatedRecords = true;
                $relatedInfo[] = 'Doctor profile';
                \Log::info("User has doctor profile");
            }

            // Check for appointments (as patient or doctor)
            $appointmentCount = \App\Models\Appointment::where('patient_ID', $user->id)
                ->orWhere('doctor_ID', $user->id)
                ->count();
            
            if ($appointmentCount > 0) {
                $hasRelatedRecords = true;
                $relatedInfo[] = "{$appointmentCount} appointment(s)";
                \Log::info("User has {$appointmentCount} appointments");
            }

            // Check for medical records
            $medicalRecordCount = \App\Models\MedicalCheckup::whereHas('appointment', function($query) use ($user) {
                $query->where('patient_ID', $user->id)->orWhere('doctor_ID', $user->id);
            })->count();

            if ($medicalRecordCount > 0) {
                $hasRelatedRecords = true;
                $relatedInfo[] = "{$medicalRecordCount} medical record(s)";
                \Log::info("User has {$medicalRecordCount} medical records");
            }

            if ($hasRelatedRecords) {
                $errorMsg = 'Cannot delete user. User has related records: ' . implode(', ', $relatedInfo) . '. Please delete related records first or deactivate the user instead.';
                \Log::info("Deletion blocked: {$errorMsg}");
                session()->flash('error', $errorMsg);
                return;
            }

            // If no related records, proceed with deletion
            \Log::info("No related records found, proceeding with deletion");
            $user->delete();
            \Log::info("User deleted successfully");

            // Log action
            \App\Models\AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'delete_user',
                'description' => "Deleted user: {$user->email}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            session()->flash('success', 'User deleted successfully.');
            
            // Force a complete refresh of the component
            $this->dispatch('$refresh');
        } catch (\Exception $e) {
            \Log::error("Error deleting user: " . $e->getMessage());
            session()->flash('error', 'Error deleting user: ' . $e->getMessage());
        }
    }

    public function with(): array
    {
        $query = User::query();

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%')
                ->orWhere('email', 'like', '%' . $this->search . '%');
        }

        if ($this->role) {
            $query->role($this->role);
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        return [
            'users' => $query->latest()->paginate(10),
            'roles' => \Spatie\Permission\Models\Role::all(),
        ];
    }
};
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">User Management</h1>
            <p class="text-sm text-gray-600">Manage staff, doctors, and office administrators.</p>
        </div>
        <flux:button variant="primary" href="{{ route('admin.users.create') }}" icon="plus">
            Create User
        </flux:button>
    </div>

    <div class="flex flex-col md:flex-row gap-4 mb-6">
        <div class="flex-1">
            <flux:input wire:model.live="search" placeholder="Search by name or email..." icon="magnifying-glass" />
        </div>
        <div class="flex gap-4">
            <div class="w-full md:w-48">
                <flux:select wire:model.live="role" placeholder="Filter by Role">
                    <flux:select.option value="">All Roles</flux:select.option>
                    @foreach($roles as $r)
                        <flux:select.option value="{{ $r->name }}">{{ ucwords(str_replace('_', ' ', $r->name)) }}
                        </flux:select.option>
                    @endforeach
                </flux:select>
            </div>
            <div class="w-full md:w-48">
                <flux:select wire:model.live="status" placeholder="Filter by Status">
                    <flux:select.option value="">All Status</flux:select.option>
                    <flux:select.option value="active">Active</flux:select.option>
                    <flux:select.option value="inactive">Inactive</flux:select.option>
                </flux:select>
            </div>
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-300">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="py-4 pl-12 pr-3 text-left text-sm font-semibold text-gray-900">Name
                    </th>
                    <th scope="col" class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Email</th>
                    <th scope="col" class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Role</th>
                    <th scope="col" class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Status</th>
                    <th scope="col" class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Joined</th>
                    <th scope="col" class="relative py-4 pl-3 pr-12">
                        <span class="sr-only">Actions</span>
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @foreach ($users as $user)
                    <tr>
                        <td class="whitespace-nowrap py-4 pl-12 pr-3 text-sm font-medium text-gray-900">
                            {{ $user->name }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ $user->email }}</td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                            @foreach($user->roles as $role)
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-zinc-100 text-zinc-800 mr-1">
                                    {{ ucwords(str_replace('_', ' ', $role->name)) }}
                                </span>
                            @endforeach
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm">
                            @if($user->status === 'active')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Active
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Inactive
                                </span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                            {{ $user->created_at->format('M d, Y') }}
                        </td>
                        <td class="relative whitespace-nowrap py-4 pl-3 pr-12 text-right text-sm font-medium">
                            <flux:button variant="ghost" size="sm" icon="pencil-square"
                                href="{{ route('admin.users.edit', $user) }}" />
                            <flux:button 
                                variant="ghost" 
                                size="sm" 
                                icon="{{ $user->status === 'active' ? 'x-circle' : 'check-circle' }}"
                                wire:click="toggleStatus({{ $user->id }})"
                                wire:confirm="Are you sure you want to {{ $user->status === 'active' ? 'deactivate' : 'activate' }} this user?">
                            </flux:button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="px-4 py-3 border-t border-gray-200 bg-gray-50 sm:px-6">
            {{ $users->links() }}
        </div>
    </div>
</div>
