@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
@if(Auth::user()->isAdmin())
<!-- Estat&iacute;sticas do Servidor Main -->
<div class="bg-white dark:bg-dark-300 rounded-xl p-6 mb-8 shadow-sm dark:shadow-2xl border border-gray-200 dark:border-0 hover:border-orange-500 transition-all duration-300">
    <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-100 dark:border-dark-200">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg flex items-center justify-center shadow-lg shadow-orange-500/20">
                <i class="bi bi-hdd-rack text-white text-2xl"></i>
            </div>
            <div>
                <p class="text-orange-600 dark:text-orange-500 text-xs font-bold uppercase tracking-wider mb-1">🖥️ Servidor Principal (Main)</p>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['main_server']['server_name'] ?? 'Main Server' }}</h2>
            </div>
        </div>
        <div class="flex items-center gap-2">
            @if(isset($stats['main_server']['status']) && $stats['main_server']['status'] === 'online')
                <span class="px-4 py-2 bg-green-100 dark:bg-green-500/10 text-green-700 dark:text-green-400 rounded-lg font-bold flex items-center gap-2 border border-green-200 dark:border-green-500/30">
                    <span class="w-2.5 h-2.5 bg-green-500 dark:bg-green-400 rounded-full animate-pulse shadow-lg shadow-green-500/50"></span>
                    Online
                </span>
            @else
                <span class="px-4 py-2 bg-red-100 dark:bg-red-500/10 text-red-700 dark:text-red-400 rounded-lg font-bold flex items-center gap-2 border border-red-200 dark:border-red-500/30">
                    <span class="w-2.5 h-2.5 bg-red-500 dark:bg-red-400 rounded-full"></span>
                    Offline
                </span>
            @endif
        </div>
    </div>

    @if(isset($stats['main_server']['status']) && $stats['main_server']['status'] === 'online')
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
        <!-- Connections -->
        <div class="bg-gray-50 dark:bg-dark-200 rounded-lg p-4 border-l-4 border-orange-500 hover:bg-gray-100 dark:hover:bg-dark-100 transition-all duration-200">
            <p class="text-gray-500 dark:text-gray-400 text-xs font-medium mb-2">Conex&otilde;es</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['main_server']['connections']) }}</p>
        </div>
        
        <!-- Users Online -->
        <div class="bg-gray-50 dark:bg-dark-200 rounded-lg p-4 border-l-4 border-green-500 hover:bg-gray-100 dark:hover:bg-dark-100 transition-all duration-200">
            <p class="text-gray-500 dark:text-gray-400 text-xs font-medium mb-2">Usu&aacute;rios Online</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['main_server']['users']) }}</p>
        </div>
        
        <!-- CPU -->
        <div class="bg-gray-50 dark:bg-dark-200 rounded-lg p-4 border-l-4 border-blue-500 hover:bg-gray-100 dark:hover:bg-dark-100 transition-all duration-200">
            <p class="text-gray-500 dark:text-gray-400 text-xs font-medium mb-2">CPU</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['main_server']['cpu'], 1) }}<span class="text-lg text-gray-500 dark:text-gray-400">%</span></p>
        </div>
        
        <!-- Memory -->
        <div class="bg-gray-50 dark:bg-dark-200 rounded-lg p-4 border-l-4 border-purple-500 hover:bg-gray-100 dark:hover:bg-dark-100 transition-all duration-200">
            <p class="text-gray-500 dark:text-gray-400 text-xs font-medium mb-2">Mem&oacute;ria</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['main_server']['mem'], 1) }}<span class="text-lg text-gray-500 dark:text-gray-400">%</span></p>
        </div>
        
        <!-- Disk -->
        <div class="bg-gray-50 dark:bg-dark-200 rounded-lg p-4 border-l-4 border-yellow-500 hover:bg-gray-100 dark:hover:bg-dark-100 transition-all duration-200">
            <p class="text-gray-500 dark:text-gray-400 text-xs font-medium mb-2">Disco</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['main_server']['disk_usage'], 1) }}<span class="text-lg text-gray-500 dark:text-gray-400">%</span></p>
        </div>
        
        <!-- Streams Live -->
        <div class="bg-gray-50 dark:bg-dark-200 rounded-lg p-4 border-l-4 border-pink-500 hover:bg-gray-100 dark:hover:bg-dark-100 transition-all duration-200">
            <p class="text-gray-500 dark:text-gray-400 text-xs font-medium mb-2">Streams Live</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['main_server']['streams_live']) }}</p>
        </div>
        
        <!-- Input -->
        <div class="bg-gray-50 dark:bg-dark-200 rounded-lg p-4 border-l-4 border-cyan-500 hover:bg-gray-100 dark:hover:bg-dark-100 transition-all duration-200">
            <p class="text-gray-500 dark:text-gray-400 text-xs font-medium mb-2">Input</p>
            <p class="text-xl font-bold text-gray-900 dark:text-white">{{ $stats['main_server']['input_mbps'] }} <span class="text-sm text-gray-500 dark:text-gray-400">Mbps</span></p>
        </div>
        
        <!-- Output -->
        <div class="bg-gray-50 dark:bg-dark-200 rounded-lg p-4 border-l-4 border-indigo-500 hover:bg-gray-100 dark:hover:bg-dark-100 transition-all duration-200">
            <p class="text-gray-500 dark:text-gray-400 text-xs font-medium mb-2">Output</p>
            <p class="text-xl font-bold text-gray-900 dark:text-white">{{ $stats['main_server']['output_mbps'] }} <span class="text-sm text-gray-500 dark:text-gray-400">Mbps</span></p>
        </div>
        
        <!-- Requests/sec -->
        <div class="bg-gray-50 dark:bg-dark-200 rounded-lg p-4 border-l-4 border-teal-500 hover:bg-gray-100 dark:hover:bg-dark-100 transition-all duration-200">
            <p class="text-gray-500 dark:text-gray-400 text-xs font-medium mb-2">Requests/sec</p>
            <p class="text-xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['main_server']['requests_sec']) }}</p>
        </div>
        
        <!-- IO Wait -->
        <div class="bg-gray-50 dark:bg-dark-200 rounded-lg p-4 border-l-4 border-red-500 hover:bg-gray-100 dark:hover:bg-dark-100 transition-all duration-200">
            <p class="text-gray-500 dark:text-gray-400 text-xs font-medium mb-2">IO Wait</p>
            <p class="text-xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['main_server']['io_wait'], 1) }}<span class="text-lg text-gray-500 dark:text-gray-400">%</span></p>
        </div>
        
        <!-- Uptime -->
        <div class="bg-gray-50 dark:bg-dark-200 rounded-lg p-4 border-l-4 border-emerald-500 hover:bg-gray-100 dark:hover:bg-dark-100 transition-all duration-200 col-span-2">
            <p class="text-gray-500 dark:text-gray-400 text-xs font-medium mb-2">Uptime</p>
            <p class="text-xl font-bold text-gray-900 dark:text-white">{{ $stats['main_server']['uptime'] }}</p>
        </div>
    </div>
    @else
    <div class="text-center py-8">
        <p class="text-gray-400">Servidor offline ou sem dados dispon&iacute;veis</p>
    </div>
    @endif
</div>

<!-- Load Balancers -->
<div class="mb-8">
    <h3 class="text-gray-900 dark:text-white text-xl font-bold mb-4 flex items-center gap-2">
        <i class="bi bi-hdd-network text-orange-500"></i>
        Status dos Load Balancers
    </h3>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        @forelse($stats['load_balancers'] as $server)
        <div class="bg-white dark:bg-dark-300 rounded-xl p-5 border border-gray-200 dark:border-0 shadow-sm dark:shadow-none hover:border-orange-500 transition-all duration-300">
            <div class="flex items-center justify-between mb-3">
                <h4 class="text-gray-900 dark:text-white font-bold text-lg">{{ $server->server_name }}</h4>
                @if($server->isOnline())
                    <span class="px-3 py-1 bg-green-100 dark:bg-green-500/10 text-green-700 dark:text-green-400 text-xs font-semibold rounded-full flex items-center gap-1">
                        <span class="w-1.5 h-1.5 bg-green-500 dark:bg-green-400 rounded-full animate-pulse"></span>
                        Online
                    </span>
                @else
                    <span class="px-3 py-1 bg-red-100 dark:bg-red-500/10 text-red-700 dark:text-red-400 text-xs font-semibold rounded-full flex items-center gap-1">
                        <span class="w-1.5 h-1.5 bg-red-500 dark:bg-red-400 rounded-full"></span>
                        Offline
                    </span>
                @endif
            </div>
            <div class="space-y-2">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500 dark:text-gray-400">IP:</span>
                    <span class="text-gray-700 dark:text-white font-mono">{{ $server->server_ip }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500 dark:text-gray-400">Tipo:</span>
                    <span class="text-orange-500 dark:text-orange-400 font-semibold">
                        {{ $server->is_main == 1 ? 'Main' : 'Load Balancer' }}
                    </span>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-8 bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200">
            <p class="text-gray-500">Nenhum Load Balancer configurado</p>
        </div>
        @endforelse
    </div>
</div>

<!-- Estat&iacute;sticas de Conte&uacute;do -->
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6 mb-8">
    <!-- Total de Canais -->
    <div class="bg-white dark:bg-dark-300 rounded-xl p-6 border border-gray-200 dark:border-0 shadow-sm dark:shadow-none hover:border-orange-500 transition-all duration-300">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-orange-100 dark:bg-orange-500/10 rounded-lg flex items-center justify-center">
                <i class="bi bi-tv text-orange-600 dark:text-orange-500 text-2xl"></i>
            </div>
        </div>
        <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-1">{{ number_format($stats['live_channels']) }}</h3>
        <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Total de Canais</p>
    </div>

    <!-- Canais Online -->
    <div class="bg-white dark:bg-dark-300 rounded-xl p-6 border border-gray-200 dark:border-0 shadow-sm dark:shadow-none hover:border-green-500 transition-all duration-300">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-green-100 dark:bg-green-500/10 rounded-lg flex items-center justify-center">
                <i class="bi bi-broadcast text-green-600 dark:text-green-500 text-2xl"></i>
            </div>
        </div>
        <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-1">{{ number_format($stats['online_streams']) }}</h3>
        <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Canais Online</p>
    </div>

    <!-- Canais Offline -->
    <div class="bg-white dark:bg-dark-300 rounded-xl p-6 border border-gray-200 dark:border-0 shadow-sm dark:shadow-none hover:border-red-500 transition-all duration-300">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-red-100 dark:bg-red-500/10 rounded-lg flex items-center justify-center">
                <i class="bi bi-broadcast-pin text-red-600 dark:text-red-500 text-2xl"></i>
            </div>
        </div>
        <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-1">{{ number_format($stats['offline_streams']) }}</h3>
        <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Canais Offline</p>
    </div>

    <!-- Total de Filmes -->
    <div class="bg-white dark:bg-dark-300 rounded-xl p-6 border border-gray-200 dark:border-0 shadow-sm dark:shadow-none hover:border-purple-500 transition-all duration-300">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-purple-100 dark:bg-purple-500/10 rounded-lg flex items-center justify-center">
                <i class="bi bi-film text-purple-600 dark:text-purple-500 text-2xl"></i>
            </div>
        </div>
        <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-1">{{ number_format($stats['movies']) }}</h3>
        <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Total de Filmes</p>
    </div>

    <!-- Total de S&eacute;ries -->
    <div class="bg-white dark:bg-dark-300 rounded-xl p-6 border border-gray-200 dark:border-0 shadow-sm dark:shadow-none hover:border-blue-500 transition-all duration-300">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-blue-100 dark:bg-blue-500/10 rounded-lg flex items-center justify-center">
                <i class="bi bi-collection-play text-blue-600 dark:text-blue-500 text-2xl"></i>
            </div>
        </div>
        <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-1">{{ number_format($stats['series']) }}</h3>
        <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Total de S&eacute;ries</p>
    </div>
</div>

<!-- Estat&iacute;sticas de Clientes -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Total Revendas -->
    <div class="bg-white dark:bg-dark-300 rounded-xl p-6 border border-gray-200 dark:border-0 shadow-sm dark:shadow-none hover:border-orange-500 transition-all duration-300">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-orange-100 dark:bg-orange-500/10 rounded-lg flex items-center justify-center">
                <i class="bi bi-shop text-orange-600 dark:text-orange-500 text-2xl"></i>
            </div>
        </div>
        <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-1">{{ $stats['total_resellers'] }}</h3>
        <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Total de Revendas</p>
    </div>

    <!-- Linhas Ativas -->
    <div class="bg-white dark:bg-dark-300 rounded-xl p-6 border border-gray-200 dark:border-0 shadow-sm dark:shadow-none hover:border-green-500 transition-all duration-300">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-green-100 dark:bg-green-500/10 rounded-lg flex items-center justify-center">
                <i class="bi bi-check-circle text-green-600 dark:text-green-500 text-2xl"></i>
            </div>
        </div>
        <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-1">{{ $stats['active_clients'] }}</h3>
        <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Linhas Ativas</p>
    </div>

    <!-- Linhas Vencidas -->
    <div class="bg-white dark:bg-dark-300 rounded-xl p-6 border border-gray-200 dark:border-0 shadow-sm dark:shadow-none hover:border-red-500 transition-all duration-300">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-red-100 dark:bg-red-500/10 rounded-lg flex items-center justify-center">
                <i class="bi bi-x-circle text-red-600 dark:text-red-500 text-2xl"></i>
            </div>
        </div>
        <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-1">{{ $stats['expired_clients'] }}</h3>
        <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Linhas Vencidas</p>
    </div>

    <!-- Conex&otilde;es Online -->
    <div class="bg-white dark:bg-dark-300 rounded-xl p-6 border border-gray-200 dark:border-0 shadow-sm dark:shadow-none hover:border-blue-500 transition-all duration-300">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-blue-100 dark:bg-blue-500/10 rounded-lg flex items-center justify-center">
                <i class="bi bi-broadcast-pin text-blue-600 dark:text-blue-500 text-2xl"></i>
            </div>
        </div>
        <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-1">{{ $stats['online_now'] }}</h3>
        <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Conex&otilde;es Online</p>
    </div>
</div>
@else
<!-- Saldo em Destaque - Apenas para Revendas -->
<div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-xl p-8 mb-8 shadow-lg shadow-orange-500/20">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-orange-100 text-sm font-medium mb-2">💰 MEU SALDO ATUAL</p>
            <h2 class="text-5xl font-bold text-white">{{ number_format($balance, 2, ',', '.') }}</h2>
            <p class="text-orange-100 text-sm mt-2">Cr&eacute;ditos dispon&iacute;veis</p>
        </div>
        <div class="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center">
            <i class="bi bi-wallet2 text-white text-4xl"></i>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <!-- Meus Clientes -->
    <div class="bg-white dark:bg-dark-300 rounded-xl p-6 border border-gray-200 dark:border-dark-200 hover:border-orange-500/50 transition-all duration-300 shadow-sm dark:shadow-none">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-orange-100 dark:bg-orange-500/10 rounded-lg flex items-center justify-center">
                <i class="bi bi-people text-orange-600 dark:text-orange-500 text-2xl"></i>
            </div>
        </div>
        <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-1">{{ $stats['total_clients'] }}</h3>
        <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Clientes (Oficial)</p>
    </div>

    <!-- Minhas Revendas -->
    <div class="bg-white dark:bg-dark-300 rounded-xl p-6 border border-gray-200 dark:border-dark-200 hover:border-purple-500/50 transition-all duration-300 shadow-sm dark:shadow-none">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-purple-100 dark:bg-purple-500/10 rounded-lg flex items-center justify-center">
                <i class="bi bi-shop text-purple-600 dark:text-purple-500 text-2xl"></i>
            </div>
        </div>
        <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-1">{{ $stats['my_resellers'] }}</h3>
        <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Minhas Revendas</p>
    </div>

    <!-- Online Agora -->
    <div class="bg-white dark:bg-dark-300 rounded-xl p-6 border border-gray-200 dark:border-dark-200 hover:border-green-500/50 transition-all duration-300 shadow-sm dark:shadow-none">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-green-100 dark:bg-green-500/10 rounded-lg flex items-center justify-center">
                <i class="bi bi-broadcast-pin text-green-600 dark:text-green-500 text-2xl"></i>
            </div>
        </div>
        <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-1">{{ $stats['online_now'] }}</h3>
        <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Online Agora 🟢</p>
    </div>
</div>
@endif

@if(Auth::user()->isAdmin())
<!-- Top Revendas -->
<div class="mb-8">
    <h3 class="text-gray-900 dark:text-white text-xl font-bold mb-4 flex items-center gap-2">
        <i class="bi bi-trophy text-orange-500"></i>
        Top Revendas
    </h3>
    <div class="bg-white dark:bg-dark-300 rounded-xl p-6 border border-gray-200 dark:border-0 shadow-sm dark:shadow-none hover:border-orange-500 transition-all duration-300">
        <div class="space-y-3">
            @forelse($stats['top_resellers'] as $index => $reseller)
            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-dark-200 rounded-lg hover:bg-gray-100 dark:hover:bg-dark-100 transition-all duration-200">
                <div class="flex items-center gap-3">
                    <span class="w-10 h-10 bg-gradient-to-br from-orange-500 to-orange-600 rounded-full flex items-center justify-center text-white font-bold shadow-lg">
                        {{ $index + 1 }}
                    </span>
                    <span class="text-gray-900 dark:text-white font-semibold text-lg">{{ $reseller['username'] }}</span>
                </div>
                <div class="text-right">
                    <span class="text-orange-600 dark:text-orange-500 font-bold text-lg">{{ $reseller['total_lines'] }} <span class="text-sm text-gray-500 dark:text-gray-400">clientes</span></span>
                    <p class="text-green-600 dark:text-green-500 text-sm font-semibold">{{ number_format($reseller['credits'], 2, ',', '.') }} cr&eacute;ditos</p>
                </div>
            </div>
            @empty
            <p class="text-gray-500 text-center py-8">Nenhuma revenda encontrada</p>
            @endforelse
        </div>
    </div>
</div>
@else
<!-- Resumo Financeiro e A&ccedil;&otilde;es R&aacute;pidas - Apenas para Revendas -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Resumo Financeiro -->
    <div class="bg-white dark:bg-dark-300 rounded-xl p-6 border border-gray-200 dark:border-dark-200 shadow-sm dark:shadow-none">
        <h3 class="text-gray-900 dark:text-white text-lg font-bold mb-6 flex items-center gap-2">
            <i class="bi bi-graph-up text-orange-500"></i>
            Resumo Financeiro
        </h3>
        <div class="space-y-4">
            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-dark-200 rounded-lg">
                <span class="text-gray-500 dark:text-gray-400">Saldo Atual</span>
                <span class="text-2xl font-bold text-orange-500">{{ number_format($balance, 2, ',', '.') }}</span>
            </div>
            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-dark-200 rounded-lg">
                <span class="text-gray-500 dark:text-gray-400">Clientes Ativos</span>
                <span class="text-xl font-bold text-gray-900 dark:text-white">{{ $stats['active_clients'] }}</span>
            </div>
            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-dark-200 rounded-lg">
                <span class="text-gray-500 dark:text-gray-400">Taxa de Ativa&ccedil;&atilde;o</span>
                <span class="text-xl font-bold text-green-600 dark:text-green-500">
                    @if($stats['total_clients'] > 0)
                        {{ number_format(($stats['active_clients'] / $stats['total_clients']) * 100, 1) }}%
                    @else
                        0%
                    @endif
                </span>
            </div>
        </div>
    </div>

    <!-- A&ccedil;&otilde;es R&aacute;pidas -->
    <div class="bg-white dark:bg-dark-300 rounded-xl p-6 border border-gray-200 dark:border-dark-200 shadow-sm dark:shadow-none">
        <h3 class="text-gray-900 dark:text-white text-lg font-bold mb-6 flex items-center gap-2">
            <i class="bi bi-lightning-charge text-orange-500"></i>
            A&ccedil;&otilde;es R&aacute;pidas
        </h3>
        <div class="space-y-3">
            <a href="{{ route('clients.index') }}" class="flex items-center gap-3 p-4 bg-gray-50 dark:bg-dark-200 rounded-lg hover:bg-gray-100 dark:hover:bg-dark-100 transition-colors duration-200 group">
                <i class="bi bi-list-ul text-orange-500 text-xl"></i>
                <span class="text-gray-600 dark:text-gray-300 group-hover:text-gray-900 dark:group-hover:text-white transition-colors duration-200">Ver Todos os Clientes</span>
            </a>
            <a href="{{ route('monitor.index') }}" class="flex items-center gap-3 p-4 bg-gray-50 dark:bg-dark-200 rounded-lg hover:bg-gray-100 dark:hover:bg-dark-100 transition-colors duration-200 group">
                <i class="bi bi-broadcast text-orange-500 text-xl"></i>
                <span class="text-gray-600 dark:text-gray-300 group-hover:text-gray-900 dark:group-hover:text-white transition-colors duration-200">Monitorar Conex&otilde;es</span>
            </a>
        </div>
    </div>
</div>
@endif

<!-- System Info -->
<div class="mt-6 bg-white dark:bg-dark-300 rounded-xl p-6 border border-gray-200 dark:border-dark-200 shadow-sm dark:shadow-none">
    <h3 class="text-gray-900 dark:text-white text-lg font-bold mb-6 flex items-center gap-2">
        <i class="bi bi-info-circle text-orange-500"></i>
        Informa&ccedil;&otilde;es do Sistema
    </h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="text-center p-4">
            <i class="bi bi-shield-check text-green-500 text-4xl mb-3"></i>
            <p class="text-gray-500 dark:text-gray-400 text-sm mb-1">Sistema Seguro</p>
            <p class="text-gray-900 dark:text-white font-semibold">Autentica&ccedil;&atilde;o XUI</p>
        </div>
        <div class="text-center p-4">
            <i class="bi bi-cloud-check text-orange-500 text-4xl mb-3"></i>
            <p class="text-gray-500 dark:text-gray-400 text-sm mb-1">API Conectada</p>
            <p class="text-gray-900 dark:text-white font-semibold">Status: Online</p>
        </div>
        <div class="text-center p-4">
            <i class="bi bi-speedometer2 text-blue-500 text-4xl mb-3"></i>
            <p class="text-gray-500 dark:text-gray-400 text-sm mb-1">Performance</p>
            <p class="text-gray-900 dark:text-white font-semibold">Otimizado</p>
        </div>
    </div>
</div>
@endsection
