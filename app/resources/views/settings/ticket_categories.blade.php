@extends('layouts.app')

@section('title', 'Categorias de Tickets')

@section('content')
<div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-6 md:mb-8 gap-4">
    <div>
        <h1 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
            <i class="bi bi-tags text-orange-500"></i>
            Categorias de Suporte
        </h1>
        <p class="text-sm md:text-base text-gray-500 dark:text-gray-400 mt-1">Gerencie as categorias e responsáveis pelos tickets de suporte.</p>
    </div>
    <button onclick="document.getElementById('modal-create').classList.remove('hidden')" class="w-full md:w-auto justify-center bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white font-bold py-2.5 px-6 rounded-xl shadow-lg shadow-orange-500/20 transition-all duration-200 flex items-center gap-2">
        <i class="bi bi-plus-lg"></i>
        Nova Categoria
    </button>
</div>

<div class="bg-white dark:bg-dark-300 rounded-xl border border-gray-200 dark:border-dark-200 overflow-hidden shadow-sm">
    <div class="overflow-x-auto custom-scrollbar">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 dark:bg-dark-200 border-b border-gray-200 dark:border-dark-100">
                    <th class="px-4 md:px-6 py-3 md:py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Nome</th>
                    <th class="px-4 md:px-6 py-3 md:py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Responsável</th>
                    <th class="px-4 md:px-6 py-3 md:py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap hidden sm:table-cell">Telefone / WhatsApp</th>
                    <th class="px-4 md:px-6 py-3 md:py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-right whitespace-nowrap">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-dark-100">
                @forelse($categories as $category)
                <tr class="hover:bg-gray-50 dark:hover:bg-dark-200/50 transition-colors">
                    <td class="px-4 md:px-6 py-3 md:py-4 text-sm font-medium text-gray-900 dark:text-white whitespace-nowrap">
                        {{ $category->name }}
                    </td>
                    <td class="px-4 md:px-6 py-3 md:py-4 text-sm text-gray-600 dark:text-gray-300 whitespace-nowrap">
                        {{ $category->responsible }}
                    </td>
                    <td class="px-4 md:px-6 py-3 md:py-4 text-sm text-gray-600 dark:text-gray-300 whitespace-nowrap hidden sm:table-cell">
                        {{ $category->phone }}
                    </td>
                    <td class="px-4 md:px-6 py-3 md:py-4 text-right flex items-center justify-end gap-2 whitespace-nowrap">
                        <button onclick="editCategory({{ $category->id }}, '{{ $category->name }}', '{{ $category->responsible }}', '{{ $category->phone }}')" class="p-2 rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-500/20 transition-colors">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <form action="{{ route('settings.ticket-categories.destroy', $category->id) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir esta categoria?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-2 rounded-lg bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-500/20 transition-colors">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                        Nenhuma categoria cadastrada.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Create -->
<div id="modal-create" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="document.getElementById('modal-create').classList.add('hidden')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white dark:bg-dark-300 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
            <form action="{{ route('settings.ticket-categories.store') }}" method="POST">
                @csrf
                <div class="bg-white dark:bg-dark-300 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4" id="modal-title">Nova Categoria</h3>
                    <div class="space-y-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nome da Categoria</label>
                            <input type="text" name="name" id="name" required class="w-full bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg px-3 py-2 text-gray-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-orange-500">
                        </div>
                        <div>
                            <label for="responsible" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nome do Responsável</label>
                            <input type="text" name="responsible" id="responsible" required class="w-full bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg px-3 py-2 text-gray-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-orange-500">
                        </div>
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Telefone / WhatsApp</label>
                            <input type="text" name="phone" id="phone" required class="w-full bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg px-3 py-2 text-gray-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-orange-500">
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-dark-200 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-orange-600 text-base font-medium text-white hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 sm:ml-3 sm:w-auto sm:text-sm">Salvar</button>
                    <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-dark-100 shadow-sm px-4 py-2 bg-white dark:bg-dark-300 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-dark-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" onclick="document.getElementById('modal-create').classList.add('hidden')">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div id="modal-edit" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="document.getElementById('modal-edit').classList.add('hidden')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white dark:bg-dark-300 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
            <form id="form-edit" action="" method="POST">
                @csrf
                @method('PUT')
                <div class="bg-white dark:bg-dark-300 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4">Editar Categoria</h3>
                    <div class="space-y-4">
                        <div>
                            <label for="edit_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nome da Categoria</label>
                            <input type="text" name="name" id="edit_name" required class="w-full bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg px-3 py-2 text-gray-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-orange-500">
                        </div>
                        <div>
                            <label for="edit_responsible" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nome do Responsável</label>
                            <input type="text" name="responsible" id="edit_responsible" required class="w-full bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg px-3 py-2 text-gray-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-orange-500">
                        </div>
                        <div>
                            <label for="edit_phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Telefone / WhatsApp</label>
                            <input type="text" name="phone" id="edit_phone" required class="w-full bg-gray-50 dark:bg-dark-200 border border-gray-300 dark:border-dark-100 rounded-lg px-3 py-2 text-gray-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-orange-500">
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-dark-200 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-orange-600 text-base font-medium text-white hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 sm:ml-3 sm:w-auto sm:text-sm">Atualizar</button>
                    <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-dark-100 shadow-sm px-4 py-2 bg-white dark:bg-dark-300 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-dark-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" onclick="document.getElementById('modal-edit').classList.add('hidden')">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function editCategory(id, name, responsible, phone) {
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_responsible').value = responsible;
        document.getElementById('edit_phone').value = phone;
        
        let url = "{{ route('settings.ticket-categories.update', ':id') }}";
        url = url.replace(':id', id);
        
        document.getElementById('form-edit').action = url;
        document.getElementById('modal-edit').classList.remove('hidden');
    }
</script>
@endpush
@endsection
