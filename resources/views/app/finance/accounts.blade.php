@extends('layouts.app', ['title' => __('messages.chart_of_accounts'), 'heading' => __('messages.nav_finance')])

@section('content')
@include('app.finance._subnav')

<section class="panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">{{ __('messages.nav_finance') }}</p>
            <h2><i class="fa-solid fa-sitemap"></i> {{ __('messages.chart_of_accounts') }}</h2>
        </div>
        <div class="head-actions">
            <div class="btn-group">
                <a href="{{ route('finance.accounts.export') }}" class="secondary-button"><i class="fa-solid fa-file-excel"></i> {{ __('messages.export') }}</a>
                <a href="#modal-import-accounts" class="secondary-button"><i class="fa-solid fa-file-import"></i> {{ __('messages.import') }}</a>
            </div>
            <a href="#modal-add-account" class="primary-button"><i class="fa-solid fa-plus"></i> {{ __('messages.new_account') }}</a>
        </div>
    </div>

    @include('partials.errors')

    @foreach (['asset','liability','equity','revenue','cogs','expense'] as $type)
    @php $group = $accounts->where('type', $type); @endphp
    @if($group->count())
    <div style="margin-bottom:24px">
        <p class="label-cap" style="margin-bottom:8px;color:var(--teal)">{{ __('messages.account_type_'.$type) }}</p>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('messages.code') }}</th>
                        <th>{{ __('messages.name') }}</th>
                        <th>{{ __('messages.parent') }}</th>
                        <th>{{ __('messages.is_active') }}</th>
                        <th>{{ __('messages.actions') }}</th>
                    </tr>
                </thead>
                @foreach ($group->sortBy('code') as $account)
                <tbody class="row-group">
                    <tr>
                        <td><code>{{ $account->code }}</code></td>
                        <td>
                            {{ $account->name }}
                            @if($account->is_system) <span class="badge-status" style="font-size:10px;margin-left:4px">system</span> @endif
                        </td>
                        <td>{{ $account->parent?->name ?? '—' }}</td>
                        <td>
                            @if($account->is_active)
                                <span class="badge-status badge-active">{{ __('messages.active') }}</span>
                            @else
                                <span class="badge-status badge-inactive">{{ __('messages.inactive') }}</span>
                            @endif
                        </td>
                        <td>
                            <div style="display:flex;gap:6px">
                                <button type="button" class="icon-button" onclick="const r=this.closest('tbody').querySelector('.edit-row');r.hidden=!r.hidden"><i class="fa-solid fa-pen"></i></button>
                                @if(!$account->is_system)
                                <form method="POST" action="{{ route('finance.accounts.destroy', $account) }}" onsubmit="return confirm('{{ __('messages.confirm_delete') }}')">
                                    @csrf @method('DELETE')
                                    <button class="icon-button" style="color:var(--rose)"><i class="fa-solid fa-trash"></i></button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    <tr class="edit-row" hidden>
                        <td colspan="5" style="padding:0">
                            <form method="POST" action="{{ route('finance.accounts.update', $account) }}" style="padding:14px 16px;background:#f6fafc;border-top:2px solid #e3ecef">
                                @csrf @method('PATCH')
                                <div class="form-grid two">
                                    <label><span class="label-cap">{{ __('messages.name') }} <span class="req">*</span></span>
                                        <input type="text" name="name" value="{{ $account->name }}" required>
                                    </label>
                                    <label><span class="label-cap">{{ __('messages.is_active') }}</span>
                                        <select name="is_active">
                                            <option value="1" {{ $account->is_active ? 'selected' : '' }}>{{ __('messages.active') }}</option>
                                            <option value="0" {{ !$account->is_active ? 'selected' : '' }}>{{ __('messages.inactive') }}</option>
                                        </select>
                                    </label>
                                </div>
                                <div style="margin-top:10px;display:flex;gap:8px">
                                    <button class="primary-button">{{ __('messages.save') }}</button>
                                    <button type="button" class="secondary-button" onclick="this.closest('tbody').querySelector('.edit-row').hidden=true">{{ __('messages.cancel') }}</button>
                                </div>
                            </form>
                        </td>
                    </tr>
                </tbody>
                @endforeach
            </table>
        </div>
    </div>
    @endif
    @endforeach
</section>

<div class="modal-overlay" id="modal-add-account">
    <div class="modal" style="max-width:480px">
        <div class="modal-head">
            <h3>{{ __('messages.new_account') }}</h3>
            <a href="#" class="modal-close"><i class="fa-solid fa-xmark"></i></a>
        </div>
        <form method="POST" action="{{ route('finance.accounts.store') }}" class="modal-body">
            @csrf
            <div class="form-grid two">
                <label><span class="label-cap">{{ __('messages.code') }} <span class="req">*</span></span>
                    <input type="text" name="code" maxlength="20" required>
                </label>
                <label><span class="label-cap">{{ __('messages.type') }} <span class="req">*</span></span>
                    <select name="type" required>
                        @foreach (['asset','liability','equity','revenue','cogs','expense'] as $t)
                            <option value="{{ $t }}">{{ __('messages.account_type_'.$t) }}</option>
                        @endforeach
                    </select>
                </label>
                <label style="grid-column:span 2"><span class="label-cap">{{ __('messages.name') }} <span class="req">*</span></span>
                    <input type="text" name="name" required>
                </label>
                <label style="grid-column:span 2"><span class="label-cap">{{ __('messages.parent_account') }}</span>
                    <select name="parent_id">
                        <option value="">—</option>
                        @foreach ($accounts->sortBy('code') as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->code }} {{ $acc->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label><span class="label-cap">{{ __('messages.is_active') }}</span>
                    <select name="is_active">
                        <option value="1" selected>{{ __('messages.active') }}</option>
                        <option value="0">{{ __('messages.inactive') }}</option>
                    </select>
                </label>
            </div>
            <div class="modal-footer">
                <a href="#" class="secondary-button">{{ __('messages.cancel') }}</a>
                <button class="primary-button">{{ __('messages.save') }}</button>
            </div>
        </form>
    </div>
</div>

@include('partials._xl-import-modal', [
    'modalId'     => 'modal-import-accounts',
    'title'       => __('messages.import') . ' Accounts',
    'importRoute' => route('finance.accounts.import'),
    'columns'     => 'code, name, type (asset/liability/equity/revenue/cogs/expense), is_active',
])
@endsection
