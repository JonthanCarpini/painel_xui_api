@extends('layouts.app')

@section('title', 'Suporte')

@section('content')
<div class="flex flex-col md:flex-row items-center justify-between mb-8 gap-4">
    <div class="w-full md:w-auto">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
            <i class="bi bi-headset text-orange-500"></i>
            Suporte / Tickets
        </h1>
        <p class="text-gray-500 dark:text-gray-400 mt-1 text-sm md:text-base">
            @if(Auth::user()->isAdmin())
                Gerencie os chamados de suporte dos revendedores.
            @else
                Abra chamados para resolver problemas ou tirar dúvidas.
            @endif
        </p>
    </div>
    @if(!Auth::user()->isAdmin())
    <a href="{{ route('tickets.create') }}" class="w-full md:w-auto bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white font-bold py-2.5 px-6 rounded-xl shadow-lg shadow-orange-500/20 transition-all duration-200 flex items-center justify-center gap-2">
        <i class="bi bi-plus-lg"></i>
        Novo Ticket
    </a>
    @endif
</div>

@if(Auth::user()->isAdmin())
<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    <!-- Sidebar de Pastas / Categorias -->
    <div class="lg:col-span-1 space-y-4">
        <div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 p-4 shadow-sm">
            <h3 class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3 px-2">Caixa de Entrada</h3>
            <nav class="space-y-1">
                <a href="{{ route('tickets.index') }}" class="flex items-center justify-between px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ !request('category') ? 'bg-orange-50 text-orange-700 dark:bg-orange-500/10 dark:text-orange-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-dark-200' }}">
                    <span class="flex items-center gap-2">
                        <i class="bi bi-inbox"></i> Todos os Tickets
                    </span>
                </a>
                
                @if($uncategorizedCount > 0)
                <a href="{{ route('tickets.index', ['category' => 'uncategorized']) }}" class="flex items-center justify-between px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request('category') === 'uncategorized' ? 'bg-orange-50 text-orange-700 dark:bg-orange-500/10 dark:text-orange-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-dark-200' }}">
                    <span class="flex items-center gap-2">
                        <i class="bi bi-question-circle"></i> Sem Categoria
                    </span>
                    <span class="bg-gray-100 dark:bg-dark-100 text-gray-600 dark:text-gray-400 py-0.5 px-2 rounded-full text-xs">{{ $uncategorizedCount }}</span>
                </a>
                @endif
            </nav>

            <h3 class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mt-6 mb-3 px-2">Categorias</h3>
            <nav class="space-y-1">
                @foreach($categoriesStats as $catId => $stat)
                <a href="{{ route('tickets.index', ['category' => $catId]) }}" class="flex items-center justify-between px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request('category') == $catId ? 'bg-orange-50 text-orange-700 dark:bg-orange-500/10 dark:text-orange-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-dark-200' }}">
                    <span class="flex items-center gap-2">
                        <i class="bi bi-folder"></i> {{ $stat['name'] }}
                    </span>
                    @if($stat['count'] > 0)
                    <span class="bg-gray-100 dark:bg-dark-100 text-gray-600 dark:text-gray-400 py-0.5 px-2 rounded-full text-xs">{{ $stat['count'] }}</span>
                    @endif
                </a>
                @endforeach
            </nav>
        </div>
    </div>

    <!-- Lista de Tickets -->
    <div class="lg:col-span-3">
        <div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 overflow-hidden shadow-sm">
            <div class="p-4 border-b border-gray-200 dark:border-dark-100 bg-gray-50 dark:bg-dark-200/50 flex items-center justify-between">
                <h2 class="font-semibold text-gray-700 dark:text-gray-200">
                    {{ $selectedCategory ?? 'Todos' }}
                </h2>
                <span class="text-xs text-gray-500">{{ $tickets->total() }} tickets</span>
            </div>
            <div class="overflow-x-auto custom-scrollbar">
@else
<div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 overflow-hidden shadow-sm">
    <div class="overflow-x-auto custom-scrollbar">
@endif
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 dark:bg-dark-200 border-b border-gray-200 dark:border-dark-100">
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap hidden sm:table-cell">ID</th>
                    @if(Auth::user()->isAdmin())
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Revendedor</th>
                    @endif
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Assunto</th>
                    @if(Auth::user()->isAdmin())
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap hidden md:table-cell">Categoria</th>
                    @endif
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Status</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap text-right">A&ccedil;&otilde;es</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-dark-100">
                @forelse($tickets as $ticket)
                <tr class="hover:bg-gray-50 dark:hover:bg-dark-200/50 transition-colors">
                    <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white whitespace-nowrap hidden sm:table-cell">
                        #{{ $ticket->id }}
                    </td>
                    @if(Auth::user()->isAdmin())
                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300 whitespace-nowrap">
                        {{ $ticket->user->username ?? 'Desconhecido' }}
                    </td>
                    @endif
                    <td class="px-4 py-3 min-w-[200px]">
                        <span class="text-sm font-medium text-gray-900 dark:text-white block truncate">{{ $ticket->title }}</span>
                        @if((Auth::user()->isAdmin() && !$ticket->admin_read) || (!Auth::user()->isAdmin() && !$ticket->user_read))
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400 mt-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> Nova Mensagem
                            </span>
                        @endif
                    </td>
                    @if(Auth::user()->isAdmin())
                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300 whitespace-nowrap hidden md:table-cell">
                        @if($ticket->extra && $ticket->extra->category)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                {{ $ticket->extra->category->name }}
                            </span>
                        @else
                            <span class="text-gray-400 italic text-xs">Sem Categoria</span>
                        @endif
                    </td>
                    @endif
                    <td class="px-4 py-3 whitespace-nowrap">
                        @php
                            $statusColors = [
                                1 => 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400',
                                0 => 'bg-gray-100 text-gray-700 dark:bg-gray-500/20 dark:text-gray-400',
                                2 => 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400',
                            ];
                            $statusLabel = [
                                1 => 'Aberto',
                                0 => 'Fechado',
                                2 => 'Respondido',
                            ];
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$ticket->status] ?? 'bg-gray-100 text-gray-500' }}">
                            {{ $statusLabel[$ticket->status] ?? 'Desconhecido' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right whitespace-nowrap">
                        <a href="{{ route('tickets.show', $ticket->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-orange-50 text-orange-600 dark:bg-orange-500/10 dark:text-orange-400 hover:bg-orange-100 dark:hover:bg-orange-500/20 transition-colors" title="Ver Ticket">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ Auth::user()->isAdmin() ? 6 : 4 }}" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                        <i class="bi bi-inbox text-4xl mb-3 block opacity-50"></i>
                        Nenhum ticket encontrado.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($tickets->hasPages())
    <div class="p-4 border-t border-gray-200 dark:border-dark-100">
        {{ $tickets->links() }}
    </div>
    @endif
</div>
@if(Auth::user()->isAdmin())
    </div>
</div>
@endif
@endsection
