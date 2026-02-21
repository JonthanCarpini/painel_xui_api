@extends('layouts.app')

@section('title', 'Meus Clientes')

@section('content')
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
        <i class="bi bi-people text-orange-500"></i>
        Lista de Clientes
        <span id="totalClientsCounter" class="ml-2 text-lg font-bold bg-orange-100 dark:bg-orange-500/10 text-orange-600 dark:text-orange-500 px-4 py-1.5 rounded-full border border-orange-200 dark:border-orange-500/20">
            Total: {{ $totalGlobal ?? count($clients) }}
        </span>
    </h1>
    <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
        {{-- Botão Sync visível para Admin e Revendas --}}
        <button onclick="syncClients()" id="btnSync" class="w-full sm:w-auto px-4 py-2 bg-blue-100 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-500/30 rounded-lg hover:bg-blue-200 dark:hover:bg-blue-500/30 transition-all flex items-center justify-center gap-2 font-medium" title="Sincronizar clientes do XUI para base local">
            <i class="bi bi-arrow-repeat"></i>
            <span class="hidden sm:inline">Sync</span>
        </button>
        
        <button onclick="openTrialModal()" class="w-full sm:w-auto px-4 py-2 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-lg hover:shadow-lg transition-all flex items-center justify-center gap-2 font-medium">
            <i class="bi bi-clock-history"></i>
            Criar Teste Gr&aacute;tis
        </button>
        <a href="{{ route('clients.create') }}" class="w-full sm:w-auto px-4 py-2 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg hover:shadow-lg transition-all flex items-center justify-center gap-2 font-medium">
            <i class="bi bi-plus-circle"></i>
            Novo Cliente
        </a>
    </div>
</div>

<!-- Filtros -->
<div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 p-4 md:p-6 mb-6 shadow-sm dark:shadow-none">
    <h3 class="text-gray-900 dark:text-white font-semibold mb-4 flex items-center gap-2">
        <i class="bi bi-funnel"></i> Filtros
    </h3>
    
    <!-- Filtros R&aacute;pidos -->
    <div class="mb-6">
        <p class="text-gray-500 dark:text-gray-400 text-sm mb-3">Filtros R&aacute;pidos (Apenas Clientes Oficiais)</p>
        <div class="flex flex-wrap gap-2">
            <button onclick="applyQuickFilter('today')" class="flex-1 sm:flex-none justify-center px-4 py-2 bg-gray-100 dark:bg-dark-200 text-gray-600 dark:text-gray-300 rounded-lg hover:bg-orange-500 hover:text-white transition-colors text-sm flex items-center gap-2 group whitespace-nowrap">
                <i class="bi bi-calendar-check"></i> Vence Hoje
                <span class="bg-orange-100 dark:bg-orange-500/20 text-orange-600 dark:text-orange-500 group-hover:bg-white/20 group-hover:text-white px-2 py-0.5 rounded text-xs font-bold border border-orange-200 dark:border-orange-500/30">{{ $quickStats['today'] ?? 0 }}</span>
            </button>
            <button onclick="applyQuickFilter('7days')" class="flex-1 sm:flex-none justify-center px-4 py-2 bg-gray-100 dark:bg-dark-200 text-gray-600 dark:text-gray-300 rounded-lg hover:bg-orange-500 hover:text-white transition-colors text-sm flex items-center gap-2 group whitespace-nowrap">
                <i class="bi bi-calendar-week"></i> 7 Dias
                <span class="bg-orange-100 dark:bg-orange-500/20 text-orange-600 dark:text-orange-500 group-hover:bg-white/20 group-hover:text-white px-2 py-0.5 rounded text-xs font-bold border border-orange-200 dark:border-orange-500/30">{{ $quickStats['7days'] ?? 0 }}</span>
            </button>
            <button onclick="applyQuickFilter('30days')" class="flex-1 sm:flex-none justify-center px-4 py-2 bg-gray-100 dark:bg-dark-200 text-gray-600 dark:text-gray-300 rounded-lg hover:bg-orange-500 hover:text-white transition-colors text-sm flex items-center gap-2 group whitespace-nowrap">
                <i class="bi bi-calendar-month"></i> 30 Dias
                <span class="bg-orange-100 dark:bg-orange-500/20 text-orange-600 dark:text-orange-500 group-hover:bg-white/20 group-hover:text-white px-2 py-0.5 rounded text-xs font-bold border border-orange-200 dark:border-orange-500/30">{{ $quickStats['30days'] ?? 0 }}</span>
            </button>
        </div>
    </div>

    <!-- Busca e Filtros -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
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
        @if(Auth::user()->isAdmin() || (isset($resellers) && count($resellers) > 0))
        <div>
            <label class="block text-sm text-gray-500 dark:text-gray-400 mb-2">Revenda</label>
            <select id="resellerFilter" class="w-full px-4 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white focus:border-orange-500 focus:outline-none transition-colors">
                <option value="">Todas</option>
                <option value="mine">Meus Clientes Diretos</option>
                @foreach($resellers as $reseller)
                    <option value="{{ $reseller['id'] }}">{{ $reseller['username'] }}</option>
                @endforeach
            </select>
        </div>
        @endif
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
                                    data-bouquets="{{ json_encode($package->bouquets ?? []) }}"
                                    data-credits="{{ $package->official_credits ?? 0 }}">
                                {{ $package->package_name }} 
                                - {{ $package->official_duration ?? 30 }} {{ $package->official_duration_in ?? 'dias' }}
                                ({{ $package->official_credits }} cr&eacute;dito{{ $package->official_credits > 1 ? 's' : '' }})
                            </option>
                        @endif
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
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

            <div class="flex flex-col-reverse sm:flex-row gap-3">
                <button type="button" onclick="closeRenewModal()" class="w-full sm:w-auto px-4 py-2 bg-gray-100 dark:bg-dark-200 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-dark-100 transition-colors font-medium">Cancelar</button>
                
                @php
                    $trustPackageConfigured = \App\Models\AppSetting::get('trust_renew_package_id');
                @endphp
                @if($trustPackageConfigured)
                <button type="button" onclick="submitRenewTrust()" 
                        class="w-full sm:w-auto px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors flex items-center justify-center gap-2 font-medium" 
                        title="Renovar usando pacote de confiança configurado">
                    <i class="bi bi-shield-check"></i>
                    <span class="hidden sm:inline">Em Confiança</span>
                </button>
                @endif

                <button type="submit" class="flex-1 px-4 py-2 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-lg hover:shadow-lg transition-all flex items-center justify-center gap-2 font-medium">
                    <span id="renewBtnText">Confirmar Renova&ccedil;&atilde;o</span>
                    <i class="bi bi-arrow-clockwise"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Scripts Adicionais -->
<script>
function syncClients() {
    const btn = document.getElementById('btnSync');
    const icon = btn.querySelector('i');
    
    if(!confirm('Isso irá sincronizar os telefones e criar registros locais para todos os clientes do XUI que ainda não possuem. Pode levar alguns instantes. Deseja continuar?')) return;
    
    icon.classList.add('animate-spin');
    btn.disabled = true;
    
    fetch('{{ route("clients.sync") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Erro: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Ocorreu um erro ao sincronizar.');
    })
    .finally(() => {
        icon.classList.remove('animate-spin');
        btn.disabled = false;
    });
}

function submitRenewTrust() {
    const id = document.getElementById('renewClientId').value;
    const name = document.getElementById('renewClientName').value;
    
    if(!id) return;
    
    if(!confirm(`Confirma a renovação em confiança para o cliente "${name}"? \nIsso usará o pacote padrão de confiança configurado.`)) return;
    
    const btn = document.querySelector('#renewModal button[onclick="submitRenewTrust()"]');
    const originalContent = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-arrow-repeat animate-spin"></i> Processando...';
    
    fetch(`/clients/${id}/renew-trust`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            closeRenewModal();
            
            // Preencher modal de sucesso
            document.getElementById('renewSuccessUsername').value = data.client.username;
            document.getElementById('renewSuccessPassword').value = '********'; // Senha não retornada na renovação por segurança, ou manter a atual
            document.getElementById('renewSuccessExpDate').value = data.client.exp_date;
            
            // Gerar texto para WhatsApp
            const message = `✅ RENOVAÇÃO EM CONFIANÇA REALIZADA!\n\n👤 Usuário: ${data.client.username}\n📅 Nova Validade: ${data.client.exp_date}\n\nObrigado pela preferência! 👍`;
            document.getElementById('renewSuccessWhatsapp').value = message;
            
            document.getElementById('renewSuccessModal').classList.remove('hidden');
            
            // Atualizar lista se necessário
            if(typeof loadClients === 'function') loadClients();
        } else {
            alert('Erro ao renovar: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Ocorreu um erro ao processar a renovação.');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalContent;
    });
}
</script>

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
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
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
                    <button onclick="copyField('renewSuccessWhatsapp')" class="absolute top-2 right-2 px-3 py-1.5 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors flex items-center gap-2">
                        <i class="bi bi-clipboard"></i>
                        Copiar
                    </button>
                </div>
            </div>
            <input type="hidden" id="renewSuccessPhone">
            <div id="renewSuccessFeedback" class="hidden mb-3"></div>
            <div class="flex flex-col sm:flex-row gap-3">
                <button onclick="closeRenewSuccessModal()" class="w-full sm:w-1/3 px-4 py-2 bg-gray-100 dark:bg-dark-200 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-dark-100 transition-colors font-medium">Fechar</button>
                <button id="renewSuccessSendBtn" onclick="sendRenewWhatsapp()" class="w-full sm:w-1/3 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium flex items-center justify-center gap-2">
                    <i class="bi bi-whatsapp"></i> Enviar via WhatsApp
                </button>
                <a href="{{ route('clients.index') }}" class="w-full sm:w-1/3 px-4 py-2 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg hover:shadow-lg transition-all text-center font-medium">
                    Ver Clientes
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
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">Mensagem do Cliente</label>
                <div class="relative">
                    <textarea id="m3uMessageText" readonly rows="8" class="w-full px-4 py-3 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white font-mono text-xs resize-none"></textarea>
                    <button onclick="copyToClipboard('m3uMessageText')" class="absolute top-2 right-2 px-2 py-1 bg-orange-500 text-white rounded hover:bg-orange-600 transition-colors text-xs">
                        <i class="bi bi-clipboard"></i>
                    </button>
                </div>
                <div class="mt-2 flex justify-end">
                    <button onclick="copyToClipboard('m3uMessageText')" class="w-full sm:w-auto px-3 py-1.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm flex items-center justify-center gap-2">
                        <i class="bi bi-clipboard"></i>
                        Copiar Mensagem
                    </button>
                </div>
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
                <button onclick="closeM3uModal()" class="w-full px-4 py-2 bg-gray-100 dark:bg-dark-200 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-dark-100 transition-colors font-medium">Fechar</button>
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
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">Usu&aacute;rio *</label>
                    <input type="text" id="editUsername" name="username" required class="w-full px-4 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white focus:border-orange-500 focus:outline-none transition-colors">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">Senha *</label>
                    <input type="text" id="editPassword" name="password" required class="w-full px-4 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white focus:border-orange-500 focus:outline-none transition-colors">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">Pacote</label>
                    <input type="text" id="editPackageName" readonly class="w-full px-4 py-2 bg-gray-100 dark:bg-dark-100 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-500 dark:text-gray-400 cursor-not-allowed">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">Conex&otilde;es</label>
                    <input type="text" id="editMaxConnections" readonly class="w-full px-4 py-2 bg-gray-100 dark:bg-dark-100 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-500 dark:text-gray-400 cursor-not-allowed">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
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
                <div id="editBouquets" class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-64 overflow-y-auto p-4 bg-gray-50 dark:bg-dark-200 rounded-lg border border-gray-200 dark:border-0 custom-scrollbar">
                    <!-- Bouquets ser&atilde;o carregados dinamicamente -->
                </div>
            </div>

            <div class="flex flex-col-reverse sm:flex-row gap-3">
                <button type="button" onclick="closeEditModal()" class="w-full sm:w-1/2 px-4 py-2 bg-gray-100 dark:bg-dark-200 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-dark-100 transition-colors font-medium">Cancelar</button>
                <button type="submit" class="w-full sm:w-1/2 px-4 py-2 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg hover:shadow-lg transition-all font-medium">
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

<!-- Modal Mensagem do Cliente (Criado com Sucesso) -->
@if(session('client_message'))
<div id="clientMessageModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white dark:bg-dark-300 rounded-xl max-w-2xl w-full border border-gray-200 dark:border-dark-200 shadow-2xl">
        <div class="p-6 border-b border-gray-200 dark:border-dark-200 flex justify-between items-center bg-gradient-to-r from-green-600 to-green-700">
            <h3 class="text-xl font-bold text-white flex items-center gap-2">
                <i class="bi bi-check-circle-fill"></i>
                Cliente Criado com Sucesso!
            </h3>
            <button onclick="document.getElementById('clientMessageModal').remove()" class="text-white hover:text-gray-200">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="p-6">
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">Dados de Acesso</label>
                <div class="relative">
                    <textarea id="clientMessageText" readonly rows="12" class="w-full px-4 py-3 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white font-mono text-sm resize-none">{{ session('client_message') }}</textarea>
                    <button onclick="copyToClipboard('clientMessageText')" class="absolute top-2 right-2 px-3 py-1.5 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors flex items-center gap-2 text-xs">
                        <i class="bi bi-clipboard"></i>
                        Copiar
                    </button>
                </div>
            </div>
            <div id="clientMsgFeedback" class="hidden mb-3"></div>
            <div class="flex flex-col sm:flex-row gap-3">
                <button onclick="document.getElementById('clientMessageModal').remove()" class="w-full sm:w-1/2 px-4 py-2 bg-gray-100 dark:bg-dark-200 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-dark-100 transition-colors font-medium">Fechar</button>
                @if(session('client_phone'))
                <button id="clientMsgSendBtn" onclick="sendViaEvolution('{{ session('client_phone') }}', document.getElementById('clientMessageText').value, 'clientMsgSendBtn', 'clientMsgFeedback')" class="w-full sm:w-1/2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium flex items-center justify-center gap-2">
                    <i class="bi bi-whatsapp"></i>
                    Enviar via WhatsApp
                </button>
                @else
                <button onclick="copyToClipboard('clientMessageText')" class="w-full sm:w-1/2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium flex items-center justify-center gap-2">
                    <i class="bi bi-clipboard"></i>
                    Copiar Mensagem
                </button>
                @endif
            </div>
        </div>
    </div>
</div>
@endif

<!-- Modal WhatsApp - Envio via Evolution API -->
<div id="whatsappModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white dark:bg-dark-300 rounded-xl max-w-lg w-full border border-gray-200 dark:border-dark-200 shadow-2xl">
        <div class="p-5 border-b border-gray-200 dark:border-dark-200 flex justify-between items-center bg-gradient-to-r from-green-600 to-green-700">
            <h3 class="text-lg font-bold text-white flex items-center gap-2">
                <i class="bi bi-whatsapp"></i>
                Enviar Mensagem WhatsApp
            </h3>
            <button onclick="closeWhatsappModal()" class="text-white hover:text-gray-200">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="p-5">
            <input type="hidden" id="waPhone">
            <input type="hidden" id="waClientId">

            <div class="mb-3">
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Destinat&aacute;rio</label>
                <div class="flex items-center gap-2 px-3 py-2 bg-gray-50 dark:bg-dark-200 rounded-lg border border-gray-200 dark:border-dark-100">
                    <i class="bi bi-person text-gray-400"></i>
                    <span id="waClientName" class="text-sm font-medium text-gray-900 dark:text-white"></span>
                    <span class="text-gray-400">-</span>
                    <span id="waPhoneDisplay" class="text-sm text-gray-500 dark:text-gray-400"></span>
                </div>
            </div>

            <div class="mb-3">
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">A&ccedil;&otilde;es R&aacute;pidas</label>
                <div class="flex flex-wrap gap-2">
                    <button onclick="waQuickAction('access')" class="px-3 py-1.5 bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-500/30 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-500/20 transition-colors text-xs font-medium flex items-center gap-1">
                        <i class="bi bi-key"></i> Dados de Acesso
                    </button>
                    <button onclick="waQuickAction('expiry')" class="px-3 py-1.5 bg-yellow-50 dark:bg-yellow-500/10 text-yellow-600 dark:text-yellow-400 border border-yellow-200 dark:border-yellow-500/30 rounded-lg hover:bg-yellow-100 dark:hover:bg-yellow-500/20 transition-colors text-xs font-medium flex items-center gap-1">
                        <i class="bi bi-calendar-event"></i> Data de Vencimento
                    </button>
                    <button onclick="waQuickAction('renewal')" class="px-3 py-1.5 bg-green-50 dark:bg-green-500/10 text-green-600 dark:text-green-400 border border-green-200 dark:border-green-500/30 rounded-lg hover:bg-green-100 dark:hover:bg-green-500/20 transition-colors text-xs font-medium flex items-center gap-1">
                        <i class="bi bi-arrow-clockwise"></i> Lembrete Renova&ccedil;&atilde;o
                    </button>
                    <button onclick="document.getElementById('waMessage').value = ''" class="px-3 py-1.5 bg-gray-50 dark:bg-gray-500/10 text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-gray-500/30 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-500/20 transition-colors text-xs font-medium flex items-center gap-1">
                        <i class="bi bi-eraser"></i> Limpar
                    </button>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Mensagem</label>
                <textarea id="waMessage" rows="8" class="w-full px-3 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500 transition-colors resize-none" placeholder="Digite sua mensagem..."></textarea>
            </div>

            <div id="waSendFeedback" class="hidden mb-3"></div>

            <div class="flex gap-3">
                <button onclick="closeWhatsappModal()" class="flex-1 px-4 py-2.5 bg-gray-100 dark:bg-dark-200 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-dark-100 transition-colors font-medium text-sm">Cancelar</button>
                <button onclick="sendWhatsappMessage()" id="waSendBtn" class="flex-1 px-4 py-2.5 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg hover:shadow-lg transition-all font-medium text-sm flex items-center justify-center gap-2">
                    <i class="bi bi-send"></i> Enviar
                </button>
            </div>
        </div>
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

// Helper para copiar texto com fallback
function copyToClipboard(elementId) {
    const el = document.getElementById(elementId);
    if (!el) return;
    
    // Selecionar texto
    el.select();
    el.setSelectionRange(0, 99999);
    
    // Tentar API moderna primeiro
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(el.value).then(() => {
            showCopyFeedback(el);
        }).catch(err => {
            console.error('Erro ao copiar via API, tentando fallback', err);
            fallbackCopy(el);
        });
    } else {
        // Fallback para contextos não seguros ou navegadores antigos
        fallbackCopy(el);
    }
}

function fallbackCopy(el) {
    try {
        document.execCommand('copy');
        showCopyFeedback(el);
    } catch (err) {
        console.error('Falha ao copiar texto', err);
        alert('Não foi possível copiar automaticamente. Por favor, copie manualmente.');
    }
}

function showCopyFeedback(el) {
    const originalBg = el.style.backgroundColor;
    el.style.backgroundColor = '#dcfce7'; // green-100
    setTimeout(() => {
        el.style.backgroundColor = originalBg;
    }, 200);
}

function copyField(elementId) {
    copyToClipboard(elementId);
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

document.getElementById('resellerFilter')?.addEventListener('change', function() {
    loadClients();
});

// Função principal de carregamento
function loadClients(page = 1) {
    currentPage = page;
    
    const search = document.getElementById('searchInput')?.value || '';
    const phone = document.getElementById('phoneInput')?.value || '';
    const status = document.getElementById('statusFilter')?.value || '';
    const type = document.getElementById('typeFilter')?.value || '';
    const resellerId = document.getElementById('resellerFilter')?.value || '';
    
    const params = new URLSearchParams({
        search: search,
        phone: phone,
        status: status,
        type: type,
        reseller_id: resellerId,
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

// Funções para Modais e Ações

function openRenewModal(clientId) {
    // Encontrar o nome do cliente na linha da tabela
    const row = document.querySelector(`tr[onclick*="${clientId}"]`) || 
                document.querySelector(`button[onclick="openRenewModal(${clientId})"]`)?.closest('tr');
    
    if (row) {
        const name = row.dataset.username || row.querySelector('td:first-child span')?.innerText.trim() || 'Cliente';
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
    const form = document.getElementById('renewForm');
    const formData = new FormData(form);
    const btn = document.getElementById('renewBtnText').parentElement;
    
    btn.disabled = true;
    btn.classList.add('opacity-50', 'cursor-not-allowed');
    
    fetch(`/clients/${clientId}/renew`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeRenewModal();
            loadClients(currentPage);
            
            // Mostrar modal de sucesso
            document.getElementById('renewSuccessUsername').value = data.client.username;
            document.getElementById('renewSuccessPassword').value = data.client.password;
            document.getElementById('renewSuccessExpDate').value = data.client.exp_date;
            
            // Gerar mensagem Whatsapp simples
            const zapMsg = `✅ Renovação Concluída!\n\n👤 Usuário: ${data.client.username}\n🔑 Senha: ${data.client.password}\n📅 Validade: ${data.client.exp_date}\n📺 Conexões: ${data.client.max_connections}`;
            document.getElementById('renewSuccessWhatsapp').value = zapMsg;
            document.getElementById('renewSuccessPhone').value = data.client.phone || '';
            document.getElementById('renewSuccessFeedback').classList.add('hidden');
            document.getElementById('renewSuccessSendBtn').disabled = false;
            document.getElementById('renewSuccessSendBtn').innerHTML = '<i class="bi bi-whatsapp"></i> Enviar via WhatsApp';
            
            document.getElementById('renewSuccessModal').classList.remove('hidden');
        } else {
            alert('Erro: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao renovar cliente.');
    })
    .finally(() => {
        btn.disabled = false;
        btn.classList.remove('opacity-50', 'cursor-not-allowed');
    });
}

function closeRenewSuccessModal() {
    document.getElementById('renewSuccessModal').classList.add('hidden');
}

function openM3uModal(clientId, username) {
    // Definir nome se passado, ou tentar pegar do DOM
    if (username) {
        document.getElementById('m3uClientName').value = username;
    } else {
        const row = document.querySelector(`tr[onclick*="${clientId}"]`) || 
                    document.querySelector(`button[onclick*="openM3uModal(${clientId}"]`)?.closest('tr');
        if (row) {
            document.getElementById('m3uClientName').value = row.dataset.username || 'Cliente';
        }
    }
    
    // Buscar dados M3U e Mensagem
    Promise.all([
        fetch(`/clients/${clientId}/m3u-data`).then(r => r.json()),
        fetch(`/clients/${clientId}/message`).then(r => r.json())
    ])
    .then(([m3uData, msgData]) => {
        document.getElementById('m3uUrl').value = m3uData.m3u_url;
        document.getElementById('hlsUrl').value = m3uData.hls_url;
        document.getElementById('m3uMessageText').value = msgData.message;
        document.getElementById('m3uModal').classList.remove('hidden');
    })
    .catch(err => {
        console.error(err);
        alert('Erro ao carregar dados');
    });
}

function closeM3uModal() {
    document.getElementById('m3uModal').classList.add('hidden');
}

function openEditModal(clientId) {
    document.getElementById('editClientId').value = clientId;
    
    fetch(`/clients/${clientId}/edit-data`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('editUsername').value = data.username;
            document.getElementById('editPassword').value = data.password;
            document.getElementById('editPhone').value = data.phone;
            document.getElementById('editEmail').value = data.email;
            document.getElementById('editNotes').value = data.notes;
            document.getElementById('editPackageName').value = data.package_name;
            document.getElementById('editMaxConnections').value = data.max_connections;
            
            // Renderizar bouquets
            const bouquetsDiv = document.getElementById('editBouquets');
            bouquetsDiv.innerHTML = '';
            
            data.all_bouquets.forEach(b => {
                const isSelected = data.selected_bouquets.includes(b.id.toString()) || data.selected_bouquets.includes(b.id);
                const html = `
                    <label class="flex items-center gap-2 p-2 bg-white dark:bg-dark-300 border border-gray-200 dark:border-dark-100 rounded-lg hover:bg-gray-50 dark:hover:bg-dark-100 cursor-pointer">
                        <input type="checkbox" name="bouquet_ids[]" value="${b.id}" ${isSelected ? 'checked' : ''} class="w-4 h-4 text-orange-500 rounded focus:ring-orange-500">
                        <span class="text-sm text-gray-700 dark:text-white truncate" title="${b.bouquet_name}">${b.bouquet_name}</span>
                    </label>
                `;
                bouquetsDiv.insertAdjacentHTML('beforeend', html);
            });
            
            document.getElementById('editModal').classList.remove('hidden');
        })
        .catch(err => alert('Erro ao carregar dados do cliente'));
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}

function submitEdit(event) {
    event.preventDefault();
    const clientId = document.getElementById('editClientId').value;
    const form = document.getElementById('editForm');
    const formData = new FormData(form);
    
    // Adicionar método PUT
    formData.append('_method', 'PUT');
    
    fetch(`/clients/${clientId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeEditModal();
            loadClients(currentPage);
            // Poderia mostrar toast de sucesso aqui
        } else {
            alert('Erro: ' + data.message);
        }
    })
    .catch(err => alert('Erro ao atualizar cliente'));
}

function deleteClient(clientId) {
    if (!confirm('Tem certeza que deseja excluir este cliente?')) return;
    
    fetch(`/clients/${clientId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(() => {
        loadClients(currentPage);
    })
    .catch(err => alert('Erro ao excluir cliente'));
}

// Trial Modal Functions
function openTrialModal() {
    // Limpar form
    document.getElementById('trialForm').reset();
    document.getElementById('trialPackageId').selectedIndex = 0;
    
    // Resetar checkboxes (desmarcar todos)
    document.querySelectorAll('#trialBouquets input[type="checkbox"]').forEach(cb => cb.checked = false);
    
    // Gerar user/pass aleatórios
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

function updateTrialPackage(select) {
    const selectedOption = select.options[select.selectedIndex];
    if (select.value) {
        document.getElementById('trialDurationValue').value = selectedOption.getAttribute('data-duration');
        document.getElementById('trialDurationUnit').value = selectedOption.getAttribute('data-duration-in');
        document.getElementById('trialMaxConnections').value = selectedOption.getAttribute('data-connections');
        
        // Atualizar bouquets (checkboxes) baseado no pacote
        const bouquetsAttr = selectedOption.getAttribute('data-bouquets');
        if (bouquetsAttr) {
            try {
                let packageBouquets = JSON.parse(bouquetsAttr);
                
                // Se ainda for string após o primeiro parse, parse novamente (caso de json encoded string no atributo)
                if (typeof packageBouquets === 'string') {
                    try {
                        packageBouquets = JSON.parse(packageBouquets);
                    } catch (e) {
                        console.error('Erro no segundo parse de bouquets:', e);
                    }
                }
                
                // Garantir que é array e normalizar para strings para comparação
                if (Array.isArray(packageBouquets)) {
                    const bouquetsToSelect = packageBouquets.map(String);
                    const checkboxes = document.querySelectorAll('#trialBouquets input[type="checkbox"]');
                    
                    checkboxes.forEach(cb => {
                        if (bouquetsToSelect.includes(cb.value.toString())) {
                            cb.checked = true;
                        } else {
                            cb.checked = false;
                        }
                    });
                }
            } catch (e) {
                console.error('Erro ao processar bouquets:', e);
            }
        }
    } else {
        document.getElementById('trialDurationValue').value = '';
        document.getElementById('trialDurationUnit').value = '';
        document.getElementById('trialMaxConnections').value = '';
        
        // Desmarcar todos
        document.querySelectorAll('#trialBouquets input[type="checkbox"]').forEach(cb => cb.checked = false);
    }
}

function submitTrial(event) {
    event.preventDefault();
    const form = document.getElementById('trialForm');
    
    // Validação manual antes do envio
    const pkgSelect = document.getElementById('trialPackageId');
    if (!pkgSelect.value) {
        alert('Selecione um pacote de teste.');
        return;
    }
    
    // Verificar se há buquês selecionados
    const checkedBouquets = document.querySelectorAll('#trialBouquets input[type="checkbox"]:checked');
    if (checkedBouquets.length === 0) {
        alert('Selecione pelo menos um buquê para o teste.');
        return;
    }

    // Garantir que os campos ocultos estejam preenchidos
    if (!document.getElementById('trialDurationValue').value) {
        updateTrialPackage(pkgSelect);
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
        if (!response.ok) {
            // Tratar erros de validação (422)
            if (response.status === 422) {
                let errorMsg = 'Dados inválidos:\n';
                if (data.errors) {
                    for (const [field, messages] of Object.entries(data.errors)) {
                        errorMsg += `- ${messages[0]}\n`;
                    }
                } else {
                    errorMsg += data.message || 'Verifique os campos.';
                }
                throw new Error(errorMsg);
            }
            throw new Error(data.message || 'Erro ao criar teste');
        }
        return data;
    })
    .then(data => {
        if (data.success) {
            closeTrialModal();
            
            // Se vier mensagem personalizada
            if (data.client_message) {
                const modalHtml = `
                <div id="ajaxClientMessageModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
                    <div class="bg-white dark:bg-dark-300 rounded-xl max-w-2xl w-full border border-gray-200 dark:border-dark-200 shadow-2xl">
                        <div class="p-6 border-b border-gray-200 dark:border-dark-200 flex justify-between items-center bg-gradient-to-r from-green-600 to-green-700">
                            <h3 class="text-xl font-bold text-white flex items-center gap-2">
                                <i class="bi bi-check-circle-fill"></i>
                                Teste Criado com Sucesso!
                            </h3>
                            <button onclick="document.getElementById('ajaxClientMessageModal').remove()" class="text-white hover:text-gray-200">
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
                                <button onclick="document.getElementById('ajaxClientMessageModal').remove()" class="flex-1 px-4 py-2 bg-gray-100 dark:bg-dark-200 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-dark-100 transition-colors font-medium">Fechar</button>
                                <button onclick="copyToClipboard('ajaxClientMessageText')" class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium flex items-center justify-center gap-2">
                                    <i class="bi bi-clipboard"></i>
                                    Copiar Mensagem
                                </button>
                            </div>
                        </div>
                    </div>
                </div>`;
                document.body.insertAdjacentHTML('beforeend', modalHtml);
            } else {
                alert('Teste criado com sucesso!');
            }
            
            loadClients(currentPage);
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

// ===== WhatsApp Modal Functions =====
let waClientData = {};

function openWhatsappModal(clientId, username, phone, password, expDate, maxConnections) {
    waClientData = { clientId, username, phone, password, expDate, maxConnections };
    document.getElementById('waClientId').value = clientId;
    document.getElementById('waPhone').value = phone;
    document.getElementById('waClientName').textContent = username;
    document.getElementById('waPhoneDisplay').textContent = phone;
    document.getElementById('waMessage').value = '';
    document.getElementById('waSendFeedback').classList.add('hidden');
    document.getElementById('waSendBtn').disabled = false;
    document.getElementById('waSendBtn').innerHTML = '<i class="bi bi-send"></i> Enviar';
    document.getElementById('whatsappModal').classList.remove('hidden');
}

function closeWhatsappModal() {
    document.getElementById('whatsappModal').classList.add('hidden');
}

function waQuickAction(type) {
    const d = waClientData;
    let msg = '';
    switch(type) {
        case 'access':
            msg = `*Seus Dados de Acesso:*\n\n` +
                  `👤 Usuário: ${d.username}\n` +
                  `🔑 Senha: ${d.password}\n` +
                  `📺 Conexões: ${d.maxConnections}\n` +
                  `📅 Validade: ${d.expDate}\n\n` +
                  `Qualquer dúvida estamos à disposição!`;
            break;
        case 'expiry':
            msg = `⚠️ *Aviso de Vencimento*\n\n` +
                  `Olá! Informamos que seu plano vence em *${d.expDate}*.\n\n` +
                  `Entre em contato para renovar e não ficar sem acesso! 😊`;
            break;
        case 'renewal':
            msg = `🔄 *Lembrete de Renovação*\n\n` +
                  `Olá! Seu plano está próximo do vencimento (${d.expDate}).\n\n` +
                  `Renove agora e continue aproveitando! 📺\n` +
                  `Entre em contato para mais informações.`;
            break;
    }
    document.getElementById('waMessage').value = msg;
}

function sendWhatsappMessage() {
    const phone = document.getElementById('waPhone').value;
    const message = document.getElementById('waMessage').value.trim();
    if (!message) { alert('Digite uma mensagem.'); return; }
    sendViaEvolution(phone, message, 'waSendBtn', 'waSendFeedback');
}

function sendRenewWhatsapp() {
    const phone = document.getElementById('renewSuccessPhone').value;
    if (!phone) { alert('Este cliente não possui telefone cadastrado.'); return; }
    const message = document.getElementById('renewSuccessWhatsapp').value;
    if (!message) { alert('Nenhuma mensagem para enviar.'); return; }
    sendViaEvolution(phone, message, 'renewSuccessSendBtn', 'renewSuccessFeedback');
}

function sendViaEvolution(phone, message, btnId, feedbackId) {
    const btn = document.getElementById(btnId);
    const feedback = feedbackId ? document.getElementById(feedbackId) : null;
    
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-arrow-repeat animate-spin"></i> Enviando...';
    }
    if (feedback) feedback.classList.add('hidden');

    fetch('{{ route("clients.send-whatsapp") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ phone, message })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            if (feedback) {
                feedback.classList.remove('hidden');
                feedback.innerHTML = '<div class="px-3 py-2 bg-green-50 dark:bg-green-500/10 text-green-600 dark:text-green-400 rounded-lg text-sm flex items-center gap-2"><i class="bi bi-check-circle-fill"></i> Mensagem enviada com sucesso!</div>';
            }
            if (btn) {
                btn.innerHTML = '<i class="bi bi-check-circle"></i> Enviado!';
                setTimeout(() => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-send"></i> Enviar';
                }, 3000);
            }
        } else {
            if (feedback) {
                feedback.classList.remove('hidden');
                feedback.innerHTML = `<div class="px-3 py-2 bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 rounded-lg text-sm flex items-center gap-2"><i class="bi bi-exclamation-triangle"></i> ${data.message}</div>`;
            } else {
                alert(data.message);
            }
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-send"></i> Enviar';
            }
        }
    })
    .catch(() => {
        if (feedback) {
            feedback.classList.remove('hidden');
            feedback.innerHTML = '<div class="px-3 py-2 bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 rounded-lg text-sm flex items-center gap-2"><i class="bi bi-exclamation-triangle"></i> Erro de conexão ao enviar.</div>';
        } else {
            alert('Erro ao enviar mensagem.');
        }
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-send"></i> Enviar';
        }
    });
}
</script>
@endpush
@if(session('client_message'))
<div id="sessionClientMessageModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white dark:bg-dark-300 rounded-xl max-w-2xl w-full border border-gray-200 dark:border-dark-200 shadow-2xl">
        <div class="p-6 border-b border-gray-200 dark:border-dark-200 flex justify-between items-center bg-gradient-to-r from-green-600 to-green-700">
            <h3 class="text-xl font-bold text-white flex items-center gap-2">
                <i class="bi bi-check-circle-fill"></i>
                Cliente Criado com Sucesso!
            </h3>
            <button onclick="document.getElementById('sessionClientMessageModal').remove()" class="text-white hover:text-gray-200">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="p-6">
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">Mensagem do Cliente</label>
                <div class="relative">
                    <textarea id="sessionClientMessageText" readonly rows="12" class="w-full px-4 py-3 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white font-mono text-sm resize-none">{{ session('client_message') }}</textarea>
                    <button onclick="copyToClipboard('sessionClientMessageText')" class="absolute top-2 right-2 px-3 py-1.5 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors flex items-center gap-2 text-xs">
                        <i class="bi bi-clipboard"></i>
                        Copiar
                    </button>
                </div>
            </div>
            <div id="sessionMsgFeedback" class="hidden mb-3"></div>
            <div class="flex gap-3">
                <button onclick="document.getElementById('sessionClientMessageModal').remove()" class="flex-1 px-4 py-2 bg-gray-100 dark:bg-dark-200 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-dark-100 transition-colors font-medium">Fechar</button>
                @if(session('client_phone'))
                <button id="sessionMsgSendBtn" onclick="sendViaEvolution('{{ session('client_phone') }}', document.getElementById('sessionClientMessageText').value, 'sessionMsgSendBtn', 'sessionMsgFeedback')" class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium flex items-center justify-center gap-2">
                    <i class="bi bi-whatsapp"></i>
                    Enviar via WhatsApp
                </button>
                @else
                <button onclick="copyToClipboard('sessionClientMessageText')" class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium flex items-center justify-center gap-2">
                    <i class="bi bi-clipboard"></i>
                    Copiar Mensagem
                </button>
                @endif
            </div>
        </div>
    </div>
</div>
@endif

@endsection
