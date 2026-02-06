<?php

namespace App\Http\Controllers;

use App\Models\TicketCategory;
use Illuminate\Http\Request;

class TicketCategoryController extends Controller
{
    public function index()
    {
        $categories = TicketCategory::all();
        return view('settings.ticket_categories', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'responsible' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
        ]);

        TicketCategory::create($request->all());

        return redirect()->back()->with('success', 'Categoria criada com sucesso!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'responsible' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
        ]);

        $category = TicketCategory::findOrFail($id);
        $category->update($request->all());

        return redirect()->back()->with('success', 'Categoria atualizada com sucesso!');
    }

    public function destroy($id)
    {
        $category = TicketCategory::findOrFail($id);
        
        // Verificar se tem tickets vinculados antes de excluir? 
        // Por enquanto vamos permitir e o ticket fica sem categoria ou tratamos isso.
        // Melhor impedir se tiver tickets.
        if ($category->extras()->count() > 0) {
            return redirect()->back()->with('error', 'Não é possível excluir esta categoria pois existem tickets vinculados a ela.');
        }

        $category->delete();

        return redirect()->back()->with('success', 'Categoria excluída com sucesso!');
    }
}
