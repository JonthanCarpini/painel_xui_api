@extends('layouts.app')

@section('title', 'Gateway de Pagamento')

@section('content')
<div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-6 md:mb-8 gap-4">
    <div>
        <h1 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
            <i class="bi bi-credit-card text-orange-500"></i>
            Gateway de Pagamento
        </h1>
        <p class="text-sm md:text-base text-gray-500 dark:text-gray-400 mt-1">Configure seus gateways de pagamento PIX para auto-renova&ccedil;&atilde;o.</p>
    </div>
</div>

@if(session('success'))
<div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-900/30 rounded-xl flex items-center gap-3">
    <i class="bi bi-check-circle-fill text-green-500 text-xl"></i>
    <span class="text-green-700 dark:text-green-400 font-medium">{{ session('success') }}</span>
</div>
@endif

@if($errors->any())
<div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-900/30 rounded-xl flex items-center gap-3">
    <i class="bi bi-exclamation-circle-fill text-red-500 text-xl"></i>
    <span class="text-red-700 dark:text-red-400 font-medium">{{ $errors->first() }}</span>
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    @foreach($providers as $providerKey => $providerLabel)
    @php $gateway = $gatewaysByProvider[$providerKey] ?? null; @endphp
    <div class="bg-white dark:bg-dark-300 rounded-xl border {{ $gateway && $gateway->active ? 'border-green-400 dark:border-green-500/50 ring-2 ring-green-200 dark:ring-green-500/20' : 'border-gray-200 dark:border-dark-200' }} shadow-sm dark:shadow-none overflow-hidden">
        <!-- Header -->
        <div class="p-5 border-b border-gray-200 dark:border-dark-200 flex items-center justify-between">
            <div class="flex items-center gap-3">
                @if($providerKey === 'asaas')
                    <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-500/20 flex items-center justify-center">
                        <i class="bi bi-bank text-blue-600 dark:text-blue-400 text-xl"></i>
                    </div>
                @elseif($providerKey === 'mercadopago')
                    <div class="w-10 h-10 rounded-lg bg-sky-100 dark:bg-sky-500/20 flex items-center justify-center">
                        <i class="bi bi-cash-coin text-sky-600 dark:text-sky-400 text-xl"></i>
                    </div>
                @else
                    <div class="w-10 h-10 rounded-lg bg-purple-100 dark:bg-purple-500/20 flex items-center justify-center">
                        <i class="bi bi-lightning text-purple-600 dark:text-purple-400 text-xl"></i>
                    </div>
                @endif
                <div>
                    <h3 class="font-bold text-gray-900 dark:text-white">{{ $providerLabel }}</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Pagamento via PIX</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" onclick="showInstructions('{{ $providerKey }}')" class="p-2 text-gray-400 hover:text-orange-500 transition-colors" title="Instru&ccedil;&otilde;es">
                    <i class="bi bi-question-circle text-lg"></i>
                </button>
                @if($gateway)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold {{ $gateway->active ? 'bg-green-100 dark:bg-green-500/20 text-green-700 dark:text-green-400' : 'bg-gray-100 dark:bg-gray-500/20 text-gray-600 dark:text-gray-400' }}">
                        <span class="w-2 h-2 rounded-full {{ $gateway->active ? 'bg-green-500 animate-pulse' : 'bg-gray-400' }}"></span>
                        {{ $gateway->active ? 'Ativo' : 'Inativo' }}
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 dark:bg-gray-500/20 text-gray-600 dark:text-gray-400">
                        <span class="w-2 h-2 rounded-full bg-gray-400"></span>
                        N&atilde;o configurado
                    </span>
                @endif
            </div>
        </div>

        <!-- Body -->
        <div class="p-5">
            @if($gateway)
                <form action="{{ route('payments.gateways.update', $gateway->id) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    @if($providerKey === 'asaas')
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-1">Access Token</label>
                            <input type="password" name="access_token" value="{{ $gateway->getCredential('access_token') }}" required
                                class="w-full px-3 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white text-sm focus:border-orange-500 focus:outline-none transition-colors font-mono"
                                placeholder="$aact_prod_...">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-1">Address Key (Chave PIX)</label>
                            <input type="text" name="address_key" value="{{ $gateway->getCredential('address_key') }}" required
                                class="w-full px-3 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white text-sm focus:border-orange-500 focus:outline-none transition-colors font-mono"
                                placeholder="uuid-da-chave-pix">
                        </div>
                    @elseif($providerKey === 'mercadopago')
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-1">Access Token</label>
                            <input type="password" name="access_token" value="{{ $gateway->getCredential('access_token') }}" required
                                class="w-full px-3 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white text-sm focus:border-orange-500 focus:outline-none transition-colors font-mono"
                                placeholder="APP_USR-...">
                        </div>
                    @elseif($providerKey === 'fastdepix')
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-1">Token</label>
                            <input type="password" name="token" value="{{ $gateway->getCredential('token') }}" required
                                class="w-full px-3 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white text-sm focus:border-orange-500 focus:outline-none transition-colors font-mono"
                                placeholder="Token FastDePix">
                        </div>
                    @endif

                    <!-- Webhook URL -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-1">URL do Webhook</label>
                        <div class="flex items-center gap-2">
                            <input type="text" readonly value="{{ url('/webhook/' . $providerKey . '/' . $gateway->webhook_secret) }}"
                                class="flex-1 px-3 py-2 bg-gray-100 dark:bg-dark-100 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-600 dark:text-gray-400 text-xs font-mono cursor-text"
                                id="webhook-url-{{ $gateway->id }}">
                            <button type="button" onclick="copyWebhook('{{ $gateway->id }}')" class="px-3 py-2 bg-gray-200 dark:bg-dark-200 text-gray-600 dark:text-gray-400 rounded-lg hover:bg-gray-300 dark:hover:bg-dark-100 transition-colors text-sm">
                                <i class="bi bi-clipboard"></i>
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Configure esta URL no painel do {{ $providerLabel }}.</p>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-wrap gap-2 pt-2">
                        <button type="submit" class="flex-1 px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-lg transition-colors text-sm font-medium">
                            <i class="bi bi-check-circle"></i> Salvar
                        </button>
                        <button type="button" onclick="testGateway({{ $gateway->id }})" class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors text-sm font-medium">
                            <i class="bi bi-plug"></i> Testar
                        </button>
                    </div>
                </form>

                <div class="flex gap-2 mt-3 pt-3 border-t border-gray-200 dark:border-dark-200">
                    <form action="{{ route('payments.gateways.toggle', $gateway->id) }}" method="POST" class="flex-1">
                        @csrf
                        <button type="submit" class="w-full px-4 py-2 {{ $gateway->active ? 'bg-yellow-500 hover:bg-yellow-600' : 'bg-green-500 hover:bg-green-600' }} text-white rounded-lg transition-colors text-sm font-medium">
                            <i class="bi {{ $gateway->active ? 'bi-pause-circle' : 'bi-play-circle' }}"></i>
                            {{ $gateway->active ? 'Desativar' : 'Ativar' }}
                        </button>
                    </form>
                    <form action="{{ route('payments.gateways.destroy', $gateway->id) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja remover este gateway?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition-colors text-sm font-medium">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </div>

                <div id="test-result-{{ $gateway->id }}" class="hidden mt-3 p-3 rounded-lg text-sm"></div>

            @else
                <form action="{{ route('payments.gateways.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="provider" value="{{ $providerKey }}">

                    @if($providerKey === 'asaas')
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-1">Access Token</label>
                            <input type="password" name="access_token" required
                                class="w-full px-3 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white text-sm focus:border-orange-500 focus:outline-none transition-colors font-mono"
                                placeholder="$aact_prod_...">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-1">Address Key (Chave PIX)</label>
                            <input type="text" name="address_key" required
                                class="w-full px-3 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white text-sm focus:border-orange-500 focus:outline-none transition-colors font-mono"
                                placeholder="uuid-da-chave-pix">
                        </div>
                    @elseif($providerKey === 'mercadopago')
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-1">Access Token</label>
                            <input type="password" name="access_token" required
                                class="w-full px-3 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white text-sm focus:border-orange-500 focus:outline-none transition-colors font-mono"
                                placeholder="APP_USR-...">
                        </div>
                    @elseif($providerKey === 'fastdepix')
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-1">Token</label>
                            <input type="password" name="token" required
                                class="w-full px-3 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white text-sm focus:border-orange-500 focus:outline-none transition-colors font-mono"
                                placeholder="Token FastDePix">
                        </div>
                    @endif

                    <button type="submit" class="w-full px-4 py-2.5 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg hover:shadow-lg hover:shadow-orange-500/20 transition-all text-sm font-medium">
                        <i class="bi bi-plus-circle"></i> Configurar {{ $providerLabel }}
                    </button>
                </form>
            @endif
        </div>
    </div>
    @endforeach
</div>

<!-- Info Card -->
<div class="mt-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-900/30 rounded-xl p-5">
    <div class="flex items-start gap-3">
        <i class="bi bi-info-circle-fill text-blue-500 text-xl mt-0.5"></i>
        <div>
            <h4 class="font-bold text-blue-700 dark:text-blue-400 mb-1">Como funciona?</h4>
            <ul class="text-sm text-blue-600 dark:text-blue-300 space-y-1">
                <li>1. Configure as credenciais do seu gateway de pagamento.</li>
                <li>2. Copie a URL do Webhook e configure no painel do gateway.</li>
                <li>3. Clique em <strong>Ativar</strong> no gateway que deseja usar.</li>
                <li>4. Apenas <strong>um gateway</strong> pode estar ativo por vez.</li>
                <li>5. Quando um cliente pagar via PIX, o sistema renovar&aacute; automaticamente a linha.</li>
            </ul>
        </div>
    </div>
</div>

<!-- Modal de Instruções -->
<div id="instructions-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-white dark:bg-dark-300 rounded-2xl border border-gray-200 dark:border-dark-200 shadow-2xl w-full max-w-lg mx-4 overflow-hidden max-h-[90vh] flex flex-col">
        <div class="p-6 border-b border-gray-200 dark:border-dark-200 flex items-center justify-between shrink-0">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2" id="instructions-title">
                <i class="bi bi-book text-orange-500"></i>
                Instru&ccedil;&otilde;es
            </h3>
            <button onclick="closeInstructions()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
                <i class="bi bi-x-lg text-xl"></i>
            </button>
        </div>
        <div class="p-6 overflow-y-auto" id="instructions-body"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const instructions = {
        asaas: {
            title: '<i class="bi bi-bank text-blue-500"></i> Instru\u00e7\u00f5es - Asaas',
            body: `
                <div class="space-y-4">
                    <div>
                        <h4 class="font-bold text-gray-900 dark:text-white mb-2">1. Criar conta no Asaas</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Acesse <a href="https://www.asaas.com" target="_blank" class="text-orange-500 hover:underline font-medium">asaas.com</a> e crie sua conta. Complete a verifica\u00e7\u00e3o de identidade para liberar o PIX.</p>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-900 dark:text-white mb-2">2. Gerar Access Token (API Key)</h4>
                        <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1 ml-4 list-disc">
                            <li>Acesse <strong>Configura\u00e7\u00f5es &gt; Integra\u00e7\u00f5es &gt; API</strong></li>
                            <li>Ou diretamente em <a href="https://www.asaas.com/apiKeys" target="_blank" class="text-orange-500 hover:underline">asaas.com/apiKeys</a></li>
                            <li>Clique em <strong>"Gerar nova chave de API"</strong></li>
                            <li>Copie o token gerado (come\u00e7a com <code class="bg-gray-100 dark:bg-dark-200 px-1 rounded">$aact_prod_</code>)</li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-900 dark:text-white mb-2">3. Obter Address Key (Chave PIX)</h4>
                        <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1 ml-4 list-disc">
                            <li>Acesse <strong>Cobran\u00e7as &gt; Receber via Pix</strong></li>
                            <li>Cadastre uma chave PIX (CPF, e-mail, celular ou aleat\u00f3ria)</li>
                            <li>O <strong>Address Key</strong> \u00e9 o UUID da chave cadastrada</li>
                            <li>Voc\u00ea pode obter via API: <code class="bg-gray-100 dark:bg-dark-200 px-1 rounded text-xs">GET /pix/addressKeys</code></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-900 dark:text-white mb-2">4. Configurar Webhook</h4>
                        <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1 ml-4 list-disc">
                            <li>Acesse <strong>Configura\u00e7\u00f5es &gt; Integra\u00e7\u00f5es &gt; Webhooks</strong></li>
                            <li>Clique em <strong>"Adicionar webhook"</strong></li>
                            <li>Cole a <strong>URL do Webhook</strong> exibida no card acima</li>
                            <li>Selecione os eventos: <strong>PAYMENT_RECEIVED</strong> e <strong>PAYMENT_CONFIRMED</strong></li>
                            <li>Salve a configura\u00e7\u00e3o</li>
                        </ul>
                    </div>
                    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-900/30 rounded-lg p-3">
                        <p class="text-sm text-green-700 dark:text-green-400 flex items-start gap-2">
                            <i class="bi bi-check-circle-fill mt-0.5"></i>
                            <span>Ap\u00f3s configurar, clique em <strong>"Testar"</strong> para verificar se a conex\u00e3o est\u00e1 funcionando.</span>
                        </p>
                    </div>
                </div>
            `
        },
        mercadopago: {
            title: '<i class="bi bi-cash-coin text-sky-500"></i> Instru\u00e7\u00f5es - Mercado Pago',
            body: `
                <div class="space-y-4">
                    <div>
                        <h4 class="font-bold text-gray-900 dark:text-white mb-2">1. Criar conta no Mercado Pago</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Acesse <a href="https://www.mercadopago.com.br" target="_blank" class="text-orange-500 hover:underline font-medium">mercadopago.com.br</a> e crie sua conta.</p>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-900 dark:text-white mb-2">2. Criar aplica\u00e7\u00e3o</h4>
                        <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1 ml-4 list-disc">
                            <li>Acesse <a href="https://www.mercadopago.com.br/developers/panel/app" target="_blank" class="text-orange-500 hover:underline">mercadopago.com.br/developers</a></li>
                            <li>Clique em <strong>"Criar aplica\u00e7\u00e3o"</strong></li>
                            <li>Selecione <strong>"Pagamentos online"</strong> e <strong>"CheckoutAPI"</strong></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-900 dark:text-white mb-2">3. Obter Access Token</h4>
                        <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1 ml-4 list-disc">
                            <li>Na aplica\u00e7\u00e3o criada, v\u00e1 em <strong>"Credenciais de produ\u00e7\u00e3o"</strong></li>
                            <li>Copie o <strong>Access Token</strong> (come\u00e7a com <code class="bg-gray-100 dark:bg-dark-200 px-1 rounded">APP_USR-</code>)</li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-900 dark:text-white mb-2">4. Configurar Webhook (IPN)</h4>
                        <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1 ml-4 list-disc">
                            <li>Na aplica\u00e7\u00e3o, v\u00e1 em <strong>"Webhooks"</strong></li>
                            <li>Cole a <strong>URL do Webhook</strong> exibida no card acima</li>
                            <li>Selecione o evento: <strong>Pagamentos</strong></li>
                        </ul>
                    </div>
                    <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-900/30 rounded-lg p-3">
                        <p class="text-sm text-yellow-700 dark:text-yellow-400 flex items-start gap-2">
                            <i class="bi bi-exclamation-triangle-fill mt-0.5"></i>
                            <span>Use sempre as credenciais de <strong>produ\u00e7\u00e3o</strong>, n\u00e3o as de teste.</span>
                        </p>
                    </div>
                </div>
            `
        },
        fastdepix: {
            title: '<i class="bi bi-lightning text-purple-500"></i> Instru\u00e7\u00f5es - FastDePix',
            body: `
                <div class="space-y-4">
                    <div>
                        <h4 class="font-bold text-gray-900 dark:text-white mb-2">1. Criar conta no FastDePix</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Acesse o painel do FastDePix e crie sua conta de integra\u00e7\u00e3o.</p>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-900 dark:text-white mb-2">2. Obter Token de API</h4>
                        <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1 ml-4 list-disc">
                            <li>No painel, acesse <strong>Configura\u00e7\u00f5es &gt; API</strong></li>
                            <li>Gere um novo token de acesso</li>
                            <li>Copie o token gerado</li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-900 dark:text-white mb-2">3. Configurar Webhook</h4>
                        <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1 ml-4 list-disc">
                            <li>No painel, acesse <strong>Configura\u00e7\u00f5es &gt; Webhooks</strong></li>
                            <li>Cole a <strong>URL do Webhook</strong> exibida no card acima</li>
                            <li>Selecione os eventos de pagamento</li>
                        </ul>
                    </div>
                    <div class="bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-900/30 rounded-lg p-3">
                        <p class="text-sm text-purple-700 dark:text-purple-400 flex items-start gap-2">
                            <i class="bi bi-info-circle-fill mt-0.5"></i>
                            <span>O FastDePix processa pagamentos PIX de forma instant\u00e2nea.</span>
                        </p>
                    </div>
                </div>
            `
        }
    };

    function showInstructions(provider) {
        const data = instructions[provider];
        if (!data) return;

        document.getElementById('instructions-title').innerHTML = data.title;
        document.getElementById('instructions-body').innerHTML = data.body;

        const modal = document.getElementById('instructions-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeInstructions() {
        const modal = document.getElementById('instructions-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    document.getElementById('instructions-modal').addEventListener('click', function(e) {
        if (e.target === this) closeInstructions();
    });

    function copyWebhook(id) {
        const input = document.getElementById('webhook-url-' + id);
        navigator.clipboard.writeText(input.value).then(() => {
            const btn = input.nextElementSibling;
            btn.innerHTML = '<i class="bi bi-check text-green-500"></i>';
            setTimeout(() => btn.innerHTML = '<i class="bi bi-clipboard"></i>', 2000);
        });
    }

    function testGateway(id) {
        const resultDiv = document.getElementById('test-result-' + id);
        resultDiv.className = 'mt-3 p-3 rounded-lg text-sm bg-gray-100 dark:bg-dark-200 text-gray-600 dark:text-gray-400';
        resultDiv.innerHTML = '<i class="bi bi-hourglass-split"></i> Testando conex\u00e3o...';
        resultDiv.classList.remove('hidden');

        fetch('/payments/gateways/' + id + '/test')
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    resultDiv.className = 'mt-3 p-3 rounded-lg text-sm bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 border border-green-200 dark:border-green-900/30';
                    resultDiv.innerHTML = '<i class="bi bi-check-circle-fill"></i> Conex\u00e3o bem-sucedida!';
                } else {
                    resultDiv.className = 'mt-3 p-3 rounded-lg text-sm bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 border border-red-200 dark:border-red-900/30';
                    resultDiv.innerHTML = '<i class="bi bi-x-circle-fill"></i> ' + (data.error || 'Erro desconhecido');
                }
            })
            .catch(() => {
                resultDiv.className = 'mt-3 p-3 rounded-lg text-sm bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 border border-red-200 dark:border-red-900/30';
                resultDiv.innerHTML = '<i class="bi bi-x-circle-fill"></i> Erro de conex\u00e3o.';
            });
    }
</script>
@endpush
