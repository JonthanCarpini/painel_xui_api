@extends('layouts.app')

@section('title', 'Gestão de Load Balancers')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
            <i class="bi bi-hdd-network text-blue-600"></i>
            Gestão de Load Balancers
        </h1>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($servers as $server)
        <div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 shadow-sm overflow-hidden hover:shadow-md transition-shadow">
            <div class="p-6">
                <div class="flex justify-between items-start mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/20 flex items-center justify-center text-blue-600 dark:text-blue-400">
                            <i class="bi bi-server text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 dark:text-white">{{ $server->server_name }}</h3>
                            <div class="text-sm text-gray-500 font-mono">{{ $server->server_ip }}</div>
                        </div>
                    </div>
                    @if($server->is_main)
                        <span class="bg-orange-100 text-orange-800 text-xs px-2 py-1 rounded font-bold uppercase">Main</span>
                    @endif
                </div>

                <div class="space-y-3 mb-6">
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-500">Status</span>
                        @if($server->status == 1)
                            <span class="text-green-600 font-bold flex items-center gap-1"><i class="bi bi-circle-fill text-[8px]"></i> Online</span>
                        @else
                            <span class="text-red-500 font-bold flex items-center gap-1"><i class="bi bi-x-circle-fill"></i> Offline</span>
                        @endif
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-500">Clientes</span>
                        <span class="font-mono bg-gray-100 dark:bg-dark-200 px-2 py-0.5 rounded">{{ $server->total_clients }}</span>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-500">Último Check</span>
                        <span class="text-gray-700 dark:text-gray-300">{{ $server->last_check_ago }}s atrás</span>
                    </div>
                </div>

                <a href="{{ route('settings.admin.servers.show', $server->id) }}" class="block w-full py-2 bg-gray-100 hover:bg-gray-200 dark:bg-dark-200 dark:hover:bg-dark-100 text-center rounded-lg text-gray-700 dark:text-gray-300 font-medium transition-colors">
                    Gerenciar Servidor
                </a>
            </div>
        </div>
        @empty
        <div class="col-span-full py-12 text-center text-gray-500 dark:text-gray-400 bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200">
            Nenhum servidor encontrado.
        </div>
        @endforelse
    </div>
</div>
@endsection
