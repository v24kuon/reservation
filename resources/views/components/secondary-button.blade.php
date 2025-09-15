@props(['as' => 'a'])

@php
    $base = 'inline-flex items-center px-4 py-2 rounded font-medium text-sm bg-gray-200 text-white hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-indigo-500';
@endphp

@if($as === 'button')
<button {{ $attributes->merge(['class' => $base]) }}>{{ $slot }}</button>
@else
<a {{ $attributes->merge(['class' => $base]) }}>{{ $slot }}</a>
@endif
