<div class="flex items-center justify-between mt-6">
    <div class="flex items-center gap-4">
        <span class="text-gray-400 text-sm">
            Mostrando {{ $clients->firstItem() ?? 0 }} a {{ $clients->lastItem() ?? 0 }} de {{ $clients->total() }} clientes
        </span>
        
        <select id="perPageSelect" onchange="changePerPage(this.value)" class="px-3 py-2 bg-dark-200 border border-dark-100 rounded-lg text-white text-sm focus:border-orange-500 focus:outline-none">
            <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20 por página</option>
            <option value="50" {{ request('per_page', 20) == 50 ? 'selected' : '' }}>50 por página</option>
            <option value="100" {{ request('per_page', 20) == 100 ? 'selected' : '' }}>100 por página</option>
            <option value="500" {{ request('per_page', 20) == 500 ? 'selected' : '' }}>500 por página</option>
            <option value="1000" {{ request('per_page', 20) == 1000 ? 'selected' : '' }}>1000 por página</option>
        </select>
    </div>

    @if($clients->hasPages())
    <div class="flex items-center gap-2">
        {{-- Primeira página --}}
        @if($clients->onFirstPage())
            <button disabled class="px-3 py-2 bg-dark-200 text-gray-500 rounded-lg cursor-not-allowed">
                <i class="bi bi-chevron-double-left"></i>
            </button>
        @else
            <button onclick="goToPage(1)" class="px-3 py-2 bg-dark-200 text-white rounded-lg hover:bg-orange-500 transition-colors">
                <i class="bi bi-chevron-double-left"></i>
            </button>
        @endif

        {{-- Página anterior --}}
        @if($clients->onFirstPage())
            <button disabled class="px-3 py-2 bg-dark-200 text-gray-500 rounded-lg cursor-not-allowed">
                <i class="bi bi-chevron-left"></i>
            </button>
        @else
            <button onclick="goToPage({{ $clients->currentPage() - 1 }})" class="px-3 py-2 bg-dark-200 text-white rounded-lg hover:bg-orange-500 transition-colors">
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
                <button class="px-4 py-2 bg-orange-500 text-white rounded-lg font-medium">
                    {{ $i }}
                </button>
            @else
                <button onclick="goToPage({{ $i }})" class="px-4 py-2 bg-dark-200 text-white rounded-lg hover:bg-orange-500 transition-colors">
                    {{ $i }}
                </button>
            @endif
        @endfor

        {{-- Próxima página --}}
        @if($clients->hasMorePages())
            <button onclick="goToPage({{ $clients->currentPage() + 1 }})" class="px-3 py-2 bg-dark-200 text-white rounded-lg hover:bg-orange-500 transition-colors">
                <i class="bi bi-chevron-right"></i>
            </button>
        @else
            <button disabled class="px-3 py-2 bg-dark-200 text-gray-500 rounded-lg cursor-not-allowed">
                <i class="bi bi-chevron-right"></i>
            </button>
        @endif

        {{-- Última página --}}
        @if($clients->hasMorePages())
            <button onclick="goToPage({{ $clients->lastPage() }})" class="px-3 py-2 bg-dark-200 text-white rounded-lg hover:bg-orange-500 transition-colors">
                <i class="bi bi-chevron-double-right"></i>
            </button>
        @else
            <button disabled class="px-3 py-2 bg-dark-200 text-gray-500 rounded-lg cursor-not-allowed">
                <i class="bi bi-chevron-double-right"></i>
            </button>
        @endif

        {{-- Input para ir para página específica --}}
        <div class="flex items-center gap-2 ml-4">
            <span class="text-gray-400 text-sm">Ir para:</span>
            <input type="number" id="gotoPageInput" min="1" max="{{ $clients->lastPage() }}" placeholder="{{ $clients->currentPage() }}" class="w-20 px-3 py-2 bg-dark-200 border border-dark-100 rounded-lg text-white text-sm text-center focus:border-orange-500 focus:outline-none">
            <button onclick="goToPage(document.getElementById('gotoPageInput').value)" class="px-3 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors text-sm">
                <i class="bi bi-arrow-right"></i>
            </button>
        </div>
    </div>
    @endif
</div>
