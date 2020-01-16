<?php

namespace App\Http\Controllers\Admin;

use App\Events\DpsParse\DpsParseApproved;
use App\Events\DpsParse\DpsParseDisapproved;
use App\Http\Controllers\Controller;
use App\Models\DpsParse;
use App\Models\User;
use App\Singleton\ClassTypes;
use App\Singleton\RoleTypes;
use App\Traits\Character\HasOrIsDpsParse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DpsParsesController extends Controller
{
    use HasOrIsDpsParse;

    /**
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(): JsonResponse
    {
        $this->authorize('admin', User::class);
        $dpsParses = DpsParse::query()
            ->with(['owner', 'character'])
            ->whereHas('owner', static function (Builder $queryToGetOauthAccounts) {
                $queryToGetOauthAccounts
                    ->whereNotNull('name')
                    ->whereHas('linkedAccounts', static function (Builder $queryToGetDiscordOauthAccounts) {
                        $queryToGetDiscordOauthAccounts->where('remote_provider', '=', 'discord');
                    });
            })
            ->whereNull('processed_by')
            ->orderBy('id')
            ->get();
        foreach ($dpsParses as $dpsParse) {
            $dpsParse->sets = $this->parseDpsParseSets($dpsParse);
            $this->parseScreenshotFiles($dpsParse);
            is_int($dpsParse->character->role) && $dpsParse->character->role = RoleTypes::getShortRoleText($dpsParse->character->role);
            is_int($dpsParse->character->class) && $dpsParse->character->class = ClassTypes::getClassName($dpsParse->character->class);
        }

        return response()->json($dpsParses);
    }

    /**
     * @param int $parseId
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(int $parseId): JsonResponse
    {
        $this->authorize('admin', User::class);

        /** @var \App\Models\User $me */
        $me = app('auth.driver')->user();

        $dpsParse = DpsParse::query()
            ->with(['character'])
            ->whereId($parseId)
            ->whereNull('processed_by')
            ->first();

        if (!$dpsParse) {
            throw new ModelNotFoundException('Parse not found!');
        }

        $dpsParse->processed_by = $me->id;
        $dpsParse->save();

        app('events')->dispatch(new DpsParseApproved($dpsParse));

        return response()->json(['message' => 'Parse approved.']);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param int                      $parseId
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Exception
     */
    public function destroy(Request $request, int $parseId): JsonResponse
    {
        $this->authorize('admin', User::class);

        /** @var \App\Models\User $me */
        $me = app('auth.driver')->user();

        $dpsParse = DpsParse::query()
            ->with(['character'])
            ->whereId($parseId)
            ->first();
        if (!$dpsParse) {
            throw new ModelNotFoundException('Parse not found!');
        }

        $dpsParse->reason_for_disapproval = $request->get('reason_for_disapproval');
        $dpsParse->processed_by = $me->id;
        $dpsParse->save();

        $dpsParse->delete();

        app('events')->dispatch(new DpsParseDisapproved($dpsParse));

        return response()->json([], JsonResponse::HTTP_NO_CONTENT);
    }
}
