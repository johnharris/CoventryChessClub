<?php

/*
|--------------------------------------------------------------------------
| Club details
|--------------------------------------------------------------------------
|
| Everything here can be changed in the .env file without touching code, so
| meeting times, the venue, officer contact details and the standard welcome
| email can all be kept up to date by whoever administers the hosting.
|
*/

$enquiryEmails = array_values(array_unique(array_filter(
    array_map('trim', explode(',', (string) env('CLUB_ENQUIRY_EMAILS', env('CLUB_ENQUIRY_EMAIL', '')))),
    fn (string $email): bool => filter_var($email, FILTER_VALIDATE_EMAIL) !== false,
)));

return [

    'name' => env('CLUB_NAME', 'Coventry Chess Club'),

    'tagline' => env('CLUB_TAGLINE', 'Chess in Coventry every Tuesday evening — all standards welcome'),

    // Every address receives its own copy of a contact-form notification. A
    // comma-separated list keeps deployment simple while avoiding disclosure of
    // the other administrators' addresses in the delivered message. The old
    // singular variable remains a fallback for existing installations.
    'enquiry_emails' => $enquiryEmails,

    'meeting' => [
        'day' => env('CLUB_MEETING_DAY', 'Tuesday'),
        'time' => env('CLUB_MEETING_TIME', '7:30pm'),
        'juniors' => env('CLUB_JUNIORS_TIME', 'Tuesday, 4:30pm'),
    ],

    // The junior section meets at a different venue from the main club night,
    // places must be booked in advance, and there is a small charge per session.
    'juniors_venue' => [
        'name' => env('CLUB_JUNIORS_VENUE_NAME', "St Oswald's Church Hall"),
        'address' => env('CLUB_JUNIORS_VENUE_ADDRESS', 'Tile Hill, Coventry'),
        'map_url' => env('CLUB_JUNIORS_VENUE_MAP_URL', 'https://www.google.com/maps/search/?api=1&query=St+Oswalds+Church+Hall+Tile+Hill+Coventry'),
        'booking_required' => (bool) env('CLUB_JUNIORS_BOOKING_REQUIRED', true),
        'fee' => env('CLUB_JUNIORS_FEE', '£5 per session'),
    ],

    'venue' => [
        'name' => env('CLUB_VENUE_NAME', 'Massey Ferguson Social Club'),
        'address' => env('CLUB_VENUE_ADDRESS', 'Broad Lane, Coventry'),
        'postcode' => env('CLUB_VENUE_POSTCODE', 'CV5 7NL'),
        'entrance' => env('CLUB_VENUE_ENTRANCE', ''),
        'map_url' => env('CLUB_VENUE_MAP_URL', 'https://www.google.com/maps/search/?api=1&query=Massey+Ferguson+Social+Club+Broad+Lane+Coventry+CV5+7NL'),
        'website' => env('CLUB_VENUE_WEBSITE', 'https://www.masseyfergusonsocialclub.co.uk/'),
    ],

    /*
    |----------------------------------------------------------------------
    | Officers
    |----------------------------------------------------------------------
    |
    | Shown on the contact page and included in the automatic reply sent to
    | anyone who uses the contact form, so a newcomer always has a person and
    | a telephone number rather than only a web form.
    |
    */
    'officers' => [
        [
            'name' => env('CLUB_CHAIRMAN_NAME', 'Dave Filer'),
            'role' => env('CLUB_CHAIRMAN_ROLE', 'Chairman'),
            'phone' => env('CLUB_CHAIRMAN_PHONE', '024 7641 1719'),
        ],
        [
            'name' => env('CLUB_SECRETARY_NAME', 'Simon Weaver'),
            'role' => env('CLUB_SECRETARY_ROLE', 'Club Secretary'),
            'phone' => env('CLUB_SECRETARY_PHONE', '01455 221297'),
        ],
    ],

    /*
    |----------------------------------------------------------------------
    | Automatic reply
    |----------------------------------------------------------------------
    |
    | Anyone who submits the contact form receives an immediate acknowledgement
    | containing the club's standard welcome information. The body lives in a
    | Blade view, but the switch and the signature are here so they can be
    | changed without touching a template.
    |
    */
    'auto_reply' => [
        'enabled' => (bool) env('CLUB_AUTO_REPLY_ENABLED', true),
        'subject' => env('CLUB_AUTO_REPLY_SUBJECT', 'Thank you for your interest in Coventry Chess Club'),
        // Simon Weaver signs the acknowledgement; Dave Filer still appears
        // above as the Chairman's telephone contact.
        'signature' => env('CLUB_AUTO_REPLY_SIGNATURE', 'Simon Weaver'),
        'signature_role' => env('CLUB_AUTO_REPLY_SIGNATURE_ROLE', 'Club Secretary'),
    ],

    'teams' => [
        // Number of sides entered in the Coventry & District League.
        'coventry_count' => env('CLUB_COVENTRY_TEAM_COUNT', 'six'),
        // Left switchable rather than deleted, so the club can mention its
        // 4NCL side again later without needing a code change.
        'plays_4ncl' => (bool) env('CLUB_PLAYS_4NCL', false),
    ],

    'coaching' => [
        'trainers' => env('CLUB_COACHING_TRAINERS', 'two fully qualified trainers'),
        'paid' => (bool) env('CLUB_COACHING_PAID', true),
    ],

    'links' => [
        'coventry_league' => env('CLUB_LINK_COVENTRY_LEAGUE', 'http://covchessleague.blogspot.com/'),
        'coventry_league_results' => env('CLUB_LINK_COVENTRY_RESULTS', 'https://ecflms.org.uk/lms/node/406/home'),
        'leamington_league' => env('CLUB_LINK_LEAMINGTON_LEAGUE', 'http://www.leamingtonchessleague.org.uk/'),
        'four_ncl' => env('CLUB_LINK_4NCL', 'https://www.4ncl.co.uk/'),
        'ecf' => env('CLUB_LINK_ECF', 'https://www.englishchess.org.uk/'),
        'facebook' => env('CLUB_LINK_FACEBOOK', 'https://www.facebook.com/groups/coventrychessclub'),
        'lichess' => env('CLUB_LINK_LICHESS', 'https://lichess.org/'),
    ],

    /*
    |----------------------------------------------------------------------
    | Hosting credit
    |----------------------------------------------------------------------
    |
    | A courtesy link in the footer to whoever hosts the site. Krystal's
    | not-for-profit scheme asks for one in return for free hosting, and the
    | club is glad to give it.
    |
    | Set the name to an empty string to remove the credit altogether, which
    | is what to do if the club ever moves to a host that does not ask for one.
    |
    */
    'hosting_credit' => [
        'name' => env('CLUB_HOST_NAME', 'Krystal'),
        'url' => env('CLUB_HOST_URL', 'https://krystal.io/'),
        'prefix' => env('CLUB_HOST_PREFIX', 'Hosted by'),
    ],

];
