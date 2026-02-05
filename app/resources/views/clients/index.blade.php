@extends('layouts.app')

@section('title', 'Meus Clientes')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-white flex items-center gap-3">
        <i class="bi bi-people text-orange-500"></i>
        Lista de Clientes
    </h1>
    <div class="flex gap-3">
        <button onclick="openTrialModal()" class="px-4 py-2 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-lg hover:shadow-lg transition-all flex items-center gap-2">
            <i class="bi bi-clock-history"></i>
            Criar Teste Grátis
        </button>
        <a href="{{ route('clients.create') }}" class="px-4 py-2 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg hover:shadow-lg transition-all flex items-center gap-2">
            <i class="bi bi-plus-circle"></i>
            Novo Cliente
        </a>
    </div>
</div>

<!-- Filtros -->
<div class="bg-dark-300 rounded-xl border border-dark-200 p-6 mb-6">
    <h3 class="text-white font-semibold mb-4">Filtros</h3>
    
    <!-- Filtros Rápidos -->
    <div class="mb-4">
        <p class="text-gray-400 text-sm mb-2">Filtros Rápidos (Apenas Clientes Oficiais)</p>
        <div class="flex flex-wrap gap-2">
            <button onclick="applyQuickFilter('today')" class="px-4 py-2 bg-dark-200 text-gray-300 rounded-lg hover:bg-orange-500 hover:text-white transition-colors text-sm flex items-center gap-2">
                <i class="bi bi-calendar-check"></i> Vence Hoje
                <span class="bg-orange-500/20 text-orange-500 group-hover:bg-white/20 group-hover:text-white px-2 py-0.5 rounded text-xs font-bold border border-orange-500/30">{{ $quickStats['today'] ?? 0 }}</span>
            </button>
            <button onclick="applyQuickFilter('7days')" class="px-4 py-2 bg-dark-200 text-gray-300 rounded-lg hover:bg-orange-500 hover:text-white transition-colors text-sm flex items-center gap-2">
                <i class="bi bi-calendar-week"></i> Vence em 7 Dias
                <span class="bg-orange-500/20 text-orange-500 group-hover:bg-white/20 group-hover:text-white px-2 py-0.5 rounded text-xs font-bold border border-orange-500/30">{{ $quickStats['7days'] ?? 0 }}</span>
            </button>
            <button onclick="applyQuickFilter('30days')" class="px-4 py-2 bg-dark-200 text-gray-300 rounded-lg hover:bg-orange-500 hover:text-white transition-colors text-sm flex items-center gap-2">
                <i class="bi bi-calendar-month"></i> Vence em 30 Dias
                <span class="bg-orange-500/20 text-orange-500 group-hover:bg-white/20 group-hover:text-white px-2 py-0.5 rounded text-xs font-bold border border-orange-500/30">{{ $quickStats['30days'] ?? 0 }}</span>
            </button>
        </div>
    </div>

    <!-- Busca e Filtros -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label class="block text-sm text-gray-400 mb-2">Username / Senha</label>
            <input type="text" id="searchInput" placeholder="Buscar por username ou senha" class="w-full px-4 py-2 bg-dark-200 border border-dark-100 rounded-lg text-white focus:border-orange-500 focus:outline-none">
        </div>
        <div>
            <label class="block text-sm text-gray-400 mb-2">Telefone</label>
            <input type="text" id="phoneInput" placeholder="Buscar por telefone" class="w-full px-4 py-2 bg-dark-200 border border-dark-100 rounded-lg text-white focus:border-orange-500 focus:outline-none">
        </div>
        <div>
            <label class="block text-sm text-gray-400 mb-2">Tipo</label>
            <select id="typeFilter" class="w-full px-4 py-2 bg-dark-200 border border-dark-100 rounded-lg text-white focus:border-orange-500 focus:outline-none">
                <option value="">Todos</option>
                <option value="client">Cliente</option>
                <option value="trial">Teste</option>
            </select>
        </div>
        <div>
            <label class="block text-sm text-gray-400 mb-2">Status</label>
            <select id="statusFilter" class="w-full px-4 py-2 bg-dark-200 border border-dark-100 rounded-lg text-white focus:border-orange-500 focus:outline-none">
                <option value="">Todos</option>
                <option value="active">Ativo</option>
                <option value="expired">Vencido</option>
                <option value="blocked">Bloqueado</option>
                <option value="trial">Teste (Antigo)</option>
            </select>
        </div>
    </div>
    
    <div class="flex gap-2 mt-4">
        <button onclick="loadClients()" class="px-6 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors flex items-center gap-2">
            <i class="bi bi-search"></i> Buscar
        </button>
        <button onclick="clearFilters()" class="px-6 py-2 bg-dark-200 text-gray-300 rounded-lg hover:bg-dark-100 transition-colors">
            <i class="bi bi-x"></i> Limpar
        </button>
    </div>
</div>

<div class="bg-dark-300 rounded-xl border border-dark-200 overflow-hidden">
    <div id="clientsTableContainer">
        @include('clients.partials.table', ['clients' => $clients])
    </div>
    
    <div id="paginationContainer" class="p-6 border-t border-dark-200">
        @include('clients.partials.pagination', ['clients' => $clients])
    </div>
</div>

<!-- Tabela antiga removida -->
@if(false)
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-dark-200 border-b border-dark-100">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider cursor-pointer hover:text-orange-500" onclick="sortTable('username')">
                        USERNAME <i class="bi bi-arrow-down-up"></i>
                    </th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider cursor-pointer hover:text-orange-500" onclick="sortTable('password')">
                        SENHA <i class="bi bi-arrow-down-up"></i>
                    </th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">REVENDA</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">TIPO</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider cursor-pointer hover:text-orange-500" onclick="sortTable('created_at')">
                        CRIADO <i class="bi bi-arrow-down-up"></i>
                    </th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider cursor-pointer hover:text-orange-500" onclick="sortTable('exp_date')">
                        VENCIMENTO <i class="bi bi-arrow-down-up"></i>
                    </th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">CONEXÕES</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider cursor-pointer hover:text-orange-500" onclick="sortTable('status')">
                        STATUS <i class="bi bi-arrow-down-up"></i>
                    </th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">AÇÕES</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-dark-200">
                @foreach($clients as $client)
                @php
                    $expDate = is_numeric($client['exp_date']) ? $client['exp_date'] : strtotime($client['exp_date']);
                    $isExpired = $expDate < time();
                    $daysLeft = ceil(($expDate - time()) / 86400);
                @endphp
                <tr class="hover:bg-dark-200 transition-colors duration-150" data-username="{{ $client['username'] }}" data-password="{{ $client['password'] }}" data-status="{{ $isExpired ? 'expired' : 'active' }}" data-trial="{{ $client['is_trial'] ?? 0 }}" data-exp="{{ $expDate }}" data-created="{{ $client['created_at'] ?? 0 }}">
                    <td class="px-6 py-4">
                        <span class="text-white font-medium">{{ $client['username'] }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <code class="text-gray-300 text-sm">{{ $client['password'] }}</code>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-gray-400 text-sm">{{ $client['member_id'] ?? 'N/A' }}</span>
                    </td>
                    <td class="px-6 py-4">
                        @if(isset($client['is_trial']) && $client['is_trial'] == 1)
                            <span class="px-2 py-1 bg-yellow-500/10 text-yellow-500 text-xs font-semibold rounded">Teste</span>
                        @else
                            <span class="px-2 py-1 bg-green-500/10 text-green-500 text-xs font-semibold rounded">Cliente</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-gray-300 text-sm">{{ isset($client['created_at']) ? date('d/m/Y, H:i', is_numeric($client['created_at']) ? $client['created_at'] : strtotime($client['created_at'])) : 'N/A' }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-sm {{ $isExpired ? 'text-red-400' : ($daysLeft <= 7 ? 'text-yellow-400' : 'text-gray-300') }}">
                            {{ date('d/m/Y, H:i', $expDate) }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-1">
                            <i class="bi bi-broadcast text-blue-400"></i>
                            <span class="text-white font-semibold">{{ $client['max_connections'] ?? 1 }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        @if(isset($client['enabled']) && $client['enabled'] == 1 && !$isExpired)
                            <span class="px-2 py-1 bg-green-500/10 text-green-400 text-xs font-semibold rounded">Ativo</span>
                        @else
                            <span class="px-2 py-1 bg-red-500/10 text-red-400 text-xs font-semibold rounded">Vencido</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-1">
                            <button onclick="openM3uModal({{ $client['id'] }}, '{{ $client['username'] }}')" class="p-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors" title="Links M3U">
                                <i class="bi bi-link-45deg"></i>
                            </button>
                            <button onclick="openEditModal({{ $client['id'] }})" class="p-2 bg-gray-600 text-white rounded hover:bg-gray-700 transition-colors" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button onclick="openRenewModal({{ $client['id'] }})" class="p-2 bg-green-600 text-white rounded hover:bg-green-700 transition-colors" title="Renovar">
                                <i class="bi bi-arrow-clockwise"></i>
                            </button>
                            <button onclick="killConnections({{ $client['id'] }})" class="p-2 bg-orange-600 text-white rounded hover:bg-orange-700 transition-colors" title="Destruir Conexão">
                                <i class="bi bi-x-circle"></i>
                            </button>
                            <a href="{{ route('clients.m3u', $client['id']) }}" class="p-2 bg-purple-600 text-white rounded hover:bg-purple-700 transition-colors" title="Download M3U" download>
                                <i class="bi bi-download"></i>
                            </a>
                            <button onclick="deleteClient({{ $client['id'] }})" class="p-2 bg-red-600 text-white rounded hover:bg-red-700 transition-colors" title="Excluir">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="text-center py-16">
        <i class="bi bi-inbox text-gray-600 text-6xl mb-4"></i>
        <h3 class="text-xl font-semibold text-gray-400 mb-2">Nenhum cliente cadastrado</h3>
        <p class="text-gray-500 mb-6">Comece criando seu primeiro cliente</p>
        <a href="{{ route('clients.create') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg hover:shadow-lg transition-all">
            <i class="bi bi-plus-circle"></i>
            Criar Primeiro Cliente
        </a>
    </div>
    @endif
</div>

<!-- Modal Renovar -->
<div id="renewModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-dark-300 rounded-xl max-w-2xl w-full border border-dark-200 shadow-2xl max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-dark-200 flex justify-between items-center sticky top-0 bg-dark-300 z-10">
            <h3 class="text-xl font-bold text-white">Renovar Cliente</h3>
            <button onclick="closeRenewModal()" class="text-gray-400 hover:text-white">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <form id="renewForm" onsubmit="submitRenew(event)" class="p-6">
            <input type="hidden" id="renewClientId">
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-400 mb-2">Cliente</label>
                <input type="text" id="renewClientName" readonly class="w-full px-4 py-2 bg-dark-200 border border-dark-100 rounded-lg text-white opacity-70">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-400 mb-2">Pacote *</label>
                <select id="renewPackageId" name="package_id" required class="w-full px-4 py-2 bg-dark-200 border border-dark-100 rounded-lg text-white focus:border-orange-500 focus:outline-none" onchange="updateRenewPackage(this)">
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
                                ({{ $package->official_credits }} crédito{{ $package->official_credits > 1 ? 's' : '' }})
                            </option>
                        @endif
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Duração *</label>
                    <input type="text" id="renewDurationDisplay" readonly class="w-full px-4 py-2 bg-dark-100 border border-dark-100 rounded-lg text-gray-400 cursor-not-allowed" value="Selecione um pacote">
                    <input type="hidden" id="renewDurationValue" name="duration_value">
                    <input type="hidden" id="renewDurationUnit" name="duration_unit">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Conexões *</label>
                    <input type="text" id="renewConnectionsDisplay" readonly class="w-full px-4 py-2 bg-dark-100 border border-dark-100 rounded-lg text-gray-400 cursor-not-allowed" value="Selecione um pacote">
                    <input type="hidden" id="renewMaxConnections" name="max_connections">
                </div>
            </div>

            <div class="flex gap-3">
                <button type="button" onclick="closeRenewModal()" class="flex-1 px-4 py-2 bg-dark-200 text-gray-300 rounded-lg hover:bg-dark-100 transition-colors">Cancelar</button>
                <button type="submit" class="flex-1 px-4 py-2 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-lg hover:shadow-lg transition-all flex items-center justify-center gap-2">
                    <span id="renewBtnText">Confirmar Renovação</span>
                    <i class="bi bi-arrow-clockwise"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Sucesso Renovação -->
<div id="renewSuccessModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-dark-300 rounded-xl max-w-2xl w-full border border-dark-200 shadow-2xl">
        <div class="p-6 border-b border-dark-200 flex justify-between items-center bg-gradient-to-r from-green-600 to-green-700">
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
                    <label class="block text-sm font-medium text-gray-400 mb-2">Usuário</label>
                    <div class="flex gap-2">
                        <input type="text" id="renewSuccessUsername" readonly class="flex-1 px-4 py-2 bg-dark-200 border border-dark-100 rounded-lg text-white font-mono">
                        <button onclick="copyField('renewSuccessUsername')" class="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600">
                            <i class="bi bi-clipboard"></i>
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Senha</label>
                    <div class="flex gap-2">
                        <input type="text" id="renewSuccessPassword" readonly class="flex-1 px-4 py-2 bg-dark-200 border border-dark-100 rounded-lg text-white font-mono">
                        <button onclick="copyField('renewSuccessPassword')" class="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600">
                            <i class="bi bi-clipboard"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-400 mb-2">Nova Validade</label>
                <input type="text" id="renewSuccessExpDate" readonly class="w-full px-4 py-2 bg-dark-200 border border-dark-100 rounded-lg text-white">
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-400 mb-2">Mensagem para WhatsApp</label>
                <div class="relative">
                    <textarea id="renewSuccessWhatsapp" readonly rows="6" class="w-full px-4 py-2 bg-dark-200 border border-dark-100 rounded-lg text-white font-mono text-sm"></textarea>
                    <button onclick="copyField('renewSuccessWhatsapp')" class="absolute top-2 right-2 px-3 py-1.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors flex items-center gap-2">
                        <i class="bi bi-whatsapp"></i>
                        Copiar para WhatsApp
                    </button>
                </div>
            </div>

            <div class="flex gap-3">
                <button onclick="closeRenewSuccessModal()" class="flex-1 px-4 py-2 bg-dark-200 text-gray-300 rounded-lg hover:bg-dark-100 transition-colors">Fechar</button>
                <a href="{{ route('clients.index') }}" class="flex-1 px-4 py-2 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg hover:shadow-lg transition-all text-center">
                    Ver Todos os Clientes
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Modal M3U -->
<div id="m3uModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-dark-300 rounded-xl max-w-2xl w-full border border-dark-200 shadow-2xl">
        <div class="p-6 border-b border-dark-200 flex justify-between items-center">
            <h3 class="text-xl font-bold text-white">URLs M3U</h3>
            <button onclick="closeM3uModal()" class="text-gray-400 hover:text-white">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="p-6">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-400 mb-2">Cliente</label>
                <input type="text" id="m3uClientName" readonly class="w-full px-4 py-2 bg-dark-200 border border-dark-100 rounded-lg text-white opacity-70">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-400 mb-2">URL M3U</label>
                <div class="flex gap-2">
                    <input type="text" id="m3uUrl" readonly class="flex-1 px-4 py-2 bg-dark-200 border border-dark-100 rounded-lg text-white font-mono text-sm">
                    <button onclick="copyToClipboard('m3uUrl')" class="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors">
                        <i class="bi bi-clipboard"></i>
                    </button>
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-400 mb-2">URL HLS</label>
                <div class="flex gap-2">
                    <input type="text" id="hlsUrl" readonly class="flex-1 px-4 py-2 bg-dark-200 border border-dark-100 rounded-lg text-white font-mono text-sm">
                    <button onclick="copyToClipboard('hlsUrl')" class="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors">
                        <i class="bi bi-clipboard"></i>
                    </button>
                </div>
            </div>

            <div class="flex gap-3">
                <button onclick="closeM3uModal()" class="flex-1 px-4 py-2 bg-dark-200 text-gray-300 rounded-lg hover:bg-dark-100 transition-colors">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div id="editModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-dark-300 rounded-xl max-w-4xl w-full border border-dark-200 shadow-2xl max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-dark-200 flex justify-between items-center sticky top-0 bg-dark-300 z-10">
            <h3 class="text-xl font-bold text-white">Editar Cliente</h3>
            <button onclick="closeEditModal()" class="text-gray-400 hover:text-white">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <form id="editForm" onsubmit="submitEdit(event)" class="p-6">
            <input type="hidden" id="editClientId">
            
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Usuário *</label>
                    <input type="text" id="editUsername" name="username" required class="w-full px-4 py-2 bg-dark-200 border border-dark-100 rounded-lg text-white focus:border-orange-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Senha *</label>
                    <input type="text" id="editPassword" name="password" required class="w-full px-4 py-2 bg-dark-200 border border-dark-100 rounded-lg text-white focus:border-orange-500 focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Pacote</label>
                    <input type="text" id="editPackageName" readonly class="w-full px-4 py-2 bg-dark-100 border border-dark-100 rounded-lg text-gray-400 cursor-not-allowed">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Conexões</label>
                    <input type="text" id="editMaxConnections" readonly class="w-full px-4 py-2 bg-dark-100 border border-dark-100 rounded-lg text-gray-400 cursor-not-allowed">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Telefone</label>
                    <input type="text" id="editPhone" name="phone" class="w-full px-4 py-2 bg-dark-200 border border-dark-100 rounded-lg text-white focus:border-orange-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">E-mail</label>
                    <input type="email" id="editEmail" name="email" class="w-full px-4 py-2 bg-dark-200 border border-dark-100 rounded-lg text-white focus:border-orange-500 focus:outline-none">
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-400 mb-2">Nota</label>
                <textarea id="editNotes" name="notes" rows="3" class="w-full px-4 py-2 bg-dark-200 border border-dark-100 rounded-lg text-white focus:border-orange-500 focus:outline-none resize-none"></textarea>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-400 mb-3">Buquês *</label>
                <div id="editBouquets" class="grid grid-cols-2 gap-3 max-h-64 overflow-y-auto p-4 bg-dark-200 rounded-lg">
                    <!-- Bouquets serão carregados dinamicamente -->
                </div>
            </div>

            <div class="flex gap-3">
                <button type="button" onclick="closeEditModal()" class="flex-1 px-4 py-2 bg-dark-200 text-gray-300 rounded-lg hover:bg-dark-100 transition-colors">Cancelar</button>
                <button type="submit" class="flex-1 px-4 py-2 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg hover:shadow-lg transition-all">
                    <span id="editBtnText">Salvar Alterações</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Create Trial -->
<div id="trialModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-dark-300 rounded-xl max-w-4xl w-full border border-dark-200 shadow-2xl max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-dark-200 flex justify-between items-center sticky top-0 bg-dark-300 z-10">
            <h3 class="text-xl font-bold text-white">Gerar Teste Rápido</h3>
            <button onclick="closeTrialModal()" class="text-gray-400 hover:text-white">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <form id="trialForm" onsubmit="submitTrial(event)" class="p-6">
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Usuário *</label>
                    <div class="flex gap-2">
                        <input type="text" id="trialUsername" name="username" required class="flex-1 px-4 py-2 bg-dark-200 border border-dark-100 rounded-lg text-white focus:border-orange-500 focus:outline-none">
                        <button type="button" onclick="generateTrialUsername()" class="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600">
                            <i class="bi bi-shuffle"></i>
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Senha *</label>
                    <div class="flex gap-2">
                        <input type="text" id="trialPassword" name="password" required class="flex-1 px-4 py-2 bg-dark-200 border border-dark-100 rounded-lg text-white focus:border-orange-500 focus:outline-none">
                        <button type="button" onclick="generateTrialPassword()" class="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600">
                            <i class="bi bi-shuffle"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-400 mb-2">Pacote de Teste *</label>
                <select id="trialPackageId" name="package_id" required class="w-full px-4 py-2 bg-dark-200 border border-dark-100 rounded-lg text-white focus:border-orange-500 focus:outline-none" onchange="updateTrialPackage(this)">
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
                <label class="block text-sm font-medium text-gray-400 mb-3">Buquês *</label>
                <div id="trialBouquets" class="grid grid-cols-2 gap-3 max-h-64 overflow-y-auto p-4 bg-dark-200 rounded-lg">
                    @foreach($bouquets as $bouquet)
                        <label class="flex items-center gap-3 p-3 bg-dark-300 rounded-lg hover:bg-dark-100 cursor-pointer transition-colors">
                            <input type="checkbox" name="bouquet_ids[]" value="{{ $bouquet['id'] }}" class="w-5 h-5 text-orange-500 bg-dark-200 border-dark-100 rounded focus:ring-orange-500">
                            <span class="text-white text-sm">{{ $bouquet['bouquet_name'] }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="flex gap-3">
                <button type="button" onclick="closeTrialModal()" class="flex-1 px-4 py-2 bg-dark-200 text-gray-300 rounded-lg hover:bg-dark-100 transition-colors">Cancelar</button>
                <button type="submit" class="flex-1 px-4 py-2 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg hover:shadow-lg transition-all">
                    <span id="trialBtnText">Gerar Teste</span>
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
// Variáveis globais
let currentSortBy = 'created_at';
let currentSortOrder = 'desc';
let currentPage = 1;
let currentPerPage = 20;

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
    loadClients();
});

// Função principal de carregamento
function loadClients(page = 1) {
    currentPage = page;
    
    const search = document.getElementById('searchInput')?.value || '';
    const phone = document.getElementById('phoneInput')?.value || '';
    const status = document.getElementById('statusFilter')?.value || '';
    
    const params = new URLSearchParams({
        search: search,
        phone: phone,
        status: status,
        sort_by: currentSortBy,
        sort_order: currentSortOrder,
        per_page: currentPerPage,
        page: page
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
    currentSortBy = 'created_at';
    currentSortOrder = 'desc';
    loadClients(1);
}

// Filtros rápidos por vencimento
function applyQuickFilter(type) {
    const now = Math.floor(Date.now() / 1000);
    let maxDays = 0;
    
    if (type === 'today') maxDays = 1;
    else if (type === '7days') maxDays = 7;
    else if (type === '30days') maxDays = 30;
    
    // Limpar outros filtros
    document.getElementById('searchInput').value = '';
    document.getElementById('phoneInput').value = '';
    document.getElementById('statusFilter').value = 'active';
    
    // Carregar e filtrar no frontend (mais rápido)
    loadClients(1);
    
    // Após carregar, filtrar por dias
    setTimeout(() => {
        const rows = document.querySelectorAll('tbody tr');
        rows.forEach(row => {
            const expDateCell = row.cells[6]?.textContent;
            if (expDateCell) {
                const parts = expDateCell.split(/[\/,\s:]+/);
                const expDate = new Date(parts[2], parts[1] - 1, parts[0], parts[3] || 0, parts[4] || 0);
                const daysLeft = Math.ceil((expDate - new Date()) / (1000 * 60 * 60 * 24));
                
                if (daysLeft >= 0 && daysLeft <= maxDays) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        });
    }, 500);
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

function submitRenew(event) {
    event.preventDefault();
    
    const clientId = document.getElementById('renewClientId').value;
    const packageId = document.getElementById('renewPackageId').value;
    const durationValue = document.getElementById('renewDurationValue').value;
    const durationUnit = document.getElementById('renewDurationUnit').value;
    const maxConnections = document.getElementById('renewMaxConnections').value;
    const btnText = document.getElementById('renewBtnText');
    const originalText = btnText.innerText;
    
    console.log('Renovação - Dados enviados:', {
        package_id: packageId,
        duration_value: durationValue,
        duration_unit: durationUnit,
        max_connections: maxConnections
    });
    
    btnText.innerText = 'Processando...';
    
    fetch(`/clients/${clientId}/renew`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            package_id: packageId,
            duration_value: durationValue,
            duration_unit: durationUnit,
            max_connections: maxConnections
        })
    })
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => {
                console.error('Resposta do servidor:', text);
                throw new Error('Erro ao renovar cliente');
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Fechar modal de renovação
            closeRenewModal();
            
            // Preencher modal de sucesso
            document.getElementById('renewSuccessUsername').value = data.client.username;
            document.getElementById('renewSuccessPassword').value = data.client.password;
            document.getElementById('renewSuccessExpDate').value = data.client.exp_date;
            
            // Formatar mensagem para WhatsApp
            const whatsappMsg = `🎉 *Renovação Confirmada!*\n\n` +
                `👤 *Usuário:* ${data.client.username}\n` +
                `🔑 *Senha:* ${data.client.password}\n` +
                `📅 *Validade:* ${data.client.exp_date}\n` +
                `📺 *Conexões:* ${data.client.max_connections}\n\n` +
                `✅ Seu acesso foi renovado com sucesso!`;
            
            document.getElementById('renewSuccessWhatsapp').value = whatsappMsg;
            
            // Mostrar modal de sucesso
            document.getElementById('renewSuccessModal').classList.remove('hidden');
        } else {
            alert(data.message || 'Erro ao renovar cliente');
            btnText.innerText = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Erro de conexão: ' + error.message);
        btnText.innerText = originalText;
    });
}

function sortTable(column) {
    const tbody = document.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    sortDirection[column] = sortDirection[column] === 'asc' ? 'desc' : 'asc';
    
    rows.sort((a, b) => {
        let aVal, bVal;
        
        if (column === 'username') {
            aVal = a.dataset.username;
            bVal = b.dataset.username;
        } else if (column === 'password') {
            aVal = a.dataset.password;
            bVal = b.dataset.password;
        } else if (column === 'exp_date') {
            aVal = parseInt(a.dataset.exp);
            bVal = parseInt(b.dataset.exp);
        } else if (column === 'created_at') {
            aVal = parseInt(a.dataset.created);
            bVal = parseInt(b.dataset.created);
        } else if (column === 'status') {
            aVal = a.dataset.status;
            bVal = b.dataset.status;
        }
        
        if (sortDirection[column] === 'asc') {
            return aVal > bVal ? 1 : -1;
        } else {
            return aVal < bVal ? 1 : -1;
        }
    });
    
    tbody.innerHTML = '';
    rows.forEach(row => tbody.appendChild(row));
}

function filterByExpiry(type) {
    const now = Math.floor(Date.now() / 1000);
    const tbody = document.querySelector('tbody');
    
    allRows.forEach(row => {
        const expDate = parseInt(row.dataset.exp);
        const daysLeft = Math.ceil((expDate - now) / 86400);
        
        let show = false;
        if (type === 'today' && daysLeft <= 1 && daysLeft >= 0) {
            show = true;
        } else if (type === '7days' && daysLeft <= 7 && daysLeft >= 0) {
            show = true;
        } else if (type === '30days' && daysLeft <= 30 && daysLeft >= 0) {
            show = true;
        }
        
        row.style.display = show ? '' : 'none';
    });
}

function applyFilters() {
    const searchInput = document.getElementById('searchInput').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value;
    const tbody = document.querySelector('tbody');
    
    allRows.forEach(row => {
        const username = row.dataset.username.toLowerCase();
        const password = row.dataset.password.toLowerCase();
        const status = row.dataset.status;
        const isTrial = row.dataset.trial === '1';
        
        let show = true;
        
        if (searchInput && !username.includes(searchInput) && !password.includes(searchInput)) {
            show = false;
        }
        
        if (statusFilter === 'active' && status !== 'active') {
            show = false;
        } else if (statusFilter === 'expired' && status !== 'expired') {
            show = false;
        } else if (statusFilter === 'trial' && !isTrial) {
            show = false;
        }
        
        row.style.display = show ? '' : 'none';
    });
}

function clearFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('statusFilter').value = '';
    document.getElementById('dateType').value = 'exp_date';
    document.getElementById('dateStart').value = '';
    
    allRows.forEach(row => {
        row.style.display = '';
    });
}

function openEditModal(clientId) {
    window.location.href = `/clients/${clientId}/edit`;
}

function renewClient(clientId) {
    if (confirm('Deseja renovar este cliente por 30 dias?')) {
        fetch(`/clients/${clientId}/renew`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        }).then(response => {
            if (response.ok) {
                alert('Cliente renovado com sucesso!');
                location.reload();
            } else {
                alert('Erro ao renovar cliente');
            }
        });
    }
}

function killConnections(clientId) {
    if (confirm('Deseja destruir todas as conexões ativas deste cliente?')) {
        fetch(`/clients/${clientId}/kill-connections`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        }).then(response => {
            if (response.ok) {
                alert('Conexões destruídas com sucesso!');
            } else {
                alert('Erro ao destruir conexões');
            }
        });
    }
}

function deleteClient(clientId) {
    if (confirm('Tem certeza que deseja excluir este cliente? Esta ação não pode ser desfeita!')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/clients/${clientId}`;
        
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = document.querySelector('meta[name="csrf-token"]').content;
        
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        
        form.appendChild(csrfInput);
        form.appendChild(methodInput);
        document.body.appendChild(form);
        form.submit();
    }
}

// Modal M3U
function openM3uModal(clientId, username) {
    document.getElementById('m3uClientName').value = username;
    
    fetch(`/clients/${clientId}/m3u-data`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('m3uUrl').value = data.m3u_url;
            document.getElementById('hlsUrl').value = data.hls_url;
            document.getElementById('m3uModal').classList.remove('hidden');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erro ao carregar URLs M3U');
        });
}

function closeM3uModal() {
    document.getElementById('m3uModal').classList.add('hidden');
}

function copyToClipboard(elementId) {
    const input = document.getElementById(elementId);
    input.select();
    document.execCommand('copy');
    alert('Copiado para a área de transferência!');
}

// Modal Edit
function openEditModal(clientId) {
    fetch(`/clients/${clientId}/edit-data`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('editClientId').value = clientId;
            document.getElementById('editUsername').value = data.username;
            document.getElementById('editPassword').value = data.password;
            document.getElementById('editPhone').value = data.phone || '';
            document.getElementById('editEmail').value = data.email || '';
            document.getElementById('editNotes').value = data.notes || '';
            document.getElementById('editPackageName').value = data.package_name;
            document.getElementById('editMaxConnections').value = data.max_connections + ' Conexão' + (data.max_connections > 1 ? 'ões' : '');
            
            // Carregar bouquets
            const bouquetsContainer = document.getElementById('editBouquets');
            bouquetsContainer.innerHTML = '';
            data.all_bouquets.forEach(bouquet => {
                const isChecked = data.selected_bouquets.includes(bouquet.id);
                bouquetsContainer.innerHTML += `
                    <label class="flex items-center gap-3 p-3 bg-dark-300 rounded-lg hover:bg-dark-100 cursor-pointer transition-colors">
                        <input type="checkbox" name="bouquet_ids[]" value="${bouquet.id}" ${isChecked ? 'checked' : ''} class="w-5 h-5 text-orange-500 bg-dark-200 border-dark-100 rounded focus:ring-orange-500">
                        <span class="text-white text-sm">${bouquet.bouquet_name}</span>
                    </label>
                `;
            });
            
            document.getElementById('editModal').classList.remove('hidden');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erro ao carregar dados do cliente');
        });
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}

function submitEdit(event) {
    event.preventDefault();
    
    const clientId = document.getElementById('editClientId').value;
    
    // Coletar dados do formulário
    const data = {
        username: document.getElementById('editUsername').value,
        password: document.getElementById('editPassword').value,
        phone: document.getElementById('editPhone').value,
        email: document.getElementById('editEmail').value,
        notes: document.getElementById('editNotes').value,
        _method: 'PUT'
    };
    
    // Coletar bouquet_ids dos checkboxes marcados
    const bouquetCheckboxes = document.querySelectorAll('#editBouquets input[type="checkbox"]:checked');
    data.bouquet_ids = Array.from(bouquetCheckboxes).map(cb => parseInt(cb.value));
    
    console.log('Editar - Dados enviados:', data);
    
    // Validar se pelo menos um bouquet está selecionado
    if (data.bouquet_ids.length === 0) {
        alert('Selecione pelo menos um buquê');
        return;
    }
    
    const btnText = document.getElementById('editBtnText');
    const originalText = btnText.innerText;
    btnText.innerText = 'Salvando...';
    
    fetch(`/clients/${clientId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => {
                console.error('Resposta do servidor:', text);
                throw new Error('Erro ao atualizar cliente');
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            alert('Cliente atualizado com sucesso!');
            location.reload();
        } else {
            alert(data.message || 'Erro ao atualizar cliente');
            btnText.innerText = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Erro de conexão: ' + error.message);
        btnText.innerText = originalText;
    });
}

// Modal Create Trial
function openTrialModal() {
    document.getElementById('trialModal').classList.remove('hidden');
}

function closeTrialModal() {
    document.getElementById('trialModal').classList.add('hidden');
}

function generateTrialUsername() {
    const prefix = 'test';
    const random = Math.floor(Math.random() * 10000).toString().padStart(4, '0');
    document.getElementById('trialUsername').value = prefix + random;
}

function generateTrialPassword() {
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
    let password = '';
    for (let i = 0; i < 8; i++) {
        password += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    document.getElementById('trialPassword').value = password;
}

function updateTrialPackage(select) {
    const selectedOption = select.options[select.selectedIndex];
    
    if (select.value) {
        const duration = selectedOption.getAttribute('data-duration');
        const durationIn = selectedOption.getAttribute('data-duration-in');
        const connections = selectedOption.getAttribute('data-connections');
        const bouquets = selectedOption.getAttribute('data-bouquets');
        
        // Preencher campos hidden
        document.getElementById('trialDurationValue').value = duration;
        document.getElementById('trialDurationUnit').value = durationIn;
        document.getElementById('trialMaxConnections').value = connections;
        
        // Auto-selecionar bouquets
        if (bouquets) {
            try {
                const bouquetIds = JSON.parse(bouquets);
                document.querySelectorAll('#trialBouquets input[type="checkbox"]').forEach(checkbox => {
                    checkbox.checked = bouquetIds.includes(parseInt(checkbox.value));
                });
            } catch (e) {
                console.error('Erro ao parsear bouquets:', e);
            }
        }
    } else {
        // Limpar campos se nenhum pacote selecionado
        document.getElementById('trialDurationValue').value = '';
        document.getElementById('trialDurationUnit').value = '';
        document.getElementById('trialMaxConnections').value = '';
        document.querySelectorAll('#trialBouquets input[type="checkbox"]').forEach(checkbox => {
            checkbox.checked = false;
        });
    }
}

// Modal Sucesso Renovação
function closeRenewSuccessModal() {
    document.getElementById('renewSuccessModal').classList.add('hidden');
    location.reload();
}

function copyField(fieldId) {
    const input = document.getElementById(fieldId);
    input.select();
    document.execCommand('copy');
    
    // Feedback visual
    const button = event.target.closest('button');
    const originalHTML = button.innerHTML;
    button.innerHTML = '<i class="bi bi-check-lg"></i> Copiado!';
    setTimeout(() => {
        button.innerHTML = originalHTML;
    }, 2000);
}

function submitTrial(event) {
    event.preventDefault();
    
    const formData = new FormData(document.getElementById('trialForm'));
    const data = Object.fromEntries(formData);
    data.bouquet_ids = formData.getAll('bouquet_ids[]');
    
    console.log('Dados enviados:', data);
    
    const btnText = document.getElementById('trialBtnText');
    const originalText = btnText.innerText;
    btnText.innerText = 'Gerando...';
    
    fetch('/clients/trial', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => {
                console.error('Resposta do servidor:', text);
                throw new Error('Erro ao criar teste');
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            alert('Teste criado com sucesso!');
            location.reload();
        } else {
            alert(data.message || 'Erro ao criar teste');
            btnText.innerText = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Erro de conexão: ' + error.message);
        btnText.innerText = originalText;
    });
}
</script>
@endpush
@endsection
