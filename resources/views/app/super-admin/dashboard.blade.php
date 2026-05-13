@extends('layouts.platform', ['title' => __('messages.super_admin_dashboard'), 'heading' => __('messages.super_admin_dashboard')])

@section('content')
<section class="stats-grid">
    @foreach ($stats as $key => $value)
        <article class="stat-card">
            <i class="fa-solid {{ ['customers'=>'fa-building-user','packages'=>'fa-tags','invoices'=>'fa-file-invoice-dollar','open_support'=>'fa-headset','notifications'=>'fa-bell'][$key] }}"></i>
            <span>{{ __('messages.' . $key) }}</span>
            <strong>{{ number_format($value) }}</strong>
        </article>
    @endforeach
</section>
<section class="panel">
    <div class="section-head"><h2>{{ __('messages.latest_support_rooms') }}</h2></div>
    <div class="activity-list">
        @foreach ($rooms as $room)
            <div class="activity-item"><i class="fa-solid fa-comments"></i><span>{{ $room->business->name }} - {{ $room->subject }}</span><strong>{{ $room->support_type }}</strong></div>
        @endforeach
    </div>
</section>
@endsection
