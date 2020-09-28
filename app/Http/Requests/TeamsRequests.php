<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TeamsRequests extends FormRequest
{
    public function getValidator(): Validator
    {
        return $this->validator;
    }

    public function authorize(Request $request): bool
    {
        $requestMethod = $request->getMethod();

        return $requestMethod === 'POST' || $requestMethod === 'DELETE'
            ? Gate::allows('is-admin')
            : Gate::allows('has-app-access');
    }

    public function rules(Request $request): array
    {
        $discordChannelIds = collect(app('discord.api')->getGuildChannels())->implode('id', ',');
        $discordRoleIds = collect(app('discord.api')->getGuildRoles())
            ->reject(static function ($item) {
                return $item['hoist'] === true || $item['mentionable'] === false;
            })
            ->implode('id', ',');

        $requestMethod = $request->getMethod();
        if ($requestMethod === 'POST') {
            return [
                'name' => 'required|string',
                'tier' => 'required|integer|between:1,4',
                'discord_role_id' => 'required|string|numeric|in:' . $discordRoleIds,
                'discord_lobby_channel_id' => 'required_with:discord_rant_channel_id|nullable|string|numeric|in:' . $discordChannelIds,
                'discord_rant_channel_id' => 'required_with:discord_lobby_channel_id|nullable|string|numeric|in:' . $discordChannelIds,
                'led_by' => 'required|numeric|exists:users,id',
            ];
        }
        if ($requestMethod === 'PUT') {
            return [
                'name' => 'sometimes|required|string',
                'tier' => 'sometimes|required|integer|between:1,4',
                'discord_role_id' => 'sometimes|required|string|numeric|in:' . $discordRoleIds,
                'discord_lobby_channel_id' => 'required_with:discord_rant_channel_id|nullable|string|numeric|in:' . $discordChannelIds,
                'discord_rant_channel_id' => 'required_with:discord_lobby_channel_id|nullable|string|numeric|in:' . $discordChannelIds,
                'led_by' => 'sometimes|required|numeric|exists:users,id',
            ];
        }

        return [];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Team name is required.',
            'tier.required' => 'Choose a tier for the content this team is specifialized in.',
            'tier.between' => 'Tier must be from 1 to 4.',
            'discord_role_id.required' => 'Discord Role-ID is required.',
            'discord_role_id.in' => 'Discord Role-ID isn\'t valid.',
            'discord_role_id.numeric' => 'Discord Role-ID needs to be a numeric value.',
            'discord_lobby_channel_id.required_with' => 'Discord Lobby Channel ID is required if Discord Rant Channel ID is present.',
            'discord_lobby_channel_id.in' => 'Discord Lobby Channel ID isn\'t valid.',
            'discord_rant_channel_id.required_with' => 'Discord Rant Channel ID is required if Discord Lobby Channel ID is present.',
            'discord_rant_channel_id.in' => 'Discord Rant Channel ID isn\'t valid.',
            'led_by.required' => 'Choose a team leader.',
            'led_by.numeric' => 'Team Leader needs to be a numeric value.',
        ];
    }
}
