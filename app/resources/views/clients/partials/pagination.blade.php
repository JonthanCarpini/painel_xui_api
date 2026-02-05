<div class="flex flex-col md:flex-row items-center justify-between mt-6 gap-4">
    <div class="flex flex-col md:flex-row items-center gap-4 w-full md:w-auto">
        <span class="text-gray-500 dark:text-gray-400 text-sm order-2 md:order-1">
            Mostrando {{ $clients->firstItem() ?? 0 }} a {{ $clients->lastItem() ?? 0 }} de {{ $clients->total() }} clientes
        </span>
        
        <select id="perPageSelect" onchange="changePerPage(this.value)" class="order-1 md:order-2 px-3 py-2 bg-white dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-700 dark:text-white text-sm focus:border-orange-500 focus:outline-none transition-colors">
            <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20 por p&aacute;gina</option>
            <option value="50" {{ request('per_page', 20) == 50 ? 'selected' : '' }}>50 por p&aacute;gina</option>
            <option value="100" {{ request('per_page', 20) == 100 ? 'selected' : '' }}>100 por p&aacute;gina</option>
            <option value="500" {{ request('per_page', 20) == 500 ? 'selected' : '' }}>500 por p&aacute;gina</option>
            <option value="1000" {{ request('per_page', 20) == 1000 ? 'selected' : '' }}>1000 por p&aacute;gina</option>
        </select>
    </div>

    @if($clients->hasPages())
    <div class="flex items-center gap-2 overflow-x-auto pb-2 md:pb-0 max-w-full">
        {{-- Primeira página --}}
        @if($clients->onFirstPage())
            <button disabled class="px-3 py-2 bg-gray-100 dark:bg-dark-200 text-gray-400 dark:text-gray-600 rounded-lg cursor-not-allowed border border-gray-200 dark:border-transparent">
                <i class="bi bi-chevron-double-left"></i>
            </button>
        @else
            <button onclick="goToPage(1)" class="px-3 py-2 bg-white dark:bg-dark-200 text-gray-600 dark:text-white rounded-lg hover:bg-orange-500 hover:text-white hover:border-orange-500 transition-colors border border-gray-200 dark:border-transparent" title="Primeira p&aacute;gina">
                <i class="bi bi-chevron-double-left"></i>
            </button>
        @endif

        {{-- Página anterior --}}
        @if($clients->onFirstPage())
            <button disabled class="px-3 py-2 bg-gray-100 dark:bg-dark-200 text-gray-400 dark:text-gray-600 rounded-lg cursor-not-allowed border border-gray-200 dark:border-transparent">
                <i class="bi bi-chevron-left"></i>
            </button>
        @else
            <button onclick="goToPage({{ $clients->currentPage() - 1 }})" class="px-3 py-2 bg-white dark:bg-dark-200 text-gray-600 dark:text-white rounded-lg hover:bg-orange-500 hover:text-white hover:border-orange-500 transition-colors border border-gray-200 dark:border-transparent" title="P&aacute;gina anterior">
                <i class="bi bi-chevron-left"></i>
            </button>
        @endif

        {{-- Números de página --}}
        @php
            $start = max(1, $clients->currentPage() - 2);
            $end = min($clients->lastPage(), $clients->currentPage() + 2);
        @endphp

        @for($i = $start; $i <= $end; $i++)
            @if($i == $clients->currentPage())
                <button class="px-4 py-2 bg-orange-500 text-white rounded-lg font-medium shadow-md shadow-orange-500/20">
                    {{ $i }}
                </button>
            @else
                <button onclick="goToPage({{ $i }})" class="px-4 py-2 bg-white dark:bg-dark-200 text-gray-600 dark:text-white rounded-lg hover:bg-orange-500 hover:text-white hover:border-orange-500 transition-colors border border-gray-200 dark:border-transparent">
                    {{ $i }}
                </button>
            @endif
        @endfor

        {{-- Próxima página --}}
        @if($clients->hasMorePages())
            <button onclick="goToPage({{ $clients->currentPage() + 1 }})" class="px-3 py-2 bg-white dark:bg-dark-200 text-gray-600 dark:text-white rounded-lg hover:bg-orange-500 hover:text-white hover:border-orange-500 transition-colors border border-gray-200 dark:border-transparent" title="Pr&oacute;xima p&aacute;gina">
                <i class="bi bi-chevron-right"></i>
            </button>
        @else
            <button disabled class="px-3 py-2 bg-gray-100 dark:bg-dark-200 text-gray-400 dark:text-gray-600 rounded-lg cursor-not-allowed border border-gray-200 dark:border-transparent">
                <i class="bi bi-chevron-right"></i>
            </button>
        @endif

        {{-- Última página --}}
        @if($clients->hasMorePages())
            <button onclick="goToPage({{ $clients->lastPage() }})" class="px-3 py-2 bg-white dark:bg-dark-200 text-gray-600 dark:text-white rounded-lg hover:bg-orange-500 hover:text-white hover:border-orange-500 transition-colors border border-gray-200 dark:border-transparent" title="&Uacute;ltima p&aacute;gina">
                <i class="bi bi-chevron-double-right"></i>
            </button>
        @else
            <button disabled class="px-3 py-2 bg-gray-100 dark:bg-dark-200 text-gray-400 dark:text-gray-600 rounded-lg cursor-not-allowed border border-gray-200 dark:border-transparent">
                <i class="bi bi-chevron-double-right"></i>
            </button>
        @endif

        {{-- Input para ir para página específica --}}
        <div class="flex items-center gap-2 ml-4 border-l border-gray-200 dark:border-dark-100 pl-4 hidden md:flex">
            <span class="text-gray-500 dark:text-gray-400 text-sm">Ir para:</span>
            <input type="number" id="gotoPageInput" min="1" max="{{ $clients->lastPage() }}" placeholder="{{ $clients->currentPage() }}" class="w-16 px-3 py-2 bg-white dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-700 dark:text-white text-sm text-center focus:border-orange-500 focus:outline-none transition-colors">
            <button onclick="goToPage(document.getElementById('gotoPageInput').value)" class="px-3 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors text-sm shadow-sm">
                <i class="bi bi-arrow-right"></i>
            </button>
        </div>
    </div>
    @endif
</div>
