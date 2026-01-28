@props([
  'variant' => 'default',
])

@php
  $classes = 'bg-white border border-gray-100 shadow-sm rounded-xl overflow-hidden';
@endphp

<div {{ $attributes->class($classes) }}>
    {{ $slot }}
</div>
