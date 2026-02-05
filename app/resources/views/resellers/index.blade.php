@extends('layouts.app')

@section('title', 'Revendedores')

@section('content')
<div class="flex items-center justify-between mb-8">
    <h1 class="text-2xl font-bold text-white flex items-center gap-3">
        <i class="bi bi-shop text-orange-500"></i>
        Meus Revendedores
    </h1>
    <a href="{{ route('resellers.create') }}" class="px-4 py-2 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg hover:shadow-lg transition-all flex items-center gap-2">
        <i class="bi bi-plus-circle"></i>
        Novo Revendedor
    </a>
</div>

<div class="bg-dark-300 rounded-xl border border-dark-200 overflow-hidden">
    @if(count($resellers) > 0)
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-dark-200 border-b border-dark-100">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Usuário</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">E-mail</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Créditos</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-dark-200">
                @foreach($resellers as $reseller)
                <tr class="hover:bg-dark-200 transition-colors duration-150">
                    <td class="px-6 py-4">
                        <span class="text-white font-medium">{{ $reseller->username }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-gray-400">{{ $reseller->email ?? 'Não informado' }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-2xl font-bold text-orange-500">{{ number_format($reseller->credits, 2, ',', '.') }}</span>
                    </td>
                    <td class="px-6 py-4">
                        @if($reseller->status == 1)
                            <span class="px-3 py-1 bg-green-500/10 text-green-400 text-sm font-semibold rounded">Ativo</span>
                        @else
                            <span class="px-3 py-1 bg-red-500/10 text-red-400 text-sm font-semibold rounded">Bloqueado</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <button onclick="openModal('recharge{{ $reseller->id }}')" class="p-2 bg-green-500/10 text-green-400 rounded hover:bg-green-500/20 transition-colors" title="Recarregar">
                                <i class="bi bi-cash-coin"></i>
                            </button>
                            <a href="{{ route('resellers.edit', $reseller->id) }}" class="p-2 bg-blue-500/10 text-blue-400 rounded hover:bg-blue-500/20 transition-colors" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <button onclick="openModal('delete{{ $reseller->id }}')" class="p-2 bg-red-500/10 text-red-400 rounded hover:bg-red-500/20 transition-colors" title="Excluir">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>

                        <!-- Modal Recarregar -->
                        <div id="recharge{{ $reseller->id }}" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
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
                                        <button type="button" onclick="closeModal('recharge{{ $reseller->id }}')" class="flex-1 px-4 py-2 bg-dark-200 text-gray-300 rounded-lg hover:bg-dark-100 transition-colors">Cancelar</button>
                                        <button type="submit" class="flex-1 px-4 py-2 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg hover:shadow-lg transition-all">Recarregar</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Modal Excluir -->
                        <div id="delete{{ $reseller->id }}" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
                            <div class="bg-dark-300 rounded-xl max-w-md w-full border border-dark-200">
                                <div class="p-6 border-b border-dark-200">
                                    <h3 class="text-xl font-bold text-white">Confirmar Exclusão</h3>
                                </div>
                                <div class="p-6">
                                    <p class="text-gray-300 mb-6">Tem certeza que deseja excluir o revendedor <strong class="text-white">{{ $reseller->username }}</strong>?</p>
                                    <div class="flex gap-3">
                                        <button type="button" onclick="closeModal('delete{{ $reseller->id }}')" class="flex-1 px-4 py-2 bg-dark-200 text-white rounded-lg hover:bg-dark-100 transition-colors">
                                            Cancelar
                                        </button>
                                        <form action="{{ route('resellers.destroy', $reseller->id) }}" method="POST" class="flex-1">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-full px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors">
                                                Excluir
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="text-center py-16">
        <i class="bi bi-shop text-gray-600 text-6xl mb-4"></i>
        <h3 class="text-xl font-semibold text-gray-400 mb-2">Nenhum revendedor cadastrado</h3>
        <p class="text-gray-500 mb-6">Comece criando seu primeiro revendedor</p>
        <a href="{{ route('resellers.create') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg hover:shadow-lg transition-all">
            <i class="bi bi-plus-circle"></i>
            Criar Primeiro Revendedor
        </a>
    </div>
    @endif
</div>

@push('scripts')
<script>
function openModal(id) {
    document.getElementById(id).classList.remove('hidden');
}
function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
}
</script>
@endpush
@endsection
