@extends('layouts.app')

@section('title', 'Atualizações Recentes')

@section('content')
<div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-6 md:mb-8 gap-4">
    <div>
        <h1 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
            <i class="bi bi-stars text-orange-500"></i>
            Atualizações Recentes
        </h1>
        <p class="text-sm md:text-base text-gray-500 dark:text-gray-400 mt-1">Confira os últimos filmes e séries adicionados ao catálogo.</p>
    </div>
</div>

<!-- Abas de Navegação -->
<div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 mb-6 shadow-sm dark:shadow-none overflow-hidden">
    <div class="flex border-b border-gray-200 dark:border-dark-200 overflow-x-auto custom-scrollbar">
        <button type="button" onclick="switchTab('movies')" id="tab-movies" class="flex-1 min-w-[160px] px-4 md:px-6 py-3 md:py-4 text-sm font-medium transition-colors flex items-center justify-center gap-2 whitespace-nowrap text-orange-600 dark:text-orange-500 border-b-2 border-orange-600 dark:border-orange-500 bg-orange-50/50 dark:bg-orange-500/10">
            <i class="bi bi-film"></i>
            Filmes Recentes
        </button>
        <button type="button" onclick="switchTab('series')" id="tab-series" class="flex-1 min-w-[160px] px-4 md:px-6 py-3 md:py-4 text-sm font-medium transition-colors flex items-center justify-center gap-2 whitespace-nowrap text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-50 dark:hover:bg-dark-200">
            <i class="bi bi-tv"></i>
            Séries Recentes
        </button>
    </div>
</div>

<!-- Conteúdo Filmes -->
<div id="content-movies" class="tab-content">
    @forelse($moviesGrouped as $date => $movies)
        <div class="mb-8">
            <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-4 flex items-center gap-2 border-b border-gray-200 dark:border-dark-200 pb-2">
                <i class="bi bi-calendar-event"></i> {{ $date }}
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4">
                @foreach($movies as $movie)
                <div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 overflow-hidden shadow-sm hover:shadow-md transition-all group">
                    <div class="aspect-[2/3] bg-gray-100 dark:bg-dark-200 relative overflow-hidden">
                        <img src="{{ $movie['stream_icon'] ?? '' }}" 
                             alt="{{ $movie['name'] ?? $movie['stream_display_name'] ?? '' }}" 
                             class="w-full h-full object-cover transition-transform group-hover:scale-105" 
                             loading="lazy" 
                             onerror="handleImageError(this, 'movie', {{ $movie['tmdb_id'] ?? 'null' }}, '{{ addslashes($movie['name'] ?? $movie['stream_display_name'] ?? '') }}')">
                        
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex flex-col justify-end p-4">
                            <span class="text-white text-xs font-medium">{{ isset($movie['added_at']) && $movie['added_at'] ? $movie['added_at']->format('H:i') : '' }}</span>
                        </div>
                    </div>
                    <div class="p-3">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white line-clamp-2 mb-1" title="{{ $movie['name'] ?? $movie['stream_display_name'] ?? '' }}">
                            {{ $movie['name'] ?? $movie['stream_display_name'] ?? '' }}
                        </h3>
                        <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                            <span>{{ $movie['year'] ?? '' }}</span>
                            @if(($movie['rating'] ?? 0) > 0)
                            <span class="flex items-center gap-1 text-yellow-500">
                                <i class="bi bi-star-fill"></i> {{ number_format($movie['rating'], 1) }}
                            </span>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    @empty
        <div class="col-span-full text-center py-12 text-gray-500 dark:text-gray-400">
            Nenhum filme encontrado recentemente.
        </div>
    @endforelse
</div>

<!-- Conteúdo Séries -->
<div id="content-series" class="tab-content hidden">
    @forelse($seriesGrouped as $date => $series)
        <div class="mb-8">
            <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-4 flex items-center gap-2 border-b border-gray-200 dark:border-dark-200 pb-2">
                <i class="bi bi-calendar-event"></i> {{ $date }}
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4">
                @foreach($series as $serie)
                <div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 overflow-hidden shadow-sm hover:shadow-md transition-all group">
                    <div class="aspect-[2/3] bg-gray-100 dark:bg-dark-200 relative overflow-hidden">
                        <img src="{{ $serie['cover'] ?? $serie['stream_icon'] ?? '' }}" 
                             alt="{{ $serie['name'] ?? $serie['title'] ?? '' }}" 
                             class="w-full h-full object-cover transition-transform group-hover:scale-105" 
                             loading="lazy" 
                             onerror="handleImageError(this, 'tv', {{ $serie['tmdb_id'] ?? 'null' }}, '{{ addslashes($serie['name'] ?? $serie['title'] ?? '') }}')">
                        
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex flex-col justify-end p-4">
                            <span class="text-white text-xs font-medium">{{ isset($serie['updated_at']) && $serie['updated_at'] ? $serie['updated_at']->format('H:i') : '' }}</span>
                        </div>
                    </div>
                    <div class="p-3">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white line-clamp-2 mb-1" title="{{ $serie['name'] ?? $serie['title'] ?? '' }}">
                            {{ $serie['name'] ?? $serie['title'] ?? '' }}
                        </h3>
                        <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                            <span>{{ $serie['release_date'] ?? '' }}</span>
                            @if(($serie['rating'] ?? 0) > 0)
                            <span class="flex items-center gap-1 text-yellow-500">
                                <i class="bi bi-star-fill"></i> {{ number_format($serie['rating'], 1) }}
                            </span>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    @empty
        <div class="col-span-full text-center py-12 text-gray-500 dark:text-gray-400">
            Nenhuma série encontrada recentemente.
        </div>
    @endforelse
</div>

@push('scripts')
<script>
    function switchTab(tabName) {
        // Remover active de todos os botões
        const buttons = {
            'movies': document.getElementById('tab-movies'),
            'series': document.getElementById('tab-series')
        };

        Object.values(buttons).forEach(btn => {
            btn.classList.remove('text-orange-600', 'dark:text-orange-500', 'border-b-2', 'border-orange-600', 'dark:border-orange-500', 'bg-orange-50/50', 'dark:bg-orange-500/10');
            btn.classList.add('text-gray-500', 'dark:text-gray-400', 'hover:text-gray-700', 'dark:hover:text-gray-300', 'hover:bg-gray-50', 'dark:hover:bg-dark-200');
        });
        
        // Adicionar active no botão clicado
        const activeBtn = buttons[tabName];
        activeBtn.classList.remove('text-gray-500', 'dark:text-gray-400', 'hover:text-gray-700', 'dark:hover:text-gray-300', 'hover:bg-gray-50', 'dark:hover:bg-dark-200');
        activeBtn.classList.add('text-orange-600', 'dark:text-orange-500', 'border-b-2', 'border-orange-600', 'dark:border-orange-500', 'bg-orange-50/50', 'dark:bg-orange-500/10');
        
        // Esconder todos os conteúdos
        document.getElementById('content-movies').classList.add('hidden');
        document.getElementById('content-series').classList.add('hidden');
        
        // Mostrar conteúdo da aba selecionada
        document.getElementById('content-' + tabName).classList.remove('hidden');
    }

    const tmdbApiKey = "{{ $tmdbApiKey }}";

    async function handleImageError(imgElement, type, tmdbId, title) {
        // Evitar loop infinito
        imgElement.onerror = null;
        
        // Fallback padrão (avatar com texto)
        const fallbackUrl = `https://ui-avatars.com/api/?name=${encodeURIComponent(title)}&background=random&size=300`;

        if (!tmdbApiKey || !tmdbId || tmdbId === 'null') {
            imgElement.src = fallbackUrl;
            return;
        }

        try {
            const endpoint = type === 'movie' ? 'movie' : 'tv';
            const response = await fetch(`https://api.themoviedb.org/3/${endpoint}/${tmdbId}?api_key=${tmdbApiKey}&language=pt-BR`);
            
            if (!response.ok) throw new Error('TMDB API Error');
            
            const data = await response.json();
            
            if (data.poster_path) {
                imgElement.src = `https://image.tmdb.org/t/p/w500${data.poster_path}`;
            } else {
                imgElement.src = fallbackUrl;
            }
        } catch (error) {
            console.error('Erro ao buscar imagem TMDB:', error);
            imgElement.src = fallbackUrl;
        }
    }
</script>
@endpush
@endsection
