<?php

namespace App\Traits\User;

use App\Models\User;
use App\Services\GuildRanksAndClearance;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;

trait IsUser
{
    public function parseLinkedAccounts(User $user): void
    {
        $linkedAccountsParsed = $user->linkedAccounts->keyBy('remote_provider');
        foreach ($linkedAccountsParsed as $linkedAccount) {
            $linkedAccount->remote_secondary_groups = !empty($linkedAccount->remote_secondary_groups)
                ? explode(',', $linkedAccount->remote_secondary_groups)
                : [];
        }
        $user->linkedAccountsParsed = $linkedAccountsParsed;
        $user->isMember = Gate::forUser($user)->allows('is-member');
        $user->isSoulshriven = Gate::forUser($user)->allows('is-soulshriven');
        $user->isAdmin = Gate::forUser($user)->allows('is-admin');
        $user->makeVisible(['linkedAccountsParsed', 'isMember', 'isSoulshriven', 'isAdmin']);
        $user->makeHidden(['linkedAccounts']);
    }

    public function parseCharacters(User $user): void
    {
        $newCharacterList = collect();
        foreach ($user->characters as $character) {
            Cache::has('character-' . $character->id);
            $newCharacterList->add(Cache::get('character-' . $character->id));
        }
        $user->setRelation('characters', $newCharacterList);
    }

    public function calculateUserRank(User $user): void
    {
        $clearanceLevel = app('guild.ranks.clearance')->calculateClearanceLevelOfUser($user);
        $user->clearanceLevel = GuildRanksAndClearance::CLEARANCE_LEVELS[$clearanceLevel] ?? null;
        $user->makeVisible('clearanceLevel');
    }
}
