@props(['as' => 'button'])

@php
    $class = 'inline-flex items-center px-4 py-2 bg-primary text-white border border-transparent rounded-md font-semibold text-sm hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-50 disabled:pointer-events-none transition ease-in-out duration-150';
    $attrs = $attributes;
    if ($as === 'a' && $attrs->get('target') === '_blank') {
        $rel = trim((string) $attrs->get('rel', ''));
        $rels = preg_split('/\s+/', $rel, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        foreach (['noopener','noreferrer'] as $token) {
            if (!in_array($token, $rels, true)) { $rels[] = $token; }
        }
        $attrs = $attrs->merge(['rel' => implode(' ', $rels)]);
    }
    $isDisabled = $attrs->has('disabled');
    if ($as === 'a' && $isDisabled) {
        // <a> は :disabled が効かないため、ARIA と見た目を直付けで無効化
        $attrs = $attrs->merge(['aria-disabled' => 'true', 'tabindex' => '-1']);
        $class .= ' opacity-50 pointer-events-none';
    }
@endphp

@if ($as === 'a' && $attrs->has('href'))
    <a {{ $attrs->merge(['class' => $class]) }}>{{ $slot }}</a>
@else
    <button {{ $attrs->merge(['type' => 'submit', 'class' => $class]) }}>{{ $slot }}</button>
@endif
