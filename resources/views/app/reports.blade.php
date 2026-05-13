@extends('layouts.app', ['title' => __('messages.reports'), 'heading' => __('messages.reports')])

@section('content')
<section class="stats-grid">
    <article class="stat-card">
        <i class="fa-solid fa-boxes-stacked"></i>
        <span>{{ __('messages.total_products') }}</span>
        <strong>{{ $stats['total_products'] }}</strong>
    </article>
    <article class="stat-card">
        <i class="fa-solid fa-exclamation-triangle"></i>
        <span>{{ __('messages.low_stock') }}</span>
        <strong>{{ $stats['low_stock_count'] }}</strong>
    </article>
    <article class="stat-card">
        <i class="fa-solid fa-coins"></i>
        <span>{{ __('messages.stock_value') }}</span>
        <strong>${{ $stats['total_stock_value'] }}</strong>
    </article>
    <article class="stat-card">
        <i class="fa-solid fa-right-left"></i>
        <span>{{ __('messages.total_movements') }}</span>
        <strong>{{ $stats['total_movements'] }}</strong>
    </article>
</section>

<section class="panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">{{ __('messages.analytics') }}</p>
            <h2>{{ __('messages.report_center') }}</h2>
        </div>
        <button class="secondary-button" onclick="window.print()"><i class="fa-solid fa-print"></i> {{ __('messages.print_report') }}</button>
    </div>
</section>

<div class="charts-grid">
    <section class="panel">
        <div class="section-head">
            <div>
                <p class="eyebrow">{{ __('messages.distribution') }}</p>
                <h3>{{ __('messages.movement_summary') }}</h3>
            </div>
        </div>
        <div style="position: relative; height: 280px;">
            <canvas id="movementSummaryChart"></canvas>
        </div>
    </section>

    <section class="panel">
        <div class="section-head">
            <div>
                <p class="eyebrow">{{ __('messages.trends') }}</p>
                <h3>{{ __('messages.monthly_movements') }}</h3>
            </div>
        </div>
        <div style="position: relative; height: 280px;">
            <canvas id="monthlyMovementsChart"></canvas>
        </div>
    </section>
</div>

<section class="panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">{{ __('messages.top_performers') }}</p>
            <h3>{{ __('messages.top_products') }}</h3>
        </div>
    </div>
    <div style="position: relative; height: 320px;">
        <canvas id="topProductsChart"></canvas>
    </div>
</section>

<script>
    // Movement Summary - Bar Chart
    const summaryCtx = document.getElementById('movementSummaryChart').getContext('2d');
    const summaryLabels = @json($chartData['movementSummary']['labels']);
    const summaryData = @json($chartData['movementSummary']['data']);
    
    new Chart(summaryCtx, {
        type: 'bar',
        data: {
            labels: summaryLabels,
            datasets: [{
                label: '{{ __("messages.quantity") }}',
                data: summaryData,
                backgroundColor: ['#0f766e', '#f59e0b', '#ef4444'],
                borderColor: ['#0d5c54', '#d97706', '#dc2626'],
                borderWidth: 1,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false,
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                    }
                }
            }
        }
    });

    // Monthly Movements - Line Chart
    const monthlyCtx = document.getElementById('monthlyMovementsChart').getContext('2d');
    const monthlyLabels = @json(array_keys($chartData['monthlyMovements']));
    const monthlyData = @json(array_values($chartData['monthlyMovements']));
    
    new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: monthlyLabels,
            datasets: [{
                label: '{{ __("messages.movements") }}',
                data: monthlyData,
                borderColor: '#8b5cf6',
                backgroundColor: 'rgba(139, 92, 246, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#8b5cf6',
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

    // Top Products - Horizontal Bar Chart
    const topCtx = document.getElementById('topProductsChart').getContext('2d');
    const topLabels = @json($chartData['topProducts']['labels']);
    const topData = @json($chartData['topProducts']['data']);
    
    new Chart(topCtx, {
        type: 'bar',
        data: {
            labels: topLabels,
            datasets: [{
                label: '{{ __("messages.quantity") }}',
                data: topData,
                backgroundColor: '#06b6d4',
                borderColor: '#0891b2',
                borderWidth: 1,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false,
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                    }
                }
            }
        }
    });
</script>
@endsection
