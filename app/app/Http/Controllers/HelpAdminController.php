<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\HelpCategory;
use App\Models\HelpPost;
use App\Models\HelpPostMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HelpAdminController extends Controller
{
    // =========================================================================
    // Categorias
    // =========================================================================

    public function categories()
    {
        $categories = HelpCategory::ordered()
            ->withCount('posts')
            ->get();

        return view('help.admin.categories', compact('categories'));
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'icon'       => 'nullable|string|max:50',
            'color'      => 'nullable|string|max:20',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['icon']       = $validated['icon'] ?? 'bi-folder';
        $validated['color']      = $validated['color'] ?? '#f97316';
        $validated['sort_order'] = $validated['sort_order'] ?? HelpCategory::max('sort_order') + 1;

        HelpCategory::create($validated);

        return redirect()->route('help.admin.categories')
            ->with('success', 'Categoria criada com sucesso.');
    }

    public function updateCategory(Request $request, HelpCategory $category)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'icon'       => 'nullable|string|max:50',
            'color'      => 'nullable|string|max:20',
            'sort_order' => 'nullable|integer',
            'is_active'  => 'nullable',
        ]);

        $validated['is_active'] = $request->has('is_active') ? true : false;

        $category->update($validated);

        return redirect()->route('help.admin.categories')
            ->with('success', 'Categoria atualizada com sucesso.');
    }

    public function destroyCategory(HelpCategory $category)
    {
        $category->delete();

        return redirect()->route('help.admin.categories')
            ->with('success', 'Categoria removida com sucesso.');
    }

    // =========================================================================
    // Posts
    // =========================================================================

    public function posts(Request $request)
    {
        $query = HelpPost::with('category')->ordered();

        if ($request->filled('category_id')) {
            $query->where('help_category_id', $request->category_id);
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $posts      = $query->paginate(20)->withQueryString();
        $categories = HelpCategory::ordered()->get();

        return view('help.admin.posts', compact('posts', 'categories'));
    }

    public function createPost()
    {
        $categories = HelpCategory::active()->ordered()->get();

        return view('help.admin.post-form', [
            'post'       => null,
            'categories' => $categories,
        ]);
    }

    public function storePost(Request $request)
    {
        $validated = $request->validate([
            'help_category_id' => 'required|exists:help_categories,id',
            'title'            => 'required|string|max:255',
            'content'          => 'required|string',
            'sort_order'       => 'nullable|integer',
            'is_active'        => 'nullable',
            'media'            => 'nullable|array',
            'media.*.type'     => 'required_with:media|in:image,video',
            'media.*.url'      => 'required_with:media|url|max:2048',
            'media.*.caption'  => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($validated, $request) {
            $post = HelpPost::create([
                'help_category_id' => $validated['help_category_id'],
                'title'            => $validated['title'],
                'content'          => $validated['content'],
                'sort_order'       => $validated['sort_order'] ?? HelpPost::max('sort_order') + 1,
                'is_active'        => $request->has('is_active'),
            ]);

            $this->syncMedia($post, $request->input('media', []));
        });

        return redirect()->route('help.admin.posts')
            ->with('success', 'Post criado com sucesso.');
    }

    public function editPost(HelpPost $post)
    {
        $post->load('media');
        $categories = HelpCategory::active()->ordered()->get();

        return view('help.admin.post-form', compact('post', 'categories'));
    }

    public function updatePost(Request $request, HelpPost $post)
    {
        $validated = $request->validate([
            'help_category_id' => 'required|exists:help_categories,id',
            'title'            => 'required|string|max:255',
            'content'          => 'required|string',
            'sort_order'       => 'nullable|integer',
            'is_active'        => 'nullable',
            'media'            => 'nullable|array',
            'media.*.type'     => 'required_with:media|in:image,video',
            'media.*.url'      => 'required_with:media|url|max:2048',
            'media.*.caption'  => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($validated, $request, $post) {
            $post->update([
                'help_category_id' => $validated['help_category_id'],
                'title'            => $validated['title'],
                'content'          => $validated['content'],
                'sort_order'       => $validated['sort_order'] ?? $post->sort_order,
                'is_active'        => $request->has('is_active'),
            ]);

            $this->syncMedia($post, $request->input('media', []));
        });

        return redirect()->route('help.admin.posts')
            ->with('success', 'Post atualizado com sucesso.');
    }

    public function destroyPost(HelpPost $post)
    {
        $post->delete();

        return redirect()->route('help.admin.posts')
            ->with('success', 'Post removido com sucesso.');
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    private function syncMedia(HelpPost $post, array $mediaItems): void
    {
        $post->media()->delete();

        foreach (array_values(array_filter($mediaItems)) as $i => $item) {
            if (empty($item['url'])) continue;

            $post->media()->create([
                'type'       => $item['type'] ?? 'image',
                'url'        => $item['url'],
                'caption'    => $item['caption'] ?? null,
                'sort_order' => $i,
            ]);
        }
    }
}
