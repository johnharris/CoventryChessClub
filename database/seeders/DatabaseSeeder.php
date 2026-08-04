<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\WhitelistEntry;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Sets the site up ready to use: an administrator account, an example member, the
 * club's standing pages and the blog content.
 *
 * The first administrator has to be created by a seeder rather than through the
 * site, because registration is gated on the whitelist and only administrators
 * can add to the whitelist.
 *
 * Credentials come from the environment so that production installs do not use
 * the documented development password:
 *
 *   ADMIN_NAME, ADMIN_EMAIL, ADMIN_PASSWORD
 */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $adminEmail = env('ADMIN_EMAIL', 'admin@coventrychessclub.test');

        $admin = User::updateOrCreate(
            ['email' => $adminEmail],
            [
                'name' => env('ADMIN_NAME', 'Club Administrator'),
                'display_name' => env('ADMIN_DISPLAY_NAME', 'Simon Weaver'),
                'password' => Hash::make(env('ADMIN_PASSWORD', 'password')),
                'role' => User::ROLE_ADMIN,
                'is_active' => true,
                'email_verified_at' => now(),
                'bio' => 'Club secretary. Posts the fixtures, the season dates and anything else that needs announcing.',
            ]
        );

        // Whitelist the administrator too, so the members screen shows a complete picture.
        WhitelistEntry::updateOrCreate(
            ['email' => $admin->email],
            [
                'name' => $admin->name,
                'role' => User::ROLE_ADMIN,
                'notes' => 'Founding administrator, created by the installer.',
                'claimed_at' => now(),
                'claimed_by_user_id' => $admin->id,
                'invite_token' => null,
            ]
        );

        // An example club member, to show the difference between the two roles.
        $memberEmail = env('MEMBER_EMAIL', 'member@coventrychessclub.test');

        $member = User::updateOrCreate(
            ['email' => $memberEmail],
            [
                'name' => env('MEMBER_NAME', 'Rhys Edwards'),
                'display_name' => 'Rhys Edwards',
                'password' => Hash::make(env('MEMBER_PASSWORD', 'password')),
                'role' => User::ROLE_MEMBER,
                'is_active' => true,
                'email_verified_at' => now(),
                'ecf_rating' => 2015,
                'bio' => 'Board one for the A team in the First Division. Available for one-to-one coaching.',
            ]
        );

        WhitelistEntry::updateOrCreate(
            ['email' => $member->email],
            [
                'name' => $member->name,
                'role' => User::ROLE_MEMBER,
                'invited_by_user_id' => $admin->id,
                'notes' => 'A team, board one. Runs the coaching.',
                'claimed_at' => now(),
                'claimed_by_user_id' => $member->id,
                'invite_token' => null,
            ]
        );

        // An unclaimed invitation, so the whitelist screen demonstrates the invite flow.
        WhitelistEntry::firstOrCreate(
            ['email' => 'newmember@example.com'],
            [
                'name' => 'A New Member',
                'role' => User::ROLE_MEMBER,
                'invited_by_user_id' => $admin->id,
                'notes' => 'Joined on a club night; invitation not yet used.',
                'invite_token' => WhitelistEntry::freshToken(),
            ]
        );

        $this->call([
            PageSeeder::class,
            PostSeeder::class,
        ]);
    }
}
