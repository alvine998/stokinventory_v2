@extends('layouts.app', ['title' => __('messages.login_history'), 'heading' => __('messages.nav_team')])

@section('content')
@include('app.team-access._subnav')

<section class="panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">{{ __('messages.nav_team') }}</p>
            <h2><i class="fa-solid fa-right-to-bracket"></i> {{ __('messages.login_history') }}</h2>
        </div>
        <span style="font-size:13px;color:#79909d;font-weight:600">{{ $logs->total() }} {{ __('messages.records') ?? 'records' }}</span>
    </div>

    {{-- Summary Cards --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:14px;margin-bottom:22px">
        <div class="stat-card" style="border-top:3px solid var(--teal);padding:16px 18px">
            <div style="display:flex;align-items:center;gap:10px">
                <i class="fa-solid fa-right-to-bracket" style="font-size:20px;color:var(--teal)"></i>
                <div>
                    <p style="font-size:11px;font-weight:700;color:#79909d;text-transform:uppercase;letter-spacing:.04em;margin:0">{{ __('messages.logins') ?? 'Logins' }}</p>
                    <p style="font-size:28px;font-weight:800;color:var(--teal);margin:0;line-height:1.1">{{ $loginCount }}</p>
                </div>
            </div>
        </div>
        <div class="stat-card" style="border-top:3px solid var(--rose);padding:16px 18px">
            <div style="display:flex;align-items:center;gap:10px">
                <i class="fa-solid fa-circle-xmark" style="font-size:20px;color:var(--rose)"></i>
                <div>
                    <p style="font-size:11px;font-weight:700;color:#79909d;text-transform:uppercase;letter-spacing:.04em;margin:0">{{ __('messages.failed') ?? 'Failed' }}</p>
                    <p style="font-size:28px;font-weight:800;color:var(--rose);margin:0;line-height:1.1">{{ $failedCount }}</p>
                </div>
            </div>
        </div>
        <div class="stat-card" style="border-top:3px solid var(--blue);padding:16px 18px">
            <div style="display:flex;align-items:center;gap:10px">
                <i class="fa-solid fa-satellite-dish" style="font-size:20px;color:var(--blue)"></i>
                <div>
                    <p style="font-size:11px;font-weight:700;color:#79909d;text-transform:uppercase;letter-spacing:.04em;margin:0">{{ __('messages.unique_ips') ?? 'Unique IPs' }}</p>
                    <p style="font-size:28px;font-weight:800;color:var(--blue);margin:0;line-height:1.1">{{ $uniqueIpCount }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" class="filter-bar">
        <label>{{ __('messages.user') }}
            <select name="user_id" onchange="this.form.submit()">
                <option value="">{{ __('messages.all') }}</option>
                @foreach($users as $u)
                <option value="{{ $u->id }}" {{ $user_id == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                @endforeach
            </select>
        </label>
        <label>{{ __('messages.status') }}
            <select name="success" onchange="this.form.submit()">
                <option value="">{{ __('messages.all') }}</option>
                <option value="1" {{ $success === '1' ? 'selected' : '' }}>{{ __('messages.success') }}</option>
                <option value="0" {{ $success === '0' ? 'selected' : '' }}>{{ __('messages.failed') ?? 'Failed' }}</option>
            </select>
        </label>
        <label>{{ __('messages.from') }}
            <input type="date" name="date_from" value="{{ $date_from }}">
        </label>
        <label>{{ __('messages.to') }}
            <input type="date" name="date_to" value="{{ $date_to }}">
        </label>
        <div style="display:flex;gap:8px;align-items:flex-end">
            <button type="submit" class="secondary-button"><i class="fa-solid fa-magnifying-glass"></i></button>
            <a href="{{ route('team-access.login-history') }}" class="secondary-button">{{ __('messages.reset') }}</a>
        </div>
    </form>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.date') }}</th>
                    <th>{{ __('messages.user') }}</th>
                    <th>{{ __('messages.status') }}</th>
                    <th>{{ __('messages.ip_address') }}</th>
                    <th>{{ __('messages.device') }}</th>
                    <th>{{ __('messages.location') }}</th>
                    <th>{{ __('messages.duration') ?? 'Duration' }}</th>
                </tr>
            </thead>
            @forelse ($logs as $log)
            @php
                $ua = $log->user_agent ?? '';
                // Detect browser icon
                $browserIcon = 'fa-globe';
                if (str_contains($ua, 'Edg')) $browserIcon = 'fa-edge';
                elseif (str_contains($ua, 'Chrome')) $browserIcon = 'fa-chrome';
                elseif (str_contains($ua, 'Firefox')) $browserIcon = 'fa-firefox';
                elseif (str_contains($ua, 'Safari')) $browserIcon = 'fa-safari';
                elseif (str_contains($ua, 'Opera')) $browserIcon = 'fa-opera';
                // Detect device icon
                $deviceIcon = 'fa-desktop';
                if (str_contains($ua, 'Mobile') || str_contains($ua, 'Android') || str_contains($ua, 'iPhone')) $deviceIcon = 'fa-mobile-screen-button';
                elseif (str_contains($ua, 'Tablet') || str_contains($ua, 'iPad')) $deviceIcon = 'fa-tablet-screen-button';
                // Detect OS label
                $os = 'Unknown OS';
                if (str_contains($ua, 'Windows')) $os = 'Windows';
                elseif (str_contains($ua, 'Mac OS') || str_contains($ua, 'Macintosh')) $os = 'macOS';
                elseif (str_contains($ua, 'Linux')) $os = 'Linux';
                elseif (str_contains($ua, 'Android')) $os = 'Android';
                elseif (str_contains($ua, 'iPhone') || str_contains($ua, 'iPad')) $os = 'iOS';
            @endphp
            <tbody class="row-group">
                <tr style="{{ !$log->is_successful ? 'background:var(--soft-rose)' : '' }}">
                    <td style="white-space:nowrap;font-size:13px">
                        {{ $log->created_at->format('d M Y') }}
                        <br><small style="opacity:.5">{{ $log->created_at->format('H:i:s') }}</small>
                    </td>
                    <td>
                        <span style="display:inline-flex;align-items:center;gap:6px;font-size:13px">
                            <i class="fa-solid fa-user-circle" style="color:#b0c4cc;font-size:15px"></i>
                            <span>
                                <strong style="display:block;line-height:1.2">{{ $log->user?->name ?? '—' }}</strong>
                                @if($log->email)<small style="opacity:.5;font-size:11px">{{ $log->email }}</small>@endif
                            </span>
                        </span>
                    </td>
                    <td>
                        @if($log->is_successful)
                            <span class="badge-status badge-active"><i class="fa-solid fa-check" style="font-size:9px"></i> {{ __('messages.success') }}</span>
                        @else
                            <span class="badge-status badge-inactive" style="background:var(--soft-rose);color:var(--rose)">
                                <i class="fa-solid fa-xmark" style="font-size:9px"></i> {{ __('messages.failed') ?? 'Failed' }}
                            </span>
                        @endif
                    </td>
                    <td>
                        @if($log->ip_address)
                        <code style="font-size:11px;background:#f1f5f9;padding:2px 6px;border-radius:4px;color:#496371">{{ $log->ip_address }}</code>
                        @else<span style="opacity:.35">—</span>@endif
                    </td>
                    <td>
                        <span style="display:inline-flex;align-items:center;gap:6px;font-size:13px;color:#496371">
                            <i class="fa-brands {{ $browserIcon }}" style="font-size:15px;color:var(--blue)"></i>
                            <i class="fa-solid {{ $deviceIcon }}" style="font-size:13px;color:#b0c4cc"></i>
                            <span>{{ $os }}</span>
                        </span>
                    </td>
                    <td style="font-size:13px;color:#79909d">
                        {{ $log->location ?? '—' }}
                    </td>
                    <td style="font-size:13px">
                        @if($log->session_duration)
                            <span style="font-weight:700">{{ gmdate('H:i:s', $log->session_duration) }}</span>
                        @else<span style="opacity:.35">—</span>@endif
                    </td>
                </tr>
            </tbody>
            @empty
            <tbody><tr><td colspan="7">
                <div style="text-align:center;padding:48px 24px;opacity:.5">
                    <i class="fa-solid fa-right-to-bracket" style="font-size:36px;display:block;margin-bottom:12px;opacity:.4;color:var(--teal)"></i>
                    <p style="font-weight:700;margin:0">{{ __('messages.no_data') }}</p>
                    <p style="font-size:13px;margin:4px 0 0;opacity:.7">No login events found for the selected filters.</p>
                </div>
            </td></tr></tbody>
            @endforelse
        </table>
    </div>

    {{ $logs->links() }}
</section>
@endsection
