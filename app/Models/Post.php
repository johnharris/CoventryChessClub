<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable([
    'user_id', 'title', 'slug', 'type', 'excerpt', 'body',
    'featured_image_id', 'featured_image_caption',
    'fen', 'orientation', 'side_to_move', 'caption', 'solution',
    'pgn', 'white_player', 'black_player', 'result', 'event', 'played_on',
    'is_published', 'is_featured', 'published_at',
])]
class Post extends Model
{
    /** An ordinary article with no chess content. */
    public const TYPE_GENERAL = 'general';

    /** A single diagram built from a FEN. */
    public const TYPE_POSITION = 'position';

    /** An annotated game built from a PGN. */
    public const TYPE_GAME = 'game';

    public const TYPES = [
        self::TYPE_GENERAL => 'General post',
        self::TYPE_POSITION => 'Chess position (FEN)',
        self::TYPE_GAME => 'Annotated game (PGN)',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
            'published_at' => 'datetime',
            'played_on' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The lead photograph, shown above the article and used as the listing
     * thumbnail. Optional: chess posts usually lead with a board instead.
     */
    public function featuredImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'featured_image_id');
    }

    /* ---------------------------------------------------------------------
     | Scopes
     * ------------------------------------------------------------------ */

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)
            ->where(function (Builder $q) {
                $q->whereNull('published_at')->orWhere('published_at', '<=', now());
            });
    }

    public function scopeNewestFirst(Builder $query): Builder
    {
        return $query->orderByRaw('COALESCE(published_at, created_at) DESC');
    }

    public function scopeOfType(Builder $query, ?string $type): Builder
    {
        return $type ? $query->where('type', $type) : $query;
    }

    /**
     * Free text search across titles, bodies and player names.
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        $like = '%'.str_replace('%', '\%', $term).'%';

        return $query->where(function (Builder $q) use ($like) {
            $q->where('title', 'like', $like)
                ->orWhere('excerpt', 'like', $like)
                ->orWhere('body', 'like', $like)
                ->orWhere('white_player', 'like', $like)
                ->orWhere('black_player', 'like', $like)
                ->orWhere('event', 'like', $like);
        });
    }

    /* ---------------------------------------------------------------------
     | Helpers
     * ------------------------------------------------------------------ */

    public function isChessPost(): bool
    {
        return in_array($this->type, [self::TYPE_POSITION, self::TYPE_GAME], true);
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? 'Post';
    }

    /**
     * Short badge text used on cards.
     */
    public function badge(): string
    {
        return match ($this->type) {
            self::TYPE_POSITION => 'Position',
            self::TYPE_GAME => 'Annotated game',
            default => 'News',
        };
    }

    public function displayDate(): string
    {
        return ($this->published_at ?? $this->created_at)->format('j F Y');
    }

    /**
     * Whether a listing card should lead with a photograph rather than a board.
     */
    public function hasFeaturedImage(): bool
    {
        return $this->featured_image_id !== null && $this->featuredImage !== null;
    }

    /**
     * "White 1-0 Black" style line for game posts.
     */
    public function matchup(): ?string
    {
        if ($this->type !== self::TYPE_GAME) {
            return null;
        }

        $white = $this->white_player ?: 'White';
        $black = $this->black_player ?: 'Black';
        $result = $this->result && $this->result !== '*' ? $this->result : 'vs';

        return "{$white} {$result} {$black}";
    }

    /**
     * Plain-text preview used on index cards and meta descriptions.
     */
    public function preview(int $words = 32): string
    {
        if (filled($this->excerpt)) {
            return $this->excerpt;
        }

        $source = $this->caption ?: $this->body ?: '';

        // Strip the most common Markdown decorations before truncating.
        $text = preg_replace('/[#>*_`~\[\]()!]/', '', (string) $source) ?? '';

        return Str::words(trim(preg_replace('/\s+/', ' ', $text) ?? ''), $words);
    }

    /**
     * Build a unique slug from a title, e.g. "Summer Cup 2026" -> "summer-cup-2026".
     */
    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'post';
        $slug = $base;
        $n = 2;

        while (static::where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = "{$base}-{$n}";
            $n++;
        }

        return $slug;
    }
}
