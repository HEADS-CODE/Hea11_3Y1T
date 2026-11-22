<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Registration | Puihaha</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="auth-page auth-member">
<main class="auth-shell">
    <section class="auth-scene"><span class="auth-kicker">Join Puihaha</span><h1>Begin your<br><em>next chapter.</em></h1><p>Create a member account and choose the learning membership that fits your journey.</p><span class="auth-orbit" aria-hidden="true">✦</span></section>
    <section class="auth-card auth-register-card">
        <a class="auth-brand" href="{{ route('login.member') }}"><span class="brand-mark">P</span><span><strong>Puihaha</strong><small>Member registration</small></span></a>
        <div class="auth-heading"><span class="eyebrow">New member</span><h2>Start <em>here.</em></h2><p>Confirm your email, then wait for membership approval.</p></div>
        @if ($errors->any())<div class="auth-alert auth-alert-error">Please review the highlighted details.</div>@endif
        <form method="POST" action="{{ route('register.member.submit') }}" class="auth-form register-form" data-member-registration>
            @csrf
            <div class="register-grid">
                <div class="auth-field"><label for="first_name">First name</label><input id="first_name" name="first_name" value="{{ old('first_name') }}" required></div>
                <div class="auth-field"><label for="middle_name">Middle name</label><input id="middle_name" name="middle_name" value="{{ old('middle_name') }}"></div>
                <div class="auth-field"><label for="last_name">Last name</label><input id="last_name" name="last_name" value="{{ old('last_name') }}" required></div>
                <div class="auth-field register-span-2"><label for="email">Email address</label><input id="email" type="email" name="email" value="{{ old('email') }}" required></div>
                <div class="auth-field"><label for="phone">Phone number</label><input id="phone" name="phone" value="{{ old('phone') }}"></div>
                <div class="auth-field"><label for="birth_date">Birth date</label><input id="birth_date" type="date" name="birth_date" value="{{ old('birth_date') }}" required></div>
                <div class="auth-field register-span-2"><label for="address">Address</label><textarea id="address" name="address" rows="3" required>{{ old('address') }}</textarea></div>
                <div class="auth-field"><label for="membership_category_id">Category</label><select id="membership_category_id" name="membership_category_id" required><option value="">Choose category</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected(old('membership_category_id') == $category->id)>{{ $category->name }}</option>@endforeach</select></div>
                <div class="auth-field"><label for="membership_level_id">Level</label><select id="membership_level_id" name="membership_level_id" required><option value="">Choose level</option>@foreach ($levels as $level)<option value="{{ $level->id }}" @selected(old('membership_level_id') == $level->id)>{{ $level->name }} · ₱{{ number_format($level->fee, 2) }}</option>@endforeach</select></div>
                <div class="auth-field"><label for="password">Password</label><input id="password" type="password" name="password" required></div>
                <div class="auth-field"><label for="password_confirmation">Confirm password</label><input id="password_confirmation" type="password" name="password_confirmation" required></div>
            </div>
            <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude') }}">
            <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude') }}">
            <div class="map-picker" data-map-picker><div class="map-picker-canvas" data-map-canvas><span>Map pin is optional. Type your address above.</span></div></div>
            <button class="auth-submit" type="submit">Create member account <span>↗</span></button>
        </form>
        <div class="auth-switch">Already registered? <a href="{{ route('login.member') }}">Sign in</a></div>
    </section>
</main>
@if (config('services.google.maps_key'))
<script>window.initMemberMap=function(){const canvas=document.querySelector('[data-map-canvas]');if(!canvas)return;const map=new google.maps.Map(canvas,{center:{lat:14.5995,lng:120.9842},zoom:11,disableDefaultUI:true});let marker;map.addListener('click',event=>{if(marker)marker.setMap(null);marker=new google.maps.Marker({position:event.latLng,map});document.querySelector('#latitude').value=event.latLng.lat().toFixed(7);document.querySelector('#longitude').value=event.latLng.lng().toFixed(7);});};</script><script async defer src="https://maps.googleapis.com/maps/api/js?key={{ urlencode(config('services.google.maps_key')) }}&callback=initMemberMap" onerror="document.querySelector('[data-map-picker]')?.insertAdjacentHTML('afterend','<p class=\"map-fallback\">The map is unavailable. You can continue using the typed address.</p>')"></script>
@else
<script>document.querySelector('[data-map-picker]')?.insertAdjacentHTML('afterend','<p class="map-fallback">Map pin is unavailable right now. You can continue using the typed address.</p>');</script>
@endif
</body>
</html>
