@extends('layouts.app')

@section('title', 'Gestão de Canais')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row items-center justify-between gap-4">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
            <i class="bi bi-collection-play text-blue-600"></i>
            Gestão de Canais
        </h1>
        
        <form action="{{ route('settings.admin.channels.index') }}" method="GET" class="w-full md:w-auto">
            <div class="relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar canais..." class="w-full md:w-64 pl-10 pr-4 py-2 rounded-lg border border-gray-300 dark:border-dark-100 dark:bg-dark-200 focus:ring-2 focus:ring-blue-500">
                <i class="bi bi-search absolute left-3 top-2.5 text-gray-400"></i>
            </div>
        </form>
    </div>

    <div class="bg-white dark:bg-dark-300 rounded-xl shadow-sm border border-gray-200 dark:border-dark-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 dark:bg-dark-200 text-xs text-gray-500 dark:text-gray-400 uppercase">
                    <tr>
                        <th class="px-6 py-3">ID</th>
                        <th class="px-6 py-3">Nome</th>
                        <th class="px-6 py-3">Fonte</th>
                        <th class="px-6 py-3">Servidor</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-dark-100">
                    @forelse($channels as $channel)
                    <tr class="hover:bg-gray-50 dark:hover:bg-dark-200/50">
                        <td class="px-6 py-4 font-mono text-sm text-gray-500">#{{ $channel->id }}</td>
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-900 dark:text-white">{{ $channel->stream_display_name }}</div>
                            <div class="text-xs text-gray-500">Cat ID: {{ $channel->category_id ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-xs text-gray-500 font-mono truncate max-w-xs" title="{{ $channel->stream_source }}">
                                {{ $channel->stream_source }}
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            SRV #{{ $channel->server_id }}
                            @if($channel->pid)
                                <span class="ml-1 text-xs bg-gray-100 dark:bg-dark-100 px-1.5 py-0.5 rounded">PID: {{ $channel->pid }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($channel->stream_status == 0)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">
                                    Offline
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                    Online
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <a href="{{ route('settings.admin.channels.edit', $channel->id) }}" class="text-blue-600 hover:text-blue-900 dark:hover:text-blue-400" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('settings.admin.channels.restart', $channel->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Reiniciar este canal?');">
                                @csrf
                                <button type="submit" class="text-orange-600 hover:text-orange-900 dark:hover:text-orange-400" title="Reiniciar">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-gray-500">Nenhum canal encontrado.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="p-4 border-t border-gray-200 dark:border-dark-100">
            {{ $channels->links() }}
        </div>
    </div>
</div>
@endsection
