<?php namespace App\Http\Controllers\Auth;

use App\Events\User\NameUpdated;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\User\IsUser;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
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
        if (!Auth::check()) {
            return response()->json([], JsonResponse::HTTP_UNAUTHORIZED);
        }

        /** @var \App\Models\User $me */
        $me = Auth::user();
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

    /**
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Exception
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteMe(): JsonResponse
    {
        $this->authorize('limited', User::class);
        if (Gate::allows('is-member') || Gate::allows('is-soulshriven')) {
            throw new AuthorizationException('Members & Soulshriven can\'t delete their accounts! Please contact the guild leader for that.');
        }

        /** @var \App\Models\User $me */
        $me = Auth::user();
        $me->forceDelete();

        return response()->json([], JsonResponse::HTTP_NO_CONTENT);
    }
}
