<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;

class FetchSetsFromEsoSetsDotCom extends Command
{
    /**
     * @var string
     */
    protected $signature = 'crawl:eso-sets';

    /**
     * @var string
     */
    protected $description = 'Crawls eso-sets.com and fetches set names and their ids.';

    /**
     * @var \GuzzleHttp\Client
     */
    private $client;

    /**
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->client = new Client([
            'base_uri' => 'https://eso-sets.com/set/',
            'timeout' => 2.0,
        ]);
    }

    public function handle(): void
    {
        $data = [];
        for ($i = 1; $i < 500; $i++) {
            try {
                $response = $this->client->request('GET', "$i");
            } catch (GuzzleException $e) {
                break;
            }
            if ($response->getStatusCode() === 200) {
                $body = $response->getBody()->getContents();
                preg_match(preg_quote('#<meta property="og:title" content="') . '(.*)' . preg_quote('"/>') . '#i', $body, $setNameMatches);
                preg_match(preg_quote('#<meta property="og:url" content="https://eso-sets.com/set/') . '(.*)' . preg_quote('"/>') . '#i', $body, $setSlugMatches);
                if (!empty($setNameMatches)) {
                    $setName = str_replace('’', '\'', $setNameMatches[1]);
                    if (($doWeHaveIrregularCharacters = preg_match("#^[a-z\'\s]+$#i", $setName, $a)) === 0 || $doWeHaveIrregularCharacters === false) {
                        $setName = mb_convert_encoding($setName, 'UTF-8', 'HTML-ENTITIES');
                    }

                    $data[] = ['id' => $i, 'name' => $setName, 'slug' => $setSlugMatches[1]];
                }
            }
        }
        $this->syncSets($data);
        $this->info('Sets succesfully synced!');
    }

    /**
     * @param array $data
     */
    private function syncSets(array $data): void
    {
        app('db.connection')->table('equipment_sets')->truncate();
        app('db.connection')->table('equipment_sets')->insert($data);
        app('db.connection')->table('equipment_sets')->update(['created_at' => Carbon::now()]);
    }
}
