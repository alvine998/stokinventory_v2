@extends('layouts.app', ['title' => __('messages.approval_requests'), 'heading' => __('messages.nav_team')])

@section('content')
@include('app.team-access._subnav')

<section class="panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">{{ __('messages.nav_team') }}</p>
            <h2><i class="fa-solid fa-inbox"></i> {{ __('messages.approval_requests') }}</h2>
        </div>
        @if($pendingCount > 0)
        <span style="display:inline-flex;align-items:center;gap:7px;background:var(--soft-amber);color:var(--amber);border-radius:999px;padding:7px 14px;font-size:13px;font-weight:700;border:1px solid #f3d08a">
            <i class="fa-solid fa-clock"></i> {{ $pendingCount }} {{ __('messages.pending') }}
        </span>
        @endif
    </div>

    {{-- Summary Cards --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:14px;margin-bottom:22px">
        <div class="stat-card" style="border-top:3px solid var(--amber);padding:16px 18px">
            <div style="display:flex;align-items:center;gap:10px">
                <i class="fa-solid fa-clock" style="font-size:20px;color:var(--amber)"></i>
                <div>
                    <p style="font-size:11px;font-weight:700;color:#79909d;text-transform:uppercase;letter-spacing:.04em;margin:0">{{ __('messages.pending') }}</p>
                    <p style="font-size:28px;font-weight:800;color:var(--amber);margin:0;line-height:1.1">{{ $pendingCount }}</p>
                </div>
            </div>
        </div>
        <div class="stat-card" style="border-top:3px solid var(--teal);padding:16px 18px">
            <div style="display:flex;align-items:center;gap:10px">
                <i class="fa-solid fa-circle-check" style="font-size:20px;color:var(--teal)"></i>
                <div>
                    <p style="font-size:11px;font-weight:700;color:#79909d;text-transform:uppercase;letter-spacing:.04em;margin:0">{{ __('messages.approved') }}</p>
                    <p style="font-size:28px;font-weight:800;color:var(--teal);margin:0;line-height:1.1">{{ $approvedCount }}</p>
                </div>
            </div>
        </div>
        <div class="stat-card" style="border-top:3px solid var(--rose);padding:16px 18px">
            <div style="display:flex;align-items:center;gap:10px">
                <i class="fa-solid fa-circle-xmark" style="font-size:20px;color:var(--rose)"></i>
                <div>
                    <p style="font-size:11px;font-weight:700;color:#79909d;text-transform:uppercase;letter-spacing:.04em;margin:0">{{ __('messages.rejected') }}</p>
                    <p style="font-size:28px;font-weight:800;color:var(--rose);margin:0;line-height:1.1">{{ $rejectedCount }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" class="filter-bar">
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
        <label>{{ __('messages.requester') }}
            <select name="requester_id" onchange="this.form.submit()">
                <option value="">{{ __('messages.all') }}</option>
                @foreach($users as $u)
                <option value="{{ $u->id }}" {{ ($requester_id ?? '') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                @endforeach
            </select>
        </label>
        <div style="display:flex;gap:8px;align-items:flex-end">
            <button type="submit" class="secondary-button"><i class="fa-solid fa-magnifying-glass"></i></button>
            <a href="{{ route('team-access.approval-requests') }}" class="secondary-button">{{ __('messages.reset') }}</a>
        </div>
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
            @php
                $isPending = $req->status === 'pending';
                $badgeClass = match($req->status) {
                    'approved'  => 'badge-active',
                    'rejected'  => 'badge-inactive',
                    default     => '',
                };
                $badgeStyle = $req->status === 'pending'
                    ? 'background:var(--soft-amber);color:var(--amber)'
                    : ($req->status === 'cancelled' ? 'background:#f1f5f9;color:#64748b' : '');
                $daysWaiting = $req->created_at->diffInDays(now());
            @endphp
            <tbody class="row-group">
                <tr style="{{ $isPending ? 'border-left:3px solid var(--amber)' : '' }}">
                    <td>
                        <strong>{{ $req->title }}</strong>
                        @if($req->reference_no)
                            <br><span class="slug-pill">{{ $req->reference_no }}</span>
                        @endif
                        @if($isPending && $daysWaiting >= 2)
                            <br><span style="font-size:11px;color:var(--amber);font-weight:700">
                                <i class="fa-solid fa-triangle-exclamation"></i> {{ $daysWaiting }}d waiting
                            </span>
                        @endif
                    </td>
                    <td>
                        <span style="display:inline-flex;align-items:center;gap:6px">
                            <i class="fa-solid fa-user-circle" style="color:#b0c4cc"></i>
                            {{ $req->requester?->name ?? '—' }}
                        </span>
                    </td>
                    <td style="font-size:13px;color:#79909d">{{ $req->workflow?->name ?? '—' }}</td>
                    <td class="num">
                        @if($req->amount)
                            <strong>Rp {{ number_format($req->amount,0,',','.') }}</strong>
                        @else<span style="opacity:.35">—</span>@endif
                    </td>
                    <td>
                        <span class="badge-status {{ $badgeClass }}" style="{{ $badgeStyle }}">
                            @if($req->status === 'pending')<i class="fa-solid fa-clock" style="font-size:9px"></i> @endif
                            @if($req->status === 'approved')<i class="fa-solid fa-check" style="font-size:9px"></i> @endif
                            @if($req->status === 'rejected')<i class="fa-solid fa-xmark" style="font-size:9px"></i> @endif
                            {{ ucfirst($req->status) }}
                        </span>
                    </td>
                    <td>
                        <span style="background:var(--soft-blue);color:var(--blue);border-radius:999px;padding:3px 10px;font-size:12px;font-weight:700">
                            {{ __('messages.step') }} {{ $req->current_step }}
                        </span>
                    </td>
                    <td style="white-space:nowrap;font-size:13px">
                        {{ $req->created_at->format('d M Y') }}
                        <br><small style="opacity:.5">{{ $req->created_at->diffForHumans() }}</small>
                    </td>
                    <td>
                        @if($isPending)
                        <div style="display:flex;gap:6px">
                            <button type="button" class="icon-button" style="color:var(--teal)" title="{{ __('messages.approve') }} / {{ __('messages.reject') }}"
                                onclick="const r=this.closest('tbody').querySelector('.edit-row');r.hidden=!r.hidden">
                                <i class="fa-solid fa-circle-check"></i>
                            </button>
                            <form method="POST" action="{{ route('team-access.approval-requests.cancel', $req) }}" onsubmit="return confirm('{{ __('messages.confirm_delete') }}')">
                                @csrf
                                <button class="icon-button" style="color:var(--rose)" title="{{ __('messages.cancel') }}"><i class="fa-solid fa-ban"></i></button>
                            </form>
                        </div>
                        @else
                        <button type="button" class="icon-button" title="{{ __('messages.view') }}"
                            onclick="const r=this.closest('tbody').querySelector('.edit-row');r.hidden=!r.hidden">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                        @endif
                    </td>
                </tr>
                <tr class="edit-row" hidden>
                    <td colspan="8" style="padding:0;border-top:2px solid {{ $isPending ? 'var(--amber)' : 'var(--blue)' }}">
                        <div style="padding:20px 24px;background:{{ $isPending ? 'var(--soft-amber)' : 'var(--soft-blue)' }}">
                            @if($isPending)
                            <p style="font-size:12px;font-weight:700;color:var(--amber);text-transform:uppercase;letter-spacing:.05em;margin-bottom:14px">
                                <i class="fa-solid fa-gavel"></i> {{ __('messages.approve') }} / {{ __('messages.reject') }}
                            </p>
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">
                                <form method="POST" action="{{ route('team-access.approval-requests.approve', $req) }}"
                                      style="background:white;border:1px solid #a7f0e4;border-radius:10px;padding:16px;display:flex;flex-direction:column;gap:10px">
                                    @csrf
                                    <div style="display:flex;align-items:center;gap:8px;color:var(--teal);font-weight:700">
                                        <i class="fa-solid fa-circle-check" style="font-size:18px"></i> {{ __('messages.approve') }}
                                    </div>
                                    <label style="font-size:13px">{{ __('messages.notes') }}<textarea name="notes" rows="2" placeholder="{{ __('messages.optional') ?? 'Optional…' }}"></textarea></label>
                                    <button class="primary-button" type="submit" style="background:var(--teal);border-color:var(--teal)">
                                        <i class="fa-solid fa-check"></i> {{ __('messages.approve') }}
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('team-access.approval-requests.reject', $req) }}"
                                      style="background:white;border:1px solid #ffd0d0;border-radius:10px;padding:16px;display:flex;flex-direction:column;gap:10px">
                                    @csrf
                                    <div style="display:flex;align-items:center;gap:8px;color:var(--rose);font-weight:700">
                                        <i class="fa-solid fa-circle-xmark" style="font-size:18px"></i> {{ __('messages.reject') }}
                                    </div>
                                    <label style="font-size:13px">{{ __('messages.notes') }}<textarea name="notes" rows="2" placeholder="{{ __('messages.required') ?? 'Reason required…' }}" required></textarea></label>
                                    <button class="primary-button" type="submit" style="background:var(--rose);border-color:var(--rose)">
                                        <i class="fa-solid fa-xmark"></i> {{ __('messages.reject') }}
                                    </button>
                                </form>
                            </div>
                            @endif

                            @if($req->actions->isNotEmpty())
                            <div style="{{ $isPending ? 'border-top:1px solid #f3d08a;padding-top:14px' : '' }}">
                                <p style="font-size:12px;font-weight:700;color:#79909d;text-transform:uppercase;letter-spacing:.05em;margin-bottom:10px">
                                    <i class="fa-solid fa-clock-rotate-left"></i> {{ __('messages.approval_history') }}
                                </p>
                                <div style="display:flex;flex-direction:column;gap:8px">
                                @foreach($req->actions as $act)
                                <div style="display:flex;align-items:flex-start;gap:12px;background:white;border-radius:8px;padding:10px 14px;border:1px solid #e5eaef">
                                    <div style="width:32px;height:32px;border-radius:999px;display:grid;place-items:center;font-size:13px;font-weight:800;flex-shrink:0;
                                        background:{{ $act->action === 'approved' ? 'var(--soft-teal)' : ($act->action === 'rejected' ? 'var(--soft-rose)' : '#f1f5f9') }};
                                        color:{{ $act->action === 'approved' ? 'var(--teal)' : ($act->action === 'rejected' ? 'var(--rose)' : '#64748b') }}">
                                        {{ $act->step }}
                                    </div>
                                    <div style="flex:1;min-width:0">
                                        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                                            <strong style="font-size:13px">{{ $act->actor?->name ?? '—' }}</strong>
                                            <span class="badge-status {{ $act->action === 'approved' ? 'badge-active' : ($act->action === 'rejected' ? 'badge-inactive' : '') }}"
                                                  style="{{ $act->action === 'pending' ? 'background:var(--soft-amber);color:var(--amber)' : '' }}">
                                                {{ ucfirst($act->action) }}
                                            </span>
                                            <span style="font-size:11px;color:#79909d;margin-left:auto">{{ $act->acted_at->format('d M Y H:i') }}</span>
                                        </div>
                                        @if($act->notes)
                                        <p style="font-size:12px;color:#607381;margin:4px 0 0;font-style:italic">"{{ $act->notes }}"</p>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                                </div>
                            </div>
                            @elseif(!$isPending)
                            <p style="opacity:.5;font-size:13px;padding:8px 0">{{ __('messages.no_data') }}</p>
                            @endif
                        </div>
                    </td>
                </tr>
            </tbody>
            @empty
            <tbody><tr><td colspan="8">
                <div style="text-align:center;padding:48px 24px;opacity:.5">
                    <i class="fa-solid fa-inbox" style="font-size:36px;display:block;margin-bottom:12px;opacity:.4"></i>
                    <p style="font-weight:700;margin:0">{{ __('messages.no_data') }}</p>
                </div>
            </td></tr></tbody>
            @endforelse
        </table>
    </div>

    {{ $requests->links() }}
</section>
@endsection
