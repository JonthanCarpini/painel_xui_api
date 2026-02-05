@extends('layouts.app')

@section('title', 'Configurações do Sistema')

@section('content')
<div class="flex flex-col md:flex-row items-center justify-between mb-8 gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
            <i class="bi bi-gear text-orange-500"></i>
            Configura&ccedil;&otilde;es do Sistema
        </h1>
        <p class="text-gray-500 dark:text-gray-400 mt-1">Gerencie as configura&ccedil;&otilde;es globais, aplicativos e mensagens.</p>
    </div>
</div>

<!-- Abas de Navegação -->
<div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 mb-6 shadow-sm dark:shadow-none overflow-hidden">
    <div class="flex border-b border-gray-200 dark:border-dark-200 overflow-x-auto">
        <button type="button" onclick="switchTab('geral')" id="tab-geral" class="tab-button active px-6 py-4 text-sm font-medium transition-colors flex items-center gap-2 whitespace-nowrap text-orange-600 dark:text-orange-500 border-b-2 border-orange-600 dark:border-orange-500 bg-orange-50/50 dark:bg-orange-500/10">
            <i class="bi bi-sliders"></i>
            Geral
        </button>
        <button type="button" onclick="switchTab('apps')" id="tab-apps" class="tab-button px-6 py-4 text-sm font-medium transition-colors flex items-center gap-2 whitespace-nowrap text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-50 dark:hover:bg-dark-200">
            <i class="bi bi-android2"></i>
            Aplicativos
        </button>
        <button type="button" onclick="switchTab('dns')" id="tab-dns" class="tab-button px-6 py-4 text-sm font-medium transition-colors flex items-center gap-2 whitespace-nowrap text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-50 dark:hover:bg-dark-200">
            <i class="bi bi-globe"></i>
            DNS
        </button>
        <button type="button" onclick="switchTab('message')" id="tab-message" class="tab-button px-6 py-4 text-sm font-medium transition-colors flex items-center gap-2 whitespace-nowrap text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-50 dark:hover:bg-dark-200">
            <i class="bi bi-chat-text"></i>
            Mensagem
        </button>
    </div>
</div>

<!-- Conteúdo da Aba Geral -->
<div id="content-geral" class="tab-content">
    <form action="{{ route('settings.update') }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Configurações Gerais -->
            <div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 p-6 shadow-sm dark:shadow-none">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <i class="bi bi-sliders text-orange-500"></i>
                    Configura&ccedil;&otilde;es Gerais
                </h2>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">Nome do Servidor</label>
                        <input type="text" name="server_name" value="{{ $settings->server_name ?? '' }}" class="w-full px-4 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white focus:border-orange-500 focus:outline-none transition-colors">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">Fuso Hor&aacute;rio Padr&atilde;o</label>
                        <select name="default_timezone" class="w-full px-4 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white focus:border-orange-500 focus:outline-none transition-colors">
                            <option value="America/Sao_Paulo" {{ ($settings->default_timezone ?? '') == 'America/Sao_Paulo' ? 'selected' : '' }}>Am&eacute;rica/S&atilde;o Paulo (BRT)</option>
                            <option value="America/New_York" {{ ($settings->default_timezone ?? '') == 'America/New_York' ? 'selected' : '' }}>Am&eacute;rica/Nova York (EST)</option>
                            <option value="Europe/London" {{ ($settings->default_timezone ?? '') == 'Europe/London' ? 'selected' : '' }}>Europa/Londres (GMT)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Configurações de Streaming -->
            <div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 p-6 shadow-sm dark:shadow-none">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <i class="bi bi-play-circle text-orange-500"></i>
                    Configura&ccedil;&otilde;es de Streaming
                </h2>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">Limite de Reprodu&ccedil;&atilde;o Simult&acirc;nea</label>
                        <input type="number" name="playback_limit" value="{{ $settings->playback_limit ?? 4 }}" class="w-full px-4 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white focus:border-orange-500 focus:outline-none transition-colors">
                    </div>
                </div>
            </div>
        </div>

        <!-- Botão Salvar -->
        <div class="mt-6 flex justify-end">
            <button type="submit" class="px-6 py-3 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg hover:shadow-lg hover:shadow-orange-500/20 transition-all flex items-center gap-2 font-medium">
                <i class="bi bi-check-circle"></i>
                Salvar Configura&ccedil;&otilde;es Gerais
            </button>
        </div>
    </form>
</div>

<!-- Conteúdo da Aba Aplicativos -->
<div id="content-apps" class="tab-content hidden">
    <div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 p-6 shadow-sm dark:shadow-none mb-6">
        <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <i class="bi bi-plus-circle text-orange-500"></i>
            Adicionar Novo Aplicativo
        </h2>
        
        <form action="{{ route('settings.apps.store') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="col-span-1 md:col-span-2 lg:col-span-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">Nome do App</label>
                    <input type="text" name="name" required class="w-full px-4 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white focus:border-orange-500 focus:outline-none transition-colors" placeholder="Ex: Blessed Player">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">C&oacute;digo Downloader</label>
                    <input type="text" name="downloader_id" class="w-full px-4 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white focus:border-orange-500 focus:outline-none transition-colors" placeholder="Ex: 6390937">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">Link Direto</label>
                    <input type="text" name="direct_link" class="w-full px-4 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white focus:border-orange-500 focus:outline-none transition-colors" placeholder="https://...">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">Dispositivos Compat&iacute;veis</label>
                    <input type="text" name="compatible_devices" class="w-full px-4 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white focus:border-orange-500 focus:outline-none transition-colors" placeholder="Ex: Android, TV Box, Roku">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">C&oacute;digo de Ativa&ccedil;&atilde;o</label>
                    <input type="text" name="activation_code" class="w-full px-4 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white focus:border-orange-500 focus:outline-none transition-colors" placeholder="Ex: p2player">
                </div>
                <div class="col-span-1 md:col-span-2 lg:col-span-3">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">Instru&ccedil;&otilde;es de Login</label>
                    <input type="text" name="login_instructions" class="w-full px-4 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white focus:border-orange-500 focus:outline-none transition-colors" placeholder="Ex: ( LOGAR COM USUARIO E SENHA )">
                </div>
            </div>
            <div class="mt-4 flex justify-end">
                <button type="submit" class="px-6 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-lg transition-colors font-medium">
                    Adicionar App
                </button>
            </div>
        </form>
    </div>

    <!-- Lista de Apps -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($apps as $app)
        <div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 p-6 shadow-sm hover:shadow-md transition-shadow relative group">
            <div class="absolute top-4 right-4 flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                <button onclick="editApp({{ json_encode($app) }})" class="p-2 bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-500/20 transition-colors">
                    <i class="bi bi-pencil"></i>
                </button>
                <form action="{{ route('settings.apps.destroy', $app->id) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="p-2 bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 rounded-lg hover:bg-red-100 dark:hover:bg-red-500/20 transition-colors">
                        <i class="bi bi-trash"></i>
                    </button>
                </form>
            </div>

            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-lg bg-orange-100 dark:bg-orange-500/20 flex items-center justify-center text-orange-600 dark:text-orange-500">
                    <i class="bi bi-android2 text-xl"></i>
                </div>
                <h3 class="font-bold text-gray-900 dark:text-white">{{ $app->name }}</h3>
            </div>

            <div class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                @if($app->downloader_id)
                <div class="flex justify-between">
                    <span class="font-medium">Downloader:</span>
                    <span class="font-mono bg-gray-100 dark:bg-dark-200 px-2 py-0.5 rounded">{{ $app->downloader_id }}</span>
                </div>
                @endif
                
                @if($app->activation_code)
                <div class="flex justify-between">
                    <span class="font-medium">C&oacute;digo:</span>
                    <span class="font-mono bg-gray-100 dark:bg-dark-200 px-2 py-0.5 rounded">{{ $app->activation_code }}</span>
                </div>
                @endif

                @if($app->login_instructions)
                <div class="pt-2 border-t border-gray-100 dark:border-dark-200 mt-2 text-xs text-center text-orange-600 dark:text-orange-500 font-medium">
                    {{ $app->login_instructions }}
                </div>
                @endif
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-12 text-gray-500 dark:text-gray-400">
            Nenhum aplicativo cadastrado.
        </div>
        @endforelse
    </div>
</div>

<!-- Conteúdo da Aba DNS -->
<div id="content-dns" class="tab-content hidden">
    <div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 p-6 shadow-sm dark:shadow-none mb-6">
        <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <i class="bi bi-plus-circle text-orange-500"></i>
            Adicionar Novo DNS
        </h2>
        
        <form action="{{ route('settings.dns.store') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">Nome Identificador</label>
                    <input type="text" name="name" required class="w-full px-4 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white focus:border-orange-500 focus:outline-none transition-colors" placeholder="Ex: DNS GERAL">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">URL do DNS</label>
                    <input type="text" name="url" required class="w-full px-4 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white focus:border-orange-500 focus:outline-none transition-colors" placeholder="Ex: http://meudns.com">
                </div>
            </div>
            <div class="mt-4 flex justify-end">
                <button type="submit" class="px-6 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-lg transition-colors font-medium">
                    Adicionar DNS
                </button>
            </div>
        </form>
    </div>

    <!-- Lista de DNS -->
    <div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 dark:bg-dark-200">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nome</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">URL</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">A&ccedil;&otilde;es</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-dark-200">
                @forelse($dnsServers as $dns)
                <tr class="hover:bg-gray-50 dark:hover:bg-dark-200/50 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                        <i class="bi bi-globe text-orange-500 mr-2"></i>
                        {{ $dns->name }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300 font-mono">
                        {{ $dns->url }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium flex justify-end gap-2">
                        <button onclick="editDns({{ json_encode($dns) }})" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <form action="{{ route('settings.dns.destroy', $dns->id) }}" method="POST" onsubmit="return confirm('Tem certeza?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                        Nenhum DNS cadastrado.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Conteúdo da Aba Mensagem -->
<div id="content-message" class="tab-content hidden">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 p-6 shadow-sm dark:shadow-none h-full">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <i class="bi bi-chat-text text-orange-500"></i>
                    Modelo de Mensagem do Cliente
                </h2>
                
                <form action="{{ route('settings.message.update') }}" method="POST" class="h-full flex flex-col">
                    @csrf
                    @method('PUT')
                    
                    <div class="flex-1 mb-4">
                        <textarea name="client_message_template" id="messageTemplate" class="w-full h-[500px] px-4 py-3 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white focus:border-orange-500 focus:outline-none transition-colors font-mono text-sm resize-none">{{ $clientMessageTemplate }}</textarea>
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="submit" class="px-6 py-3 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg hover:shadow-lg hover:shadow-orange-500/20 transition-all flex items-center gap-2 font-medium">
                            <i class="bi bi-check-circle"></i>
                            Salvar Mensagem
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="space-y-6">
            <!-- Placeholders -->
            <div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 p-6 shadow-sm dark:shadow-none">
                <h3 class="font-bold text-gray-900 dark:text-white mb-3">Vari&aacute;veis Dispon&iacute;veis</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between items-center bg-gray-50 dark:bg-dark-200 p-2 rounded cursor-pointer hover:bg-gray-100 dark:hover:bg-dark-100" onclick="insertTag('{USERNAME}')">
                        <code class="text-orange-600 dark:text-orange-400">{USERNAME}</code>
                        <span class="text-gray-500 text-xs">Usu&aacute;rio do Cliente</span>
                    </div>
                    <div class="flex justify-between items-center bg-gray-50 dark:bg-dark-200 p-2 rounded cursor-pointer hover:bg-gray-100 dark:hover:bg-dark-100" onclick="insertTag('{PASSWORD}')">
                        <code class="text-orange-600 dark:text-orange-400">{PASSWORD}</code>
                        <span class="text-gray-500 text-xs">Senha do Cliente</span>
                    </div>
                    <div class="flex justify-between items-center bg-gray-50 dark:bg-dark-200 p-2 rounded cursor-pointer hover:bg-gray-100 dark:hover:bg-dark-100" onclick="insertTag('{EXPIRATION}')">
                        <code class="text-orange-600 dark:text-orange-400">{EXPIRATION}</code>
                        <span class="text-gray-500 text-xs">Data de Vencimento</span>
                    </div>
                </div>
            </div>

            <!-- DNS List -->
            <div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 p-6 shadow-sm dark:shadow-none">
                <h3 class="font-bold text-gray-900 dark:text-white mb-3">Seus DNS</h3>
                <div class="space-y-2 text-sm max-h-60 overflow-y-auto custom-scrollbar">
                    @foreach($dnsServers as $dns)
                    <div class="flex justify-between items-center bg-gray-50 dark:bg-dark-200 p-2 rounded cursor-pointer hover:bg-gray-100 dark:hover:bg-dark-100" onclick="insertTag('{{ $dns->url }}')">
                        <span class="text-gray-700 dark:text-gray-300">{{ $dns->name }}</span>
                        <i class="bi bi-plus-circle text-gray-400"></i>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Apps List -->
            <div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 p-6 shadow-sm dark:shadow-none">
                <h3 class="font-bold text-gray-900 dark:text-white mb-3">Seus Aplicativos</h3>
                <div class="space-y-2 text-sm max-h-60 overflow-y-auto custom-scrollbar">
                    @foreach($apps as $app)
                    <div class="bg-gray-50 dark:bg-dark-200 p-3 rounded group hover:bg-gray-100 dark:hover:bg-dark-100 transition-colors cursor-pointer" onclick="insertAppDetails({{ json_encode($app) }})">
                        <div class="font-bold text-gray-900 dark:text-white mb-1 flex justify-between items-center">
                            {{ $app->name }}
                            <i class="bi bi-plus-circle text-gray-400 group-hover:text-orange-500"></i>
                        </div>
                        <div class="text-xs text-gray-500 space-y-1">
                            @if($app->downloader_id) <div>Downloader: {{ $app->downloader_id }}</div> @endif
                            @if($app->activation_code) <div>C&oacute;digo: {{ $app->activation_code }}</div> @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function switchTab(tabName) {
        // Remover active de todos os botões
        document.querySelectorAll('.tab-button').forEach(btn => {
            btn.classList.remove('active', 'text-orange-600', 'dark:text-orange-500', 'border-b-2', 'border-orange-600', 'dark:border-orange-500', 'bg-orange-50/50', 'dark:bg-orange-500/10');
            btn.classList.add('text-gray-500', 'dark:text-gray-400', 'hover:bg-gray-50', 'dark:hover:bg-dark-200');
        });
        
        // Adicionar active no botão clicado
        const activeBtn = document.getElementById('tab-' + tabName);
        activeBtn.classList.add('active', 'text-orange-600', 'dark:text-orange-500', 'border-b-2', 'border-orange-600', 'dark:border-orange-500', 'bg-orange-50/50', 'dark:bg-orange-500/10');
        activeBtn.classList.remove('text-gray-500', 'dark:text-gray-400', 'hover:bg-gray-50', 'dark:hover:bg-dark-200');
        
        // Esconder todos os conteúdos
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.add('hidden');
        });
        
        // Mostrar conteúdo da aba selecionada
        document.getElementById('content-' + tabName).classList.remove('hidden');
        
        // Atualizar URL hash
        window.location.hash = tabName;
    }

    // Verificar hash ao carregar
    if(window.location.hash) {
        const tabName = window.location.hash.substring(1);
        if(document.getElementById('tab-' + tabName)) {
            switchTab(tabName);
        }
    }

    function insertTag(tag) {
        const textarea = document.getElementById('messageTemplate');
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const text = textarea.value;
        const before = text.substring(0, start);
        const after = text.substring(end, text.length);
        
        textarea.value = before + tag + after;
        textarea.selectionStart = textarea.selectionEnd = start + tag.length;
        textarea.focus();
    }

    function insertAppDetails(app) {
        let text = `\n📺 ${app.name}\n`;
        if (app.downloader_id) text += `➕DOWNLOADER: ${app.downloader_id}\n`;
        if (app.direct_link) text += `➕ Link Direto: ${app.direct_link}\n`;
        if (app.compatible_devices) text += `➕ ${app.compatible_devices}\n`;
        if (app.activation_code) text += `➕ Código: ${app.activation_code}\n`;
        if (app.login_instructions) text += `🟡 ${app.login_instructions}\n`;
        
        insertTag(text);
    }

    function editApp(app) {
        document.querySelector('input[name="name"]').value = app.name;
        document.querySelector('input[name="downloader_id"]').value = app.downloader_id || '';
        document.querySelector('input[name="direct_link"]').value = app.direct_link || '';
        document.querySelector('input[name="compatible_devices"]').value = app.compatible_devices || '';
        document.querySelector('input[name="activation_code"]').value = app.activation_code || '';
        document.querySelector('input[name="login_instructions"]').value = app.login_instructions || '';
        
        const form = document.querySelector('form[action="{{ route('settings.apps.store') }}"]');
        form.action = '/settings/apps/' + app.id;
        
        let methodInput = form.querySelector('input[name="_method"]');
        if (!methodInput) {
            methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'PUT';
            form.appendChild(methodInput);
        }
        
        const btn = form.querySelector('button[type="submit"]');
        btn.innerText = 'Atualizar App';
        btn.classList.remove('bg-orange-500', 'hover:bg-orange-600');
        btn.classList.add('bg-blue-500', 'hover:bg-blue-600');
        
        switchTab('apps');
        form.scrollIntoView({ behavior: 'smooth' });
    }

    function editDns(dns) {
        document.querySelector('input[name="name"]').value = dns.name;
        document.querySelector('input[name="url"]').value = dns.url;
        
        const form = document.querySelector('form[action="{{ route('settings.dns.store') }}"]');
        form.action = '/settings/dns/' + dns.id;
        
        let methodInput = form.querySelector('input[name="_method"]');
        if (!methodInput) {
            methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'PUT';
            form.appendChild(methodInput);
        }
        
        const btn = form.querySelector('button[type="submit"]');
        btn.innerText = 'Atualizar DNS';
        btn.classList.remove('bg-orange-500', 'hover:bg-orange-600');
        btn.classList.add('bg-blue-500', 'hover:bg-blue-600');
        
        switchTab('dns');
        form.scrollIntoView({ behavior: 'smooth' });
    }
</script>
@endpush
@endsection
