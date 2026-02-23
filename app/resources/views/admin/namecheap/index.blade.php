@extends('layouts.app')

@section('title', 'Namecheap')

@section('content')
<div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-6 md:mb-8 gap-4">
    <div>
        <h1 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
            <i class="bi bi-globe2 text-orange-500"></i>
            Namecheap
        </h1>
        <p class="text-sm md:text-base text-gray-500 dark:text-gray-400 mt-1">Informa&ccedil;&otilde;es da sua conta Namecheap e dom&iacute;nios registrados.</p>
    </div>
    <a href="{{ route('settings.index') }}" class="px-4 py-2 bg-gray-200 dark:bg-dark-200 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-dark-100 transition-colors text-sm font-medium flex items-center gap-2">
        <i class="bi bi-gear"></i> Configura&ccedil;&otilde;es
    </a>
</div>

@if(!$configured)
<div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-900/30 rounded-xl p-6 text-center">
    <i class="bi bi-exclamation-triangle text-yellow-500 text-4xl mb-3"></i>
    <h3 class="font-bold text-yellow-700 dark:text-yellow-400 mb-2">Namecheap n&atilde;o configurado</h3>
    <p class="text-sm text-yellow-600 dark:text-yellow-300 mb-4">Configure as credenciais da API Namecheap em <strong>Configura&ccedil;&otilde;es &gt; DNS</strong> para usar este m&oacute;dulo.</p>
    <a href="{{ route('settings.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-lg transition-colors text-sm font-medium">
        <i class="bi bi-gear"></i> Ir para Configura&ccedil;&otilde;es
    </a>
</div>
@else

@if($error)
<div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-900/30 rounded-xl flex items-center gap-3">
    <i class="bi bi-exclamation-circle-fill text-red-500 text-xl"></i>
    <span class="text-red-700 dark:text-red-400 font-medium">{{ $error }}</span>
</div>
@endif

<!-- Cards de Info -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <!-- Conta -->
    <div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 p-5 shadow-sm dark:shadow-none">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-500/20 flex items-center justify-center">
                <i class="bi bi-person-circle text-blue-600 dark:text-blue-400 text-lg"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Conta</p>
                <p class="font-bold text-gray-900 dark:text-white">{{ $apiUser }}</p>
            </div>
        </div>
        <p class="text-xs text-gray-400 dark:text-gray-500">
            {{ $sandbox ? 'Modo Sandbox' : 'Modo Produ\u00e7\u00e3o' }}
        </p>
    </div>

    @if($balance)
    <!-- Saldo Disponível -->
    <div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 p-5 shadow-sm dark:shadow-none">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-lg bg-green-100 dark:bg-green-500/20 flex items-center justify-center">
                <i class="bi bi-wallet2 text-green-600 dark:text-green-400 text-lg"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Saldo Dispon&iacute;vel</p>
                <p class="font-bold text-green-600 dark:text-green-400 text-lg">${{ number_format((float)$balance['available'], 2) }}</p>
            </div>
        </div>
        <p class="text-xs text-gray-400 dark:text-gray-500">{{ $balance['currency'] }}</p>
    </div>

    <!-- Saldo da Conta -->
    <div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 p-5 shadow-sm dark:shadow-none">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-lg bg-purple-100 dark:bg-purple-500/20 flex items-center justify-center">
                <i class="bi bi-bank text-purple-600 dark:text-purple-400 text-lg"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Saldo em Conta</p>
                <p class="font-bold text-purple-600 dark:text-purple-400 text-lg">${{ number_format((float)$balance['account'], 2) }}</p>
            </div>
        </div>
        <p class="text-xs text-gray-400 dark:text-gray-500">Total na conta</p>
    </div>

    <!-- Total de Domínios -->
    <div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 p-5 shadow-sm dark:shadow-none">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-lg bg-orange-100 dark:bg-orange-500/20 flex items-center justify-center">
                <i class="bi bi-globe text-orange-600 dark:text-orange-400 text-lg"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Dom&iacute;nios</p>
                <p class="font-bold text-orange-600 dark:text-orange-400 text-lg">{{ count($domains) }}</p>
            </div>
        </div>
        <p class="text-xs text-gray-400 dark:text-gray-500">Registrados na conta</p>
    </div>
    @endif
</div>

<!-- Lista de Domínios -->
<div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 shadow-sm dark:shadow-none overflow-hidden">
    <div class="p-5 border-b border-gray-200 dark:border-dark-200 flex items-center justify-between">
        <h3 class="font-bold text-gray-900 dark:text-white flex items-center gap-2">
            <i class="bi bi-list-ul text-orange-500"></i>
            Dom&iacute;nios Registrados
        </h3>
        <span class="text-xs text-gray-500 dark:text-gray-400">{{ count($domains) }} dom&iacute;nio(s)</span>
    </div>

    @if(count($domains) > 0)
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-dark-200">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Dom&iacute;nio</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Criado</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Expira</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Status</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Auto-Renew</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">WhoisGuard</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-dark-200">
                @foreach($domains as $d)
                <tr class="hover:bg-gray-50 dark:hover:bg-dark-200 transition-colors">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <i class="bi bi-globe text-orange-500"></i>
                            <span class="font-bold text-gray-900 dark:text-white">{{ $d['name'] }}</span>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $d['created'] }}</td>
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                        <span class="{{ $d['is_expired'] ? 'text-red-500 font-bold' : '' }}">{{ $d['expires'] }}</span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($d['is_expired'])
                            <span class="px-2 py-1 bg-red-100 dark:bg-red-500/20 text-red-700 dark:text-red-400 rounded-full text-xs font-semibold">Expirado</span>
                        @elseif($d['is_locked'])
                            <span class="px-2 py-1 bg-yellow-100 dark:bg-yellow-500/20 text-yellow-700 dark:text-yellow-400 rounded-full text-xs font-semibold">Bloqueado</span>
                        @else
                            <span class="px-2 py-1 bg-green-100 dark:bg-green-500/20 text-green-700 dark:text-green-400 rounded-full text-xs font-semibold">Ativo</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($d['auto_renew'])
                            <i class="bi bi-check-circle-fill text-green-500"></i>
                        @else
                            <i class="bi bi-x-circle text-gray-400"></i>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($d['whois_guard'] === 'ENABLED')
                            <span class="px-2 py-1 bg-blue-100 dark:bg-blue-500/20 text-blue-700 dark:text-blue-400 rounded-full text-xs font-semibold">Ativo</span>
                        @elseif($d['whois_guard'] === 'NOTPRESENT')
                            <span class="text-gray-400 text-xs">N/A</span>
                        @else
                            <span class="text-yellow-500 text-xs">{{ $d['whois_guard'] }}</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="p-12 text-center">
        <i class="bi bi-globe text-gray-300 dark:text-gray-600 text-5xl mb-4"></i>
        <p class="text-gray-500 dark:text-gray-400 font-medium mb-2">Nenhum dom&iacute;nio encontrado</p>
        <p class="text-sm text-gray-400 dark:text-gray-500">Nenhum dom&iacute;nio registrado nesta conta Namecheap.</p>
    </div>
    @endif
</div>

@endif
@endsection
