<div class="px-6 py-4 hover:bg-gray-50 dark:hover:bg-dark-200 transition-colors">
    <div class="flex items-start gap-4">
        <!-- Poster -->
        <div class="w-12 h-16 rounded-lg overflow-hidden bg-gray-200 dark:bg-dark-100 shrink-0">
            <img src="{{ $req->poster_url }}" alt="{{ $req->title }}" class="w-full h-full object-cover" loading="lazy">
        </div>

        <!-- Info -->
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 mb-1">
                <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $req->type === 'movie' ? 'bg-purple-100 dark:bg-purple-500/20 text-purple-700 dark:text-purple-400' : 'bg-blue-100 dark:bg-blue-500/20 text-blue-700 dark:text-blue-400' }}">
                    {{ $req->type === 'movie' ? 'Filme' : 'Série' }}
                </span>
                @if($req->vote_average > 0)
                <span class="text-xs text-yellow-500 flex items-center gap-1">
                    <i class="bi bi-star-fill"></i> {{ number_format($req->vote_average, 1) }}
                </span>
                @endif
            </div>
            <h4 class="text-sm font-bold text-gray-900 dark:text-white">{{ $req->title }}</h4>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                {{ $req->release_date ? substr($req->release_date, 0, 4) : '' }} &middot; Pedido em {{ $req->created_at->format('d/m/Y H:i') }}
            </p>

            @if($showAdminResponse && ($req->admin_note || $req->resolved_at))
            <div class="mt-3 p-3 rounded-lg {{ $req->status === 'completed' ? 'bg-green-50 dark:bg-green-500/5 border border-green-200 dark:border-green-500/20' : 'bg-red-50 dark:bg-red-500/5 border border-red-200 dark:border-red-500/20' }}">
                @if($req->admin_note)
                <div class="mb-2">
                    <p class="text-xs font-semibold {{ $req->status === 'completed' ? 'text-green-700 dark:text-green-400' : 'text-red-700 dark:text-red-400' }} uppercase mb-1">Nota do Admin:</p>
                    <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $req->admin_note }}</p>
                </div>
                @endif
                <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-500 dark:text-gray-400">
                    @if($req->resolved_at)
                    <span class="flex items-center gap-1">
                        <i class="bi bi-calendar-check"></i> Resolvido em: {{ $req->resolved_at->format('d/m/Y H:i') }}
                    </span>
                    @endif
                    @if($req->resolver)
                    <span class="flex items-center gap-1">
                        <i class="bi bi-person"></i> Por: {{ $req->resolver->username }}
                    </span>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Status Badge -->
        <div class="shrink-0">
            @if($req->status === 'pending')
                <span class="inline-flex items-center gap-1 text-xs font-medium px-3 py-1.5 rounded-full bg-yellow-100 dark:bg-yellow-500/20 text-yellow-700 dark:text-yellow-400">
                    <i class="bi bi-clock"></i> Pendente
                </span>
            @elseif($req->status === 'completed')
                <span class="inline-flex items-center gap-1 text-xs font-medium px-3 py-1.5 rounded-full bg-green-100 dark:bg-green-500/20 text-green-700 dark:text-green-400">
                    <i class="bi bi-check-circle"></i> Concluído
                </span>
            @else
                <span class="inline-flex items-center gap-1 text-xs font-medium px-3 py-1.5 rounded-full bg-red-100 dark:bg-red-500/20 text-red-700 dark:text-red-400">
                    <i class="bi bi-x-circle"></i> Recusado
                </span>
            @endif
        </div>
    </div>
</div>
