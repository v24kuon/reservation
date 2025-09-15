@props([
    'href' => '#',
    'active' => false,
])

@php
    $baseClasses = 'block px-3 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700 focus-visible:bg-gray-200 dark:focus-visible:bg-gray-600';
    $stateClasses = $active ? ' bg-gray-100 dark:bg-gray-700 font-semibold' : '';
    $attrs = $attributes->merge(['class' => $baseClasses . $stateClasses]);
    if ($attributes->get('target') === '_blank' && ! $attributes->has('rel')) {
        $attrs = $attrs->merge(['rel' => 'noopener noreferrer']);
    }
@endphp

<a href="{{ $href }}"
   @if($active) aria-current="page" @endif
   {{ $attrs }}>
    {{ $slot }}
</a>
