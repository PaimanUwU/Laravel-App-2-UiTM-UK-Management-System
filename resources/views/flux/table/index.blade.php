@props(['paginate' => null])

<div class="w-full overflow-x-auto">
  <table {{ $attributes->class(['w-full text-left border-collapse']) }}>
    {{ $slot }}
  </table>

  @if($paginate)
    <div class="p-4 border-t border-gray-100 bg-gray-50/50">
      {{ $paginate->links() }}
    </div>
  @endif
</div>