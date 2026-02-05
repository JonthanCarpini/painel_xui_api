@extends('layouts.app')

@section('title', 'Criar Cliente')

@section('content')
<div class="flex items-center justify-between mb-8">
    <h1 class="text-2xl font-bold text-white flex items-center gap-3">
        <i class="bi bi-plus-circle text-orange-500"></i>
        Criar Novo Cliente
    </h1>
    <a href="{{ route('clients.index') }}" class="px-4 py-2 bg-dark-300 border border-dark-200 text-gray-300 rounded-lg hover:bg-dark-200 transition-colors flex items-center gap-2">
        <i class="bi bi-arrow-left"></i>
        Voltar
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <div class="bg-dark-300 rounded-xl border border-dark-200 p-6">
            <form action="{{ route('clients.store') }}" method="POST">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-2">Usuário *</label>
                        <div class="flex gap-2">
                            <input type="text" id="usernameField" name="username" value="{{ old('username') }}" required minlength="3" maxlength="50" class="flex-1 px-4 py-2 bg-dark-200 border border-dark-100 rounded-lg text-white focus:border-orange-500 focus:outline-none" placeholder="Digite o usuário">
                            <button type="button" onclick="generateUsername()" class="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors">
                                <i class="bi bi-shuffle"></i>
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Mínimo 3 caracteres</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-2">Senha *</label>
                        <div class="flex gap-2">
                            <input type="text" id="passwordField" name="password" value="{{ old('password') }}" required minlength="6" class="flex-1 px-4 py-2 bg-dark-200 border border-dark-100 rounded-lg text-white focus:border-orange-500 focus:outline-none" placeholder="Digite a senha">
                            <button type="button" onclick="generatePassword()" class="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors">
                                <i class="bi bi-shuffle"></i>
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Mínimo 6 caracteres</p>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-400 mb-2">Pacote *</label>
                    <select name="package_id" id="packageSelect" required class="w-full px-4 py-2 bg-dark-200 border border-dark-100 rounded-lg text-white focus:border-orange-500 focus:outline-none">
                        <option value="">Selecione um pacote</option>
                        @foreach($packages as $package)
                            @if($package->is_official == 1)
                                <option value="{{ $package->id }}" 
                                        data-duration="{{ $package->official_duration ?? 30 }}"
                                        data-duration-in="{{ $package->official_duration_in ?? 'days' }}"
                                        data-connections="{{ $package->max_connections ?? 1 }}"
                                        data-bouquets="{{ $package->bouquets ?? '[]' }}"
                                        {{ old('package_id') == $package->id ? 'selected' : '' }}>
                                    {{ $package->package_name }} - {{ $package->official_duration ?? 30 }} {{ $package->official_duration_in ?? 'dias' }}
                                    @if($package->official_credits > 0)
                                        ({{ $package->official_credits }} crédito{{ $package->official_credits > 1 ? 's' : '' }})
                                    @endif
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-2">Duração *</label>
                        <input type="text" id="durationDisplay" readonly class="w-full px-4 py-2 bg-dark-100 border border-dark-100 rounded-lg text-gray-400 cursor-not-allowed" value="Selecione um pacote" placeholder="Automático">
                        <input type="hidden" id="durationValue" name="duration_value" value="">
                        <input type="hidden" id="durationUnit" name="duration_unit" value="">
                        <p class="text-xs text-gray-500 mt-1">Definido automaticamente pelo pacote</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-2">Conexões Simultâneas *</label>
                        <input type="text" id="connectionsDisplay" readonly class="w-full px-4 py-2 bg-dark-100 border border-dark-100 rounded-lg text-gray-400 cursor-not-allowed" value="Selecione um pacote" placeholder="Automático">
                        <input type="hidden" id="maxConnections" name="max_connections" value="">
                        <p class="text-xs text-gray-500 mt-1">Definido automaticamente pelo pacote</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-2">Telefone (Opcional)</label>
                        <input type="text" id="phoneField" name="phone" value="{{ old('phone') }}" class="w-full px-4 py-2 bg-dark-200 border border-dark-100 rounded-lg text-white focus:border-orange-500 focus:outline-none" placeholder="(00) 00000-0000" maxlength="15">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-2">E-mail (Opcional)</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="w-full px-4 py-2 bg-dark-200 border border-dark-100 rounded-lg text-white focus:border-orange-500 focus:outline-none" placeholder="email@exemplo.com">
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-400 mb-2">Observação (Opcional)</label>
                    <textarea name="notes" rows="3" class="w-full px-4 py-2 bg-dark-200 border border-dark-100 rounded-lg text-white focus:border-orange-500 focus:outline-none" placeholder="Anotações sobre este cliente">{{ old('notes') }}</textarea>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-400 mb-3">Selecione os Canais (Buquês) *</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-64 overflow-y-auto p-4 bg-dark-200 rounded-lg">
                        @foreach($bouquets as $bouquet)
                            <label class="flex items-center gap-3 p-3 bg-dark-300 rounded-lg hover:bg-dark-100 cursor-pointer transition-colors">
                                <input type="checkbox" name="bouquet_ids[]" value="{{ $bouquet['id'] }}" {{ in_array($bouquet['id'], old('bouquet_ids', [])) ? 'checked' : '' }} class="w-5 h-5 text-orange-500 bg-dark-200 border-dark-100 rounded focus:ring-orange-500">
                                <span class="text-white text-sm">{{ $bouquet['bouquet_name'] }}</span>
                            </label>
                        @endforeach
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Selecione pelo menos um canal</p>
                </div>

                <div class="flex gap-3">
                    <button type="submit" class="flex-1 px-6 py-3 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg hover:shadow-lg hover:shadow-orange-500/20 transition-all flex items-center justify-center gap-2">
                        <i class="bi bi-check-circle"></i>
                        Criar Cliente
                    </button>
                    <a href="{{ route('clients.index') }}" class="px-6 py-3 bg-dark-200 text-gray-300 rounded-lg hover:bg-dark-100 transition-colors">Cancelar</a>
                </div>
            </form>
        </div>
    </div>

    <div class="space-y-6">
        <div class="bg-dark-300 rounded-xl border border-dark-200 p-6">
            <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                <i class="bi bi-info-circle text-orange-500"></i>
                Informações
            </h3>
            <div class="space-y-3 text-sm text-gray-400">
                <p><strong class="text-white">Dicas:</strong></p>
                <ul class="space-y-2 ml-4">
                    <li class="flex items-start gap-2">
                        <i class="bi bi-check text-green-500 mt-0.5"></i>
                        <span>Use senhas fortes e únicas</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="bi bi-check text-green-500 mt-0.5"></i>
                        <span>Escolha o pacote adequado</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="bi bi-check text-green-500 mt-0.5"></i>
                        <span>Defina duração conforme pagamento</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="bi bi-check text-green-500 mt-0.5"></i>
                        <span>Selecione apenas canais necessários</span>
                    </li>
                </ul>
            </div>
            <div class="mt-4 p-3 bg-yellow-500/10 border border-yellow-500/50 rounded-lg">
                <p class="text-yellow-400 text-sm flex items-start gap-2">
                    <i class="bi bi-exclamation-triangle-fill mt-0.5"></i>
                    <span><strong>Atenção:</strong> Créditos serão debitados automaticamente</span>
                </p>
            </div>
        </div>

        <div class="bg-dark-300 rounded-xl border border-dark-200 p-6">
            <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                <i class="bi bi-lightning-charge text-orange-500"></i>
                Ação Rápida
            </h3>
            <a href="{{ route('clients.create-trial') }}" class="block w-full px-4 py-3 bg-dark-200 border border-orange-500 text-white rounded-lg hover:bg-dark-100 transition-colors text-center">
                <i class="bi bi-clock-history mr-2"></i>
                Criar Teste Grátis
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const packageSelect = document.getElementById('packageSelect');
    const durationDisplay = document.getElementById('durationDisplay');
    const durationValue = document.getElementById('durationValue');
    const durationUnit = document.getElementById('durationUnit');
    const connectionsDisplay = document.getElementById('connectionsDisplay');
    const maxConnections = document.getElementById('maxConnections');

    // Gerar username aleatório
    function generateUsername() {
        const prefix = 'user';
        const random = Math.floor(Math.random() * 10000).toString().padStart(4, '0');
        document.getElementById('usernameField').value = prefix + random;
    }

    // Gerar senha aleatória
    function generatePassword() {
        const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
        let password = '';
        for (let i = 0; i < 8; i++) {
            password += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        document.getElementById('passwordField').value = password;
    }

    // Máscara de telefone
    document.getElementById('phoneField').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 11) value = value.slice(0, 11);
        
        if (value.length > 10) {
            value = value.replace(/^(\d{2})(\d{5})(\d{4}).*/, '($1) $2-$3');
        } else if (value.length > 6) {
            value = value.replace(/^(\d{2})(\d{4})(\d{0,4}).*/, '($1) $2-$3');
        } else if (value.length > 2) {
            value = value.replace(/^(\d{2})(\d{0,5})/, '($1) $2');
        } else if (value.length > 0) {
            value = value.replace(/^(\d*)/, '($1');
        }
        
        e.target.value = value;
    });

    packageSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        
        if (this.value) {
            const duration = selectedOption.getAttribute('data-duration');
            const durationIn = selectedOption.getAttribute('data-duration-in');
            const connections = selectedOption.getAttribute('data-connections');
            const bouquets = selectedOption.getAttribute('data-bouquets');
            
            // Mapear unidades
            const unitMap = {
                'hours': 'hora(s)',
                'hour': 'hora(s)',
                'days': 'dia(s)',
                'day': 'dia(s)',
                'months': 'mês(es)',
                'month': 'mês(es)',
                'years': 'ano(s)',
                'year': 'ano(s)'
            };
            
            const unitDisplay = unitMap[durationIn] || durationIn;
            
            // Atualizar campos
            durationDisplay.value = `${duration} ${unitDisplay}`;
            durationValue.value = duration;
            durationUnit.value = durationIn;
            
            connectionsDisplay.value = `${connections} Conexão${connections > 1 ? 'ões' : ''}`;
            maxConnections.value = connections;

            // Auto-selecionar bouquets do pacote
            if (bouquets) {
                try {
                    const bouquetIds = JSON.parse(bouquets);
                    document.querySelectorAll('input[name="bouquet_ids[]"]').forEach(checkbox => {
                        checkbox.checked = bouquetIds.includes(parseInt(checkbox.value));
                    });
                } catch (e) {
                    console.error('Erro ao parsear bouquets:', e);
                }
            }
        } else {
            durationDisplay.value = 'Selecione um pacote';
            durationValue.value = '';
            durationUnit.value = '';
            connectionsDisplay.value = 'Selecione um pacote';
            maxConnections.value = '';
            
            // Desmarcar todos os bouquets
            document.querySelectorAll('input[name="bouquet_ids[]"]').forEach(checkbox => {
                checkbox.checked = false;
            });
        }
    });
</script>
@endpush
@endsection
