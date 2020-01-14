<?php namespace App\Listeners\DpsParse;

use App\Events\DpsParse\DpsParseSubmitted;
use App\Models\Character;
use App\Models\Set;
use App\Singleton\ClassTypes;
use App\Singleton\RoleTypes;
use GuzzleHttp\RequestOptions;

class PostNewDpsParseToDiscord
{
    public function __construct()
    {
        \Cloudinary::config([
            'cloud_name' => config('filesystems.disks.cloudinary.cloud_name'),
            'api_key' => config('filesystems.disks.cloudinary.key'),
            'api_secret' => config('filesystems.disks.cloudinary.secret'),
        ]);
    }

    /**
     * @param \App\Events\DpsParse\DpsParseSubmitted $event
     *
     * @return bool
     * @throws \Exception
     */
    public function handle(DpsParseSubmitted $event): bool
    {
        $dpsParse = $event->dpsParse;
        $dpsParse->refresh();

        $owner = $event->getOwner();
        $character = $event->getCharacter();
        $gearSets = $this->getGearSets($dpsParse->sets);
        $gearSetsParsed = [];
        foreach ($gearSets as $set) {
            $gearSetsParsed[] = '[' . $set->name . '](https://eso-sets.com/set/' . $set->id . ')';
        }

        $owner->loadMissing('linkedAccounts');
        $discordAccount = $owner->linkedAccounts()->where('remote_provider', 'discord')->first();
        $mentionedName = $discordAccount ? '<@!' . $discordAccount->remote_id . '>' : $owner->name;

        $idsOfCreatedDiscordMessages = [];
        $channelId = config('services.discord.channels.dps_parses_logs');
        $responseDecoded = app('discord.api')->createMessageInChannel($channelId, [
            RequestOptions::FORM_PARAMS => [
                'payload_json' => json_encode([
                    'content' => $mentionedName . ' has submitted a DPS parse with ' . $dpsParse->dps_amount . ' DPS.',
                    'tts' => false,
                    'embed' => [
                        'color' => 0x888800,
                        'thumbnail' => [
                            'url' => cloudinary_url('special/logo.png', [
                                'secure' => true,
                                'width' => 300,
                                'height' => 300
                            ])
                        ],
                        'fields' => [
                            [
                                'name' => 'Character',
                                'value' => sprintf('[%s](https://planner.lodgeofsorceresses.com/characters/%s)', $character->name, $character->id),
                            ],
                            [
                                'name' => 'Role',
                                'value' => RoleTypes::getRoleName($character->role),
                            ],
                            [
                                'name' => 'Class',
                                'value' => ClassTypes::getClassName($character->class),
                            ],
                            [
                                'name' => 'Sets Used',
                                'value' => implode(', ', $gearSetsParsed),
                                'inline' => false
                            ],
                        ],
                        'image' => [
                            'url' => cloudinary_url($dpsParse->parse_file_hash, [
                                'secure' => true,
                            ])
                        ],
                        'footer' => [
                            'text' => 'Submitted via Lodge of Sorceresses Planner at: https://planner.lodgeofsorceresses.com'
                        ]
                    ],
                ]),
            ]
        ]);
        $idsOfCreatedDiscordMessages[] = $responseDecoded['id'];
        $dpsParse->discord_notification_message_ids = implode(',', $idsOfCreatedDiscordMessages);
        $dpsParse->save();

        return true;
    }

    /**
     * @param string $commaSeparatedSetIds
     *
     * @return \App\Models\Set[]
     */
    private function getGearSets(string $commaSeparatedSetIds): iterable
    {
        return Set::whereIn('id', explode(',', $commaSeparatedSetIds))->get();
    }
}
