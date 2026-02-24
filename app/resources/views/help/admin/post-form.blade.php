@extends('layouts.app')
@section('title', $post ? 'Editar Post' : 'Novo Post')

@push('styles')
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<style>
    .ql-editor { min-height: 200px; font-size: 14px; }
    .dark .ql-toolbar { border-color: #2D3748 !important; background: #1A202C; }
    .dark .ql-container { border-color: #2D3748 !important; background: #1A202C; color: #e2e8f0; }
    .dark .ql-editor.ql-blank::before { color: #718096; }
    .dark .ql-toolbar .ql-stroke { stroke: #a0aec0; }
    .dark .ql-toolbar .ql-fill { fill: #a0aec0; }
    .dark .ql-toolbar .ql-picker-label { color: #a0aec0; }
    .dark .ql-toolbar .ql-picker-options { background: #1A202C; border-color: #2D3748; }
    .dark .ql-toolbar button:hover .ql-stroke { stroke: #f97316; }
    .dark .ql-toolbar button.ql-active .ql-stroke { stroke: #f97316; }
</style>
@endpush

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="postForm()">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $post ? 'Editar Post' : 'Novo Post' }}</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1">{{ $post ? 'Atualize o conteúdo do artigo.' : 'Crie um novo artigo de ajuda.' }}</p>
        </div>
        <a href="{{ route('settings.help.posts') }}" class="px-4 py-2 bg-gray-200 dark:bg-dark-200 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-dark-100 transition-colors text-sm font-medium">
            <i class="bi bi-arrow-left me-1"></i> Voltar
        </a>
    </div>

    {{-- Alertas --}}
    @if($errors->any())
        <div class="bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 text-red-700 dark:text-red-400 p-4 rounded-lg">
            @foreach($errors->all() as $error) <p>{{ $error }}</p> @endforeach
        </div>
    @endif

    {{-- Formulário --}}
    <form method="POST"
          action="{{ $post ? route('settings.help.posts.update', $post) : route('settings.help.posts.store') }}"
          id="postForm">
        @csrf
        @if($post) @method('PUT') @endif

        <div class="space-y-6">
            {{-- Info básica --}}
            <div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 p-6 space-y-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Informações do Post</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Título *</label>
                        <input type="text" name="title" value="{{ old('title', $post->title ?? '') }}" required
                               class="w-full px-3 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white text-sm focus:ring-orange-500 focus:border-orange-500"
                               placeholder="Ex: Como configurar o aplicativo SmartOne">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Categoria *</label>
                        <select name="help_category_id" required class="w-full px-3 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white text-sm focus:ring-orange-500 focus:border-orange-500">
                            <option value="">Selecione...</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('help_category_id', $post->help_category_id ?? '') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ordem</label>
                        <input type="number" name="sort_order" value="{{ old('sort_order', $post->sort_order ?? 0) }}"
                               class="w-full px-3 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white text-sm focus:ring-orange-500 focus:border-orange-500">
                    </div>
                    <div class="flex items-end pb-1">
                        <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1"
                                   {{ old('is_active', $post ? $post->is_active : true) ? 'checked' : '' }}
                                   class="rounded border-gray-300 dark:border-dark-100 text-orange-500 focus:ring-orange-500">
                            Post ativo (visível para revendedores)
                        </label>
                    </div>
                </div>
            </div>

            {{-- Conteúdo (editor rico) --}}
            <div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 p-6 space-y-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Conteúdo *</h2>
                <input type="hidden" name="content" id="contentInput">
                <div id="quillEditor">{!! old('content', $post->content ?? '') !!}</div>
            </div>

            {{-- Mídias --}}
            <div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Mídias (Fotos & Vídeos)</h2>
                    <button type="button" @click="addMedia()"
                            class="px-3 py-1.5 bg-orange-100 dark:bg-orange-500/20 text-orange-600 dark:text-orange-400 rounded-lg text-xs font-medium hover:bg-orange-200 dark:hover:bg-orange-500/30 transition-colors">
                        <i class="bi bi-plus-lg me-1"></i> Adicionar Mídia
                    </button>
                </div>

                <template x-for="(item, index) in mediaItems" :key="index">
                    <div class="flex flex-wrap gap-3 items-start p-4 bg-gray-50 dark:bg-dark-200 rounded-lg border border-gray-200 dark:border-dark-100">
                        <div class="min-w-[120px]">
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Tipo</label>
                            <select :name="'media['+index+'][type]'" x-model="item.type"
                                    class="w-full px-3 py-2 bg-white dark:bg-dark-300 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white text-sm">
                                <option value="image">Imagem</option>
                                <option value="video">Vídeo</option>
                            </select>
                        </div>
                        <div class="flex-1 min-w-[250px]">
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">URL *</label>
                            <input type="url" :name="'media['+index+'][url]'" x-model="item.url" required
                                   class="w-full px-3 py-2 bg-white dark:bg-dark-300 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white text-sm"
                                   placeholder="https://...">
                        </div>
                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Legenda</label>
                            <input type="text" :name="'media['+index+'][caption]'" x-model="item.caption"
                                   class="w-full px-3 py-2 bg-white dark:bg-dark-300 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white text-sm"
                                   placeholder="Descrição da mídia">
                        </div>
                        <div class="pt-5">
                            <button type="button" @click="removeMedia(index)"
                                    class="px-3 py-2 bg-red-100 dark:bg-red-500/20 text-red-600 dark:text-red-400 rounded-lg text-sm hover:bg-red-200 dark:hover:bg-red-500/30 transition-colors">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>

                        {{-- Preview --}}
                        <div class="w-full mt-2" x-show="item.url">
                            <template x-if="item.type === 'image' && item.url">
                                <img :src="item.url" class="max-h-32 rounded-lg border border-gray-200 dark:border-dark-100 object-contain" @error="$el.style.display='none'">
                            </template>
                            <template x-if="item.type === 'video' && item.url">
                                <p class="text-xs text-gray-400"><i class="bi bi-play-circle me-1"></i> <span x-text="item.url"></span></p>
                            </template>
                        </div>
                    </div>
                </template>

                <div x-show="mediaItems.length === 0" class="text-center py-6 text-gray-400 dark:text-gray-500 text-sm">
                    <i class="bi bi-image text-2xl"></i>
                    <p class="mt-1">Nenhuma mídia adicionada. Clique em "Adicionar Mídia" para incluir fotos ou vídeos.</p>
                </div>
            </div>

            {{-- Submit --}}
            <div class="flex justify-end gap-3">
                <a href="{{ route('settings.help.posts') }}" class="px-6 py-2.5 bg-gray-200 dark:bg-dark-200 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-dark-100 transition-colors font-medium">
                    Cancelar
                </a>
                <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg hover:shadow-lg transition-all font-medium">
                    <i class="bi bi-check-lg me-1"></i> {{ $post ? 'Atualizar Post' : 'Criar Post' }}
                </button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script>
function postForm() {
    return {
        mediaItems: @json($post ? $post->media->map(fn($m) => ['type' => $m->type, 'url' => $m->url, 'caption' => $m->caption])->toArray() : []),
        addMedia() {
            this.mediaItems.push({ type: 'image', url: '', caption: '' });
        },
        removeMedia(index) {
            this.mediaItems.splice(index, 1);
        }
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const quill = new Quill('#quillEditor', {
        theme: 'snow',
        placeholder: 'Escreva as instruções aqui...',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'indent': '-1'}, { 'indent': '+1' }],
                ['blockquote', 'code-block'],
                ['link', 'image'],
                [{ 'align': [] }],
                ['clean']
            ]
        }
    });

    document.getElementById('postForm').addEventListener('submit', function () {
        document.getElementById('contentInput').value = quill.root.innerHTML;
    });
});
</script>
@endpush

@endsection
