@extends('layouts.app')

@section('title', 'Estatísticas de Revendas')

@section('content')
<div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-6 gap-4">
    <div>
        <h1 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
            <i class="bi bi-bar-chart-line text-orange-500"></i>
            Estatísticas de Revendas
        </h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Visão geral de desempenho das revendas</p>
    </div>
</div>

<!-- Filtro por Data -->
<div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 p-4 mb-6 shadow-sm dark:shadow-none">
    <form method="GET" action="{{ route('reseller-stats.index') }}" class="flex flex-col sm:flex-row items-end gap-4">
        <input type="hidden" name="sort" value="{{ $sortBy }}">
        <input type="hidden" name="dir" value="{{ $sortDir }}">
        <div class="flex-1 w-full sm:w-auto">
            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-1">Data Início</label>
            <input type="date" name="date_from" value="{{ $dateFrom }}" class="w-full px-3 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white text-sm focus:border-orange-500 focus:outline-none">
        </div>
        <div class="flex-1 w-full sm:w-auto">
            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-1">Data Fim</label>
            <input type="date" name="date_to" value="{{ $dateTo }}" class="w-full px-3 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white text-sm focus:border-orange-500 focus:outline-none">
        </div>
        <div class="flex gap-2">
            <button type="submit" class="px-4 py-2 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg hover:shadow-lg transition-all text-sm font-medium flex items-center gap-2">
                <i class="bi bi-funnel"></i>
                Filtrar
            </button>
            @if($dateFrom || $dateTo)
            <a href="{{ route('reseller-stats.index', ['sort' => $sortBy, 'dir' => $sortDir]) }}" class="px-4 py-2 bg-gray-100 dark:bg-dark-200 text-gray-600 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-dark-100 transition-colors text-sm font-medium">
                Limpar
            </a>
            @endif
        </div>
    </form>
</div>

<!-- Cards Resumo -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 p-4 shadow-sm dark:shadow-none">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-orange-100 dark:bg-orange-500/10 rounded-lg flex items-center justify-center">
                <i class="bi bi-shop text-orange-500 text-lg"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-semibold">Revendas</p>
                <p class="text-xl font-bold text-gray-900 dark:text-white">{{ count($stats) }}</p>
            </div>
        </div>
    </div>
    <div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 p-4 shadow-sm dark:shadow-none">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-blue-100 dark:bg-blue-500/10 rounded-lg flex items-center justify-center">
                <i class="bi bi-people text-blue-500 text-lg"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-semibold">Total Clientes</p>
                <p class="text-xl font-bold text-gray-900 dark:text-white">{{ number_format(collect($stats)->sum('clients_count')) }}</p>
            </div>
        </div>
    </div>
    <div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 p-4 shadow-sm dark:shadow-none">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-purple-100 dark:bg-purple-500/10 rounded-lg flex items-center justify-center">
                <i class="bi bi-diagram-3 text-purple-500 text-lg"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-semibold">Total Subrevendas</p>
                <p class="text-xl font-bold text-gray-900 dark:text-white">{{ number_format(collect($stats)->sum('sub_resellers_count')) }}</p>
            </div>
        </div>
    </div>
    <div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 p-4 shadow-sm dark:shadow-none">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-green-100 dark:bg-green-500/10 rounded-lg flex items-center justify-center">
                <i class="bi bi-cash-coin text-green-500 text-lg"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-semibold">Créditos Vendidos</p>
                <p class="text-xl font-bold text-gray-900 dark:text-white">{{ number_format(collect($stats)->sum('credits_sold'), 2, ',', '.') }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Tabela -->
<div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 overflow-hidden shadow-sm dark:shadow-none mb-6">
    @if(count($stats) > 0)
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 dark:bg-dark-200 border-b border-gray-200 dark:border-dark-100">
                <tr>
                    <th class="px-4 md:px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        #
                    </th>
                    @php
                        $baseParams = array_filter(['date_from' => $dateFrom, 'date_to' => $dateTo]);
                    @endphp
                    <th class="px-4 md:px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">
                        <a href="{{ route('reseller-stats.index', array_merge($baseParams, ['sort' => 'username', 'dir' => $sortBy === 'username' && $sortDir === 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center gap-1 hover:text-orange-500 transition-colors">
                            Revenda
                            @if($sortBy === 'username')
                                <i class="bi bi-chevron-{{ $sortDir === 'asc' ? 'up' : 'down' }} text-orange-500"></i>
                            @endif
                        </a>
                    </th>
                    <th class="px-4 md:px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">
                        <a href="{{ route('reseller-stats.index', array_merge($baseParams, ['sort' => 'credits', 'dir' => $sortBy === 'credits' && $sortDir === 'desc' ? 'asc' : 'desc'])) }}" class="flex items-center gap-1 hover:text-orange-500 transition-colors">
                            Saldo
                            @if($sortBy === 'credits')
                                <i class="bi bi-chevron-{{ $sortDir === 'asc' ? 'up' : 'down' }} text-orange-500"></i>
                            @endif
                        </a>
                    </th>
                    <th class="px-4 md:px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">
                        <a href="{{ route('reseller-stats.index', array_merge($baseParams, ['sort' => 'sub_resellers_count', 'dir' => $sortBy === 'sub_resellers_count' && $sortDir === 'desc' ? 'asc' : 'desc'])) }}" class="flex items-center gap-1 hover:text-orange-500 transition-colors">
                            Subrevendas
                            @if($sortBy === 'sub_resellers_count')
                                <i class="bi bi-chevron-{{ $sortDir === 'asc' ? 'up' : 'down' }} text-orange-500"></i>
                            @endif
                        </a>
                    </th>
                    <th class="px-4 md:px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">
                        <a href="{{ route('reseller-stats.index', array_merge($baseParams, ['sort' => 'clients_count', 'dir' => $sortBy === 'clients_count' && $sortDir === 'desc' ? 'asc' : 'desc'])) }}" class="flex items-center gap-1 hover:text-orange-500 transition-colors">
                            Clientes
                            @if($sortBy === 'clients_count')
                                <i class="bi bi-chevron-{{ $sortDir === 'asc' ? 'up' : 'down' }} text-orange-500"></i>
                            @endif
                        </a>
                    </th>
                    <th class="px-4 md:px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">
                        <a href="{{ route('reseller-stats.index', array_merge($baseParams, ['sort' => 'credits_sold', 'dir' => $sortBy === 'credits_sold' && $sortDir === 'desc' ? 'asc' : 'desc'])) }}" class="flex items-center gap-1 hover:text-orange-500 transition-colors">
                            Créd. Vendidos
                            @if($sortBy === 'credits_sold')
                                <i class="bi bi-chevron-{{ $sortDir === 'asc' ? 'up' : 'down' }} text-orange-500"></i>
                            @endif
                        </a>
                    </th>
                    <th class="px-4 md:px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">
                        Status
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-dark-200">
                @foreach($stats as $index => $reseller)
                <tr class="hover:bg-gray-50 dark:hover:bg-dark-200 transition-colors duration-150">
                    <td class="px-4 md:px-6 py-3 whitespace-nowrap text-sm text-gray-400 dark:text-gray-500 font-mono">
                        {{ $index + 1 }}
                    </td>
                    <td class="px-4 md:px-6 py-3 whitespace-nowrap">
                        <span class="text-gray-900 dark:text-white font-medium">{{ $reseller['username'] }}</span>
                    </td>
                    <td class="px-4 md:px-6 py-3 whitespace-nowrap">
                        <span class="text-orange-600 dark:text-orange-400 font-bold">{{ number_format($reseller['credits'], 2, ',', '.') }}</span>
                    </td>
                    <td class="px-4 md:px-6 py-3 whitespace-nowrap">
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-purple-50 dark:bg-purple-500/10 text-purple-600 dark:text-purple-400 text-sm font-semibold rounded-lg">
                            <i class="bi bi-diagram-3 text-xs"></i>
                            {{ $reseller['sub_resellers_count'] }}
                        </span>
                    </td>
                    <td class="px-4 md:px-6 py-3 whitespace-nowrap">
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 text-sm font-semibold rounded-lg">
                            <i class="bi bi-people text-xs"></i>
                            {{ $reseller['clients_count'] }}
                        </span>
                    </td>
                    <td class="px-4 md:px-6 py-3 whitespace-nowrap">
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-green-50 dark:bg-green-500/10 text-green-600 dark:text-green-400 text-sm font-semibold rounded-lg">
                            <i class="bi bi-cash-coin text-xs"></i>
                            {{ number_format($reseller['credits_sold'], 2, ',', '.') }}
                        </span>
                    </td>
                    <td class="px-4 md:px-6 py-3 whitespace-nowrap">
                        @if($reseller['status'] == 1)
                            <span class="px-2 py-1 bg-green-100 dark:bg-green-500/10 text-green-600 dark:text-green-400 text-xs font-semibold rounded-full">Ativo</span>
                        @else
                            <span class="px-2 py-1 bg-red-100 dark:bg-red-500/10 text-red-600 dark:text-red-400 text-xs font-semibold rounded-full">Bloqueado</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="text-center py-16">
        <i class="bi bi-bar-chart-line text-gray-400 dark:text-gray-600 text-6xl mb-4"></i>
        <h3 class="text-xl font-semibold text-gray-600 dark:text-gray-400 mb-2">Nenhuma revenda encontrada</h3>
        <p class="text-gray-500">Não há revendas para exibir estatísticas.</p>
    </div>
    @endif
</div>

<!-- Gráficos -->
@if(count($stats) > 0)
<div class="grid grid-cols-1 lg:grid-cols-1 gap-6">
    <!-- Top 20 Subrevendas -->
    <div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 p-4 md:p-6 shadow-sm dark:shadow-none">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <i class="bi bi-diagram-3 text-purple-500"></i>
            Top 20 — Subrevendas
        </h3>
        <div class="relative" style="height: 400px;">
            <canvas id="chartSubResellers"></canvas>
        </div>
    </div>

    <!-- Top 20 Clientes -->
    <div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 p-4 md:p-6 shadow-sm dark:shadow-none">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <i class="bi bi-people text-blue-500"></i>
            Top 20 — Clientes
        </h3>
        <div class="relative" style="height: 400px;">
            <canvas id="chartClients"></canvas>
        </div>
    </div>

    <!-- Top 20 Créditos Vendidos -->
    <div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 p-4 md:p-6 shadow-sm dark:shadow-none">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <i class="bi bi-cash-coin text-green-500"></i>
            Top 20 — Créditos Vendidos
            @if($dateFrom || $dateTo)
                <span class="text-xs font-normal text-gray-400 ml-2">
                    ({{ $dateFrom ? \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') : '...' }} — {{ $dateTo ? \Carbon\Carbon::parse($dateTo)->format('d/m/Y') : '...' }})
                </span>
            @endif
        </h3>
        <div class="relative" style="height: 400px;">
            <canvas id="chartCreditsSold"></canvas>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
    const chartData = @json($chartData);
    const isDark = document.documentElement.classList.contains('dark');
    const gridColor = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';
    const labelColor = isDark ? '#9ca3af' : '#6b7280';

    const defaultOptions = {
        responsive: true,
        maintainAspectRatio: false,
        indexAxis: 'y',
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: isDark ? '#1f2937' : '#fff',
                titleColor: isDark ? '#f3f4f6' : '#111827',
                bodyColor: isDark ? '#d1d5db' : '#374151',
                borderColor: isDark ? '#374151' : '#e5e7eb',
                borderWidth: 1,
                padding: 12,
                cornerRadius: 8,
            }
        },
        scales: {
            x: {
                grid: { color: gridColor },
                ticks: { color: labelColor, font: { size: 12 } },
                beginAtZero: true,
            },
            y: {
                grid: { display: false },
                ticks: { color: labelColor, font: { size: 12, weight: '500' } },
            }
        }
    };

    function createChart(canvasId, labels, data, color) {
        const ctx = document.getElementById(canvasId);
        if (!ctx || labels.length === 0) return;

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: color + '33',
                    borderColor: color,
                    borderWidth: 2,
                    borderRadius: 4,
                    barThickness: 18,
                }]
            },
            options: defaultOptions
        });
    }

    createChart('chartSubResellers', chartData.sub_resellers.labels, chartData.sub_resellers.data, '#a855f7');
    createChart('chartClients', chartData.clients.labels, chartData.clients.data, '#3b82f6');
    createChart('chartCreditsSold', chartData.credits_sold.labels, chartData.credits_sold.data, '#22c55e');
</script>
@endpush
@endsection
