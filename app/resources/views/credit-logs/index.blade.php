@extends('layouts.app')

@section('title', 'Meus Logs de Créditos')

@section('content')
<div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-6 gap-4">
    <h1 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
        <i class="bi bi-wallet2 text-orange-500"></i>
        Meus Logs de Créditos
    </h1>
    
    @if(Auth::user()->isAdmin() || Auth::user()->member_group_id == 2)
    <div class="flex gap-2">
        <a href="{{ route('credit-logs.resellers') }}" class="px-4 py-2 bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 rounded-lg hover:bg-blue-200 dark:hover:bg-blue-900/50 transition-colors flex items-center gap-2">
            <i class="bi bi-people"></i>
            Logs de Revendas
        </a>
    </div>
    @endif
</div>

<!-- Filtros -->
<div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 p-4 mb-6 shadow-sm dark:shadow-none">
    <form action="{{ route('credit-logs.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-6 gap-4">
        <div class="md:col-span-1">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-1">Buscar</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Geral..." class="w-full px-3 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white focus:border-orange-500 focus:outline-none transition-colors text-sm">
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-1">Natureza</label>
            <select name="nature" class="w-full px-3 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white focus:border-orange-500 focus:outline-none transition-colors text-sm">
                <option value="">Todas</option>
                <option value="in" {{ request('nature') == 'in' ? 'selected' : '' }}>Entrada (+)</option>
                <option value="out" {{ request('nature') == 'out' ? 'selected' : '' }}>Saída (-)</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-1">Tipo</label>
            <select name="type" class="w-full px-3 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white focus:border-orange-500 focus:outline-none transition-colors text-sm">
                <option value="">Todos</option>
                <option value="client" {{ request('type') == 'client' ? 'selected' : '' }}>Cliente</option>
                <option value="reseller" {{ request('type') == 'reseller' ? 'selected' : '' }}>Revenda</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-1">Destino</label>
            <div class="relative">
                <input type="text" id="destinationInput" name="destination" value="{{ request('destination') }}" placeholder="User..." autocomplete="off" class="w-full px-3 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white focus:border-orange-500 focus:outline-none transition-colors text-sm">
                <div id="destinationDropdown" class="hidden absolute z-10 w-full bg-white dark:bg-dark-200 border border-gray-200 dark:border-dark-100 rounded-lg shadow-lg mt-1 max-h-60 overflow-y-auto"></div>
            </div>
        </div>

        <div class="md:col-span-2 grid grid-cols-2 gap-2">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-1">Início</label>
                <input type="date" name="date_start" value="{{ request('date_start') }}" class="w-full px-3 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white focus:border-orange-500 focus:outline-none transition-colors text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-1">Fim</label>
                <input type="date" name="date_end" value="{{ request('date_end') }}" class="w-full px-3 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white focus:border-orange-500 focus:outline-none transition-colors text-sm">
            </div>
        </div>

        <div class="md:col-span-6 flex justify-end gap-2 mt-2">
            <a href="{{ route('credit-logs.index') }}" class="px-4 py-2 bg-gray-100 dark:bg-dark-200 text-gray-600 dark:text-gray-400 rounded-lg hover:bg-gray-200 transition-colors text-sm font-medium flex items-center gap-2">
                <i class="bi bi-x-lg"></i> Limpar
            </a>
            <button type="submit" class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-lg transition-colors text-sm font-medium flex items-center gap-2">
                <i class="bi bi-filter"></i> Filtrar Resultados
            </button>
        </div>
    </form>
</div>

<!-- Tabela -->
<div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 overflow-hidden shadow-sm dark:shadow-none">
    @if(count($logs) > 0)
    <div class="overflow-x-auto custom-scrollbar">
        <table class="w-full">
            <thead class="bg-gray-50 dark:bg-dark-200 border-b border-gray-200 dark:border-dark-100">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Data</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Natureza</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Tipo</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Descrição</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Destino</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Saldo Ant.</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Movimentação</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Saldo Post.</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-dark-200">
                @foreach($logs as $log)
                <tr class="hover:bg-gray-50 dark:hover:bg-dark-200 transition-colors duration-150">
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                        {{ $log['formatted_date'] }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $log['nature_class'] }}">
                            {{ $log['nature_label'] }}
                        </span>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <div class="flex items-center gap-2 text-sm {{ $log['type_class'] }}">
                            <i class="bi {{ $log['type_icon'] }}"></i>
                            <span class="font-medium">{{ $log['type_label'] }}</span>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <div class="text-sm text-gray-900 dark:text-white font-medium">{{ $log['description_formatted'] }}</div>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <div class="text-sm font-mono text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-dark-100 px-2 py-0.5 rounded inline-block">
                            {{ $log['destination'] }}
                        </div>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-mono text-gray-600 dark:text-gray-400">
                        {{ number_format((float)$log['credits_before'], 2) }}
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-right">
                        <span class="text-sm font-bold {{ $log['nature'] == 'in' ? 'text-green-600 dark:text-green-400' : ($log['nature'] == 'out' ? 'text-red-600 dark:text-red-400' : 'text-gray-600') }}">
                            {{ $log['amount_formatted'] }}
                        </span>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-bold font-mono text-gray-900 dark:text-white">
                        {{ number_format((float)$log['credits_after'], 2) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="px-4 py-3 border-t border-gray-200 dark:border-dark-100">
        {{ $logs->links() }}
    </div>
    @else
    <div class="text-center py-16">
        <i class="bi bi-wallet2 text-gray-400 dark:text-gray-600 text-6xl mb-4"></i>
        <h3 class="text-xl font-semibold text-gray-600 dark:text-gray-400 mb-2">Nenhum log encontrado</h3>
        <p class="text-gray-500">Você ainda não possui movimentações registradas ou os filtros não retornaram resultados.</p>
    </div>
    @endif
</div>

@push('scripts')
<script>
    const destinationInput = document.getElementById('destinationInput');
    const destinationDropdown = document.getElementById('destinationDropdown');
    let debounceTimer;

    destinationInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        const term = this.value;
        
        if (term.length < 2) {
            destinationDropdown.classList.add('hidden');
            destinationDropdown.innerHTML = '';
            return;
        }

        debounceTimer = setTimeout(() => {
            fetch(`{{ route('credit-logs.search-destinations') }}?term=${encodeURIComponent(term)}`)
                .then(response => response.json())
                .then(data => {
                    destinationDropdown.innerHTML = '';
                    if (data.length > 0) {
                        data.forEach(user => {
                            const div = document.createElement('div');
                            div.className = 'px-4 py-2 hover:bg-gray-100 dark:hover:bg-dark-100 cursor-pointer text-sm text-gray-700 dark:text-gray-300';
                            div.textContent = user;
                            div.onclick = () => {
                                destinationInput.value = user;
                                destinationDropdown.classList.add('hidden');
                            };
                            destinationDropdown.appendChild(div);
                        });
                        destinationDropdown.classList.remove('hidden');
                    } else {
                        destinationDropdown.classList.add('hidden');
                    }
                })
                .catch(err => console.error(err));
        }, 300);
    });

    document.addEventListener('click', function(e) {
        if (!destinationInput.contains(e.target) && !destinationDropdown.contains(e.target)) {
            destinationDropdown.classList.add('hidden');
        }
    });
</script>
@endpush
@endsection
