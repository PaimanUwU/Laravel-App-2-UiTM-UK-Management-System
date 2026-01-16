<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
      {{ __('Flux UI Test') }}
    </h2>
  </x-slot>

  <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 space-y-8">

        <section>
          <h3 class="text-lg font-medium mb-4">Buttons</h3>
          <div class="flex flex-wrap gap-4">
            <flux:button>Default Button</flux:button>
            <flux:button variant="primary">Primary Button</flux:button>
            <flux:button variant="danger">Danger Button</flux:button>
            <flux:button variant="ghost">Ghost Button</flux:button>
          </div>
        </section>

        <section>
          <h3 class="text-lg font-medium mb-4">Form Components</h3>
          <div class="max-w-md space-y-4">
            <flux:input label="Name" placeholder="Enter your name" />
            <flux:textarea label="Bio" placeholder="Tell us about yourself" />
          </div>
        </section>

        <section>
          <h3 class="text-lg font-medium mb-4">Icons</h3>
          <div class="flex gap-4">
            <flux:icon.home variant="outline" class="w-6 h-6" />
            <flux:icon.user variant="solid" class="w-6 h-6" />
            <flux:icon.cog variant="mini" class="w-6 h-6" />
          </div>
        </section>

      </div>
    </div>
  </div>
</x-app-layout>