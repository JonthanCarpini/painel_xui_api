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
            <input type="text" id="domain-search" placeholder="Digite apenas o nome (ex: meupainel)" 
                class="w-full px-4 py-3 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white focus:border-orange-500 focus:outline-none transition-colors text-lg"
                onkeydown="if(event.key==='Enter') searchDomain()">
        </div>
        <button onclick="searchDomain()" id="btn-search" class="px-6 py-3 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg hover:shadow-lg hover:shadow-orange-500/20 transition-all font-medium flex items-center gap-2">
            <i class="bi bi-search"></i>
            Pesquisar
        </button>
    </div>
    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
        Digite apenas o nome (sem extens&atilde;o) para verificar as extens&otilde;es: <strong>.{{ implode('</strong>, <strong>.', $extensions ?? ['online','site','website','xyz']) }}</strong>
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
            <div id="results-list" class="divide-y divide-gray-200 dark:divide-dark-200"></div>
        </div>
    </div>

    <div id="results-error" class="hidden">
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-900/30 rounded-xl p-5 flex items-center gap-3">
            <i class="bi bi-exclamation-circle-fill text-red-500 text-xl"></i>
            <span id="error-message" class="text-red-700 dark:text-red-400 font-medium"></span>
        </div>
    </div>
</div>

<!-- Resultado do Registro -->
<div id="register-result" class="hidden mt-6"></div>

<!-- Info -->
<div class="mt-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-900/30 rounded-xl p-5">
    <div class="flex items-start gap-3">
        <i class="bi bi-info-circle-fill text-blue-500 text-xl mt-0.5"></i>
        <div>
            <h4 class="font-bold text-blue-700 dark:text-blue-400 mb-1">Sobre o registro de dom&iacute;nios</h4>
            <ul class="text-sm text-blue-600 dark:text-blue-300 space-y-1">
                <li>- Os dom&iacute;nios s&atilde;o registrados via <strong>Namecheap</strong>.</li>
                <li>- Extens&otilde;es dispon&iacute;veis: <strong>.online</strong>, <strong>.site</strong>, <strong>.website</strong>, <strong>.xyz</strong></li>
                <li>- Ap&oacute;s o registro, o dom&iacute;nio fica vinculado &agrave; sua conta Namecheap.</li>
                <li>- O valor &eacute; cobrado diretamente do saldo da conta Namecheap do administrador.</li>
            </ul>
        </div>
    </div>
</div>

<!-- Modal de Confirmação de Registro -->
<div id="register-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-white dark:bg-dark-300 rounded-2xl border border-gray-200 dark:border-dark-200 shadow-2xl w-full max-w-md mx-4 overflow-hidden">
        <div class="p-6 border-b border-gray-200 dark:border-dark-200">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <i class="bi bi-cart-check text-green-500"></i>
                Confirmar Registro
            </h3>
        </div>
        <div class="p-6">
            <div class="bg-gray-50 dark:bg-dark-200 rounded-lg p-4 mb-4">
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Dom&iacute;nio</p>
                <p class="text-xl font-bold text-gray-900 dark:text-white" id="modal-domain"></p>
            </div>
            <div class="grid grid-cols-2 gap-3 mb-4">
                <div class="bg-gray-50 dark:bg-dark-200 rounded-lg p-3">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Per&iacute;odo</p>
                    <select id="modal-years" class="w-full bg-transparent text-gray-900 dark:text-white font-bold text-sm focus:outline-none">
                        <option value="1">1 ano</option>
                        <option value="2">2 anos</option>
                        <option value="3">3 anos</option>
                        <option value="5">5 anos</option>
                    </select>
                </div>
                <div class="bg-gray-50 dark:bg-dark-200 rounded-lg p-3">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Status</p>
                    <p class="font-bold text-green-600 dark:text-green-400 text-sm flex items-center gap-1">
                        <i class="bi bi-check-circle-fill"></i> Dispon&iacute;vel
                    </p>
                </div>
            </div>
            <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-900/30 rounded-lg p-3 mb-4">
                <p class="text-xs text-yellow-700 dark:text-yellow-400 flex items-start gap-2">
                    <i class="bi bi-exclamation-triangle-fill mt-0.5"></i>
                    <span>O valor ser&aacute; cobrado do saldo Namecheap do administrador. Ap&oacute;s confirmar, o registro &eacute; imediato e n&atilde;o pode ser desfeito.</span>
                </p>
            </div>
        </div>
        <div class="p-6 pt-0 flex gap-3">
            <button onclick="closeRegisterModal()" class="flex-1 px-4 py-2.5 bg-gray-200 dark:bg-dark-200 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-dark-100 transition-colors font-medium text-sm">
                Cancelar
            </button>
            <button onclick="confirmRegister()" id="btn-confirm-register" class="flex-1 px-4 py-2.5 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg hover:shadow-lg hover:shadow-green-500/20 transition-all font-medium text-sm flex items-center justify-center gap-2">
                <i class="bi bi-check-circle"></i> Registrar Agora
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let selectedDomain = '';

    function searchDomain() {
        const input = document.getElementById('domain-search');
        const domain = input.value.trim().replace(/[^a-zA-Z0-9\-\.]/g, '');
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
        document.getElementById('register-result').classList.add('hidden');
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
                    const regBtn = document.createElement('button');
                    regBtn.className = 'px-4 py-2 bg-gradient-to-r from-green-500 to-green-600 hover:shadow-lg hover:shadow-green-500/20 text-white rounded-lg transition-all text-sm font-medium flex items-center gap-2';
                    regBtn.innerHTML = '<i class="bi bi-cart-plus"></i> Registrar';
                    regBtn.onclick = () => openRegisterModal(item.domain);
                    row.appendChild(regBtn);
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

    function openRegisterModal(domain) {
        selectedDomain = domain;
        document.getElementById('modal-domain').textContent = domain;
        document.getElementById('modal-years').value = '1';
        const modal = document.getElementById('register-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeRegisterModal() {
        const modal = document.getElementById('register-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        selectedDomain = '';
    }

    function confirmRegister() {
        if (!selectedDomain) return;

        const years = document.getElementById('modal-years').value;
        const btn = document.getElementById('btn-confirm-register');
        const resultDiv = document.getElementById('register-result');

        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-hourglass-split animate-spin"></i> Registrando...';

        fetch('{{ route("shop.dns.register") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ domain: selectedDomain, years: parseInt(years) })
        })
        .then(r => r.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-circle"></i> Registrar Agora';
            closeRegisterModal();

            resultDiv.classList.remove('hidden');

            if (data.success) {
                resultDiv.innerHTML = `
                    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-900/30 rounded-xl p-5">
                        <div class="flex items-start gap-3">
                            <i class="bi bi-check-circle-fill text-green-500 text-2xl mt-0.5"></i>
                            <div>
                                <h4 class="font-bold text-green-700 dark:text-green-400 mb-1">${data.message}</h4>
                                <div class="text-sm text-green-600 dark:text-green-300 space-y-1">
                                    ${data.data.order_id ? '<p><strong>Order ID:</strong> ' + data.data.order_id + '</p>' : ''}
                                    ${data.data.charged_amount ? '<p><strong>Valor cobrado:</strong> $' + data.data.charged_amount + '</p>' : ''}
                                </div>
                            </div>
                        </div>
                    </div>`;
            } else {
                resultDiv.innerHTML = `
                    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-900/30 rounded-xl p-5 flex items-center gap-3">
                        <i class="bi bi-exclamation-circle-fill text-red-500 text-xl"></i>
                        <span class="text-red-700 dark:text-red-400 font-medium">${data.error || 'Erro ao registrar dom\u00ednio.'}</span>
                    </div>`;
            }
        })
        .catch(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-circle"></i> Registrar Agora';
            closeRegisterModal();

            resultDiv.classList.remove('hidden');
            resultDiv.innerHTML = `
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-900/30 rounded-xl p-5 flex items-center gap-3">
                    <i class="bi bi-exclamation-circle-fill text-red-500 text-xl"></i>
                    <span class="text-red-700 dark:text-red-400 font-medium">Erro de conex\u00e3o. Tente novamente.</span>
                </div>`;
        });
    }

    document.getElementById('register-modal').addEventListener('click', function(e) {
        if (e.target === this) closeRegisterModal();
    });
</script>
@endpush
