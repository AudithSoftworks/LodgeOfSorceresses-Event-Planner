<?php namespace App\Listeners\Cache;

use App\Models\Character;
use App\Models\Content;
use App\Models\Set;
use App\Models\Skill;
use App\Models\Team;
use App\Models\User;
use App\Singleton\ClassTypes;
use App\Singleton\RoleTypes;
use App\Traits\Character\HasOrIsDpsParse;
use App\Traits\Character\IsCharacter;
use App\Traits\Team\IsTeam;
use App\Traits\User\IsUser;
use Illuminate\Cache\Events\CacheMissed;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Recache
{
    use HasOrIsDpsParse, IsCharacter, IsTeam, IsUser;

    /**
     * @param \Illuminate\Cache\Events\CacheMissed $event
     *
     * @throws \Exception
     * @return mixed
     */
    public function handle(CacheMissed $event)
    {
        $key = $event->key;
        switch (true) {
            case $key === 'sets':
                $recache = [
                    'data' => Set::query()->get()->keyBy('id')->toArray(),
                    'ttl' => Set::CACHE_TTL,
                ];
                break;
            case $key === 'skills':
                $recache = [
                    'data' => Skill::query()->get()->keyBy('id')->toArray(),
                    'ttl' => Set::CACHE_TTL,
                ];
                break;
            case $key === 'content':
                $recache = [
                    'data' => Content::query()->orderBy('tier')->get()->keyBy('id')->toArray(),
                    'ttl' => Content::CACHE_TTL,
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
            case strpos($key, 'team-') === 0:
                $teamId = preg_replace('/\D/', '', $key);
                $recache = [
                    'data' => !empty($teamId) ? $this->getTeam((int)$teamId) : [],
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
        /** @var User $user */
        $user = User::with(['linkedAccounts', 'characters'])->find($userId);
        if ($user !== null) {
            $user->makeHidden('email');
            $this->parseLinkedAccounts($user);
            $this->parseCharacters($user);
            $this->calculateUserRank($user);
        }

        return $user;
    }

    private function getCharacter(int $characterId): ?Character
    {
        $character = Character::query()
            ->with([
                'content',
                'dpsParses' => static function (HasMany $query) {
                    $query->orderBy('id', 'desc');
                },
                'owner',
                'teams' => static function (BelongsToMany $query) {
                    $query->wherePivot('status', true);
                },
            ])
            ->whereHas('owner', static function (Builder $query) {
                $query->whereNull('deleted_at');
            })
            ->whereId($characterId)
            ->first();
        if ($character) {
            $character->class = ClassTypes::getClassName($character->class);
            $character->role = RoleTypes::getShortRoleText($character->role);
            $character->sets = $this->parseCharacterSets($character);
            $character->skills = $this->parseCharacterSkills($character);
            $this->processDpsParses($character);
            unset($character->owner->email);

            return $character;
        }

        return null;
    }

    private function getTeam(int $teamId): ?Team
    {
        /** @var \App\Models\Team $team */
        $team = Team::query()
            ->with([
                'members' => static function (BelongsToMany $query) {
                    $query->orderBy('id', 'asc');
                },
                'ledBy',
            ])
            ->whereId($teamId)
            ->first();
        if ($team) {
            $this->parseCreatedBy($team);
            $this->parseLedBy($team);
            $this->parseMembers($team);
            $this->calculateUserRank($team->ledBy);
            $this->calculateUserRank($team->createdBy);

            return $team;
        }

        return null;
    }
}
