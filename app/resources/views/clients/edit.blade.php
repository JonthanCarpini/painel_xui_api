@extends('layouts.app')

@section('title', 'Editar Cliente')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-dark-300 rounded-xl p-8 border-2 border-orange-500/50">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-white flex items-center gap-2">
                <i class="bi bi-pencil-square text-orange-500"></i>
                Editar Cliente
            </h2>
            <a href="{{ route('clients.index') }}" class="text-gray-400 hover:text-white transition-colors">
                <i class="bi bi-x-lg text-2xl"></i>
            </a>
        </div>

        @if($errors->any())
        <div class="bg-red-500/10 border border-red-500 text-red-500 px-4 py-3 rounded-lg mb-6">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('clients.update', $client['id']) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Username -->
                <div>
                    <label class="block text-gray-400 text-sm font-medium mb-2">
                        <i class="bi bi-person"></i> Usuário
                    </label>
                    <input type="text" name="username" value="{{ old('username', $client['username']) }}" required
                        class="w-full bg-dark-200 border border-dark-100 rounded-lg px-4 py-3 text-white focus:border-orange-500 focus:outline-none transition-colors">
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-gray-400 text-sm font-medium mb-2">
                        <i class="bi bi-key"></i> Senha
                    </label>
                    <input type="text" name="password" value="{{ old('password', $client['password']) }}" required
                        class="w-full bg-dark-200 border border-dark-100 rounded-lg px-4 py-3 text-white focus:border-orange-500 focus:outline-none transition-colors">
                </div>

                <!-- Package -->
                <div>
                    <label class="block text-gray-400 text-sm font-medium mb-2">
                        <i class="bi bi-box"></i> Pacote
                    </label>
                    <select name="package_id" class="w-full bg-dark-200 border border-dark-100 rounded-lg px-4 py-3 text-white focus:border-orange-500 focus:outline-none transition-colors">
                        <option value="">Nenhum</option>
                        @foreach($packages as $package)
                        <option value="{{ $package->id }}" {{ old('package_id', $client['package_id'] ?? '') == $package->id ? 'selected' : '' }}>
                            {{ $package->package_name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Max Connections -->
                <div>
                    <label class="block text-gray-400 text-sm font-medium mb-2">
                        <i class="bi bi-hdd-network"></i> Máx. Conexões
                    </label>
                    <input type="number" name="max_connections" value="{{ old('max_connections', $client['max_connections'] ?? 1) }}" min="1" max="10" required
                        class="w-full bg-dark-200 border border-dark-100 rounded-lg px-4 py-3 text-white focus:border-orange-500 focus:outline-none transition-colors">
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-gray-400 text-sm font-medium mb-2">
                        <i class="bi bi-envelope"></i> Email (Opcional)
                    </label>
                    <input type="email" name="email" value="{{ old('email', $client['contact'] ?? '') }}"
                        class="w-full bg-dark-200 border border-dark-100 rounded-lg px-4 py-3 text-white focus:border-orange-500 focus:outline-none transition-colors">
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-gray-400 text-sm font-medium mb-2">
                        <i class="bi bi-toggle-on"></i> Status
                    </label>
                    <select name="enabled" class="w-full bg-dark-200 border border-dark-100 rounded-lg px-4 py-3 text-white focus:border-orange-500 focus:outline-none transition-colors">
                        <option value="1" {{ old('enabled', $client['enabled'] ?? 1) == 1 ? 'selected' : '' }}>Ativo</option>
                        <option value="0" {{ old('enabled', $client['enabled'] ?? 1) == 0 ? 'selected' : '' }}>Inativo</option>
                    </select>
                </div>
            </div>

            <!-- Bouquets -->
            <div>
                <label class="block text-gray-400 text-sm font-medium mb-3">
                    <i class="bi bi-collection-play"></i> Bouquets (Selecione pelo menos 1)
                </label>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 max-h-64 overflow-y-auto p-4 bg-dark-200 rounded-lg border border-dark-100">
                    @foreach($bouquets as $bouquet)
                    <label class="flex items-center gap-2 p-2 hover:bg-dark-100 rounded cursor-pointer transition-colors">
                        <input type="checkbox" name="bouquet_ids[]" value="{{ $bouquet->id }}"
                            {{ in_array($bouquet->id, old('bouquet_ids', $selectedBouquets)) ? 'checked' : '' }}
                            class="w-4 h-4 text-orange-500 bg-dark-300 border-dark-100 rounded focus:ring-orange-500 focus:ring-2">
                        <span class="text-white text-sm">{{ $bouquet->bouquet_name }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            <!-- Actions -->
            <div class="flex gap-4 pt-4">
                <button type="submit" class="flex-1 bg-gradient-to-r from-orange-500 to-orange-600 text-white font-bold py-3 px-6 rounded-lg hover:shadow-lg hover:shadow-orange-500/20 transition-all duration-300">
                    <i class="bi bi-check-circle"></i> Salvar Alterações
                </button>
                <a href="{{ route('clients.index') }}" class="flex-1 bg-dark-200 text-white font-bold py-3 px-6 rounded-lg hover:bg-dark-100 transition-all duration-300 text-center">
                    <i class="bi bi-x-circle"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// Fechar modal com ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        window.location.href = '{{ route('clients.index') }}';
    }
});
</script>
@endsection
