<!DOCTYPE html>
<html lang="pt-BR" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Painel Office IPTV</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        'dark': {
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
        @keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-12px)} }
        @keyframes pulse-glow { 0%,100%{opacity:.4} 50%{opacity:.8} }
        @keyframes slide-up { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:translateY(0)} }
        .float-1 { animation: float 6s ease-in-out infinite; }
        .float-2 { animation: float 8s ease-in-out 1s infinite; }
        .float-3 { animation: float 7s ease-in-out 2s infinite; }
        .glow { animation: pulse-glow 3s ease-in-out infinite; }
        .slide-up { animation: slide-up .6s ease-out both; }
        .slide-up-d1 { animation-delay: .1s; }
        .slide-up-d2 { animation-delay: .2s; }
        .slide-up-d3 { animation-delay: .3s; }
        .slide-up-d4 { animation-delay: .4s; }
        .glass { background: rgba(21,26,35,.7); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); }
    </style>
</head>
<body class="bg-dark-500 min-h-screen font-['Inter'] overflow-hidden">
    
    <div class="min-h-screen flex">
        <!-- Painel Esquerdo — Visual / Branding (hidden no mobile) -->
        <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden bg-gradient-to-br from-dark-400 via-dark-300 to-dark-400">
            <!-- Grid Pattern -->
            <div class="absolute inset-0 opacity-[0.03]" style="background-image: radial-gradient(circle, #f97316 1px, transparent 1px); background-size: 40px 40px;"></div>
            
            <!-- Orbs flutuantes -->
            <div class="absolute top-20 left-20 w-72 h-72 bg-orange-500/10 rounded-full blur-3xl float-1"></div>
            <div class="absolute bottom-32 right-16 w-96 h-96 bg-orange-600/8 rounded-full blur-3xl float-2"></div>
            <div class="absolute top-1/2 left-1/3 w-48 h-48 bg-orange-400/5 rounded-full blur-2xl float-3"></div>
            
            <!-- Conteúdo Central -->
            <div class="relative z-10 flex flex-col items-center justify-center w-full px-16">
                <!-- Logo Grande -->
                <div class="mb-10 slide-up">
                    <div class="relative">
                        <div class="absolute inset-0 bg-orange-500/20 rounded-3xl blur-2xl glow"></div>
                        <div class="relative w-28 h-28 bg-gradient-to-br from-orange-500 to-orange-700 rounded-3xl flex items-center justify-center shadow-2xl shadow-orange-500/30">
                            <i class="bi bi-tv text-white text-5xl"></i>
                        </div>
                    </div>
                </div>
                
                <h1 class="text-5xl font-black text-white mb-4 tracking-tight slide-up slide-up-d1">
                    Office <span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-400 to-orange-600">IPTV</span>
                </h1>
                <p class="text-xl text-gray-400 font-light mb-12 slide-up slide-up-d2">Painel de Revenda Profissional</p>
                
                <!-- Feature cards -->
                <div class="space-y-4 w-full max-w-sm slide-up slide-up-d3">
                    <div class="flex items-center gap-4 p-4 rounded-xl glass border border-white/5">
                        <div class="w-10 h-10 rounded-lg bg-orange-500/10 flex items-center justify-center shrink-0">
                            <i class="bi bi-shield-check text-orange-500 text-lg"></i>
                        </div>
                        <div>
                            <p class="text-white text-sm font-semibold">Gerenciamento Seguro</p>
                            <p class="text-gray-500 text-xs">Controle total sobre suas linhas e revendas</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 p-4 rounded-xl glass border border-white/5">
                        <div class="w-10 h-10 rounded-lg bg-orange-500/10 flex items-center justify-center shrink-0">
                            <i class="bi bi-speedometer2 text-orange-500 text-lg"></i>
                        </div>
                        <div>
                            <p class="text-white text-sm font-semibold">Dashboard em Tempo Real</p>
                            <p class="text-gray-500 text-xs">Monitore conexões e performance ao vivo</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 p-4 rounded-xl glass border border-white/5">
                        <div class="w-10 h-10 rounded-lg bg-orange-500/10 flex items-center justify-center shrink-0">
                            <i class="bi bi-graph-up-arrow text-orange-500 text-lg"></i>
                        </div>
                        <div>
                            <p class="text-white text-sm font-semibold">Relatórios Avançados</p>
                            <p class="text-gray-500 text-xs">Acompanhe vendas, créditos e crescimento</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Linha decorativa na borda direita -->
            <div class="absolute right-0 top-0 bottom-0 w-px bg-gradient-to-b from-transparent via-orange-500/20 to-transparent"></div>
        </div>

        <!-- Painel Direito — Formulário de Login -->
        <div class="w-full lg:w-1/2 flex items-center justify-center p-6 sm:p-12 bg-dark-400">
            <div class="w-full max-w-md">
                
                <!-- Logo Mobile (visível apenas no mobile) -->
                <div class="lg:hidden text-center mb-10 slide-up">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-orange-500 to-orange-700 rounded-2xl mb-4 shadow-xl shadow-orange-500/20">
                        <i class="bi bi-tv text-white text-3xl"></i>
                    </div>
                    <h1 class="text-3xl font-bold text-white">
                        Office <span class="text-orange-500">IPTV</span>
                    </h1>
                </div>

                <!-- Título do Form -->
                <div class="mb-8 slide-up slide-up-d1">
                    <h2 class="text-3xl font-bold text-white mb-2">Bem-vindo de volta</h2>
                    <p class="text-gray-500">Entre com suas credenciais para acessar o painel</p>
                </div>

                <!-- Alerts -->
                @if(session('error'))
                <div class="mb-6 bg-yellow-500/10 border border-yellow-500/30 rounded-xl p-4 flex items-start gap-3 slide-up">
                    <i class="bi bi-exclamation-triangle-fill text-yellow-500 text-lg mt-0.5"></i>
                    <p class="text-yellow-400 text-sm">{{ session('error') }}</p>
                </div>
                @endif

                @if($errors->any())
                <div class="mb-6 bg-red-500/10 border border-red-500/30 rounded-xl p-4 flex items-start gap-3 slide-up">
                    <i class="bi bi-exclamation-circle-fill text-red-500 text-lg mt-0.5"></i>
                    <div class="flex-1">
                        @foreach($errors->all() as $error)
                            <p class="text-red-400 text-sm">{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Form -->
                <form action="{{ route('login.post') }}" method="POST" class="space-y-5">
                    @csrf
                    
                    <div class="slide-up slide-up-d2">
                        <label class="block text-sm font-semibold text-gray-400 mb-2">Usuário</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="bi bi-person text-gray-600 group-focus-within:text-orange-500 transition-colors"></i>
                            </div>
                            <input type="text" 
                                   name="username" 
                                   value="{{ old('username') }}"
                                   required 
                                   autofocus
                                   class="w-full pl-12 pr-4 py-3.5 bg-dark-300 border border-dark-100 rounded-xl text-white placeholder-gray-600 focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-500/20 transition-all text-base" 
                                   placeholder="Digite seu usuário">
                        </div>
                    </div>

                    <div class="slide-up slide-up-d3">
                        <label class="block text-sm font-semibold text-gray-400 mb-2">Senha</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="bi bi-lock text-gray-600 group-focus-within:text-orange-500 transition-colors"></i>
                            </div>
                            <input type="password" 
                                   name="password" 
                                   id="passwordInput"
                                   required
                                   class="w-full pl-12 pr-12 py-3.5 bg-dark-300 border border-dark-100 rounded-xl text-white placeholder-gray-600 focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-500/20 transition-all text-base" 
                                   placeholder="Digite sua senha">
                            <button type="button" 
                                    onclick="togglePassword()" 
                                    class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-600 hover:text-orange-500 transition-colors"
                                    tabindex="-1">
                                <i id="toggleIcon" class="bi bi-eye text-lg"></i>
                            </button>
                        </div>
                    </div>

                    <div class="slide-up slide-up-d4 pt-2">
                        <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-orange-500 to-orange-600 text-white font-bold rounded-xl hover:shadow-xl hover:shadow-orange-500/25 hover:from-orange-600 hover:to-orange-700 active:scale-[0.98] transition-all duration-300 flex items-center justify-center gap-2 text-base">
                            <i class="bi bi-box-arrow-in-right text-lg"></i>
                            Entrar no Painel
                        </button>
                    </div>
                </form>

                <!-- Footer minimalista -->
                <div class="mt-10 text-center slide-up slide-up-d4">
                    <p class="text-gray-600 text-xs">&copy; {{ date('Y') }} Office IPTV &mdash; Todos os direitos reservados</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('passwordInput');
            const icon = document.getElementById('toggleIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        }
    </script>
</body>
</html>
