@extends('layouts.app')

@section('title', 'Log de Créditos')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-white flex items-center gap-3">
        <i class="bi bi-clock-history text-orange-500"></i>
        Log de Créditos
    </h1>
</div>

<div class="bg-dark-300 rounded-xl border border-dark-200 overflow-hidden">
    @if(count($logs) > 0)
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-dark-200 border-b border-dark-100">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">USUÁRIO</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">AÇÃO</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">VALOR</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">SALDO ANTES</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">SALDO APÓS</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">DESCRIÇÃO</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">DATA</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-dark-200">
                @foreach($logs as $log)
                <tr class="hover:bg-dark-200 transition-colors duration-150">
                    <td class="px-6 py-4">
                        <span class="text-gray-400 text-sm">#{{ $log->id }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <div>
                            <div class="text-white font-medium">{{ $log->target->username ?? 'N/A' }}</div>
                            <div class="text-gray-500 text-xs">ID: {{ $log->target_id }}</div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        @php
                            $amount = (float) $log->amount;
                            $isPositive = $amount >= 0;
                        @endphp
                        <span class="px-3 py-1 bg-{{ $isPositive ? 'green' : 'red' }}-500/10 text-{{ $isPositive ? 'green' : 'red' }}-400 text-xs font-semibold rounded">
                            {{ $isPositive ? 'Crédito' : 'Débito' }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-sm font-semibold {{ $isPositive ? 'text-green-400' : 'text-red-400' }}">
                            {{ $isPositive ? '+' : '' }}{{ number_format($amount, 2) }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-blue-400 font-medium">{{ number_format($log->balance_before, 2) }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-green-400 font-medium">{{ number_format($log->balance_after, 2) }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-gray-300 text-sm">{{ $log->reason ?? '-' }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-gray-400 text-sm">
                            {{ date('d/m/Y H:i', $log->date) }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="text-center py-16">
        <i class="bi bi-clock-history text-gray-600 text-6xl mb-4"></i>
        <h3 class="text-xl font-semibold text-gray-400 mb-2">Nenhum log de crédito encontrado</h3>
        <p class="text-gray-500">O histórico de transações aparecerá aqui</p>
    </div>
    @endif
</div>

<!-- Resumo -->
@if(count($logs) > 0)
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
    @php
        $totalAdded = 0;
        $totalRemoved = 0;
        $totalTransactions = count($logs);
        
        foreach($logs as $log) {
            $amount = (float) $log['amount'];
            if ($amount > 0) {
                $totalAdded += $amount;
            } else {
                $totalRemoved += abs($amount);
            }
        }
    @endphp
    
    <div class="bg-dark-300 rounded-xl border border-dark-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-sm mb-1">Total Adicionado</p>
                <p class="text-2xl font-bold text-green-400">+{{ number_format($totalAdded, 2) }}</p>
            </div>
            <div class="w-12 h-12 bg-green-500/10 rounded-lg flex items-center justify-center">
                <i class="bi bi-arrow-up-circle text-green-400 text-2xl"></i>
            </div>
        </div>
    </div>
    
    <div class="bg-dark-300 rounded-xl border border-dark-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-sm mb-1">Total Removido</p>
                <p class="text-2xl font-bold text-red-400">-{{ number_format($totalRemoved, 2) }}</p>
            </div>
            <div class="w-12 h-12 bg-red-500/10 rounded-lg flex items-center justify-center">
                <i class="bi bi-arrow-down-circle text-red-400 text-2xl"></i>
            </div>
        </div>
    </div>
    
    <div class="bg-dark-300 rounded-xl border border-dark-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-sm mb-1">Total de Transações</p>
                <p class="text-2xl font-bold text-blue-400">{{ $totalTransactions }}</p>
            </div>
            <div class="w-12 h-12 bg-blue-500/10 rounded-lg flex items-center justify-center">
                <i class="bi bi-list-check text-blue-400 text-2xl"></i>
            </div>
        </div>
    </div>
</div>
@endif
@endsection
