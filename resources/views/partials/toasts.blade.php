@php
    $toastMessages = collect();

    foreach (['status' => 'success', 'success' => 'success', 'error' => 'error', 'warning' => 'warning'] as $key => $type) {
        if (session($key)) {
            $toastMessages->push(['type' => $type, 'message' => session($key)]);
        }
    }

    foreach ($errors->all() as $error) {
        $toastMessages->push(['type' => 'error', 'message' => $error]);
    }
@endphp

@if ($toastMessages->isNotEmpty())
    <div class="toast-stack" data-toast-stack>
        @foreach ($toastMessages as $toast)
            <div class="toast {{ $toast['type'] }}">
                <i class="fa-solid {{ $toast['type'] === 'success' ? 'fa-circle-check' : ($toast['type'] === 'warning' ? 'fa-triangle-exclamation' : 'fa-circle-xmark') }}"></i>
                <span>{{ $toast['message'] }}</span>
                <button type="button" aria-label="Dismiss" onclick="this.closest('.toast').remove()"><i class="fa-solid fa-xmark"></i></button>
            </div>
        @endforeach
    </div>
    <script>
        window.setTimeout(function () {
            document.querySelectorAll('[data-toast-stack] .toast').forEach(function (toast) {
                toast.classList.add('leaving');
                window.setTimeout(function () { toast.remove(); }, 250);
            });
        }, 5200);
    </script>
@endif
