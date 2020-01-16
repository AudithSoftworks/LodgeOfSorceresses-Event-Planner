<?php

namespace App\Traits\User;

use App\Models\User;
use App\Services\DiscordApi;
use App\Services\GuildRanksAndClearance;

trait IsUser
{
    public function parseLinkedAccounts(User $user): void
    {
        /** @var \App\Models\UserOAuth[] $linkedAccountsParsed */
        $linkedAccountsParsed = $user->linkedAccounts->keyBy('remote_provider');
        $user->isMember = $user->isSoulshriven = false;
        foreach ($linkedAccountsParsed as $linkedAccount) {
            if (!empty($linkedAccount->remote_secondary_groups)) {
                $linkedAccount->remote_secondary_groups = explode(',', $linkedAccount->remote_secondary_groups);
                if ($linkedAccount->remote_provider === 'discord') {
                    $user->isMember = in_array(DiscordApi::ROLE_MEMBERS, $linkedAccount->remote_secondary_groups, true);
                    $user->isSoulshriven = in_array(DiscordApi::ROLE_SOULSHRIVEN, $linkedAccount->remote_secondary_groups, true);
                }
            }
        }
        $user->linkedAccountsParsed = $linkedAccountsParsed;
        $user->makeVisible(['linkedAccountsParsed', 'isMember', 'isSoulshriven']);
        $user->makeHidden(['linkedAccounts']);
    }

    public function parseCharacters(User $user): void
    {
        $cacheStore = app('cache.store');
        $newCharacterList = collect();
        foreach ($user->characters as $character) {
            $cacheStore->has('character-' . $character->id);
            $newCharacterList->add($cacheStore->get('character-' . $character->id));
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
