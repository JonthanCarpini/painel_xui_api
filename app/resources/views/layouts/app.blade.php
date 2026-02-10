<!DOCTYPE html>
<html lang="pt-BR" class="{{ Auth::check() && Auth::user()->getPreference('dark_mode', true) ? 'dark' : '' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', Auth::check() ? Auth::user()->getPreference('panel_name', 'Painel Office') : 'Painel Office')</title>
    
    <style>[x-cloak] { display: none !important; }</style>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        'dark': {
                            50: '#2D3748',
                            100: '#1F2937',
                            200: '#1A202C',
                            300: '#151A23',
                            400: '#0F1419',
                            500: '#0A0E13',
                        }
                    }
                }
            }
        }
    </script>
    
    <style>
        html {
            font-size: 14px;
        }
        
        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.05);
        }
        
        .dark ::-webkit-scrollbar-track {
            background: #151A23;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        
        .dark ::-webkit-scrollbar-thumb {
            background: #2D3748;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        
        .dark ::-webkit-scrollbar-thumb:hover {
            background: #4A5568;
        }
        
        /* Smooth transitions */
        .transition-colors {
            transition-property: background-color, border-color, color, fill, stroke;
            transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
            transition-duration: 200ms;
        }
    </style>
    
    @stack('styles')
</head>
<body class="bg-gray-50 dark:bg-dark-400 font-['Inter'] antialiased text-sm text-gray-900 dark:text-gray-100 transition-colors duration-200">
    
    <!-- Sidebar Overlay -->
    <div id="sidebarOverlay" onclick="toggleSidebar()" class="fixed inset-0 bg-black/50 z-40 hidden lg:hidden transition-opacity opacity-0"></div>

    <!-- Sidebar -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 h-screen bg-white dark:bg-dark-300 border-r border-gray-200 dark:border-dark-200 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 flex flex-col">
        <!-- Logo -->
        <div class="p-6 border-b border-gray-200 dark:border-dark-200 shrink-0 flex justify-between items-center">
            <div class="flex items-center gap-3">
                @if(Auth::check() && Auth::user()->getPreference('logo_url'))
                    <img src="{{ Auth::user()->getPreference('logo_url') }}" alt="Logo" class="w-10 h-10 rounded-lg object-cover">
                @else
                    <div class="w-10 h-10 bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg flex items-center justify-center shrink-0">
                        <i class="bi bi-tv text-white text-xl"></i>
                    </div>
                @endif
                <div class="overflow-hidden">
                    <h1 class="text-gray-900 dark:text-white font-bold text-lg truncate">{{ Auth::check() ? Auth::user()->getPreference('panel_name', 'Office IPTV') : 'Office IPTV' }}</h1>
                    <p class="text-gray-500 dark:text-gray-400 text-xs truncate">Painel de Revenda</p>
                </div>
            </div>
            <!-- Close Button (Mobile Only) -->
            <button onclick="toggleSidebar()" class="lg:hidden text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white">
                <i class="bi bi-x-lg text-xl"></i>
            </button>
        </div>

        <!-- Menu -->
        <nav class="flex-1 overflow-y-auto p-4 custom-scrollbar">
            <div class="space-y-6">
                
                <!-- Visão Geral -->
                <div>
                    <p class="text-gray-500 dark:text-gray-500 text-xs font-semibold uppercase tracking-wider mb-2 px-3">Visão Geral</p>
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('dashboard') ? 'bg-gradient-to-r from-orange-500 to-orange-600 text-white shadow-md shadow-orange-500/20' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-dark-200 hover:text-gray-900 dark:hover:text-white' }} transition-all duration-200">
                        <i class="bi bi-grid text-lg"></i>
                        <span class="font-medium">Dashboard</span>
                    </a>
                </div>

                <!-- Gestão de Clientes -->
                <div>
                    <p class="text-gray-500 dark:text-gray-500 text-xs font-semibold uppercase tracking-wider mb-2 px-3">Gestão de Clientes</p>
                    <a href="{{ route('clients.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('clients.index') ? 'bg-gradient-to-r from-orange-500 to-orange-600 text-white shadow-md shadow-orange-500/20' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-dark-200 hover:text-gray-900 dark:hover:text-white' }} transition-all duration-200">
                        <i class="bi bi-people text-lg"></i>
                        <span class="font-medium">Meus Clientes</span>
                    </a>
                    
                    <!-- Submenu Actions -->
                    <div class="ml-3 mt-1 space-y-1 border-l-2 border-gray-100 dark:border-dark-200 pl-3">
                        <a href="{{ route('clients.create') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('clients.create') ? 'bg-orange-50 dark:bg-orange-500/10 text-orange-600 dark:text-orange-400 font-medium' : 'text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-dark-200 hover:text-gray-900 dark:hover:text-white' }} transition-all duration-200">
                            <i class="bi bi-plus-circle"></i>
                            <span>Novo Cliente</span>
                        </a>
                        <a href="{{ route('clients.create-trial') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('clients.create-trial') ? 'bg-orange-50 dark:bg-orange-500/10 text-orange-600 dark:text-orange-400 font-medium' : 'text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-dark-200 hover:text-gray-900 dark:hover:text-white' }} transition-all duration-200">
                            <i class="bi bi-clock-history"></i>
                            <span>Novo Teste</span>
                        </a>
                        <a href="{{ route('clients.export') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('clients.export') ? 'bg-orange-50 dark:bg-orange-500/10 text-orange-600 dark:text-orange-400 font-medium' : 'text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-dark-200 hover:text-gray-900 dark:hover:text-white' }} transition-all duration-200">
                            <i class="bi bi-download"></i>
                            <span>Exportar</span>
                        </a>
                    </div>
                </div>

                <!-- Gestão de Revendas (Admin ou Revendedor Master) -->
                @if(Auth::user()->isAdmin() || Auth::user()->sub_resellers_count > 0 || Auth::user()->member_group_id == 2)
                <div>
                    <p class="text-gray-500 dark:text-gray-500 text-xs font-semibold uppercase tracking-wider mb-2 px-3">{{ Auth::user()->isAdmin() ? 'Revendas' : 'Minha Equipe' }}</p>
                    <a href="{{ route('resellers.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('resellers.*') ? 'bg-gradient-to-r from-orange-500 to-orange-600 text-white shadow-md shadow-orange-500/20' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-dark-200 hover:text-gray-900 dark:hover:text-white' }} transition-all duration-200">
                        <i class="bi bi-shop text-lg"></i>
                        <span class="font-medium">Revendedores</span>
                    </a>
                    <a href="{{ route('reseller-stats.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('reseller-stats.*') ? 'bg-gradient-to-r from-orange-500 to-orange-600 text-white shadow-md shadow-orange-500/20' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-dark-200 hover:text-gray-900 dark:hover:text-white' }} transition-all duration-200">
                        <i class="bi bi-bar-chart-line text-lg"></i>
                        <span class="font-medium">Estatísticas</span>
                    </a>
                </div>
                @endif

                <!-- Ferramentas -->
                <div>
                    <p class="text-gray-500 dark:text-gray-500 text-xs font-semibold uppercase tracking-wider mb-2 px-3">Ferramentas</p>
                    <a href="{{ route('monitor.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('monitor.*') ? 'bg-gradient-to-r from-orange-500 to-orange-600 text-white shadow-md shadow-orange-500/20' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-dark-200 hover:text-gray-900 dark:hover:text-white' }} transition-all duration-200">
                        <i class="bi bi-broadcast text-lg"></i>
                        <span class="font-medium">Monitoramento</span>
                    </a>
                    <a href="{{ route('channel-test.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('channel-test.*') ? 'bg-gradient-to-r from-orange-500 to-orange-600 text-white shadow-md shadow-orange-500/20' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-dark-200 hover:text-gray-900 dark:hover:text-white' }} transition-all duration-200">
                        <i class="bi bi-play-btn text-lg"></i>
                        <span class="font-medium">Teste de Canais</span>
                    </a>
                    <a href="{{ route('vod-requests.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('vod-requests.*') ? 'bg-gradient-to-r from-orange-500 to-orange-600 text-white shadow-md shadow-orange-500/20' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-dark-200 hover:text-gray-900 dark:hover:text-white' }} transition-all duration-200">
                        <i class="bi bi-film text-lg"></i>
                        <span class="font-medium">Pedidos VOD</span>
                    </a>
                </div>

                <!-- WhatsApp -->
                <div>
                    <p class="text-gray-500 dark:text-gray-500 text-xs font-semibold uppercase tracking-wider mb-2 px-3">WhatsApp</p>
                    <a href="{{ route('whatsapp.connection') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('whatsapp.connection') ? 'bg-gradient-to-r from-green-500 to-green-600 text-white shadow-md shadow-green-500/20' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-dark-200 hover:text-gray-900 dark:hover:text-white' }} transition-all duration-200">
                        <i class="bi bi-whatsapp text-lg"></i>
                        <span class="font-medium">Conex&atilde;o</span>
                    </a>
                    <a href="{{ route('whatsapp.settings') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('whatsapp.settings') ? 'bg-gradient-to-r from-green-500 to-green-600 text-white shadow-md shadow-green-500/20' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-dark-200 hover:text-gray-900 dark:hover:text-white' }} transition-all duration-200">
                        <i class="bi bi-gear text-lg"></i>
                        <span class="font-medium">Configura&ccedil;&otilde;es</span>
                    </a>
                    <a href="{{ route('whatsapp.notifications') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('whatsapp.notifications') ? 'bg-gradient-to-r from-green-500 to-green-600 text-white shadow-md shadow-green-500/20' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-dark-200 hover:text-gray-900 dark:hover:text-white' }} transition-all duration-200">
                        <i class="bi bi-bell text-lg"></i>
                        <span class="font-medium">Notifica&ccedil;&otilde;es</span>
                    </a>
                </div>

                <!-- Suporte e Comunicação -->
                <div>
                    <p class="text-gray-500 dark:text-gray-500 text-xs font-semibold uppercase tracking-wider mb-2 px-3">Comunicação</p>
                    <a href="{{ route('notices.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('notices.*') ? 'bg-gradient-to-r from-orange-500 to-orange-600 text-white shadow-md shadow-orange-500/20' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-dark-200 hover:text-gray-900 dark:hover:text-white' }} transition-all duration-200 justify-between">
                        <div class="flex items-center gap-3">
                            <i class="bi bi-megaphone text-lg"></i>
                            <span class="font-medium">Avisos</span>
                        </div>
                        @if(isset($unreadNoticesCount) && $unreadNoticesCount > 0)
                            <span class="bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full shadow-sm">{{ $unreadNoticesCount }}</span>
                        @endif
                    </a>
                    <a href="{{ route('tickets.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('tickets.*') ? 'bg-gradient-to-r from-orange-500 to-orange-600 text-white shadow-md shadow-orange-500/20' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-dark-200 hover:text-gray-900 dark:hover:text-white' }} transition-all duration-200 justify-between">
                        <div class="flex items-center gap-3">
                            <i class="bi bi-headset text-lg"></i>
                            <span class="font-medium">Suporte</span>
                        </div>
                        @if(isset($unreadTicketsCount) && $unreadTicketsCount > 0)
                            <span class="bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full shadow-sm">{{ $unreadTicketsCount }}</span>
                        @endif
                    </a>
                </div>

                <!-- Conteúdo -->
                <div>
                    <p class="text-gray-500 dark:text-gray-500 text-xs font-semibold uppercase tracking-wider mb-2 px-3">Conteúdo</p>
                    <a href="{{ route('updates.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('updates.*') ? 'bg-gradient-to-r from-orange-500 to-orange-600 text-white shadow-md shadow-orange-500/20' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-dark-200 hover:text-gray-900 dark:hover:text-white' }} transition-all duration-200">
                        <i class="bi bi-stars text-lg"></i>
                        <span class="font-medium">Atualizações</span>
                    </a>
                </div>

                <!-- Financeiro & Logs -->
                <div>
                    <p class="text-gray-500 dark:text-gray-500 text-xs font-semibold uppercase tracking-wider mb-2 px-3">Financeiro & Logs</p>
                    <a href="{{ route('credit-logs.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('credit-logs.index') ? 'bg-gradient-to-r from-orange-500 to-orange-600 text-white shadow-md shadow-orange-500/20' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-dark-200 hover:text-gray-900 dark:hover:text-white' }} transition-all duration-200">
                        <i class="bi bi-wallet2 text-lg"></i>
                        <span class="font-medium">Meus Gastos</span>
                    </a>
                    @if(Auth::user()->isAdmin() || Auth::user()->member_group_id == 2)
                    <a href="{{ route('credit-logs.resellers') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('credit-logs.resellers') ? 'bg-gradient-to-r from-orange-500 to-orange-600 text-white shadow-md shadow-orange-500/20' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-dark-200 hover:text-gray-900 dark:hover:text-white' }} transition-all duration-200">
                        <i class="bi bi-journal-text text-lg"></i>
                        <span class="font-medium">Logs de Revendas</span>
                    </a>
                    @endif
                </div>

                <!-- Administração -->
                @if(Auth::user()->isAdmin())
                <div>
                    <p class="text-gray-500 dark:text-gray-500 text-xs font-semibold uppercase tracking-wider mb-2 px-3">Administração</p>
                    <a href="{{ route('settings.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('settings.index') ? 'bg-gradient-to-r from-orange-500 to-orange-600 text-white shadow-md shadow-orange-500/20' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-dark-200 hover:text-gray-900 dark:hover:text-white' }} transition-all duration-200">
                        <i class="bi bi-gear text-lg"></i>
                        <span class="font-medium">Configurações</span>
                    </a>
                    <a href="{{ route('settings.ticket-categories.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('settings.ticket-categories.*') ? 'bg-gradient-to-r from-orange-500 to-orange-600 text-white shadow-md shadow-orange-500/20' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-dark-200 hover:text-gray-900 dark:hover:text-white' }} transition-all duration-200">
                        <i class="bi bi-tags text-lg"></i>
                        <span class="font-medium">Categorias Tickets</span>
                    </a>
                    <a href="{{ route('settings.maintenance.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('settings.maintenance.*') ? 'bg-gradient-to-r from-orange-500 to-orange-600 text-white shadow-md shadow-orange-500/20' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-dark-200 hover:text-gray-900 dark:hover:text-white' }} transition-all duration-200">
                        <i class="bi bi-tools text-lg"></i>
                        <span class="font-medium">Manutenção</span>
                    </a>
                    <a href="{{ route('settings.admin.vod-requests.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('settings.admin.vod-requests.*') ? 'bg-gradient-to-r from-orange-500 to-orange-600 text-white shadow-md shadow-orange-500/20' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-dark-200 hover:text-gray-900 dark:hover:text-white' }} transition-all duration-200 justify-between">
                        <div class="flex items-center gap-3">
                            <i class="bi bi-film text-lg"></i>
                            <span class="font-medium">Pedidos VOD</span>
                        </div>
                        @php $pendingVodCount = \App\Models\VodRequest::pending()->count(); @endphp
                        @if($pendingVodCount > 0)
                            <span class="bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full shadow-sm">{{ $pendingVodCount }}</span>
                        @endif
                    </a>
                </div>
                @endif
            </div>
        </nav>

        <!-- User Menu -->
        <div class="p-4 border-t border-gray-200 dark:border-dark-200 space-y-2 shrink-0 bg-gray-50 dark:bg-dark-300">
            <a href="{{ route('profile.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('profile.*') ? 'bg-gradient-to-r from-orange-500 to-orange-600 text-white shadow-md shadow-orange-500/20' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-dark-200 hover:text-gray-900 dark:hover:text-white' }} transition-all duration-200 w-full">
                <i class="bi bi-person-circle text-lg"></i>
                <span class="font-medium">Meu Perfil</span>
            </a>
            
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-red-50 dark:hover:bg-red-500/10 hover:text-red-500 dark:hover:text-red-400 transition-all duration-200 w-full">
                    <i class="bi bi-box-arrow-left text-lg"></i>
                    <span class="font-medium">Sair</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="lg:ml-64 min-h-screen transition-all duration-300">
        <!-- Top Bar -->
        <header class="bg-white dark:bg-dark-300 border-b border-gray-200 dark:border-dark-200 sticky top-0 z-30 transition-colors duration-200">
            <div class="px-4 lg:px-8 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <!-- Toggle Button (Mobile) -->
                        <button onclick="toggleSidebar()" class="lg:hidden text-gray-600 dark:text-gray-300 hover:text-orange-500 transition-colors">
                            <i class="bi bi-list text-2xl"></i>
                        </button>
                        
                        <div>
                            <h2 class="text-gray-900 dark:text-white text-lg lg:text-xl font-bold">Ol&aacute;, {{ Auth::user()->username }}</h2>
                            <p class="text-gray-500 dark:text-gray-400 text-xs lg:text-sm">Painel XUI v2.0</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-2 lg:gap-4">
                        <div class="bg-gradient-to-r from-orange-500 to-orange-600 px-3 py-1.5 lg:px-4 lg:py-2 rounded-lg shadow-lg shadow-orange-500/20">
                            <span class="text-white font-bold text-sm lg:text-base">{{ number_format(Auth::user()->getCredits(), 2, ',', '.') }}</span>
                            <span class="text-orange-100 text-xs lg:text-sm ml-1 hidden sm:inline">Cr&eacute;ditos</span>
                        </div>
                        <div class="w-8 h-8 lg:w-10 lg:h-10 bg-gradient-to-br from-orange-500 to-orange-600 rounded-full flex items-center justify-center text-white font-bold shadow-lg shadow-orange-500/20 text-xs lg:text-base">
                            {{ strtoupper(substr(Auth::user()->username, 0, 2)) }}
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Content Area -->
        <div class="p-4 lg:p-8">
            @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 dark:bg-green-500/10 dark:border-green-500/50 rounded-lg p-4 flex items-center gap-3">
                <i class="bi bi-check-circle-fill text-green-600 dark:text-green-500 text-xl"></i>
                <span class="text-green-700 dark:text-green-400">{{ session('success') }}</span>
            </div>
            @endif

            @if($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 dark:bg-red-500/10 dark:border-red-500/50 rounded-lg p-4">
                <div class="flex items-start gap-3">
                    <i class="bi bi-exclamation-triangle-fill text-red-600 dark:text-red-500 text-xl"></i>
                    <div class="flex-1">
                        @foreach($errors->all() as $error)
                            <p class="text-red-700 dark:text-red-400">{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            @yield('content')
        </div>
    </main>

    @stack('scripts')

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            
            if (sidebar.classList.contains('-translate-x-full')) {
                // Abrir
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
                setTimeout(() => {
                    overlay.classList.remove('opacity-0');
                }, 10);
            } else {
                // Fechar
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('opacity-0');
                setTimeout(() => {
                    overlay.classList.add('hidden');
                }, 300);
            }
        }
    </script>
</body>
</html>
