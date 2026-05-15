@extends('layouts.platform', ['title' => __('messages.bank_accounts'), 'heading' => __('messages.bank_accounts')])

@section('content')
<section class="panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">{{ __('messages.platform_control') }}</p>
            <h2><i class="fa-solid fa-building-columns"></i> {{ __('messages.bank_accounts') }}</h2>
        </div>
        <div class="head-actions">
            <a href="#modal-add-bank" class="primary-button"><i class="fa-solid fa-plus"></i> {{ __('messages.add_new') }}</a>
        </div>
    </div>

    @include('partials.errors')

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.bank_name') }}</th>
                    <th>{{ __('messages.account_name') }}</th>
                    <th>{{ __('messages.account_number') }}</th>
                    <th>{{ __('messages.branch') }}</th>
                    <th>{{ __('messages.status') }}</th>
                    <th>{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($bankAccounts as $bankAccount)
                <tr>
                    <td><strong>{{ $bankAccount->bank_name }}</strong></td>
                    <td>{{ $bankAccount->account_name }}</td>
                    <td><code>{{ $bankAccount->account_number }}</code></td>
                    <td>{{ $bankAccount->branch ?: '—' }}</td>
                    <td>
                        <span class="badge-status {{ $bankAccount->is_active ? 'badge-active' : 'badge-inactive' }}">
                            {{ $bankAccount->is_active ? __('messages.active') : __('messages.inactive') }}
                        </span>
                    </td>
                    <td>
                        <form method="POST" action="{{ route('super-admin.bank-accounts.toggle', $bankAccount) }}" style="display:inline">
                            @csrf @method('PATCH')
                            <button class="icon-button" title="{{ $bankAccount->is_active ? __('messages.deactivate') : __('messages.activate') }}">
                                <i class="fa-solid fa-power-off"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="empty-cell">{{ __('messages.no_bank_accounts') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>

{{-- Add Bank Account Modal --}}
<div class="modal-overlay" id="modal-add-bank" role="dialog" aria-modal="true" aria-labelledby="modal-add-bank-title">
    <div class="modal-card">
        <header>
            <h3 id="modal-add-bank-title"><i class="fa-solid fa-building-columns"></i> {{ __('messages.add_bank_account') }}</h3>
            <a href="#" class="icon-button" aria-label="{{ __('messages.close') }}"><i class="fa-solid fa-xmark"></i></a>
        </header>
        <form method="POST" action="{{ route('super-admin.bank-accounts.store') }}" class="form-grid">
            @csrf
            <div class="form-grid two">
                <label><span class="label-cap">{{ __('messages.bank_name') }} <span class="req">*</span></span>
                    <input name="bank_name" value="{{ old('bank_name') }}" placeholder="BCA, Mandiri…" required>
                </label>
                <label><span class="label-cap">{{ __('messages.branch') }}</span>
                    <input name="branch" value="{{ old('branch') }}" placeholder="{{ __('messages.branch') }}">
                </label>
            </div>
            <label><span class="label-cap">{{ __('messages.account_name') }} <span class="req">*</span></span>
                <input name="account_name" value="{{ old('account_name') }}" placeholder="{{ __('messages.account_name') }}" required>
            </label>
            <label><span class="label-cap">{{ __('messages.account_number') }} <span class="req">*</span></span>
                <input name="account_number" value="{{ old('account_number') }}" placeholder="1234567890" required>
            </label>
            <label class="check-row"><input name="is_active" type="checkbox" value="1" checked> {{ __('messages.active') }}</label>
            <div class="modal-actions">
                <a href="#" class="secondary-button">{{ __('messages.cancel') }}</a>
                <button class="primary-button"><i class="fa-solid fa-floppy-disk"></i> {{ __('messages.save') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection

