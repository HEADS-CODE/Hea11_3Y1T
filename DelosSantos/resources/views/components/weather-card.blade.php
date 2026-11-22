@props([
    'latitude' => null,
    'longitude' => null,
    'label' => 'Local weather',
    'editUrl' => null,
])

<section class="weather-card" data-weather-card data-latitude="{{ $latitude }}" data-longitude="{{ $longitude }}" aria-live="polite">
    <div class="weather-card-heading"><div><span class="eyebrow">{{ $label }}</span><h2>Weather <em>right now.</em></h2></div><span class="weather-icon" data-weather-icon aria-hidden="true">☼</span></div>
    <p class="weather-status" data-weather-status>@if($latitude !== null && $longitude !== null) Weather is loading for this saved location. @else Your address may be saved, but weather needs a map pin. Use “Locate on map” and submit the profile change for approval. @endif</p>
    @if($editUrl && ($latitude === null || $longitude === null))
        <a class="weather-action" href="{{ $editUrl }}">Open Edit details</a>
    @endif
    <div class="weather-details" data-weather-details hidden><strong data-weather-temperature>—</strong><span data-weather-place>—</span><span data-weather-description>—</span><span data-weather-meta>—</span></div>
</section>
