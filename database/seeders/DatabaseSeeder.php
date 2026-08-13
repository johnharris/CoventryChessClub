<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\WhitelistEntry;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Sets the site up ready to use: the club's three administrators, an example
 * member, the standing pages and the blog content.
 *
 * The first administrators have to be created by a seeder rather than through
 * the site, because registration is gated on the whitelist and only an
 * administrator can add to the whitelist. Everybody after these three is
 * invited from the Members screen in the normal way.
 *
 * Every password comes from the environment, so a production install can be
 * given real passwords rather than the documented development one. See
 * .env.example and DEPLOYMENT.md.
 */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * The club's officers and administrators.
     *
     * Held as data rather than repeated code so that adding or removing an
     * administrator is a one-line change, and so the whitelist entry can never
     * drift out of step with the account.
     */
    private function administrators(): array
    {
        return [
            [
                'env' => 'SIMON',
                'name' => 'Simon Weaver',
                'email' => 'simonw21@yahoo.com',
                'bio' => 'Club secretary. Handles new enquiries, the fixtures and the season dates.',
                'notes' => 'Club secretary. Founding administrator.',
            ],
            [
                'env' => 'DAVE',
                'name' => 'Dave Filer',
                'email' => 'david_filer@hotmail.com',
                'bio' => 'Club chairman.',
                'notes' => 'Club chairman. Founding administrator.',
            ],
            [
                'env' => 'JOHN',
                'name' => 'John Harris',
                'email' => 'johnrharris174@fastmail.com',
                'bio' => 'Club administrator. Looks after the website.',
                'notes' => 'Club administrator. Runs the website.',
            ],
        ];
    }

    public function run(): void
    {
        $admins = [];

        foreach ($this->administrators() as $person) {
            $email = env($person['env'].'_EMAIL', $person['email']);

            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => env($person['env'].'_NAME', $person['name']),
                    'display_name' => env($person['env'].'_NAME', $person['name']),
                    'password' => Hash::make(env($person['env'].'_PASSWORD', 'password')),
                    'role' => User::ROLE_ADMIN,
                    'is_active' => true,
                    'email_verified_at' => now(),
                    'bio' => $person['bio'],
                ]
            );

            // Whitelisted as well, so the Members screen shows a complete picture
            // rather than accounts that appear to have arrived from nowhere.
            WhitelistEntry::updateOrCreate(
                ['email' => $user->email],
                [
                    'name' => $user->name,
                    'role' => User::ROLE_ADMIN,
                    'notes' => $person['notes'],
                    'claimed_at' => now(),
                    'claimed_by_user_id' => $user->id,
                    'invite_token' => null,
                ]
            );

            $admins[$person['env']] = $user;
        }

        // Simon, as club secretary, is treated as the inviter of everybody else.
        $inviter = $admins['SIMON'];

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
                'invited_by_user_id' => $inviter->id,
                'notes' => 'A team, board one. Runs the coaching.',
                'claimed_at' => now(),
                'claimed_by_user_id' => $member->id,
                'invite_token' => null,
            ]
        );

        // An unclaimed invitation, so the Members screen demonstrates the invite flow.
        WhitelistEntry::firstOrCreate(
            ['email' => 'newmember@example.com'],
            [
                'name' => 'A New Member',
                'role' => User::ROLE_MEMBER,
                'invited_by_user_id' => $inviter->id,
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
