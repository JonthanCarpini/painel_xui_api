@extends('layouts.app')

@section('title', 'Meu Perfil')

@section('content')
<div class="w-full">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <i class="bi bi-person-circle text-orange-500"></i>
                Meu Perfil
            </h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1">Gerencie suas informa&ccedil;&otilde;es e prefer&ecirc;ncias do painel.</p>
        </div>
    </div>

    <!-- Tabs Header -->
    <div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 mb-6 shadow-sm dark:shadow-none">
        <div class="flex border-b border-gray-200 dark:border-dark-200 overflow-x-auto">
            <button class="inline-block px-6 py-4 border-b-2 hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300 border-orange-500 text-orange-600 dark:text-orange-500 transition-colors duration-200 font-medium flex items-center gap-2 whitespace-nowrap" id="overview-tab" data-tabs-target="#overview" type="button" role="tab" aria-controls="overview" aria-selected="true">
                <i class="bi bi-person-vcard"></i> Vis&atilde;o Geral
            </button>
            <button class="inline-block px-6 py-4 border-b-2 border-transparent hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300 text-gray-500 dark:text-gray-400 transition-colors duration-200 font-medium flex items-center gap-2 whitespace-nowrap" id="preferences-tab" data-tabs-target="#preferences" type="button" role="tab" aria-controls="preferences" aria-selected="false">
                <i class="bi bi-sliders"></i> Prefer&ecirc;ncias
            </button>
        </div>
    </div>

    <!-- Tabs Content -->
    <div id="profileTabContent">
        
        <!-- Tab 1: Visão Geral -->
        <div class="" id="overview" role="tabpanel" aria-labelledby="overview-tab">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- Card de Identificação (Esquerda) -->
                <div class="lg:col-span-4 xl:col-span-3">
                    <div class="bg-white dark:bg-dark-300 border border-gray-200 dark:border-dark-200 rounded-xl p-6 text-center shadow-sm h-full">
                        <div class="w-24 h-24 bg-gradient-to-br from-orange-500 to-orange-600 rounded-full flex items-center justify-center text-white text-3xl font-bold mx-auto mb-4 shadow-lg shadow-orange-500/30">
                            {{ strtoupper(substr($xuiUser->username, 0, 2)) }}
                        </div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-1">{{ $xuiUser->username }}</h2>
                        <span class="inline-block px-3 py-1 bg-orange-100 dark:bg-orange-500/10 text-orange-600 dark:text-orange-500 rounded-full text-xs font-semibold mb-6 border border-orange-200 dark:border-transparent">
                            {{ $xuiUser->isAdmin() ? 'Administrador' : 'Revendedor' }}
                        </span>
                        
                        <div class="border-t border-gray-100 dark:border-dark-200 pt-6 text-left space-y-4">
                            <div class="flex justify-between items-center group hover:bg-gray-50 dark:hover:bg-dark-200/50 p-2 rounded-lg transition-colors">
                                <span class="text-gray-500 dark:text-gray-500 text-xs uppercase font-bold flex items-center gap-2"><i class="bi bi-hash"></i> ID do Sistema</span>
                                <span class="text-gray-700 dark:text-gray-300 font-mono text-sm bg-gray-100 dark:bg-dark-400 px-2 py-0.5 rounded">#{{ $xuiUser->id }}</span>
                            </div>
                            <div class="flex justify-between items-center group hover:bg-gray-50 dark:hover:bg-dark-200/50 p-2 rounded-lg transition-colors">
                                <span class="text-gray-500 dark:text-gray-500 text-xs uppercase font-bold flex items-center gap-2"><i class="bi bi-calendar-check"></i> Registro</span>
                                <span class="text-gray-700 dark:text-gray-300 text-sm">{{ date('d/m/Y', $xuiUser->date_registered) }}</span>
                            </div>
                            <div class="flex justify-between items-center group hover:bg-gray-50 dark:hover:bg-dark-200/50 p-2 rounded-lg transition-colors">
                                <span class="text-gray-500 dark:text-gray-500 text-xs uppercase font-bold flex items-center gap-2"><i class="bi bi-clock"></i> &Uacute;ltimo Login</span>
                                <span class="text-gray-700 dark:text-gray-300 text-sm">{{ date('d/m/Y H:i', $xuiUser->last_login) }}</span>
                            </div>
                            <div class="flex justify-between items-center group hover:bg-gray-50 dark:hover:bg-dark-200/50 p-2 rounded-lg transition-colors">
                                <span class="text-gray-500 dark:text-gray-500 text-xs uppercase font-bold flex items-center gap-2"><i class="bi bi-globe"></i> IP de Acesso</span>
                                <span class="text-gray-700 dark:text-gray-300 font-mono text-sm">{{ $xuiUser->ip }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Conteúdo Principal (Direita) -->
                <div class="lg:col-span-8 xl:col-span-9 space-y-6">
                    <!-- Banner de Créditos -->
                    <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-xl p-8 text-white shadow-lg shadow-orange-500/20 relative overflow-hidden">
                        <div class="absolute top-0 right-0 -mt-10 -mr-10 w-48 h-48 bg-white/10 rounded-full blur-3xl"></div>
                        <div class="absolute bottom-0 left-0 -mb-10 -ml-10 w-48 h-48 bg-black/10 rounded-full blur-3xl"></div>
                        
                        <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                            <div>
                                <h3 class="text-orange-100 font-medium mb-1 flex items-center gap-2 text-lg">
                                    <i class="bi bi-wallet2"></i> Saldo Dispon&iacute;vel
                                </h3>
                                <div class="flex items-baseline gap-2">
                                    <span class="text-5xl font-bold tracking-tight">{{ number_format($xuiUser->getCredits(), 2, ',', '.') }}</span>
                                    <span class="text-xl text-orange-200">Cr&eacute;ditos</span>
                                </div>
                                <p class="text-orange-100 text-sm mt-2 opacity-90 max-w-md">Utilize seus cr&eacute;ditos para criar novas contas de clientes, renovar assinaturas e gerenciar revendedores.</p>
                            </div>
                            
                            @if(Auth::user()->isAdmin())
                            <div class="bg-white/10 backdrop-blur-sm border border-white/20 rounded-lg p-4 min-w-[200px]">
                                <p class="text-orange-100 text-xs uppercase font-bold mb-1">Status da Conta</p>
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span>
                                    <span class="font-bold">Ativo e Verificado</span>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Cards Informativos -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-white dark:bg-dark-300 border border-gray-200 dark:border-dark-200 rounded-xl p-6 shadow-sm hover:shadow-md transition-shadow">
                            <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                                <i class="bi bi-shield-check text-blue-500"></i> Seguran&ccedil;a
                            </h4>
                            <p class="text-gray-600 dark:text-gray-400 text-sm mb-4">
                                Mantenha sua conta segura. Sua senha &eacute; a chave para acessar o painel e gerenciar seus clientes.
                            </p>
                            <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-dark-200 p-3 rounded-lg border border-gray-100 dark:border-dark-100">
                                <i class="bi bi-lock"></i>
                                <span>Senha alterada pela &uacute;ltima vez em: N/A</span>
                            </div>
                        </div>

                        <div class="bg-white dark:bg-dark-300 border border-gray-200 dark:border-dark-200 rounded-xl p-6 shadow-sm hover:shadow-md transition-shadow">
                            <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                                <i class="bi bi-info-circle text-purple-500"></i> Informa&ccedil;&otilde;es
                            </h4>
                            <p class="text-gray-600 dark:text-gray-400 text-sm mb-4">
                                Seus dados s&atilde;o sincronizados automaticamente. Para alterar informa&ccedil;&otilde;es sens&iacute;veis, contate o suporte.
                            </p>
                            <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-dark-200 p-3 rounded-lg border border-gray-100 dark:border-dark-100">
                                <i class="bi bi-envelope"></i>
                                <span>{{ $xuiUser->email ?? 'Nenhum e-mail cadastrado' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab 2: Preferências -->
        <div class="hidden" id="preferences" role="tabpanel" aria-labelledby="preferences-tab">
            <div class="bg-white dark:bg-dark-300 border border-gray-200 dark:border-dark-200 rounded-xl overflow-hidden shadow-sm">
                <div class="border-b border-gray-200 dark:border-dark-200 px-8 py-6 bg-gray-50 dark:bg-dark-200/50">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">Prefer&ecirc;ncias do Painel</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Personalize sua experi&ecirc;ncia de uso e identidade visual.</p>
                </div>

                <form action="{{ route('profile.update') }}" method="POST" class="p-8 space-y-10">
                    @csrf
                    @method('PUT')

                    <!-- Personalização Visual -->
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                        <div class="lg:col-span-4">
                            <h4 class="text-base font-bold text-gray-900 dark:text-white mb-2">Identidade Visual</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Personalize como o painel se apresenta para voc&ecirc; e seus clientes.</p>
                        </div>
                        
                        <div class="lg:col-span-8 space-y-6">
                            <!-- Nome do Painel -->
                            <div>
                                <label for="panel_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nome do Painel</label>
                                <input type="text" id="panel_name" name="panel_name" value="{{ old('panel_name', $preferences['panel_name']) }}" 
                                    class="w-full bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg px-4 py-3 text-gray-900 dark:text-white focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500 transition-colors"
                                    placeholder="Ex: Meu Painel IPTV">
                                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Este nome ser&aacute; exibido no topo do menu lateral e na aba do navegador.</p>
                            </div>

                            <!-- URL do Logo -->
                            <div>
                                <label for="logo_url" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">URL do Logo (Opcional)</label>
                                <div class="flex gap-3">
                                    <div class="flex-1">
                                        <input type="url" id="logo_url" name="logo_url" value="{{ old('logo_url', $preferences['logo_url']) }}" 
                                            class="w-full bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg px-4 py-3 text-gray-900 dark:text-white focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500 transition-colors"
                                            placeholder="https://exemplo.com/logo.png">
                                    </div>
                                    @if($preferences['logo_url'])
                                    <div class="w-12 h-12 rounded-lg border border-gray-200 dark:border-dark-100 p-1 flex items-center justify-center bg-gray-50 dark:bg-dark-200">
                                        <img src="{{ $preferences['logo_url'] }}" alt="Preview" class="max-w-full max-h-full rounded">
                                    </div>
                                    @endif
                                </div>
                                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Link direto para a imagem do seu logo. Recomendado: Formato quadrado ou retangular, fundo transparente.</p>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-gray-100 dark:border-dark-200"></div>

                    <!-- Configurações de Interface -->
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                        <div class="lg:col-span-4">
                            <h4 class="text-base font-bold text-gray-900 dark:text-white mb-2">Interface e Comportamento</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Ajuste as configura&ccedil;&otilde;es de exibi&ccedil;&atilde;o e notifica&ccedil;&otilde;es.</p>
                        </div>

                        <div class="lg:col-span-8 space-y-4">
                            <!-- Modo Escuro -->
                            <label class="flex items-center justify-between p-4 bg-gray-50 dark:bg-dark-200 rounded-xl border border-gray-200 dark:border-dark-100 cursor-pointer hover:border-orange-500 dark:hover:border-orange-500 transition-colors group">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-full bg-gray-200 dark:bg-dark-300 flex items-center justify-center group-hover:bg-orange-100 dark:group-hover:bg-orange-500/20 transition-colors">
                                        <i class="bi bi-moon-stars text-gray-600 dark:text-gray-400 group-hover:text-orange-600 dark:group-hover:text-orange-500"></i>
                                    </div>
                                    <div>
                                        <span class="block text-sm font-bold text-gray-900 dark:text-white">Modo Escuro</span>
                                        <span class="block text-xs text-gray-500 dark:text-gray-400">Ativar tema escuro para o painel.</span>
                                    </div>
                                </div>
                                <div class="relative inline-flex items-center">
                                    <input type="checkbox" id="dark_mode" name="dark_mode" value="1" class="sr-only peer" {{ $preferences['dark_mode'] ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-gray-300 dark:bg-dark-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-orange-500"></div>
                                </div>
                            </label>

                            <!-- Dashboard Stats -->
                            <label class="flex items-center justify-between p-4 bg-gray-50 dark:bg-dark-200 rounded-xl border border-gray-200 dark:border-dark-100 cursor-pointer hover:border-orange-500 dark:hover:border-orange-500 transition-colors group">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-full bg-gray-200 dark:bg-dark-300 flex items-center justify-center group-hover:bg-orange-100 dark:group-hover:bg-orange-500/20 transition-colors">
                                        <i class="bi bi-bar-chart text-gray-600 dark:text-gray-400 group-hover:text-orange-600 dark:group-hover:text-orange-500"></i>
                                    </div>
                                    <div>
                                        <span class="block text-sm font-bold text-gray-900 dark:text-white">Estat&iacute;sticas no Dashboard</span>
                                        <span class="block text-xs text-gray-500 dark:text-gray-400">Exibir contadores e gr&aacute;ficos na p&aacute;gina inicial.</span>
                                    </div>
                                </div>
                                <div class="relative inline-flex items-center">
                                    <input type="checkbox" id="show_dashboard_stats" name="show_dashboard_stats" value="1" class="sr-only peer" {{ $preferences['show_dashboard_stats'] ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-gray-300 dark:bg-dark-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-orange-500"></div>
                                </div>
                            </label>

                            <!-- Notificações -->
                            <label class="flex items-center justify-between p-4 bg-gray-50 dark:bg-dark-200 rounded-xl border border-gray-200 dark:border-dark-100 cursor-pointer hover:border-orange-500 dark:hover:border-orange-500 transition-colors group">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-full bg-gray-200 dark:bg-dark-300 flex items-center justify-center group-hover:bg-orange-100 dark:group-hover:bg-orange-500/20 transition-colors">
                                        <i class="bi bi-bell text-gray-600 dark:text-gray-400 group-hover:text-orange-600 dark:group-hover:text-orange-500"></i>
                                    </div>
                                    <div>
                                        <span class="block text-sm font-bold text-gray-900 dark:text-white">Notifica&ccedil;&otilde;es do Sistema</span>
                                        <span class="block text-xs text-gray-500 dark:text-gray-400">Receber avisos sobre manuten&ccedil;&atilde;o e novidades.</span>
                                    </div>
                                </div>
                                <div class="relative inline-flex items-center">
                                    <input type="checkbox" id="enable_notifications" name="enable_notifications" value="1" class="sr-only peer" {{ $preferences['enable_notifications'] ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-gray-300 dark:bg-dark-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-orange-500"></div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="pt-6 flex justify-end border-t border-gray-100 dark:border-dark-200">
                        <button type="submit" class="px-8 py-3 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white font-bold rounded-xl shadow-lg shadow-orange-500/20 transition-all duration-200 flex items-center gap-2 transform hover:scale-[1.02]">
                            <i class="bi bi-check-circle-fill"></i>
                            Salvar Altera&ccedil;&otilde;es
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tabs = document.querySelectorAll('[data-tabs-target]');
        const panels = document.querySelectorAll('[role="tabpanel"]');
        
        // Função para ativar aba
        function activateTab(targetId) {
            // Ocultar todos os painéis
            panels.forEach(panel => panel.classList.add('hidden'));
            
            // Remover estado ativo de todas as abas
            tabs.forEach(tab => {
                tab.setAttribute('aria-selected', 'false');
                tab.classList.remove('border-orange-500', 'text-orange-600', 'dark:text-orange-500');
                tab.classList.add('border-transparent', 'text-gray-500', 'dark:text-gray-400');
            });
            
            // Mostrar painel alvo
            const targetPanel = document.querySelector(targetId);
            if (targetPanel) targetPanel.classList.remove('hidden');
            
            // Ativar aba clicada
            const activeTab = document.querySelector(`[data-tabs-target="${targetId}"]`);
            if (activeTab) {
                activeTab.setAttribute('aria-selected', 'true');
                activeTab.classList.remove('border-transparent', 'text-gray-500', 'dark:text-gray-400');
                activeTab.classList.add('border-orange-500', 'text-orange-600', 'dark:text-orange-500');
            }
        }

        // Event Listeners
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                const target = tab.getAttribute('data-tabs-target');
                activateTab(target);
                // Atualizar URL hash sem scrollar
                history.pushState(null, null, target);
            });
        });

        // Verificar se há hash na URL (ex: #preferences) e ativar aba correspondente
        if (window.location.hash) {
            const hash = window.location.hash;
            if (document.querySelector(hash)) {
                activateTab(hash);
            }
        }
    });
</script>
@endpush
@endsection
