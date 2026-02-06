<!DOCTYPE html>
<html lang="pt-BR" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acesso Indisponível</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="h-full bg-gray-100 flex items-center justify-center p-4">
    <div class="max-w-md w-full text-center">
        <div class="bg-white rounded-2xl shadow-xl p-8 md:p-12">
            <div class="mb-6">
                <div class="w-20 h-20 bg-orange-100 rounded-full flex items-center justify-center mx-auto">
                    <i class="bi bi-cone-striped text-4xl text-orange-500"></i>
                </div>
            </div>
            
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Painel em Manutenção</h1>
            
            <p class="text-gray-500 mb-8">
                O acesso ao painel de revendas está temporariamente suspenso para atualizações programadas. Por favor, tente novamente mais tarde.
            </p>
            
            <div class="space-y-4">
                <a href="{{ route('login') }}" class="block w-full py-3 px-4 bg-gray-900 hover:bg-gray-800 text-white rounded-lg font-medium transition-colors">
                    <i class="bi bi-arrow-left me-2"></i>
                    Voltar para o Login
                </a>
            </div>
            
            <div class="mt-8 pt-8 border-t border-gray-100">
                <p class="text-xs text-gray-400">
                    &copy; {{ date('Y') }} Sistema de Gerenciamento. Todos os direitos reservados.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
