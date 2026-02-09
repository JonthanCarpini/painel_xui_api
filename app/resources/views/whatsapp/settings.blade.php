@extends('layouts.app')

@section('title', 'WhatsApp - Configurações')

@section('content')
<div class="w-full">
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-6 md:mb-8 gap-4">
        <div>
            <h1 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <i class="bi bi-gear text-green-500"></i>
                Configura&ccedil;&otilde;es de Notifica&ccedil;&atilde;o
            </h1>
            <p class="text-sm md:text-base text-gray-500 dark:text-gray-400 mt-1">Configure as mensagens e hor&aacute;rios de envio autom&aacute;tico.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-3 py-1.5 rounded-full text-xs font-bold {{ $setting->connection_status === 'connected' ? 'bg-green-100 dark:bg-green-500/10 text-green-600 dark:text-green-500 border border-green-200 dark:border-green-500/30' : 'bg-red-100 dark:bg-red-500/10 text-red-600 dark:text-red-500 border border-red-200 dark:border-red-500/30' }}">
                <i class="bi {{ $setting->connection_status === 'connected' ? 'bi-check-circle-fill' : 'bi-x-circle-fill' }}"></i>
                {{ $setting->connection_status === 'connected' ? 'Conectado' : 'Desconectado' }}
            </span>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-6 p-4 bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/30 rounded-xl text-green-700 dark:text-green-400 text-sm flex items-center gap-2">
        <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
    </div>
    @endif

    <div class="bg-white dark:bg-dark-300 border border-gray-200 dark:border-dark-200 rounded-xl overflow-hidden shadow-sm">
        <div class="border-b border-gray-200 dark:border-dark-200 px-8 py-6 bg-gray-50 dark:bg-dark-200/50">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <i class="bi bi-chat-left-text text-orange-500"></i> Notifica&ccedil;&otilde;es de Vencimento
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Configure as mensagens enviadas automaticamente aos seus clientes.</p>
        </div>

        <form action="{{ route('whatsapp.update-settings') }}" method="POST" class="p-8 space-y-8">
            @csrf
            @method('PUT')

            {{-- Toggle Ativar/Desativar --}}
            <label class="flex items-center justify-between p-4 bg-gray-50 dark:bg-dark-200 rounded-xl border border-gray-200 dark:border-dark-100 cursor-pointer hover:border-green-500 dark:hover:border-green-500 transition-colors group">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-full bg-gray-200 dark:bg-dark-300 flex items-center justify-center group-hover:bg-green-100 dark:group-hover:bg-green-500/20 transition-colors">
                        <i class="bi bi-bell text-gray-600 dark:text-gray-400 group-hover:text-green-600 dark:group-hover:text-green-500"></i>
                    </div>
                    <div>
                        <span class="block text-sm font-bold text-gray-900 dark:text-white">Notifica&ccedil;&otilde;es Ativas</span>
                        <span class="block text-xs text-gray-500 dark:text-gray-400">Enviar alertas de vencimento via WhatsApp para clientes oficiais.</span>
                    </div>
                </div>
                <div class="relative inline-flex items-center">
                    <input type="checkbox" name="notifications_enabled" value="1" class="sr-only peer" {{ $setting->notifications_enabled ? 'checked' : '' }}>
                    <div class="w-11 h-6 bg-gray-300 dark:bg-dark-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500"></div>
                </div>
            </label>

            {{-- Hor&aacute;rio e Intervalo --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        <i class="bi bi-clock text-blue-500"></i> Hor&aacute;rio de in&iacute;cio dos envios
                    </label>
                    <input type="time" name="send_start_time" value="{{ $setting->send_start_time ?? '09:00' }}" class="w-full px-4 py-3 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors text-sm" required>
                    <p class="mt-1 text-xs text-gray-400">Hor&aacute;rio em que o sistema come&ccedil;a a enviar as notifica&ccedil;&otilde;es di&aacute;rias.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        <i class="bi bi-hourglass-split text-purple-500"></i> Intervalo entre envios
                    </label>
                    <select name="send_interval_seconds" class="w-full px-4 py-3 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500 transition-colors text-sm" required>
                        <option value="10" {{ ($setting->send_interval_seconds ?? 30) == 10 ? 'selected' : '' }}>10 segundos</option>
                        <option value="15" {{ ($setting->send_interval_seconds ?? 30) == 15 ? 'selected' : '' }}>15 segundos</option>
                        <option value="20" {{ ($setting->send_interval_seconds ?? 30) == 20 ? 'selected' : '' }}>20 segundos</option>
                        <option value="30" {{ ($setting->send_interval_seconds ?? 30) == 30 ? 'selected' : '' }}>30 segundos</option>
                        <option value="45" {{ ($setting->send_interval_seconds ?? 30) == 45 ? 'selected' : '' }}>45 segundos</option>
                        <option value="60" {{ ($setting->send_interval_seconds ?? 30) == 60 ? 'selected' : '' }}>1 minuto</option>
                        <option value="120" {{ ($setting->send_interval_seconds ?? 30) == 120 ? 'selected' : '' }}>2 minutos</option>
                        <option value="180" {{ ($setting->send_interval_seconds ?? 30) == 180 ? 'selected' : '' }}>3 minutos</option>
                        <option value="300" {{ ($setting->send_interval_seconds ?? 30) == 300 ? 'selected' : '' }}>5 minutos</option>
                    </select>
                    <p class="mt-1 text-xs text-gray-400">Tempo de espera entre cada mensagem enviada (evita bloqueio do WhatsApp).</p>
                </div>
            </div>

            {{-- Vari&aacute;veis --}}
            <div class="bg-blue-50 dark:bg-blue-500/5 border border-blue-200 dark:border-blue-500/20 rounded-lg p-4">
                <p class="text-sm text-blue-700 dark:text-blue-400 font-medium mb-2"><i class="bi bi-info-circle"></i> Vari&aacute;veis dispon&iacute;veis:</p>
                <div class="flex flex-wrap gap-2">
                    <code class="px-2 py-1 bg-blue-100 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 rounded text-xs">{cliente}</code>
                    <code class="px-2 py-1 bg-blue-100 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 rounded text-xs">{vencimento}</code>
                    <code class="px-2 py-1 bg-blue-100 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 rounded text-xs">{usuario}</code>
                    <code class="px-2 py-1 bg-blue-100 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 rounded text-xs">{senha}</code>
                </div>
            </div>

            {{-- Mensagem 3 dias --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    <i class="bi bi-calendar-event text-orange-500"></i> Mensagem — 3 dias antes do vencimento
                </label>
                <textarea name="expiry_message_3d" rows="4" class="w-full px-4 py-3 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500 transition-colors resize-none text-sm" placeholder="{{ (new \App\Models\WhatsappSetting)->getDefaultMessage3d() }}">{{ $setting->expiry_message_3d }}</textarea>
                <p class="mt-1 text-xs text-gray-400">Deixe vazio para usar a mensagem padr&atilde;o.</p>
            </div>

            {{-- Mensagem 1 dia --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    <i class="bi bi-calendar-minus text-yellow-500"></i> Mensagem — 1 dia antes do vencimento
                </label>
                <textarea name="expiry_message_1d" rows="4" class="w-full px-4 py-3 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500 transition-colors resize-none text-sm" placeholder="{{ (new \App\Models\WhatsappSetting)->getDefaultMessage1d() }}">{{ $setting->expiry_message_1d }}</textarea>
                <p class="mt-1 text-xs text-gray-400">Deixe vazio para usar a mensagem padr&atilde;o.</p>
            </div>

            {{-- Mensagem hoje --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    <i class="bi bi-calendar-x text-red-500"></i> Mensagem — No dia do vencimento
                </label>
                <textarea name="expiry_message_today" rows="4" class="w-full px-4 py-3 bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg text-gray-900 dark:text-white focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500 transition-colors resize-none text-sm" placeholder="{{ (new \App\Models\WhatsappSetting)->getDefaultMessageToday() }}">{{ $setting->expiry_message_today }}</textarea>
                <p class="mt-1 text-xs text-gray-400">Deixe vazio para usar a mensagem padr&atilde;o.</p>
            </div>

            <div class="pt-4 flex justify-end border-t border-gray-100 dark:border-dark-200">
                <button type="submit" class="px-8 py-3 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-bold rounded-xl shadow-lg shadow-green-500/20 transition-all duration-200 flex items-center gap-2">
                    <i class="bi bi-check-circle-fill"></i>
                    Salvar Configura&ccedil;&otilde;es
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
