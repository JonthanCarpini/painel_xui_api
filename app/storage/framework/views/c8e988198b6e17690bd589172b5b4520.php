<div class="flex items-center justify-between mt-6">
    <div class="flex items-center gap-4">
        <span class="text-gray-400 text-sm">
            Mostrando <?php echo e($clients->firstItem() ?? 0); ?> a <?php echo e($clients->lastItem() ?? 0); ?> de <?php echo e($clients->total()); ?> clientes
        </span>
        
        <select id="perPageSelect" onchange="changePerPage(this.value)" class="px-3 py-2 bg-dark-200 border border-dark-100 rounded-lg text-white text-sm focus:border-orange-500 focus:outline-none">
            <option value="20" <?php echo e(request('per_page', 20) == 20 ? 'selected' : ''); ?>>20 por página</option>
            <option value="50" <?php echo e(request('per_page', 20) == 50 ? 'selected' : ''); ?>>50 por página</option>
            <option value="100" <?php echo e(request('per_page', 20) == 100 ? 'selected' : ''); ?>>100 por página</option>
            <option value="500" <?php echo e(request('per_page', 20) == 500 ? 'selected' : ''); ?>>500 por página</option>
            <option value="1000" <?php echo e(request('per_page', 20) == 1000 ? 'selected' : ''); ?>>1000 por página</option>
        </select>
    </div>

    <?php if($clients->hasPages()): ?>
    <div class="flex items-center gap-2">
        
        <?php if($clients->onFirstPage()): ?>
            <button disabled class="px-3 py-2 bg-dark-200 text-gray-500 rounded-lg cursor-not-allowed">
                <i class="bi bi-chevron-double-left"></i>
            </button>
        <?php else: ?>
            <button onclick="goToPage(1)" class="px-3 py-2 bg-dark-200 text-white rounded-lg hover:bg-orange-500 transition-colors">
                <i class="bi bi-chevron-double-left"></i>
            </button>
        <?php endif; ?>

        
        <?php if($clients->onFirstPage()): ?>
            <button disabled class="px-3 py-2 bg-dark-200 text-gray-500 rounded-lg cursor-not-allowed">
                <i class="bi bi-chevron-left"></i>
            </button>
        <?php else: ?>
            <button onclick="goToPage(<?php echo e($clients->currentPage() - 1); ?>)" class="px-3 py-2 bg-dark-200 text-white rounded-lg hover:bg-orange-500 transition-colors">
                <i class="bi bi-chevron-left"></i>
            </button>
        <?php endif; ?>

        
        <?php
            $start = max(1, $clients->currentPage() - 2);
            $end = min($clients->lastPage(), $clients->currentPage() + 2);
        ?>

        <?php for($i = $start; $i <= $end; $i++): ?>
            <?php if($i == $clients->currentPage()): ?>
                <button class="px-4 py-2 bg-orange-500 text-white rounded-lg font-medium">
                    <?php echo e($i); ?>

                </button>
            <?php else: ?>
                <button onclick="goToPage(<?php echo e($i); ?>)" class="px-4 py-2 bg-dark-200 text-white rounded-lg hover:bg-orange-500 transition-colors">
                    <?php echo e($i); ?>

                </button>
            <?php endif; ?>
        <?php endfor; ?>

        
        <?php if($clients->hasMorePages()): ?>
            <button onclick="goToPage(<?php echo e($clients->currentPage() + 1); ?>)" class="px-3 py-2 bg-dark-200 text-white rounded-lg hover:bg-orange-500 transition-colors">
                <i class="bi bi-chevron-right"></i>
            </button>
        <?php else: ?>
            <button disabled class="px-3 py-2 bg-dark-200 text-gray-500 rounded-lg cursor-not-allowed">
                <i class="bi bi-chevron-right"></i>
            </button>
        <?php endif; ?>

        
        <?php if($clients->hasMorePages()): ?>
            <button onclick="goToPage(<?php echo e($clients->lastPage()); ?>)" class="px-3 py-2 bg-dark-200 text-white rounded-lg hover:bg-orange-500 transition-colors">
                <i class="bi bi-chevron-double-right"></i>
            </button>
        <?php else: ?>
            <button disabled class="px-3 py-2 bg-dark-200 text-gray-500 rounded-lg cursor-not-allowed">
                <i class="bi bi-chevron-double-right"></i>
            </button>
        <?php endif; ?>

        
        <div class="flex items-center gap-2 ml-4">
            <span class="text-gray-400 text-sm">Ir para:</span>
            <input type="number" id="gotoPageInput" min="1" max="<?php echo e($clients->lastPage()); ?>" placeholder="<?php echo e($clients->currentPage()); ?>" class="w-20 px-3 py-2 bg-dark-200 border border-dark-100 rounded-lg text-white text-sm text-center focus:border-orange-500 focus:outline-none">
            <button onclick="goToPage(document.getElementById('gotoPageInput').value)" class="px-3 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors text-sm">
                <i class="bi bi-arrow-right"></i>
            </button>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php /**PATH C:\Users\admin\Documents\Projetos\painel_xui\app\resources\views/clients/partials/pagination.blade.php ENDPATH**/ ?>