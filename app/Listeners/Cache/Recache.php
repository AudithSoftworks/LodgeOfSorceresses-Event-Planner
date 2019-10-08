<?php namespace App\Listeners\Cache;

use App\Models\Character;
use App\Models\Content;
use App\Models\Set;
use App\Models\Skill;
use App\Models\User;
use App\Singleton\ClassTypes;
use App\Singleton\RoleTypes;
use App\Traits\Characters\HasOrIsDpsParse;
use App\Traits\Characters\IsCharacter;
use App\Traits\Users\IsUser;
use Illuminate\Cache\Events\CacheMissed;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Recache
{
    use HasOrIsDpsParse, IsCharacter, IsUser;

    /**
     * @param \Illuminate\Cache\Events\CacheMissed $event
     *
     * @return mixed
     * @throws \Exception
     */
    public function handle(CacheMissed $event)
    {
        $key = $event->key;
        switch (true) {
            case $key === 'sets':
                $recache = [
                    'data' => Set::query()->get()->keyBy('id')->toArray(),
                    'ttl' => Set::CACHE_TTL
                ];
                break;
            case $key === 'skills':
                $recache = [
                    'data' => Skill::query()->get()->keyBy('id')->toArray(),
                    'ttl' => Set::CACHE_TTL
                ];
                break;
            case $key === 'content':
                $recache = [
                    'data' => Content::query()->orderBy('tier')->get()->keyBy('id')->toArray(),
                    'ttl' => Content::CACHE_TTL
                ];
                break;
            case strpos($key, 'user-') === 0:
                $userId = preg_replace('/\D/', '', $key);
                $recache = [
                    'data' => !empty($userId) ? $this->getUser((int)$userId) : [],
                    'ttl' => null,
                ];
                break;
            case strpos($key, 'character-') === 0:
                $characterId = preg_replace('/\D/', '', $key);
                $recache = [
                    'data' => !empty($characterId) ? $this->getCharacter((int)$characterId) : [],
                    'ttl' => null,
                ];
                break;
        }
        if (empty($recache)) {
            return null;
        }

        app('cache.store')->put($key, $recache['data'], $recache['ttl']);

        return $recache['data'];
    }

    private function getUser(int $userId): ?User
    {
        $user = User::with([
            'linkedAccounts' => static function (HasMany $query) {
                $query->where('remote_provider', '=', 'discord')->whereNotNull('remote_secondary_groups');
            },
            'characters'
        ])->whereNotNull('name')->find($userId);
        $this->parseLinkedAccounts($user);
        $this->parseCharacters($user);
        $this->calculateUserRank($user);

        return $user;
    }

    private function getCharacter(int $characterId): ?Character
    {
        $character = Character::query()
            ->with([
                'dpsParses' => static function (HasMany $query) {
                    $query->orderBy('id', 'desc');
                },
                'content',
                'owner'
            ])
            ->whereId($characterId)
            ->first();
        if ($character) {
            $character->class = ClassTypes::getClassName($character->class);
            $character->role = RoleTypes::getShortRoleText($character->role);
            $character->sets = $this->parseCharacterSets($character);
            $character->skills = $this->parseCharacterSkills($character);
            $this->processDpsParses($character);

            return $character;
        }

        return null;
    }
}
