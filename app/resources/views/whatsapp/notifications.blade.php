@extends('layouts.app')

@section('title', 'WhatsApp - Notificações')

@section('content')
<div class="w-full">
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-6 md:mb-8 gap-4">
        <div>
            <h1 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <i class="bi bi-bell text-green-500"></i>
                Painel de Notifica&ccedil;&otilde;es
            </h1>
            <p class="text-sm md:text-base text-gray-500 dark:text-gray-400 mt-1">Acompanhe o status dos envios de hoje ({{ \Carbon\Carbon::parse($today)->format('d/m/Y') }}).</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="px-3 py-1.5 rounded-full text-xs font-bold {{ $setting->notifications_enabled ? 'bg-green-100 dark:bg-green-500/10 text-green-600 dark:text-green-500 border border-green-200 dark:border-green-500/30' : 'bg-gray-100 dark:bg-dark-200 text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-dark-100' }}">
                <i class="bi {{ $setting->notifications_enabled ? 'bi-bell-fill' : 'bi-bell-slash' }}"></i>
                {{ $setting->notifications_enabled ? 'Ativo' : 'Desativado' }}
            </span>
            <span class="text-xs text-gray-400 dark:text-gray-500">
                In&iacute;cio: {{ $setting->send_start_time ?? '09:00' }} | Intervalo: {{ $setting->send_interval_seconds ?? 30 }}s
            </span>
        </div>
    </div>

    {{-- Cards resumo --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        @foreach($groups as $type => $group)
        @php
            $total = count($group['clients']);
            $sent = collect($group['clients'])->where('status', 'sent')->count();
            $failed = collect($group['clients'])->where('status', 'failed')->count();
            $pending = collect($group['clients'])->where('status', 'pending')->count();
            $noPhone = collect($group['clients'])->where('status', 'no_phone')->count();
        @endphp
        <div class="bg-white dark:bg-dark-300 border border-gray-200 dark:border-dark-200 rounded-xl p-5 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="bi {{ $group['icon'] }} text-{{ $group['color'] }}-500"></i>
                    {{ $group['label'] }}
                </h3>
                <span class="text-2xl font-bold text-{{ $group['color'] }}-500">{{ $total }}</span>
            </div>
            <div class="flex gap-3 text-xs">
                @if($sent > 0)
                <span class="text-green-600 dark:text-green-400"><i class="bi bi-check-circle-fill"></i> {{ $sent }} enviado{{ $sent > 1 ? 's' : '' }}</span>
                @endif
                @if($pending > 0)
                <span class="text-yellow-600 dark:text-yellow-400"><i class="bi bi-clock"></i> {{ $pending }} aguardando</span>
                @endif
                @if($failed > 0)
                <span class="text-red-600 dark:text-red-400"><i class="bi bi-x-circle"></i> {{ $failed }} falha{{ $failed > 1 ? 's' : '' }}</span>
                @endif
                @if($noPhone > 0)
                <span class="text-gray-400"><i class="bi bi-phone"></i> {{ $noPhone }} sem tel.</span>
                @endif
                @if($total === 0)
                <span class="text-gray-400">Nenhum cliente</span>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    {{-- Abas por período --}}
    @php
        $defaultTab = 'today';
        foreach ($groups as $key => $g) {
            if (count($g['clients']) > 0) { $defaultTab = $key; break; }
        }
    @endphp
    <div x-data="{ activeTab: '{{ $defaultTab }}' }">
        {{-- Navegação das abas --}}
        <div class="flex bg-white dark:bg-dark-300 border border-gray-200 dark:border-dark-200 rounded-xl p-1 mb-6 shadow-sm">
            @foreach($groups as $type => $group)
            @php $total = count($group['clients']); @endphp
            <button @click="activeTab = '{{ $type }}'" :class="activeTab === '{{ $type }}' ? 'bg-{{ $group['color'] }}-500 text-white shadow-md' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-dark-200'" class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200">
                <i class="bi {{ $group['icon'] }}"></i>
                <span>{{ $group['label'] }}</span>
                <span class="px-1.5 py-0.5 rounded-full text-xs font-bold" :class="activeTab === '{{ $type }}' ? 'bg-white/20' : 'bg-gray-200 dark:bg-dark-100'">{{ $total }}</span>
            </button>
            @endforeach
        </div>

        {{-- Conteúdo das abas --}}
        @foreach($groups as $type => $group)
        <div x-show="activeTab === '{{ $type }}'" x-transition>
            @if(count($group['clients']) > 0)
            <div class="bg-white dark:bg-dark-300 border border-gray-200 dark:border-dark-200 rounded-xl overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-dark-200 bg-gray-50 dark:bg-dark-200/50">
                                <th class="text-left px-6 py-3 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Cliente</th>
                                <th class="text-left px-6 py-3 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Telefone</th>
                                <th class="text-left px-6 py-3 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Vencimento</th>
                                <th class="text-left px-6 py-3 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                <th class="text-left px-6 py-3 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Data/Hora</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-dark-200">
                            @foreach($group['clients'] as $client)
                            <tr class="hover:bg-gray-50 dark:hover:bg-dark-200/50 transition-colors">
                                <td class="px-6 py-3">
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $client['username'] }}</span>
                                </td>
                                <td class="px-6 py-3">
                                    @if($client['phone'])
                                    <div class="flex items-center gap-2">
                                        <span class="text-gray-600 dark:text-gray-400 font-mono text-xs">{{ $client['phone'] }}</span>
                                        <a href="https://wa.me/{{ $client['phone'] }}" target="_blank" class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-green-100 dark:bg-green-500/20 text-green-600 dark:text-green-400 hover:bg-green-200 dark:hover:bg-green-500/30 transition-colors" title="Abrir no WhatsApp">
                                            <i class="bi bi-whatsapp text-xs"></i>
                                        </a>
                                    </div>
                                    @else
                                    <span class="text-gray-400 text-xs italic">Não cadastrado</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3">
                                    <span class="text-gray-600 dark:text-gray-400 text-xs">{{ $client['exp_date'] }}</span>
                                </td>
                                <td class="px-6 py-3">
                                    @if($client['status'] === 'sent')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-green-100 dark:bg-green-500/10 text-green-600 dark:text-green-400 border border-green-200 dark:border-green-500/30">
                                        <i class="bi bi-check-circle-fill"></i> Enviado
                                    </span>
                                    @elseif($client['status'] === 'pending')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-yellow-100 dark:bg-yellow-500/10 text-yellow-600 dark:text-yellow-400 border border-yellow-200 dark:border-yellow-500/30">
                                        <i class="bi bi-clock"></i> Aguardando
                                    </span>
                                    @elseif($client['status'] === 'failed')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 dark:bg-red-500/10 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-500/30">
                                        <i class="bi bi-x-circle-fill"></i> Falhou
                                    </span>
                                    @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-gray-100 dark:bg-dark-200 text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-dark-100">
                                        <i class="bi bi-phone"></i> Sem Telefone
                                    </span>
                                    @endif
                                </td>
                                <td class="px-6 py-3">
                                    @if($client['sent_at'])
                                    <span class="text-gray-600 dark:text-gray-400 text-xs">
                                        @if($client['status'] === 'pending')
                                        <i class="bi bi-clock text-yellow-500"></i> Previsão: {{ $client['sent_at'] instanceof \Carbon\Carbon ? $client['sent_at']->format('d/m/Y \à\s H:i:s') : $client['sent_at'] }}
                                        @else
                                        {{ $client['sent_at'] instanceof \Carbon\Carbon ? $client['sent_at']->format('d/m/Y \à\s H:i:s') : $client['sent_at'] }}
                                        @endif
                                    </span>
                                    @else
                                    <span class="text-gray-400 text-xs">—</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @else
            <div class="bg-white dark:bg-dark-300 border border-gray-200 dark:border-dark-200 rounded-xl p-12 text-center shadow-sm">
                <div class="w-16 h-16 bg-gray-100 dark:bg-dark-200 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="bi bi-check-circle text-gray-400 text-3xl"></i>
                </div>
                <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Nenhum cliente</h4>
                <p class="text-gray-500 dark:text-gray-400 text-sm">Não há clientes com vencimento neste período.</p>
            </div>
            @endif
        </div>
        @endforeach
    </div>
</div>
@endsection
