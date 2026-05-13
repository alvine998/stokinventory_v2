@extends('layouts.app', ['title' => __('messages.approval_workflow'), 'heading' => __('messages.nav_team')])

@section('content')
@include('app.team-access._subnav')

<section class="panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">{{ __('messages.nav_team') }}</p>
            <h2><i class="fa-solid fa-code-branch"></i> {{ __('messages.approval_workflow') }}</h2>
        </div>
        <a href="#modal-add-workflow" class="primary-button"><i class="fa-solid fa-plus"></i> {{ __('messages.new_workflow') }}</a>
    </div>

    @include('partials.errors')

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.name') }}</th>
                    <th>{{ __('messages.module') }}</th>
                    <th>{{ __('messages.approvers') }}</th>
                    <th>{{ __('messages.min_amount') }}</th>
                    <th>{{ __('messages.status') }}</th>
                    <th class="num">{{ __('messages.requests') }}</th>
                    <th>{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            @forelse ($workflows as $wf)
            <tbody class="row-group">
                <tr>
                    <td><strong>{{ $wf->name }}</strong>
                        @if($wf->description)<br><small style="color:var(--ink);opacity:.6">{{ $wf->description }}</small>@endif
                    </td>
                    <td><span class="badge-status">{{ strtoupper($wf->module) }}</span></td>
                    <td>
                        @foreach($wf->approver_ids as $uid)
                            @php $u = $users->firstWhere('id', $uid) @endphp
                            <span class="badge-status" style="margin:1px">{{ $u?->name ?? 'User #'.$uid }}</span>
                        @endforeach
                    </td>
                    <td>{{ $wf->min_amount ? 'Rp '.number_format($wf->min_amount,0,',','.') : '—' }}</td>
                    <td>
                        @if($wf->is_active)
                            <span class="badge-status badge-active">{{ __('messages.active') }}</span>
                        @else
                            <span class="badge-status badge-inactive">{{ __('messages.inactive') }}</span>
                        @endif
                    </td>
                    <td class="num">{{ $wf->requests_count }}</td>
                    <td>
                        <div style="display:flex;gap:6px">
                            <button type="button" class="icon-button" onclick="const r=this.closest('tbody').querySelector('.edit-row');r.hidden=!r.hidden"><i class="fa-solid fa-pen"></i></button>
                            <form method="POST" action="{{ route('team-access.approval-workflows.destroy', $wf) }}" onsubmit="return confirm('{{ __('messages.confirm_delete') }}')">
                                @csrf @method('DELETE')
                                <button class="icon-button" style="color:var(--rose)"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                <tr class="edit-row" hidden>
                    <td colspan="7" style="background:var(--surface-2)">
                        <form method="POST" action="{{ route('team-access.approval-workflows.update', $wf) }}" style="padding:16px;display:flex;flex-direction:column;gap:12px">
                            @csrf @method('PUT')
                            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px">
                                <label>{{ __('messages.name') }}
                                    <input type="text" name="name" value="{{ $wf->name }}" required>
                                </label>
                                <label>{{ __('messages.module') }}
                                    <select name="module">
                                        @foreach(\App\Models\ApprovalWorkflow::modules() as $mod)
                                        <option value="{{ $mod }}" {{ $wf->module === $mod ? 'selected' : '' }}>{{ ucfirst($mod) }}</option>
                                        @endforeach
                                    </select>
                                </label>
                                <label>{{ __('messages.min_amount') }}
                                    <input type="number" name="min_amount" value="{{ $wf->min_amount }}" min="0" step="0.01" placeholder="0">
                                </label>
                            </div>
                            <label>{{ __('messages.approvers') }}
                                <select name="approver_ids[]" multiple style="height:100px">
                                    @foreach($users as $u)
                                    <option value="{{ $u->id }}" {{ in_array($u->id, $wf->approver_ids) ? 'selected' : '' }}>{{ $u->name }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label>{{ __('messages.description') }}
                                <textarea name="description" rows="2">{{ $wf->description }}</textarea>
                            </label>
                            <label style="display:flex;align-items:center;gap:8px">
                                <input type="checkbox" name="is_active" value="1" {{ $wf->is_active ? 'checked' : '' }}> {{ __('messages.active') }}
                            </label>
                            <div><button class="primary-button" type="submit">{{ __('messages.save') }}</button></div>
                        </form>
                    </td>
                </tr>
            </tbody>
            @empty
            <tbody><tr><td colspan="7" style="text-align:center;padding:32px;opacity:.5">{{ __('messages.no_data') }}</td></tr></tbody>
            @endforelse
        </table>
    </div>
</section>

{{-- Add Workflow Modal --}}
<div class="modal-backdrop" id="modal-add-workflow">
    <div class="modal">
        <div class="modal-header">
            <h3>{{ __('messages.new_workflow') }}</h3>
            <a href="#" class="modal-close"><i class="fa-solid fa-xmark"></i></a>
        </div>
        <form method="POST" action="{{ route('team-access.approval-workflows.store') }}" style="display:flex;flex-direction:column;gap:14px">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <label>{{ __('messages.name') }} *
                    <input type="text" name="name" required>
                </label>
                <label>{{ __('messages.module') }} *
                    <select name="module" required>
                        @foreach(\App\Models\ApprovalWorkflow::modules() as $mod)
                        <option value="{{ $mod }}">{{ ucfirst($mod) }}</option>
                        @endforeach
                    </select>
                </label>
                <label>{{ __('messages.min_amount') }}
                    <input type="number" name="min_amount" min="0" step="0.01" placeholder="0">
                </label>
                <label>{{ __('messages.trigger_event') }}
                    <input type="text" name="trigger_event" placeholder="e.g. purchase_order.submit">
                </label>
            </div>
            <label>{{ __('messages.approvers') }} *
                <small style="opacity:.6">{{ __('messages.approvers_help') }}</small>
                <select name="approver_ids[]" multiple required style="height:120px;margin-top:4px">
                    @foreach($users as $u)
                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                    @endforeach
                </select>
            </label>
            <label>{{ __('messages.description') }}
                <textarea name="description" rows="2"></textarea>
            </label>
            <label style="display:flex;align-items:center;gap:8px">
                <input type="checkbox" name="is_active" value="1" checked> {{ __('messages.active') }}
            </label>
            <div style="display:flex;gap:8px;justify-content:flex-end">
                <a href="#" class="secondary-button">{{ __('messages.cancel') }}</a>
                <button type="submit" class="primary-button">{{ __('messages.save') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
