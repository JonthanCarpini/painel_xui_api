@extends('layouts.app')

@section('title', 'Links M3U')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="bg-dark-300 rounded-xl p-8 border-2 border-orange-500/50">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-white flex items-center gap-2">
                <i class="bi bi-download text-orange-500"></i>
                Links de Acesso - {{ $client['username'] }}
            </h2>
            <a href="{{ route('clients.index') }}" class="text-gray-400 hover:text-white transition-colors">
                <i class="bi bi-x-lg text-2xl"></i>
            </a>
        </div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <!-- Link M3U -->
        <div class="bg-dark-300 rounded-xl border border-dark-200 p-6">
            <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                <i class="bi bi-link-45deg text-orange-500"></i>
                Link M3U (Recomendado)
            </h3>
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-2">URL M3U</label>
                <div class="flex gap-2">
                    <input type="text" id="m3uUrl" value="{{ $m3u_url }}" readonly class="flex-1 px-4 py-2 bg-dark-200 border border-dark-100 rounded-lg text-white font-mono text-sm">
                    <button onclick="copyToClipboard('m3uUrl')" class="px-4 py-2 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg hover:shadow-lg transition-all">
                        <i class="bi bi-clipboard"></i>
                    </button>
                </div>
                <p class="text-xs text-gray-500 mt-2">Use este link para aplicativos IPTV (IPTV Smarters, TiviMate, etc)</p>
            </div>
        </div>

        <!-- Link HLS -->
        <div class="bg-dark-300 rounded-xl border border-dark-200 p-6">
            <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                <i class="bi bi-play-circle text-orange-500"></i>
                Link HLS (Streaming)
            </h3>
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-2">URL HLS</label>
                <div class="flex gap-2">
                    <input type="text" id="hlsUrl" value="{{ $hls_url }}" readonly class="flex-1 px-4 py-2 bg-dark-200 border border-dark-100 rounded-lg text-white font-mono text-sm">
                    <button onclick="copyToClipboard('hlsUrl')" class="px-4 py-2 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg hover:shadow-lg transition-all">
                        <i class="bi bi-clipboard"></i>
                    </button>
                </div>
                <p class="text-xs text-gray-500 mt-2">Use este link para players que suportam HLS</p>
            </div>
        </div>

        <!-- Credenciais -->
        <div class="bg-dark-300 rounded-xl border border-dark-200 p-6">
            <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                <i class="bi bi-person-badge text-orange-500"></i>
                Credenciais de Acesso
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Usuário</label>
                    <div class="flex gap-2">
                        <input type="text" id="username" value="{{ $client['username'] }}" readonly class="flex-1 px-4 py-2 bg-dark-200 border border-dark-100 rounded-lg text-white">
                        <button onclick="copyToClipboard('username')" class="px-4 py-2 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg hover:shadow-lg transition-all">
                            <i class="bi bi-clipboard"></i>
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Senha</label>
                    <div class="flex gap-2">
                        <input type="text" id="password" value="{{ $client['password'] }}" readonly class="flex-1 px-4 py-2 bg-dark-200 border border-dark-100 rounded-lg text-white">
                        <button onclick="copyToClipboard('password')" class="px-4 py-2 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg hover:shadow-lg transition-all">
                            <i class="bi bi-clipboard"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <!-- Como Usar -->
        <div class="bg-dark-300 rounded-xl border border-dark-200 p-6">
            <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                <i class="bi bi-info-circle text-orange-500"></i>
                Como Usar
            </h3>
            <div class="space-y-4">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Método 1: Link M3U</p>
                    <ol class="space-y-1 text-sm text-gray-400 ml-4">
                        <li class="flex items-start gap-2">
                            <span class="text-orange-500">1.</span>
                            <span>Copie o link M3U</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-orange-500">2.</span>
                            <span>Abra seu aplicativo IPTV</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-orange-500">3.</span>
                            <span>Cole o link na opção "Adicionar Lista"</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-orange-500">4.</span>
                            <span>Aguarde o carregamento</span>
                        </li>
                    </ol>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Método 2: Credenciais</p>
                    <ol class="space-y-1 text-sm text-gray-400 ml-4">
                        <li class="flex items-start gap-2">
                            <span class="text-orange-500">1.</span>
                            <span>Abra seu aplicativo IPTV</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-orange-500">2.</span>
                            <span>Escolha "Login com Xtream Codes"</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-orange-500">3.</span>
                            <span>Digite usuário e senha</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-orange-500">4.</span>
                            <span>Configure o servidor</span>
                        </li>
                    </ol>
                </div>
            </div>
            <div class="mt-4 p-3 bg-yellow-500/10 border border-yellow-500/50 rounded-lg">
                <p class="text-yellow-400 text-sm flex items-start gap-2">
                    <i class="bi bi-exclamation-triangle-fill mt-0.5"></i>
                    <span><strong>Importante:</strong> Não compartilhe estas credenciais</span>
                </p>
            </div>
        </div>

        <!-- Apps Recomendados -->
        <div class="bg-dark-300 rounded-xl border border-dark-200 p-6">
            <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                <i class="bi bi-app-indicator text-orange-500"></i>
                Apps Recomendados
            </h3>
            <ul class="space-y-2 text-sm text-gray-400">
                <li class="flex items-center gap-2">
                    <i class="bi bi-check-circle text-green-500"></i>
                    <span>IPTV Smarters Pro</span>
                </li>
                <li class="flex items-center gap-2">
                    <i class="bi bi-check-circle text-green-500"></i>
                    <span>TiviMate</span>
                </li>
                <li class="flex items-center gap-2">
                    <i class="bi bi-check-circle text-green-500"></i>
                    <span>Perfect Player</span>
                </li>
                <li class="flex items-center gap-2">
                    <i class="bi bi-check-circle text-green-500"></i>
                    <span>GSE Smart IPTV</span>
                </li>
            </ul>
        </div>
    </div>
</div>

    </div>
</div>

@push('scripts')
<script>
function copyToClipboard(elementId) {
    const element = document.getElementById(elementId);
    element.select();
    element.setSelectionRange(0, 99999);
    
    navigator.clipboard.writeText(element.value).then(() => {
        const btn = event.target.closest('button');
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check"></i>';
        btn.classList.add('bg-green-500');
        
        setTimeout(() => {
            btn.innerHTML = originalHtml;
            btn.classList.remove('bg-green-500');
        }, 2000);
    });
}

// Fechar modal com ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        window.location.href = '{{ route('clients.index') }}';
    }
});
</script>
@endpush
@endsection
