<?php namespace App\Http\Controllers\Auth;

use App\Events\User\Updated;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\Users\IsUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class UsersController extends Controller
{
    use IsUser;

    /**
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(): JsonResponse
    {
        $this->authorize('user', User::class);
        $users = User::with('linkedAccounts')
            ->whereHas('linkedAccounts', static function (Builder $query) {
                $query->where('remote_provider', '=', 'discord')->whereNotNull('remote_secondary_groups');
            })
            ->orderBy('name')
            ->get();
        foreach ($users as &$user) {
            $this->parseLinkedAccounts($user);
        }

        return response()->json($users);
    }

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
        $me->loadMissing('linkedAccounts');
        $this->parseLinkedAccounts($me);

        return response()->json($me);
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function updateMe(Request $request): JsonResponse
    {
        $this->authorize('user', User::class);
        $validatorErrorMessages = [
            'name.required' => 'ESO ID can\'t be empty!',
        ];
        $validator = app('validator')->make($request->all(), [
            'name' => 'required|string',
        ], $validatorErrorMessages);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        /** @var \App\Models\User $me */
        $me = app('auth.driver')->user();
        $me->name = ltrim($request->get('name'), '@');
        $me->save();

        app('events')->dispatch(new Updated($me));

        return response()->json([], JsonResponse::HTTP_NO_CONTENT);
    }
}
