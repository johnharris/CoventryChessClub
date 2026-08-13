<?php

use App\Models\Media;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

/**
 * Builds a real image file on disk rather than a fake placeholder, because the
 * upload path decodes, resizes and re-encodes: a stub file would test nothing.
 */
function realImage(int $width = 1800, int $height = 1200, string $format = 'jpg'): UploadedFile
{
    $image = imagecreatetruecolor($width, $height);

    // Some visible content, so the resize is doing measurable work.
    imagefill($image, 0, 0, imagecolorallocate($image, 200, 180, 140));
    imagefilledrectangle($image, 0, 0, (int) ($width / 2), (int) ($height / 2), imagecolorallocate($image, 60, 90, 70));

    $path = tempnam(sys_get_temp_dir(), 'img').'.'.$format;

    match ($format) {
        'png' => imagepng($image, $path),
        'webp' => imagewebp($image, $path),
        'gif' => imagegif($image, $path),
        default => imagejpeg($image, $path, 92),
    };

    imagedestroy($image);

    return new UploadedFile($path, 'club photo.'.$format, match ($format) {
        'png' => 'image/png',
        'webp' => 'image/webp',
        'gif' => 'image/gif',
        default => 'image/jpeg',
    }, null, true);
}

function member(): User
{
    return User::factory()->create(['role' => 'member', 'is_active' => true]);
}

function admin(): User
{
    return User::factory()->create(['role' => 'admin', 'is_active' => true]);
}

beforeEach(function () {
    Storage::fake('public');
});

it('lets a signed-in member upload a photograph', function () {
    $response = $this->actingAs(member())
        ->postJson('/members/images', ['image' => realImage()]);

    $response->assertCreated()
        ->assertJsonStructure(['id', 'url', 'thumb_url', 'markdown', 'size', 'width', 'height']);

    expect(Media::count())->toBe(1);
});

it('refuses uploads from visitors who are not signed in', function () {
    // A fetch request gets 401 rather than a redirect it could not follow
    // usefully; a browser visiting the page normally is still sent to /login.
    $this->postJson('/members/images', ['image' => realImage()])
        ->assertUnauthorized();

    expect(Media::count())->toBe(0);
});

it('sends a guest browsing to the image library to the login page', function () {
    $this->get('/members/images')->assertRedirect('/login');
});

it('stores three sizes of every photograph', function () {
    $this->actingAs(member())->postJson('/members/images', ['image' => realImage()])->assertCreated();

    $media = Media::first();
    $disk = Storage::disk('public');

    expect($disk->exists($media->path))->toBeTrue()
        ->and($disk->exists($media->display_path))->toBeTrue()
        ->and($disk->exists($media->thumb_path))->toBeTrue();
});

it('shrinks a large photograph instead of storing it at full size', function () {
    $this->actingAs(member())
        ->postJson('/members/images', ['image' => realImage(4000, 3000)])
        ->assertCreated();

    $media = Media::first();

    // 4000px capped to 2400px on the longest edge.
    expect($media->width)->toBeLessThanOrEqual(2400);

    // The display copy is what a reader downloads, so it must be far smaller.
    $displaySize = Storage::disk('public')->size($media->display_path);
    expect($displaySize)->toBeLessThan(Storage::disk('public')->size($media->path));
});

it('crops thumbnails to a consistent shape so listing cards line up', function () {
    $this->actingAs(member())
        ->postJson('/members/images', ['image' => realImage(3000, 1000)])
        ->assertCreated();

    $thumb = Storage::disk('public')->path(Media::first()->thumb_path);
    [$width, $height] = getimagesize($thumb);

    expect($width)->toBe(600)->and($height)->toBe(400);
});

it('rejects a file that is not an image', function () {
    $this->actingAs(member())
        ->postJson('/members/images', [
            'image' => UploadedFile::fake()->create('accounts.pdf', 40, 'application/pdf'),
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('image');

    expect(Media::count())->toBe(0);
});

it('rejects a script disguised as an image', function () {
    // A PHP payload given an image extension and an image MIME type: the sort of
    // thing that turns an upload form into a way of running code on the server.
    $path = tempnam(sys_get_temp_dir(), 'evil').'.jpg';
    file_put_contents($path, "<?php echo 'this should never run'; ?>");

    $this->actingAs(member())
        ->postJson('/members/images', [
            'image' => new UploadedFile($path, 'photo.jpg', 'image/jpeg', null, true),
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('image');

    expect(Media::count())->toBe(0);
});

it('strips the location data phones embed in photographs', function () {
    // GPS coordinates in a photograph taken at a junior session should not be
    // published with it.
    $source = realImage(800, 600);
    $this->actingAs(member())->postJson('/members/images', ['image' => $source])->assertCreated();

    $stored = Storage::disk('public')->path(Media::first()->path);
    $exif = @exif_read_data($stored);

    expect($exif === false || ! isset($exif['GPSLatitude']))->toBeTrue();
});

it('accepts PNG, WebP and GIF as well as JPEG', function () {
    foreach (['png', 'webp', 'gif'] as $format) {
        $this->actingAs(member())
            ->postJson('/members/images', ['image' => realImage(900, 600, $format)])
            ->assertCreated();
    }

    expect(Media::count())->toBe(3);
});

it('shows a member only their own photographs, and an administrator everyone\'s', function () {
    $alice = member();
    $bob = member();

    $this->actingAs($alice)->postJson('/members/images', ['image' => realImage(600, 400)]);
    $this->actingAs($bob)->postJson('/members/images', ['image' => realImage(600, 400)]);

    $this->actingAs($alice)->get('/members/images')->assertOk()->assertSee($alice->name);
    $this->actingAs(admin())->get('/members/images')->assertOk()
        ->assertSee($alice->name)->assertSee($bob->name);
});

it('stops a member deleting another member\'s photograph', function () {
    $owner = member();
    $this->actingAs($owner)->postJson('/members/images', ['image' => realImage(600, 400)]);
    $media = Media::first();

    $this->actingAs(member())
        ->delete('/members/images/'.$media->id)
        ->assertForbidden();

    expect(Media::count())->toBe(1);
});

it('lets an administrator delete any photograph', function () {
    $this->actingAs(member())->postJson('/members/images', ['image' => realImage(600, 400)]);
    $media = Media::first();

    $this->actingAs(admin())->delete('/members/images/'.$media->id)->assertRedirect();

    expect(Media::count())->toBe(0);
});

it('removes the files from disk when a photograph is deleted', function () {
    $this->actingAs(member())->postJson('/members/images', ['image' => realImage(600, 400)]);
    $media = Media::first();
    $paths = [$media->path, $media->display_path, $media->thumb_path];

    $media->delete();

    foreach ($paths as $path) {
        expect(Storage::disk('public')->exists($path))->toBeFalse();
    }
});

it('keeps a post when its lead photograph is deleted', function () {
    $user = member();
    $this->actingAs($user)->postJson('/members/images', ['image' => realImage(600, 400)]);
    $media = Media::first();

    $post = Post::create([
        'user_id' => $user->id,
        'title' => 'Match report',
        'slug' => 'match-report',
        'type' => Post::TYPE_GENERAL,
        'body' => 'We won.',
        'featured_image_id' => $media->id,
        'is_published' => true,
    ]);

    $media->delete();

    expect($post->fresh())->not->toBeNull()
        ->and($post->fresh()->featured_image_id)->toBeNull();
});

it('will not attach a photograph that does not exist to a post', function () {
    $this->actingAs(member())
        ->post('/members/posts', [
            'title' => 'Match report',
            'type' => Post::TYPE_GENERAL,
            'body' => 'We won the match.',
            'featured_image_id' => 99999,
        ])
        ->assertSessionHasErrors('featured_image_id');
});

it('shows the lead photograph on the post and on its listing card', function () {
    $user = member();
    $this->actingAs($user)->postJson('/members/images', ['image' => realImage(1200, 800)]);
    $media = Media::first();
    $media->update(['alt_text' => 'The A team after the final round']);

    Post::create([
        'user_id' => $user->id,
        'title' => 'Match report',
        'slug' => 'match-report',
        'type' => Post::TYPE_GENERAL,
        'body' => 'We won.',
        'featured_image_id' => $media->id,
        'featured_image_caption' => 'Celebrating in the bar afterwards',
        'is_published' => true,
    ]);

    $this->get('/blog/match-report')->assertOk()
        ->assertSee('The A team after the final round')
        ->assertSee('Celebrating in the bar afterwards');

    $this->get('/blog')->assertOk()->assertSee($media->thumb_path);
});

/* ---------------------------------------------------------------------------
 | Puzzle answers
 * ------------------------------------------------------------------------ */

it('hides a puzzle answer behind a button rather than printing it on the page', function () {
    $user = member();

    Post::create([
        'user_id' => $user->id,
        'title' => 'Tuesday puzzle',
        'slug' => 'tuesday-puzzle',
        'type' => Post::TYPE_POSITION,
        'fen' => '8/8/8/8/8/1k6/1p6/1K1R4 w - - 0 1',
        'orientation' => 'white',
        'side_to_move' => 'w',
        'caption' => 'White to play and draw.',
        'solution' => 'Rd8 is the only move that holds.',
        'is_published' => true,
    ]);

    $response = $this->get('/blog/tuesday-puzzle');

    $response->assertOk()
        ->assertSee('Show the answer')
        ->assertSee('reveal-answer__button', false);
});

it('does not leak a puzzle answer into the blog listing summary', function () {
    $user = member();

    Post::create([
        'user_id' => $user->id,
        'title' => 'Tuesday puzzle',
        'slug' => 'tuesday-puzzle',
        'type' => Post::TYPE_POSITION,
        'fen' => '8/8/8/8/8/1k6/1p6/1K1R4 w - - 0 1',
        'orientation' => 'white',
        'side_to_move' => 'w',
        'caption' => 'White to play and draw.',
        'solution' => 'Rd8 is the only move that holds.',
        'is_published' => true,
    ]);

    // The answer must not appear on a page the reader has not asked it for.
    $this->get('/blog')->assertOk()->assertDontSee('Rd8 is the only move that holds.');
    $this->get('/')->assertOk()->assertDontSee('Rd8 is the only move that holds.');
});

it('shows no answer button when a position has no answer', function () {
    $user = member();

    Post::create([
        'user_id' => $user->id,
        'title' => 'Just a position',
        'slug' => 'just-a-position',
        'type' => Post::TYPE_POSITION,
        'fen' => '8/8/8/8/8/1k6/1p6/1K1R4 w - - 0 1',
        'orientation' => 'white',
        'side_to_move' => 'w',
        'is_published' => true,
    ]);

    $this->get('/blog/just-a-position')->assertOk()->assertDontSee('Show the answer');
});

it('writes a relative path into the post body so photographs survive a change of host', function () {
    // An absolute URL would bake today's hostname into the article text, and
    // every photograph would break the day the club moves to its own domain.
    $this->actingAs(member())->postJson('/members/images', ['image' => realImage(900, 600)]);

    $markdown = Media::first()->markdown();

    expect($markdown)->toStartWith('![')
        ->and($markdown)->toContain('](/storage/uploads/')
        ->and($markdown)->not->toContain('http://')
        ->and($markdown)->not->toContain('https://');
});

it('renders a relative image path through the Markdown pipeline', function () {
    $html = \App\Support\Markdown::toHtml("Text.\n\n![A photo](/storage/uploads/2026/08/x-display.jpg)\n");

    expect(str_contains($html, 'src="/storage/uploads/2026/08/x-display.jpg"'))->toBeTrue();
});
