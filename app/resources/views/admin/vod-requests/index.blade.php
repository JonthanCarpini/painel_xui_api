@extends('layouts.app')

@section('title', 'Gestão de Pedidos VOD')

@section('content')
<div x-data="vodAdmin()">
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-6 md:mb-8 gap-4">
        <div>
            <h1 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <i class="bi bi-film text-orange-500"></i>
                Gestão de Pedidos VOD
            </h1>
            <p class="text-sm md:text-base text-gray-500 dark:text-gray-400 mt-1">Gerencie solicitações de filmes e séries dos revendedores.</p>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <a href="{{ route('settings.admin.vod-requests.index', ['status' => 'pending']) }}" class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 p-4 hover:border-yellow-300 dark:hover:border-yellow-500/50 transition-colors {{ $status === 'pending' ? 'ring-2 ring-yellow-500' : '' }}">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-yellow-100 dark:bg-yellow-500/20 flex items-center justify-center">
                    <i class="bi bi-clock text-yellow-600 dark:text-yellow-400"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $groupStats['pending'] }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Pendentes</p>
                </div>
            </div>
        </a>
        <a href="{{ route('settings.admin.vod-requests.index', ['status' => 'completed']) }}" class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 p-4 hover:border-green-300 dark:hover:border-green-500/50 transition-colors {{ $status === 'completed' ? 'ring-2 ring-green-500' : '' }}">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-green-100 dark:bg-green-500/20 flex items-center justify-center">
                    <i class="bi bi-check-circle text-green-600 dark:text-green-400"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $groupStats['completed'] }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Concluídos</p>
                </div>
            </div>
        </a>
        <a href="{{ route('settings.admin.vod-requests.index', ['status' => 'rejected']) }}" class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 p-4 hover:border-red-300 dark:hover:border-red-500/50 transition-colors {{ $status === 'rejected' ? 'ring-2 ring-red-500' : '' }}">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-red-100 dark:bg-red-500/20 flex items-center justify-center">
                    <i class="bi bi-x-circle text-red-600 dark:text-red-400"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $groupStats['rejected'] }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Recusados</p>
                </div>
            </div>
        </a>
        <a href="{{ route('settings.admin.vod-requests.index', ['status' => 'all']) }}" class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 p-4 hover:border-orange-300 dark:hover:border-orange-500/50 transition-colors {{ $status === 'all' ? 'ring-2 ring-orange-500' : '' }}">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-orange-100 dark:bg-orange-500/20 flex items-center justify-center">
                    <i class="bi bi-collection text-orange-600 dark:text-orange-400"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $groupStats['total'] }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Total</p>
                </div>
            </div>
        </a>
    </div>

    @if(session('success'))
    <div class="bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/30 rounded-xl p-4 mb-6">
        <p class="text-green-700 dark:text-green-400 text-sm flex items-center gap-2">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
        </p>
    </div>
    @endif

    <!-- Filtros -->
    <div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 p-4 mb-6 shadow-sm dark:shadow-none">
        <form method="GET" action="{{ route('settings.admin.vod-requests.index') }}" class="flex flex-col sm:flex-row gap-3">
            <input type="hidden" name="status" value="{{ $status }}">
            <div class="flex gap-2 shrink-0">
                <select name="type" class="px-3 py-2 bg-gray-100 dark:bg-dark-200 border border-gray-200 dark:border-dark-100 rounded-lg text-sm text-gray-900 dark:text-white">
                    <option value="">Todos os tipos</option>
                    <option value="movie" {{ $type === 'movie' ? 'selected' : '' }}>Filmes</option>
                    <option value="series" {{ $type === 'series' ? 'selected' : '' }}>Séries</option>
                </select>
            </div>
            <input type="text" name="search" value="{{ $search }}" placeholder="Buscar por título..." class="flex-1 px-4 py-2 bg-gray-100 dark:bg-dark-200 border border-gray-200 dark:border-dark-100 rounded-lg text-sm text-gray-900 dark:text-white placeholder-gray-400">
            <button type="submit" class="px-6 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-lg text-sm font-medium transition-colors shrink-0">
                <i class="bi bi-funnel"></i> Filtrar
            </button>
        </form>
    </div>

    <!-- Lista de Pedidos (Agrupados) -->
    <div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 shadow-sm dark:shadow-none overflow-hidden">
        @if($requests->isEmpty())
        <div class="text-center py-12 text-gray-500 dark:text-gray-400">
            <i class="bi bi-inbox text-4xl mb-3 block"></i>
            <p>Nenhum pedido encontrado.</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-dark-200">
                    <tr>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Título</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tipo</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Solicitantes</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pedidos</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-dark-200">
                    @foreach($requests as $req)
                    <tr class="hover:bg-gray-50 dark:hover:bg-dark-200 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-14 rounded-lg overflow-hidden bg-gray-200 dark:bg-dark-100 shrink-0">
                                    @if($req->poster_path)
                                    <img src="https://image.tmdb.org/t/p/w92{{ $req->poster_path }}" alt="{{ $req->title }}" class="w-full h-full object-cover" loading="lazy">
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <h4 class="font-bold text-gray-900 dark:text-white truncate max-w-[200px]">{{ $req->title }}</h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $req->release_date ? substr($req->release_date, 0, 4) : '' }}
                                        @if($req->vote_average > 0)
                                            &middot; <span class="text-yellow-500"><i class="bi bi-star-fill"></i> {{ number_format($req->vote_average, 1) }}</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            <span class="text-xs font-medium px-2 py-1 rounded-full {{ $req->type === 'movie' ? 'bg-purple-100 dark:bg-purple-500/20 text-purple-700 dark:text-purple-400' : 'bg-blue-100 dark:bg-blue-500/20 text-blue-700 dark:text-blue-400' }}">
                                {{ $req->type === 'movie' ? 'Filme' : 'Série' }}
                            </span>
                        </td>
                        <td class="px-4 py-4">
                            <div class="flex flex-wrap gap-1">
                                @foreach($req->requesters->take(3) as $r)
                                <span class="text-xs px-2 py-0.5 bg-gray-100 dark:bg-dark-100 text-gray-700 dark:text-gray-300 rounded-full">{{ $r->user->username ?? 'N/A' }}</span>
                                @endforeach
                                @if($req->requesters->count() > 3)
                                <span class="text-xs px-2 py-0.5 bg-orange-100 dark:bg-orange-500/20 text-orange-700 dark:text-orange-400 rounded-full">+{{ $req->requesters->count() - 3 }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-4 text-center">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full text-xs font-bold {{ $req->request_count > 1 ? 'bg-orange-100 dark:bg-orange-500/20 text-orange-700 dark:text-orange-400' : 'bg-gray-100 dark:bg-dark-100 text-gray-600 dark:text-gray-400' }}">
                                {{ $req->request_count }}
                            </span>
                        </td>
                        <td class="px-4 py-4 text-center">
                            @if($req->group_status === 'pending')
                                <span class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1 rounded-full bg-yellow-100 dark:bg-yellow-500/20 text-yellow-700 dark:text-yellow-400">
                                    <i class="bi bi-clock"></i> Pendente
                                </span>
                            @elseif($req->group_status === 'completed')
                                <span class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1 rounded-full bg-green-100 dark:bg-green-500/20 text-green-700 dark:text-green-400">
                                    <i class="bi bi-check-circle"></i> Concluído
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1 rounded-full bg-red-100 dark:bg-red-500/20 text-red-700 dark:text-red-400">
                                    <i class="bi bi-x-circle"></i> Recusado
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <button @click="checkXui({{ $req->tmdb_id }}, '{{ $req->type }}', '{{ addslashes($req->title) }}', {{ $req->id }})" class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-blue-500 hover:bg-blue-600 text-white rounded-lg text-xs font-medium transition-colors" title="Buscar no servidor">
                                    <i class="bi bi-server"></i>
                                </button>
                                <a href="{{ route('settings.admin.vod-requests.show', $req->id) }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-orange-500 hover:bg-orange-600 text-white rounded-lg text-xs font-medium transition-colors" title="Ver detalhes">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($requests->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 dark:border-dark-200">
            {{ $requests->links() }}
        </div>
        @endif
        @endif
    </div>

    <!-- Modal: Resultado da busca no XUI -->
    <div x-show="showModal" x-cloak class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4" @click.self="showModal = false" @keydown.escape.window="showModal = false">
        <div class="bg-white dark:bg-dark-300 rounded-2xl max-w-lg w-full shadow-2xl" @click.stop>
            <div class="px-6 py-4 border-b border-gray-200 dark:border-dark-200 flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="bi bi-server text-blue-500"></i> Verificação no Servidor
                </h3>
                <button @click="showModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <div class="p-6 max-h-[70vh] overflow-y-auto">
                <!-- Loading -->
                <div x-show="checking" class="flex items-center justify-center py-8">
                    <i class="bi bi-arrow-repeat animate-spin text-blue-500 text-2xl mr-3"></i>
                    <span class="text-gray-500 dark:text-gray-400">Buscando no servidor...</span>
                </div>

                <!-- FILME: Encontrado -->
                <div x-show="!checking && xuiResult && xuiResult.exists && modalType === 'movie'" x-cloak>
                    <div class="bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/30 rounded-xl p-4 mb-4">
                        <div class="flex items-center gap-2 mb-3">
                            <i class="bi bi-check-circle-fill text-green-500 text-lg"></i>
                            <span class="text-green-700 dark:text-green-400 font-semibold">Encontrado no servidor!</span>
                        </div>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Nome:</span>
                                <span class="text-gray-900 dark:text-white font-medium" x-text="xuiResult?.data?.name"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Categoria:</span>
                                <span class="text-gray-900 dark:text-white font-medium" x-text="xuiResult?.data?.category"></span>
                            </div>
                            <div class="flex justify-between" x-show="xuiResult?.data?.added_date">
                                <span class="text-gray-500 dark:text-gray-400">Adicionado em:</span>
                                <span class="text-gray-900 dark:text-white font-medium" x-text="xuiResult?.data?.added_date"></span>
                            </div>
                        </div>
                    </div>

                    <form :action="resolveUrl" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="completed">
                        <textarea name="admin_note" x-model="autoNote" rows="3" class="w-full px-3 py-2 bg-gray-100 dark:bg-dark-200 border border-gray-200 dark:border-dark-100 rounded-lg text-sm text-gray-900 dark:text-white mb-3 resize-none"></textarea>
                        <button type="submit" class="w-full py-3 bg-green-500 hover:bg-green-600 text-white rounded-xl font-medium text-sm transition-colors flex items-center justify-center gap-2">
                            <i class="bi bi-check-circle"></i> Marcar como Concluído
                        </button>
                    </form>
                </div>

                <!-- FILME: Não encontrado -->
                <div x-show="!checking && xuiResult && !xuiResult.exists && modalType === 'movie'" x-cloak>
                    <div class="bg-yellow-50 dark:bg-yellow-500/10 border border-yellow-200 dark:border-yellow-500/30 rounded-xl p-4 mb-4">
                        <div class="flex items-center gap-2">
                            <i class="bi bi-exclamation-triangle text-yellow-500 text-lg"></i>
                            <span class="text-yellow-700 dark:text-yellow-400 font-semibold">Não encontrado no servidor</span>
                        </div>
                        <p class="text-yellow-600 dark:text-yellow-400/80 text-sm mt-1" x-text="'O filme \"' + modalTitle + '\" ainda não foi adicionado ao XUI.'"></p>
                    </div>
                    <button @click="showModal = false" class="w-full py-3 bg-gray-200 dark:bg-dark-200 hover:bg-gray-300 dark:hover:bg-dark-100 text-gray-700 dark:text-gray-300 rounded-xl font-medium text-sm transition-colors">
                        Fechar
                    </button>
                </div>

                <!-- SÉRIE: Resultado com temporadas -->
                <div x-show="!checking && xuiResult && modalType === 'series'" x-cloak>
                    <!-- Status da série no XUI -->
                    <template x-if="xuiResult?.exists">
                        <div class="bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/30 rounded-xl p-3 mb-4">
                            <div class="flex items-center gap-2 mb-1">
                                <i class="bi bi-check-circle-fill text-green-500"></i>
                                <span class="text-green-700 dark:text-green-400 font-semibold text-sm">Série encontrada no servidor</span>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400" x-text="xuiResult?.data?.name + ' — ' + xuiResult?.data?.category"></p>
                        </div>
                    </template>
                    <template x-if="!xuiResult?.exists">
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
                            <div class="space-y-2 max-h-64 overflow-y-auto pr-1">
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
                                                <span class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1 rounded-full bg-red-100 dark:bg-red-500/20 text-red-600 dark:text-red-400">
                                                    <i class="bi bi-x-circle"></i> Ausente
                                                </span>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    <!-- Resumo + ação de resolver -->
                    <div class="bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-500/30 rounded-lg p-3 mb-4" x-show="seasonsData">
                        <p class="text-blue-700 dark:text-blue-400 text-xs">
                            <template x-if="seasonsData && seasonsData.xui_seasons">
                                <span x-text="seasonsData.xui_seasons.length + '/' + seasonsData.seasons.length + ' temporadas no servidor. ' + (seasonsData.seasons.length - seasonsData.xui_seasons.length > 0 ? (seasonsData.seasons.length - seasonsData.xui_seasons.length) + ' ausente(s).' : 'Todas presentes!')"></span>
                            </template>
                        </p>
                    </div>

                    <form :action="resolveUrl" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="completed">
                        <textarea name="admin_note" x-model="autoNote" rows="3" class="w-full px-3 py-2 bg-gray-100 dark:bg-dark-200 border border-gray-200 dark:border-dark-100 rounded-lg text-sm text-gray-900 dark:text-white mb-3 resize-none"></textarea>
                        <div class="flex gap-2">
                            <button type="submit" class="flex-1 py-3 bg-green-500 hover:bg-green-600 text-white rounded-xl font-medium text-sm transition-colors flex items-center justify-center gap-2">
                                <i class="bi bi-check-circle"></i> Concluir
                            </button>
                            <button type="submit" name="status" value="rejected" class="flex-1 py-3 bg-red-500 hover:bg-red-600 text-white rounded-xl font-medium text-sm transition-colors flex items-center justify-center gap-2">
                                <i class="bi bi-x-circle"></i> Recusar
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Erro -->
                <div x-show="!checking && xuiError" x-cloak>
                    <div class="bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 rounded-xl p-4 mb-4">
                        <p class="text-red-600 dark:text-red-400 text-sm" x-text="xuiError"></p>
                    </div>
                    <button @click="showModal = false" class="w-full py-3 bg-gray-200 dark:bg-dark-200 hover:bg-gray-300 dark:hover:bg-dark-100 text-gray-700 dark:text-gray-300 rounded-xl font-medium text-sm transition-colors">
                        Fechar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function vodAdmin() {
    return {
        showModal: false,
        checking: false,
        xuiResult: null,
        xuiError: null,
        modalTitle: '',
        modalType: '',
        autoNote: '',
        resolveUrl: '',
        seasonsData: null,

        async checkXui(tmdbId, type, title, requestId) {
            this.showModal = true;
            this.checking = true;
            this.xuiResult = null;
            this.xuiError = null;
            this.seasonsData = null;
            this.modalTitle = title;
            this.modalType = type;
            this.autoNote = '';
            this.resolveUrl = `{{ url('settings/admin/vod-requests') }}/${requestId}/resolve`;

            try {
                if (type === 'series') {
                    const [checkResp, seasonsResp] = await Promise.all([
                        fetch(`{{ route('settings.admin.vod-requests.check-xui') }}?tmdb_id=${tmdbId}&type=${type}`),
                        fetch(`{{ route('settings.admin.vod-requests.check-seasons') }}?tmdb_id=${tmdbId}`)
                    ]);

                    if (!checkResp.ok) {
                        this.xuiError = 'Erro HTTP ' + checkResp.status;
                        return;
                    }
                    this.xuiResult = await checkResp.json();

                    if (seasonsResp.ok) {
                        this.seasonsData = await seasonsResp.json();
                    }

                    // Gerar nota automática com info de temporadas
                    this.buildSeriesNote();
                } else {
                    const resp = await fetch(`{{ route('settings.admin.vod-requests.check-xui') }}?tmdb_id=${tmdbId}&type=${type}`);
                    if (!resp.ok) {
                        this.xuiError = 'Erro HTTP ' + resp.status;
                        return;
                    }
                    this.xuiResult = await resp.json();

                    if (this.xuiResult.exists) {
                        const d = this.xuiResult.data;
                        let note = `Pedido Adicionado\ud83d\udc4f\ud83c\udffb\n`;
                        note += `Nome: ${d.name}\n`;
                        if (d.category) note += `Categoria: ${d.category}\n`;
                        if (d.added_date) note += `Adicionado em: ${d.added_date}`;
                        this.autoNote = note;
                    }
                }
            } catch (e) {
                this.xuiError = 'Erro de conexão ao verificar servidor.';
            } finally {
                this.checking = false;
            }
        },

        buildSeriesNote() {
            let note = '';
            if (this.xuiResult?.exists) {
                const d = this.xuiResult.data;
                note += `Série encontrada no servidor\n`;
                note += `Nome: ${d.name}\n`;
                if (d.category) note += `Categoria: ${d.category}\n`;
            } else {
                note += `Série não encontrada no servidor\n`;
            }

            if (this.seasonsData?.seasons) {
                const total = this.seasonsData.seasons.length;
                const inXui = this.seasonsData.xui_seasons?.length || 0;
                const missing = total - inXui;
                note += `\nTemporadas: ${inXui}/${total} no servidor`;
                if (missing > 0) {
                    const missingNums = this.seasonsData.seasons
                        .filter(s => !s.exists_in_xui)
                        .map(s => s.season_number);
                    note += `\nAusentes: ${missingNums.join(', ')}`;
                } else {
                    note += ` (todas presentes)`;
                }
            }
            this.autoNote = note;
        }
    };
}
</script>
@endpush
@endsection
