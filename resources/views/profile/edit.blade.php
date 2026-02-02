<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div> -->

            <livewire:profile.profile-edit />

            <flux:card class="p-4">
                <div class="flex items-center gap-3 mb-6">
                    <div class="p-2 bg-red-50 text-red-600 rounded-lg">
                        <flux:icon.trash variant="solid" class="w-6 h-6" />
                    </div>
                    <h2 class="text-xl font-bold">Delete Account</h2>
                </div>
                @include('profile.partials.delete-user-form')
            </flux:card>
        </div>
    </div>
</x-app-layout>
