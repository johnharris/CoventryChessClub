<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Support\ImageProcessor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Uploading and managing the images members use in posts.
 *
 * Uploads arrive by fetch from the post editor and return JSON, so that a
 * member can drop a photograph into an article without losing what they have
 * already typed.
 */
class MediaController extends Controller
{
    public function __construct(private readonly ImageProcessor $images) {}

    /**
     * The image library: everything a member may use, newest first.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        return view('members.media.index', [
            'media' => Media::query()
                ->with('user')
                ->when(! $user->isAdmin(), fn ($q) => $q->where('user_id', $user->id))
                ->latest()
                ->paginate(24),
            'diskUsage' => Media::sum('size'),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'image' => [
                'required',
                'file',
                'image',
                'mimetypes:'.implode(',', ImageProcessor::ALLOWED_MIMES),
                // 12MB: generous enough for a modern phone photograph, since
                // the stored copies are far smaller than whatever arrives.
                'max:12288',
            ],
            'alt_text' => ['nullable', 'string', 'max:255'],
        ], [
            'image.image' => 'That file is not an image.',
            'image.mimetypes' => 'Please upload a JPEG, PNG, WebP or GIF image.',
            'image.max' => 'That image is larger than 12MB. Please choose a smaller one.',
        ]);

        try {
            $media = $this->processAndStore(
                $request->file('image'),
                $request->user()->id,
                $request->string('alt_text')->trim()->value() ?: null
            );
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'That image could not be processed. Please try a different file.',
            ], 422);
        }

        return response()->json([
            'id' => $media->id,
            'url' => $media->url(),
            'thumb_url' => $media->thumbUrl(),
            'markdown' => $media->markdown(),
            'alt_text' => $media->alt_text,
            'original_name' => $media->original_name,
            'size' => $media->humanSize(),
            'width' => $media->width,
            'height' => $media->height,
        ], 201);
    }

    public function update(Request $request, Media $medium): RedirectResponse
    {
        $this->authoriseFor($request, $medium);

        $data = $request->validate([
            'alt_text' => ['nullable', 'string', 'max:255'],
        ]);

        $medium->update($data);

        return back()->with('status', 'Image description updated.');
    }

    public function destroy(Request $request, Media $medium): RedirectResponse
    {
        $this->authoriseFor($request, $medium);

        $usedBy = $medium->posts()->count();

        $medium->delete();

        return back()->with('status', $usedBy > 0
            ? 'Image deleted. It was the lead photograph on '.$usedBy.' '.Str::plural('post', $usedBy).', which will now show no photograph.'
            : 'Image deleted.');
    }

    /**
     * Members manage their own uploads; administrators manage everyone's.
     */
    private function authoriseFor(Request $request, Media $medium): void
    {
        $user = $request->user();

        abort_unless($user->isAdmin() || $medium->user_id === $user->id, 403);
    }

    /**
     * Decodes, resizes and re-encodes an upload into the three stored sizes.
     *
     * Nothing from the uploaded file is copied verbatim: every stored byte is
     * produced by re-encoding decoded pixels, so a file that is both a valid
     * image and something else cannot survive.
     */
    public function processAndStore(UploadedFile $file, ?int $userId, ?string $altText = null): Media
    {
        $disk = Storage::disk('public');
        $directory = 'uploads/'.now()->format('Y/m');
        $stem = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'image';
        $stem = Str::limit($stem, 60, '').'-'.Str::lower(Str::random(6));

        $source = $this->images->read($file);
        $transparent = $this->images->hasTransparency($source, $file->getMimeType());

        try {
            // The retained original, capped so that a 12-megapixel photograph
            // does not sit on the hosting account at full size for ever.
            $original = $this->images->resizeToFit($source, ImageProcessor::MAX_ORIGINAL, ImageProcessor::MAX_ORIGINAL);
            [$bytes, $extension, $mime] = $this->images->encode($original, $transparent, 86);
            $path = $directory.'/'.$stem.'.'.$extension;
            $disk->put($path, $bytes);
            $width = imagesx($original);
            $height = imagesy($original);
            imagedestroy($original);

            // The copy used in post bodies.
            $display = $this->images->resizeToFit($source, ImageProcessor::DISPLAY_WIDTH, ImageProcessor::DISPLAY_WIDTH * 3);
            [$displayBytes, $displayExtension] = $this->images->encode($display, $transparent);
            $displayPath = $directory.'/'.$stem.'-display.'.$displayExtension;
            $disk->put($displayPath, $displayBytes);
            imagedestroy($display);

            // The cropped thumbnail used on listing cards.
            $thumb = $this->images->cropToFill($source, ImageProcessor::THUMB_WIDTH, ImageProcessor::THUMB_HEIGHT);
            [$thumbBytes, $thumbExtension] = $this->images->encode($thumb, $transparent);
            $thumbPath = $directory.'/'.$stem.'-thumb.'.$thumbExtension;
            $disk->put($thumbPath, $thumbBytes);
            imagedestroy($thumb);
        } finally {
            imagedestroy($source);
        }

        return Media::create([
            'user_id' => $userId,
            'path' => $path,
            'display_path' => $displayPath,
            'thumb_path' => $thumbPath,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $mime,
            'size' => $disk->size($path) + $disk->size($displayPath) + $disk->size($thumbPath),
            'width' => $width,
            'height' => $height,
            'alt_text' => $altText,
        ]);
    }
}
