<?php

return [
    'enquiry_acknowledgement' => [
        'label' => 'Enquiry acknowledgement',
        'description' => 'Sent automatically to someone after they submit the public contact form.',
        'enabled' => (bool) env('CLUB_AUTO_REPLY_ENABLED', true),
        'subject' => env('CLUB_AUTO_REPLY_SUBJECT', 'Thank you for your interest in Coventry Chess Club'),
        'body' => <<<'MD'
Hi {{name}},

Thank you for your interest in {{club_name}}.

We have League and Social matches every {{meeting_day}} from {{meeting_time}} at {{venue_name}}, {{venue_address}}, {{venue_postcode}}.

We have {{team_count}} teams in the Coventry & District chess league, plus a number of players who play friendlies. {{four_ncl_sentence}}

Our Junior section is every {{junior_time}} and only {{junior_fee}} and is held at {{junior_venue_name}}, {{junior_venue_address}}. Spots for this session fill up very quickly and must be pre-booked, so please check with us before attending.

If you wish to visit us and have a few games of chess, you are very welcome to come to the club on a {{meeting_day}} evening.

Private tuition is also available for a fee if required, from our {{coaching_trainers}}.

For any further information you require, do feel free to telephone either of us:

{{officer_list}}

We also have a Facebook group here: {{facebook_url}} — which again you are welcome to join.

If you do decide to come down to the club, it may be best if you telephone one of us beforehand and then we can make sure someone will keep an eye out for you on arrival and introduce you to other club members.
MD,
        'signature' => env('CLUB_AUTO_REPLY_SIGNATURE', 'Simon Weaver'),
        'signature_role' => env('CLUB_AUTO_REPLY_SIGNATURE_ROLE', 'Club Secretary'),
        'placeholders' => [
            'name',
            'club_name',
            'meeting_day',
            'meeting_time',
            'venue_name',
            'venue_address',
            'venue_postcode',
            'team_count',
            'four_ncl_sentence',
            'junior_time',
            'junior_fee',
            'junior_venue_name',
            'junior_venue_address',
            'coaching_trainers',
            'officer_list',
            'facebook_url',
        ],
    ],

    'member_confirmation' => [
        'label' => 'Member confirmation',
        'description' => 'Sent after an invited member successfully creates their account.',
        'enabled' => true,
        'subject' => 'Your Coventry Chess Club member account is ready',
        'body' => <<<'MD'
Hi {{name}},

Your {{club_name}} member account has been created successfully.

You can sign in at {{login_url}} using {{email}} and the password you chose during registration.

The members area lets you update your profile, write club posts and upload photographs. Administrators have additional controls for enquiries, members, standing pages and website settings.

If you did not create this account, please contact a club administrator as soon as possible.
MD,
        'signature' => 'Coventry Chess Club',
        'signature_role' => '',
        'placeholders' => [
            'name',
            'email',
            'club_name',
            'login_url',
        ],
    ],

    'limits' => [
        'subject' => 190,
        'body' => 10000,
        'signature' => 120,
        'signature_role' => 120,
    ],
];
