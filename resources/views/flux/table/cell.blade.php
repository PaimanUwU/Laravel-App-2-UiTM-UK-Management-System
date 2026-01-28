@props(['align' => 'start'])

<td {{ $attributes->class([
  'px-4 py-4 text-sm text-gray-600',
  'text-left' => $align === 'start',
  'text-right' => $align === 'end',
  'text-center' => $align === 'center',
]) }}>
  {{ $slot }}
</td>