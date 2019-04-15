<?php

return [
    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],
    'ses' => [
        'key' => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => 'eu-central-1',
    ],
    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],
    'stripe' => [
        'model' => \App\Models\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook' => [
            'secret' => env('STRIPE_WEBHOOK_SECRET'),
            'tolerance' => env('STRIPE_WEBHOOK_TOLERANCE', 300),
        ],
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', 'http://basis.audith.org/oauth/google')
    ],
    'twitter' => [
        'client_id' => env('TWITTER_CLIENT_ID'),
        'client_secret' => env('TWITTER_CLIENT_SECRET'),
        'redirect' => env('TWITTER_REDIRECT_URI', 'http://basis.audith.org/oauth/twitter')
    ],
    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('FACEBOOK_REDIRECT_URI', 'http://basis.audith.org/oauth/facebook')
    ],
    'ips' => [
        'client_id' => env('IPS_CLIENT_ID'),
        'client_secret' => env('IPS_CLIENT_SECRET'),
        'redirect' => env('IPS_REDIRECT_URI'),
        'api_key' => env('IPS_API_KEY'),
        'url' => env('IPS_URL'),
    ],
    'discord' => [
        'client_id' => env('DISCORD_CLIENT_ID'),
        'client_secret' => env('DISCORD_CLIENT_SECRET'),
        'redirect' => env('DISCORD_REDIRECT_URI'),
        'bot_token' => env('DISCORD_BOT_TOKEN'),
        'url' => 'https://discordapp.com/api',
        'channels' => [
            'announcements' => env('APP_ENV') === 'production' ? env('DISCORD_ANNOUNCEMENTS_CHANNEL_ID') : env('DISCORD_TEST_CHANNEL_ID'),
            'midgame_dps_parses' => env('APP_ENV') === 'production' ? env('DISCORD_MIDGAME_DPS_PARSES_CHANNEL_ID') : env('DISCORD_TEST_CHANNEL_ID'),
            'endgame_dps_parses' => env('APP_ENV') === 'production' ? env('DISCORD_ENDGAME_DPS_PARSES_CHANNEL_ID') : env('DISCORD_TEST_CHANNEL_ID'),
        ],
    ],
];
