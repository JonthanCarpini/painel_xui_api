@extends('layouts.app')

@section('title', 'Detalhes do Pedido - ' . $vodRequest->title)

@section('content')
<div class="mb-6">
    <a href="{{ route('settings.admin.vod-requests.index') }}" class="text-sm text-gray-500 dark:text-gray-400 hover:text-orange-500 dark:hover:text-orange-400 transition-colors flex items-center gap-1">
        <i class="bi bi-arrow-left"></i> Voltar para Pedidos
    </a>
</div>

@if(session('success'))
<div class="bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/30 rounded-xl p-4 mb-6">
    <p class="text-green-700 dark:text-green-400 text-sm flex items-center gap-2">
        <i class="bi bi-check-circle"></i> {{ session('success') }}
    </p>
</div>
@endif

<!-- Header com backdrop -->
<div class="relative rounded-2xl overflow-hidden mb-6 h-56 md:h-72 bg-gray-200 dark:bg-dark-200">
    @if($vodRequest->backdrop_path)
    <img src="{{ $vodRequest->backdrop_url }}" alt="" class="w-full h-full object-cover">
    @endif
    <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 to-transparent"></div>
    <div class="absolute bottom-0 left-0 right-0 p-6 flex items-end gap-5">
        <div class="w-24 h-36 md:w-32 md:h-48 rounded-xl overflow-hidden shadow-2xl shrink-0 border-2 border-white/20">
            <img src="{{ $vodRequest->poster_url }}" alt="{{ $vodRequest->title }}" class="w-full h-full object-cover">
        </div>
        <div class="flex-1 min-w-0 pb-1">
            <div class="flex items-center gap-2 mb-2">
                <span class="text-xs font-medium px-2.5 py-1 rounded-full {{ $vodRequest->type === 'movie' ? 'bg-purple-500/30 text-purple-300' : 'bg-blue-500/30 text-blue-300' }}">
                    {{ $vodRequest->type === 'movie' ? 'Filme' : 'Série' }}
                </span>
                @if($vodRequest->status === 'pending')
                    <span class="text-xs font-medium px-2.5 py-1 rounded-full bg-yellow-500/30 text-yellow-300">Pendente</span>
                @elseif($vodRequest->status === 'completed')
                    <span class="text-xs font-medium px-2.5 py-1 rounded-full bg-green-500/30 text-green-300">Concluído</span>
                @else
                    <span class="text-xs font-medium px-2.5 py-1 rounded-full bg-red-500/30 text-red-300">Recusado</span>
                @endif
            </div>
            <h1 class="text-2xl md:text-3xl font-bold text-white mb-1">{{ $vodRequest->title }}</h1>
            @if($vodRequest->original_title && $vodRequest->original_title !== $vodRequest->title)
            <p class="text-gray-300 text-sm">{{ $vodRequest->original_title }}</p>
            @endif
            <div class="flex items-center gap-4 mt-2 text-sm text-gray-300">
                @if($vodRequest->release_date)
                <span class="flex items-center gap-1"><i class="bi bi-calendar"></i> {{ $vodRequest->release_date }}</span>
                @endif
                @if($vodRequest->vote_average > 0)
                <span class="flex items-center gap-1 text-yellow-400"><i class="bi bi-star-fill"></i> {{ number_format($vodRequest->vote_average, 1) }}</span>
                @endif
                <span class="flex items-center gap-1 text-orange-400"><i class="bi bi-people-fill"></i> {{ $requestCount }} pedido(s)</span>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Coluna principal -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Sinopse -->
        @if($vodRequest->overview)
        <div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 p-6 shadow-sm dark:shadow-none">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                <i class="bi bi-text-paragraph"></i> Sinopse
            </h2>
            <p class="text-gray-600 dark:text-gray-400 text-sm leading-relaxed">{{ $vodRequest->overview }}</p>
        </div>
        @endif

        <!-- Detalhes do TMDB -->
        @if($tmdbDetails)
        <div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 p-6 shadow-sm dark:shadow-none">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <i class="bi bi-info-circle"></i> Informações do TMDB
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                @if(isset($tmdbDetails['genres']))
                <div>
                    <span class="text-gray-500 dark:text-gray-400 block mb-1">Gêneros</span>
                    <div class="flex flex-wrap gap-1">
                        @foreach($tmdbDetails['genres'] as $genre)
                        <span class="px-2 py-0.5 bg-gray-100 dark:bg-dark-200 rounded-full text-xs text-gray-700 dark:text-gray-300">{{ $genre['name'] }}</span>
                        @endforeach
                    </div>
                </div>
                @endif

                @if(isset($tmdbDetails['runtime']))
                <div>
                    <span class="text-gray-500 dark:text-gray-400 block mb-1">Duração</span>
                    <span class="text-gray-900 dark:text-white font-medium">{{ $tmdbDetails['runtime'] }} min</span>
                </div>
                @endif

                @if(isset($tmdbDetails['number_of_seasons']))
                <div>
                    <span class="text-gray-500 dark:text-gray-400 block mb-1">Temporadas</span>
                    <span class="text-gray-900 dark:text-white font-medium">{{ $tmdbDetails['number_of_seasons'] }}</span>
                </div>
                @endif

                @if(isset($tmdbDetails['number_of_episodes']))
                <div>
                    <span class="text-gray-500 dark:text-gray-400 block mb-1">Episódios</span>
                    <span class="text-gray-900 dark:text-white font-medium">{{ $tmdbDetails['number_of_episodes'] }}</span>
                </div>
                @endif

                @if(isset($tmdbDetails['status']))
                <div>
                    <span class="text-gray-500 dark:text-gray-400 block mb-1">Status</span>
                    <span class="text-gray-900 dark:text-white font-medium">{{ $tmdbDetails['status'] }}</span>
                </div>
                @endif

                @if(isset($tmdbDetails['popularity']))
                <div>
                    <span class="text-gray-500 dark:text-gray-400 block mb-1">Popularidade TMDB</span>
                    <span class="text-gray-900 dark:text-white font-medium">{{ number_format($tmdbDetails['popularity'], 1) }}</span>
                </div>
                @endif

                @if(isset($tmdbDetails['credits']['cast']) && count($tmdbDetails['credits']['cast']) > 0)
                <div class="sm:col-span-2">
                    <span class="text-gray-500 dark:text-gray-400 block mb-1">Elenco Principal</span>
                    <p class="text-gray-900 dark:text-white font-medium">
                        {{ implode(', ', array_slice(array_column($tmdbDetails['credits']['cast'], 'name'), 0, 8)) }}
                    </p>
                </div>
                @endif

                @if(isset($tmdbDetails['credits']['crew']))
                @php
                    $directors = collect($tmdbDetails['credits']['crew'])->where('job', 'Director')->pluck('name')->toArray();
                @endphp
                @if(!empty($directors))
                <div>
                    <span class="text-gray-500 dark:text-gray-400 block mb-1">Diretor</span>
                    <span class="text-gray-900 dark:text-white font-medium">{{ implode(', ', $directors) }}</span>
                </div>
                @endif
                @endif
            </div>
        </div>
        @endif

        <!-- Status no servidor XUI -->
        <div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 p-6 shadow-sm dark:shadow-none">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <i class="bi bi-server"></i> Status no Servidor
            </h2>
            @if($existsInXui)
            <div class="bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/30 rounded-xl p-4">
                <div class="flex items-center gap-2 mb-3">
                    <i class="bi bi-check-circle-fill text-green-500 text-lg"></i>
                    <span class="text-green-700 dark:text-green-400 font-semibold">Já existe no servidor!</span>
                </div>
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <span class="text-gray-500 dark:text-gray-400">Nome:</span>
                        <p class="text-gray-900 dark:text-white font-medium">{{ $vodRequest->type === 'movie' ? $existsInXui->stream_display_name : $existsInXui->title }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500 dark:text-gray-400">Categoria:</span>
                        <p class="text-gray-900 dark:text-white font-medium">{{ $categoryName }}</p>
                    </div>
                    @if($addedDate)
                    <div>
                        <span class="text-gray-500 dark:text-gray-400">Adicionado em:</span>
                        <p class="text-gray-900 dark:text-white font-medium">{{ $addedDate }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @else
            <div class="bg-yellow-50 dark:bg-yellow-500/10 border border-yellow-200 dark:border-yellow-500/30 rounded-xl p-4">
                <div class="flex items-center gap-2">
                    <i class="bi bi-exclamation-triangle text-yellow-500 text-lg"></i>
                    <span class="text-yellow-700 dark:text-yellow-400 font-semibold">Não encontrado no servidor.</span>
                </div>
                <p class="text-yellow-600 dark:text-yellow-400/80 text-sm mt-1">Este título ainda não foi adicionado ao XUI.</p>
            </div>
            @endif
        </div>

        <!-- Quem solicitou -->
        <div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 p-6 shadow-sm dark:shadow-none">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <i class="bi bi-people"></i> Solicitantes ({{ $requestCount }})
            </h2>
            <div class="divide-y divide-gray-200 dark:divide-dark-200">
                @foreach($allRequesters as $r)
                <div class="flex items-center justify-between py-3 {{ $loop->first ? '' : '' }}">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-orange-100 dark:bg-orange-500/20 flex items-center justify-center">
                            <i class="bi bi-person text-orange-600 dark:text-orange-400 text-sm"></i>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $r->user->username ?? 'Usuário #' . $r->user_id }}</span>
                            @if($r->user && $r->user->isAdmin())
                            <span class="text-xs text-orange-500 ml-1">(Admin)</span>
                            @elseif($r->user && $r->user->isReseller())
                            <span class="text-xs text-blue-500 ml-1">(Revenda)</span>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $r->created_at->format('d/m/Y H:i') }}</span>
                        @if($r->status === 'pending')
                            <span class="w-2 h-2 rounded-full bg-yellow-500"></span>
                        @elseif($r->status === 'completed')
                            <span class="w-2 h-2 rounded-full bg-green-500"></span>
                        @else
                            <span class="w-2 h-2 rounded-full bg-red-500"></span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Coluna lateral -->
    <div class="space-y-6">
        <!-- Ações -->
        @if($vodRequest->status === 'pending')
        <div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 p-6 shadow-sm dark:shadow-none">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <i class="bi bi-lightning"></i> Ações
            </h2>

            <form method="POST" action="{{ route('settings.admin.vod-requests.resolve', $vodRequest->id) }}" id="resolveForm">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300 block mb-2">Nota (opcional)</label>
                    <textarea name="admin_note" rows="3" placeholder="Motivo da decisão..." class="w-full px-3 py-2 bg-gray-100 dark:bg-dark-200 border border-gray-200 dark:border-dark-100 rounded-lg text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-orange-500 focus:border-transparent resize-none"></textarea>
                </div>

                <div class="flex gap-3">
                    <button type="submit" name="status" value="completed" class="flex-1 py-3 bg-green-500 hover:bg-green-600 text-white rounded-xl font-medium text-sm transition-colors flex items-center justify-center gap-2" onclick="return confirm('Marcar como concluído? Todos os pedidos deste título serão atualizados.')">
                        <i class="bi bi-check-circle"></i> Concluir
                    </button>
                    <button type="submit" name="status" value="rejected" class="flex-1 py-3 bg-red-500 hover:bg-red-600 text-white rounded-xl font-medium text-sm transition-colors flex items-center justify-center gap-2" onclick="return confirm('Recusar este pedido? Todos os pedidos deste título serão atualizados.')">
                        <i class="bi bi-x-circle"></i> Recusar
                    </button>
                </div>
            </form>
        </div>
        @else
        <div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 p-6 shadow-sm dark:shadow-none">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <i class="bi bi-info-circle"></i> Resolução
            </h2>
            <div class="space-y-3 text-sm">
                <div>
                    <span class="text-gray-500 dark:text-gray-400">Status:</span>
                    <p class="font-medium {{ $vodRequest->status === 'completed' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                        {{ $vodRequest->status === 'completed' ? 'Concluído' : 'Recusado' }}
                    </p>
                </div>
                @if($vodRequest->admin_note)
                <div>
                    <span class="text-gray-500 dark:text-gray-400">Nota do Admin:</span>
                    <p class="text-gray-900 dark:text-white">{{ $vodRequest->admin_note }}</p>
                </div>
                @endif
                @if($vodRequest->resolved_at)
                <div>
                    <span class="text-gray-500 dark:text-gray-400">Resolvido em:</span>
                    <p class="text-gray-900 dark:text-white">{{ $vodRequest->resolved_at->format('d/m/Y H:i') }}</p>
                </div>
                @endif
                @if($vodRequest->resolver)
                <div>
                    <span class="text-gray-500 dark:text-gray-400">Resolvido por:</span>
                    <p class="text-gray-900 dark:text-white">{{ $vodRequest->resolver->username }}</p>
                </div>
                @endif
            </div>

            <!-- Permitir reabrir -->
            <form method="POST" action="{{ route('settings.admin.vod-requests.resolve', $vodRequest->id) }}" class="mt-4">
                @csrf
                @method('PUT')
                <input type="hidden" name="status" value="completed">
                @if($vodRequest->status === 'rejected')
                <button type="submit" class="w-full py-2.5 bg-green-500 hover:bg-green-600 text-white rounded-xl font-medium text-sm transition-colors flex items-center justify-center gap-2" onclick="return confirm('Alterar para concluído?')">
                    <i class="bi bi-check-circle"></i> Marcar como Concluído
                </button>
                @endif
            </form>
        </div>
        @endif

        <!-- Info rápida -->
        <div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 p-6 shadow-sm dark:shadow-none">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <i class="bi bi-card-list"></i> Resumo
            </h2>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">TMDB ID</span>
                    <a href="https://www.themoviedb.org/{{ $vodRequest->type === 'movie' ? 'movie' : 'tv' }}/{{ $vodRequest->tmdb_id }}" target="_blank" class="text-orange-500 hover:text-orange-600 font-medium flex items-center gap-1">
                        {{ $vodRequest->tmdb_id }} <i class="bi bi-box-arrow-up-right text-xs"></i>
                    </a>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Tipo</span>
                    <span class="text-gray-900 dark:text-white font-medium">{{ $vodRequest->type === 'movie' ? 'Filme' : 'Série' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Total de Pedidos</span>
                    <span class="text-orange-500 font-bold">{{ $requestCount }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Primeiro Pedido</span>
                    <span class="text-gray-900 dark:text-white">{{ $allRequesters->last()?->created_at->format('d/m/Y') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Último Pedido</span>
                    <span class="text-gray-900 dark:text-white">{{ $allRequesters->first()?->created_at->format('d/m/Y') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
