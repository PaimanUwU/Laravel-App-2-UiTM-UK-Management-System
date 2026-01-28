@props(['align' => 'start'])

<th {{ $attributes->class([
  'px-4 py-3 text-xs font-bold uppercase tracking-wider text-gray-600 whitespace-nowrap',
  'text-left' => $align === 'start',
  'text-right' => $align === 'end',
  'text-center' => $align === 'center',
]) }}>
  {{ $slot }}
</th>