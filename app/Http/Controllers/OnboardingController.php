<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\DiscordApi;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class OnboardingController extends Controller
{
    private const CMS_CONTENT_MAP_BY_STEPS = [
        'members' => [
            1 => [1912],
            2 => [12160],
            3 => [10722],
            4 => [10399],
        ],
        'soulshriven' => [
            1 => [10722],
            2 => [10741],
            3 => [10734],
        ],
    ];

    /**
     * @param int $step
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCmsContentByStepForMemberOnboarding(int $step): JsonResponse
    {
        $this->authorize('limited', User::class);

        if (!array_key_exists($step, self::CMS_CONTENT_MAP_BY_STEPS['members'])) {
            throw new ModelNotFoundException();
        }

        $posts = $this->getCmsContent(self::CMS_CONTENT_MAP_BY_STEPS['members'][$step]);

        return response()->json($posts);
    }

    /**
     * @param int $step
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCmsContentByStepForSoulshrivenOnboarding(int $step): JsonResponse
    {
        $this->authorize('limited', User::class);

        if (!array_key_exists($step, self::CMS_CONTENT_MAP_BY_STEPS['soulshriven'])) {
            throw new ModelNotFoundException();
        }

        $posts = $this->getCmsContent(self::CMS_CONTENT_MAP_BY_STEPS['soulshriven'][$step]);

        return response()->json($posts);
    }

    /**
     * @param string[] $postIds
     *
     * @return string[][]
     */
    private function getCmsContent(array $postIds): array
    {
        $ipsApi = app('ips.api');
        $posts = [];
        foreach ($postIds as $postId) {
            $dataFromApi = $ipsApi->getPost($postId);
            $posts[] = [
                'content' => $dataFromApi['content'],
                'url' => $dataFromApi['url'],
            ];
        }

        return $posts;
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Validation\ValidationException
     * @return \Illuminate\Http\JsonResponse
     */
    public function finalizeOnboarding(Request $request): JsonResponse
    {
        $this->authorize('limited', User::class);

        $validator = Validator::make($request->all(), [
            'mode' => 'required|in:members,soulshriven',
        ]);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $modeParam = $request->get('mode');
        if ($modeParam === 'members' && Gate::allows('is-member')) {
            throw new AuthorizationException('Current user is already a Member.');
        }
        if ($modeParam === 'soulshriven' && Gate::allows('is-soulshriven')) {
            throw new AuthorizationException('Current user is already a Soulshriven.');
        }

        /** @var \Illuminate\Contracts\Auth\Authenticatable|\App\Models\User $me */
        $me = Auth::user();
        app('guild.ranks.clearance')->refreshGivenUsersDiscordRoles(
            $me,
            $modeParam === 'members' ? DiscordApi::ROLE_MEMBERS : DiscordApi::ROLE_SOULSHRIVEN
        );
        $me->refresh();
        Cache::has('user-' . $me->id); // Recache trigger

        return response()->json(Cache::get('user-' . $me->id));
    }
}
