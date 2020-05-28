<?php

namespace App\Traits\Team;

use App\Models\Team;
use Illuminate\Database\Eloquent\Builder;

trait IsTeam
{
    public function parseCreatedBy(Team $team): void
    {
        $cacheStore = app('cache.store');
        $cacheStore->has('user-' . $team->created_by); // Recache trigger.
        $createdBy = $cacheStore->get('user-' . $team->created_by);
        $team->setRelation('createdBy', $createdBy);
    }

    public function parseLedBy(Team $team): void
    {
        $cacheStore = app('cache.store');
        $cacheStore->has('user-' . $team->led_by); // Recache trigger.
        $ledBy = $cacheStore->get('user-' . $team->led_by);
        $team->setRelation('ledBy', $ledBy);
    }

    public function parseMembers(Team $team): void
    {
        $cacheStore = app('cache.store');
        $newMemberList = collect();
        $team
            ->with(['members'])
            ->whereHas('members', static function (Builder $queryTeamMembers) {
                $queryTeamMembers
                    ->orderBy('name')
                    ->with('owner')
                    ->whereHas('owner', static function (Builder $queryUser) {
                        $queryUser->orderBy('name');
                    });
            });
        foreach ($team->members as $character) {
            $cacheStore->has('character-' . $character->id); // Recache trigger.
            $cachedCharacter = $cacheStore->get('character-' . $character->id);
            if ($cachedCharacter !== null) { // Characters of deleted users might become unfetchable, thus NULL.
                /** @var \App\Models\Character $cachedCharacter */
                $cachedCharacter->setRelation('teamMembership', $character->teamMembership);
                $newMemberList->add($cachedCharacter);
            }
        }
        $team->setRelation('members', $newMemberList);
    }
}
