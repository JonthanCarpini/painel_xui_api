@extends('layouts.app')

@section('title', 'Gestão de Pedidos VOD')

@section('content')
<div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-6 md:mb-8 gap-4">
    <div>
        <h1 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
            <i class="bi bi-film text-orange-500"></i>
            Gestão de Pedidos VOD
        </h1>
        <p class="text-sm md:text-base text-gray-500 dark:text-gray-400 mt-1">Gerencie solicitações de filmes e séries dos revendedores.</p>
    </div>
</div>

<!-- Stats -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <a href="{{ route('settings.admin.vod-requests.index', ['status' => 'pending']) }}" class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 p-4 hover:border-yellow-300 dark:hover:border-yellow-500/50 transition-colors {{ $status === 'pending' ? 'ring-2 ring-yellow-500' : '' }}">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-yellow-100 dark:bg-yellow-500/20 flex items-center justify-center">
                <i class="bi bi-clock text-yellow-600 dark:text-yellow-400"></i>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['pending'] }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Pendentes</p>
            </div>
        </div>
    </a>
    <a href="{{ route('settings.admin.vod-requests.index', ['status' => 'completed']) }}" class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 p-4 hover:border-green-300 dark:hover:border-green-500/50 transition-colors {{ $status === 'completed' ? 'ring-2 ring-green-500' : '' }}">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-green-100 dark:bg-green-500/20 flex items-center justify-center">
                <i class="bi bi-check-circle text-green-600 dark:text-green-400"></i>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['completed'] }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Concluídos</p>
            </div>
        </div>
    </a>
    <a href="{{ route('settings.admin.vod-requests.index', ['status' => 'rejected']) }}" class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 p-4 hover:border-red-300 dark:hover:border-red-500/50 transition-colors {{ $status === 'rejected' ? 'ring-2 ring-red-500' : '' }}">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-red-100 dark:bg-red-500/20 flex items-center justify-center">
                <i class="bi bi-x-circle text-red-600 dark:text-red-400"></i>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['rejected'] }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Recusados</p>
            </div>
        </div>
    </a>
    <a href="{{ route('settings.admin.vod-requests.index', ['status' => 'all']) }}" class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 p-4 hover:border-orange-300 dark:hover:border-orange-500/50 transition-colors {{ $status === 'all' ? 'ring-2 ring-orange-500' : '' }}">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-orange-100 dark:bg-orange-500/20 flex items-center justify-center">
                <i class="bi bi-collection text-orange-600 dark:text-orange-400"></i>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total'] }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Total</p>
            </div>
        </div>
    </a>
</div>

@if(session('success'))
<div class="bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/30 rounded-xl p-4 mb-6">
    <p class="text-green-700 dark:text-green-400 text-sm flex items-center gap-2">
        <i class="bi bi-check-circle"></i> {{ session('success') }}
    </p>
</div>
@endif

<!-- Filtros -->
<div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 p-4 mb-6 shadow-sm dark:shadow-none">
    <form method="GET" action="{{ route('settings.admin.vod-requests.index') }}" class="flex flex-col sm:flex-row gap-3">
        <input type="hidden" name="status" value="{{ $status }}">
        <div class="flex gap-2 shrink-0">
            <select name="type" class="px-3 py-2 bg-gray-100 dark:bg-dark-200 border border-gray-200 dark:border-dark-100 rounded-lg text-sm text-gray-900 dark:text-white">
                <option value="">Todos os tipos</option>
                <option value="movie" {{ $type === 'movie' ? 'selected' : '' }}>Filmes</option>
                <option value="series" {{ $type === 'series' ? 'selected' : '' }}>Séries</option>
            </select>
        </div>
        <input type="text" name="search" value="{{ $search }}" placeholder="Buscar por título..." class="flex-1 px-4 py-2 bg-gray-100 dark:bg-dark-200 border border-gray-200 dark:border-dark-100 rounded-lg text-sm text-gray-900 dark:text-white placeholder-gray-400">
        <button type="submit" class="px-6 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-lg text-sm font-medium transition-colors shrink-0">
            <i class="bi bi-funnel"></i> Filtrar
        </button>
    </form>
</div>

<!-- Lista de Pedidos -->
<div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 shadow-sm dark:shadow-none overflow-hidden">
    @if($requests->isEmpty())
    <div class="text-center py-12 text-gray-500 dark:text-gray-400">
        <i class="bi bi-inbox text-4xl mb-3 block"></i>
        <p>Nenhum pedido encontrado.</p>
    </div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-dark-200">
                <tr>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Título</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tipo</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Solicitante</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pedidos</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Data</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-dark-200">
                @foreach($requests as $req)
                @php
                    $reqCount = \App\Models\VodRequest::where('tmdb_id', $req->tmdb_id)->where('type', $req->type)->count();
                @endphp
                <tr class="hover:bg-gray-50 dark:hover:bg-dark-200 transition-colors">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-14 rounded-lg overflow-hidden bg-gray-200 dark:bg-dark-100 shrink-0">
                                <img src="{{ $req->poster_url }}" alt="{{ $req->title }}" class="w-full h-full object-cover" loading="lazy">
                            </div>
                            <div class="min-w-0">
                                <h4 class="font-bold text-gray-900 dark:text-white truncate max-w-[200px]">{{ $req->title }}</h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $req->release_date ? substr($req->release_date, 0, 4) : '' }}
                                    @if($req->vote_average > 0)
                                        &middot; <span class="text-yellow-500"><i class="bi bi-star-fill"></i> {{ number_format($req->vote_average, 1) }}</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-4">
                        <span class="text-xs font-medium px-2 py-1 rounded-full {{ $req->type === 'movie' ? 'bg-purple-100 dark:bg-purple-500/20 text-purple-700 dark:text-purple-400' : 'bg-blue-100 dark:bg-blue-500/20 text-blue-700 dark:text-blue-400' }}">
                            {{ $req->type === 'movie' ? 'Filme' : 'Série' }}
                        </span>
                    </td>
                    <td class="px-4 py-4 text-gray-700 dark:text-gray-300">
                        {{ $req->user->username ?? 'N/A' }}
                    </td>
                    <td class="px-4 py-4 text-center">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full text-xs font-bold {{ $reqCount > 1 ? 'bg-orange-100 dark:bg-orange-500/20 text-orange-700 dark:text-orange-400' : 'bg-gray-100 dark:bg-dark-100 text-gray-600 dark:text-gray-400' }}">
                            {{ $reqCount }}
                        </span>
                    </td>
                    <td class="px-4 py-4 text-gray-500 dark:text-gray-400 text-xs">
                        {{ $req->created_at->format('d/m/Y H:i') }}
                    </td>
                    <td class="px-4 py-4 text-center">
                        @if($req->status === 'pending')
                            <span class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1 rounded-full bg-yellow-100 dark:bg-yellow-500/20 text-yellow-700 dark:text-yellow-400">
                                <i class="bi bi-clock"></i> Pendente
                            </span>
                        @elseif($req->status === 'completed')
                            <span class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1 rounded-full bg-green-100 dark:bg-green-500/20 text-green-700 dark:text-green-400">
                                <i class="bi bi-check-circle"></i> Concluído
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1 rounded-full bg-red-100 dark:bg-red-500/20 text-red-700 dark:text-red-400">
                                <i class="bi bi-x-circle"></i> Recusado
                            </span>
                        @endif
                    </td>
                    <td class="px-4 py-4 text-center">
                        <a href="{{ route('settings.admin.vod-requests.show', $req->id) }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-orange-500 hover:bg-orange-600 text-white rounded-lg text-xs font-medium transition-colors">
                            <i class="bi bi-eye"></i> Ver
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($requests->hasPages())
    <div class="px-6 py-4 border-t border-gray-200 dark:border-dark-200">
        {{ $requests->links() }}
    </div>
    @endif
    @endif
</div>
@endsection
