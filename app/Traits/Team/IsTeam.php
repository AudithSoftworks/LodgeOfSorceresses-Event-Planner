<?php

namespace App\Traits\Team;

use App\Models\Team;

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
        foreach ($team->members as $member) {
            $cacheStore->has('user-' . $member->id); // Recache trigger.
            $newMemberList->add($cacheStore->get('user-' . $member->id));
        }
        $team->setRelation('members', $newMemberList);
    }
}
