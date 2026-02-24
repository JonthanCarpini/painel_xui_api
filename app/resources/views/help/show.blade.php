@extends('layouts.app')
@section('title', $post->title . ' - Central de Ajuda')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
        <a href="{{ route('help.index') }}" class="hover:text-orange-500 transition-colors">Central de Ajuda</a>
        <i class="bi bi-chevron-right text-xs"></i>
        <a href="{{ route('help.index', ['category' => $post->help_category_id]) }}" class="hover:text-orange-500 transition-colors">{{ $post->category->name }}</a>
        <i class="bi bi-chevron-right text-xs"></i>
        <span class="text-gray-700 dark:text-gray-300">{{ Str::limit($post->title, 40) }}</span>
    </div>

    {{-- Post --}}
    <article class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 overflow-hidden">
        {{-- Header --}}
        <div class="p-6 pb-4 border-b border-gray-100 dark:border-dark-200">
            <div class="flex items-center gap-3 mb-3">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold"
                      style="background-color: {{ $post->category->color }}15; color: {{ $post->category->color }}">
                    <i class="bi {{ $post->category->icon }}"></i> {{ $post->category->name }}
                </span>
                <span class="text-xs text-gray-400 dark:text-gray-500">Atualizado {{ $post->updated_at->diffForHumans() }}</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $post->title }}</h1>
        </div>

        {{-- Conteúdo --}}
        <div class="p-6 prose dark:prose-invert prose-sm max-w-none
                    prose-headings:text-gray-900 dark:prose-headings:text-white
                    prose-p:text-gray-700 dark:prose-p:text-gray-300
                    prose-a:text-orange-500 prose-a:no-underline hover:prose-a:underline
                    prose-strong:text-gray-900 dark:prose-strong:text-white
                    prose-code:bg-gray-100 dark:prose-code:bg-dark-200 prose-code:px-1 prose-code:py-0.5 prose-code:rounded
                    prose-blockquote:border-orange-400 prose-blockquote:bg-orange-50 dark:prose-blockquote:bg-orange-500/5 prose-blockquote:rounded-r-lg prose-blockquote:py-1 prose-blockquote:px-4
                    prose-img:rounded-lg prose-img:border prose-img:border-gray-200 dark:prose-img:border-dark-200">
            {!! $post->content !!}
        </div>

        {{-- Mídias --}}
        @if($post->media->isNotEmpty())
        <div class="p-6 pt-0 space-y-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white border-t border-gray-100 dark:border-dark-200 pt-6">
                <i class="bi bi-paperclip me-1"></i> Anexos ({{ $post->media->count() }})
            </h3>

            {{-- Imagens --}}
            @if($post->images->isNotEmpty())
            <div class="space-y-4">
                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Imagens</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($post->images as $img)
                    <div class="group relative">
                        <a href="{{ $img->url }}" target="_blank" class="block">
                            <img src="{{ $img->url }}" alt="{{ $img->caption ?? $post->title }}"
                                 class="w-full rounded-lg border border-gray-200 dark:border-dark-200 object-cover max-h-64 hover:opacity-90 transition-opacity"
                                 onerror="this.parentElement.innerHTML='<div class=\'flex items-center justify-center h-32 bg-gray-100 dark:bg-dark-200 rounded-lg text-gray-400\'><i class=\'bi bi-image-alt text-2xl\'></i></div>'">
                        </a>
                        @if($img->caption)
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 text-center">{{ $img->caption }}</p>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Vídeos --}}
            @if($post->videos->isNotEmpty())
            <div class="space-y-4">
                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Vídeos</h4>
                <div class="space-y-4">
                    @foreach($post->videos as $vid)
                    <div class="rounded-lg border border-gray-200 dark:border-dark-200 overflow-hidden">
                        @if(str_contains($vid->url, 'youtube.com') || str_contains($vid->url, 'youtu.be'))
                            @php
                                preg_match('/(?:v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $vid->url, $m);
                                $ytId = $m[1] ?? '';
                            @endphp
                            @if($ytId)
                            <div class="relative w-full" style="padding-bottom: 56.25%;">
                                <iframe src="https://www.youtube.com/embed/{{ $ytId }}" frameborder="0" allowfullscreen
                                        class="absolute inset-0 w-full h-full"></iframe>
                            </div>
                            @endif
                        @else
                            <video controls class="w-full max-h-96">
                                <source src="{{ $vid->url }}">
                                Seu navegador não suporta vídeos.
                            </video>
                        @endif
                        @if($vid->caption)
                        <p class="px-4 py-2 text-xs text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-dark-200">{{ $vid->caption }}</p>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
        @endif
    </article>

    {{-- Posts relacionados --}}
    @if($relatedPosts->isNotEmpty())
    <div class="space-y-4">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Outros artigos em {{ $post->category->name }}</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            @foreach($relatedPosts as $related)
            <a href="{{ route('help.show', $related) }}" class="flex items-center gap-3 p-4 bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 hover:border-orange-300 dark:hover:border-orange-500/30 hover:shadow-md transition-all group">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0"
                     style="background-color: {{ $post->category->color }}15; color: {{ $post->category->color }}">
                    <i class="bi {{ $post->category->icon }} text-lg"></i>
                </div>
                <div class="min-w-0">
                    <p class="font-medium text-gray-900 dark:text-white group-hover:text-orange-500 transition-colors text-sm truncate">{{ $related->title }}</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500">{{ $related->updated_at->diffForHumans() }}</p>
                </div>
                <i class="bi bi-chevron-right text-gray-300 dark:text-gray-600 ml-auto shrink-0"></i>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Voltar --}}
    <div class="text-center">
        <a href="{{ route('help.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 hover:text-orange-500 transition-colors">
            <i class="bi bi-arrow-left"></i> Voltar para Central de Ajuda
        </a>
    </div>
</div>
@endsection
