@extends('layouts.app')
@section('title', 'Central de Ajuda - Categorias')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Categorias da Central de Ajuda</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1">Gerencie as categorias dos artigos de ajuda.</p>
        </div>
        <a href="{{ route('settings.help.posts') }}" class="px-4 py-2 bg-gray-200 dark:bg-dark-200 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-dark-100 transition-colors text-sm font-medium">
            <i class="bi bi-file-text me-1"></i> Ver Posts
        </a>
    </div>

    {{-- Alertas --}}
    @if(session('success'))
        <div class="bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/30 text-green-700 dark:text-green-400 p-4 rounded-lg">
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 text-red-700 dark:text-red-400 p-4 rounded-lg">
            @foreach($errors->all() as $error) <p>{{ $error }}</p> @endforeach
        </div>
    @endif

    {{-- Form Nova Categoria --}}
    <div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Nova Categoria</h2>
        <form action="{{ route('settings.help.categories.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
            @csrf
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nome</label>
                <input type="text" name="name" required class="w-full px-3 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white text-sm focus:ring-orange-500 focus:border-orange-500" placeholder="Ex: Aplicativos">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ícone (Bootstrap)</label>
                <input type="text" name="icon" value="bi-folder" class="w-full px-3 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white text-sm focus:ring-orange-500 focus:border-orange-500" placeholder="bi-folder">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cor</label>
                <input type="color" name="color" value="#f97316" class="w-full h-[38px] px-1 py-1 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg cursor-pointer">
            </div>
            <div>
                <button type="submit" class="w-full px-4 py-2 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg hover:shadow-lg transition-all text-sm font-medium">
                    <i class="bi bi-plus-lg me-1"></i> Criar
                </button>
            </div>
        </form>
    </div>

    {{-- Lista de Categorias --}}
    <div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-dark-200">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Categorias ({{ $categories->count() }})</h2>
        </div>

        @forelse($categories as $cat)
        <div class="px-6 py-4 border-b border-gray-100 dark:border-dark-200 last:border-b-0 hover:bg-gray-50 dark:hover:bg-dark-200/50 transition-colors" x-data="{ editing: false }">
            {{-- Modo visualização --}}
            <div x-show="!editing" class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background-color: {{ $cat->color }}20; color: {{ $cat->color }}">
                        <i class="bi {{ $cat->icon }} text-xl"></i>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $cat->name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $cat->posts_count }} posts &middot; Ordem: {{ $cat->sort_order }}</p>
                    </div>
                    @if(!$cat->is_active)
                        <span class="px-2 py-0.5 bg-red-100 dark:bg-red-500/20 text-red-600 dark:text-red-400 text-xs rounded-full font-medium">Inativa</span>
                    @endif
                </div>
                <div class="flex items-center gap-2">
                    <button @click="editing = true" class="px-3 py-1.5 bg-blue-100 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400 rounded-lg text-xs font-medium hover:bg-blue-200 dark:hover:bg-blue-500/30 transition-colors">
                        <i class="bi bi-pencil"></i> Editar
                    </button>
                    <form action="{{ route('settings.help.categories.destroy', $cat) }}" method="POST" onsubmit="return confirm('Remover esta categoria e todos os seus posts?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="px-3 py-1.5 bg-red-100 dark:bg-red-500/20 text-red-600 dark:text-red-400 rounded-lg text-xs font-medium hover:bg-red-200 dark:hover:bg-red-500/30 transition-colors">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </div>
            </div>

            {{-- Modo edição --}}
            <div x-show="editing" x-cloak>
                <form action="{{ route('settings.help.categories.update', $cat) }}" method="POST" class="grid grid-cols-1 md:grid-cols-6 gap-3 items-end">
                    @csrf @method('PUT')
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Nome</label>
                        <input type="text" name="name" value="{{ $cat->name }}" required class="w-full px-3 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Ícone</label>
                        <input type="text" name="icon" value="{{ $cat->icon }}" class="w-full px-3 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Cor</label>
                        <input type="color" name="color" value="{{ $cat->color }}" class="w-full h-[38px] px-1 py-1 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Ordem</label>
                        <div class="flex gap-2">
                            <input type="number" name="sort_order" value="{{ $cat->sort_order }}" class="w-full px-3 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white text-sm">
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                            <input type="checkbox" name="is_active" value="1" {{ $cat->is_active ? 'checked' : '' }} class="rounded border-gray-300 dark:border-dark-100 text-orange-500 focus:ring-orange-500">
                            Ativa
                        </label>
                        <button type="submit" class="px-3 py-2 bg-green-500 text-white rounded-lg text-xs font-medium hover:bg-green-600 transition-colors">
                            <i class="bi bi-check-lg"></i>
                        </button>
                        <button type="button" @click="editing = false" class="px-3 py-2 bg-gray-200 dark:bg-dark-100 text-gray-600 dark:text-gray-400 rounded-lg text-xs font-medium hover:bg-gray-300 dark:hover:bg-dark-50 transition-colors">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @empty
        <div class="px-6 py-12 text-center">
            <i class="bi bi-folder-plus text-4xl text-gray-300 dark:text-gray-600"></i>
            <p class="text-gray-500 dark:text-gray-400 mt-2">Nenhuma categoria criada ainda.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
