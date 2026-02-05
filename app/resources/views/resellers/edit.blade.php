@extends('layouts.app')

@section('title', 'Editar Revendedor')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="bg-dark-300 rounded-xl p-8 border-2 border-orange-500/50">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-white flex items-center gap-2">
                <i class="bi bi-pencil text-orange-500"></i>
                Editar Revendedor
            </h2>
            <a href="{{ route('resellers.index') }}" class="text-gray-400 hover:text-white transition-colors">
                <i class="bi bi-x-lg text-2xl"></i>
            </a>
        </div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <div class="bg-dark-200 rounded-xl border border-dark-100 p-6">
            <form action="{{ route('resellers.update', $reseller->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-400 mb-2">Usuário</label>
                    <input type="text" value="{{ $reseller->username }}" readonly class="w-full px-4 py-2 bg-dark-200 border border-dark-100 rounded-lg text-gray-500">
                    <p class="text-xs text-gray-500 mt-1">O usuário não pode ser alterado</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-2">Nova Senha (Opcional)</label>
                        <input type="text" name="password" class="w-full px-4 py-2 bg-dark-200 border border-dark-100 rounded-lg text-white focus:border-orange-500 focus:outline-none" placeholder="Digite a nova senha" minlength="6">
                        <p class="text-xs text-gray-500 mt-1">Deixe em branco para manter a senha atual</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-2">E-mail (Opcional)</label>
                        <input type="email" name="email" value="{{ old('email', $reseller->email ?? '') }}" class="w-full px-4 py-2 bg-dark-200 border border-dark-100 rounded-lg text-white focus:border-orange-500 focus:outline-none" placeholder="email@exemplo.com">
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-400 mb-2">Status *</label>
                    <select name="status" required class="w-full px-4 py-2 bg-dark-200 border border-dark-100 rounded-lg text-white focus:border-orange-500 focus:outline-none">
                        <option value="1" {{ old('status', $reseller->status ?? 1) == 1 ? 'selected' : '' }}>Ativo</option>
                        <option value="0" {{ old('status', $reseller->status ?? 1) == 0 ? 'selected' : '' }}>Bloqueado</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Bloqueie o revendedor para impedir o acesso</p>
                </div>

                <div class="flex gap-3">
                    <button type="submit" class="flex-1 px-6 py-3 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg hover:shadow-lg hover:shadow-orange-500/20 transition-all flex items-center justify-center gap-2">
                        <i class="bi bi-check-circle"></i>
                        Salvar Alterações
                    </button>
                    <a href="{{ route('resellers.index') }}" class="px-6 py-3 bg-dark-200 text-gray-300 rounded-lg hover:bg-dark-100 transition-colors">Cancelar</a>
                </div>
            </form>
        </div>
    </div>

    <div class="space-y-6">
        <div class="bg-dark-300 rounded-xl border border-dark-200 p-6">
            <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                <i class="bi bi-wallet2 text-orange-500"></i>
                Informações Financeiras
            </h3>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-400 mb-2">Saldo Atual</label>
                <div class="text-4xl font-bold text-orange-500">{{ number_format($reseller->credits, 2, ',', '.') }}</div>
            </div>
            <button onclick="openRechargeModal()" class="w-full px-4 py-3 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg hover:shadow-lg transition-all flex items-center justify-center gap-2">
                <i class="bi bi-cash-coin"></i>
                Recarregar Créditos
            </button>
        </div>

        <div class="bg-dark-300 rounded-xl border border-dark-200 p-6">
            <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                <i class="bi bi-info-circle text-orange-500"></i>
                Informações
            </h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-400">ID:</span>
                    <span class="text-white font-semibold">{{ $reseller->id }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">Grupo:</span>
                    <span class="text-white font-semibold">Revendedor</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Recarregar -->
<div id="rechargeModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-dark-300 rounded-xl max-w-md w-full border border-dark-200">
        <div class="p-6 border-b border-dark-200">
            <h3 class="text-xl font-bold text-white">Recarregar Créditos</h3>
        </div>
        <form action="{{ route('resellers.recharge', $reseller->id) }}" method="POST" class="p-6">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-400 mb-2">Revendedor</label>
                <input type="text" value="{{ $reseller->username }}" readonly class="w-full px-4 py-2 bg-dark-200 border border-dark-100 rounded-lg text-white">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-400 mb-2">Saldo Atual</label>
                <input type="text" value="{{ number_format($reseller->credits, 2, ',', '.') }}" readonly class="w-full px-4 py-2 bg-dark-200 border border-dark-100 rounded-lg text-white">
            </div>
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-400 mb-2">Valor da Recarga</label>
                <input type="number" name="amount" step="0.01" min="0.01" required class="w-full px-4 py-2 bg-dark-200 border border-dark-100 rounded-lg text-white focus:border-orange-500 focus:outline-none" placeholder="0.00">
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="closeRechargeModal()" class="flex-1 px-4 py-2 bg-dark-200 text-gray-300 rounded-lg hover:bg-dark-100 transition-colors">Cancelar</button>
                <button type="submit" class="flex-1 px-4 py-2 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg hover:shadow-lg transition-all">Recarregar</button>
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
        window.location.href = '{{ route('resellers.index') }}';
    }
});
</script>
@endpush
@endsection
