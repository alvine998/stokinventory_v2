@extends('layouts.app', ['title' => __('messages.activity_tracking'), 'heading' => __('messages.nav_team')])

@section('content')
@include('app.team-access._subnav')

<section class="panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">{{ __('messages.nav_team') }}</p>
            <h2><i class="fa-solid fa-chart-line"></i> {{ __('messages.activity_tracking') }}</h2>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px;align-items:flex-end">
        <label>{{ __('messages.user') }}
            <select name="user_id" onchange="this.form.submit()">
                <option value="">{{ __('messages.all') }}</option>
                @foreach($users as $u)
                <option value="{{ $u->id }}" {{ $user_id == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                @endforeach
            </select>
        </label>
        <label>{{ __('messages.action') }}
            <select name="action" onchange="this.form.submit()">
                <option value="">{{ __('messages.all') }}</option>
                @foreach($actions as $act)
                <option value="{{ $act }}" {{ $action === $act ? 'selected' : '' }}>{{ ucfirst($act) }}</option>
                @endforeach
            </select>
        </label>
        <label>{{ __('messages.from') }}
            <input type="date" name="date_from" value="{{ $date_from }}">
        </label>
        <label>{{ __('messages.to') }}
            <input type="date" name="date_to" value="{{ $date_to }}">
        </label>
        <label>{{ __('messages.search') }}
            <input type="text" name="search" value="{{ $search }}" placeholder="{{ __('messages.search') }}..." style="width:180px">
        </label>
        <button type="submit" class="secondary-button"><i class="fa-solid fa-magnifying-glass"></i></button>
        <a href="{{ route('team-access.activity-log') }}" class="secondary-button">{{ __('messages.reset') }}</a>
    </form>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.date') }}</th>
                    <th>{{ __('messages.user') }}</th>
                    <th>{{ __('messages.action') }}</th>
                    <th>{{ __('messages.description') }}</th>
                    <th>{{ __('messages.subject') }}</th>
                    <th>{{ __('messages.ip_address') }}</th>
                </tr>
            </thead>
            @forelse ($logs as $log)
            <tbody class="row-group">
                <tr>
                    <td style="white-space:nowrap">{{ $log->created_at->format('d M Y H:i') }}</td>
                    <td>{{ $log->user?->name ?? '—' }}</td>
                    <td>
                        @php
                            $actColor = match(true) {
                                in_array($log->action, ['created','login'])  => 'var(--teal)',
                                in_array($log->action, ['updated'])          => 'var(--blue)',
                                in_array($log->action, ['deleted','logout']) => 'var(--rose)',
                                default                                      => 'var(--ink)',
                            };
                        @endphp
                        <span class="badge-status" style="color:{{ $actColor }};border-color:{{ $actColor }}">{{ strtoupper($log->action) }}</span>
                    </td>
                    <td>{{ $log->description }}</td>
                    <td>
                        @if($log->subject_type)
                            <code style="font-size:.75rem">{{ class_basename($log->subject_type) }} #{{ $log->subject_id }}</code>
                        @else
                            —
                        @endif
                    </td>
                    <td><code style="font-size:.75rem">{{ $log->ip_address ?? '—' }}</code></td>
                </tr>
                @if($log->properties)
                <tr class="edit-row" hidden>
                    <td colspan="6" style="background:var(--surface-2);padding:12px 16px">
                        <pre style="font-size:.78rem;overflow:auto;max-height:150px">{{ json_encode($log->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </td>
                </tr>
                @endif
            </tbody>
            @empty
            <tbody><tr><td colspan="6" style="text-align:center;padding:32px;opacity:.5">{{ __('messages.no_data') }}</td></tr></tbody>
            @endforelse
        </table>
    </div>

    {{ $logs->links() }}
</section>
@endsection
