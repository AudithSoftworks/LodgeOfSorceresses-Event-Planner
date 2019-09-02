<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\User;
use App\Singleton\ClassTypes;
use App\Singleton\RoleTypes;
use App\Traits\Characters\HasDpsParse;
use App\Traits\Characters\IsCharacter;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;

class CharactersController extends Controller
{
    use IsCharacter, HasDpsParse;

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
            foreach ($characters as $character) {
                $character->class = ClassTypes::getClassName($character->class);
                $character->role = RoleTypes::getShortRoleText($character->role);
                $character->sets = $this->parseCharacterSets($character);
                $character->skills = $this->parseCharacterSkills($character);

                foreach ($character->dpsParses as $dpsParse) {
                    $dpsParse->sets = $this->parseDpsParseSets($dpsParse);
                    $this->parseScreenshotFiles($dpsParse);
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
        $this->authorize('limited', User::class);
        $character = Character::query()
            ->with([
                'dpsParses' => static function (HasMany $query) {
                    $query->whereNotNull('processed_by')->orderBy('id', 'desc');
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

        $character->sets = $this->parseCharacterSets($character);
        $character->skills = $this->parseCharacterSkills($character);

        foreach ($character->dpsParses as $dpsParse) {
            $dpsParse->sets = $this->parseDpsParseSets($dpsParse);
            $this->parseScreenshotFiles($dpsParse);
        }

        return response()->json($character);
    }
}
