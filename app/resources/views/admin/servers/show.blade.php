@extends('layouts.app')

@section('title', 'Detalhes do Servidor')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <i class="bi bi-server text-blue-600"></i>
                {{ $server->server_name }}
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">IP: {{ $server->server_ip }} | ID: {{ $server->id }}</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('settings.admin.servers.index') }}" class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 dark:bg-dark-200 dark:hover:bg-dark-100 dark:text-gray-300 rounded-lg transition-colors">
                <i class="bi bi-arrow-left me-1"></i> Voltar
            </a>
            <div class="px-3 py-1 rounded-lg {{ $server->status == 1 ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' }} font-medium text-sm">
                {{ $server->status == 1 ? 'Online' : 'Offline' }}
            </div>
        </div>
    </div>

    <!-- Métricas -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-dark-300 p-4 rounded-xl border border-gray-200 dark:border-dark-200 shadow-sm">
            <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Total de Clientes</div>
            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $server->total_clients }}</div>
        </div>
        <div class="bg-white dark:bg-dark-300 p-4 rounded-xl border border-gray-200 dark:border-dark-200 shadow-sm">
            <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Streams Ativos</div>
            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $onlineStreams }} <span class="text-sm font-normal text-gray-400">/ {{ $streamsCount }}</span></div>
        </div>
        <div class="bg-white dark:bg-dark-300 p-4 rounded-xl border border-gray-200 dark:border-dark-200 shadow-sm">
            <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Porta HTTP</div>
            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $server->http_broadcast_port }}</div>
        </div>
        <div class="bg-white dark:bg-dark-300 p-4 rounded-xl border border-gray-200 dark:border-dark-200 shadow-sm">
            <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Última Checagem</div>
            <div class="text-lg font-medium text-gray-900 dark:text-white">{{ $server->last_check_ago }}s atrás</div>
        </div>
    </div>

    <!-- Ações de Controle -->
    <div class="bg-white dark:bg-dark-300 rounded-xl shadow-sm border border-gray-200 dark:border-dark-200 overflow-hidden">
        <div class="p-6 border-b border-gray-200 dark:border-dark-200 bg-gray-50 dark:bg-dark-200">
            <h3 class="font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <i class="bi bi-joystick"></i>
                Painel de Controle
            </h3>
        </div>
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            
            <!-- Reiniciar Serviços -->
            <form action="{{ route('settings.admin.servers.action', $server->id) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja reiniciar os serviços deste servidor? Isso pode desconectar usuários.');">
                @csrf
                <input type="hidden" name="action" value="restart_services">
                <button type="submit" class="w-full h-full p-4 text-left border rounded-lg hover:bg-orange-50 hover:border-orange-200 dark:hover:bg-orange-900/10 dark:border-dark-100 transition-all group">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-10 h-10 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center group-hover:bg-orange-200 transition-colors">
                            <i class="bi bi-arrow-repeat text-xl"></i>
                        </div>
                        <span class="font-bold text-gray-900 dark:text-white">Reiniciar Serviços</span>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Reinicia o serviço principal do XUI no servidor.</p>
                </button>
            </form>

            <!-- Reiniciar Streams -->
            <form action="{{ route('settings.admin.servers.action', $server->id) }}" method="POST" onsubmit="return confirm('Isso irá reiniciar TODOS os streams neste servidor. Confirmar?');">
                @csrf
                <input type="hidden" name="action" value="restart_streams">
                <button type="submit" class="w-full h-full p-4 text-left border rounded-lg hover:bg-blue-50 hover:border-blue-200 dark:hover:bg-blue-900/10 dark:border-dark-100 transition-all group">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center group-hover:bg-blue-200 transition-colors">
                            <i class="bi bi-play-circle text-xl"></i>
                        </div>
                        <span class="font-bold text-gray-900 dark:text-white">Reiniciar Streams</span>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Reinicia todos os processos de transmissão ativos.</p>
                </button>
            </form>

            <!-- Matar Conexões -->
            <form action="{{ route('settings.admin.servers.action', $server->id) }}" method="POST" onsubmit="return confirm('ATENÇÃO: Isso derrubará todas as conexões de clientes ativos. Continuar?');">
                @csrf
                <input type="hidden" name="action" value="kill_connections">
                <button type="submit" class="w-full h-full p-4 text-left border rounded-lg hover:bg-red-50 hover:border-red-200 dark:hover:bg-red-900/10 dark:border-dark-100 transition-all group">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-10 h-10 rounded-full bg-red-100 text-red-600 flex items-center justify-center group-hover:bg-red-200 transition-colors">
                            <i class="bi bi-x-octagon text-xl"></i>
                        </div>
                        <span class="font-bold text-gray-900 dark:text-white">Matar Conexões</span>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Derruba todas as conexões de clientes no servidor.</p>
                </button>
            </form>

            <!-- Parar Todos Streams -->
            <form action="{{ route('settings.admin.servers.action', $server->id) }}" method="POST" onsubmit="return confirm('Isso irá PARAR todos os streams. Eles não reiniciarão automaticamente. Confirmar?');">
                @csrf
                <input type="hidden" name="action" value="stop_all_streams">
                <button type="submit" class="w-full h-full p-4 text-left border rounded-lg hover:bg-gray-50 hover:border-gray-300 dark:hover:bg-dark-200 dark:border-dark-100 transition-all group">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-10 h-10 rounded-full bg-gray-200 text-gray-600 flex items-center justify-center group-hover:bg-gray-300 transition-colors">
                            <i class="bi bi-stop-circle text-xl"></i>
                        </div>
                        <span class="font-bold text-gray-900 dark:text-white">Parar Streams</span>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Encerra todos os processos de stream sem reiniciar.</p>
                </button>
            </form>

            <!-- Iniciar Todos Streams -->
            <form action="{{ route('settings.admin.servers.action', $server->id) }}" method="POST">
                @csrf
                <input type="hidden" name="action" value="start_all_streams">
                <button type="submit" class="w-full h-full p-4 text-left border rounded-lg hover:bg-green-50 hover:border-green-200 dark:hover:bg-green-900/10 dark:border-dark-100 transition-all group">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-10 h-10 rounded-full bg-green-100 text-green-600 flex items-center justify-center group-hover:bg-green-200 transition-colors">
                            <i class="bi bi-play-fill text-xl"></i>
                        </div>
                        <span class="font-bold text-gray-900 dark:text-white">Iniciar Streams</span>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Tenta iniciar todos os streams parados ou com erro.</p>
                </button>
            </form>

        </div>
    </div>

    <!-- JSON Data (Debug/Info) -->
    <div class="bg-white dark:bg-dark-300 rounded-xl shadow-sm border border-gray-200 dark:border-dark-200 p-6">
        <h3 class="font-bold text-gray-900 dark:text-white mb-4">Informações Técnicas</h3>
        <pre class="bg-gray-50 dark:bg-dark-400 p-4 rounded-lg text-xs text-gray-600 dark:text-gray-300 overflow-x-auto font-mono max-h-64">{{ json_encode(json_decode($server->watchdog_data ?? '{}'), JSON_PRETTY_PRINT) }}</pre>
    </div>
</div>
@endsection
