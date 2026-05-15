@extends('layouts.app', ['title' => __('messages.audit_log'), 'heading' => __('messages.nav_team')])

@section('content')
@include('app.team-access._subnav')

<section class="panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">{{ __('messages.nav_team') }}</p>
            <h2><i class="fa-solid fa-shield-halved"></i> {{ __('messages.audit_log') }}</h2>
        </div>
        <span style="font-size:13px;color:#79909d;font-weight:600">
            {{ $logs->total() }} {{ __('messages.records') ?? 'records' }}
        </span>
    </div>

    {{-- Filters --}}
    <form method="GET" class="filter-bar">
        <label>{{ __('messages.event') }}
            <select name="event" onchange="this.form.submit()">
                <option value="">{{ __('messages.all') }}</option>
                @foreach(['created','updated','deleted','restored'] as $ev)
                <option value="{{ $ev }}" {{ $event === $ev ? 'selected' : '' }}>
                    {{ ucfirst($ev) }}
                </option>
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
            <input type="text" name="model_type" value="{{ $model_type }}" placeholder="e.g. Product" style="width:130px">
        </label>
        <label>{{ __('messages.from') }}
            <input type="date" name="date_from" value="{{ $date_from }}">
        </label>
        <label>{{ __('messages.to') }}
            <input type="date" name="date_to" value="{{ $date_to }}">
        </label>
        <div style="display:flex;gap:8px;align-items:flex-end">
            <button type="submit" class="secondary-button"><i class="fa-solid fa-magnifying-glass"></i></button>
            <a href="{{ route('team-access.audit-log') }}" class="secondary-button">{{ __('messages.reset') }}</a>
        </div>
    </form>

    {{-- Event legend --}}
    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px">
        @foreach([
            'created'  => ['var(--teal)',   'var(--soft-teal)',   'fa-plus-circle'],
            'updated'  => ['var(--blue)',   'var(--soft-blue)',   'fa-pen'],
            'deleted'  => ['var(--rose)',   'var(--soft-rose)',   'fa-trash'],
            'restored' => ['var(--violet)', 'var(--soft-violet)', 'fa-rotate-left'],
        ] as $evName => [$color, $bg, $icon])
        <span style="display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:999px;font-size:12px;font-weight:700;background:{{ $bg }};color:{{ $color }}">
            <i class="fa-solid {{ $icon }}" style="font-size:10px"></i>{{ ucfirst($evName) }}
        </span>
        @endforeach
    </div>

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
            @php
                $evColors = [
                    'created'  => ['var(--teal)',   'var(--soft-teal)',   'fa-plus-circle'],
                    'updated'  => ['var(--blue)',   'var(--soft-blue)',   'fa-pen'],
                    'deleted'  => ['var(--rose)',   'var(--soft-rose)',   'fa-trash'],
                    'restored' => ['var(--violet)', 'var(--soft-violet)', 'fa-rotate-left'],
                ];
                [$evColor, $evBg, $evIcon] = $evColors[$log->event] ?? ['var(--ink)', '#f1f5f9', 'fa-circle'];
            @endphp
            <tbody class="row-group">
                <tr>
                    <td style="white-space:nowrap;font-size:13px">
                        {{ $log->created_at->format('d M Y') }}
                        <br><small style="opacity:.5">{{ $log->created_at->format('H:i:s') }}</small>
                    </td>
                    <td>
                        @if($log->user)
                        <span style="display:inline-flex;align-items:center;gap:6px;font-size:13px">
                            <i class="fa-solid fa-user-circle" style="color:#b0c4cc;font-size:15px"></i>
                            <span>
                                <strong style="display:block;line-height:1.2">{{ $log->user->name }}</strong>
                                <small style="opacity:.5;font-size:11px">{{ $log->user->email }}</small>
                            </span>
                        </span>
                        @else<span style="opacity:.35">—</span>@endif
                    </td>
                    <td>
                        <span style="display:inline-flex;align-items:center;gap:5px;background:{{ $evBg }};color:{{ $evColor }};border-radius:999px;padding:4px 10px;font-size:11px;font-weight:800;letter-spacing:.04em">
                            <i class="fa-solid {{ $evIcon }}" style="font-size:9px"></i>
                            {{ strtoupper($log->event) }}
                        </span>
                    </td>
                    <td>
                        <code style="background:#e8f0f3;color:#38505d;border-radius:5px;padding:3px 7px;font-size:12px">
                            {{ class_basename($log->auditable_type) }}
                        </code>
                    </td>
                    <td style="font-size:13px;max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                        {{ $log->auditable_label ?: '#'.$log->auditable_id }}
                    </td>
                    <td>
                        @if($log->ip_address)
                        <code style="font-size:11px;background:#f1f5f9;padding:2px 6px;border-radius:4px;color:#496371">{{ $log->ip_address }}</code>
                        @else<span style="opacity:.35">—</span>@endif
                    </td>
                    <td>
                        @if($log->old_values || $log->new_values)
                        <button type="button" class="icon-button" title="{{ __('messages.view') }} changes"
                            onclick="const r=this.closest('tbody').querySelector('.edit-row');r.hidden=!r.hidden;this.querySelector('i').classList.toggle('fa-eye');this.querySelector('i').classList.toggle('fa-eye-slash')">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                        @else<span style="opacity:.35">—</span>@endif
                    </td>
                </tr>
                @if($log->old_values || $log->new_values)
                <tr class="edit-row" hidden>
                    <td colspan="7" style="padding:0;border-top:2px solid {{ $evColor }}">
                        <div style="padding:16px 20px;background:{{ $evBg }}">
                            <p style="font-size:11px;font-weight:700;color:{{ $evColor }};text-transform:uppercase;letter-spacing:.05em;margin-bottom:12px">
                                <i class="fa-solid {{ $evIcon }}"></i>
                                {{ strtoupper($log->event) }} — {{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}
                            </p>
                            <div style="display:grid;grid-template-columns:{{ ($log->old_values && $log->new_values) ? '1fr 1fr' : '1fr' }};gap:16px">
                                @if($log->old_values)
                                <div>
                                    <p style="font-size:11px;font-weight:700;color:var(--rose);text-transform:uppercase;letter-spacing:.04em;margin-bottom:6px">
                                        <i class="fa-solid fa-arrow-left"></i> {{ __('messages.old_values') }}
                                    </p>
                                    <div style="background:white;border:1px solid #ffd0d0;border-radius:8px;overflow:hidden">
                                        @foreach($log->old_values as $key => $val)
                                        <div style="display:flex;border-bottom:1px solid #ffd0d0;font-size:12px">
                                            <span style="padding:6px 10px;background:#fff0f3;color:var(--rose);font-weight:700;min-width:120px;border-right:1px solid #ffd0d0;word-break:break-all">{{ $key }}</span>
                                            <span style="padding:6px 10px;color:#496371;word-break:break-all;flex:1">{{ is_array($val) ? json_encode($val) : $val }}</span>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                                @if($log->new_values)
                                <div>
                                    <p style="font-size:11px;font-weight:700;color:var(--teal);text-transform:uppercase;letter-spacing:.04em;margin-bottom:6px">
                                        <i class="fa-solid fa-arrow-right"></i> {{ __('messages.new_values') }}
                                    </p>
                                    <div style="background:white;border:1px solid #a7f0e4;border-radius:8px;overflow:hidden">
                                        @foreach($log->new_values as $key => $val)
                                        <div style="display:flex;border-bottom:1px solid #a7f0e4;font-size:12px">
                                            <span style="padding:6px 10px;background:var(--soft-teal);color:var(--teal);font-weight:700;min-width:120px;border-right:1px solid #a7f0e4;word-break:break-all">{{ $key }}</span>
                                            <span style="padding:6px 10px;color:#496371;word-break:break-all;flex:1">{{ is_array($val) ? json_encode($val) : $val }}</span>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                            </div>
                            @if($log->user_agent)
                            <p style="font-size:11px;color:#79909d;margin-top:10px;display:flex;align-items:center;gap:6px">
                                <i class="fa-solid fa-laptop"></i>
                                {{ Str::limit($log->user_agent, 100) }}
                            </p>
                            @endif
                        </div>
                    </td>
                </tr>
                @endif
            </tbody>
            @empty
            <tbody><tr><td colspan="7">
                <div style="text-align:center;padding:48px 24px;opacity:.5">
                    <i class="fa-solid fa-shield-halved" style="font-size:36px;display:block;margin-bottom:12px;opacity:.4;color:var(--teal)"></i>
                    <p style="font-weight:700;margin:0">{{ __('messages.no_data') }}</p>
                    <p style="font-size:13px;margin:4px 0 0;opacity:.7">No audit events match your filters.</p>
                </div>
            </td></tr></tbody>
            @endforelse
        </table>
    </div>

    {{ $logs->links() }}
</section>
@endsection
