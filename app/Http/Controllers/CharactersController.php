<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\File;
use App\Models\User;
use App\Singleton\ClassTypes;
use App\Singleton\RoleTypes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;
use UnexpectedValueException;

class CharactersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(): JsonResponse
    {
        $this->authorize('user', User::class);
        $characters = Character::query()
            ->with([
                'owner',
                'dpsParses' => static function (HasMany $query) {
                    $query->whereNotNull('processed_by');
                },
                'content'
            ])
            ->orderBy('id', 'desc')
            ->get();
        if ($characters->count()) {
            app('cache.store')->has('sets'); // Trigger Recache listener.
            $sets = app('cache.store')->get('sets');
            app('cache.store')->has('skills'); // Trigger Recache listener.
            $skills = app('cache.store')->get('skills');
            foreach ($characters as $character) {
                $character->class = ClassTypes::getClassName($character->class);
                $character->role = RoleTypes::getShortRoleText($character->role);

                $characterSets = array_filter($sets, static function ($key) use ($character) {
                    return in_array($key, explode(',', $character->sets), false);
                }, ARRAY_FILTER_USE_KEY);
                $character->sets = array_values($characterSets);

                $characterSkills = array_filter($skills, static function ($key) use ($character) {
                    return in_array($key, explode(',', $character->skills), false);
                }, ARRAY_FILTER_USE_KEY);
                $character->skills = array_values($characterSkills);

                foreach ($character->dpsParses as $dpsParse) {
                    $setsUsedInDpsParse = array_filter($sets, static function ($key) use ($dpsParse) {
                        return in_array($key, explode(',', $dpsParse->sets), false);
                    }, ARRAY_FILTER_USE_KEY);
                    $dpsParse->sets = array_values($setsUsedInDpsParse);

                    $parseFile = File::whereHash($dpsParse->parse_file_hash)->first();
                    $superstarFile = File::whereHash($dpsParse->superstar_file_hash)->first();
                    if (!$parseFile || !$superstarFile) {
                        throw new UnexpectedValueException('Couldn\'t find screenshot file records!');
                    }
                    $dpsParse->parse_file_hash = app('filestream')->url($parseFile);
                    $dpsParse->superstar_file_hash = app('filestream')->url($superstarFile);
                }
            }
        }

        return response()->json($characters);
    }

    /**
     * Display the specified resource.
     *
     * @param int $char
     *
     * @return JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(int $char): JsonResponse
    {
        $this->authorize('user', User::class);
        $character = Character::query()
            ->with([
                'dpsParses' => static function (HasMany $query) {
                    $query->whereNotNull('processed_by');
                },
                'content',
                'owner'
            ])
            ->whereId($char)
            ->first();
        if (!$character) {
            return response()->json(['message' => 'Character not found!'])->setStatusCode(404);
        }

        $character->class = ClassTypes::getClassName($character->class);
        $character->role = RoleTypes::getRoleName($character->role);

        app('cache.store')->has('sets'); // Trigger Recache listener.
        $sets = app('cache.store')->get('sets');
        $charactersSets = array_filter($sets, static function ($key) use ($character) {
            return in_array($key, explode(',', $character->sets), false);
        }, ARRAY_FILTER_USE_KEY);
        $character->sets = array_values($charactersSets);

        app('cache.store')->has('skills'); // Trigger Recache listener.
        $skills = app('cache.store')->get('skills');
        $charactersSkills = array_filter($skills, static function ($key) use ($character) {
            return in_array($key, explode(',', $character->skills), false);
        }, ARRAY_FILTER_USE_KEY);
        $character->skills = array_values($charactersSkills);

        foreach ($character->dpsParses as $dpsParse) {
            $setsUsedInDpsParse = array_filter($sets, static function ($key) use ($dpsParse) {
                return in_array($key, explode(',', $dpsParse->sets), false);
            }, ARRAY_FILTER_USE_KEY);
            $dpsParse->sets = array_values($setsUsedInDpsParse);

            $parseFile = File::whereHash($dpsParse->parse_file_hash)->first();
            $superstarFile = File::whereHash($dpsParse->superstar_file_hash)->first();
            if (!$parseFile || !$superstarFile) {
                throw new UnexpectedValueException('Couldn\'t find screenshot file records!');
            }
            $dpsParse->parse_file_hash = app('filestream')->url($parseFile);
            $dpsParse->superstar_file_hash = app('filestream')->url($superstarFile);
        }

        return response()->json($character);
    }
}
