<?php

use App\Models\HomepageSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function homepageAdmin(): User
{
    return User::factory()->create([
        'role' => User::ROLE_ADMIN,
        'is_active' => true,
    ]);
}

function homepageMember(): User
{
    return User::factory()->create([
        'role' => User::ROLE_MEMBER,
        'is_active' => true,
    ]);
}

it('shows the Italian Game on a fresh installation without creating a setting row', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee(HomepageSetting::DEFAULT_FEN, false)
        ->assertSee(HomepageSetting::DEFAULT_CAPTION);

    $this->assertDatabaseCount('homepage_settings', 0);
});

it('keeps the homepage position screen for administrators only', function () {
    $this->get('/members/homepage')->assertRedirect('/login');

    $this->actingAs(homepageMember())
        ->get('/members/homepage')
        ->assertForbidden();

    $this->actingAs(homepageAdmin())
        ->get('/members/homepage')
        ->assertOk()
        ->assertSee('Header chess position')
        ->assertSee('Save homepage position');
});

it('lets an administrator change the homepage position, viewpoint and caption', function () {
    $fen = '8/8/8/8/1k6/1p6/1K1R4/8 w - - 0 1';
    $caption = 'Winning position from the Summer Cup final';

    $this->actingAs(homepageAdmin())
        ->put('/members/homepage', [
            'hero_fen' => $fen,
            'hero_orientation' => 'black',
            'hero_caption' => $caption,
        ])
        ->assertRedirect(route('members.homepage.edit'))
        ->assertSessionHas('status', 'Homepage position saved.');

    $this->assertDatabaseHas('homepage_settings', [
        'id' => 1,
        'hero_fen' => $fen,
        'hero_orientation' => 'black',
        'hero_caption' => $caption,
    ]);

    $this->get('/')
        ->assertOk()
        ->assertSee('data-fen="'.$fen.'"', false)
        ->assertSee('data-orientation="black"', false)
        ->assertSee($caption);
});

it('rejects an invalid homepage position', function () {
    $this->actingAs(homepageAdmin())
        ->from('/members/homepage')
        ->put('/members/homepage', [
            'hero_fen' => 'not a chess position',
            'hero_orientation' => 'white',
            'hero_caption' => 'Broken board',
        ])
        ->assertRedirect('/members/homepage')
        ->assertSessionHasErrors('hero_fen');

    $this->assertDatabaseCount('homepage_settings', 0);
});

it('rejects an unsupported board viewpoint', function () {
    $this->actingAs(homepageAdmin())
        ->from('/members/homepage')
        ->put('/members/homepage', [
            'hero_fen' => HomepageSetting::DEFAULT_FEN,
            'hero_orientation' => 'sideways',
            'hero_caption' => null,
        ])
        ->assertRedirect('/members/homepage')
        ->assertSessionHasErrors('hero_orientation');
});

it('lets an administrator restore the Italian Game default', function () {
    HomepageSetting::query()->create([
        'hero_fen' => '8/8/8/8/1k6/1p6/1K1R4/8 w - - 0 1',
        'hero_orientation' => 'black',
        'hero_caption' => 'Summer Cup finish',
    ]);

    $this->actingAs(homepageAdmin())
        ->delete('/members/homepage')
        ->assertRedirect(route('members.homepage.edit'))
        ->assertSessionHas('status', 'Homepage position restored to the Italian Game.');

    $this->assertDatabaseHas('homepage_settings', [
        'id' => 1,
        'hero_fen' => HomepageSetting::DEFAULT_FEN,
        'hero_orientation' => HomepageSetting::DEFAULT_ORIENTATION,
        'hero_caption' => HomepageSetting::DEFAULT_CAPTION,
    ]);
});
