<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('stores chess puzzle solutions longer than a varchar column', function () {
    expect(Schema::getColumnType('posts', 'solution'))->toBe('text');

    $user = User::factory()->create();
    $solution = str_repeat('A complete explanation of the winning continuation. ', 12);

    DB::table('posts')->insert([
        'user_id' => $user->id,
        'title' => 'Production schema test',
        'slug' => 'production-schema-test',
        'type' => 'position',
        'solution' => $solution,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    expect(DB::table('posts')->value('solution'))->toBe($solution);
});
