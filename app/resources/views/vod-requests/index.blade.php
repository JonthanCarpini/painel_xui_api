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
    <div x-show="selectedItem" x-cloak class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4" @click.self="selectedItem = null" @keydown.escape.window="selectedItem = null">
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

                <!-- Resultado: já existe no servidor -->
                <div x-show="!checking && checkResult?.exists" x-cloak>
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
                            <div class="flex justify-between" x-show="checkResult?.data?.year">
                                <span class="text-gray-500 dark:text-gray-400">Ano:</span>
                                <span class="text-gray-900 dark:text-white font-medium" x-text="checkResult?.data?.year"></span>
                            </div>
                            <div class="flex justify-between" x-show="checkResult?.data?.rating > 0">
                                <span class="text-gray-500 dark:text-gray-400">Nota:</span>
                                <span class="text-yellow-500 font-medium flex items-center gap-1">
                                    <i class="bi bi-star-fill"></i> <span x-text="checkResult?.data?.rating"></span>
                                </span>
                            </div>
                        </div>
                    </div>
                    <button @click="selectedItem = null; checkResult = null" class="w-full py-3 bg-gray-200 dark:bg-dark-200 hover:bg-gray-300 dark:hover:bg-dark-100 text-gray-700 dark:text-gray-300 rounded-xl font-medium transition-colors">
                        Fechar
                    </button>
                </div>

                <!-- Resultado: não existe, pode solicitar -->
                <div x-show="!checking && checkResult && !checkResult.exists" x-cloak>
                    <!-- Info do filme -->
                    <p class="text-gray-600 dark:text-gray-400 text-sm mb-4 line-clamp-4" x-text="selectedItem?.overview"></p>

                    <div class="flex items-center gap-4 text-sm text-gray-500 dark:text-gray-400 mb-4">
                        <span x-show="selectedItem?.release_date" class="flex items-center gap-1">
                            <i class="bi bi-calendar"></i> <span x-text="selectedItem?.release_date"></span>
                        </span>
                        <span x-show="selectedItem?.vote_average > 0" class="flex items-center gap-1 text-yellow-500">
                            <i class="bi bi-star-fill"></i> <span x-text="selectedItem?.vote_average.toFixed(1)"></span>
                        </span>
                    </div>

                    <!-- Já solicitado por alguém -->
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
<div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 shadow-sm dark:shadow-none overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-dark-200">
        <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
            <i class="bi bi-list-check"></i> Meus Pedidos
        </h2>
    </div>

    @if($myRequests->isEmpty())
    <div class="text-center py-12 text-gray-500 dark:text-gray-400">
        <i class="bi bi-inbox text-4xl mb-3 block"></i>
        <p>Você ainda não fez nenhum pedido.</p>
    </div>
    @else
    <div class="divide-y divide-gray-200 dark:divide-dark-200">
        @foreach($myRequests as $req)
        <div class="flex items-center gap-4 px-6 py-4 hover:bg-gray-50 dark:hover:bg-dark-200 transition-colors">
            <!-- Poster -->
            <div class="w-12 h-16 rounded-lg overflow-hidden bg-gray-200 dark:bg-dark-100 shrink-0">
                <img src="{{ $req->poster_url }}" alt="{{ $req->title }}" class="w-full h-full object-cover" loading="lazy">
            </div>

            <!-- Info -->
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $req->type === 'movie' ? 'bg-purple-100 dark:bg-purple-500/20 text-purple-700 dark:text-purple-400' : 'bg-blue-100 dark:bg-blue-500/20 text-blue-700 dark:text-blue-400' }}">
                        {{ $req->type === 'movie' ? 'Filme' : 'Série' }}
                    </span>
                    @if($req->vote_average > 0)
                    <span class="text-xs text-yellow-500 flex items-center gap-1">
                        <i class="bi bi-star-fill"></i> {{ number_format($req->vote_average, 1) }}
                    </span>
                    @endif
                </div>
                <h4 class="text-sm font-bold text-gray-900 dark:text-white truncate">{{ $req->title }}</h4>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $req->release_date ? substr($req->release_date, 0, 4) : '' }} &middot; Pedido em {{ $req->created_at->format('d/m/Y H:i') }}</p>
            </div>

            <!-- Status -->
            <div class="shrink-0">
                @if($req->status === 'pending')
                    <span class="inline-flex items-center gap-1 text-xs font-medium px-3 py-1.5 rounded-full bg-yellow-100 dark:bg-yellow-500/20 text-yellow-700 dark:text-yellow-400">
                        <i class="bi bi-clock"></i> Pendente
                    </span>
                @elseif($req->status === 'completed')
                    <span class="inline-flex items-center gap-1 text-xs font-medium px-3 py-1.5 rounded-full bg-green-100 dark:bg-green-500/20 text-green-700 dark:text-green-400">
                        <i class="bi bi-check-circle"></i> Concluído
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 text-xs font-medium px-3 py-1.5 rounded-full bg-red-100 dark:bg-red-500/20 text-red-700 dark:text-red-400">
                        <i class="bi bi-x-circle"></i> Recusado
                    </span>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    @if($myRequests->hasPages())
    <div class="px-6 py-4 border-t border-gray-200 dark:border-dark-200">
        {{ $myRequests->links() }}
    </div>
    @endif
    @endif
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
        submitting: false,
        submitSuccess: false,

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
            this.submitSuccess = false;

            try {
                const resp = await fetch(`{{ route('vod-requests.check') }}?tmdb_id=${item.tmdb_id}&type=${this.type}`);
                this.checkResult = await resp.json();
            } catch (e) {
                this.checkResult = { exists: false, already_requested: false };
            } finally {
                this.checking = false;
            }
        },

        async submitRequest() {
            if (!this.selectedItem) return;
            this.submitting = true;

            try {
                const resp = await fetch(`{{ route('vod-requests.store') }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        type: this.type,
                        tmdb_id: this.selectedItem.tmdb_id,
                        title: this.selectedItem.title,
                        original_title: this.selectedItem.original_title,
                        poster_path: this.selectedItem.poster_path,
                        backdrop_path: this.selectedItem.backdrop_path,
                        overview: this.selectedItem.overview,
                        release_date: this.selectedItem.release_date,
                        vote_average: this.selectedItem.vote_average,
                    }),
                });

                const data = await resp.json();

                if (!resp.ok) {
                    this.error = data.error || 'Erro ao enviar pedido.';
                    return;
                }

                this.submitSuccess = true;
                this.checkResult = null;
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
