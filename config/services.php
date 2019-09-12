<?php

return [
    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],
    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],
    'ses' => [
        'key' => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => 'eu-central-1',
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
    'ips' => [
        'client_id' => env('IPS_CLIENT_ID'),
        'client_secret' => env('IPS_CLIENT_SECRET'),
        'redirect' => env('IPS_REDIRECT_URI'),
        'api_key' => env('IPS_API_KEY'),
        'url' => env('IPS_URL'),
        'forums' => [
            'herald' => env('IPS_HERALD_FORUM_ID'),
        ],
    ],
    'discord' => [
        'client_id' => env('DISCORD_CLIENT_ID'),
        'client_secret' => env('DISCORD_CLIENT_SECRET'),
        'redirect' => env('DISCORD_REDIRECT_URI'),
        'bot_token' => env('DISCORD_BOT_TOKEN'),
        'url' => 'https://discordapp.com/api',
        'guild_id' => env('DISCORD_GUILD_ID', 229980402574557184),
        'channels' => [
            'announcements' => env('APP_ENV') === 'production' ? env('DISCORD_ANNOUNCEMENTS_CHANNEL_ID') : env('DISCORD_TEST_CHANNEL_ID'),
            'subscriptions' => env('APP_ENV') === 'production' ? env('DISCORD_SUBSCRIPTIONS_CHANNEL_ID') : env('DISCORD_TEST_CHANNEL_ID'),
            'officer_hq' => env('APP_ENV') === 'production' ? env('DISCORD_OFFICER_HQ_CHANNEL_ID') : env('DISCORD_TEST_CHANNEL_ID'),
            'dps_parses' => env('APP_ENV') === 'production' ? env('DISCORD_DPS_PARSES_CHANNEL_ID') : env('DISCORD_TEST_CHANNEL_ID'),
        ],
    ],
    'pmg' => [
        'api_token' => env('PMG_API_TOKEN'),
    ],
];
