@php
    $model = $model ?? null;
    $addressRequired = $addressRequired ?? false;
    $requiredMark = $addressRequired ? ' <span>*</span>' : '';
@endphp
<div class="address-fields" data-address-map>
    <div class="form-field"><label for="unit_floor_building">Unit / Floor / Building <span class="optional">Optional</span></label><input id="unit_floor_building" name="unit_floor_building" value="{{ old('unit_floor_building', $model->unit_floor_building ?? '') }}" data-address-part></div>
    <div class="form-field"><label for="house_street">House no. and Street{!! $requiredMark !!}</label><input id="house_street" name="house_street" value="{{ old('house_street', $model->house_street ?? '') }}" @if($addressRequired) required @endif data-address-part>@error('house_street')<span class="field-error">{{ $message }}</span>@enderror</div>
    <div class="form-field"><label for="barangay">Barangay{!! $requiredMark !!}</label><input id="barangay" name="barangay" value="{{ old('barangay', $model->barangay ?? '') }}" @if($addressRequired) required @endif data-address-part>@error('barangay')<span class="field-error">{{ $message }}</span>@enderror</div>
    <div class="form-field"><label for="city_municipality">City or Municipality{!! $requiredMark !!}</label><input id="city_municipality" name="city_municipality" value="{{ old('city_municipality', $model->city_municipality ?? '') }}" @if($addressRequired) required @endif data-address-part>@error('city_municipality')<span class="field-error">{{ $message }}</span>@enderror</div>
    <div class="form-field"><label for="province">Province{!! $requiredMark !!}</label><input id="province" name="province" value="{{ old('province', $model->province ?? '') }}" @if($addressRequired) required @endif data-address-part>@error('province')<span class="field-error">{{ $message }}</span>@enderror</div>
    <div class="form-field"><label for="zip_code">ZIP code{!! $requiredMark !!}</label><input id="zip_code" name="zip_code" inputmode="numeric" value="{{ old('zip_code', $model->zip_code ?? '') }}" @if($addressRequired) required @endif data-address-part>@error('zip_code')<span class="field-error">{{ $message }}</span>@enderror</div>
    <input type="hidden" id="address" name="address" value="{{ old('address', $model->address ?? '') }}" data-full-address>
    <input type="hidden" id="latitude" name="latitude" value="{{ old('latitude', $model->latitude ?? '') }}">
    <input type="hidden" id="longitude" name="longitude" value="{{ old('longitude', $model->longitude ?? '') }}">
    <div class="map-field form-span-2"><div class="map-field-heading"><label>Location pin <span class="optional">Optional</span></label><button class="button button-ghost button-small" type="button" data-locate-map>Locate on map</button></div><div class="map-picker-canvas" data-map-canvas><span>Type the address fields, or place a pin when Maps is available.</span></div><p class="map-privacy">Location coordinates are saved only to support membership records and map-assisted address capture. You may continue without a pin.</p><p class="map-fallback" data-map-fallback hidden>Map pin is unavailable right now. You can continue using the typed address.</p></div>
</div>
