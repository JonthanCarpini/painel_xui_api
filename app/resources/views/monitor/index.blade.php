@extends('layouts.app')

@section('title', 'Monitoramento')

@section('content')
<div class="flex items-center justify-between mb-8">
    <h1 class="text-2xl font-bold text-white flex items-center gap-3">
        <i class="bi bi-broadcast text-orange-500"></i>
        Monitoramento Ao Vivo
    </h1>
    <button onclick="location.reload()" class="px-4 py-2 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg hover:shadow-lg transition-all flex items-center gap-2">
        <i class="bi bi-arrow-clockwise"></i>
        Atualizar
    </button>
</div>

@if(count($connections) > 0)
<div class="mb-6 bg-green-500/10 border border-green-500/50 rounded-lg p-4 flex items-center gap-3">
    <i class="bi bi-broadcast-pin text-green-500 text-2xl"></i>
    <span class="text-green-400 font-semibold">{{ count($connections) }} conexões ativas no momento</span>
</div>
@endif

<div class="bg-dark-300 rounded-xl border border-dark-200 overflow-hidden mb-8">
    @if(count($connections) > 0)
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-dark-200 border-b border-dark-100">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Usuário</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Canal / Conteúdo</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Player</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">ISP</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">IP</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Duração</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Ação</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-dark-200">
                @foreach($connections as $conn)
                @php
                    $duration = $conn->duration ?? 0;
                    $hours = floor($duration / 3600);
                    $minutes = floor(($duration % 3600) / 60);
                    $seconds = $duration % 60;
                @endphp
                <tr class="hover:bg-dark-200 transition-colors duration-150">
                    <td class="px-6 py-4">
                        <span class="text-white font-medium">{{ $conn->username ?? 'N/A' }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <div>
                            <div class="text-white text-sm">{{ $conn->stream_name ?? 'Stream ID: ' . ($conn->stream_id ?? 'N/A') }}</div>
                            @if(isset($conn->container))
                                <div class="text-xs text-gray-500">Formato: {{ strtoupper($conn->container) }}</div>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-gray-300 text-sm">{{ $conn->user_agent ?? 'N/A' }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-gray-300 text-sm">{{ $conn->isp ?? 'N/A' }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            @if($conn->geoip_country_code)
                                <img src="https://flagcdn.com/w20/{{ strtolower($conn->geoip_country_code) }}.png" 
                                     alt="{{ $conn->geoip_country_code }}" 
                                     class="w-5 h-auto rounded"
                                     title="{{ $conn->geoip_country_code }}">
                            @endif
                            <code class="px-3 py-1 bg-dark-100 text-gray-300 rounded font-mono text-sm">{{ $conn->ip ?? 'N/A' }}</code>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-3 py-1 bg-blue-500/10 text-blue-400 text-sm font-semibold rounded">
                            @if($hours > 0)
                                {{ $hours }}h {{ $minutes }}m
                            @else
                                {{ $minutes }}m {{ $seconds }}s
                            @endif
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <button onclick="killConnection({{ $conn->activity_id }})" class="px-3 py-1.5 bg-red-500/10 text-red-400 rounded hover:bg-red-500/20 transition-colors flex items-center gap-2">
                            <i class="bi bi-x-circle"></i>
                            Derrubar
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="text-center py-16">
        <i class="bi bi-broadcast text-gray-600 text-6xl mb-4"></i>
        <h3 class="text-xl font-semibold text-gray-400 mb-2">Nenhuma conexão ativa</h3>
        <p class="text-gray-500">Não há clientes assistindo no momento</p>
    </div>
    @endif
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="bg-dark-300 rounded-xl p-6 border border-dark-200 text-center">
        <i class="bi bi-people text-orange-500 text-4xl mb-3"></i>
        <h3 class="text-3xl font-bold text-white mb-1">{{ count($connections) }}</h3>
        <p class="text-gray-400 text-sm font-medium">Conexões Ativas</p>
    </div>
    <div class="bg-dark-300 rounded-xl p-6 border border-dark-200 text-center">
        <i class="bi bi-clock-history text-green-500 text-4xl mb-3"></i>
        <h3 class="text-3xl font-bold text-white mb-1">
            @php
                $avgDuration = $connections->count() > 0 ? $connections->sum('duration') / $connections->count() : 0;
                echo gmdate("H:i", $avgDuration);
            @endphp
        </h3>
        <p class="text-gray-400 text-sm font-medium">Tempo Médio</p>
    </div>
    <div class="bg-dark-300 rounded-xl p-6 border border-dark-200 text-center">
        <i class="bi bi-activity text-blue-500 text-4xl mb-3"></i>
        <h3 class="text-3xl font-bold text-white mb-1">{{ $connections->pluck('user_id')->unique()->count() }}</h3>
        <p class="text-gray-400 text-sm font-medium">Usuários Únicos</p>
    </div>
</div>

@push('scripts')
<script>
    // Auto-refresh a cada 30 segundos
    setTimeout(() => {
        location.reload();
    }, 30000);

    // Função para derrubar conexão
    function killConnection(activityId) {
        if (!confirm('Tem certeza que deseja derrubar esta conexão?')) {
            return;
        }

        fetch(`/monitor/kill/${activityId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Feedback visual
                alert(data.message);
                // Recarregar página para atualizar lista
                location.reload();
            } else {
                alert('Erro: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao derrubar conexão. Tente novamente.');
        });
    }
</script>
@endpush
@endsection
