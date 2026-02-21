@extends('layouts.app')

@section('title', 'Editar Revendedor')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="bg-white dark:bg-dark-300 rounded-xl p-8 border-2 border-orange-500/50 shadow-sm dark:shadow-none">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <i class="bi bi-pencil text-orange-500"></i>
                Editar Revendedor
            </h2>
            <a href="{{ route('resellers.index') }}" class="text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                <i class="bi bi-x-lg text-2xl"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2">
                <div class="bg-gray-50 dark:bg-dark-200 rounded-xl border border-gray-200 dark:border-dark-100 p-6">
                    <form action="{{ route('resellers.update', $reseller['id']) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">Usu&aacute;rio</label>
                            <input type="text" value="{{ $reseller['username'] }}" readonly class="w-full px-4 py-2 bg-gray-100 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-500 dark:text-gray-400 cursor-not-allowed">
                            <p class="text-xs text-gray-500 mt-1">O usu&aacute;rio n&atilde;o pode ser alterado</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">Nova Senha (Opcional)</label>
                                <input type="text" name="password" class="w-full px-4 py-2 bg-white dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white focus:border-orange-500 focus:outline-none transition-colors" placeholder="Digite a nova senha" minlength="6">
                                <p class="text-xs text-gray-500 mt-1">Deixe em branco para manter a senha atual</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">E-mail (Opcional)</label>
                                <input type="email" name="email" value="{{ old('email', $reseller['email'] ?? '') }}" class="w-full px-4 py-2 bg-white dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white focus:border-orange-500 focus:outline-none transition-colors" placeholder="email@exemplo.com">
                            </div>
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">Status *</label>
                            <select name="status" required class="w-full px-4 py-2 bg-white dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white focus:border-orange-500 focus:outline-none transition-colors">
                                <option value="1" {{ old('status', $reseller['status'] ?? 1) == 1 ? 'selected' : '' }}>Ativo</option>
                                <option value="0" {{ old('status', $reseller['status'] ?? 1) == 0 ? 'selected' : '' }}>Bloqueado</option>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Bloqueie o revendedor para impedir o acesso</p>
                        </div>

                        <div class="flex gap-3">
                            <button type="submit" class="flex-1 px-6 py-3 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg hover:shadow-lg hover:shadow-orange-500/20 transition-all flex items-center justify-center gap-2 font-medium">
                                <i class="bi bi-check-circle"></i>
                                Salvar Altera&ccedil;&otilde;es
                            </button>
                            <a href="{{ route('resellers.index') }}" class="px-6 py-3 bg-white dark:bg-dark-200 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-dark-100 transition-colors font-medium border border-gray-200 dark:border-transparent">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-gray-50 dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 p-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                        <i class="bi bi-wallet2 text-orange-500"></i>
                        Informa&ccedil;&otilde;es Financeiras
                    </h3>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Saldo Atual</label>
                        <div class="text-4xl font-bold text-orange-500">{{ number_format((float)($reseller['credits'] ?? 0), 2, ',', '.') }}</div>
                    </div>
                    <button onclick="openRechargeModal()" class="w-full px-4 py-3 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg hover:shadow-lg transition-all flex items-center justify-center gap-2 font-medium">
                        <i class="bi bi-cash-coin"></i>
                        Recarregar Cr&eacute;ditos
                    </button>
                </div>

                <div class="bg-gray-50 dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 p-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                        <i class="bi bi-info-circle text-orange-500"></i>
                        Informa&ccedil;&otilde;es
                    </h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between border-b border-gray-200 dark:border-dark-200 pb-2">
                            <span class="text-gray-500 dark:text-gray-400">ID:</span>
                            <span class="text-gray-900 dark:text-white font-semibold">{{ $reseller['id'] }}</span>
                        </div>
                        <div class="flex justify-between pt-2">
                            <span class="text-gray-500 dark:text-gray-400">Grupo:</span>
                            <span class="text-gray-900 dark:text-white font-semibold">Revendedor</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Recarregar -->
        <div id="rechargeModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
            <div class="bg-white dark:bg-dark-300 rounded-xl max-w-md w-full border border-gray-200 dark:border-dark-200 shadow-xl">
                <div class="p-6 border-b border-gray-200 dark:border-dark-200 flex justify-between items-center">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">Recarregar Cr&eacute;ditos</h3>
                    <button onclick="closeRechargeModal()" class="text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <form action="{{ route('resellers.recharge', $reseller['id']) }}" method="POST" class="p-6">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">Revendedor</label>
                        <input type="text" value="{{ $reseller['username'] }}" readonly class="w-full px-4 py-2 bg-gray-100 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-500 dark:text-white opacity-70">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">Saldo Atual</label>
                        <input type="text" value="{{ number_format((float)($reseller['credits'] ?? 0), 2, ',', '.') }}" readonly class="w-full px-4 py-2 bg-gray-100 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-500 dark:text-white opacity-70">
                    </div>
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">Valor da Recarga</label>
                        <input type="number" name="amount" step="0.01" required class="w-full px-4 py-2 bg-white dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white focus:border-orange-500 focus:outline-none transition-colors" placeholder="Ex: 10 ou -10">
                    </div>
                    <div class="flex gap-3">
                        <button type="button" onclick="closeRechargeModal()" class="flex-1 px-4 py-2 bg-gray-100 dark:bg-dark-200 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-dark-100 transition-colors font-medium">Cancelar</button>
                        <button type="submit" class="flex-1 px-4 py-2 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg hover:shadow-lg transition-all font-medium">Recarregar</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
function openRechargeModal() {
    document.getElementById('rechargeModal').classList.remove('hidden');
}
function closeRechargeModal() {
    document.getElementById('rechargeModal').classList.add('hidden');
}

// Fechar modal com ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        if (!document.getElementById('rechargeModal').classList.contains('hidden')) {
            closeRechargeModal();
        } else {
            window.location.href = '{{ route('resellers.index') }}';
        }
    }
});
</script>
@endpush
@endsection
