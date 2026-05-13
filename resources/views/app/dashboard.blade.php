@extends('layouts.app', ['title' => __('messages.dashboard'), 'heading' => __('messages.dashboard')])

@section('content')
<section class="stats-grid">
    @foreach ($stats as $key => $value)
        <article class="stat-card">
            <i class="fa-solid {{ ['products' => 'fa-boxes-stacked', 'stores' => 'fa-store', 'warehouses' => 'fa-warehouse', 'users' => 'fa-users'][$key] }}"></i>
            <span>{{ __('messages.' . $key) }}</span>
            <strong>{{ number_format($value) }}</strong>
        </article>
    @endforeach
</section>

<div class="charts-grid">
    <section class="panel">
        <div class="section-head">
            <div>
                <p class="eyebrow">{{ __('messages.analytics') }}</p>
                <h3>{{ __('messages.movements_trend') }}</h3>
            </div>
        </div>
        <div style="position: relative; height: 250px;">
            <canvas id="movementsTrendChart"></canvas>
        </div>
    </section>

    <section class="panel">
        <div class="section-head">
            <div>
                <p class="eyebrow">{{ __('messages.distribution') }}</p>
                <h3>{{ __('messages.movement_types') }}</h3>
            </div>
        </div>
        <div style="position: relative; height: 250px;">
            <canvas id="movementTypesChart"></canvas>
        </div>
    </section>
</div>

<section class="panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">{{ __('messages.today_snapshot') }}</p>
            <h2>{{ __('messages.stock_activity') }}</h2>
        </div>
        <button class="secondary-button" onclick="window.print()"><i class="fa-solid fa-print"></i> {{ __('messages.print_receipt') }}</button>
    </div>
    <div class="activity-list">
        @forelse ($movements as $movement)
            <div class="activity-item"><i class="fa-solid fa-right-left"></i><span>{{ $movement->reference_no ?? __('messages.stock_movement') }}</span><strong>{{ $movement->quantity }}</strong></div>
        @empty
            <div class="empty-state"><i class="fa-solid fa-box-open"></i><p>{{ __('messages.no_data_yet') }}</p></div>
        @endforelse
    </div>
</section>

<script>
    // Movements Trend Chart
    const trendCtx = document.getElementById('movementsTrendChart').getContext('2d');
    const trendLabels = @json(array_keys($chartData['movementsTrend']));
    const trendData = @json(array_values($chartData['movementsTrend']));
    
    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: trendLabels,
            datasets: [{
                label: '{{ __("messages.stock_movements") }}',
                data: trendData,
                borderColor: '#0f766e',
                backgroundColor: 'rgba(15, 118, 110, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#0f766e',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                    }
                }
            }
        }
    });

    // Movement Types Chart
    const typesCtx = document.getElementById('movementTypesChart').getContext('2d');
    const typeLabels = @json($chartData['movementTypes']['labels']);
    const typeData = @json($chartData['movementTypes']['data']);
    const colors = ['#0f766e', '#f59e0b', '#ef4444', '#8b5cf6'];
    
    new Chart(typesCtx, {
        type: 'doughnut',
        data: {
            labels: typeLabels,
            datasets: [{
                data: typeData,
                backgroundColor: colors.slice(0, typeLabels.length),
                borderColor: '#fff',
                borderWidth: 2,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom',
                }
            }
        }
    });
</script>
@endsection
