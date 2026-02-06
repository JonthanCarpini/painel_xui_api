@extends('layouts.app')

@section('title', 'Quadro de Avisos')

@section('content')
<div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-8 gap-4">
    <div>
        <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
            <i class="bi bi-pin-angle-fill text-red-500 rotate-45"></i>
            Quadro de Avisos
        </h1>
        <p class="text-gray-500 dark:text-gray-400 mt-2">Fique por dentro das novidades e comunicados importantes.</p>
    </div>
</div>

@if(count($notices) > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 p-4">
        @forelse($notices as $index => $notice)
            @php
                // Cores padrão baseadas no tipo
                $baseClasses = match($notice->type) {
                    'info' => 'bg-cyan-100 text-cyan-900 dark:bg-cyan-900/40 dark:text-cyan-100 border-cyan-200 dark:border-cyan-800',
                    'warning' => 'bg-yellow-100 text-yellow-900 dark:bg-yellow-900/40 dark:text-yellow-100 border-yellow-200 dark:border-yellow-800',
                    'danger' => 'bg-red-100 text-red-900 dark:bg-red-900/40 dark:text-red-100 border-red-200 dark:border-red-800',
                    'success' => 'bg-green-100 text-green-900 dark:bg-green-900/40 dark:text-green-100 border-green-200 dark:border-green-800',
                    default => 'bg-gray-100 text-gray-900 dark:bg-gray-800 dark:text-gray-100 border-gray-200 dark:border-gray-700'
                };
                
                // Se houver cor definida pelo admin
                $customStyle = '';
                if (!empty($notice->color)) {
                    if (str_starts_with($notice->color, '#')) {
                        // É Hex - Aplicar como inline style
                        // Adicionamos classes de texto genéricas para garantir legibilidade
                        $baseClasses = 'text-gray-900 border-gray-200 dark:text-gray-100 dark:border-gray-700'; 
                        $customStyle = "background-color: {$notice->color};";
                    } else {
                        // É Classe Tailwind
                        $baseClasses = $notice->color;
                    }
                }
                
                // Rotação aleatória leve para efeito realista (-2deg a 2deg)
                $rotation = ($index % 2 == 0) ? '-rotate-1' : 'rotate-1';
                if ($index % 3 == 0) $rotation = 'rotate-2';
                if ($index % 5 == 0) $rotation = '-rotate-2';
            @endphp

            <div class="relative group {{ $rotation }} hover:rotate-0 transition-transform duration-300 ease-in-out h-full">
                <!-- Sombra suave para dar profundidade -->
                <div class="absolute inset-0 bg-black/5 dark:bg-black/50 rounded-lg transform translate-y-2 translate-x-2 blur-sm -z-10"></div>
                
                <!-- O "Papel" -->
                <div class="{{ $baseClasses }} p-6 rounded-lg border shadow-sm h-full flex flex-col relative overflow-hidden" style="{{ $customStyle }}">
                    <!-- Tachinha/Pin -->
                    <div class="absolute -top-3 left-1/2 transform -translate-x-1/2 z-10">
                        <i class="bi bi-pin-fill text-red-500 text-2xl drop-shadow-md"></i>
                    </div>

                    <!-- Cabeçalho -->
                    <div class="mt-2 mb-4 border-b border-black/10 dark:border-white/10 pb-2">
                        <div class="flex justify-between items-start">
                            <h2 class="font-bold text-lg leading-tight">{{ $notice->title }}</h2>
                            @if($notice->type === 'danger' || $notice->type === 'warning')
                                <i class="bi bi-exclamation-triangle-fill text-xl opacity-70"></i>
                            @endif
                        </div>
                        <span class="text-xs opacity-70 mt-1 block font-mono">
                            {{ $notice->created_at->format('d/m/Y') }}
                        </span>
                    </div>
                    
                    <!-- Conteúdo -->
                    <div class="prose prose-sm dark:prose-invert max-w-none flex-grow font-medium opacity-90 whitespace-pre-wrap leading-relaxed">
                        {{ $notice->message }}
                    </div>
                </div>
            </div>
        @empty
            <!-- Não deve entrar aqui devido ao if count > 0, mas por segurança -->
        @endforelse
    </div>
@else
    <div class="flex flex-col items-center justify-center py-20 bg-gray-50 dark:bg-dark-300 rounded-xl border border-dashed border-gray-300 dark:border-dark-100">
        <div class="w-20 h-20 bg-gray-100 dark:bg-dark-200 rounded-full flex items-center justify-center mb-4 text-gray-400 dark:text-gray-500 transform -rotate-12">
            <i class="bi bi-stickies text-4xl"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Quadro Vazio</h3>
        <p class="text-gray-500 dark:text-gray-400 text-center max-w-sm">
            Nenhum aviso ou comunicado foi fixado no quadro até o momento.
        </p>
    </div>
@endif
@endsection
