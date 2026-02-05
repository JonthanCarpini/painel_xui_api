@extends('layouts.app')

@section('title', 'Configurações do Sistema')

@section('content')
<div class="flex items-center justify-between mb-8">
    <h1 class="text-2xl font-bold text-white flex items-center gap-3">
        <i class="bi bi-gear text-orange-500"></i>
        Configurações do Sistema
    </h1>
</div>

<!-- Abas de Navegação -->
<div class="bg-dark-300 rounded-xl border border-dark-200 mb-6">
    <div class="flex border-b border-dark-200">
        <button type="button" onclick="switchTab('geral')" id="tab-geral" class="tab-button active px-6 py-4 text-sm font-medium transition-colors flex items-center gap-2">
            <i class="bi bi-sliders"></i>
            Geral
        </button>
        <button type="button" onclick="switchTab('painel')" id="tab-painel" class="tab-button px-6 py-4 text-sm font-medium transition-colors flex items-center gap-2">
            <i class="bi bi-layout-text-window-reverse"></i>
            Painel
        </button>
        <button type="button" onclick="switchTab('backup')" id="tab-backup" class="tab-button px-6 py-4 text-sm font-medium transition-colors flex items-center gap-2">
            <i class="bi bi-cloud-arrow-down"></i>
            Backup
        </button>
    </div>
</div>

<form action="{{ route('settings.update') }}" method="POST">
    @csrf
    @method('PUT')

    <!-- Conteúdo da Aba Geral -->
    <div id="content-geral" class="tab-content">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Configurações Gerais -->
        <div class="bg-dark-300 rounded-xl border border-dark-200 p-6">
            <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                <i class="bi bi-sliders text-orange-500"></i>
                Configurações Gerais
            </h2>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Nome do Servidor</label>
                    <input type="text" name="server_name" value="{{ $settings->server_name ?? '' }}" class="w-full px-4 py-2 bg-dark-200 border border-dark-100 rounded-lg text-white focus:border-orange-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Fuso Horário Padrão</label>
                    <select name="default_timezone" class="w-full px-4 py-2 bg-dark-200 border border-dark-100 rounded-lg text-white focus:border-orange-500 focus:outline-none">
                        <option value="America/Sao_Paulo" {{ ($settings->default_timezone ?? '') == 'America/Sao_Paulo' ? 'selected' : '' }}>América/São Paulo (BRT)</option>
                        <option value="America/New_York" {{ ($settings->default_timezone ?? '') == 'America/New_York' ? 'selected' : '' }}>América/Nova York (EST)</option>
                        <option value="Europe/London" {{ ($settings->default_timezone ?? '') == 'Europe/London' ? 'selected' : '' }}>Europa/Londres (GMT)</option>
                        <option value="Europe/Paris" {{ ($settings->default_timezone ?? '') == 'Europe/Paris' ? 'selected' : '' }}>Europa/Paris (CET)</option>
                        <option value="Asia/Tokyo" {{ ($settings->default_timezone ?? '') == 'Asia/Tokyo' ? 'selected' : '' }}>Ásia/Tóquio (JST)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Idioma</label>
                    <select name="language" class="w-full px-4 py-2 bg-dark-200 border border-dark-100 rounded-lg text-white focus:border-orange-500 focus:outline-none">
                        <option value="pt" {{ ($settings->language ?? '') == 'pt' ? 'selected' : '' }}>Português</option>
                        <option value="en" {{ ($settings->language ?? '') == 'en' ? 'selected' : '' }}>English</option>
                        <option value="es" {{ ($settings->language ?? '') == 'es' ? 'selected' : '' }}>Español</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Formato de Data</label>
                    <select name="date_format" class="w-full px-4 py-2 bg-dark-200 border border-dark-100 rounded-lg text-white focus:border-orange-500 focus:outline-none">
                        <option value="d/m/Y" {{ ($settings->date_format ?? '') == 'd/m/Y' ? 'selected' : '' }}>DD/MM/AAAA</option>
                        <option value="Y-m-d" {{ ($settings->date_format ?? '') == 'Y-m-d' ? 'selected' : '' }}>AAAA-MM-DD</option>
                        <option value="m/d/Y" {{ ($settings->date_format ?? '') == 'm/d/Y' ? 'selected' : '' }}>MM/DD/AAAA</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Formato de Data/Hora</label>
                    <select name="datetime_format" class="w-full px-4 py-2 bg-dark-200 border border-dark-100 rounded-lg text-white focus:border-orange-500 focus:outline-none">
                        <option value="d/m/Y H:i:s" {{ ($settings->datetime_format ?? '') == 'd/m/Y H:i:s' ? 'selected' : '' }}>DD/MM/AAAA HH:MM:SS</option>
                        <option value="Y-m-d H:i:s" {{ ($settings->datetime_format ?? '') == 'Y-m-d H:i:s' ? 'selected' : '' }}>AAAA-MM-DD HH:MM:SS</option>
                        <option value="m/d/Y h:i A" {{ ($settings->datetime_format ?? '') == 'm/d/Y h:i A' ? 'selected' : '' }}>MM/DD/AAAA HH:MM AM/PM</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Configurações de Streaming -->
        <div class="bg-dark-300 rounded-xl border border-dark-200 p-6">
            <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                <i class="bi bi-play-circle text-orange-500"></i>
                Configurações de Streaming
            </h2>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Client Prebuffer (bytes)</label>
                    <input type="number" name="client_prebuffer" value="{{ $settings->client_prebuffer ?? 30 }}" class="w-full px-4 py-2 bg-dark-200 border border-dark-100 rounded-lg text-white focus:border-orange-500 focus:outline-none">
                    <p class="text-xs text-gray-500 mt-1">Buffer inicial do cliente</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Limite de Reprodução Simultânea</label>
                    <input type="number" name="playback_limit" value="{{ $settings->playback_limit ?? 4 }}" class="w-full px-4 py-2 bg-dark-200 border border-dark-100 rounded-lg text-white focus:border-orange-500 focus:outline-none">
                    <p class="text-xs text-gray-500 mt-1">Máximo de streams simultâneos por usuário</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Auto Kick (horas)</label>
                    <input type="number" name="user_auto_kick_hours" value="{{ $settings->user_auto_kick_hours ?? 4 }}" class="w-full px-4 py-2 bg-dark-200 border border-dark-100 rounded-lg text-white focus:border-orange-500 focus:outline-none">
                    <p class="text-xs text-gray-500 mt-1">Desconectar usuários inativos após X horas</p>
                </div>
            </div>
        </div>

        <!-- Configurações de Segurança -->
        <div class="bg-dark-300 rounded-xl border border-dark-200 p-6">
            <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                <i class="bi bi-shield-check text-orange-500"></i>
                Configurações de Segurança
            </h2>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Limite de Flood (requisições)</label>
                    <input type="number" name="flood_limit" value="{{ $settings->flood_limit ?? 40 }}" class="w-full px-4 py-2 bg-dark-200 border border-dark-100 rounded-lg text-white focus:border-orange-500 focus:outline-none">
                    <p class="text-xs text-gray-500 mt-1">Máximo de requisições permitidas</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Flood Segundos</label>
                    <input type="number" name="flood_seconds" value="{{ $settings->flood_seconds ?? 2 }}" class="w-full px-4 py-2 bg-dark-200 border border-dark-100 rounded-lg text-white focus:border-orange-500 focus:outline-none">
                    <p class="text-xs text-gray-500 mt-1">Intervalo de tempo para contagem de flood</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Senha da API</label>
                    <input type="password" name="api_pass" value="{{ $settings->api_pass ?? '' }}" class="w-full px-4 py-2 bg-dark-200 border border-dark-100 rounded-lg text-white focus:border-orange-500 focus:outline-none" placeholder="••••••••">
                    <p class="text-xs text-gray-500 mt-1">Senha para acesso à API externa</p>
                </div>
            </div>
        </div>

        <!-- Configurações de Integração -->
        <div class="bg-dark-300 rounded-xl border border-dark-200 p-6">
            <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                <i class="bi bi-plug text-orange-500"></i>
                Integrações
            </h2>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">TMDB API Key</label>
                    <input type="text" name="tmdb_api_key" value="{{ $settings->tmdb_api_key ?? '' }}" class="w-full px-4 py-2 bg-dark-200 border border-dark-100 rounded-lg text-white focus:border-orange-500 focus:outline-none" placeholder="Chave da API do TMDB">
                    <p class="text-xs text-gray-500 mt-1">Para buscar informações de filmes/séries</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Mensagem do Dia</label>
                    <textarea name="message_of_day" rows="4" class="w-full px-4 py-2 bg-dark-200 border border-dark-100 rounded-lg text-white focus:border-orange-500 focus:outline-none" placeholder="Mensagem exibida aos usuários">{{ $settings->message_of_day ?? '' }}</textarea>
                    <p class="text-xs text-gray-500 mt-1">Mensagem exibida no painel dos clientes</p>
                </div>
            </div>
        </div>
    </div>

        </div>
    </div>

    <!-- Conteúdo da Aba Painel -->
    <div id="content-painel" class="tab-content hidden">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-dark-300 rounded-xl border border-dark-200 p-6">
                <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                    <i class="bi bi-palette text-orange-500"></i>
                    Aparência do Painel
                </h2>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-2">Logo do Painel</label>
                        <input type="text" name="panel_logo" value="{{ $settings->panel_logo ?? '' }}" class="w-full px-4 py-2 bg-dark-200 border border-dark-100 rounded-lg text-white focus:border-orange-500 focus:outline-none" placeholder="URL da logo">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-2">Nome do Painel</label>
                        <input type="text" name="panel_name" value="{{ $settings->panel_name ?? 'Office IPTV' }}" class="w-full px-4 py-2 bg-dark-200 border border-dark-100 rounded-lg text-white focus:border-orange-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-2">Cor Primária</label>
                        <input type="color" name="panel_primary_color" value="{{ $settings->panel_primary_color ?? '#f97316' }}" class="w-full h-12 px-2 py-1 bg-dark-200 border border-dark-100 rounded-lg cursor-pointer">
                    </div>
                </div>
            </div>

            <div class="bg-dark-300 rounded-xl border border-dark-200 p-6">
                <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                    <i class="bi bi-toggles text-orange-500"></i>
                    Funcionalidades
                </h2>
                
                <div class="space-y-4">
                    <label class="flex items-center justify-between p-3 bg-dark-200 rounded-lg cursor-pointer">
                        <span class="text-white text-sm">Habilitar Modo Escuro</span>
                        <input type="checkbox" name="dark_mode" {{ ($settings->dark_mode ?? 1) ? 'checked' : '' }} class="w-5 h-5 text-orange-500 bg-dark-100 border-dark-100 rounded focus:ring-orange-500">
                    </label>

                    <label class="flex items-center justify-between p-3 bg-dark-200 rounded-lg cursor-pointer">
                        <span class="text-white text-sm">Mostrar Estatísticas no Dashboard</span>
                        <input type="checkbox" name="show_dashboard_stats" {{ ($settings->dashboard_stats ?? 1) ? 'checked' : '' }} class="w-5 h-5 text-orange-500 bg-dark-100 border-dark-100 rounded focus:ring-orange-500">
                    </label>

                    <label class="flex items-center justify-between p-3 bg-dark-200 rounded-lg cursor-pointer">
                        <span class="text-white text-sm">Habilitar Notificações</span>
                        <input type="checkbox" name="enable_notifications" {{ ($settings->enable_notifications ?? 1) ? 'checked' : '' }} class="w-5 h-5 text-orange-500 bg-dark-100 border-dark-100 rounded focus:ring-orange-500">
                    </label>
                </div>
            </div>
        </div>
    </div>

    <!-- Conteúdo da Aba Backup -->
    <div id="content-backup" class="tab-content hidden">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-dark-300 rounded-xl border border-dark-200 p-6">
                <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                    <i class="bi bi-clock-history text-orange-500"></i>
                    Backup Automático
                </h2>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-2">Frequência de Backup</label>
                        <select name="automatic_backups" class="w-full px-4 py-2 bg-dark-200 border border-dark-100 rounded-lg text-white focus:border-orange-500 focus:outline-none">
                            <option value="off" {{ ($settings->automatic_backups ?? 'off') == 'off' ? 'selected' : '' }}>Desativado</option>
                            <option value="daily" {{ ($settings->automatic_backups ?? '') == 'daily' ? 'selected' : '' }}>Diário</option>
                            <option value="weekly" {{ ($settings->automatic_backups ?? '') == 'weekly' ? 'selected' : '' }}>Semanal</option>
                            <option value="monthly" {{ ($settings->automatic_backups ?? '') == 'monthly' ? 'selected' : '' }}>Mensal</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-2">Backups a Manter</label>
                        <input type="number" name="backups_to_keep" value="{{ $settings->backups_to_keep ?? 5 }}" class="w-full px-4 py-2 bg-dark-200 border border-dark-100 rounded-lg text-white focus:border-orange-500 focus:outline-none">
                        <p class="text-xs text-gray-500 mt-1">Número de backups antigos a manter</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-2">Último Backup</label>
                        <input type="text" value="{{ $settings->last_backup ? date('d/m/Y H:i', $settings->last_backup) : 'Nunca' }}" readonly class="w-full px-4 py-2 bg-dark-100 border border-dark-100 rounded-lg text-gray-400 cursor-not-allowed">
                    </div>
                </div>
            </div>

            <div class="bg-dark-300 rounded-xl border border-dark-200 p-6">
                <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                    <i class="bi bi-cloud-upload text-orange-500"></i>
                    Backup Remoto
                </h2>
                
                <div class="space-y-4">
                    <label class="flex items-center justify-between p-3 bg-dark-200 rounded-lg cursor-pointer">
                        <span class="text-white text-sm">Enviar para Dropbox</span>
                        <input type="checkbox" name="dropbox_remote" {{ ($settings->dropbox_remote ?? 0) ? 'checked' : '' }} class="w-5 h-5 text-orange-500 bg-dark-100 border-dark-100 rounded focus:ring-orange-500">
                    </label>

                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-2">Token do Dropbox</label>
                        <input type="password" name="dropbox_token" value="{{ $settings->dropbox_token ?? '' }}" class="w-full px-4 py-2 bg-dark-200 border border-dark-100 rounded-lg text-white focus:border-orange-500 focus:outline-none" placeholder="••••••••">
                    </div>

                    <label class="flex items-center justify-between p-3 bg-dark-200 rounded-lg cursor-pointer">
                        <span class="text-white text-sm">Manter Backups no Dropbox</span>
                        <input type="checkbox" name="dropbox_keep" {{ ($settings->dropbox_keep ?? 0) ? 'checked' : '' }} class="w-5 h-5 text-orange-500 bg-dark-100 border-dark-100 rounded focus:ring-orange-500">
                    </label>

                    <div class="mt-4 p-4 bg-blue-500/10 border border-blue-500/50 rounded-lg">
                        <p class="text-blue-400 text-sm flex items-start gap-2">
                            <i class="bi bi-info-circle mt-0.5"></i>
                            <span>Configure o token do Dropbox para habilitar backups automáticos na nuvem.</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Botões de Ação -->
    <div class="mt-6 flex gap-3">
        <button type="submit" class="px-6 py-3 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg hover:shadow-lg hover:shadow-orange-500/20 transition-all flex items-center gap-2">
            <i class="bi bi-check-circle"></i>
            Salvar Configurações
        </button>
        <a href="{{ route('dashboard') }}" class="px-6 py-3 bg-dark-200 text-gray-300 rounded-lg hover:bg-dark-100 transition-colors">
            Cancelar
        </a>
    </div>
</form>

@push('scripts')
<script>
    function switchTab(tabName) {
        // Remover active de todos os botões
        document.querySelectorAll('.tab-button').forEach(btn => {
            btn.classList.remove('active', 'text-orange-500', 'border-b-2', 'border-orange-500');
            btn.classList.add('text-gray-400');
        });
        
        // Adicionar active no botão clicado
        const activeBtn = document.getElementById('tab-' + tabName);
        activeBtn.classList.add('active', 'text-orange-500', 'border-b-2', 'border-orange-500');
        activeBtn.classList.remove('text-gray-400');
        
        // Esconder todos os conteúdos
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.add('hidden');
        });
        
        // Mostrar conteúdo da aba selecionada
        document.getElementById('content-' + tabName).classList.remove('hidden');
    }
</script>
@endpush
@endsection
