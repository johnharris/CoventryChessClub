<?php

use App\Models\Enquiry;
use App\Models\Page;
use App\Models\Post;
use App\Models\User;
use App\Models\WhitelistEntry;
use App\Support\ChessNotation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

/**
 * Helpers
 */
function makeAdmin(): User
{
    return User::create([
        'name' => 'Club Administrator',
        'email' => 'admin@test.local',
        'password' => Hash::make('secret1234'),
        'role' => User::ROLE_ADMIN,
        'is_active' => true,
    ]);
}

function makeMember(string $email = 'member@test.local'): User
{
    return User::create([
        'name' => 'Club Member',
        'email' => $email,
        'password' => Hash::make('secret1234'),
        'role' => User::ROLE_MEMBER,
        'is_active' => true,
    ]);
}

function makePost(array $attributes = []): Post
{
    $author = $attributes['user_id'] ?? makeMember('author@test.local')->id;

    return Post::create(array_merge([
        'user_id' => $author,
        'title' => 'A club night report',
        'slug' => 'a-club-night-report',
        'type' => Post::TYPE_GENERAL,
        'body' => 'We played some chess.',
        'is_published' => true,
        'published_at' => now()->subDay(),
    ], $attributes));
}

/* -------------------------------------------------------------------------
 | Public pages
 * ---------------------------------------------------------------------- */

it('shows the home page', function () {
    $this->get('/')->assertOk()->assertSee('Coventry Chess Club', false);
});

it('shows the blog index with published posts only', function () {
    $author = makeMember();

    makePost(['user_id' => $author->id, 'title' => 'Visible post', 'slug' => 'visible-post']);

    Post::create([
        'user_id' => $author->id,
        'title' => 'Hidden draft',
        'slug' => 'hidden-draft',
        'type' => Post::TYPE_GENERAL,
        'body' => 'Not ready.',
        'is_published' => false,
    ]);

    $this->get('/blog')
        ->assertOk()
        ->assertSee('Visible post')
        ->assertDontSee('Hidden draft');
});

it('hides scheduled posts until their publication time', function () {
    makePost([
        'title' => 'Future post',
        'slug' => 'future-post',
        'published_at' => now()->addWeek(),
    ]);

    $this->get('/blog')->assertOk()->assertDontSee('Future post');
});

it('shows a single published post', function () {
    makePost(['title' => 'Readable post', 'slug' => 'readable-post']);

    $this->get('/blog/readable-post')->assertOk()->assertSee('Readable post');
});

it('returns 404 for a draft post when not signed in', function () {
    Post::create([
        'user_id' => makeMember()->id,
        'title' => 'Secret draft',
        'slug' => 'secret-draft',
        'type' => Post::TYPE_GENERAL,
        'body' => 'Work in progress.',
        'is_published' => false,
    ]);

    $this->get('/blog/secret-draft')->assertNotFound();
});

it('lets the author preview their own draft', function () {
    $author = makeMember();

    Post::create([
        'user_id' => $author->id,
        'title' => 'My draft',
        'slug' => 'my-draft',
        'type' => Post::TYPE_GENERAL,
        'body' => 'Work in progress.',
        'is_published' => false,
    ]);

    $this->actingAs($author)->get('/blog/my-draft')->assertOk()->assertSee('My draft');
});

it('shows a published club page at its slug', function () {
    Page::create([
        'title' => 'Fixtures',
        'slug' => 'fixtures',
        'body' => 'Our matches this season.',
        'is_published' => true,
    ]);

    $this->get('/fixtures')->assertOk()->assertSee('Our matches this season.');
});

/* -------------------------------------------------------------------------
 | Whitelist-gated registration
 * ---------------------------------------------------------------------- */

it('refuses registration for an address that is not whitelisted', function () {
    $this->post('/register', [
        'name' => 'Gate Crasher',
        'email' => 'nobody@example.com',
        'password' => 'secret1234',
        'password_confirmation' => 'secret1234',
    ])->assertSessionHasErrors('email');

    expect(User::where('email', 'nobody@example.com')->exists())->toBeFalse();
});

it('allows registration for a whitelisted address and claims the invitation', function () {
    $entry = WhitelistEntry::create([
        'email' => 'invited@example.com',
        'name' => 'Invited Player',
        'role' => User::ROLE_MEMBER,
        'invite_token' => WhitelistEntry::freshToken(),
    ]);

    $this->post('/register', [
        'name' => 'Invited Player',
        'email' => 'invited@example.com',
        'password' => 'secret1234',
        'password_confirmation' => 'secret1234',
    ])->assertRedirect(route('members.dashboard'));

    $user = User::where('email', 'invited@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->role)->toBe(User::ROLE_MEMBER)
        ->and($user->is_active)->toBeTrue();

    expect($entry->fresh()->claimed_at)->not->toBeNull();
    expect($entry->fresh()->invite_token)->toBeNull();
});

it('grants the role recorded on the whitelist entry', function () {
    WhitelistEntry::create([
        'email' => 'officer@example.com',
        'role' => User::ROLE_ADMIN,
        'invite_token' => WhitelistEntry::freshToken(),
    ]);

    $this->post('/register', [
        'name' => 'Club Officer',
        'email' => 'officer@example.com',
        'password' => 'secret1234',
        'password_confirmation' => 'secret1234',
    ]);

    expect(User::where('email', 'officer@example.com')->first()->isAdmin())->toBeTrue();
});

it('refuses a second registration against a claimed invitation', function () {
    WhitelistEntry::create([
        'email' => 'once@example.com',
        'role' => User::ROLE_MEMBER,
        'claimed_at' => now(),
    ]);

    $this->post('/register', [
        'name' => 'Second Attempt',
        'email' => 'once@example.com',
        'password' => 'secret1234',
        'password_confirmation' => 'secret1234',
    ])->assertSessionHasErrors('email');
});

/* -------------------------------------------------------------------------
 | Authentication and access control
 * ---------------------------------------------------------------------- */

it('signs a member in with the right password', function () {
    $user = makeMember();

    $this->post('/login', ['email' => $user->email, 'password' => 'secret1234'])
        ->assertRedirect(route('members.dashboard'));

    $this->assertAuthenticatedAs($user);
});

it('rejects a wrong password', function () {
    $user = makeMember();

    $this->post('/login', ['email' => $user->email, 'password' => 'wrong-password'])
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('refuses a suspended account', function () {
    $user = makeMember();
    $user->update(['is_active' => false]);

    $this->post('/login', ['email' => $user->email, 'password' => 'secret1234'])
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('sends guests to the login page when they ask for the members area', function () {
    $this->get('/members')->assertRedirect('/login');
});

it('keeps ordinary members out of the administrator screens', function () {
    $member = makeMember();

    $this->actingAs($member)->get('/members/whitelist')->assertForbidden();
    $this->actingAs($member)->get('/members/enquiries')->assertForbidden();
    $this->actingAs($member)->get('/members/pages')->assertForbidden();
});

it('lets administrators into the administrator screens', function () {
    $admin = makeAdmin();

    $this->actingAs($admin)->get('/members/whitelist')->assertOk();
    $this->actingAs($admin)->get('/members/enquiries')->assertOk();
    $this->actingAs($admin)->get('/members/pages')->assertOk();
});

/* -------------------------------------------------------------------------
 | Writing posts
 * ---------------------------------------------------------------------- */

it('lets a member publish a general post', function () {
    $this->actingAs(makeMember())->post('/members/posts', [
        'type' => Post::TYPE_GENERAL,
        'title' => 'Round four result',
        'body' => 'We won 3.5-1.5.',
        'is_published' => '1',
    ])->assertRedirect();

    $post = Post::where('title', 'Round four result')->first();

    expect($post)->not->toBeNull()
        ->and($post->slug)->toBe('round-four-result')
        ->and($post->is_published)->toBeTrue()
        ->and($post->published_at)->not->toBeNull();
});

it('stores a position post and keeps the FEN', function () {
    $fen = '8/8/8/8/1k6/1p6/1K1R4/8 w - - 0 1';

    $this->actingAs(makeMember())->post('/members/posts', [
        'type' => Post::TYPE_POSITION,
        'title' => 'A rook ending',
        'body' => 'Worth knowing.',
        'fen' => $fen,
        'orientation' => 'white',
        'caption' => 'White to play and draw',
        'is_published' => '1',
    ])->assertRedirect();

    expect(Post::where('title', 'A rook ending')->first()->fen)->toBe($fen);
});

it('rejects an invalid FEN', function () {
    $this->actingAs(makeMember())->post('/members/posts', [
        'type' => Post::TYPE_POSITION,
        'title' => 'Broken position',
        'body' => 'Nope.',
        'fen' => 'this is not a fen',
    ])->assertSessionHasErrors('fen');
});

it('stores a game post and keeps the PGN', function () {
    $pgn = '[White "Smith, J"] [Black "Jones, A"] [Result "1-0"] 1. e4 e5 2. Nf3 Nc6 3. Bb5 a6 1-0';

    $this->actingAs(makeMember())->post('/members/posts', [
        'type' => Post::TYPE_GAME,
        'title' => 'A Spanish win',
        'body' => 'Notes to follow.',
        'pgn' => $pgn,
        'orientation' => 'white',
        'is_published' => '1',
    ])->assertRedirect();

    expect(Post::where('title', 'A Spanish win')->first()->pgn)->toContain('1. e4 e5');
});

it('rejects a PGN with no moves', function () {
    $this->actingAs(makeMember())->post('/members/posts', [
        'type' => Post::TYPE_GAME,
        'title' => 'Empty game',
        'body' => 'Nope.',
        'pgn' => '[White "Nobody"]',
    ])->assertSessionHasErrors('pgn');
});

it('stops a member editing somebody else’s post', function () {
    $post = makePost();
    $other = makeMember('other@test.local');

    $this->actingAs($other)->get("/members/posts/{$post->id}/edit")->assertForbidden();
});

it('lets an administrator edit anybody’s post', function () {
    $post = makePost();

    $this->actingAs(makeAdmin())->get("/members/posts/{$post->id}/edit")->assertOk();
});

it('gives each post a unique slug', function () {
    $author = makeMember();

    foreach (['First go', 'First go'] as $title) {
        $this->actingAs($author)->post('/members/posts', [
            'type' => Post::TYPE_GENERAL,
            'title' => $title,
            'body' => 'Body text.',
        ]);
    }

    expect(Post::where('slug', 'first-go')->count())->toBe(1)
        ->and(Post::where('slug', 'first-go-2')->count())->toBe(1);
});

/* -------------------------------------------------------------------------
 | Contact form
 * ---------------------------------------------------------------------- */

it('stores a contact enquiry', function () {
    $this->post('/contact', [
        'name' => 'Prospective Player',
        'email' => 'hello@example.com',
        'subject' => 'Joining',
        'enquiry_type' => 'join',
        'message' => 'I would like to come along on Tuesday.',
    ])->assertRedirect();

    $enquiry = Enquiry::where('email', 'hello@example.com')->first();

    expect($enquiry)->not->toBeNull()
        ->and($enquiry->enquiry_type)->toBe('join')
        ->and($enquiry->is_read)->toBeFalse();
});

it('validates the contact form', function () {
    $this->post('/contact', ['name' => '', 'email' => 'not-an-email', 'message' => ''])
        ->assertSessionHasErrors(['name', 'email', 'enquiry_type', 'message']);
});

it('records a self-assessed playing strength', function () {
    $this->post('/contact', [
        'name' => 'Improving Player',
        'email' => 'strength@example.com',
        'enquiry_type' => 'join',
        'playing_strength' => 'intermediate',
        'message' => 'I have played a bit and would like a game.',
    ])->assertRedirect();

    $enquiry = Enquiry::where('email', 'strength@example.com')->first();

    expect($enquiry->playing_strength)->toBe('intermediate')
        ->and($enquiry->strengthLabel())->toBe('Intermediate')
        ->and($enquiry->strengthHint())->not->toBeNull();
});

it('accepts an enquiry with no playing strength given', function () {
    $this->post('/contact', [
        'name' => 'Private Person',
        'email' => 'quiet@example.com',
        'enquiry_type' => 'general',
        'playing_strength' => '',
        'message' => 'Just asking about opening times please.',
    ])->assertRedirect()->assertSessionHasNoErrors();

    $enquiry = Enquiry::where('email', 'quiet@example.com')->first();

    expect($enquiry)->not->toBeNull()
        ->and($enquiry->playing_strength)->toBeNull()
        ->and($enquiry->strengthLabel())->toBeNull();
});

it('rejects a playing strength that is not one of the three options', function () {
    $this->post('/contact', [
        'name' => 'Grandmaster Pretender',
        'email' => 'gm@example.com',
        'enquiry_type' => 'join',
        'playing_strength' => 'grandmaster',
        'message' => 'I am obviously very good indeed.',
    ])->assertSessionHasErrors('playing_strength');

    expect(Enquiry::where('email', 'gm@example.com')->exists())->toBeFalse();
});

it('shows the playing strength dropdown on the contact page', function () {
    $response = $this->get('/contact');

    $response->assertOk()
        ->assertSee('name="playing_strength"', false)
        ->assertSee('Beginner')
        ->assertSee('Intermediate')
        ->assertSee('Advanced')
        ->assertSee('Prefer not to say');
});

it('shows the playing strength to an administrator reading the enquiry', function () {
    $enquiry = Enquiry::create([
        'name' => 'Strong Player',
        'email' => 'strong@example.com',
        'enquiry_type' => 'join',
        'playing_strength' => 'advanced',
        'message' => 'I play league chess and have moved to Coventry.',
    ]);

    $this->actingAs(makeAdmin())
        ->get("/members/enquiries/{$enquiry->id}")
        ->assertOk()
        ->assertSee('Advanced');
});

it('rejects a message that is too short', function () {
    $this->post('/contact', [
        'name' => 'Brief Person',
        'email' => 'brief@example.com',
        'enquiry_type' => 'general',
        'message' => 'Hi',
    ])->assertSessionHasErrors('message');
});

it('rejects a submission that fills the honeypot', function () {
    $this->post('/contact', [
        'name' => 'Spam Bot',
        'email' => 'spam@example.com',
        'enquiry_type' => 'general',
        'message' => 'Buy things from my website please.',
        'website' => 'http://spam.example.com',
    ])->assertSessionHasErrors('website');

    expect(Enquiry::count())->toBe(0);
});

it('rate limits repeated submissions from the same address', function () {
    $payload = [
        'name' => 'Keen Person',
        'email' => 'keen@example.com',
        'enquiry_type' => 'general',
        'message' => 'I have a great many questions about the club.',
    ];

    for ($i = 0; $i < 3; $i++) {
        $this->post('/contact', $payload)->assertRedirect();
    }

    $this->post('/contact', $payload)->assertSessionHasErrors('message');

    expect(Enquiry::count())->toBe(3);
});

it('lets an administrator read and archive an enquiry', function () {
    $enquiry = Enquiry::create([
        'name' => 'Prospective Player',
        'email' => 'hello@example.com',
        'enquiry_type' => 'join',
        'message' => 'I would like to come along on Tuesday.',
    ]);

    $admin = makeAdmin();

    // Opening an enquiry marks it as read.
    $this->actingAs($admin)->get("/members/enquiries/{$enquiry->id}")->assertOk();

    expect($enquiry->fresh()->is_read)->toBeTrue();

    $this->actingAs($admin)->put("/members/enquiries/{$enquiry->id}", ['is_archived' => '1'])
        ->assertRedirect();

    expect($enquiry->fresh()->is_archived)->toBeTrue();
});

/* -------------------------------------------------------------------------
 | Chess notation helpers
 * ---------------------------------------------------------------------- */

it('accepts a valid FEN', function () {
    expect(ChessNotation::isValidFen(ChessNotation::START_FEN))->toBeTrue()
        ->and(ChessNotation::isValidFen('8/8/8/8/1k6/1p6/1K1R4/8 w - - 0 1'))->toBeTrue();
});

it('rejects a malformed FEN and explains why', function () {
    expect(ChessNotation::isValidFen('nonsense'))->toBeFalse()
        ->and(ChessNotation::fenError('rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP w KQkq - 0 1'))
        ->toContain('8 ranks')
        ->and(ChessNotation::fenError('8/8/8/8/8/8/8/8 w - - 0 1'))
        ->toContain('white king')
        ->and(ChessNotation::fenError('rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR x KQkq - 0 1'))
        ->toContain('side to move');
});

it('reads the side to move from a FEN', function () {
    expect(ChessNotation::sideToMove(ChessNotation::START_FEN))->toBe('w')
        ->and(ChessNotation::sideToMove('8/8/8/8/1k6/1p6/1K1R4/8 b - - 0 1'))->toBe('b');
});

it('fills in the optional FEN fields when they are missing', function () {
    expect(ChessNotation::normaliseFen('8/8/8/8/1k6/1p6/1K1R4/8 w'))
        ->toBe('8/8/8/8/1k6/1p6/1K1R4/8 w - - 0 1');
});

it('pulls the headers and moves out of a PGN', function () {
    $pgn = "[Event \"Coventry League\"]\n"
        ."[White \"Morphy, P\"]\n"
        ."[Black \"Duke of Brunswick\"]\n"
        ."[Result \"1-0\"]\n\n"
        .'1. e4 e5 2. Nf3 d6 {The Philidor.} 3. d4 Bg4 1-0';

    $game = ChessNotation::parseGame($pgn);

    expect($game['headers']['White'])->toBe('Morphy, P')
        ->and($game['headers']['Result'])->toBe('1-0')
        ->and($game['moveText'])->toContain('1. e4 e5')
        ->and($game['annotations'])->toContain('The Philidor.')
        ->and(ChessNotation::isValidPgn($pgn))->toBeTrue();
});

it('rejects a PGN whose braces are unbalanced', function () {
    expect(ChessNotation::pgnError('1. e4 e5 {an unfinished comment'))
        ->toContain('braces');
});

it('converts PGN dates, including partial ones', function () {
    expect(ChessNotation::pgnDate('2026.02.28'))->toBe('2026-02-28')
        ->and(ChessNotation::pgnDate('1858.??.??'))->toBe('1858-01-01')
        ->and(ChessNotation::pgnDate('not a date'))->toBeNull();
});

/* ----------------------------------------------------------------------
 | Junior section details
 |
 | The junior session moved to a separate venue and requires pre-booking.
 | These guard against the old wording creeping back in.
 * ---------------------------------------------------------------------- */

it('does not advertise junior coaching as part of a typical club night', function () {
    $this->get('/')
        ->assertOk()
        ->assertDontSee('Junior section: coaching and graded games')
        ->assertDontSee('6:00pm');
});

it('states the junior venue and pre-booking requirement on the home page', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('4:30pm')
        ->assertSee("St Oswald's Church Hall")
        ->assertSee('Tile Hill')
        ->assertSee('booked in advance');
});

it('states the junior venue and pre-booking requirement on the contact page', function () {
    $this->get('/contact')
        ->assertOk()
        ->assertSee("St Oswald's Church Hall")
        ->assertSee('Tile Hill')
        ->assertSee('pre-booked');
});

/* ----------------------------------------------------------------------
 * Hosting credit
 *
 * Krystal's not-for-profit hosting scheme asks for a link in the footer in
 * return for free hosting, so the club has undertaken to display one. Were it
 * ever to disappear silently the club would be quietly in breach of the terms
 * of its own hosting, which is worth a test.
 * ---------------------------------------------------------------------- */
it('credits the host in the footer', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('Hosted by')
        ->assertSee('krystal.io', false);
});

it('credits the host on an interior page too', function () {
    $this->get('/contact')
        ->assertOk()
        ->assertSee('Hosted by');
});

it('leaves the credit out when no host is named', function () {
    config(['club.hosting_credit.name' => '']);

    $this->get('/')
        ->assertOk()
        ->assertDontSee('Hosted by');
});
