@extends('layouts.app')

@section('title', 'Editar Canal')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Editar Canal #{{ $channel->id }}</h1>
        <a href="{{ route('settings.admin.channels.index') }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>

    <div class="bg-white dark:bg-dark-300 rounded-xl shadow-sm border border-gray-200 dark:border-dark-200 p-6">
        <form action="{{ route('settings.admin.channels.update', $channel->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nome -->
                <div class="col-span-2">
                    <label for="stream_display_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nome do Canal</label>
                    <input type="text" name="stream_display_name" id="stream_display_name" value="{{ old('stream_display_name', $channel->stream_display_name) }}" class="w-full rounded-lg border-gray-300 dark:border-dark-100 dark:bg-dark-200 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Fonte -->
                <div class="col-span-2">
                    <label for="stream_source" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fonte (URL)</label>
                    <textarea name="stream_source" id="stream_source" rows="3" class="w-full rounded-lg border-gray-300 dark:border-dark-100 dark:bg-dark-200 focus:ring-blue-500 focus:border-blue-500 font-mono text-sm">{{ old('stream_source', $channel->stream_source) }}</textarea>
                    <p class="text-xs text-gray-500 mt-1">Insira múltiplas fontes separadas por JSON ou conforme padrão do sistema.</p>
                </div>

                <!-- Categoria -->
                <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Categoria</label>
                    <select name="category_id" id="category_id" class="w-full rounded-lg border-gray-300 dark:border-dark-100 dark:bg-dark-200 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Sem Categoria</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ (old('category_id', $channel->category_id) == $cat->id) ? 'selected' : '' }}>
                                {{ $cat->category_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Servidor -->
                <div>
                    <label for="server_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Servidor (Load Balancer)</label>
                    <select name="server_id" id="server_id" class="w-full rounded-lg border-gray-300 dark:border-dark-100 dark:bg-dark-200 focus:ring-blue-500 focus:border-blue-500">
                        @foreach($servers as $srv)
                            <option value="{{ $srv->id }}" {{ (old('server_id', $streamServer->server_id ?? '') == $srv->id) ? 'selected' : '' }}>
                                {{ $srv->server_name }} ({{ $srv->server_ip }})
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-orange-500 mt-1">Alterar o servidor exigirá reinício do canal.</p>
                </div>

                <!-- Notas -->
                <div class="col-span-2">
                    <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notas</label>
                    <textarea name="notes" id="notes" rows="2" class="w-full rounded-lg border-gray-300 dark:border-dark-100 dark:bg-dark-200 focus:ring-blue-500 focus:border-blue-500">{{ old('notes', $channel->notes) }}</textarea>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-200 dark:border-dark-100">
                <a href="{{ route('settings.admin.channels.index') }}" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">Cancelar</a>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2">
                    <i class="bi bi-save"></i> Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
