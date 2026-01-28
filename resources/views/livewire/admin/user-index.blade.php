<?php

use Livewire\Volt\Component;
use App\Models\User;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $role = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function deleteUser(User $user)
    {
        // Don't allow deleting yourself
        if ($user->id === auth()->id()) {
            return;
        }

        $user->delete();

        // Log action
        \App\Models\AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'delete_user',
            'description' => "Deleted user: {$user->email}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function with(): array
    {
        $query = User::query()->whereDoesntHave('roles', function ($q) {
            $q->where('name', 'patient');
        });

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->role) {
            $query->role($this->role);
        }

        return [
            'users' => $query->latest()->paginate(10),
            'roles' => \Spatie\Permission\Models\Role::where('name', '!=', 'patient')->get(),
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

    <div class="flex flex-col md:flex-row gap-4">
        <div class="flex-1">
            <flux:input wire:model.live="search" placeholder="Search by name or email..." icon="magnifying-glass" />
        </div>
        <div class="w-full md:w-64">
            <flux:select wire:model.live="role" placeholder="Filter by Role">
                <flux:select.option value="">All Roles</flux:select.option>
                @foreach($roles as $r)
                    <flux:select.option value="{{ $r->name }}">{{ ucwords(str_replace('_', ' ', $r->name)) }}
                    </flux:select.option>
                @endforeach
            </flux:select>
        </div>
    </div>

    <flux:card class="overflow-hidden">
        <flux:table :paginate="$users">
            <flux:table.columns>
                <flux:table.column>Name</flux:table.column>
                <flux:table.column>Email</flux:table.column>
                <flux:table.column>Role</flux:table.column>
                <flux:table.column>Joined</flux:table.column>
                <flux:table.column align="end">Actions</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($users as $user)
                    <flux:table.row :key="$user->id">
                        <flux:table.cell class="font-medium">{{ $user->name }}</flux:table.cell>
                        <flux:table.cell>{{ $user->email }}</flux:table.cell>
                        <flux:table.cell>
                            @foreach($user->roles as $role)
                                <flux:badge color="teal" size="sm" class="mr-1">
                                    {{ ucwords(str_replace('_', ' ', $role->name)) }}
                                </flux:badge>
                            @endforeach
                        </flux:table.cell>
                        <flux:table.cell>{{ $user->created_at->format('M d, Y') }}</flux:table.cell>
                        <flux:table.cell align="end">
                            <flux:button variant="ghost" size="sm" icon="pencil-square"
                                href="{{ route('admin.users.edit', $user) }}" />
                            <flux:button variant="ghost" size="sm" icon="trash" wire:click="deleteUser({{ $user->id }})"
                                wire:confirm="Are you sure you want to delete this user?" />
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </flux:card>
</div>