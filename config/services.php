<?php

use App\Models\User;

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
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'eu-central-1'),
    ],
    'stripe' => [
        'model' => User::class,
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
        'bot_id' => env('DISCORD_BOT_ID', '343344270066843649'),
        'bot_token' => env('DISCORD_BOT_TOKEN'),
        'url' => 'https://discordapp.com/api',
        'guild_id' => env('DISCORD_GUILD_ID', '229980402574557184'),
        'channels' => [
            'announcements' => env('DISCORD_ANNOUNCEMENTS_CHANNEL_ID', '551378145500987392'),
            'achievements' => env('DISCORD_ACHIEVEMENTS_CHANNEL_ID', '467837773156581426'),
            'subscriptions' => env('DISCORD_SUBSCRIPTIONS_CHANNEL_ID', '551378145500987392'),
            'dps_parses_logs' => env('DISCORD_DPS_PARSES_CHANNEL_ID', '551378145500987392'),
            'pve_core_announcements' => env('DISCORD_PVE_CORE_ANNOUNCEMENTS_CHANNEL_ID', '551378145500987392'),
            'pve_open_events' => env('DISCORD_PVE_OPEN_EVENTS_CHANNEL_ID', '635780862809604098'),
            'officer_hq' => env('DISCORD_OFFICER_HQ_CHANNEL_ID', '551378145500987392'),
            'officer_logs' => env('DISCORD_OFFICER_LOGS_CHANNEL_ID', '700249699701227520'),
        ],
    ],
    'pmg' => [
        'api_token' => env('PMG_API_TOKEN'),
    ],
];
