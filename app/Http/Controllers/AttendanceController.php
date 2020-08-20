<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use App\Traits\Attendance\IsAttendance;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

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

        $validator = Validator::make($request->all(), [
            'b' => 'sometimes|date_format:' . DateTimeInterface::ATOM,
        ], [
            'b.date_format' => '"b" parameter needs to be in ISODate format.',
        ]);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

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
        if ($request->has('b')) {
            $offsetDate = (new CarbonImmutable($request->get('b')));
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
