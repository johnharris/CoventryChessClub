<?php

/*
|--------------------------------------------------------------------------
| Club details
|--------------------------------------------------------------------------
|
| Everything here can be changed in the .env file without touching code, so
| meeting times, the venue and the contact address can be kept up to date by
| whoever administers the hosting.
|
*/

return [

    'name' => env('CLUB_NAME', 'Coventry Chess Club'),

    'tagline' => env('CLUB_TAGLINE', 'Chess in Coventry every Tuesday evening — all standards welcome'),

    // Where contact form notifications are sent. Leave blank to store enquiries
    // in the site inbox only (the default until SMTP is configured).
    'enquiry_email' => env('CLUB_ENQUIRY_EMAIL'),

    'meeting' => [
        'day' => env('CLUB_MEETING_DAY', 'Tuesday'),
        'time' => env('CLUB_MEETING_TIME', '7:30pm'),
        'juniors' => env('CLUB_JUNIORS_TIME', 'Tuesday, 6:00pm – 7:15pm'),
    ],

    'venue' => [
        'name' => env('CLUB_VENUE_NAME', 'Massey Ferguson Social Club'),
        'address' => env('CLUB_VENUE_ADDRESS', 'Broad Lane, Coventry'),
        'postcode' => env('CLUB_VENUE_POSTCODE', 'CV5 7NL'),
        'map_url' => env('CLUB_VENUE_MAP_URL', 'https://www.google.com/maps/search/?api=1&query=Massey+Ferguson+Social+Club+Broad+Lane+Coventry+CV5+7NL'),
        'website' => env('CLUB_VENUE_WEBSITE', 'https://www.masseyfergusonsocialclub.co.uk/'),
    ],

    'links' => [
        'coventry_league' => env('CLUB_LINK_COVENTRY_LEAGUE', 'http://covchessleague.blogspot.com/'),
        'coventry_league_results' => env('CLUB_LINK_COVENTRY_RESULTS', 'https://ecflms.org.uk/lms/node/406/home'),
        'leamington_league' => env('CLUB_LINK_LEAMINGTON_LEAGUE', 'http://www.leamingtonchessleague.org.uk/'),
        'ecf' => env('CLUB_LINK_ECF', 'https://www.englishchess.org.uk/'),
        'facebook' => env('CLUB_LINK_FACEBOOK', 'https://www.facebook.com/search/top?q=coventry%20chess%20club'),
        'lichess' => env('CLUB_LINK_LICHESS', 'https://lichess.org/'),
    ],

];
