@props(['disabled' => false])

<input
    @disabled($disabled)
    @if($id) id="{{ $id }}" @endif
    @if($name) name="{{ $name }}" @endif
    type="{{ $type }}"
    @if(!is_null($value)) value="{{ $value }}" @endif
    @if($required) required @endif
    @if($placeholder) placeholder="{{ $placeholder }}" @endif
    @if($autocomplete) autocomplete="{{ $autocomplete }}" @endif
    @if($minlength) minlength="{{ $minlength }}" @endif
    @if($maxlength) maxlength="{{ $maxlength }}" @endif
    {{ $attributes->merge(['class' => 'border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:border-indigo-500 dark:focus:border-indigo-400 focus:ring-indigo-500 dark:focus:ring-indigo-400 rounded-md shadow-sm ' . $class]) }}
>
