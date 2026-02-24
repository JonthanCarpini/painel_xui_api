<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\HelpCategory;
use App\Models\HelpPost;
use Illuminate\Http\Request;

class HelpController extends Controller
{
    public function index(Request $request)
    {
        if (AppSetting::get('module_help_enabled', '0') !== '1') {
            abort(404);
        }

        $categories = HelpCategory::active()
            ->ordered()
            ->withCount(['activePosts as posts_count'])
            ->get();

        $query = HelpPost::with('category', 'media')
            ->active()
            ->whereHas('category', fn ($q) => $q->active())
            ->ordered();

        if ($request->filled('category')) {
            $query->where('help_category_id', $request->category);
        }

        if ($request->filled('q')) {
            $query->search($request->q);
        }

        $posts = $query->paginate(12)->withQueryString();

        return view('help.index', compact('categories', 'posts'));
    }

    public function show(HelpPost $post)
    {
        if (AppSetting::get('module_help_enabled', '0') !== '1') {
            abort(404);
        }

        if (!$post->is_active || !$post->category->is_active) {
            abort(404);
        }

        $post->load('category', 'media');

        $relatedPosts = HelpPost::active()
            ->where('help_category_id', $post->help_category_id)
            ->where('id', '!=', $post->id)
            ->ordered()
            ->limit(5)
            ->get();

        return view('help.show', compact('post', 'relatedPosts'));
    }
}
