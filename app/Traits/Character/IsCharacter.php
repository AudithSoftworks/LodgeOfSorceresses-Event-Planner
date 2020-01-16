<?php

namespace App\Traits\Character;

use App\Models\Character;

trait IsCharacter
{
    public function parseCharacterSets(Character $character): array
    {
        app('cache.store')->has('sets'); // Trigger Recache listener.
        $sets = app('cache.store')->get('sets');

        $characterSets = array_filter($sets, static function ($key) use ($character) {
            return in_array($key, explode(',', $character->sets), false);
        }, ARRAY_FILTER_USE_KEY);

        return array_values($characterSets);
    }

    public function parseCharacterSkills(Character $character): array
    {
        app('cache.store')->has('skills'); // Trigger Recache listener.
        $skills = app('cache.store')->get('skills');

        $characterSkills = array_filter($skills, static function ($key) use ($character) {
            return in_array($key, explode(',', $character->skills), false);
        }, ARRAY_FILTER_USE_KEY);

        return array_values($characterSkills);
    }
}
