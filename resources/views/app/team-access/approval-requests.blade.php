@extends('layouts.app', ['title' => __('messages.approval_requests'), 'heading' => __('messages.nav_team')])

@section('content')
@include('app.team-access._subnav')

<section class="panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">{{ __('messages.nav_team') }}</p>
            <h2><i class="fa-solid fa-inbox"></i> {{ __('messages.approval_requests') }}</h2>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="stat-grid" style="margin-bottom:20px">
        <div class="stat-card" style="border-top:3px solid var(--amber)">
            <p class="stat-label">{{ __('messages.pending') }}</p>
            <p class="stat-value" style="color:var(--amber)">{{ $pendingCount }}</p>
        </div>
        <div class="stat-card" style="border-top:3px solid var(--teal)">
            <p class="stat-label">{{ __('messages.approved') }}</p>
            <p class="stat-value" style="color:var(--teal)">{{ $approvedCount }}</p>
        </div>
        <div class="stat-card" style="border-top:3px solid var(--rose)">
            <p class="stat-label">{{ __('messages.rejected') }}</p>
            <p class="stat-value" style="color:var(--rose)">{{ $rejectedCount }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px;align-items:flex-end">
        <label>{{ __('messages.status') }}
            <select name="status" onchange="this.form.submit()">
                <option value="">{{ __('messages.all') }}</option>
                @foreach(['pending','approved','rejected','cancelled'] as $s)
                <option value="{{ $s }}" {{ $status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </label>
        <label>{{ __('messages.module') }}
            <select name="module" onchange="this.form.submit()">
                <option value="">{{ __('messages.all') }}</option>
                @foreach(\App\Models\ApprovalWorkflow::modules() as $mod)
                <option value="{{ $mod }}" {{ $module === $mod ? 'selected' : '' }}>{{ ucfirst($mod) }}</option>
                @endforeach
            </select>
        </label>
    </form>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.title') }}</th>
                    <th>{{ __('messages.requester') }}</th>
                    <th>{{ __('messages.workflow') }}</th>
                    <th class="num">{{ __('messages.amount') }}</th>
                    <th>{{ __('messages.status') }}</th>
                    <th>{{ __('messages.step') }}</th>
                    <th>{{ __('messages.date') }}</th>
                    <th>{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            @forelse ($requests as $req)
            <tbody class="row-group">
                <tr>
                    <td>
                        <strong>{{ $req->title }}</strong>
                        @if($req->reference_no)<br><small style="opacity:.6">{{ $req->reference_no }}</small>@endif
                    </td>
                    <td>{{ $req->requester?->name ?? '—' }}</td>
                    <td>{{ $req->workflow?->name ?? '—' }}</td>
                    <td class="num">{{ $req->amount ? 'Rp '.number_format($req->amount,0,',','.') : '—' }}</td>
                    <td>
                        @php
                            $badgeClass = match($req->status) {
                                'approved'  => 'badge-active',
                                'rejected'  => 'badge-inactive',
                                'pending'   => 'badge-pending',
                                default     => '',
                            };
                        @endphp
                        <span class="badge-status {{ $badgeClass }}">{{ ucfirst($req->status) }}</span>
                    </td>
                    <td>{{ $req->current_step }}</td>
                    <td>{{ $req->created_at->format('d M Y') }}</td>
                    <td>
                        @if($req->status === 'pending')
                        <div style="display:flex;gap:6px">
                            <button type="button" class="icon-button" style="color:var(--teal)"
                                onclick="const r=this.closest('tbody').querySelector('.edit-row');r.hidden=!r.hidden"
                                title="{{ __('messages.approve') }} / {{ __('messages.reject') }}">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <form method="POST" action="{{ route('team-access.approval-requests.cancel', $req) }}" onsubmit="return confirm('{{ __('messages.confirm_delete') }}')">
                                @csrf
                                <button class="icon-button" style="color:var(--rose)" title="{{ __('messages.cancel') }}"><i class="fa-solid fa-ban"></i></button>
                            </form>
                        </div>
                        @else
                        <button type="button" class="icon-button" onclick="const r=this.closest('tbody').querySelector('.edit-row');r.hidden=!r.hidden">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                        @endif
                    </td>
                </tr>
                <tr class="edit-row" hidden>
                    <td colspan="8" style="background:var(--surface-2);padding:16px">
                        @if($req->status === 'pending')
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                            <form method="POST" action="{{ route('team-access.approval-requests.approve', $req) }}" style="display:flex;flex-direction:column;gap:8px">
                                @csrf
                                <label>{{ __('messages.notes') }}<textarea name="notes" rows="2"></textarea></label>
                                <button class="primary-button" type="submit" style="background:var(--teal)"><i class="fa-solid fa-check"></i> {{ __('messages.approve') }}</button>
                            </form>
                            <form method="POST" action="{{ route('team-access.approval-requests.reject', $req) }}" style="display:flex;flex-direction:column;gap:8px">
                                @csrf
                                <label>{{ __('messages.notes') }}<textarea name="notes" rows="2"></textarea></label>
                                <button class="primary-button" type="submit" style="background:var(--rose)"><i class="fa-solid fa-xmark"></i> {{ __('messages.reject') }}</button>
                            </form>
                        </div>
                        @endif
                        @if($req->actions->isNotEmpty())
                        <h4 style="margin:12px 0 6px">{{ __('messages.approval_history') }}</h4>
                        <table style="font-size:.85rem">
                            <thead><tr><th>{{ __('messages.step') }}</th><th>{{ __('messages.actor') }}</th><th>{{ __('messages.action') }}</th><th>{{ __('messages.notes') }}</th><th>{{ __('messages.date') }}</th></tr></thead>
                            <tbody>
                            @foreach($req->actions as $act)
                            <tr>
                                <td>{{ $act->step }}</td>
                                <td>{{ $act->actor?->name ?? '—' }}</td>
                                <td><span class="badge-status {{ $act->action === 'approved' ? 'badge-active' : ($act->action === 'rejected' ? 'badge-inactive' : '') }}">{{ ucfirst($act->action) }}</span></td>
                                <td>{{ $act->notes ?? '—' }}</td>
                                <td>{{ $act->acted_at->format('d M Y H:i') }}</td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                        @endif
                    </td>
                </tr>
            </tbody>
            @empty
            <tbody><tr><td colspan="8" style="text-align:center;padding:32px;opacity:.5">{{ __('messages.no_data') }}</td></tr></tbody>
            @endforelse
        </table>
    </div>

    {{ $requests->links() }}
</section>
@endsection
