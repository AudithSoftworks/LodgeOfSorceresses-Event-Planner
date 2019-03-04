<?php namespace App\Listeners\DpsParses;

use App\Events\DpsParses\DpsParseSubmitted;
use App\Models\Character;
use App\Models\EquipmentSet;
use App\Singleton\ClassTypes;
use App\Singleton\RoleTypes;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class PostNewDpsParseToDiscord
{
    private const DISCORD_API_ENDPOINT = 'https://discordapp.com/api/';

    private const DISCORD_MIDGAME_DPS_PARSES_CHANNEL_ID = '460038712311545856';

    private const DISCORD_CORE_DPS_PARSES_CHANNEL_ID = '496635762855641090';

    private const DISCORD_TEST_CHANNEL_ID = '551378145500987392';

    public function __construct()
    {
        \Cloudinary::config([
            'cloud_name' => config('filesystems.disks.cloudinary.cloud_name'),
            'api_key' => config('filesystems.disks.cloudinary.key'),
            'api_secret' => config('filesystems.disks.cloudinary.secret'),
        ]);
    }

    /**
     * @param \App\Events\DpsParses\DpsParseSubmitted $event
     *
     * @return bool
     * @throws \Exception
     */
    public function handle(DpsParseSubmitted $event)
    {
        $dpsParse = $event->dpsParse;
        $dpsParse->refresh();

        $botAccessToken = config('services.discord_bot.token');
        $discordClient = new Client([
            'base_uri' => self::DISCORD_API_ENDPOINT,
            'headers' => [
                'Authorization' => 'Bot ' . $botAccessToken,
                'Content-Type' => 'application/json'
            ],
        ]);

        /** @var \App\Models\User $me */
        $me = app('auth.driver')->user();
        $character = $this->getCharacter($dpsParse->character_id);
        $gearSets = $this->getGearSets($dpsParse->sets);
        $gearSetsParsed = [];
        foreach ($gearSets as $set) {
            $gearSetsParsed[] = '[' . $set->name . '](' . 'https://eso-sets.com/set/' . $set->id . ')';
        }

        $params = [
            [
                RequestOptions::FORM_PARAMS => [
                    'content' => '**' . $me->name . ' has submitted a DPS parse with ' . $dpsParse->dps_amount . ' DPS.**',
                    'tts' => false,
                ]
            ],
            [
                RequestOptions::FORM_PARAMS => [
                    'payload_json' => json_encode([
                        'embed' => [
                            'title' => 'Parse Screenshot',
                            'color' => 0x888800,
                            'fields' => [
                                [
                                    'name' => 'Role',
                                    'value' => RoleTypes::getRoleName($character->role),
                                ],
                                [
                                    'name' => 'Class',
                                    'value' => ClassTypes::getClassName($character->class),
                                ],
                            ],
                            'image' => [
                                'url' => cloudinary_url($dpsParse->parse_file_hash, [
                                    'secure' => true,
                                    'width' => 800,
                                    'height' => 800,
                                    'gravity' => 'auto:classic',
                                    'crop' => 'fill'
                                ])
                            ],
                        ],
                    ]),
                ]
            ],
            [
                RequestOptions::FORM_PARAMS => [
                    'payload_json' => json_encode([
                        'embed' => [
                            'title' => 'Superstar Screenshot',
                            'color' => 0x888800,
                            'fields' => [
                                [
                                    'name' => 'Sets Used',
                                    'value' => implode(', ', $gearSetsParsed),
                                    'inline' => false
                                ],
                            ],
                            'timestamp' => (new \DateTimeImmutable($dpsParse->created_at))->format('c'),
                            'image' => [
                                'url' => cloudinary_url($dpsParse->superstar_file_hash, [
                                    'secure' => true,
                                    'width' => 800,
                                    'height' => 800,
                                    'gravity' => 'auto:classic',
                                    'crop' => 'fill'
                                ])
                            ],
                        ],
                    ]),
                ]

            ],
        ];

        $idsOfCreatedDiscordMessages = [];
        foreach ($params as $param) {
            $response = $discordClient->post('channels/' . self::DISCORD_TEST_CHANNEL_ID . '/messages', $param);
            $bodyDecoded = json_decode($response->getBody()->getContents(), JSON_OBJECT_AS_ARRAY);
            $idsOfCreatedDiscordMessages[] = $bodyDecoded['id'];
        }
        $dpsParse->discord_notification_message_ids = implode(',', $idsOfCreatedDiscordMessages);
        $dpsParse->save();

        return true;
    }

    /**
     * @param int $char
     *
     * @return Character
     */
    private function getCharacter(int $char): Character
    {
        return Character::whereId($char)->first();
    }

    /**
     * @param string $commaSeparatedSetIds
     *
     * @return \App\Models\EquipmentSet[]
     */
    private function getGearSets(string $commaSeparatedSetIds): iterable
    {
        return EquipmentSet::whereIn('id', explode(',', $commaSeparatedSetIds))->get();
    }
}
