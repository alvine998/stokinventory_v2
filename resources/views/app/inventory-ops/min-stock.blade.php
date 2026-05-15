@extends('layouts.app', ['title' => __('messages.min_stock_alert'), 'heading' => __('messages.nav_inventory')])

@section('content')
@include('app.inventory-ops._subnav')

<section class="panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">{{ __('messages.nav_inventory') }}</p>
            <h2><i class="fa-solid fa-triangle-exclamation"></i> {{ __('messages.min_stock_alert') }}</h2>
        </div>
        <span style="font-size:13px;color:#888">{{ $products->count() }} {{ __('messages.products_below_min') }}</span>
        <a href="{{ route('inventory.min-stock.export') }}" class="secondary-button"><i class="fa-solid fa-file-excel"></i> {{ __('messages.export') }}</a>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.product') }}</th>
                    <th>{{ __('messages.sku') }}</th>
                    <th>{{ __('messages.current_stock') }}</th>
                    <th>{{ __('messages.minimum_stock') }}</th>
                    <th>{{ __('messages.deficit') }}</th>
                    <th>{{ __('messages.reorder_point') }}</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($products as $p)
                @php $deficit = $p->minimum_stock - $p->current_stock; @endphp
                <tr>
                    <td><strong>{{ $p->name }}</strong></td>
                    <td><code>{{ $p->sku ?: '—' }}</code></td>
                    <td>
                        <span style="color:{{ $p->current_stock <= 0 ? 'var(--rose)' : 'var(--amber)' }};font-weight:600">
                            {{ number_format($p->current_stock) }}
                        </span>
                    </td>
                    <td>{{ number_format($p->minimum_stock) }}</td>
                    <td style="color:var(--rose);font-weight:600">−{{ number_format($deficit) }}</td>
                    <td>{{ number_format($p->reorder_point) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="empty-cell">
                        <i class="fa-solid fa-circle-check" style="color:var(--teal)"></i>
                        {{ __('messages.no_min_stock_alerts') }}
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
