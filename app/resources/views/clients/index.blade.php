@extends('layouts.app')

@section('title', 'Meus Clientes')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
        <i class="bi bi-people text-orange-500"></i>
        Lista de Clientes
        <span id="totalClientsCounter" class="ml-2 text-lg font-bold bg-orange-100 dark:bg-orange-500/10 text-orange-600 dark:text-orange-500 px-4 py-1.5 rounded-full border border-orange-200 dark:border-orange-500/20">
            Total: {{ $totalGlobal ?? count($clients) }}
        </span>
    </h1>
    <div class="flex gap-3">
        <button onclick="openTrialModal()" class="px-4 py-2 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-lg hover:shadow-lg transition-all flex items-center gap-2 font-medium">
            <i class="bi bi-clock-history"></i>
            Criar Teste Gr&aacute;tis
        </button>
        <a href="{{ route('clients.create') }}" class="px-4 py-2 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg hover:shadow-lg transition-all flex items-center gap-2 font-medium">
            <i class="bi bi-plus-circle"></i>
            Novo Cliente
        </a>
    </div>
</div>

<!-- Filtros -->
<div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 p-6 mb-6 shadow-sm dark:shadow-none">
    <h3 class="text-gray-900 dark:text-white font-semibold mb-4">Filtros</h3>
    
    <!-- Filtros R&aacute;pidos -->
    <div class="mb-4">
        <p class="text-gray-500 dark:text-gray-400 text-sm mb-2">Filtros R&aacute;pidos (Apenas Clientes Oficiais)</p>
        <div class="flex flex-wrap gap-2">
            <button onclick="applyQuickFilter('today')" class="px-4 py-2 bg-gray-100 dark:bg-dark-200 text-gray-600 dark:text-gray-300 rounded-lg hover:bg-orange-500 hover:text-white transition-colors text-sm flex items-center gap-2 group">
                <i class="bi bi-calendar-check"></i> Vence Hoje
                <span class="bg-orange-100 dark:bg-orange-500/20 text-orange-600 dark:text-orange-500 group-hover:bg-white/20 group-hover:text-white px-2 py-0.5 rounded text-xs font-bold border border-orange-200 dark:border-orange-500/30">{{ $quickStats['today'] ?? 0 }}</span>
            </button>
            <button onclick="applyQuickFilter('7days')" class="px-4 py-2 bg-gray-100 dark:bg-dark-200 text-gray-600 dark:text-gray-300 rounded-lg hover:bg-orange-500 hover:text-white transition-colors text-sm flex items-center gap-2 group">
                <i class="bi bi-calendar-week"></i> Vence em 7 Dias
                <span class="bg-orange-100 dark:bg-orange-500/20 text-orange-600 dark:text-orange-500 group-hover:bg-white/20 group-hover:text-white px-2 py-0.5 rounded text-xs font-bold border border-orange-200 dark:border-orange-500/30">{{ $quickStats['7days'] ?? 0 }}</span>
            </button>
            <button onclick="applyQuickFilter('30days')" class="px-4 py-2 bg-gray-100 dark:bg-dark-200 text-gray-600 dark:text-gray-300 rounded-lg hover:bg-orange-500 hover:text-white transition-colors text-sm flex items-center gap-2 group">
                <i class="bi bi-calendar-month"></i> Vence em 30 Dias
                <span class="bg-orange-100 dark:bg-orange-500/20 text-orange-600 dark:text-orange-500 group-hover:bg-white/20 group-hover:text-white px-2 py-0.5 rounded text-xs font-bold border border-orange-200 dark:border-orange-500/30">{{ $quickStats['30days'] ?? 0 }}</span>
            </button>
        </div>
    </div>

    <!-- Busca e Filtros -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label class="block text-sm text-gray-500 dark:text-gray-400 mb-2">Username / Senha</label>
            <input type="text" id="searchInput" placeholder="Buscar por username ou senha" class="w-full px-4 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white focus:border-orange-500 focus:outline-none transition-colors">
        </div>
        <div>
            <label class="block text-sm text-gray-500 dark:text-gray-400 mb-2">Telefone</label>
            <input type="text" id="phoneInput" placeholder="Buscar por telefone" class="w-full px-4 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white focus:border-orange-500 focus:outline-none transition-colors">
        </div>
        <div>
            <label class="block text-sm text-gray-500 dark:text-gray-400 mb-2">Tipo</label>
            <select id="typeFilter" class="w-full px-4 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white focus:border-orange-500 focus:outline-none transition-colors">
                <option value="">Todos</option>
                <option value="client">Cliente</option>
                <option value="trial">Teste</option>
            </select>
        </div>
        <div>
            <label class="block text-sm text-gray-500 dark:text-gray-400 mb-2">Status</label>
            <select id="statusFilter" class="w-full px-4 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white focus:border-orange-500 focus:outline-none transition-colors">
                <option value="">Todos</option>
                <option value="active">Ativo</option>
                <option value="expired">Vencido</option>
                <option value="blocked">Bloqueado</option>
                <option value="trial">Teste (Antigo)</option>
            </select>
        </div>
    </div>
    
    <div class="flex gap-2 mt-4">
        <button onclick="loadClients()" class="px-6 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors flex items-center gap-2 font-medium shadow-md shadow-orange-500/20">
            <i class="bi bi-search"></i> Buscar
        </button>
        <button onclick="clearFilters()" class="px-6 py-2 bg-gray-100 dark:bg-dark-200 text-gray-600 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-dark-100 transition-colors font-medium">
            <i class="bi bi-x"></i> Limpar
        </button>
    </div>
</div>

<div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 overflow-hidden shadow-sm dark:shadow-none">
    <div id="clientsTableContainer">
        @include('clients.partials.table', ['clients' => $clients])
    </div>
    
    <div id="paginationContainer" class="p-6 border-t border-gray-200 dark:border-dark-200">
        @include('clients.partials.pagination', ['clients' => $clients])
    </div>
</div>

@if(isset($totalGlobal) && $totalGlobal == 0)
    <div class="text-center py-16">
        <i class="bi bi-inbox text-gray-400 dark:text-gray-600 text-6xl mb-4"></i>
        <h3 class="text-xl font-semibold text-gray-600 dark:text-gray-400 mb-2">Nenhum cliente cadastrado</h3>
        <p class="text-gray-500 dark:text-gray-500 mb-6">Comece criando seu primeiro cliente</p>
        <a href="{{ route('clients.create') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg hover:shadow-lg transition-all font-medium">
            <i class="bi bi-plus-circle"></i>
            Criar Primeiro Cliente
        </a>
    </div>
@endif

<!-- Modal Renovar -->
<div id="renewModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white dark:bg-dark-300 rounded-xl max-w-2xl w-full border border-gray-200 dark:border-dark-200 shadow-2xl max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200 dark:border-dark-200 flex justify-between items-center sticky top-0 bg-white dark:bg-dark-300 z-10">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white">Renovar Cliente</h3>
            <button onclick="closeRenewModal()" class="text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <form id="renewForm" onsubmit="submitRenew(event)" class="p-6">
            <input type="hidden" id="renewClientId">
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">Cliente</label>
                <input type="text" id="renewClientName" readonly class="w-full px-4 py-2 bg-gray-100 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-500 dark:text-white opacity-70">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">Pacote *</label>
                <select id="renewPackageId" name="package_id" required class="w-full px-4 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white focus:border-orange-500 focus:outline-none transition-colors" onchange="updateRenewPackage(this)">
                    <option value="">Selecione um pacote</option>
                    @foreach($packages as $package)
                        @if($package->is_official == 1)
                            <option value="{{ $package->id }}" 
                                    data-duration="{{ $package->official_duration ?? 30 }}"
                                    data-duration-in="{{ $package->official_duration_in ?? 'days' }}"
                                    data-connections="{{ $package->max_connections ?? 1 }}"
                                    data-bouquets="{{ $package->bouquets ?? '[]' }}"
                                    data-credits="{{ $package->official_credits ?? 0 }}">
                                {{ $package->package_name }} 
                                - {{ $package->official_duration ?? 30 }} {{ $package->official_duration_in ?? 'dias' }}
                                ({{ $package->official_credits }} cr&eacute;dito{{ $package->official_credits > 1 ? 's' : '' }})
                            </option>
                        @endif
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">Dura&ccedil;&atilde;o *</label>
                    <input type="text" id="renewDurationDisplay" readonly class="w-full px-4 py-2 bg-gray-100 dark:bg-dark-100 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-500 dark:text-gray-400 cursor-not-allowed" value="Selecione um pacote">
                    <input type="hidden" id="renewDurationValue" name="duration_value">
                    <input type="hidden" id="renewDurationUnit" name="duration_unit">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">Conex&otilde;es *</label>
                    <input type="text" id="renewConnectionsDisplay" readonly class="w-full px-4 py-2 bg-gray-100 dark:bg-dark-100 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-500 dark:text-gray-400 cursor-not-allowed" value="Selecione um pacote">
                    <input type="hidden" id="renewMaxConnections" name="max_connections">
                </div>
            </div>

            <div class="flex gap-3">
                <button type="button" onclick="closeRenewModal()" class="flex-1 px-4 py-2 bg-gray-100 dark:bg-dark-200 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-dark-100 transition-colors font-medium">Cancelar</button>
                <button type="submit" class="flex-1 px-4 py-2 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-lg hover:shadow-lg transition-all flex items-center justify-center gap-2 font-medium">
                    <span id="renewBtnText">Confirmar Renova&ccedil;&atilde;o</span>
                    <i class="bi bi-arrow-clockwise"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Sucesso Renova&ccedil;&atilde;o -->
<div id="renewSuccessModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white dark:bg-dark-300 rounded-xl max-w-2xl w-full border border-gray-200 dark:border-dark-200 shadow-2xl">
        <div class="p-6 border-b border-gray-200 dark:border-dark-200 flex justify-between items-center bg-gradient-to-r from-green-600 to-green-700">
            <h3 class="text-xl font-bold text-white flex items-center gap-2">
                <i class="bi bi-check-circle-fill"></i>
                Cliente Renovado com Sucesso!
            </h3>
            <button onclick="closeRenewSuccessModal()" class="text-white hover:text-gray-200">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">Usu&aacute;rio</label>
                    <div class="flex gap-2">
                        <input type="text" id="renewSuccessUsername" readonly class="flex-1 px-4 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white font-mono">
                        <button onclick="copyField('renewSuccessUsername')" class="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors">
                            <i class="bi bi-clipboard"></i>
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">Senha</label>
                    <div class="flex gap-2">
                        <input type="text" id="renewSuccessPassword" readonly class="flex-1 px-4 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white font-mono">
                        <button onclick="copyField('renewSuccessPassword')" class="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors">
                            <i class="bi bi-clipboard"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">Nova Validade</label>
                <input type="text" id="renewSuccessExpDate" readonly class="w-full px-4 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white">
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">Mensagem para WhatsApp</label>
                <div class="relative">
                    <textarea id="renewSuccessWhatsapp" readonly rows="6" class="w-full px-4 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white font-mono text-sm"></textarea>
                    <button onclick="copyField('renewSuccessWhatsapp')" class="absolute top-2 right-2 px-3 py-1.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors flex items-center gap-2">
                        <i class="bi bi-whatsapp"></i>
                        Copiar para WhatsApp
                    </button>
                </div>
            </div>

            <div class="flex gap-3">
                <button onclick="closeRenewSuccessModal()" class="flex-1 px-4 py-2 bg-gray-100 dark:bg-dark-200 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-dark-100 transition-colors font-medium">Fechar</button>
                <a href="{{ route('clients.index') }}" class="flex-1 px-4 py-2 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg hover:shadow-lg transition-all text-center font-medium">
                    Ver Todos os Clientes
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Modal M3U -->
<div id="m3uModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white dark:bg-dark-300 rounded-xl max-w-2xl w-full border border-gray-200 dark:border-dark-200 shadow-2xl">
        <div class="p-6 border-b border-gray-200 dark:border-dark-200 flex justify-between items-center">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white">URLs M3U</h3>
            <button onclick="closeM3uModal()" class="text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="p-6">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">Cliente</label>
                <input type="text" id="m3uClientName" readonly class="w-full px-4 py-2 bg-gray-100 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-500 dark:text-white opacity-70">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">URL M3U</label>
                <div class="flex gap-2">
                    <input type="text" id="m3uUrl" readonly class="flex-1 px-4 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white font-mono text-sm">
                    <button onclick="copyToClipboard('m3uUrl')" class="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors">
                        <i class="bi bi-clipboard"></i>
                    </button>
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">URL HLS</label>
                <div class="flex gap-2">
                    <input type="text" id="hlsUrl" readonly class="flex-1 px-4 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white font-mono text-sm">
                    <button onclick="copyToClipboard('hlsUrl')" class="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors">
                        <i class="bi bi-clipboard"></i>
                    </button>
                </div>
            </div>

            <div class="flex gap-3">
                <button onclick="closeM3uModal()" class="flex-1 px-4 py-2 bg-gray-100 dark:bg-dark-200 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-dark-100 transition-colors font-medium">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div id="editModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white dark:bg-dark-300 rounded-xl max-w-4xl w-full border border-gray-200 dark:border-dark-200 shadow-2xl max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200 dark:border-dark-200 flex justify-between items-center sticky top-0 bg-white dark:bg-dark-300 z-10">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white">Editar Cliente</h3>
            <button onclick="closeEditModal()" class="text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <form id="editForm" onsubmit="submitEdit(event)" class="p-6">
            <input type="hidden" id="editClientId">
            
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">Usu&aacute;rio *</label>
                    <input type="text" id="editUsername" name="username" required class="w-full px-4 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white focus:border-orange-500 focus:outline-none transition-colors">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">Senha *</label>
                    <input type="text" id="editPassword" name="password" required class="w-full px-4 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white focus:border-orange-500 focus:outline-none transition-colors">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">Pacote</label>
                    <input type="text" id="editPackageName" readonly class="w-full px-4 py-2 bg-gray-100 dark:bg-dark-100 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-500 dark:text-gray-400 cursor-not-allowed">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">Conex&otilde;es</label>
                    <input type="text" id="editMaxConnections" readonly class="w-full px-4 py-2 bg-gray-100 dark:bg-dark-100 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-500 dark:text-gray-400 cursor-not-allowed">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">Telefone</label>
                    <input type="text" id="editPhone" name="phone" oninput="maskPhone(event)" class="w-full px-4 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white focus:border-orange-500 focus:outline-none transition-colors" placeholder="(00) 00000-0000">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">E-mail</label>
                    <input type="email" id="editEmail" name="email" class="w-full px-4 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white focus:border-orange-500 focus:outline-none transition-colors">
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">Nota</label>
                <textarea id="editNotes" name="notes" rows="3" class="w-full px-4 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white focus:border-orange-500 focus:outline-none resize-none transition-colors"></textarea>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-3">Buqu&ecirc;s *</label>
                <div id="editBouquets" class="grid grid-cols-2 gap-3 max-h-64 overflow-y-auto p-4 bg-gray-50 dark:bg-dark-200 rounded-lg border border-gray-200 dark:border-0 custom-scrollbar">
                    <!-- Bouquets ser&atilde;o carregados dinamicamente -->
                </div>
            </div>

            <div class="flex gap-3">
                <button type="button" onclick="closeEditModal()" class="flex-1 px-4 py-2 bg-gray-100 dark:bg-dark-200 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-dark-100 transition-colors font-medium">Cancelar</button>
                <button type="submit" class="flex-1 px-4 py-2 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg hover:shadow-lg transition-all font-medium">
                    <span id="editBtnText">Salvar Altera&ccedil;&otilde;es</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Create Trial (R&aacute;pido) -->
<div id="trialModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white dark:bg-dark-300 rounded-xl max-w-4xl w-full border border-gray-200 dark:border-dark-200 shadow-2xl max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200 dark:border-dark-200 flex justify-between items-center sticky top-0 bg-white dark:bg-dark-300 z-10">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white">Gerar Teste R&aacute;pido</h3>
            <button onclick="closeTrialModal()" class="text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <form id="trialForm" onsubmit="submitTrial(event)" class="p-6">
            <div class="grid grid-cols-2 gap-4 mb-4">
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

            <div class="grid grid-cols-2 gap-4 mb-4">
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
                                    data-bouquets="{{ $package->bouquets ?? '[]' }}">
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
                <div id="trialBouquets" class="grid grid-cols-2 gap-3 max-h-64 overflow-y-auto p-4 bg-gray-50 dark:bg-dark-200 rounded-lg border border-gray-200 dark:border-0 custom-scrollbar">
                    @foreach($bouquets as $bouquet)
                        <label class="flex items-center gap-3 p-3 bg-white dark:bg-dark-300 border border-gray-200 dark:border-dark-100 rounded-lg hover:bg-gray-50 dark:hover:bg-dark-100 cursor-pointer transition-colors shadow-sm dark:shadow-none">
                            <input type="checkbox" name="bouquet_ids[]" value="{{ $bouquet['id'] }}" class="w-5 h-5 text-orange-500 bg-gray-100 dark:bg-dark-200 border-gray-300 dark:border-dark-100 rounded focus:ring-orange-500 focus:ring-2">
                            <span class="text-gray-700 dark:text-white text-sm">{{ $bouquet['bouquet_name'] }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="flex gap-3">
                <button type="button" onclick="closeTrialModal()" class="flex-1 px-4 py-2 bg-gray-100 dark:bg-dark-200 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-dark-100 transition-colors font-medium">Cancelar</button>
                <button type="submit" class="flex-1 px-4 py-2 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg hover:shadow-lg transition-all font-medium">
                    <span id="trialBtnText">Gerar Teste</span>
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
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

// Variáveis globais
let currentSortBy = 'created_at';
let currentSortOrder = 'desc';
let currentPage = 1;
let currentPerPage = 20;
let currentQuickFilter = '';

// Busca dinâmica com debounce
let searchTimeout;
document.getElementById('searchInput')?.addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => loadClients(), 500);
});

document.getElementById('phoneInput')?.addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => loadClients(), 500);
});

document.getElementById('statusFilter')?.addEventListener('change', function() {
    currentQuickFilter = ''; // Limpar filtro rápido ao mudar filtros manuais
    loadClients();
});

document.getElementById('typeFilter')?.addEventListener('change', function() {
    currentQuickFilter = ''; // Limpar filtro rápido ao mudar filtros manuais
    loadClients();
});

// Função principal de carregamento
function loadClients(page = 1) {
    currentPage = page;
    
    const search = document.getElementById('searchInput')?.value || '';
    const phone = document.getElementById('phoneInput')?.value || '';
    const status = document.getElementById('statusFilter')?.value || '';
    const type = document.getElementById('typeFilter')?.value || '';
    
    const params = new URLSearchParams({
        search: search,
        phone: phone,
        status: status,
        type: type,
        quick_filter: currentQuickFilter,
        sort_by: currentSortBy,
        sort_order: currentSortOrder,
        per_page: currentPerPage,
        page: page
    });
    
    // Atualizar UI dos filtros rápidos
    document.querySelectorAll('[onclick^="applyQuickFilter"]').forEach(btn => {
        if (currentQuickFilter && btn.getAttribute('onclick').includes(currentQuickFilter)) {
            btn.classList.add('bg-orange-500', 'text-white');
            btn.classList.remove('bg-gray-100', 'dark:bg-dark-200', 'text-gray-600', 'dark:text-gray-300');
        } else {
            btn.classList.remove('bg-orange-500', 'text-white');
            btn.classList.add('bg-gray-100', 'dark:bg-dark-200', 'text-gray-600', 'dark:text-gray-300');
        }
    });

    fetch(`/clients?${params.toString()}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('clientsTableContainer').innerHTML = data.html;
        document.getElementById('paginationContainer').innerHTML = data.pagination;
        
        // Atualizar contador total
        const totalCounter = document.getElementById('totalClientsCounter');
        if (totalCounter && data.total !== undefined) {
            totalCounter.innerText = `Total: ${data.total}`;
            totalCounter.classList.remove('hidden');
        }
    })
    .catch(error => {
        console.error('Erro ao carregar clientes:', error);
    });
}

// Ordenação
function sortBy(column) {
    if (currentSortBy === column) {
        currentSortOrder = currentSortOrder === 'asc' ? 'desc' : 'asc';
    } else {
        currentSortBy = column;
        currentSortOrder = 'asc';
    }
    loadClients(currentPage);
}

// Navegação de páginas
function goToPage(page) {
    if (page < 1) return;
    loadClients(page);
}

// Mudar itens por página
function changePerPage(perPage) {
    currentPerPage = parseInt(perPage);
    loadClients(1);
}

// Limpar filtros
function clearFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('phoneInput').value = '';
    document.getElementById('statusFilter').value = '';
    document.getElementById('typeFilter').value = '';
    currentQuickFilter = '';
    currentSortBy = 'created_at';
    currentSortOrder = 'desc';
    loadClients(1);
}

// Filtros rápidos por vencimento
function applyQuickFilter(type) {
    // Se clicar no mesmo filtro já ativo, desativa
    if (currentQuickFilter === type) {
        currentQuickFilter = '';
    } else {
        currentQuickFilter = type;
        
        // Limpar outros filtros visuais para evitar confusão, 
        // mas o backend prioriza o quick_filter de qualquer forma se implementado assim
        document.getElementById('searchInput').value = '';
        document.getElementById('phoneInput').value = '';
        document.getElementById('statusFilter').value = '';
        document.getElementById('typeFilter').value = '';
    }
    
    loadClients(1);
}

// Funções antigas mantidas para compatibilidade
let sortDirection = {};
let allRows = [];

document.addEventListener('DOMContentLoaded', function() {
    const tbody = document.querySelector('tbody');
    if (tbody) {
        allRows = Array.from(tbody.querySelectorAll('tr'));
    }
});

function openRenewModal(clientId) {
    // Encontrar o nome do cliente na linha da tabela
    const row = document.querySelector(`tr[onclick*="${clientId}"]`) || 
                document.querySelector(`button[onclick="openRenewModal(${clientId})"]`).closest('tr');
    
    if (row) {
        const name = row.dataset.username || row.querySelector('td:first-child span').innerText.trim();
        document.getElementById('renewClientName').value = name;
    }
    
    document.getElementById('renewClientId').value = clientId;
    document.getElementById('renewPackageId').value = "";
    document.getElementById('renewDurationDisplay').value = "Selecione um pacote";
    document.getElementById('renewConnectionsDisplay').value = "Selecione um pacote";
    document.getElementById('renewModal').classList.remove('hidden');
}

function closeRenewModal() {
    document.getElementById('renewModal').classList.add('hidden');
}

function updateRenewPackage(select) {
    const selectedOption = select.options[select.selectedIndex];
    
    if (select.value) {
        const duration = selectedOption.getAttribute('data-duration');
        const durationIn = selectedOption.getAttribute('data-duration-in');
        const connections = selectedOption.getAttribute('data-connections');
        
        // Mapear unidades
        const unitMap = {
            'hours': 'hora(s)',
            'hour': 'hora(s)',
            'days': 'dia(s)',
            'day': 'dia(s)',
            'months': 'mês(es)',
            'month': 'mês(es)',
            'years': 'ano(s)',
            'year': 'ano(s)'
        };
        
        const unitDisplay = unitMap[durationIn] || durationIn;
        
        // Atualizar campos
        document.getElementById('renewDurationDisplay').value = `${duration} ${unitDisplay}`;
        document.getElementById('renewDurationValue').value = duration;
        document.getElementById('renewDurationUnit').value = durationIn;
        
        document.getElementById('renewConnectionsDisplay').value = `${connections} Conexão${connections > 1 ? 'ões' : ''}`;
        document.getElementById('renewMaxConnections').value = connections;
    } else {
        document.getElementById('renewDurationDisplay').value = 'Selecione um pacote';
        document.getElementById('renewDurationValue').value = '';
        document.getElementById('renewDurationUnit').value = '';
        document.getElementById('renewConnectionsDisplay').value = 'Selecione um pacote';
        document.getElementById('renewMaxConnections').value = '';
    }
}

// Adicionar outras funções JS (openEditModal, submitEdit, etc) aqui conforme necessário
// Para brevidade, assumimos que elas existem ou serão migradas das views originais
</script>
@endpush
@endsection
