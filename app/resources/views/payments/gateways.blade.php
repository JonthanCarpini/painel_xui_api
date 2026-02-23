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
    <div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 shadow-sm dark:shadow-none overflow-hidden">
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

            @if($gateway)
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold {{ $gateway->active ? 'bg-green-100 dark:bg-green-500/20 text-green-700 dark:text-green-400' : 'bg-gray-100 dark:bg-gray-500/20 text-gray-600 dark:text-gray-400' }}">
                    <span class="w-2 h-2 rounded-full {{ $gateway->active ? 'bg-green-500' : 'bg-gray-400' }}"></span>
                    {{ $gateway->active ? 'Ativo' : 'Inativo' }}
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 dark:bg-gray-500/20 text-gray-600 dark:text-gray-400">
                    <span class="w-2 h-2 rounded-full bg-gray-400"></span>
                    N&atilde;o configurado
                </span>
            @endif
        </div>

        <!-- Body -->
        <div class="p-5">
            @if($gateway)
                <!-- Gateway já configurado -->
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
                            <input type="text" readonly value="{{ route('webhook.asaas', $gateway->webhook_secret) }}"
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

                <!-- Test Result -->
                <div id="test-result-{{ $gateway->id }}" class="hidden mt-3 p-3 rounded-lg text-sm"></div>

            @else
                <!-- Gateway não configurado -->
                <form action="{{ route('payments.gateways.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="provider" value="{{ $providerKey }}">

                    @if($providerKey === 'asaas')
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-1">Access Token</label>
                            <input type="password" name="access_token" required
                                class="w-full px-3 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white text-sm focus:border-orange-500 focus:outline-none transition-colors font-mono"
                                placeholder="$aact_prod_...">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Obtenha em <a href="https://www.asaas.com/apiKeys" target="_blank" class="text-orange-500 hover:underline">asaas.com/apiKeys</a></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-1">Address Key (Chave PIX)</label>
                            <input type="text" name="address_key" required
                                class="w-full px-3 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white text-sm focus:border-orange-500 focus:outline-none transition-colors font-mono"
                                placeholder="uuid-da-chave-pix">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">UUID da chave PIX cadastrada no Asaas.</p>
                        </div>
                    @elseif($providerKey === 'mercadopago')
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-1">Access Token</label>
                            <input type="password" name="access_token" required
                                class="w-full px-3 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white text-sm focus:border-orange-500 focus:outline-none transition-colors font-mono"
                                placeholder="APP_USR-...">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Obtenha em <a href="https://www.mercadopago.com.br/developers/panel/app" target="_blank" class="text-orange-500 hover:underline">mercadopago.com.br/developers</a></p>
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
                <li>3. Ative o gateway para come&ccedil;ar a receber pagamentos PIX.</li>
                <li>4. Quando um cliente pagar, o sistema renovar&aacute; automaticamente a linha.</li>
            </ul>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function copyWebhook(id) {
        const input = document.getElementById('webhook-url-' + id);
        navigator.clipboard.writeText(input.value).then(() => {
            const btn = input.nextElementSibling;
            btn.innerHTML = '<i class="bi bi-check"></i>';
            setTimeout(() => btn.innerHTML = '<i class="bi bi-clipboard"></i>', 2000);
        });
    }

    function testGateway(id) {
        const resultDiv = document.getElementById('test-result-' + id);
        resultDiv.className = 'mt-3 p-3 rounded-lg text-sm bg-gray-100 dark:bg-dark-200 text-gray-600 dark:text-gray-400';
        resultDiv.innerHTML = '<i class="bi bi-hourglass-split"></i> Testando conex&atilde;o...';
        resultDiv.classList.remove('hidden');

        fetch('/payments/gateways/' + id + '/test')
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    resultDiv.className = 'mt-3 p-3 rounded-lg text-sm bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 border border-green-200 dark:border-green-900/30';
                    resultDiv.innerHTML = '<i class="bi bi-check-circle-fill"></i> Conex&atilde;o bem-sucedida!';
                } else {
                    resultDiv.className = 'mt-3 p-3 rounded-lg text-sm bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 border border-red-200 dark:border-red-900/30';
                    resultDiv.innerHTML = '<i class="bi bi-x-circle-fill"></i> ' + (data.error || 'Erro desconhecido');
                }
            })
            .catch(() => {
                resultDiv.className = 'mt-3 p-3 rounded-lg text-sm bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 border border-red-200 dark:border-red-900/30';
                resultDiv.innerHTML = '<i class="bi bi-x-circle-fill"></i> Erro de conex&atilde;o.';
            });
    }
</script>
@endpush
