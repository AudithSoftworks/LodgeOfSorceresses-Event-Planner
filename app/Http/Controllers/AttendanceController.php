<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
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

        $attendances = Attendance::query()
            ->select('attendances.*')
            ->whereHas('attendees', static function (Builder $query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->with('attendees')
            ->orderBy('created_at')
            ->get();
        if ($attendances->count()) {
            $ipsApi = app('ips.api');
            foreach ($attendances as $attendance) {
                $attendance->gallery_image_ids = !empty($attendance->gallery_image_ids)
                    ? explode(',', $attendance->gallery_image_ids)
                    : [];
                $images = [];
                foreach ($attendance->gallery_image_ids as $galleryImageId) {
                    $imageData = $ipsApi->getGalleryImage($galleryImageId);
                    $images[] = $imageData['images'];
                }
                $attendance->setAttribute('gallery_images', $images);
                $attendance->makeVisible(['gallery_images']);
                $attendance->makeHidden(['gallery_image_ids']);

                foreach ($attendance->attendees as $attendee) {
                    $attendee->makeHidden(['email', 'created_at', 'updated_at', 'deleted_at', 'attendees']);
                    if (($attendance->created_by ?? null) === null && $attendee->getOriginal('pivot_is_author')) {
                        $attendance->setAttribute('created_by', $attendee);
                        $attendance->makeVisible(['created_by']);
                    }
                }
            }
        }

        return response()->json($attendances);
    }
}
