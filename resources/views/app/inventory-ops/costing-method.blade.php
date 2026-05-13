@extends('layouts.app', ['title' => __('messages.costing_method'), 'heading' => __('messages.nav_inventory')])

@section('content')
@include('app.inventory-ops._subnav')

<section class="panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">{{ __('messages.nav_inventory') }}</p>
            <h2><i class="fa-solid fa-calculator"></i> {{ __('messages.costing_method') }}</h2>
        </div>
    </div>

    @include('partials.errors')

    @php
        $methodLabels = [
            'fifo'    => ['label' => 'FIFO',         'hint' => __('messages.costing_fifo_hint'),    'color' => 'var(--blue)'],
            'fefo'    => ['label' => 'FEFO',         'hint' => __('messages.costing_fefo_hint'),    'color' => 'var(--teal)'],
            'average' => ['label' => __('messages.average_cost'), 'hint' => __('messages.costing_avg_hint'), 'color' => 'var(--violet)'],
        ];
    @endphp

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.product') }}</th>
                    <th>{{ __('messages.sku') }}</th>
                    <th>{{ __('messages.costing_method') }}</th>
                    <th>{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            @foreach ($products as $product)
            <tbody class="row-group">
                <tr>
                    <td><strong>{{ $product->name }}</strong></td>
                    <td><code>{{ $product->sku ?: '—' }}</code></td>
                    <td>
                        @php $m = $methodLabels[$product->costing_method] ?? $methodLabels['average']; @endphp
                        <span class="badge-status" style="background:{{ $m['color'] }}22;color:{{ $m['color'] }};border:1px solid {{ $m['color'] }}44">
                            {{ $m['label'] }}
                        </span>
                        <small style="color:#888;margin-left:6px">{{ $m['hint'] }}</small>
                    </td>
                    <td>
                        <button type="button" class="icon-button" title="{{ __('messages.edit') }}"
                            onclick="const r=this.closest('tbody').querySelector('.edit-row');r.hidden=!r.hidden;this.querySelector('i').className=r.hidden?'fa-solid fa-pen':'fa-solid fa-chevron-up'">
                            <i class="fa-solid fa-pen"></i>
                        </button>
                    </td>
                </tr>
                <tr class="edit-row" hidden>
                    <td colspan="4" style="padding:0">
                        <form method="POST" action="{{ route('inventory.costing-method.update', $product) }}" class="edit-inline-form">
                            @csrf @method('PATCH')
                            <div class="form-grid two">
                                <label><span class="label-cap">{{ __('messages.costing_method') }} <span class="req">*</span></span>
                                    <select name="costing_method" required>
                                        <option value="fifo"    {{ $product->costing_method === 'fifo'    ? 'selected' : '' }}>FIFO — {{ __('messages.costing_fifo_hint') }}</option>
                                        <option value="fefo"    {{ $product->costing_method === 'fefo'    ? 'selected' : '' }}>FEFO — {{ __('messages.costing_fefo_hint') }}</option>
                                        <option value="average" {{ $product->costing_method === 'average' ? 'selected' : '' }}>{{ __('messages.average_cost') }} — {{ __('messages.costing_avg_hint') }}</option>
                                    </select>
                                </label>
                            </div>
                            <div style="display:flex;gap:8px">
                                <button class="primary-button">{{ __('messages.save') }}</button>
                            </div>
                        </form>
                    </td>
                </tr>
            </tbody>
            @endforeach
        </table>
    </div>
</section>
@endsection
