@props([
    'latitude' => null,
    'longitude' => null,
    'label' => 'Local weather',
    'editUrl' => null,
])

<section class="weather-card {{ !empty($uses_default) ? 'weather-card-default' : '' }}" data-weather-card data-latitude="{{ $latitude }}" data-longitude="{{ $longitude }}" aria-live="polite">
    <div class="weather-card-heading">
        <div><span class="eyebrow">{{ $label }}</span><h2>Weather <em>right now.</em></h2><p class="weather-card-subtitle">A quiet signal from the location connected to this portal.</p></div>
        <span class="weather-icon" data-weather-icon aria-hidden="true">☼</span>
    </div>
    <p class="weather-status" data-weather-status>Weather is loading for this location.</p>
    @if($editUrl && !empty($uses_default))
        <a class="weather-action" href="{{ $editUrl }}">Add a saved location</a>
    @endif
    <div class="weather-details" data-weather-details hidden>
        <strong data-weather-temperature>—</strong>
        <div class="weather-reading">
            <span data-weather-place>—</span>
            <span class="weather-meta" data-weather-meta>—</span>
            <span class="weather-description" data-weather-description>—</span>
        </div>
    </div>
</section>
