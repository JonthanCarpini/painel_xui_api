@extends('layouts.app')
@section('title', 'Central de Ajuda - Categorias')

@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="categoryPage()">

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
        <form action="{{ route('settings.help.categories.store') }}" method="POST" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nome *</label>
                    <input type="text" name="name" required class="w-full px-3 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white text-sm focus:ring-orange-500 focus:border-orange-500" placeholder="Ex: Aplicativos">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cor</label>
                    <input type="color" name="color" value="#f97316" class="w-full h-[38px] px-1 py-1 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg cursor-pointer">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full px-4 py-2 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg hover:shadow-lg transition-all text-sm font-medium">
                        <i class="bi bi-plus-lg me-1"></i> Criar Categoria
                    </button>
                </div>
            </div>

            {{-- Seletor Visual de Ícones --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Escolha um Ícone</label>
                <input type="hidden" name="icon" :value="newIcon">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center bg-orange-100 dark:bg-orange-500/20 text-orange-500 border-2 border-orange-500 shrink-0">
                        <i class="bi text-2xl" :class="newIcon"></i>
                    </div>
                    <span class="text-sm text-gray-500 dark:text-gray-400">Selecionado: <strong class="text-gray-900 dark:text-white" x-text="newIcon"></strong></span>
                </div>
                <div class="grid grid-cols-8 sm:grid-cols-10 md:grid-cols-14 lg:grid-cols-18 gap-1.5 max-h-48 overflow-y-auto p-2 bg-gray-50 dark:bg-dark-200 rounded-lg border border-gray-200 dark:border-dark-100">
                    <template x-for="icon in icons" :key="icon">
                        <button type="button" @click="newIcon = icon"
                                :class="newIcon === icon ? 'bg-orange-500 text-white ring-2 ring-orange-400 shadow-lg' : 'bg-white dark:bg-dark-300 text-gray-600 dark:text-gray-400 hover:bg-orange-50 dark:hover:bg-orange-500/10 hover:text-orange-500'"
                                class="w-9 h-9 rounded-lg flex items-center justify-center transition-all duration-150 border border-gray-200 dark:border-dark-100 cursor-pointer">
                            <i class="bi text-base" :class="icon"></i>
                        </button>
                    </template>
                </div>
            </div>
        </form>
    </div>

    {{-- Lista de Categorias --}}
    <div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-dark-200">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Categorias ({{ $categories->count() }})</h2>
        </div>

        @forelse($categories as $cat)
        <div class="px-6 py-4 border-b border-gray-100 dark:border-dark-200 last:border-b-0 hover:bg-gray-50 dark:hover:bg-dark-200/50 transition-colors" x-data="{ editing: false, editIcon: '{{ $cat->icon }}' }">
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
                <form action="{{ route('settings.help.categories.update', $cat) }}" method="POST" class="space-y-4">
                    @csrf @method('PUT')
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Nome</label>
                            <input type="text" name="name" value="{{ $cat->name }}" required class="w-full px-3 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Cor</label>
                            <input type="color" name="color" value="{{ $cat->color }}" class="w-full h-[38px] px-1 py-1 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Ordem</label>
                            <input type="number" name="sort_order" value="{{ $cat->sort_order }}" class="w-full px-3 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white text-sm">
                        </div>
                        <div class="flex items-center gap-2">
                            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                <input type="checkbox" name="is_active" value="1" {{ $cat->is_active ? 'checked' : '' }} class="rounded border-gray-300 dark:border-dark-100 text-orange-500 focus:ring-orange-500">
                                Ativa
                            </label>
                            <button type="submit" class="px-3 py-2 bg-green-500 text-white rounded-lg text-xs font-medium hover:bg-green-600 transition-colors">
                                <i class="bi bi-check-lg"></i> Salvar
                            </button>
                            <button type="button" @click="editing = false" class="px-3 py-2 bg-gray-200 dark:bg-dark-100 text-gray-600 dark:text-gray-400 rounded-lg text-xs font-medium hover:bg-gray-300 dark:hover:bg-dark-50 transition-colors">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                    </div>
                    {{-- Seletor de ícone na edição --}}
                    <div>
                        <input type="hidden" name="icon" :value="editIcon">
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">Ícone</label>
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-10 h-10 rounded-lg flex items-center justify-center border-2 border-orange-500" style="background-color: {{ $cat->color }}20; color: {{ $cat->color }}">
                                <i class="bi text-xl" :class="editIcon"></i>
                            </div>
                            <span class="text-xs text-gray-400" x-text="editIcon"></span>
                        </div>
                        <div class="grid grid-cols-10 sm:grid-cols-14 md:grid-cols-18 gap-1 max-h-32 overflow-y-auto p-2 bg-gray-50 dark:bg-dark-200 rounded-lg border border-gray-200 dark:border-dark-100">
                            <template x-for="icon in icons" :key="'edit-{{ $cat->id }}-'+icon">
                                <button type="button" @click="editIcon = icon"
                                        :class="editIcon === icon ? 'bg-orange-500 text-white ring-2 ring-orange-400' : 'bg-white dark:bg-dark-300 text-gray-600 dark:text-gray-400 hover:bg-orange-50 dark:hover:bg-orange-500/10 hover:text-orange-500'"
                                        class="w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-150 border border-gray-200 dark:border-dark-100 cursor-pointer">
                                    <i class="bi text-sm" :class="icon"></i>
                                </button>
                            </template>
                        </div>
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

@push('scripts')
<script>
function categoryPage() {
    return {
        newIcon: 'bi-folder',
        icons: [
            'bi-phone', 'bi-tablet', 'bi-laptop', 'bi-tv', 'bi-display', 'bi-pc-display',
            'bi-router', 'bi-wifi', 'bi-globe', 'bi-globe2', 'bi-link-45deg', 'bi-ethernet',
            'bi-app', 'bi-app-indicator', 'bi-window', 'bi-terminal', 'bi-code-slash', 'bi-gear',
            'bi-tools', 'bi-wrench', 'bi-sliders', 'bi-toggles', 'bi-cpu', 'bi-hdd',
            'bi-play-circle', 'bi-play-btn', 'bi-film', 'bi-camera-video', 'bi-music-note', 'bi-broadcast',
            'bi-cast', 'bi-collection-play', 'bi-disc', 'bi-headphones', 'bi-speaker', 'bi-volume-up',
            'bi-image', 'bi-images', 'bi-camera', 'bi-palette', 'bi-brush', 'bi-easel',
            'bi-person', 'bi-people', 'bi-person-circle', 'bi-person-badge', 'bi-person-plus', 'bi-person-gear',
            'bi-shield-check', 'bi-shield-lock', 'bi-lock', 'bi-unlock', 'bi-key', 'bi-fingerprint',
            'bi-credit-card', 'bi-wallet2', 'bi-cash', 'bi-currency-dollar', 'bi-receipt', 'bi-bag',
            'bi-cart', 'bi-shop', 'bi-basket', 'bi-gift', 'bi-tag', 'bi-tags',
            'bi-chat', 'bi-chat-dots', 'bi-chat-text', 'bi-envelope', 'bi-send', 'bi-megaphone',
            'bi-bell', 'bi-bell-fill', 'bi-alarm', 'bi-clock', 'bi-calendar', 'bi-calendar-check',
            'bi-question-circle', 'bi-info-circle', 'bi-exclamation-circle', 'bi-exclamation-triangle', 'bi-check-circle', 'bi-x-circle',
            'bi-star', 'bi-star-fill', 'bi-heart', 'bi-bookmark', 'bi-flag', 'bi-trophy',
            'bi-lightning', 'bi-lightning-charge', 'bi-fire', 'bi-rocket', 'bi-magic', 'bi-bullseye',
            'bi-folder', 'bi-folder2', 'bi-file-earmark', 'bi-file-text', 'bi-file-pdf', 'bi-file-zip',
            'bi-cloud', 'bi-cloud-download', 'bi-cloud-upload', 'bi-download', 'bi-upload', 'bi-share',
            'bi-box', 'bi-archive', 'bi-database', 'bi-server', 'bi-diagram-3', 'bi-layers',
            'bi-map', 'bi-pin-map', 'bi-compass', 'bi-signpost', 'bi-house', 'bi-building',
            'bi-whatsapp', 'bi-telegram', 'bi-facebook', 'bi-instagram', 'bi-youtube', 'bi-android',
            'bi-apple', 'bi-windows', 'bi-google', 'bi-amazon', 'bi-spotify', 'bi-twitch',
            'bi-qr-code', 'bi-upc-scan', 'bi-printer', 'bi-clipboard', 'bi-journal', 'bi-book',
            'bi-mortarboard', 'bi-lightbulb', 'bi-puzzle', 'bi-joystick', 'bi-controller', 'bi-headset',
        ]
    }
}
</script>
@endpush

@endsection
