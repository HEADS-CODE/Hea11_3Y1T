@php
    $toastMessages = collect([
        session('success') ? ['type' => 'success', 'message' => session('success')] : null,
        session('error') ? ['type' => 'error', 'message' => session('error')] : null,
        session('info') ? ['type' => 'info', 'message' => session('info')] : null,
    ])->filter();
@endphp
@if($toastMessages->isNotEmpty())<div class="toast-region" data-toast-region aria-live="polite" aria-atomic="false">@foreach($toastMessages as $toast)<div class="toast toast-{{ $toast['type'] }}" role="status" data-toast><span>{{ $toast['message'] }}</span><button type="button" data-toast-close aria-label="Close notification">×</button></div>@endforeach</div>@endif
