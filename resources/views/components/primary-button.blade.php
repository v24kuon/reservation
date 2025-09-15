@props(['as' => 'button'])

@php
    $class = 'inline-flex items-center px-4 py-2 bg-primary text-white border border-transparent rounded-md font-semibold text-sm hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150';
@endphp

@if ($as === 'a')
    <a {{ $attributes->merge(['class' => $class]) }}>{{ $slot }}</a>
@else
    <button {{ $attributes->merge(['type' => 'submit', 'class' => $class]) }}>{{ $slot }}</button>
@endif
