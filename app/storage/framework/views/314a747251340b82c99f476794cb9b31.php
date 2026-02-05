<div class="overflow-x-auto">
    <table class="w-full">
        <thead>
            <tr class="border-b border-dark-200">
                <th class="px-4 py-3 text-left">
                    <button onclick="sortBy('username')" class="text-gray-400 hover:text-white flex items-center gap-1 text-sm font-medium uppercase tracking-wider">
                        USERNAME <i class="bi bi-arrow-down-up text-xs"></i>
                    </button>
                </th>
                <th class="px-4 py-3 text-left">
                    <button onclick="sortBy('password')" class="text-gray-400 hover:text-white flex items-center gap-1 text-sm font-medium uppercase tracking-wider">
                        SENHA <i class="bi bi-arrow-down-up text-xs"></i>
                    </button>
                </th>
                <th class="px-4 py-3 text-left">
                    <button onclick="sortBy('contact')" class="text-gray-400 hover:text-white flex items-center gap-1 text-sm font-medium uppercase tracking-wider">
                        TELEFONE <i class="bi bi-arrow-down-up text-xs"></i>
                    </button>
                </th>
                <th class="px-4 py-3 text-left">
                    <button onclick="sortBy('member_id')" class="text-gray-400 hover:text-white flex items-center gap-1 text-sm font-medium uppercase tracking-wider">
                        REVENDA <i class="bi bi-arrow-down-up text-xs"></i>
                    </button>
                </th>
                <th class="px-4 py-3 text-left">
                    <button onclick="sortBy('is_trial')" class="text-gray-400 hover:text-white flex items-center gap-1 text-sm font-medium uppercase tracking-wider">
                        TIPO <i class="bi bi-arrow-down-up text-xs"></i>
                    </button>
                </th>
                <th class="px-4 py-3 text-left">
                    <button onclick="sortBy('created_at')" class="text-gray-400 hover:text-white flex items-center gap-1 text-sm font-medium uppercase tracking-wider">
                        CRIADO <i class="bi bi-arrow-down-up text-xs"></i>
                    </button>
                </th>
                <th class="px-4 py-3 text-left">
                    <button onclick="sortBy('exp_date')" class="text-gray-400 hover:text-white flex items-center gap-1 text-sm font-medium uppercase tracking-wider">
                        VENCIMENTO <i class="bi bi-arrow-down-up text-xs"></i>
                    </button>
                </th>
                <th class="px-4 py-3 text-left">
                    <button onclick="sortBy('max_connections')" class="text-gray-400 hover:text-white flex items-center gap-1 text-sm font-medium uppercase tracking-wider">
                        CONEXÕES <i class="bi bi-arrow-down-up text-xs"></i>
                    </button>
                </th>
                <th class="px-4 py-3 text-left">
                    <span class="text-gray-400 text-sm font-medium uppercase tracking-wider">STATUS</span>
                </th>
                <th class="px-4 py-3 text-left">
                    <span class="text-gray-400 text-sm font-medium uppercase tracking-wider">AÇÕES</span>
                </th>
            </tr>
        </thead>
        <tbody class="divide-y divide-dark-200">
            <?php $__empty_1 = true; $__currentLoopData = $clients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $client): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr class="hover:bg-dark-200/50 transition-colors">
                <td class="px-4 py-4">
                    <span class="text-white font-medium"><?php echo e($client->username); ?></span>
                </td>
                <td class="px-4 py-4">
                    <span class="text-gray-300"><?php echo e($client->password); ?></span>
                </td>
                <td class="px-4 py-4">
                    <span class="text-gray-300"><?php echo e($client->admin_notes ?? '-'); ?></span>
                </td>
                <td class="px-4 py-4">
                    <span class="text-gray-300"><?php echo e($client->member->username ?? 'N/A'); ?></span>
                </td>
                <td class="px-4 py-4">
                    <?php if($client->is_trial): ?>
                        <span class="px-3 py-1 bg-yellow-500/20 text-yellow-400 rounded-full text-xs font-medium">Teste</span>
                    <?php else: ?>
                        <span class="px-3 py-1 bg-green-500/20 text-green-400 rounded-full text-xs font-medium">Cliente</span>
                    <?php endif; ?>
                </td>
                <td class="px-4 py-4">
                    <span class="text-gray-400 text-sm"><?php echo e(date('d/m/Y, H:i', $client->created_at)); ?></span>
                </td>
                <td class="px-4 py-4">
                    <?php
                        $isExpired = $client->exp_date <= time();
                        $daysLeft = ceil(($client->exp_date - time()) / 86400);
                    ?>
                    <span class="text-sm <?php echo e($isExpired ? 'text-red-400' : ($daysLeft <= 7 ? 'text-yellow-400' : 'text-gray-300')); ?>">
                        <?php echo e(date('d/m/Y, H:i', $client->exp_date)); ?>

                    </span>
                </td>
                <td class="px-4 py-4">
                    <span class="text-white flex items-center gap-1">
                        <i class="bi bi-wifi text-orange-500"></i>
                        <?php echo e($client->max_connections); ?>

                    </span>
                </td>
                <td class="px-4 py-4">
                    <?php if($client->enabled && $client->admin_enabled && $client->exp_date > time()): ?>
                        <span class="px-3 py-1 bg-green-500/20 text-green-400 rounded-full text-xs font-medium">Ativo</span>
                    <?php else: ?>
                        <span class="px-3 py-1 bg-red-500/20 text-red-400 rounded-full text-xs font-medium">Inativo</span>
                    <?php endif; ?>
                </td>
                <td class="px-4 py-4">
                    <div class="flex gap-2">
                        <button onclick="window.location.href='/clients/<?php echo e($client->id); ?>/m3u'" class="p-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors" title="M3U">
                            <i class="bi bi-file-earmark-code"></i>
                        </button>
                        <button onclick="openEditModal(<?php echo e($client->id); ?>)" class="p-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors" title="Editar">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button onclick="openRenewModal(<?php echo e($client->id); ?>, '<?php echo e($client->username); ?>')" class="p-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors" title="Renovar">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>
                        <button onclick="window.open('https://wa.me/<?php echo e(preg_replace('/[^0-9]/', '', $client->contact ?? '')); ?>', '_blank')" class="p-2 bg-orange-500 hover:bg-orange-600 text-white rounded-lg transition-colors" title="WhatsApp">
                            <i class="bi bi-whatsapp"></i>
                        </button>
                        <button onclick="window.location.href='/clients/<?php echo e($client->id); ?>/logs'" class="p-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors" title="Logs">
                            <i class="bi bi-clock-history"></i>
                        </button>
                        <button onclick="confirmDelete(<?php echo e($client->id); ?>)" class="p-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors" title="Excluir">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
                <td colspan="10" class="px-4 py-8 text-center text-gray-400">
                    Nenhum cliente encontrado
                </td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php /**PATH C:\Users\admin\Documents\Projetos\painel_xui\app\resources\views/clients/partials/table.blade.php ENDPATH**/ ?>