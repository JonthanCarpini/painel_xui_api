@extends('layouts.app')

@section('title', 'Novo Ticket')

@section('content')
@section('content')
<div class="max-w-3xl mx-auto">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 sm:mb-8 gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <i class="bi bi-plus-circle text-orange-500"></i>
                Novo Ticket
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Descreva seu problema ou dúvida detalhadamente.</p>
        </div>
        <a href="{{ route('tickets.index') }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 font-medium flex items-center gap-2 transition-colors text-sm sm:text-base">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>

    <div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 shadow-sm overflow-hidden">
        <form action="{{ route('tickets.store') }}" method="POST" class="p-4 sm:p-8 space-y-6">
            @csrf

            <div>
                <label for="category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Categoria</label>
                <select id="category_id" name="category_id" required
                    class="w-full bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg px-4 py-3 text-gray-900 dark:text-white focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500 transition-colors text-sm sm:text-base">
                    <option value="">Selecione uma categoria...</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Assunto</label>
                <input type="text" id="title" name="title" value="{{ old('title') }}" required
                    class="w-full bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg px-4 py-3 text-gray-900 dark:text-white focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500 transition-colors text-sm sm:text-base"
                    placeholder="Ex: Problema com renovação de cliente">
            </div>

            <div>
                <label for="message" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Mensagem</label>
                <textarea id="message" name="message" rows="6" required
                    class="w-full bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg px-4 py-3 text-gray-900 dark:text-white focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500 transition-colors resize-none text-sm sm:text-base"
                    placeholder="Descreva o que está acontecendo...">{{ old('message') }}</textarea>
            </div>

            <div class="pt-4 border-t border-gray-100 dark:border-dark-200 flex justify-end">
                <button type="submit" class="w-full sm:w-auto bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white font-bold py-3 px-8 rounded-xl shadow-lg shadow-orange-500/20 transition-all duration-200 flex items-center justify-center gap-2 transform hover:scale-[1.02]">
                    <i class="bi bi-send"></i>
                    Enviar Ticket
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
