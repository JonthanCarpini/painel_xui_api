@extends('layouts.app')

@section('title', 'Área de Manutenção')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <i class="bi bi-tools text-orange-500"></i>
                Área de Manutenção
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Gerencie bloqueios, tarefas agendadas e monitoramento do sistema.</p>
        </div>
    </div>

    <!-- Abas de Navegação -->
    <div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 mb-6 shadow-sm dark:shadow-none overflow-hidden">
        <div class="flex border-b border-gray-200 dark:border-dark-200 overflow-x-auto custom-scrollbar">
            <button type="button" onclick="switchTab('geral')" id="tab-geral" class="tab-button active flex-1 min-w-[120px] px-4 md:px-6 py-3 md:py-4 text-sm font-medium transition-colors flex items-center justify-center gap-2 whitespace-nowrap text-orange-600 dark:text-orange-500 border-b-2 border-orange-600 dark:border-orange-500 bg-orange-50/50 dark:bg-orange-500/10">
                <i class="bi bi-sliders"></i>
                Geral
            </button>
            <button type="button" onclick="switchTab('canais')" id="tab-canais" class="tab-button flex-1 min-w-[120px] px-4 md:px-6 py-3 md:py-4 text-sm font-medium transition-colors flex items-center justify-center gap-2 whitespace-nowrap text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-50 dark:hover:bg-dark-200">
                <i class="bi bi-collection-play"></i>
                Canais
            </button>
            <button type="button" onclick="switchTab('servers')" id="tab-servers" class="tab-button flex-1 min-w-[120px] px-4 md:px-6 py-3 md:py-4 text-sm font-medium transition-colors flex items-center justify-center gap-2 whitespace-nowrap text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-50 dark:hover:bg-dark-200">
                <i class="bi bi-hdd-network"></i>
                Load Balancers
            </button>
        </div>
    </div>

    <!-- Conteúdo da Aba Geral -->
    <div id="content-geral" class="tab-content">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Configurações de Bloqueio -->
            <div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 shadow-sm">
                <div class="p-6 border-b border-gray-200 dark:border-dark-200">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="bi bi-shield-lock"></i>
                        Controle de Acesso
                    </h2>
                </div>
                <div class="p-6">
                    <form action="{{ route('settings.maintenance.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="space-y-6">
                            <!-- Bloqueio de Revendas -->
                            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-dark-200 rounded-lg border border-gray-200 dark:border-dark-100">
                                <div>
                                    <label for="maintenance_resellers" class="font-medium text-gray-900 dark:text-white block">Bloquear Painel de Revendas</label>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Impede o acesso de todos os revendedores (exceto Admins).</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="maintenance_resellers" id="maintenance_resellers" value="1" class="sr-only peer" {{ $settings['maintenance_resellers'] == '1' ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-orange-300 dark:peer-focus:ring-orange-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-orange-500"></div>
                                </label>
                            </div>

                            <!-- Bloqueio de Testes -->
                            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-dark-200 rounded-lg border border-gray-200 dark:border-dark-100">
                                <div>
                                    <label for="maintenance_tests" class="font-medium text-gray-900 dark:text-white block">Bloquear Testes de Canais</label>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Desativa a página de teste de canais para revendedores.</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="maintenance_tests" id="maintenance_tests" value="1" class="sr-only peer" {{ $settings['maintenance_tests'] == '1' ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-orange-300 dark:peer-focus:ring-orange-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-orange-500"></div>
                                </label>
                            </div>

                            <!-- Bloqueio de Criação de Testes -->
                            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-dark-200 rounded-lg border border-gray-200 dark:border-dark-100">
                                <div>
                                    <label for="disable_trial" class="font-medium text-gray-900 dark:text-white block">Bloquear Criação de Testes</label>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Impede que revendedores criem novos testes temporários.</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="disable_trial" id="disable_trial" value="1" class="sr-only peer" {{ ($settings['disable_trial'] ?? 0) == '1' ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-orange-300 dark:peer-focus:ring-orange-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-orange-500"></div>
                                </label>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <button type="submit" class="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors font-medium text-sm flex items-center gap-2">
                                <i class="bi bi-save"></i>
                                Salvar Configurações
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Ações Manuais -->
            <div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 shadow-sm">
                <div class="p-6 border-b border-gray-200 dark:border-dark-200">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="bi bi-ghost"></i>
                        Cliente Fantasma
                    </h2>
                </div>
                <div class="p-6">
                    <div class="bg-blue-50 dark:bg-blue-500/10 border border-blue-100 dark:border-blue-500/20 rounded-lg p-4 mb-6">
                        <div class="flex gap-3">
                            <i class="bi bi-info-circle text-blue-500 mt-0.5"></i>
                            <div>
                                <h4 class="font-bold text-blue-900 dark:text-blue-100 text-sm">Rotação Manual</h4>
                                <p class="text-xs text-blue-700 dark:text-blue-300 mt-1">
                                    Você pode forçar a rotação da senha e atualização da lista de canais agora mesmo. Isso pode desconectar usuários assistindo via teste.
                                </p>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('settings.maintenance.ghost-rotate') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full px-4 py-3 bg-gray-100 dark:bg-dark-200 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-dark-100 rounded-lg hover:bg-gray-200 dark:hover:bg-dark-100 transition-colors font-medium flex items-center justify-center gap-2">
                            <i class="bi bi-arrow-repeat"></i>
                            Executar Rotação e Sincronização Agora
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Conteúdo da Aba Canais -->
    <div id="content-canais" class="tab-content hidden">
        <div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-gray-200 dark:border-dark-200 bg-gray-50 dark:bg-dark-200">
                <h3 class="font-bold text-gray-900 dark:text-white">Últimos Canais (Monitoramento)</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50 dark:bg-dark-200 text-xs text-gray-500 dark:text-gray-400 uppercase">
                        <tr>
                            <th class="px-4 py-3">ID</th>
                            <th class="px-4 py-3">Nome</th>
                            <th class="px-4 py-3">Fonte</th>
                            <th class="px-4 py-3">Servidor</th>
                            <th class="px-4 py-3">PID</th>
                            <th class="px-4 py-3 text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-dark-200">
                        @forelse($streams as $stream)
                        <tr class="hover:bg-gray-50 dark:hover:bg-dark-200/50">
                            <td class="px-4 py-3 text-sm font-mono text-gray-500">
                                <a href="{{ route('settings.admin.channels.edit', $stream->id) }}" class="hover:text-blue-600 underline decoration-dotted">
                                    {{ $stream->id }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">
                                <a href="{{ route('settings.admin.channels.edit', $stream->id) }}" class="hover:text-blue-600">
                                    {{ $stream->stream_display_name }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500 truncate max-w-xs" title="{{ $stream->stream_source }}">{{ $stream->stream_source }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                @if($stream->server_id)
                                    <a href="{{ route('settings.admin.servers.show', $stream->server_id) }}" class="bg-gray-100 dark:bg-dark-100 px-2 py-0.5 rounded hover:bg-blue-100 hover:text-blue-700 transition-colors">SRV #{{ $stream->server_id }}</a>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm font-mono text-gray-500">{{ $stream->pid ?? '-' }}</td>
                            <td class="px-4 py-3 text-right">
                                @if($stream->pid && $stream->stream_status != 0)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                        Online
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">
                                        Offline
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">Nenhum canal encontrado.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Conteúdo da Aba Load Balancers -->
    <div id="content-servers" class="tab-content hidden">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($servers as $server)
            <div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 shadow-sm p-6 relative overflow-hidden">
                @if($server->is_main)
                <div class="absolute top-0 right-0 bg-orange-500 text-white text-xs px-2 py-1 rounded-bl-lg font-bold">MAIN</div>
                @endif
                
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 rounded-lg bg-blue-100 dark:bg-blue-900/20 flex items-center justify-center text-blue-600 dark:text-blue-400">
                        <i class="bi bi-server text-2xl"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900 dark:text-white">{{ $server->server_name }}</h3>
                        <p class="text-sm text-gray-500 font-mono">{{ $server->server_ip }}</p>
                    </div>
                </div>

                <div class="space-y-3 text-sm">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500">Status</span>
                        @if($server->status == 1)
                            <span class="text-green-600 font-bold flex items-center gap-1"><i class="bi bi-circle-fill text-[8px]"></i> Ativo</span>
                        @else
                            <span class="text-red-500 font-bold flex items-center gap-1"><i class="bi bi-x-circle-fill"></i> Inativo</span>
                        @endif
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500">Clientes Conectados</span>
                        <span class="font-mono bg-gray-100 dark:bg-dark-200 px-2 py-0.5 rounded">{{ $server->total_clients }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500">Última Checagem</span>
                        <span class="text-gray-700 dark:text-gray-300">{{ $server->last_check_ago }}s atrás</span>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-full text-center py-12 text-gray-500 dark:text-gray-400 bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200">
                Nenhum servidor encontrado.
            </div>
            @endforelse
        </div>
    </div>
</div>

<script>
function switchTab(tabName) {
    // Esconder todos os conteúdos
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Mostrar conteúdo selecionado
    document.getElementById('content-' + tabName).classList.remove('hidden');
    
    // Resetar botões
    document.querySelectorAll('.tab-button').forEach(btn => {
        btn.classList.remove('active', 'text-orange-600', 'dark:text-orange-500', 'border-b-2', 'border-orange-600', 'dark:border-orange-500', 'bg-orange-50/50', 'dark:bg-orange-500/10');
        btn.classList.add('text-gray-500', 'dark:text-gray-400', 'hover:text-gray-700', 'dark:hover:text-gray-300', 'hover:bg-gray-50', 'dark:hover:bg-dark-200');
    });
    
    // Ativar botão selecionado
    const activeBtn = document.getElementById('tab-' + tabName);
    activeBtn.classList.remove('text-gray-500', 'dark:text-gray-400', 'hover:text-gray-700', 'dark:hover:text-gray-300', 'hover:bg-gray-50', 'dark:hover:bg-dark-200');
    activeBtn.classList.add('active', 'text-orange-600', 'dark:text-orange-500', 'border-b-2', 'border-orange-600', 'dark:border-orange-500', 'bg-orange-50/50', 'dark:bg-orange-500/10');
}
</script>
@endsection
