@if (config('services.google.maps_key'))
<script async defer src="https://maps.googleapis.com/maps/api/js?key={{ urlencode(config('services.google.maps_key')) }}&callback=initPuihahaMaps"></script>
@else
<script>window.addEventListener('DOMContentLoaded',()=>document.querySelectorAll('[data-map-fallback]').forEach((item)=>{item.hidden=false;}));</script>
@endif
