<?php namespace App\Http\Controllers\Auth;

use App\Events\User\NameUpdated;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\User\IsUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UsersController extends Controller
{
    use IsUser;

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function me(): JsonResponse
    {
        $guard = app('auth.driver');

        if (!$guard->check()) {
            return response()->json([], JsonResponse::HTTP_UNAUTHORIZED);
        }

        /** @var \App\Models\User $me */
        $me = $guard->user();
        Cache::has('user-' . $me->id); // Recache trigger

        return response()->json(Cache::get('user-' . $me->id));
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Validation\ValidationException
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateMe(Request $request): JsonResponse
    {
        $this->authorize('user', User::class);
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
        ], [
            'name.required' => 'ESO ID can\'t be empty!',
        ]);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        /** @var \App\Models\User $me */
        $me = Auth::user();
        $me->name = ltrim($request->get('name'), '@');
        $me->save();

        Event::dispatch(new NameUpdated($me));
        Cache::has('user-' . $me->id); // Recache trigger

        return response()->json(Cache::get('user-' . $me->id));
    }
}
