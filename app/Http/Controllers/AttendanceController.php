<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use App\Traits\Attendance\IsAttendance;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    use IsAttendance;

    /**
     * @param \Illuminate\Http\Request $request
     * @param int $userId
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Exception
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(Request $request, int $userId): JsonResponse
    {
        $this->authorize('user', User::class);

        $firstEverAttendance = Attendance::query()
            ->select('attendances.*')
            ->whereHas('attendees', static function (Builder $query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->orderBy('created_at')
            ->first();

        $query = Attendance::query()
            ->select('attendances.*')
            ->whereHas('attendees', static function (Builder $query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->with('attendees')
            ->orderBy('created_at', 'desc');
        if ($request->has('b') && preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\+\d{2}:\d{2}$/', $offsetDate = $request->get('b'))) {
            $offsetDate = (new CarbonImmutable($offsetDate));
            $attendances = new EloquentCollection();
            if ($firstEverAttendance !== null) {
                while ($attendances->count() === 0) {
                    $offsetDate = $offsetDate->subWeek();
                    $startOfOffsetDatesWeek = $offsetDate->copy()->startOfWeek();
                    $endOfOffsetDatesWeek = $offsetDate->copy()->endOfWeek();
                    if ($endOfOffsetDatesWeek->isBefore($firstEverAttendance->created_at)) {
                        break;
                    }
                    $tempQuery = clone $query;
                    $tempQuery->where('created_at', '>=', $startOfOffsetDatesWeek);
                    $tempQuery->where('created_at', '<=', $endOfOffsetDatesWeek);
                    $attendances = $tempQuery->get();
                    if ($attendances->count() > 0) {
                        break;
                    }
                }
            }
        } else {
            $query->where('created_at', '>=', new CarbonImmutable('3 weeks ago Monday'));
            $attendances = $query->get();
        }
        if ($attendances->count()) {
            foreach ($attendances as $attendance) {
                $this->parseAttendanceGalleryImages($attendance);
                $this->parseAttendanceAttendees($attendance);
            }
        }

        return response()
            ->json($attendances)
            ->header('X-First-Attendance-Date', $firstEverAttendance ? $firstEverAttendance->created_at->toISOString() : null);
    }
}
