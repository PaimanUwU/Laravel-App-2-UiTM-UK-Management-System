<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-6 px-4 sm:px-0">
        <div class="mx-auto">
            <livewire:dashboard.main />
        </div>
    </div>
</x-app-layout>