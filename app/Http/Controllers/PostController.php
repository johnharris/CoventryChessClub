<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Rules\ValidFen;
use App\Rules\ValidPgn;
use App\Support\ChessNotation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PostController extends Controller
{
    /* =====================================================================
     | Public blog
     * ================================================================== */

    /**
     * The blog index, with an optional type filter and free text search.
     */
    public function index(Request $request): View
    {
        $type = $request->query('type');
        $type = array_key_exists((string) $type, Post::TYPES) ? $type : null;

        $posts = Post::query()
            ->with(['user', 'featuredImage'])
            ->published()
            ->ofType($type)
            ->search($request->query('q'))
            ->newestFirst()
            ->paginate(9)
            ->withQueryString();

        return view('posts.index', [
            'posts' => $posts,
            'activeType' => $type,
            'query' => $request->query('q'),
        ]);
    }

    /**
     * A single post. Drafts are visible only to their author or an admin.
     */
    public function show(Request $request, Post $post): View
    {
        if (! $post->is_published || ($post->published_at && $post->published_at->isFuture())) {
            $user = $request->user();

            abort_unless($user && $user->ownsOrAdmin($post), 404);
        }

        $post->load('user');

        return view('posts.show', [
            'post' => $post,
            'game' => $post->type === Post::TYPE_GAME
                ? ChessNotation::parseGame((string) $post->pgn)
                : null,
            'related' => Post::query()
                ->published()
                ->whereKeyNot($post->id)
                ->when($post->isChessPost(), fn ($q) => $q->whereIn('type', [
                    Post::TYPE_POSITION, Post::TYPE_GAME,
                ]))
                ->newestFirst()
                ->limit(3)
                ->get(),
        ]);
    }

    /* =====================================================================
     | Member area
     * ================================================================== */

    /**
     * A member sees their own posts; an admin sees everybody's.
     */
    public function manage(Request $request): View
    {
        $user = $request->user();

        $posts = Post::query()
            ->with(['user', 'featuredImage'])
            ->when(! $user->isAdmin(), fn ($q) => $q->where('user_id', $user->id))
            ->search($request->query('q'))
            ->orderByDesc('updated_at')
            ->paginate(15)
            ->withQueryString();

        return view('members.posts.index', [
            'posts' => $posts,
            'query' => $request->query('q'),
        ]);
    }

    public function create(Request $request): View
    {
        $type = $request->query('type');

        return view('members.posts.create', [
            'type' => array_key_exists((string) $type, Post::TYPES) ? $type : Post::TYPE_GENERAL,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $data['user_id'] = $request->user()->id;
        $data['slug'] = Post::uniqueSlug($data['title']);

        $post = Post::create($this->withDerivedFields($data));

        return redirect()->route('members.posts.edit', $post)
            ->with('status', $post->is_published ? 'Post published.' : 'Draft saved.');
    }

    public function edit(Request $request, Post $post): View
    {
        Gate::authorize('update', $post);

        return view('members.posts.edit', ['post' => $post]);
    }

    public function update(Request $request, Post $post): RedirectResponse
    {
        Gate::authorize('update', $post);

        $data = $this->validated($request, $post);

        // Only re-slug when the title actually changed, so existing links survive.
        if ($data['title'] !== $post->title) {
            $data['slug'] = Post::uniqueSlug($data['title'], $post->id);
        }

        $post->update($this->withDerivedFields($data));

        return redirect()->route('members.posts.edit', $post)
            ->with('status', 'Changes saved.');
    }

    public function destroy(Request $request, Post $post): RedirectResponse
    {
        Gate::authorize('delete', $post);

        $post->delete();

        return redirect()->route('members.posts.index')
            ->with('status', 'Post deleted.');
    }

    /* =====================================================================
     | Validation
     * ================================================================== */

    /**
     * Rules depend on the post type: a FEN is required for a position, a PGN for
     * a game, and neither applies to a general post.
     */
    protected function validated(Request $request, ?Post $post = null): array
    {
        $type = $request->input('type', Post::TYPE_GENERAL);

        $rules = [
            'title' => ['required', 'string', 'max:180'],
            'type' => ['required', Rule::in(array_keys(Post::TYPES))],
            'excerpt' => ['nullable', 'string', 'max:400'],
            'body' => ['nullable', 'string', 'max:65000'],
            'published_at' => ['nullable', 'date'],
            'orientation' => ['nullable', Rule::in(['white', 'black'])],
            // exists rather than a blind integer: a member must not be able to
            // attach an image id that was never uploaded.
            'featured_image_id' => ['nullable', 'integer', 'exists:media,id'],
            'featured_image_caption' => ['nullable', 'string', 'max:255'],
        ];

        if ($type === Post::TYPE_POSITION) {
            $rules['fen'] = ['required', 'string', 'max:120', new ValidFen];
            $rules['caption'] = ['nullable', 'string', 'max:180'];
            $rules['solution'] = ['nullable', 'string', 'max:255'];
        }

        if ($type === Post::TYPE_GAME) {
            $rules['pgn'] = ['required', 'string', 'max:200000', new ValidPgn];
            $rules['white_player'] = ['nullable', 'string', 'max:120'];
            $rules['black_player'] = ['nullable', 'string', 'max:120'];
            $rules['result'] = ['nullable', Rule::in(['1-0', '0-1', '1/2-1/2', '*'])];
            $rules['event'] = ['nullable', 'string', 'max:180'];
            $rules['played_on'] = ['nullable', 'date'];
        }

        $data = $request->validate($rules);

        $data['is_published'] = $request->boolean('is_published');
        // Only admins may pin a post to the top of the home page.
        $data['is_featured'] = $request->user()->isAdmin()
            ? $request->boolean('is_featured')
            : (bool) ($post?->is_featured ?? false);
        $data['orientation'] = $data['orientation'] ?? 'white';

        return $data;
    }

    /**
     * Fill in what we can work out for the author: the publication timestamp,
     * the side to move (from the FEN), and any PGN headers left blank.
     */
    protected function withDerivedFields(array $data): array
    {
        if (($data['is_published'] ?? false) && blank($data['published_at'] ?? null)) {
            $data['published_at'] = now();
        }

        if (($data['type'] ?? null) === Post::TYPE_POSITION && filled($data['fen'] ?? null)) {
            $data['fen'] = ChessNotation::normaliseFen($data['fen']);
            $data['side_to_move'] = ChessNotation::sideToMove($data['fen']);
        }

        if (($data['type'] ?? null) === Post::TYPE_GAME && filled($data['pgn'] ?? null)) {
            $headers = ChessNotation::pgnHeaders($data['pgn']);

            // The member may leave these blank, in which case we take them from
            // the PGN's own header tags.
            $data['white_player'] = ($data['white_player'] ?? null) ?: ($headers['White'] ?? null);
            $data['black_player'] = ($data['black_player'] ?? null) ?: ($headers['Black'] ?? null);
            $data['result'] = ($data['result'] ?? null) ?: ($headers['Result'] ?? null);
            $data['event'] = ($data['event'] ?? null) ?: ($headers['Event'] ?? null);

            if (blank($data['played_on'] ?? null) && filled($headers['Date'] ?? null)) {
                $data['played_on'] = ChessNotation::pgnDate($headers['Date']);
            }
        }

        return $data;
    }
}
