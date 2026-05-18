@extends('layouts.app', ['title' => __('messages.approval_workflow'), 'heading' => __('messages.nav_team')])

@section('content')
@include('app.team-access._subnav')

<section class="panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">{{ __('messages.nav_team') }}</p>
            <h2><i class="fa-solid fa-code-branch"></i> {{ __('messages.approval_workflow') }}</h2>
        </div>
        <div style="display:flex;align-items:center;gap:10px">
            <span style="font-size:13px;color:#79909d;font-weight:600">{{ $workflows->count() }} {{ __('messages.approval_workflow') }}</span>
            <a href="#modal-add-workflow" class="primary-button"><i class="fa-solid fa-plus"></i> {{ __('messages.new_workflow') }}</a>
        </div>
    </div>

    @include('partials.errors')

    @if($workflows->isEmpty())
    <div style="text-align:center;padding:64px 24px">
        <i class="fa-solid fa-code-branch" style="font-size:40px;margin-bottom:14px;display:block;color:var(--teal);opacity:.35"></i>
        <p style="font-weight:700;font-size:16px;margin-bottom:4px;opacity:.6">{{ __('messages.no_data') }}</p>
        <p style="font-size:13px;opacity:.45">Create your first approval workflow to get started.</p>
        <a href="#modal-add-workflow" class="primary-button" style="margin-top:16px;display:inline-flex"><i class="fa-solid fa-plus"></i> {{ __('messages.new_workflow') }}</a>
    </div>
    @else
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
            @foreach ($workflows as $wf)
            <tbody class="row-group">
                <tr>
                    <td>
                        <strong>{{ $wf->name }}</strong>
                        @if($wf->description)<br><small style="color:#79909d;font-size:12px">{{ $wf->description }}</small>@endif
                        @if($wf->trigger_event)<br><span class="slug-pill" style="margin-top:3px;display:inline-block">{{ $wf->trigger_event }}</span>@endif
                    </td>
                    <td>
                        <span class="badge-status" style="background:var(--soft-blue);color:var(--blue);font-weight:700;letter-spacing:.03em">{{ strtoupper($wf->module) }}</span>
                    </td>
                    <td>
                        <div style="display:flex;flex-wrap:wrap;gap:4px;max-width:220px">
                            @foreach($wf->approver_ids as $uid)
                                @php $u = $users->firstWhere('id', $uid) @endphp
                                <span style="display:inline-flex;align-items:center;gap:4px;background:var(--soft-teal);color:var(--teal);border-radius:999px;padding:3px 9px;font-size:11px;font-weight:700"><i class="fa-solid fa-user" style="font-size:9px"></i>{{ $u?->name ?? 'User #'.$uid }}</span>
                            @endforeach
                        </div>
                    </td>
                    <td>
                        @if($wf->min_amount)
                            <span style="font-weight:700;color:var(--amber)">Rp {{ number_format($wf->min_amount,0,',','.') }}</span>
                        @else<span style="opacity:.35">—</span>@endif
                    </td>
                    <td>
                        @if($wf->is_active)
                            <span class="badge-status badge-active"><i class="fa-solid fa-circle" style="font-size:7px"></i> {{ __('messages.active') }}</span>
                        @else
                            <span class="badge-status badge-inactive"><i class="fa-regular fa-circle" style="font-size:7px"></i> {{ __('messages.inactive') }}</span>
                        @endif
                    </td>
                    <td class="num">
                        @if($wf->requests_count > 0)
                            <span style="font-weight:700;color:var(--blue)">{{ $wf->requests_count }}</span>
                        @else<span style="opacity:.35">0</span>@endif
                    </td>
                    <td>
                        <div style="display:flex;gap:6px">
                            <button type="button" class="icon-button" title="{{ __('messages.edit') }}"
                                onclick="const r=this.closest('tbody').querySelector('.edit-row');r.hidden=!r.hidden">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <form method="POST" action="{{ route('team-access.approval-workflows.destroy', $wf) }}" onsubmit="return confirm('{{ __('messages.confirm_delete') }}')">
                                @csrf @method('DELETE')
                                <button class="icon-button" style="color:var(--rose)" title="{{ __('messages.delete') }}"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                <tr class="edit-row" hidden>
                    <td colspan="7" style="padding:0;border-top:2px solid var(--blue)">
                        <form method="POST" action="{{ route('team-access.approval-workflows.update', $wf) }}"
                              style="padding:20px 24px;background:var(--soft-blue);display:flex;flex-direction:column;gap:14px">
                            @csrf @method('PUT')
                            <p style="font-size:12px;font-weight:700;color:var(--blue);text-transform:uppercase;letter-spacing:.05em">
                                <i class="fa-solid fa-pen-to-square"></i> {{ __('messages.edit') }}: {{ $wf->name }}
                            </p>
                            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px">
                                <label>{{ __('messages.name') }}<input type="text" name="name" value="{{ $wf->name }}" placeholder="{{ __('messages.name') }}" required></label>
                                <label>{{ __('messages.module') }}
                                    <select name="module">
                                        @foreach(\App\Models\ApprovalWorkflow::modules() as $mod)
                                        <option value="{{ $mod }}" {{ $wf->module === $mod ? 'selected' : '' }}>{{ ucfirst($mod) }}</option>
                                        @endforeach
                                    </select>
                                </label>
                                <label>{{ __('messages.min_amount') }}<input type="number" name="min_amount" value="{{ $wf->min_amount }}" min="0" step="0.01" placeholder="0"></label>
                            </div>
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                                <label>{{ __('messages.approvers') }}
                                    <small style="color:var(--blue);opacity:.7;display:block;margin-bottom:4px">Hold Ctrl / Cmd to multi-select</small>
                                    <select name="approver_ids[]" multiple style="height:100px">
                                        @foreach($users as $u)
                                        <option value="{{ $u->id }}" {{ in_array($u->id, $wf->approver_ids) ? 'selected' : '' }}>{{ $u->name }}</option>
                                        @endforeach
                                    </select>
                                </label>
                                <div style="display:flex;flex-direction:column;gap:10px">
                                    <label>{{ __('messages.description') }}<textarea name="description" rows="3">{{ $wf->description }}</textarea></label>
                                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                                        <input type="checkbox" name="is_active" value="1" {{ $wf->is_active ? 'checked' : '' }}>
                                        <span>{{ __('messages.active') }}</span>
                                    </label>
                                </div>
                            </div>
                            <div><button class="primary-button" type="submit"><i class="fa-solid fa-floppy-disk"></i> {{ __('messages.save_changes') }}</button></div>
                        </form>
                    </td>
                </tr>
            </tbody>
            @endforeach
        </table>
    </div>
    @endif
</section>

{{-- Add Workflow Modal --}}
<div class="modal-overlay" id="modal-add-workflow">
    <div class="modal" style="max-width:560px">
        <div class="modal-header">
            <h3><i class="fa-solid fa-code-branch" style="color:var(--teal)"></i> {{ __('messages.new_workflow') }}</h3>
            <a href="#" class="modal-close"><i class="fa-solid fa-xmark"></i></a>
        </div>
        <form method="POST" action="{{ route('team-access.approval-workflows.store') }}" style="display:flex;flex-direction:column;gap:14px">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <label>{{ __('messages.name') }} *<input type="text" name="name" required placeholder="e.g. PO Approval"></label>
                <label>{{ __('messages.module') }} *
                    <select name="module" required>
                        @foreach(\App\Models\ApprovalWorkflow::modules() as $mod)
                        <option value="{{ $mod }}">{{ ucfirst($mod) }}</option>
                        @endforeach
                    </select>
                </label>
                <label>{{ __('messages.min_amount') }}<input type="number" name="min_amount" min="0" step="0.01" placeholder="0 = always trigger"></label>
                <label>{{ __('messages.trigger_event') }}<input type="text" name="trigger_event" placeholder="e.g. purchase_order.submit"></label>
            </div>
            <label>{{ __('messages.approvers') }} *
                <small style="opacity:.6;display:block;margin-bottom:4px">Hold Ctrl / Cmd to select multiple approvers</small>
                <select name="approver_ids[]" multiple required style="height:120px">
                    @foreach($users as $u)
                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                    @endforeach
                </select>
            </label>
            <label>{{ __('messages.description') }}<textarea name="description" rows="2" placeholder="Optional notes…"></textarea></label>
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                <input type="checkbox" name="is_active" value="1" checked> <span>{{ __('messages.active') }}</span>
            </label>
            <div style="display:flex;gap:8px;justify-content:flex-end;padding-top:6px;border-top:1px solid #d1dde4">
                <a href="#" class="secondary-button">{{ __('messages.cancel') }}</a>
                <button type="submit" class="primary-button"><i class="fa-solid fa-floppy-disk"></i> {{ __('messages.save') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
