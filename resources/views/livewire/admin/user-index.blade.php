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

    <div class="flex flex-col md:flex-row gap-4 mb-6">
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

    <div class="px-4 bg-white overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-300">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="py-4 pl-12 pr-3 text-left text-sm font-semibold text-gray-900">Name
                    </th>
                    <th scope="col" class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Email</th>
                    <th scope="col" class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Role</th>
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
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                            {{ $user->created_at->format('M d, Y') }}
                        </td>
                        <td class="relative whitespace-nowrap py-4 pl-3 pr-12 text-right text-sm font-medium">
                            <flux:button variant="ghost" size="sm" icon="pencil-square"
                                href="{{ route('admin.users.edit', $user) }}" />
                            <flux:button variant="ghost" size="sm" icon="trash" wire:click="deleteUser({{ $user->id }})"
                                wire:confirm="Are you sure you want to delete this user?" />
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