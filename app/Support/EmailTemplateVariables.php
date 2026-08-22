<?php

namespace App\Support;

use App\Models\EmailTemplate;
use App\Models\Enquiry;
use App\Models\User;

class EmailTemplateVariables
{
    /** @return array<string, string> */
    public static function forEnquiry(Enquiry $enquiry): array
    {
        $meeting = config('club.meeting');
        $venue = config('club.venue');
        $juniorsVenue = config('club.juniors_venue');
        $officers = config('club.officers');

        return [
            'name' => $enquiry->name,
            'club_name' => config('club.name'),
            'meeting_day' => (string) $meeting['day'],
            'meeting_time' => (string) $meeting['time'],
            'venue_name' => (string) $venue['name'],
            'venue_address' => (string) $venue['address'],
            'venue_postcode' => (string) $venue['postcode'],
            'team_count' => (string) config('club.teams.coventry_count'),
            'four_ncl_sentence' => config('club.teams.plays_4ncl') ? 'We also have a team in the 4NCL.' : '',
            'junior_time' => (string) $meeting['juniors'],
            'junior_fee' => (string) $juniorsVenue['fee'],
            'junior_venue_name' => (string) $juniorsVenue['name'],
            'junior_venue_address' => (string) $juniorsVenue['address'],
            'coaching_trainers' => (string) config('club.coaching.trainers'),
            'officer_list' => collect($officers)
                ->map(fn (array $officer): string => '- '.$officer['role'].', '.$officer['name'].' — '.$officer['phone'])
                ->implode("\n"),
            'facebook_url' => (string) config('club.links.facebook'),
        ];
    }

    /** @return array<string, string> */
    public static function forMember(User $user): array
    {
        return [
            'name' => $user->publicName(),
            'email' => $user->email,
            'club_name' => config('club.name'),
            'login_url' => route('login'),
        ];
    }

    /** @return array<string, string> */
    public static function sample(string $key, User $administrator): array
    {
        if ($key === EmailTemplate::MEMBER_CONFIRMATION) {
            return self::forMember(new User([
                'name' => 'Alex Member',
                'email' => 'alex.member@example.com',
            ]));
        }

        return self::forEnquiry(new Enquiry([
            'name' => 'Alex Visitor',
            'email' => 'alex.visitor@example.com',
            'enquiry_type' => 'join',
            'message' => 'I would like to visit the club.',
        ]));
    }
}
