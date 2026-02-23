@extends('layouts.app')

@section('title', 'Comprar DNS')

@section('content')
<div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-6 md:mb-8 gap-4">
    <div>
        <h1 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
            <i class="bi bi-globe text-orange-500"></i>
            Comprar DNS
        </h1>
        <p class="text-sm md:text-base text-gray-500 dark:text-gray-400 mt-1">Pesquise e registre dom&iacute;nios para seus clientes.</p>
    </div>
</div>

<!-- Busca de Domínio -->
<div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 p-6 shadow-sm dark:shadow-none mb-6">
    <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
        <i class="bi bi-search text-orange-500"></i>
        Pesquisar Dom&iacute;nio
    </h2>

    <div class="flex flex-col sm:flex-row gap-3">
        <div class="flex-1">
            <input type="text" id="domain-search" placeholder="Digite o dom&iacute;nio desejado (ex: meupainel)" 
                class="w-full px-4 py-3 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white focus:border-orange-500 focus:outline-none transition-colors text-lg"
                onkeydown="if(event.key==='Enter') searchDomain()">
        </div>
        <button onclick="searchDomain()" id="btn-search" class="px-6 py-3 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg hover:shadow-lg hover:shadow-orange-500/20 transition-all font-medium flex items-center gap-2">
            <i class="bi bi-search"></i>
            Pesquisar
        </button>
    </div>
    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
        Digite apenas o nome (sem extens&atilde;o) para verificar v&aacute;rias extens&otilde;es, ou o dom&iacute;nio completo (ex: meupainel.com).
    </p>
</div>

<!-- Resultados -->
<div id="search-results" class="hidden">
    <div id="results-loading" class="hidden">
        <div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 p-12 shadow-sm dark:shadow-none text-center">
            <div class="animate-spin w-10 h-10 border-4 border-orange-500 border-t-transparent rounded-full mx-auto mb-4"></div>
            <p class="text-gray-500 dark:text-gray-400">Verificando disponibilidade...</p>
        </div>
    </div>

    <div id="results-content" class="hidden">
        <div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 shadow-sm dark:shadow-none overflow-hidden">
            <div class="p-5 border-b border-gray-200 dark:border-dark-200">
                <h3 class="font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="bi bi-list-check text-orange-500"></i>
                    Resultados da Pesquisa
                </h3>
            </div>
            <div id="results-list" class="divide-y divide-gray-200 dark:divide-dark-200">
            </div>
        </div>
    </div>

    <div id="results-error" class="hidden">
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-900/30 rounded-xl p-5 flex items-center gap-3">
            <i class="bi bi-exclamation-circle-fill text-red-500 text-xl"></i>
            <span id="error-message" class="text-red-700 dark:text-red-400 font-medium"></span>
        </div>
    </div>
</div>

<!-- Info -->
<div class="mt-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-900/30 rounded-xl p-5">
    <div class="flex items-start gap-3">
        <i class="bi bi-info-circle-fill text-blue-500 text-xl mt-0.5"></i>
        <div>
            <h4 class="font-bold text-blue-700 dark:text-blue-400 mb-1">Sobre o registro de dom&iacute;nios</h4>
            <ul class="text-sm text-blue-600 dark:text-blue-300 space-y-1">
                <li>- Os dom&iacute;nios s&atilde;o registrados via <strong>Namecheap</strong>.</li>
                <li>- Ap&oacute;s o registro, o DNS ser&aacute; configurado automaticamente.</li>
                <li>- O pagamento ser&aacute; feito via seu gateway de pagamento configurado.</li>
            </ul>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function searchDomain() {
        const input = document.getElementById('domain-search');
        const domain = input.value.trim();
        if (!domain) return;

        const resultsDiv = document.getElementById('search-results');
        const loadingDiv = document.getElementById('results-loading');
        const contentDiv = document.getElementById('results-content');
        const errorDiv = document.getElementById('results-error');
        const btn = document.getElementById('btn-search');

        resultsDiv.classList.remove('hidden');
        loadingDiv.classList.remove('hidden');
        contentDiv.classList.add('hidden');
        errorDiv.classList.add('hidden');
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Pesquisando...';

        fetch('{{ route("shop.dns.search") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ domain })
        })
        .then(r => r.json())
        .then(data => {
            loadingDiv.classList.add('hidden');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-search"></i> Pesquisar';

            if (!data.success) {
                errorDiv.classList.remove('hidden');
                document.getElementById('error-message').textContent = data.error || 'Erro desconhecido';
                return;
            }

            contentDiv.classList.remove('hidden');
            const list = document.getElementById('results-list');
            list.innerHTML = '';

            data.data.forEach(item => {
                const row = document.createElement('div');
                row.className = 'flex items-center justify-between p-4 hover:bg-gray-50 dark:hover:bg-dark-200 transition-colors';
                
                const left = document.createElement('div');
                left.className = 'flex items-center gap-3';
                
                const icon = document.createElement('div');
                icon.className = item.available 
                    ? 'w-8 h-8 rounded-full bg-green-100 dark:bg-green-500/20 flex items-center justify-center'
                    : 'w-8 h-8 rounded-full bg-red-100 dark:bg-red-500/20 flex items-center justify-center';
                icon.innerHTML = item.available 
                    ? '<i class="bi bi-check-lg text-green-600 dark:text-green-400"></i>'
                    : '<i class="bi bi-x-lg text-red-600 dark:text-red-400"></i>';
                
                const info = document.createElement('div');
                info.innerHTML = `
                    <p class="font-bold text-gray-900 dark:text-white">${item.domain}</p>
                    <p class="text-xs ${item.available ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'}">
                        ${item.available ? 'Dispon\u00edvel' : 'Indispon\u00edvel'}
                        ${item.premium ? ' (Premium)' : ''}
                    </p>
                `;
                
                left.appendChild(icon);
                left.appendChild(info);
                row.appendChild(left);

                if (item.available) {
                    const btn = document.createElement('button');
                    btn.className = 'px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg transition-colors text-sm font-medium';
                    btn.innerHTML = '<i class="bi bi-cart-plus"></i> Registrar';
                    btn.onclick = () => alert('Funcionalidade de registro em desenvolvimento. Em breve!');
                    row.appendChild(btn);
                }

                list.appendChild(row);
            });
        })
        .catch(() => {
            loadingDiv.classList.add('hidden');
            errorDiv.classList.remove('hidden');
            document.getElementById('error-message').textContent = 'Erro de conex\u00e3o. Tente novamente.';
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-search"></i> Pesquisar';
        });
    }
</script>
@endpush
