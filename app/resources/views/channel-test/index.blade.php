@extends('layouts.app')

@section('title', 'Teste de Canais')

@section('content')
<style>
    /* Custom Scrollbar */
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: rgba(0,0,0,0.05);
        border-radius: 4px;
    }
    .dark .custom-scrollbar::-webkit-scrollbar-track {
        background: rgba(255,255,255,0.05);
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #f97316; /* Orange-500 */
        border-radius: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #ea580c; /* Orange-600 */
    }
</style>

<div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-6 gap-4">
    <div>
        <h1 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
            <i class="bi bi-play-btn text-orange-500"></i>
            Teste de Canais
        </h1>
        <p class="text-sm md:text-base text-gray-500 dark:text-gray-400 mt-1">Gere um teste r&aacute;pido e verifique a qualidade dos canais.</p>
    </div>
</div>


<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Coluna da Esquerda: Seleção -->
    <div class="lg:col-span-1 space-y-6">
        
        <!-- Tabs de Tipo -->
        <div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 p-1 shadow-sm flex">
            <button onclick="switchTab('live')" id="tab-live" class="flex-1 py-2 text-sm font-medium rounded-lg transition-colors bg-orange-500 text-white shadow-sm">
                Canais
            </button>
            <button onclick="switchTab('movie')" id="tab-movie" class="flex-1 py-2 text-sm font-medium rounded-lg transition-colors text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-dark-200">
                Filmes
            </button>
            <button onclick="switchTab('series')" id="tab-series" class="flex-1 py-2 text-sm font-medium rounded-lg transition-colors text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-dark-200">
                Séries
            </button>
        </div>

        <!-- Selecionar Categoria -->
        <div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 p-4 md:p-6 shadow-sm dark:shadow-none">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <i class="bi bi-list-ul text-orange-500"></i>
                1. Selecione a Categoria
            </h2>
            
            <select id="categorySelect" class="w-full px-4 py-3 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white focus:border-orange-500 focus:outline-none transition-colors">
                <option value="">Selecione uma categoria...</option>
                <!-- Populated via JS -->
            </select>
        </div>

        <!-- Lista de Canais -->
        <div id="channelsContainer" class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 p-4 md:p-6 shadow-sm dark:shadow-none hidden">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <i id="listIcon" class="bi bi-tv text-orange-500"></i>
                2. Escolha o Item
            </h2>
            
            <div class="mb-4">
                <input type="text" id="channelSearch" placeholder="Buscar..." class="w-full px-4 py-2 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white focus:border-orange-500 focus:outline-none transition-colors text-sm">
            </div>

            <!-- Lista com altura fixa igualada ao player (aprox) -->
            <div id="channelsList" class="space-y-2 h-[500px] overflow-y-auto custom-scrollbar pr-2">
                <!-- Canais serão carregados aqui via JS -->
                <div class="text-center py-8 text-gray-500">
                    <div class="animate-spin inline-block w-6 h-6 border-2 border-orange-500 border-t-transparent rounded-full mb-2"></div>
                    <p>Carregando...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Coluna do Meio: Player e Status -->
    <div class="lg:col-span-2">
        <div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 overflow-hidden shadow-sm dark:shadow-none sticky top-24">
            <div class="p-4 md:p-6 border-b border-gray-200 dark:border-dark-200 flex justify-between items-center">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="bi bi-display text-orange-500"></i>
                    Player de Teste
                </h2>
                <div class="flex items-center gap-2">
                    <span id="statusBadge" class="hidden px-3 py-1 bg-gray-100 dark:bg-dark-100 text-gray-600 dark:text-gray-400 text-xs font-semibold rounded-full">
                        Aguardando seleção
                    </span>
                </div>
            </div>

            <!-- Área do Player -->
            <div class="relative bg-black aspect-video flex items-center justify-center group">
                <div id="playerPlaceholder" class="text-center p-6">
                    <i class="bi bi-play-circle text-gray-700 dark:text-gray-600 text-6xl mb-4 block group-hover:text-orange-500 transition-colors"></i>
                    <p class="text-gray-500 dark:text-gray-400">Selecione um canal ao lado para iniciar o teste</p>
                </div>
                
                <video id="videoPlayer" class="w-full h-full hidden" controls autoplay></video>
            </div>

            <!-- Informações do Teste, Estatísticas e Ações -->
            <div id="testInfo" class="p-4 md:p-6 bg-gray-50 dark:bg-dark-200 hidden border-t border-gray-200 dark:border-dark-200">
                
                <!-- Cabeçalho do Canal -->
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-bold mb-1">Canal Atual</p>
                        <h3 id="currentChannelName" class="text-gray-900 dark:text-white font-bold text-xl truncate">-</h3>
                        <p id="currentCategory" class="text-sm text-gray-500 dark:text-gray-400">-</p>
                    </div>
                    
                    <div class="flex flex-wrap gap-2">
                        @if(Auth::user()->isAdmin())
                        <button onclick="restartChannel()" id="btnRestart" class="px-4 py-2 bg-blue-100 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 rounded-lg hover:bg-blue-200 dark:hover:bg-blue-500/20 transition-colors flex items-center gap-2 text-sm font-medium">
                            <i class="bi bi-arrow-clockwise"></i>
                            Reiniciar
                        </button>
                        @endif
                        
                        <button onclick="openReportModal()" class="px-4 py-2 bg-red-100 dark:bg-red-500/10 text-red-600 dark:text-red-400 rounded-lg hover:bg-red-200 dark:hover:bg-red-500/20 transition-colors flex items-center gap-2 text-sm font-medium">
                            <i class="bi bi-exclamation-triangle"></i>
                            Reportar
                        </button>
                    </div>
                </div>

                <!-- Estatísticas -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-4 bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-100">
                    <div class="text-center">
                        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-bold mb-1">Status</p>
                        <p id="statStatus" class="font-bold text-gray-700 dark:text-gray-300">
                            <span class="animate-pulse">...</span>
                        </p>
                    </div>
                    <div class="text-center border-l border-gray-100 dark:border-dark-100">
                        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-bold mb-1">Uptime</p>
                        <p id="statUptime" class="font-bold text-gray-700 dark:text-gray-300">-</p>
                    </div>
                    <div class="text-center border-l border-gray-100 dark:border-dark-100">
                        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-bold mb-1">Clientes</p>
                        <p id="statClients" class="font-bold text-gray-700 dark:text-gray-300">-</p>
                    </div>
                    <div class="text-center border-l border-gray-100 dark:border-dark-100">
                        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-bold mb-1">Tipo</p>
                        <p id="statType" class="font-bold text-gray-700 dark:text-gray-300">-</p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Modal de Reportar Problema -->
<div id="reportModal" class="fixed inset-0 z-50 hidden">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-opacity" onclick="closeReportModal()"></div>
    
    <!-- Modal Content -->
    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-white dark:bg-dark-300 rounded-xl shadow-2xl p-6 border border-gray-200 dark:border-dark-200">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <i class="bi bi-exclamation-triangle text-red-500"></i>
            Reportar Problema
        </h3>
        
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
            Descreva o problema encontrado no canal <strong id="reportChannelName" class="text-gray-900 dark:text-gray-300"></strong>. 
            Um ticket ser&aacute; aberto automaticamente com os dados técnicos do canal.
        </p>
        
        <textarea id="reportDescription" rows="4" class="w-full px-4 py-3 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white focus:border-orange-500 focus:outline-none transition-colors mb-4" placeholder="Ex: Canal sem &aacute;udio, travando muito, tela preta..."></textarea>
        
        <div class="flex justify-end gap-3">
            <button onclick="closeReportModal()" class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-dark-200 rounded-lg transition-colors text-sm font-medium">
                Cancelar
            </button>
            <button onclick="submitReport()" id="btnSubmitReport" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors text-sm font-medium flex items-center gap-2">
                <span>Enviar Reporte</span>
                <i class="bi bi-send"></i>
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
<script>
    const categoriesByType = @json($categoriesByType);
    let currentType = 'live';

    const categorySelect = document.getElementById('categorySelect');
    const channelsContainer = document.getElementById('channelsContainer');
    const channelsList = document.getElementById('channelsList');
    const channelSearch = document.getElementById('channelSearch');
    const playerPlaceholder = document.getElementById('playerPlaceholder');
    const videoPlayer = document.getElementById('videoPlayer');
    const testInfo = document.getElementById('testInfo');
    const currentChannelName = document.getElementById('currentChannelName');
    const currentCategory = document.getElementById('currentCategory');
    const statusBadge = document.getElementById('statusBadge');
    const listIcon = document.getElementById('listIcon');
    
    // Stats Elements
    const statStatus = document.getElementById('statStatus');
    const statUptime = document.getElementById('statUptime');
    const statClients = document.getElementById('statClients');
    const statType = document.getElementById('statType');
    const btnRestart = document.getElementById('btnRestart');
    
    // Modal Elements
    const reportModal = document.getElementById('reportModal');
    const reportChannelName = document.getElementById('reportChannelName');
    const reportDescription = document.getElementById('reportDescription');
    const btnSubmitReport = document.getElementById('btnSubmitReport');

    let hls = null;
    let allChannels = [];
    let currentChannelData = null;

    const SERVER_IP = '{{ $xuiIp }}';
    const XUI_PROXY_BASE = '{{ $xuiProxyBase }}';

    function proxyUrl(url) {
        if (!url) return url;

        // Converter http://IP(:porta)/path → https://xui.domain/path
        const ipRegex = new RegExp('https?://' + SERVER_IP.replace(/\./g, '\\.') + '(:\\d+)?/');
        if (ipRegex.test(url)) {
            return url.replace(ipRegex, XUI_PROXY_BASE + '/');
        }

        return url;
    }

    // Inicializar Tabs
    function switchTab(type) {
        currentType = type;
        
        // Atualizar botões
        ['live', 'movie', 'series'].forEach(t => {
            const btn = document.getElementById(`tab-${t}`);
            if (t === type) {
                btn.className = 'flex-1 py-2 text-sm font-medium rounded-lg transition-colors bg-orange-500 text-white shadow-sm';
            } else {
                btn.className = 'flex-1 py-2 text-sm font-medium rounded-lg transition-colors text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-dark-200';
            }
        });

        // Atualizar ícone da lista
        if (type === 'live') listIcon.className = 'bi bi-tv text-orange-500';
        else if (type === 'movie') listIcon.className = 'bi bi-film text-orange-500';
        else if (type === 'series') listIcon.className = 'bi bi-collection-play text-orange-500';

        // Atualizar Select de Categorias
        categorySelect.innerHTML = '<option value="">Selecione uma categoria...</option>';
        
        const categories = categoriesByType[type] || [];
        categories.forEach(cat => {
            const option = document.createElement('option');
            option.value = cat.group_title;
            option.textContent = cat.group_title;
            categorySelect.appendChild(option);
        });

        // Resetar container de canais
        channelsContainer.classList.add('hidden');
        allChannels = [];
    }

    // Iniciar na tab padrão
    switchTab('live');

    // Carregar canais ao selecionar categoria
    categorySelect.addEventListener('change', function() {
        const category = this.value;
        if (!category) {
            channelsContainer.classList.add('hidden');
            return;
        }

        channelsContainer.classList.remove('hidden');
        channelsList.innerHTML = `
            <div class="text-center py-8 text-gray-500">
                <div class="animate-spin inline-block w-6 h-6 border-2 border-orange-500 border-t-transparent rounded-full mb-2"></div>
                <p>Carregando...</p>
            </div>
        `;

        fetch(`{{ route('channel-test.get-streams') }}?category=${encodeURIComponent(category)}&type=${currentType}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) throw new Error(data.error);
                allChannels = data; // Guardar para busca local
                renderChannels(data);
            })
            .catch(error => {
                channelsList.innerHTML = `
                    <div class="text-center py-8 text-red-500">
                        <i class="bi bi-exclamation-circle text-2xl mb-2 block"></i>
                        <p>Erro ao carregar: ${error.message}</p>
                    </div>
                `;
            });
    });

    // Filtro de busca de canais
    channelSearch.addEventListener('input', function() {
        const term = this.value.toLowerCase();
        const filtered = allChannels.filter(c => c.name.toLowerCase().includes(term));
        renderChannels(filtered);
    });

    function renderChannels(channels) {
        if (channels.length === 0) {
            channelsList.innerHTML = `
                <div class="text-center py-8 text-gray-500">
                    <p>Nenhum item encontrado nesta categoria.</p>
                </div>
            `;
            return;
        }

        channelsList.innerHTML = channels.map(channel => `
            <div onclick="playChannel(${JSON.stringify(channel).replace(/"/g, '&quot;')})" 
                 class="flex items-center gap-3 p-3 rounded-lg hover:bg-orange-50 dark:hover:bg-orange-500/10 cursor-pointer transition-colors group border border-transparent hover:border-orange-200 dark:hover:border-orange-500/30">
                
                <!-- Status Dot (Placeholder inicial) -->
                <div class="w-2 h-2 rounded-full bg-gray-300 dark:bg-gray-600 channel-status-dot" data-id="${channel.id}"></div>

                <div class="w-10 h-10 bg-gray-100 dark:bg-dark-100 rounded flex items-center justify-center shrink-0">
                    ${channel.icon ? `<img src="${channel.icon}" class="w-8 h-8 object-contain" onerror="this.src=''">` : 
                      (currentType === 'movie' ? '<i class="bi bi-film text-gray-400"></i>' : 
                      (currentType === 'series' ? '<i class="bi bi-collection-play text-gray-400"></i>' : 
                      '<i class="bi bi-tv text-gray-400"></i>'))}
                </div>
                <span class="font-medium text-gray-700 dark:text-gray-300 group-hover:text-orange-600 dark:group-hover:text-orange-400 text-sm truncate flex-1">
                    ${channel.name}
                </span>
                <i class="bi bi-play-fill text-gray-300 group-hover:text-orange-500 text-xl"></i>
            </div>
        `).join('');
    }

    // Iniciar teste e reproduzir canal
    window.playChannel = function(channel) {
        currentChannelData = channel; // Armazena objeto completo
        
        currentChannelName.textContent = channel.name;
        currentCategory.textContent = categorySelect.value;
        
        playerPlaceholder.classList.add('hidden');
        videoPlayer.classList.remove('hidden');
        testInfo.classList.remove('hidden');
        
        // Status badge UI
        statusBadge.classList.remove('hidden', 'bg-gray-100', 'text-gray-600', 'bg-red-100', 'text-red-600');
        statusBadge.classList.add('bg-green-100', 'text-green-600', 'dark:bg-green-500/10', 'dark:text-green-400');
        statusBadge.textContent = "Reproduzindo";

        // Resetar estatísticas visualmente
        resetStats();
        
        // Buscar detalhes técnicos e estatísticas
        fetchChannelDetails(channel.id);

        // Player Logic (HLS.js)
        if (hls) {
            hls.destroy();
            hls = null;
        }
        videoPlayer.pause();
        videoPlayer.src = "";

        const streamUrl = proxyUrl(channel.stream_url);
        const isDirectVideo = /\.(mp4|mkv|avi|mov|wmv|flv|webm|ts)(\?|$)/i.test(streamUrl);

        if (isDirectVideo) {
            videoPlayer.src = streamUrl;
            videoPlayer.play().catch(e => console.log("Autoplay blocked", e));
        } else if (Hls.isSupported()) {
            // Resolver URL via backend (evita redirect 302 no browser que causa CORS)
            startHlsPlayer(channel, streamUrl);
        } else if (videoPlayer.canPlayType('application/vnd.apple.mpegurl')) {
            videoPlayer.src = streamUrl;
            videoPlayer.addEventListener('loadedmetadata', function() {
                videoPlayer.play();
            });
        }
    }

    function startHlsPlayer(channel, fallbackUrl) {
        const resolveUrl = `{{ route('channel-test.resolve-stream') }}?channel_id=${channel.id}&type=${currentType}`;

        fetch(resolveUrl)
            .then(res => res.json())
            .then(data => {
                const streamSrc = data.resolved_url || fallbackUrl;
                initHls(streamSrc);
            })
            .catch(err => {
                console.warn('resolve-stream failed, using fallback', err);
                initHls(fallbackUrl);
            });
    }

    function initHls(streamSrc) {
        if (hls) {
            hls.destroy();
            hls = null;
        }

        hls = new Hls({
            xhrSetup: function(xhr, url) {
                const proxied = proxyUrl(url);
                if (proxied !== url) {
                    xhr.open('GET', proxied, true);
                }
            }
        });
        hls.loadSource(streamSrc);
        hls.attachMedia(videoPlayer);
        hls.on(Hls.Events.MANIFEST_PARSED, function() {
            videoPlayer.play().catch(e => console.log("Autoplay blocked", e));
        });
        hls.on(Hls.Events.ERROR, function(event, data) {
            if (data.fatal) {
                switch (data.type) {
                    case Hls.ErrorTypes.NETWORK_ERROR:
                        console.log("fatal network error encountered, try to recover");
                        hls.startLoad();
                        break;
                    case Hls.ErrorTypes.MEDIA_ERROR:
                        console.log("fatal media error encountered, try to recover");
                        hls.recoverMediaError();
                        break;
                    default:
                        hls.destroy();
                        break;
                }
            }
        });
    }

    function resetStats() {
        statStatus.innerHTML = '<span class="animate-pulse">...</span>';
        statUptime.textContent = '-';
        statClients.textContent = '-';
        statType.textContent = '-';
    }

    function fetchChannelDetails(id) {
        fetch(`{{ url('channel-test/details') }}/${id}`)
            .then(res => res.json())
            .then(data => {
                if (data.error) {
                    statStatus.innerHTML = '<span class="text-red-500">Erro</span>';
                    return;
                }

                // Atualizar UI Estatísticas
                const isOnline = data.status === 'Online';
                statStatus.innerHTML = isOnline 
                    ? '<span class="text-green-500"><i class="bi bi-circle-fill text-[8px] mr-1"></i>Online</span>' 
                    : '<span class="text-red-500"><i class="bi bi-circle-fill text-[8px] mr-1"></i>Offline</span>';
                
                statUptime.textContent = data.uptime || '-';
                statClients.textContent = data.clients || '0';
                statType.textContent = data.on_demand ? 'VOD' : 'Live';

                // Atualizar bolinha na lista (opcional, apenas visual para o canal atual)
                const dot = document.querySelector(`.channel-status-dot[data-id="${id}"]`);
                if (dot) {
                    dot.classList.remove('bg-gray-300', 'dark:bg-gray-600', 'bg-green-500', 'bg-red-500');
                    dot.classList.add(isOnline ? 'bg-green-500' : 'bg-red-500');
                }
            })
            .catch(err => {
                console.error(err);
                statStatus.textContent = 'Erro';
            });
    }

    window.restartChannel = function() {
        if (!currentChannelData || !currentChannelData.id) return;
        
        if (!confirm('Tem certeza que deseja reiniciar este canal? Isso desconectará todos os clientes atuais.')) return;

        const btn = document.getElementById('btnRestart');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="animate-spin inline-block w-4 h-4 border-2 border-current border-t-transparent rounded-full"></span>';

        fetch(`{{ url('channel-test/restart') }}/${currentChannelData.id}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.result) {
                alert('Comando de reinício enviado com sucesso!');
                // Recarregar stats após alguns segundos
                setTimeout(() => fetchChannelDetails(currentChannelData.id), 3000);
            } else {
                alert('Erro ao reiniciar: ' + (data.message || 'Falha desconhecida'));
            }
        })
        .catch(err => {
            alert('Erro de conexão ao reiniciar canal');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
    }

    // Funções do Modal de Reporte
    window.openReportModal = function() {
        if (!currentChannelData) return;
        reportChannelName.textContent = currentChannelData.name;
        reportDescription.value = '';
        reportModal.classList.remove('hidden');
    }

    window.closeReportModal = function() {
        reportModal.classList.add('hidden');
    }

    window.submitReport = function() {
        const description = reportDescription.value.trim();
        if (!description) {
            alert('Por favor, descreva o problema.');
            return;
        }

        const originalBtnText = btnSubmitReport.innerHTML;
        btnSubmitReport.disabled = true;
        btnSubmitReport.innerHTML = '<span class="animate-spin inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full mr-2"></span> Enviando...';

        fetch('{{ route("channel-test.report") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                channel_name: currentChannelData.name,
                stream_url: currentChannelData.stream_url,
                local_id: currentChannelData.id,
                stream_id: currentChannelData.stream_id,
                category_name: categorySelect.value,
                problem_description: description
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Reporte enviado com sucesso! Verifique seus tickets.');
                closeReportModal();
            } else {
                alert('Erro ao enviar reporte: ' + (data.message || 'Erro desconhecido'));
            }
        })
        .catch(error => {
            alert('Erro de conexão ao enviar reporte.');
            console.error(error);
        })
        .finally(() => {
            btnSubmitReport.disabled = false;
            btnSubmitReport.innerHTML = originalBtnText;
        });
    }
</script>
@endpush
@endsection
