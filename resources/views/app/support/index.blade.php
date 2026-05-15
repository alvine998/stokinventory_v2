@extends('layouts.app', ['title' => __('messages.my_tickets'), 'heading' => __('messages.my_tickets')])

@section('content')
<section class="panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">{{ __('messages.support') }}</p>
            <h2><i class="fa-solid fa-headset"></i> {{ __('messages.my_tickets') }}</h2>
        </div>
        <div class="head-actions">
            <a href="#modal-new-ticket" class="primary-button"><i class="fa-solid fa-plus"></i> {{ __('messages.new_ticket') }}</a>
        </div>
    </div>

    @include('partials.errors')

    @if (session('status'))
        <div class="report-alert-ok" style="margin-bottom:16px">
            <i class="fa-solid fa-circle-check"></i> {{ session('status') }}
        </div>
    @endif

    @if ($rooms->isEmpty())
        <div class="empty-state" style="padding:56px 0">
            <i class="fa-solid fa-ticket" style="font-size:2.5rem;color:var(--teal);opacity:.5"></i>
            <p>{{ __('messages.no_tickets') }}</p>
            <a href="#modal-new-ticket" class="primary-button" style="margin-top:8px"><i class="fa-solid fa-plus"></i> {{ __('messages.new_ticket') }}</a>
        </div>
    @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('messages.ticket_subject') }}</th>
                        <th>{{ __('messages.support_type') }}</th>
                        <th>{{ __('messages.status') }}</th>
                        <th>{{ __('messages.messages_count') }}</th>
                        <th>{{ __('messages.created_at') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rooms as $room)
                    <tr>
                        <td class="text-muted">#{{ $room->id }}</td>
                        <td><strong>{{ $room->subject }}</strong></td>
                        <td><span class="badge-tag">{{ __('messages.ticket_type_' . $room->support_type) }}</span></td>
                        <td>
                            <span class="badge-status {{ $room->status === 'open' ? 'badge-active' : 'badge-inactive' }}">
                                {{ $room->status === 'open' ? __('messages.ticket_status_open') : __('messages.ticket_status_closed') }}
                            </span>
                        </td>
                        <td>{{ $room->messages_count }}</td>
                        <td>{{ $room->created_at->format('d M Y, H:i') }}</td>
                        <td>
                            <a href="{{ route('support.show', $room) }}" class="secondary-button" style="padding:5px 12px;font-size:12px">
                                <i class="fa-solid fa-eye"></i> {{ __('messages.view') }}
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>

{{-- New Ticket Modal --}}
<div class="modal-overlay" id="modal-new-ticket" role="dialog" aria-modal="true" aria-labelledby="modal-new-ticket-title">
    <div class="modal-card">
        <header>
            <h3 id="modal-new-ticket-title"><i class="fa-solid fa-ticket"></i> {{ __('messages.new_ticket') }}</h3>
            <a href="{{ route('support.index') }}" class="icon-button" aria-label="{{ __('messages.close') }}"><i class="fa-solid fa-xmark"></i></a>
        </header>
        <form method="POST" action="{{ route('support.store') }}" class="form-grid">
            @csrf
            <div class="form-grid two">
                <label><span class="label-cap">{{ __('messages.support_type') }} <span class="req">*</span></span>
                    <select name="support_type" required>
                        <option value="general"    {{ old('support_type') === 'general'    ? 'selected' : '' }}>{{ __('messages.ticket_type_general') }}</option>
                        <option value="billing"    {{ old('support_type') === 'billing'    ? 'selected' : '' }}>{{ __('messages.ticket_type_billing') }}</option>
                        <option value="technical"  {{ old('support_type') === 'technical'  ? 'selected' : '' }}>{{ __('messages.ticket_type_technical') }}</option>
                        <option value="onboarding" {{ old('support_type') === 'onboarding' ? 'selected' : '' }}>{{ __('messages.ticket_type_onboarding') }}</option>
                    </select>
                </label>
                <label><span class="label-cap">{{ __('messages.ticket_subject') }} <span class="req">*</span></span>
                    <input name="subject" value="{{ old('subject') }}" placeholder="{{ __('messages.ticket_subject_ph') }}" required maxlength="160">
                </label>
            </div>
            <label><span class="label-cap">{{ __('messages.message') }} <span class="req">*</span></span>
                <textarea name="message" rows="4" placeholder="{{ __('messages.write_message') }}" required maxlength="5000">{{ old('message') }}</textarea>
            </label>
            <div class="modal-actions">
                <a href="{{ route('support.index') }}" class="secondary-button">{{ __('messages.cancel') }}</a>
                <button class="primary-button"><i class="fa-solid fa-paper-plane"></i> {{ __('messages.submit_ticket') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
