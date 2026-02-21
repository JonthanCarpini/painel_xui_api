@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
@if(Auth::user()->isAdmin())
<!-- Estat&iacute;sticas do Servidor Main -->
<div class="bg-white dark:bg-dark-300 rounded-xl p-6 mb-8 shadow-sm dark:shadow-2xl border border-gray-200 dark:border-0 hover:border-orange-500 transition-all duration-300">
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-6 pb-4 border-b border-gray-100 dark:border-dark-200 gap-4">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg flex items-center justify-center shadow-lg shadow-orange-500/20 shrink-0">
                <i class="bi bi-hdd-rack text-white text-2xl"></i>
            </div>
            <div>
                <p class="text-orange-600 dark:text-orange-500 text-xs font-bold uppercase tracking-wider mb-1">🖥️ Servidor Principal (Main)</p>
                <h2 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white truncate">{{ $stats['main_server']['server_name'] ?? 'Main Server' }}</h2>
            </div>
        </div>
        <div class="flex items-center gap-2 w-full md:w-auto">
            @if(isset($stats['main_server']['status']) && $stats['main_server']['status'] === 'online')
                <span class="w-full md:w-auto justify-center px-4 py-2 bg-green-100 dark:bg-green-500/10 text-green-700 dark:text-green-400 rounded-lg font-bold flex items-center gap-2 border border-green-200 dark:border-green-500/30">
                    <span class="w-2.5 h-2.5 bg-green-500 dark:bg-green-400 rounded-full animate-pulse shadow-lg shadow-green-500/50"></span>
                    Online
                </span>
            @else
                <span class="w-full md:w-auto justify-center px-4 py-2 bg-red-100 dark:bg-red-500/10 text-red-700 dark:text-red-400 rounded-lg font-bold flex items-center gap-2 border border-red-200 dark:border-red-500/30">
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
<!-- Saldo e Ações Rápidas -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <!-- Card Saldo -->
    <div class="md:col-span-2 bg-gradient-to-r from-orange-500 to-orange-600 rounded-xl p-8 shadow-lg shadow-orange-500/20 relative overflow-hidden">
        <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="text-center md:text-left">
                <p class="text-orange-100 text-sm font-bold uppercase tracking-wider mb-2">💰 Meu Saldo Atual</p>
                <h2 class="text-5xl font-bold text-white mb-2">{{ number_format($balance, 2, ',', '.') }}</h2>
                <p class="text-orange-100 text-sm">Cr&eacute;ditos dispon&iacute;veis para uso imediato</p>
            </div>
            
            <!-- Botões de Ação Rápida (Integrados ao Card de Saldo) -->
            <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
                <button onclick="openTrialModal()" class="px-6 py-3 bg-white/20 hover:bg-white/30 text-white rounded-lg backdrop-blur-sm transition-all font-bold flex items-center justify-center gap-2 border border-white/30 shadow-lg">
                    <i class="bi bi-clock-history"></i>
                    Teste Rápido
                </button>
                <a href="{{ route('clients.create') }}" class="px-6 py-3 bg-white text-orange-600 hover:bg-orange-50 rounded-lg transition-all font-bold flex items-center justify-center gap-2 shadow-lg">
                    <i class="bi bi-person-plus-fill"></i>
                    Novo Cliente
                </a>
            </div>
        </div>
        
        <!-- Decoration -->
        <div class="absolute right-0 top-0 h-full w-1/3 bg-white/5 skew-x-12 pointer-events-none"></div>
        <div class="absolute -bottom-10 -left-10 w-40 h-40 bg-white/10 rounded-full blur-3xl pointer-events-none"></div>
    </div>

    <!-- Card Online Agora (Movido para cá para balancear o layout) -->
    <div class="bg-white dark:bg-dark-300 rounded-xl p-6 border border-gray-200 dark:border-dark-200 shadow-sm dark:shadow-none flex flex-col justify-center items-center text-center hover:border-green-500/50 transition-all">
        <div class="w-16 h-16 bg-green-100 dark:bg-green-500/10 rounded-full flex items-center justify-center mb-4">
            <i class="bi bi-broadcast-pin text-green-600 dark:text-green-500 text-3xl animate-pulse"></i>
        </div>
        <h3 class="text-4xl font-bold text-gray-900 dark:text-white mb-1">{{ $stats['online_now'] }}</h3>
        <p class="text-gray-500 dark:text-gray-400 text-sm font-bold uppercase tracking-wide">Usuários Online</p>
    </div>
</div>

<!-- Card Unificado: Estatísticas e Carteira -->
<div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 shadow-sm dark:shadow-none mb-8 overflow-hidden">
    <div class="p-6 border-b border-gray-200 dark:border-dark-200 bg-gray-50/50 dark:bg-dark-200/50">
        <h3 class="text-gray-900 dark:text-white text-lg font-bold flex items-center gap-2">
            <i class="bi bi-bar-chart-fill text-blue-500"></i>
            Visão Geral da Carteira e Vendas
        </h3>
    </div>
    
    <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Vendas Hoje -->
        <div class="p-4 rounded-xl bg-green-50 dark:bg-green-900/10 border border-green-100 dark:border-green-900/20">
            <div class="flex justify-between items-start mb-2">
                <div>
                    <p class="text-xs font-bold text-green-600 dark:text-green-400 uppercase tracking-wide">Vendas Hoje</p>
                    <h4 class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $stats['sales_today'] ?? 0 }}</h4>
                </div>
                <div class="p-2 bg-green-100 dark:bg-green-500/20 rounded-lg text-green-600 dark:text-green-400">
                    <i class="bi bi-cart-check"></i>
                </div>
            </div>
            <p class="text-xs text-green-600/80 dark:text-green-400/70">Novos clientes oficiais</p>
        </div>

        <!-- Vendas Mês -->
        <div class="p-4 rounded-xl bg-blue-50 dark:bg-blue-900/10 border border-blue-100 dark:border-blue-900/20">
            <div class="flex justify-between items-start mb-2">
                <div>
                    <p class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wide">Vendas Mês</p>
                    <h4 class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $stats['sales_month'] ?? 0 }}</h4>
                </div>
                <div class="p-2 bg-blue-100 dark:bg-blue-500/20 rounded-lg text-blue-600 dark:text-blue-400">
                    <i class="bi bi-calendar-check"></i>
                </div>
            </div>
            <p class="text-xs text-blue-600/80 dark:text-blue-400/70">Acumulado mensal</p>
        </div>

        <!-- Testes Hoje -->
        <div class="p-4 rounded-xl bg-purple-50 dark:bg-purple-900/10 border border-purple-100 dark:border-purple-900/20">
            <div class="flex justify-between items-start mb-2">
                <div>
                    <p class="text-xs font-bold text-purple-600 dark:text-purple-400 uppercase tracking-wide">Testes Hoje</p>
                    <h4 class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $stats['trials_today'] ?? 0 }}</h4>
                </div>
                <div class="p-2 bg-purple-100 dark:bg-purple-500/20 rounded-lg text-purple-600 dark:text-purple-400">
                    <i class="bi bi-clock-history"></i>
                </div>
            </div>
            <p class="text-xs text-purple-600/80 dark:text-purple-400/70">Testes gerados</p>
        </div>

        <!-- Testes Mês -->
        <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-700/10 border border-gray-100 dark:border-gray-700/20">
            <div class="flex justify-between items-start mb-2">
                <div>
                    <p class="text-xs font-bold text-gray-600 dark:text-gray-400 uppercase tracking-wide">Testes Mês</p>
                    <h4 class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $stats['trials_month'] ?? 0 }}</h4>
                </div>
                <div class="p-2 bg-gray-100 dark:bg-gray-600/20 rounded-lg text-gray-600 dark:text-gray-400">
                    <i class="bi bi-calendar-range"></i>
                </div>
            </div>
            <p class="text-xs text-gray-600/80 dark:text-gray-400/70">Acumulado mensal</p>
        </div>
    </div>

    <!-- Divisor -->
    <div class="h-px bg-gray-100 dark:bg-dark-200 mx-6"></div>

    <div class="p-6">
        <h4 class="text-sm font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">Status da Carteira de Clientes</h4>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="text-center p-3 rounded-lg bg-gray-50 dark:bg-dark-200">
                <span class="block text-2xl font-bold text-gray-900 dark:text-white mb-1">{{ $stats['total_clients'] ?? 0 }}</span>
                <span class="text-xs font-medium text-gray-500">Total Clientes</span>
            </div>
            <div class="text-center p-3 rounded-lg bg-green-50 dark:bg-green-900/10">
                <span class="block text-2xl font-bold text-green-600 dark:text-green-400 mb-1">{{ $stats['active_clients'] ?? 0 }}</span>
                <span class="text-xs font-medium text-green-600/80 dark:text-green-400/80">Ativos</span>
            </div>
            <div class="text-center p-3 rounded-lg bg-red-50 dark:bg-red-900/10">
                <span class="block text-2xl font-bold text-red-600 dark:text-red-400 mb-1">{{ $stats['expired_clients'] ?? 0 }}</span>
                <span class="text-xs font-medium text-red-600/80 dark:text-red-400/80">Vencidos</span>
            </div>
            <div class="text-center p-3 rounded-lg bg-yellow-50 dark:bg-yellow-900/10">
                <span class="block text-2xl font-bold text-yellow-600 dark:text-yellow-400 mb-1">{{ $stats['expiring_today'] ?? 0 }}</span>
                <span class="text-xs font-medium text-yellow-600/80 dark:text-yellow-400/80">Vence Hoje</span>
            </div>
        </div>
    </div>
</div>

<!-- Gráficos e Lista de Vencimentos -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <!-- Coluna dos Gráficos (Ocupa 2/3) -->
    <div class="lg:col-span-2 space-y-6">
        @if(isset($charts))
        
        <div class="grid grid-cols-1 gap-6">
            <!-- Gráfico Clientes x Testes -->
            <div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 shadow-sm dark:shadow-none p-6">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-gray-900 dark:text-white text-sm font-bold flex items-center gap-2">
                        <i class="bi bi-people text-orange-500"></i>
                        Clientes vs Testes
                    </h4>
                    <div class="bg-gray-100 dark:bg-dark-200 p-1 rounded-lg flex text-xs font-bold">
                        <button onclick="updateClientChart('7')" id="btnClient7" class="px-3 py-1.5 rounded-md bg-white dark:bg-dark-300 text-orange-600 dark:text-orange-500 shadow-sm transition-all">7 Dias</button>
                        <button onclick="updateClientChart('30')" id="btnClient30" class="px-3 py-1.5 rounded-md text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-all">30 Dias</button>
                    </div>
                </div>
                <div class="relative h-64 w-full">
                    <canvas id="clientsChart"></canvas>
                </div>
            </div>

            <!-- Gráfico Revendas x Recargas -->
            <div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 shadow-sm dark:shadow-none p-6">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-gray-900 dark:text-white text-sm font-bold flex items-center gap-2">
                        <i class="bi bi-shop text-blue-500"></i>
                        Novas Revendas vs Recargas
                    </h4>
                    <div class="bg-gray-100 dark:bg-dark-200 p-1 rounded-lg flex text-xs font-bold">
                        <button onclick="updateResellerChart('7')" id="btnReseller7" class="px-3 py-1.5 rounded-md bg-white dark:bg-dark-300 text-blue-600 dark:text-blue-500 shadow-sm transition-all">7 Dias</button>
                        <button onclick="updateResellerChart('30')" id="btnReseller30" class="px-3 py-1.5 rounded-md text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-all">30 Dias</button>
                    </div>
                </div>
                <div class="relative h-64 w-full">
                    <canvas id="resellersChart"></canvas>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Coluna de Vencimentos (Ocupa 1/3) -->
    <div class="lg:col-span-1">
        <div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 shadow-sm dark:shadow-none h-full flex flex-col">
            <div class="p-6 border-b border-gray-200 dark:border-dark-200 bg-gray-50/50 dark:bg-dark-200/50">
                <h3 class="text-gray-900 dark:text-white text-lg font-bold flex items-center gap-2">
                    <i class="bi bi-calendar-event text-red-500"></i>
                    Vencem em 7 Dias
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Próximos vencimentos</p>
            </div>
            
            <div class="flex-1 p-0 overflow-hidden relative" style="min-height: 400px; max-height: 600px;">
                <div class="absolute inset-0 overflow-y-auto custom-scrollbar">
                    @if(isset($expiringClients) && count($expiringClients) > 0)
                        <div class="divide-y divide-gray-100 dark:divide-dark-100">
                            @foreach($expiringClients as $client)
                            <div class="p-4 hover:bg-gray-50 dark:hover:bg-dark-200/50 transition-colors">
                                <div class="flex items-start justify-between mb-1">
                                    <div class="font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                        <i class="bi bi-person text-gray-400"></i>
                                        {{ $client->username }}
                                    </div>
                                    @php
                                        $daysLeft = ceil(($client->exp_date - time()) / 86400);
                                        $colorClass = $daysLeft <= 1 ? 'text-red-600 bg-red-50 dark:bg-red-900/20' : ($daysLeft <= 3 ? 'text-orange-600 bg-orange-50 dark:bg-orange-900/20' : 'text-yellow-600 bg-yellow-50 dark:bg-yellow-900/20');
                                    @endphp
                                    <span class="text-xs font-bold px-2 py-0.5 rounded-full {{ $colorClass }}">
                                        {{ $daysLeft }} dia(s)
                                    </span>
                                </div>
                                <div class="flex justify-between items-center text-xs text-gray-500 dark:text-gray-400">
                                    <span>{{ date('d/m/Y H:i', $client->exp_date) }}</span>
                                    @if($client->member)
                                    <span class="flex items-center gap-1" title="Revendedor">
                                        <i class="bi bi-shop"></i> {{ $client->member->username }}
                                    </span>
                                    @endif
                                </div>
                                @if($client->admin_notes)
                                <div class="mt-2 text-xs bg-gray-50 dark:bg-dark-200 p-1.5 rounded border border-gray-100 dark:border-dark-100 text-gray-600 dark:text-gray-300 truncate">
                                    <i class="bi bi-telephone text-[10px] mr-1"></i> {{ $client->admin_notes }}
                                </div>
                                @endif
                                <div class="mt-2 flex gap-2">
                                    <button onclick="window.location.href='{{ route('clients.index', ['search' => $client->username]) }}'" class="flex-1 py-1 bg-blue-50 dark:bg-blue-900/10 text-blue-600 dark:text-blue-400 rounded hover:bg-blue-100 dark:hover:bg-blue-900/20 text-xs font-bold transition-colors">
                                        Gerenciar
                                    </button>
                                    <button onclick="window.open('https://wa.me/{{ preg_replace('/[^0-9]/', '', $client->admin_notes) }}?text=Olá {{ $client->username }}, sua assinatura vence em {{ $daysLeft }} dias. Vamos renovar?', '_blank')" class="flex-1 py-1 bg-green-50 dark:bg-green-900/10 text-green-600 dark:text-green-400 rounded hover:bg-green-100 dark:hover:bg-green-900/20 text-xs font-bold transition-colors {{ !$client->admin_notes ? 'opacity-50 cursor-not-allowed' : '' }}" {{ !$client->admin_notes ? 'disabled' : '' }}>
                                        WhatsApp
                                    </button>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center h-full p-8 text-center text-gray-400">
                            <i class="bi bi-calendar-check text-4xl mb-3 opacity-30"></i>
                            <p class="text-sm">Nenhum cliente vencendo nos próximos 7 dias.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@if(($stats['my_resellers'] ?? 0) > 0)
<!-- Minhas Revendas & Recargas -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Card Estatísticas Revendas -->
    <div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 shadow-sm dark:shadow-none flex flex-col">
        <div class="p-6 border-b border-gray-200 dark:border-dark-200 bg-gray-50/50 dark:bg-dark-200/50">
            <h3 class="text-gray-900 dark:text-white text-lg font-bold flex items-center gap-2">
                <i class="bi bi-shop text-purple-500"></i>
                Minhas Revendas
            </h3>
        </div>
        
        <div class="p-6 grid grid-cols-2 gap-4">
            <div class="bg-purple-50 dark:bg-purple-900/10 p-4 rounded-xl text-center">
                <i class="bi bi-people-fill text-2xl text-purple-600 dark:text-purple-400 mb-2 block"></i>
                <span class="text-3xl font-bold text-gray-900 dark:text-white block">{{ $stats['my_resellers'] ?? 0 }}</span>
                <span class="text-xs text-gray-500 dark:text-gray-400 uppercase font-bold">Total Revendas</span>
            </div>
            
            <div class="bg-red-50 dark:bg-red-900/10 p-4 rounded-xl text-center">
                <i class="bi bi-slash-circle text-2xl text-red-600 dark:text-red-400 mb-2 block"></i>
                <span class="text-3xl font-bold text-gray-900 dark:text-white block">{{ $stats['inactive_resellers'] ?? 0 }}</span>
                <span class="text-xs text-gray-500 dark:text-gray-400 uppercase font-bold">Inativas (+30d)</span>
            </div>
            
            <div class="bg-yellow-50 dark:bg-yellow-900/10 p-4 rounded-xl text-center col-span-2">
                <div class="flex items-center justify-center gap-3">
                    <i class="bi bi-wallet2 text-2xl text-yellow-600 dark:text-yellow-400"></i>
                    <div class="text-left">
                        <span class="text-2xl font-bold text-gray-900 dark:text-white block leading-none">{{ $stats['resellers_no_credit'] ?? 0 }}</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400 uppercase font-bold">Revendas Sem Crédito</span>
                    </div>
                </div>
            </div>
        </div>

        @if(!empty($stats['top_resellers']))
        <div class="px-6 pb-6 mt-auto">
            <h4 class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Top 5 Revendas</h4>
            <div class="space-y-2">
                @foreach($stats['top_resellers'] as $reseller)
                <div class="flex justify-between items-center p-2 hover:bg-gray-50 dark:hover:bg-dark-200 rounded-lg transition-colors cursor-default">
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center text-xs font-bold">
                            {{ $loop->iteration }}
                        </div>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $reseller['username'] }}</span>
                    </div>
                    <div class="text-right">
                        <span class="text-xs font-bold bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 px-2 py-0.5 rounded-full">{{ $reseller['total_lines'] }} clientes</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <!-- Card Últimas Recargas -->
    <div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 shadow-sm dark:shadow-none flex flex-col h-full">
        <div class="p-6 border-b border-gray-200 dark:border-dark-200 bg-gray-50/50 dark:bg-dark-200/50 flex justify-between items-center">
            <h3 class="text-gray-900 dark:text-white text-lg font-bold flex items-center gap-2">
                <i class="bi bi-currency-exchange text-green-500"></i>
                Últimas Recargas Enviadas
            </h3>
            <a href="{{ route('credit-logs.index', ['nature' => 'out']) }}" class="text-xs font-bold text-blue-500 hover:text-blue-600">Ver todas</a>
        </div>
        
        <div class="p-0 flex-1 overflow-hidden">
            @if(!empty($stats['last_recharges']) && count($stats['last_recharges']) > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-gray-500 dark:text-gray-400 uppercase bg-gray-50 dark:bg-dark-200 border-b border-gray-100 dark:border-dark-100">
                        <tr>
                            <th class="px-6 py-3 font-medium">Data</th>
                            <th class="px-6 py-3 font-medium">Destino</th>
                            <th class="px-6 py-3 font-medium text-right">Valor</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-dark-100">
                        @foreach($stats['last_recharges'] as $log)
                        <tr class="hover:bg-gray-50 dark:hover:bg-dark-200/50 transition-colors">
                            <td class="px-6 py-3 whitespace-nowrap text-gray-600 dark:text-gray-400">
                                {{ date('d/m H:i', $log->date) }}
                            </td>
                            <td class="px-6 py-3 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                                        <i class="bi bi-person text-gray-500 text-xs"></i>
                                    </div>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $log->target->username ?? 'N/A' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-3 whitespace-nowrap text-right">
                                <span class="font-bold text-red-600 dark:text-red-400">
                                    -{{ number_format(abs($log->amount), 2, ',', '.') }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="flex flex-col items-center justify-center h-48 text-gray-400">
                <i class="bi bi-cash-stack text-4xl mb-2 opacity-50"></i>
                <p>Nenhuma recarga recente</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endif

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

<!-- Modal Create Trial (R&aacute;pido) -->
@if(isset($packages) && isset($bouquets))
<div id="trialModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white dark:bg-dark-300 rounded-xl max-w-4xl w-full border border-gray-200 dark:border-dark-200 shadow-2xl max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200 dark:border-dark-200 flex justify-between items-center sticky top-0 bg-white dark:bg-dark-300 z-10">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white">Gerar Teste R&aacute;pido</h3>
            <button onclick="closeTrialModal()" class="text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <form id="trialForm" onsubmit="submitTrial(event)" class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">Usu&aacute;rio *</label>
                    <div class="flex gap-2">
                        <input type="text" id="trialUsername" name="username" required class="flex-1 px-4 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white focus:border-orange-500 focus:outline-none transition-colors">
                        <button type="button" onclick="generateTrialUsername()" class="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors">
                            <i class="bi bi-shuffle"></i>
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">Senha *</label>
                    <div class="flex gap-2">
                        <input type="text" id="trialPassword" name="password" required class="flex-1 px-4 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white focus:border-orange-500 focus:outline-none transition-colors">
                        <button type="button" onclick="generateTrialPassword()" class="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors">
                            <i class="bi bi-shuffle"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">Telefone</label>
                    <input type="text" id="trialPhone" name="phone" oninput="maskPhone(event)" class="w-full px-4 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white focus:border-orange-500 focus:outline-none transition-colors" placeholder="(00) 00000-0000">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">Nota</label>
                    <input type="text" id="trialNotes" name="notes" class="w-full px-4 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white focus:border-orange-500 focus:outline-none transition-colors" placeholder="Opcional">
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">Pacote de Teste *</label>
                <select id="trialPackageId" name="package_id" required class="w-full px-4 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white focus:border-orange-500 focus:outline-none transition-colors" onchange="updateTrialPackage(this)">
                    <option value="">Selecione um pacote de teste</option>
                    @foreach($packages as $package)
                        @if($package->is_trial == 1)
                            <option value="{{ $package->id }}" 
                                    data-duration="{{ $package->trial_duration ?? 24 }}"
                                    data-duration-in="{{ $package->trial_duration_in ?? 'hours' }}"
                                    data-connections="{{ $package->max_connections ?? 1 }}"
                                    data-bouquets="{{ is_string($package->bouquets) ? $package->bouquets : json_encode($package->bouquets ?? []) }}">
                                {{ $package->package_name }} - {{ $package->trial_duration ?? 24 }} {{ $package->trial_duration_in ?? 'horas' }}
                            </option>
                        @endif
                    @endforeach
                </select>
                <input type="hidden" id="trialDurationValue" name="duration_value">
                <input type="hidden" id="trialDurationUnit" name="duration_unit">
                <input type="hidden" id="trialMaxConnections" name="max_connections">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-3">Buqu&ecirc;s *</label>
                <div id="trialBouquets" class="grid grid-cols-1 sm:grid-cols-2 gap-3 w-full max-h-60 overflow-y-auto p-4 bg-gray-50 dark:bg-dark-200 rounded-lg border border-gray-200 dark:border-0 custom-scrollbar">
                    @foreach($bouquets as $bouquet)
                        <label class="flex items-center gap-3 p-3 bg-white dark:bg-dark-300 border border-gray-200 dark:border-dark-100 rounded-lg hover:bg-gray-50 dark:hover:bg-dark-100 cursor-pointer transition-colors shadow-sm dark:shadow-none w-full">
                            <input type="checkbox" name="bouquet_ids[]" value="{{ $bouquet->id }}" class="w-5 h-5 text-orange-500 bg-gray-100 dark:bg-dark-200 border-gray-300 dark:border-dark-100 rounded focus:ring-orange-500 focus:ring-2">
                            <span class="text-gray-700 dark:text-white text-sm break-all">{{ $bouquet->bouquet_name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="flex flex-col-reverse sm:flex-row gap-3">
                <button type="button" onclick="closeTrialModal()" class="w-full sm:w-1/2 px-4 py-2 bg-gray-100 dark:bg-dark-200 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-dark-100 transition-colors font-medium">Cancelar</button>
                <button type="submit" class="w-full sm:w-1/2 px-4 py-2 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg hover:shadow-lg transition-all font-medium">
                    <span id="trialBtnText">Gerar Teste</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Inicialização dos Gráficos
    document.addEventListener('DOMContentLoaded', function() {
        @if(isset($charts))
        const chartConfig = {
            type: 'bar',
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: document.documentElement.classList.contains('dark') ? '#cbd5e1' : '#64748b' }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: document.documentElement.classList.contains('dark') ? '#334155' : '#e2e8f0' },
                        ticks: { color: document.documentElement.classList.contains('dark') ? '#cbd5e1' : '#64748b' }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: document.documentElement.classList.contains('dark') ? '#cbd5e1' : '#64748b' }
                    }
                }
            }
        };

        const chartData = {
            '7': @json($charts['days_7']),
            '30': @json($charts['days_30'])
        };
        
        let clientsChart = null;
        let resellersChart = null;

        // Função para atualizar gráfico de Clientes
        window.updateClientChart = function(days) {
            const data = chartData[days];
            const ctxClients = document.getElementById('clientsChart');

            if (clientsChart) clientsChart.destroy();

            // Atualizar botões Clientes
            ['7', '30'].forEach(d => {
                const btn = document.getElementById(`btnClient${d}`);
                const isActive = d === days;
                
                if (isActive) {
                    btn.classList.add('bg-white', 'dark:bg-dark-300', 'text-orange-600', 'dark:text-orange-500', 'shadow-sm');
                    btn.classList.remove('text-gray-500', 'dark:text-gray-400');
                } else {
                    btn.classList.remove('bg-white', 'dark:bg-dark-300', 'text-orange-600', 'dark:text-orange-500', 'shadow-sm');
                    btn.classList.add('text-gray-500', 'dark:text-gray-400');
                }
            });

            clientsChart = new Chart(ctxClients, {
                ...chartConfig,
                data: {
                    labels: data.labels,
                    datasets: [
                        {
                            label: 'Novos Clientes',
                            data: data.clients,
                            backgroundColor: '#f97316', // orange-500
                            borderRadius: 4
                        },
                        {
                            label: 'Testes Gerados',
                            data: data.trials,
                            backgroundColor: '#a855f7', // purple-500
                            borderRadius: 4
                        }
                    ]
                }
            });
        };

        // Função para atualizar gráfico de Revendas
        window.updateResellerChart = function(days) {
            const data = chartData[days];
            const ctxResellers = document.getElementById('resellersChart');

            if (resellersChart) resellersChart.destroy();

            // Atualizar botões Revendas
            ['7', '30'].forEach(d => {
                const btn = document.getElementById(`btnReseller${d}`);
                const isActive = d === days;
                
                if (isActive) {
                    btn.classList.add('bg-white', 'dark:bg-dark-300', 'text-blue-600', 'dark:text-blue-500', 'shadow-sm');
                    btn.classList.remove('text-gray-500', 'dark:text-gray-400');
                } else {
                    btn.classList.remove('bg-white', 'dark:bg-dark-300', 'text-blue-600', 'dark:text-blue-500', 'shadow-sm');
                    btn.classList.add('text-gray-500', 'dark:text-gray-400');
                }
            });

            resellersChart = new Chart(ctxResellers, {
                ...chartConfig,
                data: {
                    labels: data.labels,
                    datasets: [
                        {
                            label: 'Novas Revendas',
                            data: data.resellers,
                            backgroundColor: '#3b82f6', // blue-500
                            borderRadius: 4
                        },
                        {
                            label: 'Recargas Efetuadas',
                            data: data.recharges,
                            backgroundColor: '#22c55e', // green-500
                            borderRadius: 4
                        }
                    ]
                }
            });
        };

        // Iniciar ambos com 7 dias
        updateClientChart('7');
        updateResellerChart('7');
        @endif
    });

    // Scripts do Modal de Teste Rápido
    function openTrialModal() {
        document.getElementById('trialForm').reset();
        document.getElementById('trialPackageId').selectedIndex = 0;
        document.querySelectorAll('#trialBouquets input[type="checkbox"]').forEach(cb => cb.checked = false);
        generateTrialUsername();
        generateTrialPassword();
        document.getElementById('trialModal').classList.remove('hidden');
    }

    function closeTrialModal() {
        document.getElementById('trialModal').classList.add('hidden');
    }

    function generateTrialUsername() {
        const random = Math.floor(Math.random() * 100000);
        document.getElementById('trialUsername').value = 'teste' + random;
    }

    function generateTrialPassword() {
        const random = Math.floor(Math.random() * 1000000);
        document.getElementById('trialPassword').value = random.toString();
    }

    function maskPhone(event) {
        let input = event.target;
        let value = input.value.replace(/\D/g, '');
        if (value.length > 11) value = value.slice(0, 11);
        if (value.length > 2) {
            if (value.length <= 10) {
                value = `(${value.slice(0, 2)}) ${value.slice(2, 6)}-${value.slice(6)}`;
            } else {
                value = `(${value.slice(0, 2)}) ${value.slice(2, 7)}-${value.slice(7)}`;
            }
        } else if (value.length > 0) {
            value = `(${value}`;
        }
        input.value = value;
    }

    function updateTrialPackage(select) {
        const selectedOption = select.options[select.selectedIndex];
        if (select.value) {
            document.getElementById('trialDurationValue').value = selectedOption.getAttribute('data-duration');
            document.getElementById('trialDurationUnit').value = selectedOption.getAttribute('data-duration-in');
            document.getElementById('trialMaxConnections').value = selectedOption.getAttribute('data-connections');
            
            const bouquetsAttr = selectedOption.getAttribute('data-bouquets');
            if (bouquetsAttr) {
                try {
                    let packageBouquets = JSON.parse(bouquetsAttr);
                    if (typeof packageBouquets === 'string') packageBouquets = JSON.parse(packageBouquets);
                    if (Array.isArray(packageBouquets)) {
                        const bouquetsToSelect = packageBouquets.map(String);
                        document.querySelectorAll('#trialBouquets input[type="checkbox"]').forEach(cb => {
                            cb.checked = bouquetsToSelect.includes(cb.value.toString());
                        });
                    }
                } catch (e) { console.error('Erro ao processar bouquets:', e); }
            }
        } else {
            document.getElementById('trialDurationValue').value = '';
            document.getElementById('trialDurationUnit').value = '';
            document.getElementById('trialMaxConnections').value = '';
            document.querySelectorAll('#trialBouquets input[type="checkbox"]').forEach(cb => cb.checked = false);
        }
    }

    function submitTrial(event) {
        event.preventDefault();
        const form = document.getElementById('trialForm');
        
        if (!document.getElementById('trialPackageId').value) {
            alert('Selecione um pacote de teste.');
            return;
        }
        
        if (document.querySelectorAll('#trialBouquets input[type="checkbox"]:checked').length === 0) {
            alert('Selecione pelo menos um buquê para o teste.');
            return;
        }

        if (!document.getElementById('trialDurationValue').value) {
            updateTrialPackage(document.getElementById('trialPackageId'));
        }
        
        const formData = new FormData(form);
        const btn = document.getElementById('trialBtnText').parentElement;
        
        btn.disabled = true;
        btn.classList.add('opacity-50');
        
        fetch('/clients/trial', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(async response => {
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'Erro ao criar teste');
            return data;
        })
        .then(data => {
            if (data.success) {
                closeTrialModal();
                if (data.client_message) {
                    const modalHtml = `
                    <div id="ajaxClientMessageModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
                        <div class="bg-white dark:bg-dark-300 rounded-xl max-w-2xl w-full border border-gray-200 dark:border-dark-200 shadow-2xl">
                            <div class="p-6 border-b border-gray-200 dark:border-dark-200 flex justify-between items-center bg-gradient-to-r from-green-600 to-green-700">
                                <h3 class="text-xl font-bold text-white flex items-center gap-2">
                                    <i class="bi bi-check-circle-fill"></i>
                                    Teste Criado com Sucesso!
                                </h3>
                                <button onclick="document.getElementById('ajaxClientMessageModal').remove(); window.location.reload();" class="text-white hover:text-gray-200">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                            <div class="p-6">
                                <div class="mb-6">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">Dados de Acesso</label>
                                    <div class="relative">
                                        <textarea id="ajaxClientMessageText" readonly rows="12" class="w-full px-4 py-3 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white font-mono text-sm resize-none">${data.client_message}</textarea>
                                        <button onclick="copyToClipboard('ajaxClientMessageText')" class="absolute top-2 right-2 px-3 py-1.5 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors flex items-center gap-2 text-xs">
                                            <i class="bi bi-clipboard"></i>
                                            Copiar
                                        </button>
                                    </div>
                                </div>
                                <div class="flex gap-3">
                                    <button onclick="document.getElementById('ajaxClientMessageModal').remove(); window.location.reload();" class="flex-1 px-4 py-2 bg-gray-100 dark:bg-dark-200 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-dark-100 transition-colors font-medium">Fechar</button>
                                    <button onclick="window.open('https://wa.me/?text=' + encodeURIComponent(document.getElementById('ajaxClientMessageText').value), '_blank')" class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium flex items-center justify-center gap-2">
                                        <i class="bi bi-whatsapp"></i>
                                        Enviar no WhatsApp
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>`;
                    document.body.insertAdjacentHTML('beforeend', modalHtml);
                } else {
                    alert('Teste criado com sucesso!');
                    window.location.reload();
                }
            } else {
                alert('Erro: ' + data.message);
            }
        })
        .catch(err => alert(err.message))
        .finally(() => {
            btn.disabled = false;
            btn.classList.remove('opacity-50');
        });
    }

    // Helper simples para copy se não existir global
    if (typeof copyToClipboard !== 'function') {
        function copyToClipboard(elementId) {
            const el = document.getElementById(elementId);
            if (!el) return;
            el.select();
            el.setSelectionRange(0, 99999);
            try {
                navigator.clipboard.writeText(el.value);
            } catch(e) {
                document.execCommand('copy');
            }
            alert('Copiado!');
        }
    }
</script>
@endpush
