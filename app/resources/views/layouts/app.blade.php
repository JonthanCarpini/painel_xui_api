<!DOCTYPE html>
<html lang="pt-BR" class="{{ Auth::check() && Auth::user()->getPreference('dark_mode', true) ? 'dark' : '' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', Auth::check() ? Auth::user()->getPreference('panel_name', 'Painel Office') : 'Painel Office')</title>
    
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
    
    <!-- Sidebar -->
    <aside class="fixed left-0 top-0 h-screen w-64 bg-white dark:bg-dark-300 border-r border-gray-200 dark:border-dark-200 z-40 transition-colors duration-200 flex flex-col">
        <!-- Logo -->
        <div class="p-6 border-b border-gray-200 dark:border-dark-200 shrink-0">
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
        </div>

        <!-- Menu -->
        <nav class="flex-1 overflow-y-auto p-4 custom-scrollbar">
            <div class="space-y-1">
                <p class="text-gray-500 dark:text-gray-500 text-xs font-semibold uppercase tracking-wider mb-3 px-3">Menu Principal</p>
                
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('dashboard') ? 'bg-gradient-to-r from-orange-500 to-orange-600 text-white shadow-md shadow-orange-500/20' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-dark-200 hover:text-gray-900 dark:hover:text-white' }} transition-all duration-200">
                    <i class="bi bi-grid text-lg"></i>
                    <span class="font-medium">Dashboard</span>
                </a>
                
                <a href="{{ route('clients.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('clients.*') ? 'bg-gradient-to-r from-orange-500 to-orange-600 text-white shadow-md shadow-orange-500/20' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-dark-200 hover:text-gray-900 dark:hover:text-white' }} transition-all duration-200">
                    <i class="bi bi-people text-lg"></i>
                    <span class="font-medium">Clientes</span>
                </a>
                
                <!-- Submenu Clientes -->
                <div class="ml-6 space-y-1 mb-2 mt-1 border-l-2 border-gray-100 dark:border-dark-200 pl-2">
                    <a href="{{ route('clients.create') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('clients.create') ? 'bg-orange-50 dark:bg-orange-500/10 text-orange-600 dark:text-orange-400 font-medium' : 'text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-dark-200 hover:text-gray-900 dark:hover:text-white' }} transition-all duration-200">
                        <i class="bi bi-plus-circle"></i>
                        <span>Criar Cliente</span>
                    </a>
                    <a href="{{ route('clients.create-trial') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('clients.create-trial') ? 'bg-orange-50 dark:bg-orange-500/10 text-orange-600 dark:text-orange-400 font-medium' : 'text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-dark-200 hover:text-gray-900 dark:hover:text-white' }} transition-all duration-200">
                        <i class="bi bi-clock-history"></i>
                        <span>Criar Teste</span>
                    </a>
                    <a href="{{ route('clients.export') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('clients.export') ? 'bg-orange-50 dark:bg-orange-500/10 text-orange-600 dark:text-orange-400 font-medium' : 'text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-dark-200 hover:text-gray-900 dark:hover:text-white' }} transition-all duration-200">
                        <i class="bi bi-download"></i>
                        <span>Exportar Clientes</span>
                    </a>
                </div>
                
                <a href="{{ route('monitor.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('monitor.*') ? 'bg-gradient-to-r from-orange-500 to-orange-600 text-white shadow-md shadow-orange-500/20' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-dark-200 hover:text-gray-900 dark:hover:text-white' }} transition-all duration-200">
                    <i class="bi bi-broadcast text-lg"></i>
                    <span class="font-medium">Monitoramento</span>
                </a>

                <div class="pt-4">
                    <p class="text-gray-500 dark:text-gray-500 text-xs font-semibold uppercase tracking-wider mb-3 px-3">{{ Auth::user()->isAdmin() ? 'Administra&ccedil;&atilde;o' : 'Minha Equipe' }}</p>
                    <a href="{{ route('resellers.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('resellers.*') ? 'bg-gradient-to-r from-orange-500 to-orange-600 text-white shadow-md shadow-orange-500/20' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-dark-200 hover:text-gray-900 dark:hover:text-white' }} transition-all duration-200">
                        <i class="bi bi-shop text-lg"></i>
                        <span class="font-medium">Revendedores</span>
                    </a>
                    <a href="{{ route('credit-logs.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('credit-logs.*') ? 'bg-gradient-to-r from-orange-500 to-orange-600 text-white shadow-md shadow-orange-500/20' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-dark-200 hover:text-gray-900 dark:hover:text-white' }} transition-all duration-200">
                        <i class="bi bi-clock-history text-lg"></i>
                        <span class="font-medium">Log de Cr&eacute;ditos</span>
                    </a>
                    @if(Auth::user()->isAdmin())
                    <a href="{{ route('settings.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('settings.*') ? 'bg-gradient-to-r from-orange-500 to-orange-600 text-white shadow-md shadow-orange-500/20' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-dark-200 hover:text-gray-900 dark:hover:text-white' }} transition-all duration-200">
                        <i class="bi bi-gear text-lg"></i>
                        <span class="font-medium">Configura&ccedil;&otilde;es</span>
                    </a>
                    @endif
                </div>

                <div class="pt-4">
                    <p class="text-gray-500 dark:text-gray-500 text-xs font-semibold uppercase tracking-wider mb-3 px-3">A&ccedil;&otilde;es R&aacute;pidas</p>
                    <a href="{{ route('clients.create') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-dark-200 hover:text-gray-900 dark:hover:text-white transition-all duration-200">
                        <i class="bi bi-plus-circle text-lg"></i>
                        <span class="font-medium">Criar Cliente</span>
                    </a>
                    <a href="{{ route('clients.create-trial') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-dark-200 hover:text-gray-900 dark:hover:text-white transition-all duration-200">
                        <i class="bi bi-clock-history text-lg"></i>
                        <span class="font-medium">Gerar Teste</span>
                    </a>
                </div>
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
    <main class="ml-64 min-h-screen transition-all duration-200">
        <!-- Top Bar -->
        <header class="bg-white dark:bg-dark-300 border-b border-gray-200 dark:border-dark-200 sticky top-0 z-30 transition-colors duration-200">
            <div class="px-8 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-gray-900 dark:text-white text-xl font-bold">Ol&aacute;, {{ Auth::user()->username }}</h2>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Painel XUI v2.0</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="bg-gradient-to-r from-orange-500 to-orange-600 px-4 py-2 rounded-lg shadow-lg shadow-orange-500/20">
                            <span class="text-white font-bold">{{ number_format(Auth::user()->getCredits(), 2, ',', '.') }}</span>
                            <span class="text-orange-100 text-sm ml-1">Cr&eacute;ditos</span>
                        </div>
                        <div class="w-10 h-10 bg-gradient-to-br from-orange-500 to-orange-600 rounded-full flex items-center justify-center text-white font-bold shadow-lg shadow-orange-500/20">
                            {{ strtoupper(substr(Auth::user()->username, 0, 2)) }}
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Content Area -->
        <div class="p-8">
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
</body>
</html>
