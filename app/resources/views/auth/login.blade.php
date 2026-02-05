<!DOCTYPE html>
<html lang="pt-BR" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Painel Office IPTV</title>
    
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
                            100: '#1F2937',
                            200: '#1A202C',
                            300: '#151A23',
                            400: '#0F1419',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gradient-to-br from-dark-400 to-dark-300 min-h-screen flex items-center justify-center p-4 font-['Inter']">
    
    <div class="w-full max-w-md">
        <div class="bg-dark-300 rounded-2xl border border-dark-200 p-8 shadow-2xl">
            <!-- Logo -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-orange-500 to-orange-600 rounded-2xl mb-4">
                    <i class="bi bi-tv text-white text-4xl"></i>
                </div>
                <h1 class="text-3xl font-bold text-white mb-2">Office IPTV</h1>
                <p class="text-gray-400">Painel de Revenda Profissional</p>
            </div>

            <!-- Alerts -->
            @if($errors->any())
            <div class="mb-6 bg-red-500/10 border border-red-500/50 rounded-lg p-4 flex items-start gap-3">
                <i class="bi bi-exclamation-triangle-fill text-red-500 text-xl"></i>
                <div class="flex-1">
                    @foreach($errors->all() as $error)
                        <p class="text-red-400 text-sm">{{ $error }}</p>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Form -->
            <form action="{{ route('login.post') }}" method="POST" class="space-y-6">
                @csrf
                
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Usuário</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="bi bi-person text-gray-500"></i>
                        </div>
                        <input type="text" 
                               name="username" 
                               value="{{ old('username') }}"
                               required 
                               autofocus
                               class="w-full pl-12 pr-4 py-3 bg-dark-200 border border-dark-100 rounded-lg text-white placeholder-gray-500 focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-500/20 transition-all" 
                               placeholder="Digite seu usuário">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Senha</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="bi bi-lock text-gray-500"></i>
                        </div>
                        <input type="password" 
                               name="password" 
                               required
                               class="w-full pl-12 pr-4 py-3 bg-dark-200 border border-dark-100 rounded-lg text-white placeholder-gray-500 focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-500/20 transition-all" 
                               placeholder="Digite sua senha">
                    </div>
                </div>

                <button type="submit" class="w-full py-3 bg-gradient-to-r from-orange-500 to-orange-600 text-white font-semibold rounded-lg hover:shadow-lg hover:shadow-orange-500/30 transition-all duration-300 flex items-center justify-center gap-2">
                    <i class="bi bi-box-arrow-in-right"></i>
                    Entrar no Sistema
                </button>
            </form>

            <!-- Footer -->
            <div class="mt-8 pt-6 border-t border-dark-200 text-center">
                <p class="text-gray-500 text-sm">
                    Desenvolvido com <i class="bi bi-heart-fill text-red-500"></i> usando Laravel + Tailwind CSS
                </p>
            </div>
        </div>
    </div>

</body>
</html>
