@extends('layouts.app')

@section('title', 'Exportar Clientes')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
        <i class="bi bi-download text-orange-500"></i>
        Exportar Clientes
    </h1>
    <a href="{{ route('clients.index') }}" class="px-4 py-2 bg-gray-100 dark:bg-dark-200 text-gray-600 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-dark-100 transition-colors flex items-center gap-2 font-medium">
        <i class="bi bi-arrow-left"></i>
        Voltar
    </a>
</div>

<!-- Filtros -->
<div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 p-6 mb-6 shadow-sm dark:shadow-none">
    <h3 class="text-gray-900 dark:text-white font-semibold mb-4">Filtros de Exportação</h3>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm text-gray-500 dark:text-gray-400 mb-2">Username / Senha</label>
            <input type="text" id="searchInput" placeholder="Buscar por username ou senha" class="w-full px-4 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white focus:border-orange-500 focus:outline-none transition-colors">
        </div>
        <div>
            <label class="block text-sm text-gray-500 dark:text-gray-400 mb-2">Telefone</label>
            <input type="text" id="phoneInput" placeholder="Buscar por telefone" class="w-full px-4 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white focus:border-orange-500 focus:outline-none transition-colors">
        </div>
        <div>
            <label class="block text-sm text-gray-500 dark:text-gray-400 mb-2">Status</label>
            <select id="statusFilter" class="w-full px-4 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white focus:border-orange-500 focus:outline-none transition-colors">
                <option value="">Todos</option>
                <option value="active">Ativo</option>
                <option value="expired">Vencido</option>
                <option value="trial">Teste</option>
            </select>
        </div>
    </div>
    
    <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-500/30 rounded-lg">
        <p class="text-blue-600 dark:text-blue-400 text-sm flex items-center gap-2">
            <i class="bi bi-info-circle"></i>
            Os filtros aplicados acima serão usados na exportação. Deixe em branco para exportar todos os clientes.
        </p>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- Exportar CSV -->
    <div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 p-6 shadow-sm dark:shadow-none hover:shadow-md transition-shadow">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-12 h-12 bg-green-100 dark:bg-green-500/20 rounded-lg flex items-center justify-center">
                <i class="bi bi-filetype-csv text-2xl text-green-600 dark:text-green-400"></i>
            </div>
            <div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Exportar CSV</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Formato compatível com Excel</p>
            </div>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mb-4 text-sm">
            Escolha entre o relatório completo ou apenas lista de contatos.
        </p>
        <div class="flex flex-col gap-2">
            <button onclick="exportCSV('full')" class="w-full px-4 py-2 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg hover:shadow-lg transition-all flex items-center justify-center gap-2 font-medium">
                <i class="bi bi-download"></i>
                Exportar Completo
            </button>
            <button onclick="exportCSV('simple')" class="w-full px-4 py-2 bg-gray-100 dark:bg-dark-200 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-dark-100 transition-all flex items-center justify-center gap-2 font-medium border border-gray-200 dark:border-dark-100">
                <i class="bi bi-person-lines-fill"></i>
                Exportar Contatos (Nome/Tel)
            </button>
        </div>
    </div>

    <!-- Exportar TXT -->
    <div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 p-6 shadow-sm dark:shadow-none hover:shadow-md transition-shadow">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-12 h-12 bg-blue-100 dark:bg-blue-500/20 rounded-lg flex items-center justify-center">
                <i class="bi bi-file-text text-2xl text-blue-600 dark:text-blue-400"></i>
            </div>
            <div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Exportar TXT</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Formato texto simples</p>
            </div>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mb-4 text-sm">
            Exporte uma lista simples com username e senha, um por linha, ideal para importação em outros sistemas.
        </p>
        <button onclick="exportTXT()" class="w-full px-4 py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg hover:shadow-lg transition-all flex items-center justify-center gap-2 font-medium">
            <i class="bi bi-download"></i>
            Baixar TXT
        </button>
    </div>

    <!-- Exportar JSON -->
    <div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 p-6 shadow-sm dark:shadow-none hover:shadow-md transition-shadow">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-12 h-12 bg-purple-100 dark:bg-purple-500/20 rounded-lg flex items-center justify-center">
                <i class="bi bi-filetype-json text-2xl text-purple-600 dark:text-purple-400"></i>
            </div>
            <div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Exportar JSON</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Formato para APIs</p>
            </div>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mb-4 text-sm">
            Exporte todos os dados dos clientes em formato JSON, incluindo todas as informações detalhadas.
        </p>
        <button onclick="exportJSON()" class="w-full px-4 py-3 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-lg hover:shadow-lg transition-all flex items-center justify-center gap-2 font-medium">
            <i class="bi bi-download"></i>
            Baixar JSON
        </button>
    </div>

    <!-- Exportar M3U em Massa -->
    <div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 p-6 shadow-sm dark:shadow-none hover:shadow-md transition-shadow">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-12 h-12 bg-orange-100 dark:bg-orange-500/20 rounded-lg flex items-center justify-center">
                <i class="bi bi-file-earmark-code text-2xl text-orange-600 dark:text-orange-400"></i>
            </div>
            <div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Exportar M3U</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Links de playlist</p>
            </div>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mb-4 text-sm">
            Exporte uma lista com todos os links M3U dos seus clientes para compartilhamento rápido.
        </p>
        <button onclick="exportM3U()" class="w-full px-4 py-3 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg hover:shadow-lg transition-all flex items-center justify-center gap-2 font-medium">
            <i class="bi bi-download"></i>
            Baixar M3U
        </button>
    </div>
</div>

<!-- Informações -->
<div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 p-6 mt-6 shadow-sm dark:shadow-none">
    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
        <i class="bi bi-info-circle text-orange-500"></i>
        Informações sobre Exportação
    </h3>
    <ul class="space-y-2 text-gray-600 dark:text-gray-300 text-sm">
        <li class="flex items-start gap-2">
            <i class="bi bi-check-circle text-green-500 mt-0.5"></i>
            <span>Os arquivos exportados contêm apenas os clientes que você tem permissão para visualizar</span>
        </li>
        <li class="flex items-start gap-2">
            <i class="bi bi-check-circle text-green-500 mt-0.5"></i>
            <span>Clientes inativos ou vencidos também são incluídos na exportação</span>
        </li>
        <li class="flex items-start gap-2">
            <i class="bi bi-check-circle text-green-500 mt-0.5"></i>
            <span>Os dados são exportados em tempo real, sempre atualizados</span>
        </li>
        <li class="flex items-start gap-2">
            <i class="bi bi-shield-check text-orange-500 mt-0.5"></i>
            <span>Mantenha os arquivos exportados em local seguro, pois contêm informações sensíveis</span>
        </li>
    </ul>
</div>
@endsection

@push('scripts')
<script>
function getFilters() {
    const search = document.getElementById('searchInput')?.value || '';
    const phone = document.getElementById('phoneInput')?.value || '';
    const status = document.getElementById('statusFilter')?.value || '';
    
    const params = new URLSearchParams();
    if (search) params.append('search', search);
    if (phone) params.append('phone', phone);
    if (status) params.append('status', status);
    
    return params.toString();
}

function exportCSV(mode = 'full') {
    const filters = getFilters();
    // Converter string de filtros em URLSearchParams para manipular facilmente
    const params = new URLSearchParams(filters);
    params.append('mode', mode);
    
    window.location.href = '/clients/export/csv?' + params.toString();
}

function exportTXT() {
    const filters = getFilters();
    window.location.href = '/clients/export/txt' + (filters ? '?' + filters : '');
}

function exportJSON() {
    const filters = getFilters();
    window.location.href = '/clients/export/json' + (filters ? '?' + filters : '');
}

function exportM3U() {
    const filters = getFilters();
    window.location.href = '/clients/export/m3u' + (filters ? '?' + filters : '');
}
</script>
@endpush
