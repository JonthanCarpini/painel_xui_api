@extends('layouts.app')

@section('title', 'Criar Revendedor')

@section('content')
<div class="flex items-center justify-between mb-8">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
        <i class="bi bi-plus-circle text-orange-500"></i>
        Criar Novo Revendedor
    </h1>
    <a href="{{ route('resellers.index') }}" class="px-4 py-2 bg-gray-100 dark:bg-dark-300 border border-gray-200 dark:border-dark-200 text-gray-600 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-dark-200 transition-colors flex items-center gap-2 font-medium">
        <i class="bi bi-arrow-left"></i>
        Voltar
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 p-6 shadow-sm dark:shadow-none">
            <form action="{{ route('resellers.store') }}" method="POST">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">Usu&aacute;rio *</label>
                        <input type="text" name="username" value="{{ old('username') }}" required minlength="3" maxlength="50" class="w-full px-4 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white focus:border-orange-500 focus:outline-none transition-colors" placeholder="Digite o usu&aacute;rio">
                        <p class="text-xs text-gray-500 mt-1">M&iacute;nimo 3 caracteres</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">Senha *</label>
                        <input type="text" name="password" value="{{ old('password') }}" required minlength="6" class="w-full px-4 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white focus:border-orange-500 focus:outline-none transition-colors" placeholder="Digite a senha">
                        <p class="text-xs text-gray-500 mt-1">M&iacute;nimo 6 caracteres</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">E-mail (Opcional)</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="w-full px-4 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white focus:border-orange-500 focus:outline-none transition-colors" placeholder="email@exemplo.com">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">Cr&eacute;ditos Iniciais *</label>
                        <input type="number" name="credits" value="{{ old('credits', 0) }}" step="0.01" min="0" required class="w-full px-4 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white focus:border-orange-500 focus:outline-none transition-colors" placeholder="0.00">
                        <p class="text-xs text-gray-500 mt-1">Saldo inicial do revendedor</p>
                    </div>
                </div>

                <div class="mb-6 p-4 bg-yellow-50 dark:bg-yellow-500/10 border border-yellow-200 dark:border-yellow-500/50 rounded-lg">
                    <p class="text-yellow-700 dark:text-yellow-400 text-sm flex items-start gap-2">
                        <i class="bi bi-exclamation-triangle-fill mt-0.5"></i>
                        <span><strong>Aten&ccedil;&atilde;o:</strong> O revendedor ser&aacute; criado com permiss&otilde;es para criar clientes e sub-revendedores.</span>
                    </p>
                </div>

                <div class="flex gap-3">
                    <button type="submit" class="flex-1 px-6 py-3 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg hover:shadow-lg hover:shadow-orange-500/20 transition-all flex items-center justify-center gap-2 font-medium">
                        <i class="bi bi-check-circle"></i>
                        Criar Revendedor
                    </button>
                    <a href="{{ route('resellers.index') }}" class="px-6 py-3 bg-gray-100 dark:bg-dark-200 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-dark-100 transition-colors font-medium">Cancelar</a>
                </div>
            </form>
        </div>
    </div>

    <div class="space-y-6">
        <div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 p-6 shadow-sm dark:shadow-none">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <i class="bi bi-info-circle text-orange-500"></i>
                Sobre Revendedores
            </h3>
            <div class="space-y-4">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Permiss&otilde;es</p>
                    <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                        <li class="flex items-start gap-2">
                            <i class="bi bi-check text-green-500 mt-0.5"></i>
                            <span>Criar e gerenciar clientes</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="bi bi-check text-green-500 mt-0.5"></i>
                            <span>Gerar testes gratuitos</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="bi bi-check text-green-500 mt-0.5"></i>
                            <span>Monitorar conex&otilde;es</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="bi bi-check text-green-500 mt-0.5"></i>
                            <span>Visualizar relat&oacute;rios</span>
                        </li>
                    </ul>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Restri&ccedil;&otilde;es</p>
                    <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                        <li class="flex items-start gap-2">
                            <i class="bi bi-x text-red-500 mt-0.5"></i>
                            <span>N&atilde;o pode criar revendedores</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="bi bi-x text-red-500 mt-0.5"></i>
                            <span>N&atilde;o pode alterar configura&ccedil;&otilde;es</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="bi bi-x text-red-500 mt-0.5"></i>
                            <span>Cr&eacute;ditos limitados ao saldo</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 p-6 shadow-sm dark:shadow-none">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <i class="bi bi-lightbulb text-orange-500"></i>
                Dica
            </h3>
            <p class="text-gray-600 dark:text-gray-400 text-sm leading-relaxed">
                Defina cr&eacute;ditos iniciais adequados para que o revendedor possa come&ccedil;ar a trabalhar imediatamente. Voc&ecirc; pode recarregar depois.
            </p>
        </div>
    </div>
</div>
@endsection
