@props([
    'src' => '',
    'alt' => '',
    'class' => '',
    'loading' => 'lazy',
    'containerClass' => '',
    'placeholderHeight' => '0'
])

@php
    // Generate a deterministic class for min-height to avoid inline style attributes (CSP-friendly)
    $ph = trim($placeholderHeight);
    $hasSkeleton = $ph !== '' && $ph !== '0' && $ph !== '0px';
    $placeholderClass = $hasSkeleton ? 'lazy-min-'.substr(md5($placeholderHeight), 0, 8) : '';
    $useWrapper = $hasSkeleton || trim($containerClass) !== '';
@endphp

@if($useWrapper)
    <div class="{{ $containerClass ?? '' }} {{ $hasSkeleton ? 'relative overflow-hidden' : '' }} {{ $placeholderClass }}">
        <!-- Actual image (no Alpine gating to avoid invisible images) -->
        <img
             src="{{ $src }}"
             alt="{{ $alt ?? '' }}"
             class="{{ $class ?? '' }}"
             loading="{{ $loading ?? 'lazy' }}"
             decoding="async"
             />
    </div>
@else
    <!-- Render without wrapper for small inline icons -->
    <img
         src="{{ $src }}"
         alt="{{ $alt ?? '' }}"
         class="{{ $class ?? '' }}"
         loading="{{ $loading ?? 'lazy' }}"
         decoding="async"
         />
@endif

@push('after-styles')
<style nonce="{{ request()->attributes->get('csp_nonce') }}">
.lazy-image-container {
    position: relative;
    display: inline-block;
    overflow: hidden;
}

.lazy-placeholder {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    min-height: 40px;
    z-index: 1;
}

.lazy-image {
    display: block;
    max-width: 100%;
    height: auto;
    position: relative;
    z-index: 2;
    transition: opacity 0.3s ease;
}

/* Deterministic min-height class injected per instance to avoid inline style attributes */
@if($hasSkeleton && $placeholderClass)
.{{ $placeholderClass }} {
    min-height: {{ $placeholderHeight }};
}
@endif
</style>
@endpush