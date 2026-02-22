@extends('layouts.app')

@section('title', 'Pedidos de Filmes e Séries')

@section('content')
<div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-6 md:mb-8 gap-4">
    <div>
        <h1 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
            <i class="bi bi-film text-orange-500"></i>
            Pedidos de Filmes e Séries
        </h1>
        <p class="text-sm md:text-base text-gray-500 dark:text-gray-400 mt-1">Busque e solicite a inclusão de filmes ou séries no servidor.</p>
    </div>
</div>

@if(!$hasTmdbKey)
<div class="bg-yellow-50 dark:bg-yellow-500/10 border border-yellow-200 dark:border-yellow-500/30 rounded-xl p-4 mb-6">
    <div class="flex items-center gap-3">
        <i class="bi bi-exclamation-triangle text-yellow-500 text-xl"></i>
        <p class="text-yellow-700 dark:text-yellow-400 text-sm">A API Key do TMDB não está configurada. Contate o administrador para habilitar a busca.</p>
    </div>
</div>
@endif

<!-- Busca -->
<div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 p-6 mb-6 shadow-sm dark:shadow-none" x-data="vodSearch()">
    <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
        <i class="bi bi-search"></i> Buscar no TMDB
    </h2>

    <div class="flex flex-col sm:flex-row gap-3 mb-4">
        <!-- Tipo -->
        <div class="flex bg-gray-100 dark:bg-dark-200 rounded-lg p-1 shrink-0">
            <button type="button" @click="type = 'movie'" :class="type === 'movie' ? 'bg-orange-500 text-white shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'" class="px-4 py-2 rounded-md text-sm font-medium transition-all">
                <i class="bi bi-film"></i> Filme
            </button>
            <button type="button" @click="type = 'series'" :class="type === 'series' ? 'bg-orange-500 text-white shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'" class="px-4 py-2 rounded-md text-sm font-medium transition-all">
                <i class="bi bi-tv"></i> Série
            </button>
        </div>

        <!-- Campo de busca -->
        <div class="flex-1 flex gap-2">
            <input type="text" x-model="query" @keydown.enter="search()" placeholder="Digite o nome do filme ou série..." class="flex-1 px-4 py-2 bg-gray-100 dark:bg-dark-200 border border-gray-200 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-orange-500 focus:border-transparent text-sm" :disabled="loading">
            <button @click="search()" :disabled="loading || query.length < 2" class="px-6 py-2 bg-orange-500 hover:bg-orange-600 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-lg text-sm font-medium transition-colors flex items-center gap-2 shrink-0">
                <i class="bi" :class="loading ? 'bi-arrow-repeat animate-spin' : 'bi-search'"></i>
                Buscar
            </button>
        </div>
    </div>

    <!-- Erro -->
    <div x-show="error" x-cloak class="bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 rounded-lg p-3 mb-4">
        <p class="text-red-600 dark:text-red-400 text-sm" x-text="error"></p>
    </div>

    <!-- Resultados da busca -->
    <div x-show="results.length > 0" x-cloak>
        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3" x-text="'Resultados (' + results.length + ')'"></h3>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
            <template x-for="item in results" :key="item.tmdb_id">
                <div @click="selectItem(item)" class="bg-gray-50 dark:bg-dark-200 rounded-xl border border-gray-200 dark:border-dark-100 overflow-hidden shadow-sm hover:shadow-md hover:border-orange-300 dark:hover:border-orange-500/50 transition-all cursor-pointer group">
                    <div class="aspect-[2/3] bg-gray-200 dark:bg-dark-100 relative overflow-hidden">
                        <img :src="item.poster_path ? 'https://image.tmdb.org/t/p/w300' + item.poster_path : 'https://ui-avatars.com/api/?name=' + encodeURIComponent(item.title) + '&background=random&size=300'" :alt="item.title" class="w-full h-full object-cover transition-transform group-hover:scale-105" loading="lazy">
                        <div class="absolute top-2 right-2">
                            <span class="bg-black/70 text-yellow-400 text-xs font-bold px-2 py-1 rounded-full flex items-center gap-1" x-show="item.vote_average > 0">
                                <i class="bi bi-star-fill"></i> <span x-text="item.vote_average.toFixed(1)"></span>
                            </span>
                        </div>
                    </div>
                    <div class="p-3">
                        <h4 class="text-sm font-bold text-gray-900 dark:text-white line-clamp-2" x-text="item.title"></h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1" x-text="item.release_date ? item.release_date.substring(0, 4) : ''"></p>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Modal de detalhes / verificação -->
    <div x-show="selectedItem !== null" x-cloak class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4" @click.self="selectedItem = null" @keydown.escape.window="selectedItem = null">
        <div class="bg-white dark:bg-dark-300 rounded-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto shadow-2xl" @click.stop>
            <!-- Header com backdrop -->
            <div class="relative h-48 bg-gray-200 dark:bg-dark-200 overflow-hidden rounded-t-2xl">
                <img x-show="selectedItem?.backdrop_path" :src="selectedItem?.backdrop_path ? 'https://image.tmdb.org/t/p/w780' + selectedItem.backdrop_path : ''" class="w-full h-full object-cover" loading="lazy">
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent"></div>
                <button @click="selectedItem = null" class="absolute top-3 right-3 bg-black/50 hover:bg-black/70 text-white rounded-full w-8 h-8 flex items-center justify-center transition-colors">
                    <i class="bi bi-x-lg"></i>
                </button>
                <div class="absolute bottom-4 left-4 right-4">
                    <h3 class="text-white text-xl font-bold" x-text="selectedItem?.title"></h3>
                    <p class="text-gray-300 text-sm" x-text="selectedItem?.original_title"></p>
                </div>
            </div>

            <div class="p-6">
                <!-- Loading check -->
                <div x-show="checking" class="flex items-center justify-center py-8">
                    <i class="bi bi-arrow-repeat animate-spin text-orange-500 text-2xl mr-3"></i>
                    <span class="text-gray-500 dark:text-gray-400">Verificando no servidor...</span>
                </div>

                <!-- Erro na verificação -->
                <div x-show="!checking && checkError" x-cloak>
                    <div class="bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 rounded-xl p-4 mb-4">
                        <div class="flex items-center gap-2 mb-2">
                            <i class="bi bi-exclamation-triangle-fill text-red-500 text-lg"></i>
                            <span class="text-red-700 dark:text-red-400 font-semibold">Erro na verificação</span>
                        </div>
                        <p class="text-red-600 dark:text-red-400 text-sm" x-text="checkError"></p>
                    </div>
                    <div class="flex gap-3">
                        <button @click="selectedItem = null; checkError = null" class="flex-1 py-3 bg-gray-200 dark:bg-dark-200 hover:bg-gray-300 dark:hover:bg-dark-100 text-gray-700 dark:text-gray-300 rounded-xl font-medium transition-colors">
                            Fechar
                        </button>
                        <button @click="selectItem(selectedItem)" class="flex-1 py-3 bg-orange-500 hover:bg-orange-600 text-white rounded-xl font-medium transition-colors flex items-center justify-center gap-2">
                            <i class="bi bi-arrow-repeat"></i> Tentar novamente
                        </button>
                    </div>
                </div>

                <!-- Resultado: FILME já existe no servidor -->
                <div x-show="!checking && !checkError && checkResult?.exists && type === 'movie'" x-cloak>
                    <div class="bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/30 rounded-xl p-4 mb-4">
                        <div class="flex items-center gap-2 mb-3">
                            <i class="bi bi-check-circle-fill text-green-500 text-lg"></i>
                            <span class="text-green-700 dark:text-green-400 font-semibold">Já existe no servidor!</span>
                        </div>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Nome:</span>
                                <span class="text-gray-900 dark:text-white font-medium" x-text="checkResult?.data?.name"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Categoria:</span>
                                <span class="text-gray-900 dark:text-white font-medium" x-text="checkResult?.data?.category"></span>
                            </div>
                            <div class="flex justify-between" x-show="checkResult?.data?.added_date">
                                <span class="text-gray-500 dark:text-gray-400">Adicionado em:</span>
                                <span class="text-gray-900 dark:text-white font-medium" x-text="checkResult?.data?.added_date"></span>
                            </div>
                        </div>
                    </div>
                    <button @click="selectedItem = null; checkResult = null" class="w-full py-3 bg-gray-200 dark:bg-dark-200 hover:bg-gray-300 dark:hover:bg-dark-100 text-gray-700 dark:text-gray-300 rounded-xl font-medium transition-colors">
                        Fechar
                    </button>
                </div>

                <!-- Resultado: SÉRIE - busca profunda com temporadas -->
                <div x-show="!checking && !checkError && checkResult && type === 'series'" x-cloak>
                    <p class="text-gray-600 dark:text-gray-400 text-sm mb-4 line-clamp-3" x-text="selectedItem?.overview"></p>

                    <!-- Info da série no XUI -->
                    <template x-if="checkResult?.exists">
                        <div class="bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/30 rounded-xl p-3 mb-4">
                            <div class="flex items-center gap-2 mb-1">
                                <i class="bi bi-check-circle-fill text-green-500"></i>
                                <span class="text-green-700 dark:text-green-400 font-semibold text-sm">Série encontrada no servidor</span>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400" x-text="checkResult?.data?.name + ' — ' + checkResult?.data?.category"></p>
                        </div>
                    </template>
                    <template x-if="!checkResult?.exists">
                        <div class="bg-yellow-50 dark:bg-yellow-500/10 border border-yellow-200 dark:border-yellow-500/30 rounded-xl p-3 mb-4">
                            <p class="text-yellow-700 dark:text-yellow-400 text-sm flex items-center gap-2">
                                <i class="bi bi-exclamation-triangle"></i>
                                Série não encontrada no servidor.
                            </p>
                        </div>
                    </template>

                    <!-- Lista de Temporadas -->
                    <template x-if="seasonsData && seasonsData.seasons && seasonsData.seasons.length > 0">
                        <div class="mb-4">
                            <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                                <i class="bi bi-collection-play text-orange-500"></i>
                                Temporadas
                                <span class="text-xs font-normal text-gray-400" x-text="'(' + seasonsData.seasons.length + ' no TMDB, ' + (seasonsData.xui_seasons?.length || 0) + ' no servidor)'"></span>
                            </h4>
                            <div class="space-y-2 max-h-60 overflow-y-auto pr-1">
                                <template x-for="season in seasonsData.seasons" :key="season.season_number">
                                    <div class="flex items-center justify-between p-3 rounded-lg border transition-colors"
                                         :class="season.exists_in_xui ? 'bg-green-50 dark:bg-green-500/5 border-green-200 dark:border-green-500/20' : 'bg-gray-50 dark:bg-dark-200 border-gray-200 dark:border-dark-100'">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 text-sm font-bold"
                                                 :class="season.exists_in_xui ? 'bg-green-100 dark:bg-green-500/20 text-green-600 dark:text-green-400' : 'bg-gray-200 dark:bg-dark-100 text-gray-500 dark:text-gray-400'">
                                                <span x-text="season.season_number"></span>
                                            </div>
                                            <div class="min-w-0">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate" x-text="season.name"></p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    <span x-text="season.episode_count + ' episódios'"></span>
                                                    <span x-show="season.air_date" x-text="' · ' + (season.air_date ? season.air_date.substring(0, 4) : '')"></span>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="shrink-0 ml-2">
                                            <template x-if="season.exists_in_xui">
                                                <span class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1 rounded-full bg-green-100 dark:bg-green-500/20 text-green-600 dark:text-green-400">
                                                    <i class="bi bi-check-circle"></i> No servidor
                                                </span>
                                            </template>
                                            <template x-if="!season.exists_in_xui">
                                                <button @click="submitRequest(season.season_number)" :disabled="submitting"
                                                    class="inline-flex items-center gap-1 text-xs font-medium px-3 py-1.5 rounded-full bg-orange-500 hover:bg-orange-600 disabled:opacity-50 text-white transition-colors">
                                                    <i class="bi" :class="submitting ? 'bi-arrow-repeat animate-spin' : 'bi-send'"></i> Solicitar
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    <!-- Solicitar série inteira (se não existe no XUI) -->
                    <template x-if="!checkResult?.exists">
                        <div class="flex gap-3">
                            <button @click="selectedItem = null; checkResult = null; seasonsData = null" class="flex-1 py-3 bg-gray-200 dark:bg-dark-200 hover:bg-gray-300 dark:hover:bg-dark-100 text-gray-700 dark:text-gray-300 rounded-xl font-medium transition-colors">
                                Cancelar
                            </button>
                            <button @click="submitRequest()" :disabled="submitting" class="flex-1 py-3 bg-orange-500 hover:bg-orange-600 disabled:opacity-50 text-white rounded-xl font-medium transition-colors flex items-center justify-center gap-2">
                                <i class="bi" :class="submitting ? 'bi-arrow-repeat animate-spin' : 'bi-send'"></i>
                                Solicitar Série Completa
                            </button>
                        </div>
                    </template>
                    <template x-if="checkResult?.exists">
                        <button @click="selectedItem = null; checkResult = null; seasonsData = null" class="w-full py-3 bg-gray-200 dark:bg-dark-200 hover:bg-gray-300 dark:hover:bg-dark-100 text-gray-700 dark:text-gray-300 rounded-xl font-medium transition-colors">
                            Fechar
                        </button>
                    </template>
                </div>

                <!-- Resultado: FILME não existe, pode solicitar -->
                <div x-show="!checking && !checkError && checkResult && !checkResult.exists && type === 'movie'" x-cloak>
                    <p class="text-gray-600 dark:text-gray-400 text-sm mb-4 line-clamp-4" x-text="selectedItem?.overview"></p>

                    <div class="flex items-center gap-4 text-sm text-gray-500 dark:text-gray-400 mb-4">
                        <span x-show="selectedItem?.release_date" class="flex items-center gap-1">
                            <i class="bi bi-calendar"></i> <span x-text="selectedItem?.release_date"></span>
                        </span>
                        <span x-show="selectedItem?.vote_average > 0" class="flex items-center gap-1 text-yellow-500">
                            <i class="bi bi-star-fill"></i> <span x-text="selectedItem?.vote_average.toFixed(1)"></span>
                        </span>
                    </div>

                    <div x-show="checkResult?.already_requested" class="bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-500/30 rounded-xl p-3 mb-4">
                        <p class="text-blue-700 dark:text-blue-400 text-sm flex items-center gap-2">
                            <i class="bi bi-info-circle"></i>
                            Este título já foi solicitado e está aguardando análise. Você pode reforçar o pedido.
                        </p>
                    </div>

                    <div class="flex gap-3">
                        <button @click="selectedItem = null; checkResult = null" class="flex-1 py-3 bg-gray-200 dark:bg-dark-200 hover:bg-gray-300 dark:hover:bg-dark-100 text-gray-700 dark:text-gray-300 rounded-xl font-medium transition-colors">
                            Cancelar
                        </button>
                        <button @click="submitRequest()" :disabled="submitting" class="flex-1 py-3 bg-orange-500 hover:bg-orange-600 disabled:opacity-50 text-white rounded-xl font-medium transition-colors flex items-center justify-center gap-2">
                            <i class="bi" :class="submitting ? 'bi-arrow-repeat animate-spin' : 'bi-send'"></i>
                            Solicitar
                        </button>
                    </div>
                </div>

                <!-- Sucesso -->
                <div x-show="submitSuccess" x-cloak>
                    <div class="text-center py-4">
                        <i class="bi bi-check-circle text-green-500 text-4xl mb-3"></i>
                        <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-1">Pedido Enviado!</h4>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Seu pedido foi registrado e será analisado pelo administrador.</p>
                    </div>
                    <button @click="selectedItem = null; checkResult = null; submitSuccess = false" class="w-full py-3 bg-orange-500 hover:bg-orange-600 text-white rounded-xl font-medium transition-colors mt-2">
                        Fechar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Meus Pedidos -->
<div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 shadow-sm dark:shadow-none overflow-hidden" x-data="{ activeTab: 'pending' }">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-dark-200">
        <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2 mb-4">
            <i class="bi bi-list-check"></i> Meus Pedidos
        </h2>
        <!-- Tabs -->
        <div class="flex gap-1 bg-gray-100 dark:bg-dark-200 rounded-lg p-1">
            <button @click="activeTab = 'pending'" :class="activeTab === 'pending' ? 'bg-yellow-500 text-white shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'" class="flex-1 py-2 px-3 rounded-md text-sm font-medium transition-all flex items-center justify-center gap-2">
                <i class="bi bi-clock"></i> Pendentes
                @if($pendingRequests->count() > 0)
                <span class="bg-white/20 text-xs px-1.5 py-0.5 rounded-full" x-show="activeTab === 'pending'">{{ $pendingRequests->count() }}</span>
                <span class="bg-yellow-100 dark:bg-yellow-500/20 text-yellow-700 dark:text-yellow-400 text-xs px-1.5 py-0.5 rounded-full" x-show="activeTab !== 'pending'">{{ $pendingRequests->count() }}</span>
                @endif
            </button>
            <button @click="activeTab = 'completed'" :class="activeTab === 'completed' ? 'bg-green-500 text-white shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'" class="flex-1 py-2 px-3 rounded-md text-sm font-medium transition-all flex items-center justify-center gap-2">
                <i class="bi bi-check-circle"></i> Concluídos
                @if($completedRequests->count() > 0)
                <span class="bg-white/20 text-xs px-1.5 py-0.5 rounded-full" x-show="activeTab === 'completed'">{{ $completedRequests->count() }}</span>
                <span class="bg-green-100 dark:bg-green-500/20 text-green-700 dark:text-green-400 text-xs px-1.5 py-0.5 rounded-full" x-show="activeTab !== 'completed'">{{ $completedRequests->count() }}</span>
                @endif
            </button>
            <button @click="activeTab = 'rejected'" :class="activeTab === 'rejected' ? 'bg-red-500 text-white shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'" class="flex-1 py-2 px-3 rounded-md text-sm font-medium transition-all flex items-center justify-center gap-2">
                <i class="bi bi-x-circle"></i> Recusados
                @if($rejectedRequests->count() > 0)
                <span class="bg-white/20 text-xs px-1.5 py-0.5 rounded-full" x-show="activeTab === 'rejected'">{{ $rejectedRequests->count() }}</span>
                <span class="bg-red-100 dark:bg-red-500/20 text-red-700 dark:text-red-400 text-xs px-1.5 py-0.5 rounded-full" x-show="activeTab !== 'rejected'">{{ $rejectedRequests->count() }}</span>
                @endif
            </button>
        </div>
    </div>

    <!-- Tab: Pendentes -->
    <div x-show="activeTab === 'pending'">
        @if($pendingRequests->isEmpty())
        <div class="text-center py-12 text-gray-500 dark:text-gray-400">
            <i class="bi bi-clock text-4xl mb-3 block text-yellow-400"></i>
            <p>Nenhum pedido pendente.</p>
        </div>
        @else
        <div class="divide-y divide-gray-200 dark:divide-dark-200">
            @foreach($pendingRequests as $req)
            @include('vod-requests._request-card', ['req' => $req, 'showAdminResponse' => false])
            @endforeach
        </div>
        @endif
    </div>

    <!-- Tab: Concluídos -->
    <div x-show="activeTab === 'completed'" x-cloak>
        @if($completedRequests->isEmpty())
        <div class="text-center py-12 text-gray-500 dark:text-gray-400">
            <i class="bi bi-check-circle text-4xl mb-3 block text-green-400"></i>
            <p>Nenhum pedido concluído ainda.</p>
        </div>
        @else
        <div class="divide-y divide-gray-200 dark:divide-dark-200">
            @foreach($completedRequests as $req)
            @include('vod-requests._request-card', ['req' => $req, 'showAdminResponse' => true])
            @endforeach
        </div>
        @endif
    </div>

    <!-- Tab: Recusados -->
    <div x-show="activeTab === 'rejected'" x-cloak>
        @if($rejectedRequests->isEmpty())
        <div class="text-center py-12 text-gray-500 dark:text-gray-400">
            <i class="bi bi-x-circle text-4xl mb-3 block text-red-400"></i>
            <p>Nenhum pedido recusado.</p>
        </div>
        @else
        <div class="divide-y divide-gray-200 dark:divide-dark-200">
            @foreach($rejectedRequests as $req)
            @include('vod-requests._request-card', ['req' => $req, 'showAdminResponse' => true])
            @endforeach
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function vodSearch() {
    return {
        type: 'movie',
        query: '',
        loading: false,
        error: null,
        results: [],
        selectedItem: null,
        checking: false,
        checkResult: null,
        checkError: null,
        submitting: false,
        submitSuccess: false,
        // Séries - temporadas
        seasonsData: null,
        loadingSeasons: false,
        selectedSeason: null,

        async search() {
            if (this.query.length < 2) return;
            this.loading = true;
            this.error = null;
            this.results = [];

            try {
                const resp = await fetch(`{{ route('vod-requests.search') }}?type=${this.type}&query=${encodeURIComponent(this.query)}`);
                const data = await resp.json();

                if (!resp.ok) {
                    this.error = data.error || 'Erro na busca.';
                    return;
                }

                this.results = data.results || [];
                if (this.results.length === 0) {
                    this.error = 'Nenhum resultado encontrado.';
                }
            } catch (e) {
                this.error = 'Erro de conexão.';
            } finally {
                this.loading = false;
            }
        },

        async selectItem(item) {
            this.selectedItem = item;
            this.checking = true;
            this.checkResult = null;
            this.checkError = null;
            this.submitSuccess = false;
            this.seasonsData = null;
            this.selectedSeason = null;

            try {
                if (this.type === 'series') {
                    // Busca profunda: verificar existência + temporadas
                    const [checkResp, seasonsResp] = await Promise.all([
                        fetch(`{{ route('vod-requests.check') }}?tmdb_id=${item.tmdb_id}&type=${this.type}&title=${encodeURIComponent(item.title)}`),
                        fetch(`{{ route('vod-requests.check-seasons') }}?tmdb_id=${item.tmdb_id}`)
                    ]);

                    if (!checkResp.ok) {
                        this.checkError = 'Erro ao verificar no servidor (HTTP ' + checkResp.status + ')';
                        return;
                    }
                    this.checkResult = await checkResp.json();

                    if (seasonsResp.ok) {
                        this.seasonsData = await seasonsResp.json();
                    }
                } else {
                    const resp = await fetch(`{{ route('vod-requests.check') }}?tmdb_id=${item.tmdb_id}&type=${this.type}&title=${encodeURIComponent(item.title)}`);
                    if (!resp.ok) {
                        this.checkError = 'Erro ao verificar no servidor (HTTP ' + resp.status + ')';
                        return;
                    }
                    this.checkResult = await resp.json();
                }
            } catch (e) {
                this.checkError = 'Erro de conexão ao verificar no servidor.';
            } finally {
                this.checking = false;
            }
        },

        async submitRequest(seasonNumber = null) {
            if (!this.selectedItem) return;
            this.submitting = true;

            try {
                const body = {
                    type: this.type,
                    tmdb_id: this.selectedItem.tmdb_id,
                    title: this.selectedItem.title,
                    original_title: this.selectedItem.original_title,
                    poster_path: this.selectedItem.poster_path,
                    backdrop_path: this.selectedItem.backdrop_path,
                    overview: this.selectedItem.overview,
                    release_date: this.selectedItem.release_date,
                    vote_average: this.selectedItem.vote_average,
                };

                if (seasonNumber) {
                    body.season_number = seasonNumber;
                }

                const resp = await fetch(`{{ route('vod-requests.store') }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify(body),
                });

                const data = await resp.json();

                if (!resp.ok) {
                    this.error = data.error || 'Erro ao enviar pedido.';
                    return;
                }

                this.submitSuccess = true;
                this.checkResult = null;
                this.seasonsData = null;
            } catch (e) {
                this.error = 'Erro de conexão ao enviar pedido.';
            } finally {
                this.submitting = false;
            }
        }
    };
}
</script>
@endpush
@endsection
