@extends('layouts.platform', ['title' => __('messages.support_rooms'), 'heading' => __('messages.support_rooms')])

@section('content')
<section class="panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">{{ __('messages.platform_control') }}</p>
            <h2><i class="fa-solid fa-headset"></i> {{ __('messages.support_rooms') }}</h2>
        </div>
        @if (auth()->user()->isPlatformStaff(['super_admin', 'platform_admin']))
            <a href="#modal-open-room" class="primary-button"><i class="fa-solid fa-plus"></i> {{ __('messages.open_room') }}</a>
        @endif
    </div>

    @include('partials.errors')

    <div class="support-grid">
        @forelse ($rooms as $room)
        <article class="support-card">
            <div class="support-card-header">
                <div class="support-card-meta">
                    <span class="badge-tag">{{ $room->support_type }}</span>
                    <h3>{{ $room->subject }}</h3>
                    <p class="text-muted">{{ $room->business->name }}</p>
                </div>
                <div class="support-card-status">
                    <span class="badge-status {{ $room->status === 'open' ? 'badge-active' : 'badge-inactive' }}">{{ $room->status }}</span>
                    @if ($room->assigned_user_id)
                        <small class="text-muted"><i class="fa-solid fa-user-tie"></i> {{ $room->assignedUser?->name }}</small>
                    @endif
                </div>
            </div>

            <div class="chat-thread">
                @foreach ($room->messages as $message)
                <div class="chat-bubble {{ $message->is_staff_reply ? 'chat-bubble--staff' : 'chat-bubble--customer' }}">
                    <p>{{ $message->message }}</p>
                    <time>{{ $message->created_at->format('d M, H:i') }}</time>
                </div>
                @endforeach
            </div>

            <form method="POST" action="{{ route('super-admin.support-rooms.reply', $room) }}" class="reply-form">
                @csrf
                <input name="message" placeholder="{{ __('messages.reply_message') }}" required>
                <button class="primary-button"><i class="fa-solid fa-reply"></i> {{ __('messages.reply') }}</button>
            </form>
        </article>
        @empty
            <p class="empty-cell">{{ __('messages.no_support_rooms') }}</p>
        @endforelse
    </div>
</section>

@if (auth()->user()->isPlatformStaff(['super_admin', 'platform_admin']))
{{-- Open Support Room Modal --}}
<div class="modal-overlay" id="modal-open-room" role="dialog" aria-modal="true" aria-labelledby="modal-open-room-title">
    <div class="modal-card">
        <header>
            <h3 id="modal-open-room-title"><i class="fa-solid fa-headset"></i> {{ __('messages.open_room') }}</h3>
            <a href="#" class="icon-button" aria-label="{{ __('messages.close') }}"><i class="fa-solid fa-xmark"></i></a>
        </header>
        <form method="POST" action="{{ route('super-admin.support-rooms.store') }}" class="form-grid">
            @csrf
            <div class="form-grid two">
                <label><span class="label-cap">{{ __('messages.customer') }} <span class="req">*</span></span>
                    <select name="business_id" required>
                        <option value="">{{ __('messages.select_customer') }}</option>
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label><span class="label-cap">{{ __('messages.assign_agent') }}</span>
                    <select name="assigned_user_id">
                        <option value="">{{ __('messages.select_agent') }}</option>
                        @foreach ($agents as $agent)
                            <option value="{{ $agent->id }}">{{ $agent->name }} – {{ $agent->platform_role }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
            <div class="form-grid two">
                <label><span class="label-cap">{{ __('messages.support_type') }} <span class="req">*</span></span>
                    <select name="support_type" required>
                        <option value="billing">Billing</option>
                        <option value="technical">Technical</option>
                        <option value="onboarding">Onboarding</option>
                        <option value="general">General</option>
                    </select>
                </label>
                <label><span class="label-cap">{{ __('messages.subject') }} <span class="req">*</span></span>
                    <input name="subject" value="{{ old('subject') }}" placeholder="{{ __('messages.subject') }}" required>
                </label>
            </div>
            <label><span class="label-cap">{{ __('messages.message') }} <span class="req">*</span></span>
                <textarea name="message" rows="3" placeholder="{{ __('messages.message') }}" required>{{ old('message') }}</textarea>
            </label>
            <div class="modal-actions">
                <a href="#" class="secondary-button">{{ __('messages.cancel') }}</a>
                <button class="primary-button"><i class="fa-solid fa-plus"></i> {{ __('messages.open_room') }}</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection

