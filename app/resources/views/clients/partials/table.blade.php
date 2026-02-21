<div class="overflow-x-auto custom-scrollbar rounded-lg border border-gray-200 dark:border-dark-200">
    @php
        $trustPackageId = \App\Models\AppSetting::get('trust_renew_package_id');
    @endphp
    <table class="w-full divide-y divide-gray-200 dark:divide-dark-200">
        <thead class="bg-gray-50 dark:bg-dark-200">
            <tr>
                <th scope="col" class="px-4 py-3 text-left whitespace-nowrap">
                    <button onclick="sortBy('username')" class="group text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hover:text-orange-500 dark:hover:text-white transition-colors flex items-center gap-1">
                        Username <i class="bi bi-arrow-down-up opacity-0 group-hover:opacity-100 transition-opacity"></i>
                    </button>
                </th>
                <th scope="col" class="px-4 py-3 text-left whitespace-nowrap">
                    <button onclick="sortBy('password')" class="group text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hover:text-orange-500 dark:hover:text-white transition-colors flex items-center gap-1">
                        Senha <i class="bi bi-arrow-down-up opacity-0 group-hover:opacity-100 transition-opacity"></i>
                    </button>
                </th>
                <th scope="col" class="px-4 py-3 text-left whitespace-nowrap hidden sm:table-cell">
                    <button onclick="sortBy('contact')" class="group text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hover:text-orange-500 dark:hover:text-white transition-colors flex items-center gap-1">
                        Telefone <i class="bi bi-arrow-down-up opacity-0 group-hover:opacity-100 transition-opacity"></i>
                    </button>
                </th>
                <th scope="col" class="px-4 py-3 text-left whitespace-nowrap hidden md:table-cell">
                    <button onclick="sortBy('member_id')" class="group text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hover:text-orange-500 dark:hover:text-white transition-colors flex items-center gap-1">
                        Revenda <i class="bi bi-arrow-down-up opacity-0 group-hover:opacity-100 transition-opacity"></i>
                    </button>
                </th>
                <th scope="col" class="px-4 py-3 text-left whitespace-nowrap">
                    <button onclick="sortBy('is_trial')" class="group text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hover:text-orange-500 dark:hover:text-white transition-colors flex items-center gap-1">
                        Tipo <i class="bi bi-arrow-down-up opacity-0 group-hover:opacity-100 transition-opacity"></i>
                    </button>
                </th>
                <th scope="col" class="px-4 py-3 text-left whitespace-nowrap hidden lg:table-cell">
                    <button onclick="sortBy('id')" class="group text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hover:text-orange-500 dark:hover:text-white transition-colors flex items-center gap-1">
                        ID <i class="bi bi-arrow-down-up opacity-0 group-hover:opacity-100 transition-opacity"></i>
                    </button>
                </th>
                <th scope="col" class="px-4 py-3 text-left whitespace-nowrap">
                    <button onclick="sortBy('exp_date')" class="group text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hover:text-orange-500 dark:hover:text-white transition-colors flex items-center gap-1">
                        Vencimento <i class="bi bi-arrow-down-up opacity-0 group-hover:opacity-100 transition-opacity"></i>
                    </button>
                </th>
                <th scope="col" class="px-4 py-3 text-left whitespace-nowrap hidden sm:table-cell">
                    <button onclick="sortBy('max_connections')" class="group text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hover:text-orange-500 dark:hover:text-white transition-colors flex items-center gap-1">
                        Conexões <i class="bi bi-arrow-down-up opacity-0 group-hover:opacity-100 transition-opacity"></i>
                    </button>
                </th>
                <th scope="col" class="px-4 py-3 text-left whitespace-nowrap">
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</span>
                </th>
                <th scope="col" class="px-4 py-3 text-right whitespace-nowrap">
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Ações</span>
                </th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-dark-200 bg-white dark:bg-dark-300">
            @forelse($clients as $client)
            <tr class="hover:bg-gray-50 dark:hover:bg-dark-200/50 transition-colors">
                <td class="px-4 py-3 whitespace-nowrap">
                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $client['username'] }}</span>
                </td>
                <td class="px-4 py-3 whitespace-nowrap">
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-gray-600 dark:text-gray-300 font-mono">{{ $client['password'] }}</span>
                        <button onclick="copyToClipboardText('{{ $client['password'] }}')" class="text-gray-400 hover:text-orange-500 transition-colors" title="Copiar Senha">
                            <i class="bi bi-clipboard text-xs"></i>
                        </button>
                    </div>
                </td>
                <td class="px-4 py-3 whitespace-nowrap hidden sm:table-cell">
                    <span class="text-sm text-gray-600 dark:text-gray-300">
                        @if(!empty($client['local_phone']))
                            {{ $client['local_phone'] }} <i class="bi bi-database text-[10px] text-blue-400" title="Salvo localmente"></i>
                        @else
                            {{ $client['contact'] ?? '-' }}
                        @endif
                    </span>
                </td>
                <td class="px-4 py-3 whitespace-nowrap hidden md:table-cell">
                    <span class="text-sm text-gray-600 dark:text-gray-300">{{ $client['member_username'] ?? 'N/A' }}</span>
                </td>
                <td class="px-4 py-3 whitespace-nowrap">
                    @if($client['is_trial'] ?? 0)
                        <span class="px-2 py-0.5 bg-yellow-100 dark:bg-yellow-500/20 text-yellow-700 dark:text-yellow-400 rounded-full text-xs font-semibold border border-yellow-200 dark:border-transparent">Teste</span>
                    @else
                        <span class="px-2 py-0.5 bg-green-100 dark:bg-green-500/20 text-green-700 dark:text-green-400 rounded-full text-xs font-semibold border border-green-200 dark:border-transparent">Cliente</span>
                    @endif
                </td>
                <td class="px-4 py-3 whitespace-nowrap hidden lg:table-cell">
                    <span class="text-sm text-gray-500 dark:text-gray-400">#{{ $client['id'] ?? '?' }}</span>
                </td>
                <td class="px-4 py-3 whitespace-nowrap">
                    @php
                        $expDate = (int)($client['exp_date'] ?? 0);
                        $isExpired = $expDate <= time();
                        $daysLeft = ceil(($expDate - time()) / 86400);
                        
                        $textClass = 'text-gray-600 dark:text-gray-300';
                        if ($isExpired) {
                            $textClass = 'text-red-600 dark:text-red-400 font-semibold';
                        } elseif ($daysLeft <= 7) {
                            $textClass = 'text-yellow-600 dark:text-yellow-400 font-medium';
                        }
                    @endphp
                    <span class="text-sm {{ $textClass }}">
                        {{ date('d/m/Y H:i', $expDate) }}
                    </span>
                </td>
                <td class="px-4 py-3 whitespace-nowrap hidden sm:table-cell">
                    <span class="text-sm text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="bi bi-wifi text-orange-500"></i>
                        {{ $client['max_connections'] }}
                    </span>
                </td>
                <td class="px-4 py-3 whitespace-nowrap">
                    @if(($client['enabled'] ?? 0) && ($client['admin_enabled'] ?? true) && ($client['exp_date'] ?? 0) > time())
                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-500/20 dark:text-green-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Ativo
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-500/20 dark:text-red-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Inativo
                        </span>
                    @endif
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-right">
                    <div class="flex items-center justify-end gap-1">
                        <button onclick="openM3uModal({{ $client['id'] }}, '{{ $client['username'] }}')" class="p-1.5 bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-500/20 rounded-lg transition-colors" title="M3U">
                            <i class="bi bi-file-earmark-code"></i>
                        </button>
                        <button onclick="openEditModal({{ $client['id'] }})" class="p-1.5 bg-gray-50 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600/50 rounded-lg transition-colors" title="Editar">
                            <i class="bi bi-pencil"></i>
                        </button>
                        @if($trustPackageId)
                        <button onclick="submitRenewTrustRow({{ $client['id'] }}, '{{ $client['username'] }}')" class="p-1.5 bg-purple-50 dark:bg-purple-500/10 text-purple-600 dark:text-purple-400 hover:bg-purple-100 dark:hover:bg-purple-500/20 rounded-lg transition-colors" title="Renovar em Confiança">
                            <i class="bi bi-shield-check"></i>
                        </button>
                        @endif
                        <button onclick="openRenewModal({{ $client['id'] }})" class="p-1.5 bg-green-50 dark:bg-green-500/10 text-green-600 dark:text-green-400 hover:bg-green-100 dark:hover:bg-green-500/20 rounded-lg transition-colors" title="Renovar">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>
                        @php
                            $clientPhone = $client['local_phone'] ?? $client['contact'] ?? null;
                        @endphp
                        @if($clientPhone)
                        <button onclick="openWhatsappModal({{ $client['id'] }}, '{{ addslashes($client['username']) }}', '{{ addslashes($clientPhone) }}', '{{ $client['password'] }}', '{{ date('d/m/Y H:i', $client['exp_date']) }}', {{ $client['max_connections'] }})" class="p-1.5 bg-orange-50 dark:bg-orange-500/10 text-orange-600 dark:text-orange-400 hover:bg-orange-100 dark:hover:bg-orange-500/20 rounded-lg transition-colors" title="WhatsApp">
                            <i class="bi bi-whatsapp"></i>
                        </button>
                        @endif
                        <button onclick="deleteClient({{ $client['id'] }})" class="p-1.5 bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-500/20 rounded-lg transition-colors" title="Excluir">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="px-4 py-12 text-center text-gray-500 dark:text-gray-400">
                    <div class="flex flex-col items-center justify-center">
                        <i class="bi bi-inbox text-4xl mb-3 text-gray-300 dark:text-gray-600"></i>
                        <p class="font-medium">Nenhum cliente encontrado</p>
                        <p class="text-sm mt-1">Tente ajustar os filtros ou crie um novo cliente.</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<script>
    function copyToClipboardText(text) {
        navigator.clipboard.writeText(text).then(() => {
            // Feedback opcional se desejado
        });
    }

    function submitRenewTrustRow(id, name) {
        // Usa a mesma função do modal, mas ajustada para receber params
        // Como a function original lê do DOM, vamos criar uma versão que aceita args
        // Ou preencher os inputs hidden do modal e chamar a function original?
        // Melhor criar uma function universal.
        
        if(!confirm(`Confirma a renovação em confiança para o cliente "${name}"?`)) return;
        
        // Mostrar loading no botão clicado? Difícil pegar referência aqui.
        // Vamos usar um overlay global ou alert
        
        fetch(`/clients/${id}/renew-trust`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                // Sucesso - Mostrar modal de sucesso
                document.getElementById('renewSuccessUsername').value = data.client.username;
                document.getElementById('renewSuccessPassword').value = '********';
                document.getElementById('renewSuccessExpDate').value = data.client.exp_date;
                
                const message = `✅ RENOVAÇÃO EM CONFIANÇA REALIZADA!\n\n👤 Usuário: ${data.client.username}\n📅 Nova Validade: ${data.client.exp_date}\n\nObrigado pela preferência! 👍`;
                document.getElementById('renewSuccessWhatsapp').value = message;
                document.getElementById('renewSuccessPhone').value = data.client.phone || '';
                document.getElementById('renewSuccessFeedback').classList.add('hidden');
                document.getElementById('renewSuccessSendBtn').disabled = false;
                document.getElementById('renewSuccessSendBtn').innerHTML = '<i class="bi bi-whatsapp"></i> Enviar via WhatsApp';
                
                document.getElementById('renewSuccessModal').classList.remove('hidden');
                
                if(typeof loadClients === 'function') loadClients();
            } else {
                alert('Erro ao renovar: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Ocorreu um erro ao processar a renovação.');
        });
    }
</script>
