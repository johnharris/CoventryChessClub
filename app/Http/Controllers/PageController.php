<?php

namespace App\Http\Controllers;

use App\Models\HomepageSetting;
use App\Models\Page;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    /* =====================================================================
     | Public
     * ================================================================== */

    /**
     * The home page: a featured post, the latest news and the newest chess content.
     */
    public function home(): View
    {
        $featured = Post::with(['user', 'featuredImage'])->published()->where('is_featured', true)
            ->newestFirst()->first()
            ?? Post::with(['user', 'featuredImage'])->published()->newestFirst()->first();

        return view('home', [
            'homepageSettings' => HomepageSetting::current(),
            'featured' => $featured,
            'latest' => Post::with(['user', 'featuredImage'])->published()
                ->when($featured, fn ($q) => $q->whereKeyNot($featured->id))
                ->newestFirst()->limit(4)->get(),
            'chessPosts' => Post::with(['user', 'featuredImage'])->published()
                ->whereIn('type', [Post::TYPE_POSITION, Post::TYPE_GAME])
                ->when($featured, fn ($q) => $q->whereKeyNot($featured->id))
                ->newestFirst()->limit(3)->get(),
        ]);
    }

    public function show(Page $page): View
    {
        $administratorPreview = ! $page->is_published
            && auth()->check()
            && auth()->user()->is_active
            && auth()->user()->isAdmin();

        abort_unless($page->is_published || $administratorPreview, 404);

        return view('pages.show', [
            'page' => $page,
            'administratorPreview' => $administratorPreview,
        ]);
    }

    /* =====================================================================
     | Admin
     * ================================================================== */

    public function index(): View
    {
        return view('members.admin.pages.index', [
            'pages' => Page::orderBy('nav_order')->orderBy('title')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('members.admin.pages.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = Page::uniqueSlug($data['title']);

        $page = Page::create($data);

        return redirect()->route('members.pages.edit', $page)
            ->with('status', 'Page created.');
    }

    public function edit(Page $page): View
    {
        return view('members.admin.pages.edit', ['page' => $page]);
    }

    public function update(Request $request, Page $page): RedirectResponse
    {
        $data = $this->validated($request);

        if ($data['title'] !== $page->title) {
            $data['slug'] = Page::uniqueSlug($data['title'], $page->id);
        }

        $page->update($data);

        return redirect()->route('members.pages.edit', $page)
            ->with('status', 'Page saved.');
    }

    public function destroy(Page $page): RedirectResponse
    {
        $page->delete();

        return redirect()->route('members.pages.index')->with('status', 'Page deleted.');
    }

    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'body' => ['nullable', 'string', 'max:65000'],
            'nav_order' => ['nullable', 'integer', 'min:0', 'max:999'],
        ]);

        $data['is_published'] = $request->boolean('is_published');
        $data['show_in_nav'] = $request->boolean('show_in_nav');
        $data['nav_order'] = $data['nav_order'] ?? 0;

        return $data;
    }
}
