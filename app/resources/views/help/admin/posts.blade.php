@extends('layouts.app')
@section('title', 'Central de Ajuda - Posts')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Posts da Central de Ajuda</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1">Gerencie os artigos e instruções para revendedores.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('help.admin.categories') }}" class="px-4 py-2 bg-gray-200 dark:bg-dark-200 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-dark-100 transition-colors text-sm font-medium">
                <i class="bi bi-folder me-1"></i> Categorias
            </a>
            <a href="{{ route('help.admin.posts.create') }}" class="px-4 py-2 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg hover:shadow-lg transition-all text-sm font-medium">
                <i class="bi bi-plus-lg me-1"></i> Novo Post
            </a>
        </div>
    </div>

    {{-- Alertas --}}
    @if(session('success'))
        <div class="bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/30 text-green-700 dark:text-green-400 p-4 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    {{-- Filtros --}}
    <div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 p-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Buscar</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por título..." class="w-full px-3 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white text-sm">
            </div>
            <div class="min-w-[180px]">
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Categoria</label>
                <select name="category_id" class="w-full px-3 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white text-sm">
                    <option value="">Todas</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-800 dark:bg-dark-100 text-white rounded-lg text-sm font-medium hover:bg-gray-700 dark:hover:bg-dark-50 transition-colors">
                <i class="bi bi-search me-1"></i> Filtrar
            </button>
            @if(request()->hasAny(['search', 'category_id']))
                <a href="{{ route('help.admin.posts') }}" class="px-4 py-2 bg-gray-200 dark:bg-dark-200 text-gray-600 dark:text-gray-400 rounded-lg text-sm font-medium hover:bg-gray-300 transition-colors">Limpar</a>
            @endif
        </form>
    </div>

    {{-- Tabela de Posts --}}
    <div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 dark:bg-dark-200">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Ordem</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Título</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Categoria</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-dark-200">
                @forelse($posts as $post)
                <tr class="hover:bg-gray-50 dark:hover:bg-dark-200/50 transition-colors">
                    <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 font-mono">{{ $post->sort_order }}</td>
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-900 dark:text-white text-sm">{{ $post->title }}</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ Str::limit(strip_tags($post->content), 80) }}</p>
                    </td>
                    <td class="px-4 py-3">
                        @if($post->category)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium" style="background-color: {{ $post->category->color }}15; color: {{ $post->category->color }}">
                            <i class="bi {{ $post->category->icon }}"></i> {{ $post->category->name }}
                        </span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if($post->is_active)
                            <span class="px-2 py-0.5 bg-green-100 dark:bg-green-500/20 text-green-600 dark:text-green-400 text-xs rounded-full font-medium">Ativo</span>
                        @else
                            <span class="px-2 py-0.5 bg-red-100 dark:bg-red-500/20 text-red-600 dark:text-red-400 text-xs rounded-full font-medium">Inativo</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('help.admin.posts.edit', $post) }}" class="px-3 py-1.5 bg-blue-100 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400 rounded-lg text-xs font-medium hover:bg-blue-200 dark:hover:bg-blue-500/30 transition-colors">
                                <i class="bi bi-pencil"></i> Editar
                            </a>
                            <form action="{{ route('help.admin.posts.destroy', $post) }}" method="POST" onsubmit="return confirm('Remover este post?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="px-3 py-1.5 bg-red-100 dark:bg-red-500/20 text-red-600 dark:text-red-400 rounded-lg text-xs font-medium hover:bg-red-200 dark:hover:bg-red-500/30 transition-colors">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-12 text-center">
                        <i class="bi bi-file-earmark-plus text-4xl text-gray-300 dark:text-gray-600"></i>
                        <p class="text-gray-500 dark:text-gray-400 mt-2">Nenhum post criado ainda.</p>
                        <a href="{{ route('help.admin.posts.create') }}" class="inline-block mt-3 px-4 py-2 bg-orange-500 text-white rounded-lg text-sm font-medium hover:bg-orange-600 transition-colors">
                            <i class="bi bi-plus-lg me-1"></i> Criar primeiro post
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($posts->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 dark:border-dark-200">
            {{ $posts->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
