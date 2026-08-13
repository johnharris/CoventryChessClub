<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * An image uploaded by a member for use in a post.
 *
 * Three versions of every upload are kept: a capped original, a display copy
 * sized for a post body, and a cropped thumbnail for listings. Serving a 1200px
 * copy instead of a 12-megapixel phone photograph is the difference between a
 * club site that loads on a phone and one that does not.
 */
#[Fillable([
    'user_id', 'path', 'display_path', 'thumb_path', 'original_name',
    'mime_type', 'size', 'width', 'height', 'alt_text',
])]
class Media extends Model
{
    protected $table = 'media';

    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class, 'featured_image_id');
    }

    /* ---------------------------------------------------------------------
     | URLs
     |
     | `display` is what belongs in a post body, `thumb` in a listing, and
     | `original` only where someone genuinely wants the full-size image.
     * ------------------------------------------------------------------ */

    public function url(): string
    {
        return Storage::disk('public')->url($this->display_path ?: $this->path);
    }

    public function thumbUrl(): string
    {
        return Storage::disk('public')->url($this->thumb_path ?: $this->display_path ?: $this->path);
    }

    public function originalUrl(): string
    {
        return Storage::disk('public')->url($this->path);
    }

    /**
     * A root-relative path to the display copy, e.g. /storage/uploads/2026/08/x.jpg
     *
     * This is deliberately not a full URL. The path is written into the text of
     * a post and stored for good, so an absolute address would bake today's
     * hostname into the article: every photograph inserted before the club moves
     * to its own domain would then point at the old host and stop loading. A
     * relative path follows the site wherever it goes.
     */
    public function relativeUrl(): string
    {
        $url = $this->url();
        $path = parse_url($url, PHP_URL_PATH);

        return $path ?: $url;
    }

    /**
     * The Markdown a member can paste into a post body to place this image.
     */
    public function markdown(): string
    {
        $alt = $this->alt_text ?: pathinfo($this->original_name, PATHINFO_FILENAME);

        return '!['.$alt.']('.$this->relativeUrl().')';
    }

    public function humanSize(): string
    {
        $kb = $this->size / 1024;

        return $kb < 1024
            ? round($kb).' KB'
            : round($kb / 1024, 1).' MB';
    }

    /**
     * Removes every stored version of the image from disk.
     *
     * Called when the record is deleted so that an image removed from the
     * library does not silently continue to occupy the hosting allowance.
     */
    protected static function booted(): void
    {
        static::deleting(function (self $media) {
            $disk = Storage::disk('public');

            foreach (array_filter([$media->path, $media->display_path, $media->thumb_path]) as $path) {
                if ($disk->exists($path)) {
                    $disk->delete($path);
                }
            }
        });
    }
}
