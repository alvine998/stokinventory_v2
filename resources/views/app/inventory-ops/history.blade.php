@extends('layouts.app', ['title' => __('messages.stock_history'), 'heading' => __('messages.nav_inventory')])

@section('content')
@include('app.inventory-ops._subnav')

<section class="panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">{{ __('messages.nav_inventory') }}</p>
            <h2><i class="fa-solid fa-clock-rotate-left"></i> {{ __('messages.stock_history') }}</h2>
        </div>
        <div class="head-actions">
            <a href="{{ route('inventory.history.export') }}" class="secondary-button"><i class="fa-solid fa-file-excel"></i> {{ __('messages.export') }}</a>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" class="filter-bar" style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:18px">
        <select name="product_id" onchange="this.form.submit()">
            <option value="">{{ __('messages.all_products') }}</option>
            @foreach ($products as $p)
                <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
            @endforeach
        </select>
        <select name="warehouse_id" onchange="this.form.submit()">
            <option value="">{{ __('messages.all_warehouses') }}</option>
            @foreach ($warehouses as $w)
                <option value="{{ $w->id }}" {{ request('warehouse_id') == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
            @endforeach
        </select>
        <select name="type" onchange="this.form.submit()">
            <option value="">{{ __('messages.all_types') }}</option>
            <option value="in"  {{ request('type') === 'in'  ? 'selected' : '' }}>In</option>
            <option value="out" {{ request('type') === 'out' ? 'selected' : '' }}>Out</option>
        </select>
        <input type="date" name="from" value="{{ request('from') }}" placeholder="{{ __('messages.from') }}" onchange="this.form.submit()">
        <input type="date" name="to"   value="{{ request('to') }}"   placeholder="{{ __('messages.to') }}"   onchange="this.form.submit()">
        @if (request()->hasAny(['product_id','warehouse_id','type','from','to']))
            <a href="{{ route('inventory.history') }}" class="secondary-button" style="padding:7px 14px">{{ __('messages.reset') }}</a>
        @endif
    </form>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.date') }}</th>
                    <th>{{ __('messages.product') }}</th>
                    <th>{{ __('messages.warehouse') }}</th>
                    <th>{{ __('messages.type') }}</th>
                    <th>{{ __('messages.quantity') }}</th>
                    <th>{{ __('messages.reference_no') }}</th>
                    <th>{{ __('messages.notes') }}</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($movements as $m)
                <tr>
                    <td>{{ $m->moved_at?->format('d M Y H:i') ?? '—' }}</td>
                    <td>{{ $m->product?->name ?? '—' }}</td>
                    <td>{{ $m->warehouse?->name ?? '—' }}</td>
                    <td>
                        <span class="badge-status {{ $m->type === 'in' ? 'badge-active' : 'badge-inactive' }}">
                            {{ strtoupper($m->type) }}
                        </span>
                    </td>
                    <td>{{ number_format($m->quantity) }}</td>
                    <td>{{ $m->reference_no ?: '—' }}</td>
                    <td style="max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $m->notes ?: '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="empty-cell">{{ __('messages.no_movements') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{ $movements->links() }}
</section>
@endsection
