@extends('layouts.app', ['title' => __('messages.login_history'), 'heading' => __('messages.nav_team')])

@section('content')
@include('app.team-access._subnav')

<section class="panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">{{ __('messages.nav_team') }}</p>
            <h2><i class="fa-solid fa-right-to-bracket"></i> {{ __('messages.login_history') }}</h2>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="stat-grid" style="margin-bottom:20px">
        <div class="stat-card" style="border-top:3px solid var(--teal)">
            <p class="stat-label">{{ __('messages.total_logins') }}</p>
            <p class="stat-value" style="color:var(--teal)">{{ number_format($totalLogins) }}</p>
        </div>
        <div class="stat-card" style="border-top:3px solid var(--rose)">
            <p class="stat-label">{{ __('messages.failed_logins') }}</p>
            <p class="stat-value" style="color:var(--rose)">{{ number_format($failedLogins) }}</p>
        </div>
        <div class="stat-card" style="border-top:3px solid var(--blue)">
            <p class="stat-label">{{ __('messages.active_sessions') }}</p>
            <p class="stat-value" style="color:var(--blue)">{{ number_format($activeNow) }}</p>
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
        <label>{{ __('messages.result') }}
            <select name="success" onchange="this.form.submit()">
                <option value="">{{ __('messages.all') }}</option>
                <option value="1" {{ $success === '1' ? 'selected' : '' }}>{{ __('messages.successful') }}</option>
                <option value="0" {{ $success === '0' ? 'selected' : '' }}>{{ __('messages.failed') }}</option>
            </select>
        </label>
        <label>{{ __('messages.from') }}
            <input type="date" name="date_from" value="{{ $date_from }}">
        </label>
        <label>{{ __('messages.to') }}
            <input type="date" name="date_to" value="{{ $date_to }}">
        </label>
        <button type="submit" class="secondary-button"><i class="fa-solid fa-magnifying-glass"></i></button>
        <a href="{{ route('team-access.login-history') }}" class="secondary-button">{{ __('messages.reset') }}</a>
    </form>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.user') }}</th>
                    <th>{{ __('messages.result') }}</th>
                    <th>{{ __('messages.login_at') }}</th>
                    <th>{{ __('messages.logout_at') }}</th>
                    <th>{{ __('messages.duration') }}</th>
                    <th>{{ __('messages.ip_address') }}</th>
                    <th>{{ __('messages.device') }}</th>
                </tr>
            </thead>
            @forelse ($histories as $h)
            <tbody class="row-group">
                <tr style="{{ !$h->is_successful ? 'background:rgba(var(--rose-rgb),.05)' : '' }}">
                    <td><strong>{{ $h->user?->name ?? '—' }}</strong><br><small style="opacity:.6">{{ $h->user?->email }}</small></td>
                    <td>
                        @if($h->is_successful)
                            <span class="badge-status badge-active">{{ __('messages.successful') }}</span>
                        @else
                            <span class="badge-status badge-inactive">{{ __('messages.failed') }}</span>
                        @endif
                    </td>
                    <td style="white-space:nowrap">{{ $h->login_at->format('d M Y H:i') }}</td>
                    <td style="white-space:nowrap">{{ $h->logout_at?->format('d M Y H:i') ?? '—' }}</td>
                    <td>{{ $h->duration ?? '—' }}</td>
                    <td><code style="font-size:.75rem">{{ $h->ip_address ?? '—' }}</code></td>
                    <td>
                        @if($h->user_agent)
                        <button type="button" class="icon-button" onclick="const r=this.closest('tbody').querySelector('.edit-row');r.hidden=!r.hidden">
                            <i class="fa-solid fa-laptop"></i>
                        </button>
                        @else —
                        @endif
                    </td>
                </tr>
                @if($h->user_agent)
                <tr class="edit-row" hidden>
                    <td colspan="7" style="background:var(--surface-2);padding:12px 16px">
                        <p style="font-size:.8rem;word-break:break-all;opacity:.7">{{ $h->user_agent }}</p>
                    </td>
                </tr>
                @endif
            </tbody>
            @empty
            <tbody><tr><td colspan="7" style="text-align:center;padding:32px;opacity:.5">{{ __('messages.no_data') }}</td></tr></tbody>
            @endforelse
        </table>
    </div>

    {{ $histories->links() }}
</section>
@endsection
