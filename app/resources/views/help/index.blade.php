@extends('layouts.app')
@section('title', 'Central de Ajuda')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Central de Ajuda</h1>
        <p class="text-gray-500 dark:text-gray-400 mt-1">Tutoriais, guias e instruções para utilizar o painel e aplicativos.</p>
    </div>

    {{-- Busca + Filtro --}}
    <div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 p-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[220px]">
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Buscar</label>
                <div class="relative">
                    <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Buscar por título ou conteúdo..."
                           class="w-full pl-9 pr-3 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white text-sm focus:ring-orange-500 focus:border-orange-500">
                </div>
            </div>
            <div class="min-w-[180px]">
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Categoria</label>
                <select name="category" class="w-full px-3 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white text-sm focus:ring-orange-500 focus:border-orange-500">
                    <option value="">Todas as categorias</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }} ({{ $cat->posts_count }})</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg text-sm font-medium hover:shadow-lg transition-all">
                <i class="bi bi-search me-1"></i> Buscar
            </button>
            @if(request()->hasAny(['q', 'category']))
                <a href="{{ route('help.index') }}" class="px-4 py-2 bg-gray-200 dark:bg-dark-200 text-gray-600 dark:text-gray-400 rounded-lg text-sm font-medium hover:bg-gray-300 transition-colors">Limpar</a>
            @endif
        </form>
    </div>

    {{-- Categorias (chips) --}}
    @if($categories->isNotEmpty())
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('help.index', array_filter(['q' => request('q')])) }}"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium transition-colors
                  {{ !request('category') ? 'bg-orange-500 text-white shadow-md' : 'bg-gray-100 dark:bg-dark-200 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-dark-100' }}">
            Todas
        </a>
        @foreach($categories as $cat)
        <a href="{{ route('help.index', array_filter(['category' => $cat->id, 'q' => request('q')])) }}"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium transition-colors
                  {{ request('category') == $cat->id ? 'text-white shadow-md' : 'hover:opacity-80' }}"
           style="{{ request('category') == $cat->id ? 'background-color:'.$cat->color : 'background-color:'.$cat->color.'15; color:'.$cat->color }}">
            <i class="bi {{ $cat->icon }}"></i> {{ $cat->name }}
            <span class="opacity-70">({{ $cat->posts_count }})</span>
        </a>
        @endforeach
    </div>
    @endif

    {{-- Grid de Posts --}}
    @if($posts->isNotEmpty())
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($posts as $post)
        <a href="{{ route('help.show', $post) }}" class="group bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 overflow-hidden hover:shadow-lg hover:border-orange-300 dark:hover:border-orange-500/30 transition-all duration-200">
            {{-- Thumbnail --}}
            @if($post->images->isNotEmpty())
            <div class="h-40 overflow-hidden bg-gray-100 dark:bg-dark-200">
                <img src="{{ $post->images->first()->url }}" alt="{{ $post->title }}"
                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                     onerror="this.parentElement.innerHTML='<div class=\'flex items-center justify-center h-full text-gray-300 dark:text-gray-600\'><i class=\'bi bi-image text-3xl\'></i></div>'">
            </div>
            @elseif($post->videos->isNotEmpty())
            <div class="h-40 overflow-hidden bg-gray-900 flex items-center justify-center">
                <i class="bi bi-play-circle text-4xl text-white/60 group-hover:text-orange-400 transition-colors"></i>
            </div>
            @endif

            <div class="p-4 space-y-2">
                {{-- Categoria badge --}}
                @if($post->category)
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold"
                      style="background-color: {{ $post->category->color }}15; color: {{ $post->category->color }}">
                    <i class="bi {{ $post->category->icon }}"></i> {{ $post->category->name }}
                </span>
                @endif

                <h3 class="font-semibold text-gray-900 dark:text-white group-hover:text-orange-500 transition-colors line-clamp-2">
                    {{ $post->title }}
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2">
                    {{ Str::limit(strip_tags($post->content), 120) }}
                </p>

                <div class="flex items-center justify-between pt-2">
                    <span class="text-[10px] text-gray-400 dark:text-gray-500">{{ $post->updated_at->diffForHumans() }}</span>
                    @if($post->media->count() > 0)
                    <span class="text-[10px] text-gray-400 dark:text-gray-500">
                        <i class="bi bi-paperclip"></i> {{ $post->media->count() }} {{ $post->media->count() === 1 ? 'anexo' : 'anexos' }}
                    </span>
                    @endif
                </div>
            </div>
        </a>
        @endforeach
    </div>

    @if($posts->hasPages())
    <div class="mt-6">
        {{ $posts->links() }}
    </div>
    @endif

    @else
    <div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 p-12 text-center">
        <i class="bi bi-question-circle text-5xl text-gray-300 dark:text-gray-600"></i>
        <p class="text-gray-500 dark:text-gray-400 mt-3 text-lg">Nenhum artigo encontrado.</p>
        @if(request()->hasAny(['q', 'category']))
            <a href="{{ route('help.index') }}" class="inline-block mt-3 text-orange-500 hover:text-orange-600 font-medium text-sm">
                <i class="bi bi-arrow-left me-1"></i> Ver todos os artigos
            </a>
        @else
            <p class="text-gray-400 dark:text-gray-500 text-sm mt-1">Em breve novos artigos serão publicados.</p>
        @endif
    </div>
    @endif
</div>
@endsection
