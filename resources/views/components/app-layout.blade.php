@props(['title' => null])

@php
    $layoutTitle = $title;
@endphp

<x-layouts.app :title="$layoutTitle">
    {{ $slot }}
</x-layouts.app>

