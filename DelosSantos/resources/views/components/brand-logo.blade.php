{{-- Symbol logo for navigation/cards; full logo for authentication hero areas. --}}
@props(['variant' => 'symbol', 'size' => 36, 'class' => ''])
@php
    $base = $variant === 'full' ? 'puihaha-logo' : 'puihaha-symbol';
    $px = (int) $size;
@endphp
<span class="brand-logo {{ $class }}" style="--logo-size: {{ $px }}px" aria-hidden="true">
    <img class="brand-logo-light" src="{{ asset("assets/logo/{$base}-light-mode.svg") }}" alt="" width="{{ $px }}" height="{{ $px }}">
    <img class="brand-logo-dark" src="{{ asset("assets/logo/{$base}-dark-mode.svg") }}" alt="" width="{{ $px }}" height="{{ $px }}">
</span>
