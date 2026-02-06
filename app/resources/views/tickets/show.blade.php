@extends('layouts.app')

@section('title', 'Ticket #' . $ticket->id)

@section('content')
<div class="h-[calc(100vh-9rem)] flex flex-col max-w-5xl mx-auto">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-center justify-between mb-3 gap-2 shrink-0">
        <div class="flex items-center gap-3 overflow-hidden">
            <a href="{{ route('tickets.index') }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors p-1">
                <i class="bi bi-arrow-left text-xl"></i>
            </a>
            <div class="flex flex-col">
                <div class="flex items-center gap-2">
                    <h1 class="text-lg font-bold text-gray-900 dark:text-white truncate">Ticket #{{ $ticket->id }}</h1>
                    @php
                        $statusColors = [
                            1 => 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400',
                            0 => 'bg-gray-100 text-gray-700 dark:bg-gray-500/20 dark:text-gray-400',
                            2 => 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400',
                        ];
                        $statusLabel = [
                            1 => 'Aberto',
                            0 => 'Fechado',
                            2 => 'Respondido',
                        ];
                    @endphp
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $statusColors[$ticket->status] ?? 'bg-gray-100 text-gray-500' }}">
                        {{ $statusLabel[$ticket->status] ?? 'Desconhecido' }}
                    </span>
                </div>
                <h2 class="text-sm text-gray-500 dark:text-gray-400 truncate max-w-[250px] sm:max-w-md">{{ $ticket->title }}</h2>
            </div>
        </div>
        
        @if($ticket->status != 0)
        <form action="{{ route('tickets.close', $ticket->id) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja fechar este ticket?');">
            @csrf
            @method('PUT')
            <button type="submit" class="text-xs bg-red-100 dark:bg-red-900/20 text-red-600 dark:text-red-400 hover:bg-red-200 dark:hover:bg-red-900/40 font-bold py-1.5 px-3 rounded-lg transition-colors flex items-center gap-1">
                <i class="bi bi-x-circle"></i>
                Fechar
            </button>
        </form>
        @endif
    </div>

    <!-- Chat Card Unificado -->
    <div class="flex-1 flex flex-col bg-[#e5ddd5] dark:bg-[#0b141a] rounded-xl overflow-hidden shadow-md border border-gray-200 dark:border-dark-200 relative">
        <!-- Background Image -->
        <div class="absolute inset-0 z-0 pointer-events-none opacity-40 dark:opacity-20" 
             style="background-image: url('{{ asset('background_chat.png') }}'); background-repeat: repeat;">
        </div>

        <!-- Área de Mensagens -->
        <div class="relative z-10 flex-1 overflow-y-auto p-4 space-y-3 custom-scrollbar scroll-smooth" id="chatContainer">
            <!-- Info Categoria (Sticky Top) -->
            @if($ticket->category)
            <div class="flex justify-center sticky top-0 z-20 pb-4">
                <div class="bg-white/90 dark:bg-dark-300/90 backdrop-blur-md shadow-sm rounded-full px-3 py-1 text-[10px] uppercase font-bold text-gray-500 dark:text-gray-400 border border-gray-100 dark:border-dark-100 flex items-center gap-2">
                    <span class="text-orange-600 dark:text-orange-500">{{ $ticket->category->name }}</span>
                    @if(Auth::user()->isAdmin() && $ticket->category->responsible)
                        <span class="w-1 h-1 rounded-full bg-gray-300 dark:bg-gray-600"></span>
                        <span>{{ $ticket->category->responsible }}</span>
                    @endif
                </div>
            </div>
            @endif
            
            @foreach($ticket->replies as $reply)
                @php
                    $isAdminMsg = $reply->admin_reply == 1;
                    $isMe = Auth::user()->isAdmin() ? $isAdminMsg : !$isAdminMsg;
                    
                    // Estilo WhatsApp
                    $bubbleClass = $isMe 
                        ? 'bg-[#d9fdd3] dark:bg-[#005c4b] rounded-2xl rounded-tr-none ml-auto shadow-sm' 
                        : 'bg-white dark:bg-[#202c33] rounded-2xl rounded-tl-none mr-auto shadow-sm';
                    
                    $textClass = 'text-gray-900 dark:text-[#e9edef]';
                    $metaClass = 'text-gray-500 dark:text-[#8696a0]';
                @endphp
                
                <div class="flex w-full {{ $isMe ? 'justify-end' : 'justify-start' }} group animate-fade-in">
                    <div class="max-w-[85%] md:max-w-[70%]">
                        <!-- Nome (se não for eu e for admin vendo) -->
                        @if(!$isMe && Auth::user()->isAdmin() && !$isAdminMsg)
                        <div class="text-[10px] font-bold text-orange-600 dark:text-orange-500 mb-0.5 ml-2">
                            {{ $ticket->user->username ?? 'Revendedor' }}
                        </div>
                        @endif

                        <div class="p-2 px-3 {{ $bubbleClass }} relative min-w-[100px]">
                            <!-- Mensagem -->
                            <div class="text-sm whitespace-pre-wrap leading-relaxed {{ $textClass }} break-words pb-3">
                                {!! nl2br(e($reply->message)) !!}
                            </div>
                            
                            <!-- Metadados (Hora + Check) -->
                            <div class="absolute bottom-1 right-2 flex items-center gap-1">
                                <span class="text-[9px] {{ $metaClass }}">{{ date('H:i', $reply->date) }}</span>
                                @if($isMe)
                                    <i class="bi bi-check2-all text-xs {{ ($ticket->admin_read && !$isAdminMsg) || ($ticket->user_read && $isAdminMsg) ? 'text-[#53bdeb]' : 'text-gray-400' }}"></i>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Área de Input (Fixa no rodapé do card) -->
        <div class="relative z-20 bg-[#f0f2f5] dark:bg-[#202c33] p-3 border-t border-gray-200 dark:border-gray-700/30">
            @if($ticket->status != 0)
            <form action="{{ route('tickets.reply', $ticket->id) }}" method="POST" id="replyForm">
                @csrf
                <div class="flex items-end gap-2">
                    <div class="flex-1 bg-white dark:bg-[#2a3942] rounded-xl border border-transparent focus-within:border-gray-300 dark:focus-within:border-gray-600 transition-colors shadow-sm">
                        <textarea name="message" rows="1" required
                            class="w-full bg-transparent px-4 py-3 text-gray-900 dark:text-white focus:outline-none placeholder-gray-500 dark:placeholder-gray-400 resize-none max-h-32 overflow-y-auto custom-scrollbar"
                            placeholder="Digite uma mensagem"
                            oninput="this.style.height = ''; this.style.height = Math.min(this.scrollHeight, 128) + 'px'"></textarea>
                    </div>
                    <button type="submit" class="w-11 h-11 flex items-center justify-center bg-[#00a884] hover:bg-[#008f72] text-white rounded-full shadow-md transition-all duration-200 transform hover:scale-105 shrink-0">
                        <i class="bi bi-send-fill text-lg ml-0.5"></i>
                    </button>
                </div>
            </form>
            @else
            <div class="flex flex-col items-center justify-center py-2 text-gray-500 dark:text-gray-400">
                <i class="bi bi-lock-fill text-xl mb-1"></i>
                <span class="text-xs font-medium uppercase tracking-wide">Ticket Fechado</span>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Auto scroll to bottom
    function scrollToBottom() {
        const chatContainer = document.getElementById('chatContainer');
        if (chatContainer) {
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }
    }

    // Scroll on load
    scrollToBottom();

    // Submit on Enter (optional, like Whatsapp Web)
    const textarea = document.querySelector('textarea[name="message"]');
    if (textarea) {
        textarea.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                if (this.value.trim().length > 0) {
                    document.getElementById('replyForm').submit();
                }
            }
        });
    }
</script>
<style>
    .animate-fade-in {
        animation: fadeIn 0.3s ease-out forwards;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endpush
@endsection
