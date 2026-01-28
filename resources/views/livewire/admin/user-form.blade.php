<?php

use Livewire\Volt\Component;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

new class extends Component {
    public ?User $user = null;
    public $name = '';
    public $email = '';
    public $password = '';
    public $role = '';

    public function mount(?User $user = null)
    {
        if ($user && $user->exists) {
            $this->user = $user;
            $this->name = $user->name;
            $this->email = $user->email;
            $this->role = $user->roles->first()?->name ?? '';
        }
    }

    public function save()
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($this->user?->id)],
            'password' => $this->user ? 'nullable|min:8' : 'required|min:8',
            'role' => 'required|exists:roles,name',
        ]);

        $userData = [
            'name' => $this->name,
            'email' => $this->email,
        ];

        if ($this->password) {
            $userData['password'] = Hash::make($this->password);
        }

        if ($this->user) {
            $this->user->update($userData);
            $this->user->syncRoles([$this->role]);
            $action = 'update_user';
            $description = "Updated user: {$this->email}";
        } else {
            $newUser = User::create($userData);
            $newUser->assignRole($this->role);
            $action = 'create_user';
            $description = "Created user: {$this->email} with role: {$this->role}";
        }

        // Log action
        \App\Models\AuditLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()->route('admin.users.index')
            ->with('status', 'User saved successfully.');
    }

    public function with()
    {
        return [
            'roles' => Role::where('name', '!=', 'patient')->get(),
        ];
    }
};
?>

<div class="max-w-2xl mx-auto space-y-6">
    <div>
        <flux:button variant="ghost" href="{{ route('admin.users.index') }}" icon="chevron-left" class="-ml-2 mb-4">
            Back to User List
        </flux:button>
        <h1 class="text-2xl font-bold text-gray-900">{{ $user ? 'Edit User' : 'Create New User' }}</h1>
        <p class="text-sm text-gray-600">Enter details for the new staff or administrator.</p>
    </div>

    <flux:card>
        <form wire:submit="save" class="space-y-6">
            <flux:input wire:model="name" label="Full Name" placeholder="e.g. Dr. John Doe" />

            <flux:input wire:model="email" type="email" label="Email Address" placeholder="staff@uitm.edu.my" />

            <flux:select wire:model="role" label="System Role" placeholder="Choose a role">
                @foreach($roles as $r)
                    <flux:select.option value="{{ $r->name }}">{{ ucwords(str_replace('_', ' ', $r->name)) }}
                    </flux:select.option>
                @endforeach
            </flux:select>

            <flux:input wire:model="password" type="password" label="Password"
                :placeholder="$user ? 'Leave blank to keep current password' : 'Enter a strong password'" />

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <flux:button href="{{ route('admin.users.index') }}" variant="ghost">Cancel</flux:button>
                <flux:button type="submit" variant="primary">{{ $user ? 'Update User' : 'Create User' }}</flux:button>
            </div>
        </form>
    </flux:card>
</div>