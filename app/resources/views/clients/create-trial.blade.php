@extends('layouts.app')

@section('title', 'Criar Teste')

@section('content')
<div class="flex items-center justify-between mb-8">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
        <i class="bi bi-clock-history text-orange-500"></i>
        Gerar Teste R&aacute;pido
    </h1>
    <a href="{{ route('clients.index') }}" class="px-4 py-2 bg-white dark:bg-dark-300 border border-gray-200 dark:border-dark-200 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-dark-200 transition-colors flex items-center gap-2">
        <i class="bi bi-arrow-left"></i>
        Voltar
    </a>
</div>

<!-- Modal de Sucesso -->
@if(session('trial_success'))
<div id="successModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white dark:bg-dark-300 rounded-xl max-w-2xl w-full border border-gray-200 dark:border-dark-200 shadow-2xl">
        <div class="p-6 border-b border-gray-200 dark:border-dark-200 flex justify-between items-center bg-gradient-to-r from-green-600 to-green-700">
            <h3 class="text-xl font-bold text-white flex items-center gap-2">
                <i class="bi bi-check-circle-fill"></i>
                Teste Criado com Sucesso!
            </h3>
            <button onclick="closeSuccessModal()" class="text-white hover:text-gray-200">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">Usu&aacute;rio</label>
                    <div class="flex gap-2">
                        <input type="text" id="successUsername" readonly value="{{ session('trial_success')['username'] }}" class="flex-1 px-4 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white font-mono">
                        <button onclick="copyField('successUsername')" class="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors">
                            <i class="bi bi-clipboard"></i>
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">Senha</label>
                    <div class="flex gap-2">
                        <input type="text" id="successPassword" readonly value="{{ session('trial_success')['password'] }}" class="flex-1 px-4 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white font-mono">
                        <button onclick="copyField('successPassword')" class="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors">
                            <i class="bi bi-clipboard"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">Validade</label>
                    <input type="text" readonly value="{{ session('trial_success')['exp_date'] }}" class="w-full px-4 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">Conex&otilde;es</label>
                    <input type="text" readonly value="{{ session('trial_success')['max_connections'] }}" class="w-full px-4 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white">
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">URL M3U</label>
                <div class="flex gap-2">
                    <input type="text" id="successM3u" readonly value="{{ session('trial_success')['m3u_url'] }}" class="flex-1 px-4 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white font-mono text-sm">
                    <button onclick="copyField('successM3u')" class="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors">
                        <i class="bi bi-clipboard"></i>
                    </button>
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">URL HLS</label>
                <div class="flex gap-2">
                    <input type="text" id="successHls" readonly value="{{ session('trial_success')['hls_url'] }}" class="flex-1 px-4 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white font-mono text-sm">
                    <button onclick="copyField('successHls')" class="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors">
                        <i class="bi bi-clipboard"></i>
                    </button>
                </div>
            </div>

            @if(session('client_message'))
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">Mensagem do Cliente (WhatsApp)</label>
                <div class="relative">
                    <textarea id="trialClientMessage" readonly rows="8" class="w-full px-4 py-3 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white font-mono text-xs resize-none">{{ session('client_message') }}</textarea>
                    <button onclick="copyField('trialClientMessage')" class="absolute top-2 right-2 px-2 py-1 bg-orange-500 text-white rounded hover:bg-orange-600 transition-colors text-xs">
                        <i class="bi bi-clipboard"></i>
                    </button>
                </div>
                <div class="mt-2 flex justify-end">
                    <button onclick="window.open('https://wa.me/?text=' + encodeURIComponent(document.getElementById('trialClientMessage').value), '_blank')" class="px-3 py-1.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm flex items-center gap-2">
                        <i class="bi bi-whatsapp"></i>
                        Enviar no WhatsApp
                    </button>
                </div>
            </div>
            @endif

            <div class="flex gap-3">
                <button onclick="closeSuccessModal()" class="flex-1 px-4 py-2 bg-gray-100 dark:bg-dark-200 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-dark-100 transition-colors font-medium">Fechar</button>
                <a href="{{ route('clients.index') }}" class="flex-1 px-4 py-2 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg hover:shadow-lg transition-all text-center font-medium">
                    Ver Todos os Clientes
                </a>
            </div>
        </div>
    </div>
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 p-6 shadow-sm dark:shadow-none">
            <form action="{{ route('clients.store-trial') }}" method="POST">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">Usu&aacute;rio *</label>
                        <div class="flex gap-2">
                            <input type="text" id="usernameField" name="username" value="{{ old('username') }}" required minlength="3" maxlength="50" class="flex-1 px-4 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white focus:border-orange-500 focus:outline-none transition-colors" placeholder="Digite o usu&aacute;rio">
                            <button type="button" onclick="generateUsername()" class="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors">
                                <i class="bi bi-shuffle"></i>
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">M&iacute;nimo 3 caracteres</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">Senha *</label>
                        <div class="flex gap-2">
                            <input type="text" id="passwordField" name="password" value="{{ old('password') }}" required minlength="6" class="flex-1 px-4 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white focus:border-orange-500 focus:outline-none transition-colors" placeholder="Digite a senha">
                            <button type="button" onclick="generatePassword()" class="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors">
                                <i class="bi bi-shuffle"></i>
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">M&iacute;nimo 6 caracteres</p>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">Pacote de Teste *</label>
                    <select name="package_id" id="packageSelect" required class="w-full px-4 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white focus:border-orange-500 focus:outline-none transition-colors">
                        <option value="">Selecione um pacote de teste</option>
                        @foreach($packages as $package)
                            @if($package->is_trial == 1)
                                <option value="{{ $package->id }}" 
                                        data-duration="{{ $package->trial_duration ?? 24 }}"
                                        data-duration-in="{{ $package->trial_duration_in ?? 'hours' }}"
                                        data-connections="{{ $package->max_connections ?? 1 }}"
                                        data-bouquets="{{ json_encode($package->bouquets ?? []) }}"
                                        {{ old('package_id') == $package->id ? 'selected' : '' }}>
                                    {{ $package->package_name }} - {{ $package->trial_duration ?? 24 }} {{ $package->trial_duration_in ?? 'horas' }}
                                    @if($package->trial_credits > 0)
                                        ({{ $package->trial_credits }} cr&eacute;dito{{ $package->trial_credits > 1 ? 's' : '' }})
                                    @else
                                        (Gr&aacute;tis)
                                    @endif
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">Dura&ccedil;&atilde;o *</label>
                        <input type="text" id="durationDisplay" readonly class="w-full px-4 py-2 bg-gray-100 dark:bg-dark-100 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-500 dark:text-gray-400 cursor-not-allowed" value="Selecione um pacote" placeholder="Autom&aacute;tico">
                        <input type="hidden" id="durationValue" name="duration_value" value="">
                        <input type="hidden" id="durationUnit" name="duration_unit" value="">
                        <p class="text-xs text-gray-500 mt-1">Definido automaticamente pelo pacote</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">Conex&otilde;es Simult&acirc;neas *</label>
                        <input type="text" id="connectionsDisplay" readonly class="w-full px-4 py-2 bg-gray-100 dark:bg-dark-100 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-500 dark:text-gray-400 cursor-not-allowed" value="Selecione um pacote" placeholder="Autom&aacute;tico">
                        <input type="hidden" id="maxConnections" name="max_connections" value="">
                        <p class="text-xs text-gray-500 mt-1">Definido automaticamente pelo pacote</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">Telefone (Opcional)</label>
                        <input type="text" id="phoneField" name="phone" value="{{ old('phone') }}" class="w-full px-4 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white focus:border-orange-500 focus:outline-none transition-colors" placeholder="(00) 00000-0000" maxlength="15">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">E-mail (Opcional)</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="w-full px-4 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white focus:border-orange-500 focus:outline-none transition-colors" placeholder="email@exemplo.com">
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">Observa&ccedil;&atilde;o (Opcional)</label>
                    <textarea name="notes" rows="3" class="w-full px-4 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white focus:border-orange-500 focus:outline-none transition-colors" placeholder="Anota&ccedil;&otilde;es sobre este teste">{{ old('notes') }}</textarea>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">Formato de Sa&iacute;da (Access Output)</label>
                    <div class="flex flex-wrap gap-3 p-4 bg-gray-50 dark:bg-dark-200 rounded-lg border border-gray-200 dark:border-0">
                        <label class="flex items-center gap-2 p-2 bg-white dark:bg-dark-300 border border-gray-200 dark:border-dark-100 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-dark-100 transition-colors">
                            <input type="checkbox" name="access_output[]" value="1" checked class="w-5 h-5 text-orange-500 bg-gray-100 dark:bg-dark-200 border-gray-300 dark:border-dark-100 rounded focus:ring-orange-500 focus:ring-2">
                            <span class="text-gray-700 dark:text-white text-sm">HLS (M3U8)</span>
                        </label>
                        <label class="flex items-center gap-2 p-2 bg-white dark:bg-dark-300 border border-gray-200 dark:border-dark-100 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-dark-100 transition-colors">
                            <input type="checkbox" name="access_output[]" value="2" checked class="w-5 h-5 text-orange-500 bg-gray-100 dark:bg-dark-200 border-gray-300 dark:border-dark-100 rounded focus:ring-orange-500 focus:ring-2">
                            <span class="text-gray-700 dark:text-white text-sm">MPEGTS (TS)</span>
                        </label>
                        <label class="flex items-center gap-2 p-2 bg-white dark:bg-dark-300 border border-gray-200 dark:border-dark-100 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-dark-100 transition-colors">
                            <input type="checkbox" name="access_output[]" value="3" class="w-5 h-5 text-orange-500 bg-gray-100 dark:bg-dark-200 border-gray-300 dark:border-dark-100 rounded focus:ring-orange-500 focus:ring-2">
                            <span class="text-gray-700 dark:text-white text-sm">RTMP</span>
                        </label>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Formatos de streaming permitidos para este teste</p>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-3">Selecione os Canais (Buqu&ecirc;s) *</label>
                    <div id="trialBouquets" class="grid grid-cols-1 sm:grid-cols-2 gap-3 w-full max-h-60 overflow-y-auto p-4 bg-gray-50 dark:bg-dark-200 rounded-lg border border-gray-200 dark:border-0 custom-scrollbar">
                    @foreach($bouquets as $bouquet)
                        <label class="flex items-center gap-3 p-3 bg-white dark:bg-dark-300 border border-gray-200 dark:border-dark-100 rounded-lg hover:bg-gray-50 dark:hover:bg-dark-100 cursor-pointer transition-colors shadow-sm dark:shadow-none w-full">
                            <input type="checkbox" name="bouquet_ids[]" value="{{ $bouquet->id }}" class="w-5 h-5 text-orange-500 bg-gray-100 dark:bg-dark-200 border-gray-300 dark:border-dark-100 rounded focus:ring-orange-500 focus:ring-2">
                            <span class="text-gray-700 dark:text-white text-sm break-all">{{ $bouquet->bouquet_name }}</span>
                        </label>
                    @endforeach
                </div>
                    <p class="text-xs text-gray-500 mt-2">Selecione pelo menos um canal</p>
                </div>

                <div class="flex gap-3">
                    <button type="submit" class="flex-1 px-6 py-3 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg hover:shadow-lg hover:shadow-orange-500/20 transition-all flex items-center justify-center gap-2 font-medium">
                        <i class="bi bi-check-circle"></i>
                        Gerar Teste
                    </button>
                    <a href="{{ route('clients.index') }}" class="px-6 py-3 bg-gray-100 dark:bg-dark-200 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-dark-100 transition-colors font-medium">Cancelar</a>
                </div>
            </form>
        </div>
    </div>

    <div class="space-y-6">
        <div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 p-6 shadow-sm dark:shadow-none">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <i class="bi bi-info-circle text-orange-500"></i>
                Sobre Testes
            </h3>
            <div class="space-y-3 text-sm text-gray-600 dark:text-gray-400">
                <p><strong class="text-gray-900 dark:text-white">Caracter&iacute;sticas:</strong></p>
                <ul class="space-y-2 ml-4">
                    <li class="flex items-start gap-2">
                        <i class="bi bi-check text-green-500 mt-0.5"></i>
                        <span>Apenas 1 conex&atilde;o simult&acirc;nea</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="bi bi-check text-green-500 mt-0.5"></i>
                        <span>Dura&ccedil;&atilde;o limitada (horas)</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="bi bi-check text-green-500 mt-0.5"></i>
                        <span>Ideal para demonstra&ccedil;&atilde;o</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="bi bi-check text-green-500 mt-0.5"></i>
                        <span>N&atilde;o consome cr&eacute;ditos</span>
                    </li>
                </ul>
            </div>
            <div class="mt-4 p-3 bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/50 rounded-lg">
                <p class="text-green-700 dark:text-green-400 text-sm flex items-start gap-2">
                    <i class="bi bi-check-circle-fill mt-0.5"></i>
                    <span><strong>Gr&aacute;tis:</strong> Testes n&atilde;o debitam cr&eacute;ditos</span>
                </p>
            </div>
        </div>

        <div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 p-6 shadow-sm dark:shadow-none">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <i class="bi bi-lightbulb text-orange-500"></i>
                Dica Profissional
            </h3>
            <p class="text-gray-600 dark:text-gray-400 text-sm leading-relaxed">
                Use testes para demonstrar a qualidade do servi&ccedil;o antes da venda. Isso aumenta a taxa de convers&atilde;o!
            </p>
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
        const prefix = 'test';
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

    // Funções do modal de sucesso
    function closeSuccessModal() {
        document.getElementById('successModal').style.display = 'none';
    }

    function copyField(fieldId) {
        const input = document.getElementById(fieldId);
        input.select();
        document.execCommand('copy');
        alert('Copiado para a área de transferência!');
    }
</script>
@endpush
@endsection
