@extends('layouts.app')

@section('title', 'Em Manutenção')

@section('content')
<div class="min-h-[60vh] flex flex-col items-center justify-center text-center p-4">
    <div class="bg-white dark:bg-dark-300 p-8 rounded-2xl shadow-lg max-w-lg w-full border border-gray-200 dark:border-dark-200">
        <div class="mb-6">
            <div class="w-20 h-20 bg-orange-100 dark:bg-orange-500/10 rounded-full flex items-center justify-center mx-auto">
                <i class="bi bi-cone-striped text-4xl text-orange-500"></i>
            </div>
        </div>
        
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Sistema em Manutenção</h1>
        
        <p class="text-gray-600 dark:text-gray-400 mb-8">
            {{ $message ?? 'Estamos realizando melhorias no sistema. Por favor, aguarde alguns instantes e tente novamente.' }}
        </p>

        <div class="flex justify-center gap-4">
            <a href="{{ route('login') }}" class="px-6 py-2.5 bg-gray-100 dark:bg-dark-200 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-dark-100 transition-colors font-medium">
                Voltar ao Login
            </a>
            
            <button onclick="window.location.reload()" class="px-6 py-2.5 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors font-medium shadow-lg shadow-orange-500/20">
                Tentar Novamente
            </button>
        </div>
    </div>
    
    <p class="mt-8 text-sm text-gray-500 dark:text-gray-500">
        &copy; {{ date('Y') }} {{ config('app.name') }}. Todos os direitos reservados.
    </p>
</div>
@endsection
