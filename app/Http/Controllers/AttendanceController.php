<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\JsonResponse;

class AttendanceController extends Controller
{
    /**
     * @param int $userId
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(int $userId): JsonResponse
    {
        $this->authorize('user', User::class);

        $query = Attendance::query()
//            ->where('created_at', '>=', new CarbonImmutable('2 months ago Monday'))
        ;
        $attendances = $query->whereHas('attendees', static function (Builder $query) use ($userId) {
            $query->where('user_id', $userId);
        })->with('attendees')->orderBy('created_at')->get();
        foreach ($attendances as $attendance) {
            $attendance->gallery_image_ids = !empty($attendance->gallery_image_ids)
                ? explode(',', $attendance->gallery_image_ids)
                : [];
            foreach ($attendance->attendees as $attendee) {
                $attendee->makeHidden(['email', 'created_at', 'updated_at', 'deleted_at', 'attendees']);
            }
        }

        return response()->json($attendances);
    }
}
