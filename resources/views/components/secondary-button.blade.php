@props(['as' => 'a'])

@php
    $base = 'inline-flex items-center px-4 py-2 rounded font-medium text-sm bg-gray-200 text-gray-900 hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-indigo-500 disabled:opacity-50 disabled:pointer-events-none';
    $attrs = $attributes;
    if ($attrs->get('target') === '_blank' && ! $attrs->has('rel')) {
        $attrs = $attrs->merge(['rel' => 'noopener noreferrer']);
    }
@endphp

@if($as === 'button' || ($as === 'a' && ! $attrs->has('href')))
<button {{ $attrs->merge(['type' => 'button', 'class' => $base]) }}>{{ $slot }}</button>
@else
<a {{ $attrs->merge(['class' => $base]) }}>{{ $slot }}</a>
@endif
