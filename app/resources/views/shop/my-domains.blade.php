@extends('layouts.app')

@section('title', 'Meus Domínios')

@section('content')
<div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-6 md:mb-8 gap-4">
    <div>
        <h1 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
            <i class="bi bi-collection text-orange-500"></i>
            Meus Dom&iacute;nios
        </h1>
        <p class="text-sm md:text-base text-gray-500 dark:text-gray-400 mt-1">Gerencie seus dom&iacute;nios e selecione qual usar nas listas.</p>
    </div>
    <a href="{{ route('shop.dns') }}" class="px-4 py-2 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg hover:shadow-lg hover:shadow-orange-500/20 transition-all text-sm font-medium flex items-center gap-2">
        <i class="bi bi-cart-plus"></i> Comprar Dom&iacute;nio
    </a>
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

<!-- Pedidos Pendentes -->
@if($pendingOrders->count() > 0)
<div class="mb-6 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-900/30 rounded-xl p-5">
    <h3 class="font-bold text-yellow-700 dark:text-yellow-400 mb-3 flex items-center gap-2">
        <i class="bi bi-hourglass-split"></i>
        Pedidos Aguardando Pagamento
    </h3>
    <div class="space-y-2">
        @foreach($pendingOrders as $order)
        <div class="flex items-center justify-between bg-white dark:bg-dark-300 rounded-lg p-3 border border-yellow-200 dark:border-yellow-900/30">
            <div>
                <p class="font-bold text-gray-900 dark:text-white">{{ $order->domain }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Ref: {{ $order->order_ref }} &mdash; R$ {{ number_format($order->price_brl, 2, ',', '.') }}</p>
            </div>
            <span class="px-3 py-1 bg-yellow-100 dark:bg-yellow-500/20 text-yellow-700 dark:text-yellow-400 rounded-full text-xs font-semibold">
                Aguardando PIX
            </span>
        </div>
        @endforeach
    </div>
</div>
@endif

<!-- Adicionar Domínio Particular -->
<div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 p-6 shadow-sm dark:shadow-none mb-6">
    <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
        <i class="bi bi-plus-circle text-orange-500"></i>
        Adicionar Dom&iacute;nio Particular
    </h2>
    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Adicione um dom&iacute;nio que voc&ecirc; j&aacute; possui para usar nas listas dos seus clientes.</p>

    <form action="{{ route('shop.my-domains.add-custom') }}" method="POST" class="flex flex-col sm:flex-row gap-3">
        @csrf
        <div class="flex-1">
            <input type="text" name="domain" placeholder="Ex: meudominio.com" required
                class="w-full px-4 py-2.5 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white focus:border-orange-500 focus:outline-none transition-colors text-sm font-mono"
                pattern="[a-zA-Z0-9\-]+\.[a-zA-Z]{2,}">
        </div>
        <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg hover:shadow-lg hover:shadow-orange-500/20 transition-all font-medium text-sm flex items-center gap-2">
            <i class="bi bi-plus-circle"></i> Adicionar
        </button>
    </form>
</div>

<!-- Lista de Domínios -->
<div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 shadow-sm dark:shadow-none overflow-hidden">
    <div class="p-5 border-b border-gray-200 dark:border-dark-200 flex items-center justify-between">
        <h3 class="font-bold text-gray-900 dark:text-white flex items-center gap-2">
            <i class="bi bi-globe text-orange-500"></i>
            Seus Dom&iacute;nios
        </h3>
        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $domains->count() }} dom&iacute;nio(s)</span>
    </div>

    @if($domains->count() > 0)
    <div class="divide-y divide-gray-200 dark:divide-dark-200">
        @foreach($domains as $domain)
        <div class="flex items-center justify-between p-4 hover:bg-gray-50 dark:hover:bg-dark-200 transition-colors">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg {{ $domain->is_active ? 'bg-green-100 dark:bg-green-500/20' : 'bg-gray-100 dark:bg-gray-500/20' }} flex items-center justify-center">
                    @if($domain->is_active)
                        <i class="bi bi-check-circle-fill text-green-600 dark:text-green-400 text-lg"></i>
                    @else
                        <i class="bi bi-globe text-gray-500 dark:text-gray-400 text-lg"></i>
                    @endif
                </div>
                <div>
                    <p class="font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        {{ $domain->domain }}
                        @if($domain->is_active)
                            <span class="px-2 py-0.5 bg-green-100 dark:bg-green-500/20 text-green-700 dark:text-green-400 rounded-full text-xs font-semibold">Ativo</span>
                        @endif
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        @if($domain->type === 'purchased')
                            <span class="text-blue-500"><i class="bi bi-cart-check"></i> Comprado</span>
                            @if($domain->paid_amount_brl)
                                &mdash; R$ {{ number_format($domain->paid_amount_brl, 2, ',', '.') }}
                            @endif
                            @if($domain->expires_at)
                                &mdash; Expira: {{ $domain->expires_at->format('d/m/Y') }}
                            @endif
                        @else
                            <span class="text-purple-500"><i class="bi bi-person"></i> Particular</span>
                        @endif
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                @if(!$domain->is_active)
                    <form action="{{ route('shop.my-domains.activate', $domain->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 bg-green-500 hover:bg-green-600 text-white rounded-lg text-xs font-medium transition-colors" title="Ativar para as listas">
                            <i class="bi bi-check-circle"></i> Ativar
                        </button>
                    </form>
                @else
                    <span class="px-3 py-1.5 bg-green-100 dark:bg-green-500/20 text-green-700 dark:text-green-400 rounded-lg text-xs font-medium">
                        <i class="bi bi-broadcast"></i> Em uso
                    </span>
                @endif

                @if($domain->type === 'custom')
                    <form action="{{ route('shop.my-domains.remove', $domain->id) }}" method="POST" onsubmit="return confirm('Remover este dom\u00ednio?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-3 py-1.5 bg-red-500 hover:bg-red-600 text-white rounded-lg text-xs font-medium transition-colors">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="p-12 text-center">
        <i class="bi bi-globe text-gray-300 dark:text-gray-600 text-5xl mb-4"></i>
        <p class="text-gray-500 dark:text-gray-400 font-medium mb-2">Nenhum dom&iacute;nio cadastrado</p>
        <p class="text-sm text-gray-400 dark:text-gray-500">Compre um dom&iacute;nio na loja ou adicione um dom&iacute;nio particular acima.</p>
    </div>
    @endif
</div>

<!-- Info -->
<div class="mt-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-900/30 rounded-xl p-5">
    <div class="flex items-start gap-3">
        <i class="bi bi-info-circle-fill text-blue-500 text-xl mt-0.5"></i>
        <div>
            <h4 class="font-bold text-blue-700 dark:text-blue-400 mb-1">Sobre os dom&iacute;nios</h4>
            <ul class="text-sm text-blue-600 dark:text-blue-300 space-y-1">
                <li>- O dom&iacute;nio <strong>ativo</strong> ser&aacute; usado como DNS nas listas dos seus clientes.</li>
                <li>- Apenas <strong>um dom&iacute;nio</strong> pode estar ativo por vez.</li>
                <li>- Dom&iacute;nios <strong>comprados</strong> n&atilde;o podem ser removidos.</li>
                <li>- Dom&iacute;nios <strong>particulares</strong> s&atilde;o dom&iacute;nios que voc&ecirc; j&aacute; possui e quer usar no painel.</li>
            </ul>
        </div>
    </div>
</div>
@endsection
