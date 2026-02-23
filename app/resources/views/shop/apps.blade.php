@extends('layouts.app')

@section('title', 'Ativar Aplicativos')

@section('content')
<div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-6 md:mb-8 gap-4">
    <div>
        <h1 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
            <i class="bi bi-phone text-orange-500"></i>
            Ativar Aplicativos
        </h1>
        <p class="text-sm md:text-base text-gray-500 dark:text-gray-400 mt-1">Ative aplicativos para seus clientes.</p>
    </div>
</div>

<div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 shadow-sm dark:shadow-none p-12 text-center">
    <div class="max-w-md mx-auto">
        <div class="w-20 h-20 rounded-full bg-orange-100 dark:bg-orange-500/20 flex items-center justify-center mx-auto mb-6">
            <i class="bi bi-tools text-orange-500 text-4xl"></i>
        </div>
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Em Desenvolvimento</h2>
        <p class="text-gray-500 dark:text-gray-400 mb-6">
            Esta funcionalidade est&aacute; sendo desenvolvida e estar&aacute; dispon&iacute;vel em breve.
            Voc&ecirc; poder&aacute; ativar aplicativos IPTV diretamente pelo painel.
        </p>
        <div class="inline-flex items-center gap-2 px-4 py-2 bg-orange-50 dark:bg-orange-500/10 text-orange-600 dark:text-orange-400 rounded-lg text-sm font-medium">
            <i class="bi bi-clock-history"></i>
            Previs&atilde;o: Em breve
        </div>
    </div>
</div>
@endsection
