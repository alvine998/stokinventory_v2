@extends('layouts.app', ['title' => __('messages.journal_auto'), 'heading' => __('messages.nav_finance')])

@section('content')
@include('app.finance._subnav')

<section class="panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">{{ __('messages.nav_finance') }}</p>
            <h2><i class="fa-solid fa-book-open"></i> {{ __('messages.journal_auto') }}</h2>
        </div>
        <div class="head-actions">
            <a href="{{ route('finance.journals.export') }}" class="secondary-button"><i class="fa-solid fa-file-excel"></i> {{ __('messages.export') }}</a>
            <a href="#modal-add-journal" class="primary-button"><i class="fa-solid fa-plus"></i> {{ __('messages.new_journal') }}</a>
        </div>
    </div>

    @include('partials.errors')

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.entry_no') }}</th>
                    <th>{{ __('messages.date') }}</th>
                    <th>{{ __('messages.description') }}</th>
                    <th>{{ __('messages.reference') }}</th>
                    <th>{{ __('messages.source') }}</th>
                    <th class="num">{{ __('messages.total_debit') }}</th>
                    <th>{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            @forelse ($journals as $j)
            <tbody class="row-group">
                <tr>
                    <td><strong>{{ $j->entry_no }}</strong></td>
                    <td>{{ $j->entry_date->format('d M Y') }}</td>
                    <td>{{ $j->description }}</td>
                    <td><code>{{ $j->reference_no ?? '—' }}</code></td>
                    <td>
                        @if($j->is_auto)
                            <span class="badge-status badge-active"><i class="fa-solid fa-bolt"></i> Auto</span>
                        @else
                            <span class="badge-status">Manual</span>
                        @endif
                    </td>
                    <td class="num">Rp {{ number_format($j->totalDebit(), 0, ',', '.') }}</td>
                    <td>
                        <div style="display:flex;gap:6px">
                            <button type="button" class="icon-button"
                                onclick="const r=this.closest('tbody').querySelector('.edit-row');r.hidden=!r.hidden;this.querySelector('i').className=r.hidden?'fa-solid fa-chevron-down':'fa-solid fa-chevron-up'">
                                <i class="fa-solid fa-chevron-down"></i>
                            </button>
                            @if(!$j->is_auto)
                            <form method="POST" action="{{ route('finance.journals.destroy', $j) }}" onsubmit="return confirm('{{ __('messages.confirm_delete') }}')">
                                @csrf @method('DELETE')
                                <button class="icon-button" style="color:var(--rose)"><i class="fa-solid fa-trash"></i></button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                <tr class="edit-row" hidden>
                    <td colspan="7" style="padding:0">
                        <div style="padding:14px 16px;background:#f6fafc;border-top:2px solid #e3ecef">
                            <table style="width:100%;font-size:13px">
                                <thead><tr style="color:#888">
                                    <th style="text-align:left;padding:4px 8px">{{ __('messages.account') }}</th>
                                    <th style="text-align:left;padding:4px 8px">{{ __('messages.description') }}</th>
                                    <th style="text-align:right;padding:4px 8px">{{ __('messages.debit') }}</th>
                                    <th style="text-align:right;padding:4px 8px">{{ __('messages.credit') }}</th>
                                </tr></thead>
                                <tbody>
                                @foreach ($j->lines as $line)
                                <tr>
                                    <td style="padding:4px 8px"><code>{{ $line->account->code }}</code> {{ $line->account->name }}</td>
                                    <td style="padding:4px 8px">{{ $line->description ?? '—' }}</td>
                                    <td style="padding:4px 8px;text-align:right">{{ $line->debit > 0 ? 'Rp '.number_format($line->debit, 0, ',', '.') : '' }}</td>
                                    <td style="padding:4px 8px;text-align:right">{{ $line->credit > 0 ? 'Rp '.number_format($line->credit, 0, ',', '.') : '' }}</td>
                                </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </td>
                </tr>
            </tbody>
            @empty
                <tbody><tr><td colspan="7" class="empty-cell">{{ __('messages.no_journals') }}</td></tr></tbody>
            @endforelse
        </table>
    </div>
    {{ $journals->links() }}
</section>

{{-- Add Journal Modal --}}
<div class="modal-overlay" id="modal-add-journal">
    <div class="modal" style="max-width:700px">
        <div class="modal-head">
            <h3>{{ __('messages.new_journal') }}</h3>
            <a href="#" class="modal-close"><i class="fa-solid fa-xmark"></i></a>
        </div>
        <form method="POST" action="{{ route('finance.journals.store') }}" class="modal-body">
            @csrf
            <div class="form-grid two">
                <label><span class="label-cap">{{ __('messages.date') }} <span class="req">*</span></span>
                    <input type="date" name="entry_date" value="{{ date('Y-m-d') }}" required>
                </label>
                <label><span class="label-cap">{{ __('messages.reference') }}</span>
                    <input type="text" name="reference_no" placeholder="SO-0001">
                </label>
                <label style="grid-column:span 2"><span class="label-cap">{{ __('messages.description') }} <span class="req">*</span></span>
                    <input type="text" name="description" required>
                </label>
            </div>
            <p class="label-cap" style="margin:16px 0 8px">{{ __('messages.journal_lines') }} <span class="req">*</span> <span style="font-weight:400;color:#888;font-size:11px">— {{ __('messages.journal_must_balance') }}</span></p>
            <div style="display:grid;grid-template-columns:1fr 1fr 130px 130px 24px;gap:6px;font-size:11px;font-weight:700;text-transform:uppercase;color:#888;letter-spacing:.04em;padding-bottom:4px">
                <span>{{ __('messages.account') }}</span><span>{{ __('messages.description') }}</span><span>{{ __('messages.debit') }}</span><span>{{ __('messages.credit') }}</span><span></span>
            </div>
            <div id="journal-lines">
                @foreach ([0,1] as $i)
                <div class="journal-line" style="display:grid;grid-template-columns:1fr 1fr 130px 130px 24px;gap:6px;margin-bottom:5px;align-items:center">
                    <select name="lines[{{ $i }}][account_id]">
                        <option value="">— {{ __('messages.select_account') }} —</option>
                        @foreach ($accounts->groupBy('type') as $type => $group)
                            <optgroup label="{{ __('messages.account_type_'.$type) }}">
                                @foreach ($group as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->code }} {{ $acc->name }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    <input type="text" name="lines[{{ $i }}][description]" placeholder="{{ __('messages.description') }}">
                    <input type="number" name="lines[{{ $i }}][debit]" min="0" step="0.01" placeholder="0">
                    <input type="number" name="lines[{{ $i }}][credit]" min="0" step="0.01" placeholder="0">
                    <button type="button" class="icon-button remove-journal-line" style="color:var(--rose)"><i class="fa-solid fa-xmark"></i></button>
                </div>
                @endforeach
            </div>
            <button type="button" id="add-journal-line" class="secondary-button" style="font-size:12px;padding:5px 12px;margin-top:4px">
                <i class="fa-solid fa-plus"></i> {{ __('messages.add_line') }}
            </button>
            <div class="modal-footer">
                <a href="#" class="secondary-button">{{ __('messages.cancel') }}</a>
                <button class="primary-button">{{ __('messages.save') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    let lineCount = {{ count([0,1]) }};
    const accountOptions = `{!! $accounts->groupBy('type')->map(function($group, $type) {
        $opts = $group->map(fn($a) => '<option value="'.$a->id.'">'.$a->code.' '.$a->name.'</option>')->implode('');
        return '<optgroup label="'.$type.'">'.$opts.'</optgroup>';
    })->implode('') !!}`;

    document.getElementById('add-journal-line').addEventListener('click', function () {
        const container = document.getElementById('journal-lines');
        const div = document.createElement('div');
        div.className = 'journal-line';
        div.style.cssText = 'display:grid;grid-template-columns:1fr 1fr 130px 130px 24px;gap:6px;margin-bottom:5px;align-items:center';
        div.innerHTML = `
            <select name="lines[${lineCount}][account_id]">
                <option value="">— {{ __('messages.select_account') }} —</option>
                ${accountOptions}
            </select>
            <input type="text" name="lines[${lineCount}][description]" placeholder="{{ __('messages.description') }}">
            <input type="number" name="lines[${lineCount}][debit]" min="0" step="0.01" placeholder="0">
            <input type="number" name="lines[${lineCount}][credit]" min="0" step="0.01" placeholder="0">
            <button type="button" class="icon-button remove-journal-line" style="color:var(--rose)"><i class="fa-solid fa-xmark"></i></button>
        `;
        container.appendChild(div);
        lineCount++;
    });

    document.getElementById('journal-lines').addEventListener('click', function (e) {
        const btn = e.target.closest('.remove-journal-line');
        if (btn) btn.closest('.journal-line').remove();
    });
})();
</script>
@endpush
