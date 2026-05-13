@extends('layouts.platform', ['title' => __('messages.notification_management'), 'heading' => __('messages.notification_management')])

@section('content')
<section class="panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">{{ __('messages.platform_control') }}</p>
            <h2><i class="fa-solid fa-bell"></i> {{ __('messages.notification_management') }}</h2>
        </div>
        <a href="#modal-add-notification" class="primary-button"><i class="fa-solid fa-paper-plane"></i> {{ __('messages.send_notification') }}</a>
    </div>

    @include('partials.errors')

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.title') }}</th>
                    <th>{{ __('messages.message') }}</th>
                    <th>{{ __('messages.audience') }}</th>
                    <th>{{ __('messages.channel') }}</th>
                    <th>{{ __('messages.sent_at') }}</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($notifications as $notification)
                <tr>
                    <td><strong>{{ $notification->title }}</strong></td>
                    <td>{{ Str::limit($notification->message, 60) }}</td>
                    <td><span class="badge-tag">{{ $notification->audience }}</span></td>
                    <td><code>{{ $notification->channel }}</code></td>
                    <td>{{ optional($notification->sent_at)->format('d M Y H:i') ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="empty-cell">{{ __('messages.no_notifications') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>

{{-- Send Notification Modal --}}
<div class="modal-overlay" id="modal-add-notification" role="dialog" aria-modal="true" aria-labelledby="modal-add-notification-title">
    <div class="modal-card">
        <header>
            <h3 id="modal-add-notification-title"><i class="fa-solid fa-paper-plane"></i> {{ __('messages.send_notification') }}</h3>
            <a href="#" class="icon-button" aria-label="{{ __('messages.close') }}"><i class="fa-solid fa-xmark"></i></a>
        </header>
        <form method="POST" action="{{ route('super-admin.notifications.store') }}" class="form-grid" id="notif-form">
            @csrf
            <div class="form-grid two">
                <label><span class="label-cap">{{ __('messages.audience') }} <span class="req">*</span></span>
                    <select name="audience" required id="notif-audience" onchange="toggleSpecific(this)">
                        <option value="all">{{ __('messages.all_customers') }}</option>
                        <option value="specific">{{ __('messages.specific_customer') }}</option>
                    </select>
                </label>
                <label id="specific-customer-wrap" style="display:none"><span class="label-cap">{{ __('messages.customer') }}</span>
                    <select name="business_id">
                        <option value="">{{ __('messages.select_customer') }}</option>
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
            <label><span class="label-cap">{{ __('messages.title') }} <span class="req">*</span></span>
                <input name="title" value="{{ old('title') }}" placeholder="{{ __('messages.title') }}" required>
            </label>
            <label><span class="label-cap">{{ __('messages.message') }} <span class="req">*</span></span>
                <textarea name="message" rows="3" placeholder="{{ __('messages.message') }}" required>{{ old('message') }}</textarea>
            </label>
            <label><span class="label-cap">{{ __('messages.channel') }} <span class="req">*</span></span>
                <select name="channel" required>
                    <option value="in_app" @selected(old('channel','in_app')=='in_app')>In-App</option>
                    <option value="email" @selected(old('channel')=='email')>Email</option>
                    <option value="sms" @selected(old('channel')=='sms')>SMS</option>
                </select>
            </label>
            <div class="modal-actions">
                <a href="#" class="secondary-button">{{ __('messages.cancel') }}</a>
                <button class="primary-button"><i class="fa-solid fa-paper-plane"></i> {{ __('messages.send') }}</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function toggleSpecific(sel) {
    document.getElementById('specific-customer-wrap').style.display = sel.value === 'specific' ? '' : 'none';
}
</script>
@endpush
@endsection

