@extends('layouts.app')

@section('title', 'WhatsApp - Conexão')

@section('content')
<div class="w-full">
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-6 md:mb-8 gap-4">
        <div>
            <h1 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <i class="bi bi-whatsapp text-green-500"></i>
                Conex&atilde;o WhatsApp
            </h1>
            <p class="text-sm md:text-base text-gray-500 dark:text-gray-400 mt-1">Conecte seu WhatsApp para enviar notifica&ccedil;&otilde;es autom&aacute;ticas.</p>
        </div>
    </div>

    @if(session('warning'))
    <div class="mb-6 p-4 bg-yellow-50 dark:bg-yellow-500/10 border border-yellow-200 dark:border-yellow-500/30 rounded-xl text-yellow-700 dark:text-yellow-400 text-sm flex items-center gap-2">
        <i class="bi bi-exclamation-triangle"></i> {{ session('warning') }}
    </div>
    @endif

    @if(!$setting)
    {{-- Sem inst&acirc;ncia --}}
    <div class="bg-white dark:bg-dark-300 border border-gray-200 dark:border-dark-200 rounded-xl overflow-hidden shadow-sm">
        <div class="border-b border-gray-200 dark:border-dark-200 px-8 py-6 bg-gray-50 dark:bg-dark-200/50">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <i class="bi bi-whatsapp text-green-500"></i> Conex&atilde;o WhatsApp
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Conecte seu WhatsApp para enviar notifica&ccedil;&otilde;es autom&aacute;ticas de vencimento aos seus clientes.</p>
        </div>
        <div class="p-8 text-center">
            <div class="w-20 h-20 bg-green-100 dark:bg-green-500/10 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="bi bi-whatsapp text-green-500 text-4xl"></i>
            </div>
            <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Nenhuma inst&acirc;ncia configurada</h4>
            <p class="text-gray-500 dark:text-gray-400 text-sm mb-6 max-w-md mx-auto">
                Crie uma inst&acirc;ncia WhatsApp para come&ccedil;ar a enviar notifica&ccedil;&otilde;es autom&aacute;ticas de vencimento para seus clientes.
            </p>
            <button onclick="createWhatsappInstance()" id="btnCreateInstance" class="px-6 py-3 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-xl hover:shadow-lg transition-all font-medium flex items-center gap-2 mx-auto">
                <i class="bi bi-plus-circle"></i>
                Criar Inst&acirc;ncia WhatsApp
            </button>
        </div>
    </div>
    @else
    {{-- Inst&acirc;ncia existe --}}
    <div class="bg-white dark:bg-dark-300 border border-gray-200 dark:border-dark-200 rounded-xl overflow-hidden shadow-sm">
        <div class="border-b border-gray-200 dark:border-dark-200 px-8 py-6 bg-gray-50 dark:bg-dark-200/50 flex items-center justify-between">
            <div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="bi bi-whatsapp text-green-500"></i> Conex&atilde;o WhatsApp
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Inst&acirc;ncia: <code class="bg-gray-200 dark:bg-dark-400 px-2 py-0.5 rounded text-xs">{{ $setting->instance_name }}</code></p>
            </div>
            <div id="statusBadge">
                <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-gray-100 dark:bg-dark-200 text-gray-500 dark:text-gray-400">
                    <i class="bi bi-arrow-repeat animate-spin"></i> Verificando...
                </span>
            </div>
        </div>

        <div class="p-8">
            {{-- QR Code Area --}}
            <div id="qrcodeArea" class="hidden">
                <div class="text-center">
                    <p class="text-gray-600 dark:text-gray-400 mb-4">Escaneie o QR Code com seu WhatsApp para conectar:</p>
                    <div id="qrcodeContainer" class="inline-block bg-white p-4 rounded-xl border border-gray-200 dark:border-dark-200 shadow-sm mb-4">
                        <div class="w-64 h-64 flex items-center justify-center">
                            <i class="bi bi-arrow-repeat animate-spin text-3xl text-gray-400"></i>
                        </div>
                    </div>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mb-4">Ap&oacute;s escanear, clique no bot&atilde;o abaixo para confirmar.</p>
                    <button onclick="confirmScanned()" id="btnConfirmScan" class="px-6 py-3 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-xl hover:shadow-lg transition-all font-medium flex items-center gap-2 mx-auto">
                        <i class="bi bi-check-circle"></i> J&aacute; escaneei o QR Code
                    </button>
                </div>
            </div>

            {{-- Connected Area --}}
            <div id="connectedArea" class="hidden">
                <div class="text-center">
                    <div class="w-16 h-16 bg-green-100 dark:bg-green-500/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="bi bi-check-circle-fill text-green-500 text-3xl"></i>
                    </div>
                    <h4 class="text-lg font-bold text-green-600 dark:text-green-500 mb-2">WhatsApp Conectado!</h4>
                    <p class="text-gray-500 dark:text-gray-400 text-sm mb-6">Seu WhatsApp est&aacute; conectado e pronto para enviar notifica&ccedil;&otilde;es.</p>
                    <div class="flex gap-3 justify-center">
                        <button onclick="disconnectWhatsapp()" class="px-4 py-2 bg-yellow-100 dark:bg-yellow-500/10 text-yellow-600 dark:text-yellow-500 border border-yellow-200 dark:border-yellow-500/30 rounded-lg hover:bg-yellow-200 dark:hover:bg-yellow-500/20 transition-colors text-sm font-medium">
                            <i class="bi bi-box-arrow-right"></i> Desconectar
                        </button>
                        <button onclick="deleteWhatsappInstance()" class="px-4 py-2 bg-red-100 dark:bg-red-500/10 text-red-600 dark:text-red-500 border border-red-200 dark:border-red-500/30 rounded-lg hover:bg-red-200 dark:hover:bg-red-500/20 transition-colors text-sm font-medium">
                            <i class="bi bi-trash"></i> Excluir Inst&acirc;ncia
                        </button>
                    </div>
                </div>
            </div>

            {{-- Disconnected Area --}}
            <div id="disconnectedArea" class="hidden">
                <div class="text-center">
                    <div class="w-16 h-16 bg-yellow-100 dark:bg-yellow-500/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="bi bi-exclamation-triangle text-yellow-500 text-3xl"></i>
                    </div>
                    <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-2">WhatsApp Desconectado</h4>
                    <p class="text-gray-500 dark:text-gray-400 text-sm mb-6">Clique abaixo para reconectar escaneando o QR Code.</p>
                    <div class="flex gap-3 justify-center">
                        <button onclick="fetchQrCode()" class="px-6 py-2 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg hover:shadow-lg transition-all font-medium">
                            <i class="bi bi-qr-code"></i> Conectar
                        </button>
                        <button onclick="deleteWhatsappInstance()" class="px-4 py-2 bg-red-100 dark:bg-red-500/10 text-red-600 dark:text-red-500 border border-red-200 dark:border-red-500/30 rounded-lg hover:bg-red-200 dark:hover:bg-red-500/20 transition-colors text-sm font-medium">
                            <i class="bi bi-trash"></i> Excluir
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
(function() {
    const csrfToken = '{{ csrf_token() }}';
    let statusInterval = null;
    let qrRenewTimeout = null;
    let waitingForScan = false;
    const hasInstance = {{ $setting ? 'true' : 'false' }};
    const QR_LIFETIME_MS = 45000;
    const POLL_FAST_MS = 3000;
    const POLL_IDLE_MS = 15000;

    if (hasInstance) {
        checkStatus();
        statusInterval = setInterval(checkStatus, POLL_IDLE_MS);
    }

    window.createWhatsappInstance = function() {
        const btn = document.getElementById('btnCreateInstance');
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-arrow-repeat animate-spin"></i> Criando...';

        fetch('{{ route("whatsapp.create-instance") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erro: ' + data.message);
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-plus-circle"></i> Criar Inst\u00e2ncia WhatsApp';
            }
        })
        .catch(() => {
            alert('Erro ao criar inst\u00e2ncia.');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-plus-circle"></i> Criar Inst\u00e2ncia WhatsApp';
        });
    };

    window.fetchQrCode = function() {
        waitingForScan = true;
        document.getElementById('qrcodeArea')?.classList.remove('hidden');
        document.getElementById('disconnectedArea')?.classList.add('hidden');
        document.getElementById('connectedArea')?.classList.add('hidden');
        loadQrOnce();
        if (statusInterval) clearInterval(statusInterval);
        statusInterval = setInterval(checkStatus, POLL_FAST_MS);
    };

    function loadQrOnce() {
        const container = document.getElementById('qrcodeContainer');
        container.innerHTML = '<div class="w-64 h-64 flex items-center justify-center"><i class="bi bi-arrow-repeat animate-spin text-3xl text-gray-400"></i></div>';

        fetch('{{ route("whatsapp.qrcode") }}', {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success && data.qrcode) {
                const src = data.qrcode.startsWith('data:') ? data.qrcode : 'data:image/png;base64,' + data.qrcode;
                container.innerHTML = '<img src="' + src + '" class="w-64 h-64" alt="QR Code">';
                if (qrRenewTimeout) clearTimeout(qrRenewTimeout);
                qrRenewTimeout = setTimeout(function() {
                    if (waitingForScan) loadQrOnce();
                }, QR_LIFETIME_MS);
            } else if (data.success && !data.qrcode) {
                checkStatus();
            }
        })
        .catch(() => {
            container.innerHTML = '<div class="w-64 h-64 flex items-center justify-center text-red-500"><i class="bi bi-exclamation-triangle text-3xl"></i></div>';
        });
    }

    function checkStatus() {
        fetch('{{ route("whatsapp.status") }}', {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
        })
        .then(r => r.json())
        .then(data => {
            const badge = document.getElementById('statusBadge');
            const qrArea = document.getElementById('qrcodeArea');
            const connArea = document.getElementById('connectedArea');
            const discArea = document.getElementById('disconnectedArea');

            if (data.status === 'connected') {
                if (badge) badge.innerHTML = '<span class="px-3 py-1.5 rounded-full text-xs font-bold bg-green-100 dark:bg-green-500/10 text-green-600 dark:text-green-500 border border-green-200 dark:border-green-500/30"><i class="bi bi-check-circle-fill"></i> Conectado</span>';
                qrArea?.classList.add('hidden');
                connArea?.classList.remove('hidden');
                discArea?.classList.add('hidden');
                stopWaiting();
            } else if (data.status === 'connecting') {
                if (badge) badge.innerHTML = '<span class="px-3 py-1.5 rounded-full text-xs font-bold bg-yellow-100 dark:bg-yellow-500/10 text-yellow-600 dark:text-yellow-500 border border-yellow-200 dark:border-yellow-500/30"><i class="bi bi-arrow-repeat animate-spin"></i> Conectando...</span>';
            } else {
                if (badge) badge.innerHTML = '<span class="px-3 py-1.5 rounded-full text-xs font-bold bg-red-100 dark:bg-red-500/10 text-red-600 dark:text-red-500 border border-red-200 dark:border-red-500/30"><i class="bi bi-x-circle-fill"></i> Desconectado</span>';
                connArea?.classList.add('hidden');
                if (!waitingForScan) {
                    discArea?.classList.remove('hidden');
                    qrArea?.classList.add('hidden');
                }
            }
        })
        .catch(() => {});
    }

    function stopWaiting() {
        waitingForScan = false;
        if (qrRenewTimeout) { clearTimeout(qrRenewTimeout); qrRenewTimeout = null; }
        if (statusInterval) clearInterval(statusInterval);
        statusInterval = setInterval(checkStatus, POLL_IDLE_MS);
    }

    window.confirmScanned = function() {
        const btn = document.getElementById('btnConfirmScan');
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-arrow-repeat animate-spin"></i> Verificando conex\u00e3o...';

        if (qrRenewTimeout) { clearTimeout(qrRenewTimeout); qrRenewTimeout = null; }

        document.getElementById('qrcodeContainer').innerHTML = '<div class="w-64 h-64 flex items-center justify-center"><div class="text-center"><i class="bi bi-arrow-repeat animate-spin text-3xl text-green-500 mb-2"></i><p class="text-sm text-gray-400">Reiniciando inst\u00e2ncia...</p></div></div>';

        fetch('{{ route("whatsapp.confirm-scan") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'connected') {
                checkStatus();
            } else {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-check-circle"></i> J\u00e1 escaneei o QR Code';
                if (statusInterval) clearInterval(statusInterval);
                statusInterval = setInterval(checkStatus, 2000);
                setTimeout(function() {
                    if (statusInterval) clearInterval(statusInterval);
                    statusInterval = setInterval(checkStatus, POLL_IDLE_MS);
                }, 20000);
            }
        })
        .catch(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-circle"></i> J\u00e1 escaneei o QR Code';
        });
    };

    window.disconnectWhatsapp = function() {
        if (!confirm('Deseja desconectar o WhatsApp?')) return;
        fetch('{{ route("whatsapp.disconnect") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => { if (data.success) location.reload(); else alert('Erro ao desconectar.'); })
        .catch(() => alert('Erro ao desconectar.'));
    };

    window.deleteWhatsappInstance = function() {
        if (!confirm('Tem certeza que deseja excluir a inst\u00e2ncia WhatsApp? Todas as configura\u00e7\u00f5es ser\u00e3o perdidas.')) return;
        fetch('{{ route("whatsapp.delete") }}', {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => { if (data.success) location.reload(); else alert('Erro ao excluir.'); })
        .catch(() => alert('Erro ao excluir.'));
    };
})();
</script>
@endpush
@endsection
