@extends('layouts.app', ['title' => __('messages.audit_log'), 'heading' => __('messages.nav_team')])

@section('content')
@include('app.team-access._subnav')

<section class="panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">{{ __('messages.nav_team') }}</p>
            <h2><i class="fa-solid fa-shield-halved"></i> {{ __('messages.audit_log') }}</h2>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px;align-items:flex-end">
        <label>{{ __('messages.event') }}
            <select name="event" onchange="this.form.submit()">
                <option value="">{{ __('messages.all') }}</option>
                @foreach(['created','updated','deleted','restored'] as $ev)
                <option value="{{ $ev }}" {{ $event === $ev ? 'selected' : '' }}>{{ ucfirst($ev) }}</option>
                @endforeach
            </select>
        </label>
        <label>{{ __('messages.user') }}
            <select name="user_id" onchange="this.form.submit()">
                <option value="">{{ __('messages.all') }}</option>
                @foreach($users as $u)
                <option value="{{ $u->id }}" {{ $user_id == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                @endforeach
            </select>
        </label>
        <label>{{ __('messages.model_type') }}
            <input type="text" name="model_type" value="{{ $model_type }}" placeholder="e.g. Product" style="width:140px">
        </label>
        <label>{{ __('messages.from') }}
            <input type="date" name="date_from" value="{{ $date_from }}">
        </label>
        <label>{{ __('messages.to') }}
            <input type="date" name="date_to" value="{{ $date_to }}">
        </label>
        <button type="submit" class="secondary-button"><i class="fa-solid fa-magnifying-glass"></i></button>
        <a href="{{ route('team-access.audit-log') }}" class="secondary-button">{{ __('messages.reset') }}</a>
    </form>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.date') }}</th>
                    <th>{{ __('messages.user') }}</th>
                    <th>{{ __('messages.event') }}</th>
                    <th>{{ __('messages.model') }}</th>
                    <th>{{ __('messages.record') }}</th>
                    <th>{{ __('messages.ip_address') }}</th>
                    <th>{{ __('messages.changes') }}</th>
                </tr>
            </thead>
            @forelse ($logs as $log)
            <tbody class="row-group">
                <tr>
                    <td style="white-space:nowrap">{{ $log->created_at->format('d M Y H:i') }}</td>
                    <td>{{ $log->user?->name ?? '—' }}</td>
                    <td>
                        @php
                            $evColor = match($log->event) {
                                'created'  => 'var(--teal)',
                                'updated'  => 'var(--blue)',
                                'deleted'  => 'var(--rose)',
                                default    => 'var(--ink)',
                            };
                        @endphp
                        <span class="badge-status" style="color:{{ $evColor }};border-color:{{ $evColor }}">{{ strtoupper($log->event) }}</span>
                    </td>
                    <td><code>{{ class_basename($log->auditable_type) }}</code></td>
                    <td>{{ $log->auditable_label ?: '#'.$log->auditable_id }}</td>
                    <td><code style="font-size:.75rem">{{ $log->ip_address }}</code></td>
                    <td>
                        @if($log->old_values || $log->new_values)
                        <button type="button" class="icon-button" onclick="const r=this.closest('tbody').querySelector('.edit-row');r.hidden=!r.hidden">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                        @else
                        —
                        @endif
                    </td>
                </tr>
                @if($log->old_values || $log->new_values)
                <tr class="edit-row" hidden>
                    <td colspan="7" style="background:var(--surface-2);padding:16px">
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                            @if($log->old_values)
                            <div>
                                <p style="font-weight:600;margin-bottom:4px;color:var(--rose)">{{ __('messages.old_values') }}</p>
                                <pre style="font-size:.78rem;overflow:auto;max-height:200px;background:var(--surface-1);padding:8px;border-radius:6px">{{ json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </div>
                            @endif
                            @if($log->new_values)
                            <div>
                                <p style="font-weight:600;margin-bottom:4px;color:var(--teal)">{{ __('messages.new_values') }}</p>
                                <pre style="font-size:.78rem;overflow:auto;max-height:200px;background:var(--surface-1);padding:8px;border-radius:6px">{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </div>
                            @endif
                        </div>
                        @if($log->user_agent)
                        <p style="font-size:.78rem;opacity:.5;margin-top:8px">UA: {{ Str::limit($log->user_agent, 120) }}</p>
                        @endif
                    </td>
                </tr>
                @endif
            </tbody>
            @empty
            <tbody><tr><td colspan="7" style="text-align:center;padding:32px;opacity:.5">{{ __('messages.no_data') }}</td></tr></tbody>
            @endforelse
        </table>
    </div>

    {{ $logs->links() }}
</section>
@endsection
