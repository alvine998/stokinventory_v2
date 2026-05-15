@extends('layouts.app', ['title' => __('messages.shipment_tracking'), 'heading' => __('messages.nav_sales')])

@section('content')
@include('app.sales._subnav')

<section class="panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">{{ __('messages.nav_sales') }}</p>
            <h2><i class="fa-solid fa-location-dot"></i> {{ __('messages.shipment_tracking') }}</h2>
        </div>
        <div class="head-actions">
            <a href="{{ route('sales.shipment-tracking.export') }}" class="secondary-button"><i class="fa-solid fa-file-excel"></i> {{ __('messages.export') }}</a>
        </div>
    </div>

    @include('partials.errors')

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.do_no') }}</th>
                    <th>{{ __('messages.customer') }}</th>
                    <th>{{ __('messages.expedition') }}</th>
                    <th>{{ __('messages.tracking_no') }}</th>
                    <th>{{ __('messages.status') }}</th>
                    <th>{{ __('messages.shipped_at') }}</th>
                    <th>{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            @forelse ($dos as $do)
            @php
                $doColors = ['draft'=>'','shipped'=>'','in_transit'=>'','delivered'=>'badge-active','failed'=>'badge-inactive','returned'=>'badge-inactive'];
            @endphp
            <tbody class="row-group">
                <tr>
                    <td><strong>{{ $do->do_no }}</strong></td>
                    <td>{{ $do->customer?->name ?? '—' }}</td>
                    <td>{{ $do->expedition?->name ?? '—' }}</td>
                    <td>
                        @if($do->tracking_no && $do->trackingUrl())
                            <a href="{{ $do->trackingUrl() }}" target="_blank" rel="noopener" style="color:var(--teal)">{{ $do->tracking_no }}</a>
                        @else
                            {{ $do->tracking_no ?? '—' }}
                        @endif
                    </td>
                    <td><span class="badge-status {{ $doColors[$do->status] ?? '' }}">{{ __('messages.do_status_'.$do->status) }}</span></td>
                    <td>{{ $do->shipped_at?->format('d M Y') ?? '—' }}</td>
                    <td>
                        <button type="button" class="icon-button" title="{{ __('messages.view_tracking') }}"
                            onclick="const r=this.closest('tbody').querySelector('.edit-row');r.hidden=!r.hidden;this.querySelector('i').className=r.hidden?'fa-solid fa-chevron-down':'fa-solid fa-chevron-up'">
                            <i class="fa-solid fa-chevron-down"></i>
                        </button>
                    </td>
                </tr>
                <tr class="edit-row" hidden>
                    <td colspan="7" style="padding:0">
                        <div style="padding:16px;background:#f6fafc;border-top:2px solid #e3ecef">
                            <div style="display:grid;grid-template-columns:1fr 340px;gap:24px">
                                <div>
                                    <p style="font-size:12px;font-weight:700;margin:0 0 10px;color:#555;text-transform:uppercase;letter-spacing:.04em">{{ __('messages.tracking_history') }}</p>
                                    @forelse ($do->trackings as $t)
                                        <div style="position:relative;padding:0 0 14px 20px;border-left:2px solid var(--teal);margin-left:6px">
                                            <div style="position:absolute;left:-5px;top:0;width:8px;height:8px;border-radius:50%;background:var(--teal)"></div>
                                            <span style="font-size:12px;font-weight:600">{{ $t->status }}</span>
                                            @if($t->location) <span style="font-size:12px;color:#888;margin-left:6px">{{ $t->location }}</span> @endif
                                            <p style="font-size:12px;color:#555;margin:2px 0">{{ $t->description }}</p>
                                            <span style="font-size:11px;color:#aaa">{{ $t->tracked_at->format('d M Y H:i') }}</span>
                                        </div>
                                    @empty
                                        <p style="font-size:12px;color:#aaa">{{ __('messages.no_tracking_events') }}</p>
                                    @endforelse
                                </div>
                                <div>
                                    <p style="font-size:12px;font-weight:700;margin:0 0 10px;color:#555;text-transform:uppercase;letter-spacing:.04em">{{ __('messages.add_tracking_event') }}</p>
                                    <form method="POST" action="{{ route('sales.shipment-tracking.store', $do) }}">
                                        @csrf
                                        <div style="display:flex;flex-direction:column;gap:8px">
                                            <label><span class="label-cap">{{ __('messages.status') }}</span>
                                                <input type="text" name="status" placeholder="in_transit" required>
                                            </label>
                                            <label><span class="label-cap">{{ __('messages.location') }}</span>
                                                <input type="text" name="location" placeholder="Sorting hub Bandung">
                                            </label>
                                            <label><span class="label-cap">{{ __('messages.description') }}</span>
                                                <input type="text" name="description" placeholder="{{ __('messages.tracking_note_placeholder') }}">
                                            </label>
                                            <label><span class="label-cap">{{ __('messages.date') }}</span>
                                                <input type="datetime-local" name="tracked_at">
                                            </label>
                                            <button type="submit" class="primary-button" style="margin-top:4px">{{ __('messages.add_tracking_event') }}</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            </tbody>
            @empty
                <tbody><tr><td colspan="7" class="empty-cell">{{ __('messages.no_dos') }}</td></tr></tbody>
            @endforelse
        </table>
    </div>
    {{ $dos->links() }}
</section>
@endsection
