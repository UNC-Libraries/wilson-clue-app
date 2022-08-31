<?php

use App\Team;

$team = new Team;

return [
    'homepage' => [
        'description' => 'This appears on the homepage just below the jumbotron, ONLY when all games are DORMANT or ARCHIVED',
        'markdown' => true,
        'vars' => [],
        'rows' => 3,
    ],
    'special_notice' => [
        'description' => 'This appears on the homepage just below the jumbotron, above the main navigation when registration is ON',
        'markdown' => true,
        'rows' => 7,
        'vars' => [
            'game_date' => 'The weekday, month and day of the game',
            'game_time' => 'The game start time',
        ],
    ],
    'registration_closed' => [
        'description' => 'This appears on the homepage just below the jumbotron, above the main navigation when registration is OFF and a game is ACTIVE.',
        'markdown' => true,
        'rows' => 4,
        'vars' => [
            'game_date' => 'The weekday, month and day of the game',
            'game_time' => 'The game start time',
        ],
    ],
    'team_status_message:_registered_team' => [
        'description' => 'Shows up on the team management page. For teams that are fully registered.',
        'markdown' => false,
        'rows' => 3,
    ],
    'team_status_message:_waitlist' => [
        'description' => 'Shows up on the team management page. For teams that are on the waitlist AND they have at least '.$team::MINIMUM_PLAYERS.' players.',
        'markdown' => false,
        'rows' => 3,
    ],
    'team_status_message:_not_enough_players,_open_spots' => [
        'description' => "Shows up on the team management page. For teams that are on the waitlist because they don't have at least ".$team::MINIMUM_PLAYERS.' players AND there are open spots available for the game.',
        'markdown' => false,
        'rows' => 3,
    ],
    'team_status_message:_not_enough_players,_game_full' => [
        'description' => "Shows up on the team management page. For teams that are on the waitlist AND they don't have at least ".$team::MINIMUM_PLAYERS.' players AND there are NO open spots available for the game.',
        'markdown' => false,
        'rows' => 3,
    ],
    'email:_initial_registration' => [
        'description' => 'The email is sent to the player who initially registers the team. The first line will be used as the email subject.',
        'markdown' => true,
        'rows' => 7,
        'vars' => [
            'team_name' => "The team's name.",
            'game_date' => 'The weekday, month and day of the game',
            'game_time' => 'The game start time',
            'team_management_url' => 'The link to the team management page. In markdown, this would go in between the 
                                        parenthesis when creating a link. E.g. [Text to display](||team_management_url||)',
        ],
    ],
    'email:_fully_registered' => [
        'description' => 'The email is sent to all the players of a team confirming they are fully registered. The first line will be used as the email subject.
                            NOTE: This email will NOT be sent if you register a team from the admin interface. 
                            You will need to email that team directly.',
        'markdown' => true,
        'rows' => 7,
        'vars' => [
            'team_name' => "The team's name.",
            'game_date' => 'The weekday, month and day of the game',
            'game_time' => 'The game start time',
            'team_management_url' => 'The link to the team management page. In markdown, this would go in between the 
                                        parenthesis when creating a link. E.g. [Text to display](||team_management_url||)',
        ],
    ],
];
