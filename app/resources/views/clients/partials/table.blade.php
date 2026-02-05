<div class="overflow-x-auto">
    <table class="w-full">
        <thead>
            <tr class="border-b border-gray-200 dark:border-dark-200 bg-gray-50 dark:bg-dark-200">
                <th class="px-4 py-3 text-left">
                    <button onclick="sortBy('username')" class="text-gray-500 dark:text-gray-400 hover:text-orange-500 dark:hover:text-white flex items-center gap-1 text-sm font-medium uppercase tracking-wider transition-colors">
                        USERNAME <i class="bi bi-arrow-down-up text-xs"></i>
                    </button>
                </th>
                <th class="px-4 py-3 text-left">
                    <button onclick="sortBy('password')" class="text-gray-500 dark:text-gray-400 hover:text-orange-500 dark:hover:text-white flex items-center gap-1 text-sm font-medium uppercase tracking-wider transition-colors">
                        SENHA <i class="bi bi-arrow-down-up text-xs"></i>
                    </button>
                </th>
                <th class="px-4 py-3 text-left">
                    <button onclick="sortBy('contact')" class="text-gray-500 dark:text-gray-400 hover:text-orange-500 dark:hover:text-white flex items-center gap-1 text-sm font-medium uppercase tracking-wider transition-colors">
                        TELEFONE <i class="bi bi-arrow-down-up text-xs"></i>
                    </button>
                </th>
                <th class="px-4 py-3 text-left">
                    <button onclick="sortBy('member_id')" class="text-gray-500 dark:text-gray-400 hover:text-orange-500 dark:hover:text-white flex items-center gap-1 text-sm font-medium uppercase tracking-wider transition-colors">
                        REVENDA <i class="bi bi-arrow-down-up text-xs"></i>
                    </button>
                </th>
                <th class="px-4 py-3 text-left">
                    <button onclick="sortBy('is_trial')" class="text-gray-500 dark:text-gray-400 hover:text-orange-500 dark:hover:text-white flex items-center gap-1 text-sm font-medium uppercase tracking-wider transition-colors">
                        TIPO <i class="bi bi-arrow-down-up text-xs"></i>
                    </button>
                </th>
                <th class="px-4 py-3 text-left">
                    <button onclick="sortBy('created_at')" class="text-gray-500 dark:text-gray-400 hover:text-orange-500 dark:hover:text-white flex items-center gap-1 text-sm font-medium uppercase tracking-wider transition-colors">
                        CRIADO <i class="bi bi-arrow-down-up text-xs"></i>
                    </button>
                </th>
                <th class="px-4 py-3 text-left">
                    <button onclick="sortBy('exp_date')" class="text-gray-500 dark:text-gray-400 hover:text-orange-500 dark:hover:text-white flex items-center gap-1 text-sm font-medium uppercase tracking-wider transition-colors">
                        VENCIMENTO <i class="bi bi-arrow-down-up text-xs"></i>
                    </button>
                </th>
                <th class="px-4 py-3 text-left">
                    <button onclick="sortBy('max_connections')" class="text-gray-500 dark:text-gray-400 hover:text-orange-500 dark:hover:text-white flex items-center gap-1 text-sm font-medium uppercase tracking-wider transition-colors">
                        CONEX&Otilde;ES <i class="bi bi-arrow-down-up text-xs"></i>
                    </button>
                </th>
                <th class="px-4 py-3 text-left">
                    <span class="text-gray-500 dark:text-gray-400 text-sm font-medium uppercase tracking-wider">STATUS</span>
                </th>
                <th class="px-4 py-3 text-left">
                    <span class="text-gray-500 dark:text-gray-400 text-sm font-medium uppercase tracking-wider">A&Ccedil;&Otilde;ES</span>
                </th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-dark-200 bg-white dark:bg-dark-300">
            @forelse($clients as $client)
            <tr class="hover:bg-gray-50 dark:hover:bg-dark-200/50 transition-colors">
                <td class="px-4 py-4">
                    <span class="text-gray-900 dark:text-white font-medium">{{ $client->username }}</span>
                </td>
                <td class="px-4 py-4">
                    <span class="text-gray-600 dark:text-gray-300 font-mono text-sm">{{ $client->password }}</span>
                </td>
                <td class="px-4 py-4">
                    <span class="text-gray-600 dark:text-gray-300 text-sm">{{ $client->contact ?? '-' }}</span>
                </td>
                <td class="px-4 py-4">
                    <span class="text-gray-600 dark:text-gray-300 text-sm">{{ $client->member->username ?? 'N/A' }}</span>
                </td>
                <td class="px-4 py-4">
                    @if($client->is_trial)
                        <span class="px-3 py-1 bg-yellow-100 dark:bg-yellow-500/20 text-yellow-700 dark:text-yellow-400 rounded-full text-xs font-semibold border border-yellow-200 dark:border-transparent">Teste</span>
                    @else
                        <span class="px-3 py-1 bg-green-100 dark:bg-green-500/20 text-green-700 dark:text-green-400 rounded-full text-xs font-semibold border border-green-200 dark:border-transparent">Cliente</span>
                    @endif
                </td>
                <td class="px-4 py-4">
                    <span class="text-gray-500 dark:text-gray-400 text-sm">{{ date('d/m/Y H:i', $client->created_at) }}</span>
                </td>
                <td class="px-4 py-4">
                    @php
                        $isExpired = $client->exp_date <= time();
                        $daysLeft = ceil(($client->exp_date - time()) / 86400);
                        
                        $textClass = 'text-gray-600 dark:text-gray-300';
                        if ($isExpired) {
                            $textClass = 'text-red-600 dark:text-red-400 font-semibold';
                        } elseif ($daysLeft <= 7) {
                            $textClass = 'text-yellow-600 dark:text-yellow-400 font-medium';
                        }
                    @endphp
                    <span class="text-sm {{ $textClass }}">
                        {{ date('d/m/Y H:i', $client->exp_date) }}
                    </span>
                </td>
                <td class="px-4 py-4">
                    <span class="text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="bi bi-wifi text-orange-500"></i>
                        {{ $client->max_connections }}
                    </span>
                </td>
                <td class="px-4 py-4">
                    @if($client->enabled && ($client->admin_enabled ?? true) && $client->exp_date > time())
                        <span class="px-3 py-1 bg-green-100 dark:bg-green-500/20 text-green-700 dark:text-green-400 rounded-full text-xs font-semibold border border-green-200 dark:border-transparent">Ativo</span>
                    @else
                        <span class="px-3 py-1 bg-red-100 dark:bg-red-500/20 text-red-700 dark:text-red-400 rounded-full text-xs font-semibold border border-red-200 dark:border-transparent">Inativo</span>
                    @endif
                </td>
                <td class="px-4 py-4">
                    <div class="flex gap-2">
                        <button onclick="openM3uModal({{ $client->id }}, '{{ $client->username }}')" class="p-2 bg-blue-100 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400 hover:bg-blue-200 dark:hover:bg-blue-500/30 rounded-lg transition-colors border border-blue-200 dark:border-transparent" title="M3U">
                            <i class="bi bi-file-earmark-code"></i>
                        </button>
                        <button onclick="openEditModal({{ $client->id }})" class="p-2 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors border border-gray-200 dark:border-transparent" title="Editar">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button onclick="openRenewModal({{ $client->id }})" class="p-2 bg-green-100 dark:bg-green-500/20 text-green-600 dark:text-green-400 hover:bg-green-200 dark:hover:bg-green-500/30 rounded-lg transition-colors border border-green-200 dark:border-transparent" title="Renovar">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>
                        @if($client->contact)
                        <button onclick="window.open('https://wa.me/{{ preg_replace('/[^0-9]/', '', $client->contact ?? '') }}', '_blank')" class="p-2 bg-orange-100 dark:bg-orange-500/20 text-orange-600 dark:text-orange-400 hover:bg-orange-200 dark:hover:bg-orange-500/30 rounded-lg transition-colors border border-orange-200 dark:border-transparent" title="WhatsApp">
                            <i class="bi bi-whatsapp"></i>
                        </button>
                        @endif
                        <button onclick="deleteClient({{ $client->id }})" class="p-2 bg-red-100 dark:bg-red-500/20 text-red-600 dark:text-red-400 hover:bg-red-200 dark:hover:bg-red-500/30 rounded-lg transition-colors border border-red-200 dark:border-transparent" title="Excluir">
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
