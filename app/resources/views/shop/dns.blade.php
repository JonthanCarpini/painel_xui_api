@extends('layouts.app')

@section('title', 'Comprar DNS')

@section('content')
<div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-6 md:mb-8 gap-4">
    <div>
        <h1 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
            <i class="bi bi-globe text-orange-500"></i>
            Comprar DNS
        </h1>
        <p class="text-sm md:text-base text-gray-500 dark:text-gray-400 mt-1">
            @if($isAdmin)
                Verifique a disponibilidade de dom&iacute;nios na Namecheap.
            @else
                Pesquise e compre dom&iacute;nios para usar nas suas listas.
            @endif
        </p>
    </div>
    @if(!$isAdmin)
    <a href="{{ route('shop.my-domains') }}" class="px-4 py-2 bg-gray-200 dark:bg-dark-200 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-dark-100 transition-colors text-sm font-medium flex items-center gap-2">
        <i class="bi bi-collection"></i> Meus Dom&iacute;nios
    </a>
    @endif
</div>

@if(!$isAdmin && !$hasShopGateway)
<div class="mb-6 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-900/30 rounded-xl flex items-center gap-3">
    <i class="bi bi-exclamation-triangle-fill text-yellow-500 text-xl"></i>
    <span class="text-yellow-700 dark:text-yellow-400 font-medium">O administrador ainda n&atilde;o configurou o gateway de pagamento da loja. A compra est&aacute; temporariamente indispon&iacute;vel.</span>
</div>
@endif

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
            <i class="bi bi-search"></i> Pesquisar
        </button>
    </div>
    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
        Extens&otilde;es: <strong>.{{ implode('</strong>, <strong>.', $extensions ?? ['online','site','website','xyz']) }}</strong>
        @if(!$isAdmin)
            &mdash; Cota&ccedil;&atilde;o atual: <strong>R$ {{ number_format($exchangeRate, 2, ',', '.') }}</strong>/USD
            @if($markupPercent > 0) + {{ $markupPercent }}% acr&eacute;scimo @endif
        @endif
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

<!-- Info -->
<div class="mt-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-900/30 rounded-xl p-5">
    <div class="flex items-start gap-3">
        <i class="bi bi-info-circle-fill text-blue-500 text-xl mt-0.5"></i>
        <div>
            <h4 class="font-bold text-blue-700 dark:text-blue-400 mb-1">Como funciona</h4>
            <ul class="text-sm text-blue-600 dark:text-blue-300 space-y-1">
                @if($isAdmin)
                    <li>- Voc&ecirc; pode verificar a disponibilidade de dom&iacute;nios.</li>
                    <li>- Configure o gateway e o percentual de acr&eacute;scimo em <strong>Configura&ccedil;&otilde;es</strong>.</li>
                    <li>- Os revendedores compram dom&iacute;nios pagando via PIX com acr&eacute;scimo.</li>
                @else
                    <li>- Pesquise o dom&iacute;nio desejado e veja o pre&ccedil;o em reais.</li>
                    <li>- Clique em <strong>Comprar</strong> para gerar o QR Code PIX.</li>
                    <li>- Ap&oacute;s o pagamento, o dom&iacute;nio &eacute; registrado automaticamente.</li>
                    <li>- Acesse <strong>Meus Dom&iacute;nios</strong> para gerenciar e ativar para as listas.</li>
                @endif
            </ul>
        </div>
    </div>
</div>

<!-- Modal PIX (apenas reseller) -->
@if(!$isAdmin)
<div id="pix-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-white dark:bg-dark-300 rounded-2xl border border-gray-200 dark:border-dark-200 shadow-2xl w-full max-w-md mx-4 overflow-hidden">
        <div class="p-6 border-b border-gray-200 dark:border-dark-200 flex items-center justify-between">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <i class="bi bi-qr-code text-green-500"></i>
                Pagamento PIX
            </h3>
            <button onclick="closePixModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                <i class="bi bi-x-lg text-xl"></i>
            </button>
        </div>
        <div class="p-6 text-center" id="pix-modal-body">
            <div id="pix-loading" class="hidden">
                <div class="animate-spin w-10 h-10 border-4 border-green-500 border-t-transparent rounded-full mx-auto mb-4"></div>
                <p class="text-gray-500 dark:text-gray-400">Gerando QR Code PIX...</p>
            </div>
            <div id="pix-content" class="hidden">
                <div class="bg-gray-50 dark:bg-dark-200 rounded-lg p-3 mb-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Dom&iacute;nio</p>
                    <p class="text-lg font-bold text-gray-900 dark:text-white" id="pix-domain"></p>
                </div>
                <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4 mb-4">
                    <p class="text-sm text-green-600 dark:text-green-400">Valor</p>
                    <p class="text-3xl font-bold text-green-700 dark:text-green-400" id="pix-value"></p>
                </div>
                <div class="mb-4">
                    <img id="pix-qr-image" src="" alt="QR Code PIX" class="mx-auto w-48 h-48 rounded-lg border border-gray-200 dark:border-dark-200">
                </div>
                <div class="mb-4">
                    <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Pix Copia e Cola</label>
                    <div class="flex items-center gap-2">
                        <input type="text" id="pix-copy-paste" readonly class="flex-1 px-3 py-2 bg-gray-100 dark:bg-dark-100 border border-gray-300 dark:border-dark-100 rounded-lg text-xs font-mono text-gray-600 dark:text-gray-400">
                        <button onclick="copyPixPayload()" class="px-3 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg text-sm transition-colors">
                            <i class="bi bi-clipboard"></i>
                        </button>
                    </div>
                </div>
                <div id="pix-status" class="p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-900/30 rounded-lg">
                    <p class="text-sm text-yellow-700 dark:text-yellow-400 flex items-center justify-center gap-2">
                        <i class="bi bi-hourglass-split"></i> Aguardando pagamento...
                    </p>
                </div>
            </div>
            <div id="pix-error" class="hidden">
                <i class="bi bi-exclamation-circle text-red-500 text-4xl mb-3"></i>
                <p class="text-red-700 dark:text-red-400 font-medium" id="pix-error-msg"></p>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
    const isAdmin = {{ $isAdmin ? 'true' : 'false' }};
    const hasGateway = {{ $hasShopGateway ? 'true' : 'false' }};
    let currentOrderRef = null;
    let pollInterval = null;

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
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Pesquisando...';

        fetch('{{ route("shop.dns.search") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
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

                let priceHtml = '';
                if (!isAdmin && item.available && item.price_brl) {
                    priceHtml = `<span class="text-xs text-gray-500 dark:text-gray-400 ml-2">R$ ${item.price_brl.toFixed(2).replace('.', ',')}/ano</span>`;
                }

                const info = document.createElement('div');
                info.innerHTML = `
                    <p class="font-bold text-gray-900 dark:text-white">${item.domain}${priceHtml}</p>
                    <p class="text-xs ${item.available ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'}">
                        ${item.available ? 'Dispon\u00edvel' : 'Indispon\u00edvel'}
                        ${item.premium ? ' (Premium)' : ''}
                    </p>
                `;

                left.appendChild(icon);
                left.appendChild(info);
                row.appendChild(left);

                if (item.available && !isAdmin && hasGateway) {
                    const buyBtn = document.createElement('button');
                    buyBtn.className = 'px-4 py-2 bg-gradient-to-r from-green-500 to-green-600 hover:shadow-lg hover:shadow-green-500/20 text-white rounded-lg transition-all text-sm font-medium flex items-center gap-2';
                    buyBtn.innerHTML = `<i class="bi bi-cart-plus"></i> R$ ${item.price_brl ? item.price_brl.toFixed(2).replace('.', ',') : '---'}`;
                    buyBtn.onclick = () => purchaseDomain(item.domain, item.price_brl);
                    row.appendChild(buyBtn);
                } else if (item.available && isAdmin) {
                    const badge = document.createElement('span');
                    badge.className = 'px-3 py-1 bg-green-100 dark:bg-green-500/20 text-green-700 dark:text-green-400 rounded-full text-xs font-medium';
                    badge.textContent = 'Dispon\u00edvel';
                    row.appendChild(badge);
                }

                list.appendChild(row);
            });
        })
        .catch(() => {
            loadingDiv.classList.add('hidden');
            errorDiv.classList.remove('hidden');
            document.getElementById('error-message').textContent = 'Erro de conex\u00e3o.';
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-search"></i> Pesquisar';
        });
    }

    @if(!$isAdmin)
    function purchaseDomain(domain, priceBrl) {
        const modal = document.getElementById('pix-modal');
        const loading = document.getElementById('pix-loading');
        const content = document.getElementById('pix-content');
        const error = document.getElementById('pix-error');

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        loading.classList.remove('hidden');
        content.classList.add('hidden');
        error.classList.add('hidden');

        fetch('{{ route("shop.dns.purchase") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify({ domain, years: 1 })
        })
        .then(r => r.json())
        .then(data => {
            loading.classList.add('hidden');

            if (!data.success) {
                error.classList.remove('hidden');
                document.getElementById('pix-error-msg').textContent = data.error || 'Erro ao gerar PIX.';
                return;
            }

            content.classList.remove('hidden');
            document.getElementById('pix-domain').textContent = domain;
            document.getElementById('pix-value').textContent = 'R$ ' + parseFloat(data.price_brl).toFixed(2).replace('.', ',');
            document.getElementById('pix-copy-paste').value = data.pix_payload || '';

            if (data.pix_encoded_image) {
                document.getElementById('pix-qr-image').src = 'data:image/png;base64,' + data.pix_encoded_image;
            }

            currentOrderRef = data.order_ref;
            startPolling();
        })
        .catch(() => {
            loading.classList.add('hidden');
            error.classList.remove('hidden');
            document.getElementById('pix-error-msg').textContent = 'Erro de conex\u00e3o.';
        });
    }

    function closePixModal() {
        const modal = document.getElementById('pix-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        stopPolling();
    }

    function copyPixPayload() {
        const input = document.getElementById('pix-copy-paste');
        navigator.clipboard.writeText(input.value).then(() => {
            const btn = input.nextElementSibling;
            btn.innerHTML = '<i class="bi bi-check text-white"></i>';
            setTimeout(() => btn.innerHTML = '<i class="bi bi-clipboard"></i>', 2000);
        });
    }

    function startPolling() {
        stopPolling();
        pollInterval = setInterval(() => {
            if (!currentOrderRef) return;
            fetch('/shop/dns/order/' + currentOrderRef + '/status')
                .then(r => r.json())
                .then(data => {
                    if (data.status === 'registered') {
                        stopPolling();
                        document.getElementById('pix-status').innerHTML = `
                            <p class="text-sm text-green-700 dark:text-green-400 flex items-center justify-center gap-2">
                                <i class="bi bi-check-circle-fill"></i> Pagamento confirmado! Dom\u00ednio registrado com sucesso!
                            </p>`;
                        document.getElementById('pix-status').className = 'p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-900/30 rounded-lg';
                    } else if (data.status === 'paid') {
                        document.getElementById('pix-status').innerHTML = `
                            <p class="text-sm text-blue-700 dark:text-blue-400 flex items-center justify-center gap-2">
                                <i class="bi bi-hourglass-split"></i> Pagamento recebido! Registrando dom\u00ednio...
                            </p>`;
                        document.getElementById('pix-status').className = 'p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-900/30 rounded-lg';
                    } else if (data.status === 'failed') {
                        stopPolling();
                        document.getElementById('pix-status').innerHTML = `
                            <p class="text-sm text-red-700 dark:text-red-400 flex items-center justify-center gap-2">
                                <i class="bi bi-x-circle-fill"></i> Falha ao registrar. Contate o administrador.
                            </p>`;
                        document.getElementById('pix-status').className = 'p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-900/30 rounded-lg';
                    }
                });
        }, 5000);
    }

    function stopPolling() {
        if (pollInterval) { clearInterval(pollInterval); pollInterval = null; }
    }

    document.getElementById('pix-modal').addEventListener('click', function(e) {
        if (e.target === this) closePixModal();
    });
    @endif
</script>
@endpush
