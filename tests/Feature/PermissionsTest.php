<?php

/**
 * The permission rules the club asked to have confirmed:
 *
 *   A member may   : write posts, edit their own, delete their own
 *   A member may NOT: edit or delete anybody else's post
 *   An admin may   : edit or delete anybody's post, and feature posts
 *
 * These are asserted through real HTTP requests rather than by calling the
 * policy directly, so a route that forgot to check the policy would still fail
 * the test.
 */

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function activeMember(string $name = 'Member'): User
{
    return User::factory()->create(['name' => $name, 'role' => 'member', 'is_active' => true]);
}

function activeAdmin(string $name = 'Administrator'): User
{
    return User::factory()->create(['name' => $name, 'role' => 'admin', 'is_active' => true]);
}

function postBy(User $user, string $slug = 'a-post'): Post
{
    return Post::create([
        'user_id' => $user->id,
        'title' => 'A post by '.$user->name,
        'slug' => $slug,
        'type' => Post::TYPE_GENERAL,
        'body' => 'Some words.',
        'is_published' => true,
    ]);
}

/* ---------------------------------------------------------------------------
 | A member and their own posts
 * ------------------------------------------------------------------------ */

it('lets a member write a post', function () {
    $this->actingAs(activeMember())
        ->post('/members/posts', [
            'title' => 'Round three report',
            'type' => Post::TYPE_GENERAL,
            'body' => 'We drew 2-2.',
            'is_published' => '1',
        ])
        ->assertRedirect();

    expect(Post::where('title', 'Round three report')->exists())->toBeTrue();
});

it('lets a member edit their own post', function () {
    $member = activeMember();
    $post = postBy($member);

    $this->actingAs($member)->get('/members/posts/'.$post->id.'/edit')->assertOk();

    $this->actingAs($member)
        ->put('/members/posts/'.$post->id, [
            'title' => 'Corrected title',
            'type' => Post::TYPE_GENERAL,
            'body' => 'Corrected words.',
            'is_published' => '1',
        ])
        ->assertRedirect();

    expect($post->fresh()->title)->toBe('Corrected title');
});

it('lets a member delete their own post', function () {
    $member = activeMember();
    $post = postBy($member);

    $this->actingAs($member)->delete('/members/posts/'.$post->id)->assertRedirect();

    expect(Post::find($post->id))->toBeNull();
});

/* ---------------------------------------------------------------------------
 | A member and somebody else's posts
 * ------------------------------------------------------------------------ */

it('stops a member opening the edit form for another member\'s post', function () {
    $author = activeMember('Author');
    $other = activeMember('Someone else');
    $post = postBy($author);

    $this->actingAs($other)->get('/members/posts/'.$post->id.'/edit')->assertForbidden();
});

it('stops a member saving changes to another member\'s post', function () {
    $author = activeMember('Author');
    $other = activeMember('Someone else');
    $post = postBy($author);

    $this->actingAs($other)
        ->put('/members/posts/'.$post->id, [
            'title' => 'Hijacked',
            'type' => Post::TYPE_GENERAL,
            'body' => 'Changed without permission.',
        ])
        ->assertForbidden();

    expect($post->fresh()->title)->not->toBe('Hijacked');
});

it('stops a member deleting another member\'s post', function () {
    $author = activeMember('Author');
    $other = activeMember('Someone else');
    $post = postBy($author);

    $this->actingAs($other)->delete('/members/posts/'.$post->id)->assertForbidden();

    expect(Post::find($post->id))->not->toBeNull();
});

/* ---------------------------------------------------------------------------
 | An administrator
 * ------------------------------------------------------------------------ */

it('lets an administrator edit any member\'s post', function () {
    $member = activeMember('Author');
    $post = postBy($member);

    $this->actingAs(activeAdmin())
        ->put('/members/posts/'.$post->id, [
            'title' => 'Edited by the secretary',
            'type' => Post::TYPE_GENERAL,
            'body' => 'Tidied up.',
            'is_published' => '1',
        ])
        ->assertRedirect();

    expect($post->fresh()->title)->toBe('Edited by the secretary');
});

it('lets an administrator delete any member\'s post', function () {
    $member = activeMember('Author');
    $post = postBy($member);

    $this->actingAs(activeAdmin())->delete('/members/posts/'.$post->id)->assertRedirect();

    expect(Post::find($post->id))->toBeNull();
});

it('keeps the post author when an administrator edits it', function () {
    // Editing somebody else's post must not quietly steal the by-line.
    $member = activeMember('Author');
    $post = postBy($member);

    $this->actingAs(activeAdmin())->put('/members/posts/'.$post->id, [
        'title' => 'Tidied up',
        'type' => Post::TYPE_GENERAL,
        'body' => 'Spelling corrected.',
        'is_published' => '1',
    ]);

    expect($post->fresh()->user_id)->toBe($member->id);
});

/* ---------------------------------------------------------------------------
 | Featuring a post on the home page — administrators only
 * ------------------------------------------------------------------------ */

it('does not let a member pin their own post to the home page', function () {
    $member = activeMember();

    $this->actingAs($member)->post('/members/posts', [
        'title' => 'Look at me',
        'type' => Post::TYPE_GENERAL,
        'body' => 'Words.',
        'is_published' => '1',
        'is_featured' => '1',
    ]);

    expect(Post::where('title', 'Look at me')->first()->is_featured)->toBeFalse();
});

it('lets an administrator pin a post to the home page', function () {
    $this->actingAs(activeAdmin())->post('/members/posts', [
        'title' => 'Club AGM',
        'type' => Post::TYPE_GENERAL,
        'body' => 'Words.',
        'is_published' => '1',
        'is_featured' => '1',
    ]);

    expect(Post::where('title', 'Club AGM')->first()->is_featured)->toBeTrue();
});

/* ---------------------------------------------------------------------------
 | Listings, drafts and suspended accounts
 * ------------------------------------------------------------------------ */

it('shows a member only their own posts in the members area', function () {
    $member = activeMember('Mine');
    $other = activeMember('Theirs');
    postBy($member, 'mine');
    postBy($other, 'theirs');

    $this->actingAs($member)->get('/members/posts')
        ->assertOk()
        ->assertSee('A post by Mine')
        ->assertDontSee('A post by Theirs');
});

it('shows an administrator every post in the members area', function () {
    $member = activeMember('Mine');
    $other = activeMember('Theirs');
    postBy($member, 'mine');
    postBy($other, 'theirs');

    $this->actingAs(activeAdmin())->get('/members/posts')
        ->assertOk()
        ->assertSee('A post by Mine')
        ->assertSee('A post by Theirs');
});

it('hides another member\'s unpublished draft from the public and from members', function () {
    $author = activeMember('Author');
    $draft = Post::create([
        'user_id' => $author->id,
        'title' => 'Unfinished thoughts',
        'slug' => 'unfinished-thoughts',
        'type' => Post::TYPE_GENERAL,
        'body' => 'Half written.',
        'is_published' => false,
    ]);

    $this->get('/blog/unfinished-thoughts')->assertNotFound();
    $this->actingAs(activeMember('Nosy'))->get('/blog/unfinished-thoughts')->assertNotFound();
    $this->actingAs($author)->get('/blog/unfinished-thoughts')->assertOk();
    $this->actingAs(activeAdmin())->get('/blog/unfinished-thoughts')->assertOk();
});

it('stops a suspended member writing or editing anything', function () {
    $suspended = User::factory()->create(['role' => 'member', 'is_active' => false]);
    $post = postBy(activeMember('Author'));

    // A suspended account is turned away at the door, before any policy applies.
    $this->actingAs($suspended)->get('/members/posts/create')->assertRedirect();
    $this->actingAs($suspended)->delete('/members/posts/'.$post->id)->assertRedirect();

    expect(Post::find($post->id))->not->toBeNull();
});

/* ---------------------------------------------------------------------------
 | The club's three administrators
 * ------------------------------------------------------------------------ */

it('creates all three club administrators, each able to sign in and administer', function () {
    $this->seed();

    $expected = [
        'simonw21@yahoo.com' => 'Simon Weaver',
        'david_filer@hotmail.com' => 'Dave Filer',
        'johnrharris174@fastmail.com' => 'John Harris',
    ];

    foreach ($expected as $email => $name) {
        $user = User::where('email', $email)->first();

        expect($user)->not->toBeNull("No account was created for {$email}")
            ->and($user->name)->toBe($name)
            ->and($user->isAdmin())->toBeTrue()
            ->and($user->is_active)->toBeTrue();

        // Signing in with the seeded password must actually work.
        $this->post('/logout');
        $this->post('/login', ['email' => $email, 'password' => 'password'])
            ->assertRedirect();

        // And each one must reach all three administrator screens.
        $this->actingAs($user)->get('/members/whitelist')->assertOk();
        $this->actingAs($user)->get('/members/enquiries')->assertOk();
        $this->actingAs($user)->get('/members/pages')->assertOk();
    }

    expect(User::where('role', 'admin')->count())->toBe(3);
});

it('whitelists each administrator so the Members screen shows them all', function () {
    $this->seed();

    $this->actingAs(User::where('email', 'johnrharris174@fastmail.com')->first())
        ->get('/members/whitelist')
        ->assertOk()
        ->assertSee('Simon Weaver')
        ->assertSee('Dave Filer')
        ->assertSee('John Harris');
});
