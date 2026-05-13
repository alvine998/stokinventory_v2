@extends('layouts.app', ['title' => $room->subject, 'heading' => __('messages.my_tickets')])

@section('content')
<section class="panel">
    {{-- Header --}}
    <div class="section-head">
        <div>
            <p class="eyebrow">
                <a href="{{ route('support.index') }}" style="color:var(--teal)">
                    <i class="fa-solid fa-arrow-left"></i> {{ __('messages.my_tickets') }}
                </a>
            </p>
            <h2 style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
                <span class="badge-tag">{{ __('messages.ticket_type_' . $room->support_type) }}</span>
                {{ $room->subject }}
                <span class="badge-status {{ $room->status === 'open' ? 'badge-active' : 'badge-inactive' }}" style="font-size:11px">
                    {{ $room->status === 'open' ? __('messages.ticket_status_open') : __('messages.ticket_status_closed') }}
                </span>
            </h2>
            <p class="text-muted" style="font-size:12px;margin-top:4px">
                <i class="fa-solid fa-clock"></i> {{ __('messages.created_at') }}: {{ $room->created_at->format('d M Y, H:i') }}
                @if ($room->assignedUser)
                    &nbsp;·&nbsp;<i class="fa-solid fa-user-tie"></i> {{ $room->assignedUser->name }}
                @endif
            </p>
        </div>
        <div style="display:flex;gap:8px;align-items:flex-start">
            @if ($room->status === 'open')
                <form method="POST" action="{{ route('support.close', $room) }}">
                    @csrf
                    <button class="secondary-button" style="font-size:12px;padding:7px 14px">
                        <i class="fa-solid fa-lock"></i> {{ __('messages.close_ticket') }}
                    </button>
                </form>
            @else
                <form method="POST" action="{{ route('support.reopen', $room) }}">
                    @csrf
                    <button class="primary-button" style="font-size:12px;padding:7px 14px">
                        <i class="fa-solid fa-lock-open"></i> {{ __('messages.reopen_ticket') }}
                    </button>
                </form>
            @endif
        </div>
    </div>

    @include('partials.errors')

    @if (session('status'))
        <div class="report-alert-ok" style="margin-bottom:16px">
            <i class="fa-solid fa-circle-check"></i> {{ session('status') }}
        </div>
    @endif

    {{-- Chat Thread --}}
    <div class="chat-thread" id="chat-thread" style="max-height:520px;overflow-y:auto;border:1px solid #e3ecef;border-radius:12px;padding:20px;background:#f8fbfc;margin-bottom:16px;display:grid;gap:14px">
        @forelse ($room->messages as $msg)
        @php $isMine = ! $msg->is_staff_reply; @endphp
        <div style="display:flex;flex-direction:column;align-items:{{ $isMine ? 'flex-end' : 'flex-start' }}">
            <p style="font-size:11px;font-weight:600;color:var(--teal);margin:0 0 4px;opacity:.8">
                {{ $isMine ? __('messages.you') : ($msg->user?->name ?? __('messages.support_team')) }}
            </p>
            <div class="chat-bubble {{ $isMine ? 'chat-bubble--customer' : 'chat-bubble--staff' }}" style="max-width:72%">
                <p style="white-space:pre-wrap">{{ $msg->message }}</p>
                <time>{{ $msg->created_at->format('d M Y, H:i') }}</time>
            </div>
        </div>
        @empty
            <p class="text-muted" style="text-align:center">{{ __('messages.no_data_yet') }}</p>
        @endforelse
    </div>

    {{-- Reply Form --}}
    @if ($room->status === 'open')
        <form method="POST" action="{{ route('support.reply', $room) }}" class="reply-form">
            @csrf
            <textarea name="message" placeholder="{{ __('messages.write_message') }}" required maxlength="5000"
                      style="resize:vertical;min-height:72px;border-radius:10px;border:1px solid #cddde7;padding:10px 14px;font-size:14px;font-family:inherit"></textarea>
            <button class="primary-button" style="align-self:flex-end;padding:10px 20px">
                <i class="fa-solid fa-paper-plane"></i> {{ __('messages.send_message') }}
            </button>
        </form>
    @else
        <div class="report-alert-warn" style="margin-top:8px">
            <i class="fa-solid fa-lock"></i> {{ __('messages.ticket_closed_note') }}
        </div>
    @endif
</section>
@endsection

@push('scripts')
<script>
// Auto-scroll chat to bottom
(function () {
    var el = document.getElementById('chat-thread');
    if (el) el.scrollTop = el.scrollHeight;
})();
</script>
@endpush
