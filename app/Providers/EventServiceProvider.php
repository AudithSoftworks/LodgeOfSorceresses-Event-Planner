<?php namespace App\Providers;

use App\Events;
use App\Listeners;
use Illuminate\Cache\Events\CacheMissed;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        CacheMissed::class => [
            Listeners\Cache\Recache::class
        ],

        Events\Character\CharacterDeleting::class => [
            Listeners\DpsParse\DeleteMessagesForDpsParseLogsOnDiscord::class
        ],
        Events\Character\CharacterDeleted::class => [
            Listeners\Cache\DeleteUserCache::class,
            Listeners\Cache\DeleteCharacterCache::class,
            Listeners\User\RerankPlayerOnDiscord::class
        ],
        Events\Character\CharacterDemoted::class => [
            Listeners\Cache\DeleteUserCache::class,
            Listeners\Cache\DeleteCharacterCache::class,
            Listeners\User\RerankPlayerOnDiscord::class
        ],
        Events\Character\CharacterPromoted::class => [
            Listeners\User\RerankPlayerOnDiscord::class
        ],
        Events\Character\CharacterSaved::class => [
            Listeners\Cache\DeleteUserCache::class,
            Listeners\Cache\DeleteCharacterCache::class,
        ],
        Events\Character\CharacterReset::class => [
            Listeners\Cache\DeleteUserCache::class,
            Listeners\Cache\DeleteCharacterCache::class,
            Listeners\User\RerankPlayerOnDiscord::class
        ],

        Events\DpsParse\DpsParseSubmitted::class => [
            Listeners\Cache\DeleteCharacterCache::class,
            Listeners\DpsParse\PostNewDpsParseToDiscord::class
        ],
        Events\DpsParse\DpsParseDeleted::class => [
            Listeners\Cache\DeleteCharacterCache::class,
            Listeners\DpsParse\DeleteMessagesForDpsParseLogsOnDiscord::class
        ],
        Events\DpsParse\DpsParseApproved::class => [
            Listeners\Cache\DeleteCharacterCache::class,
            Listeners\DpsParse\ProcessDpsParse::class,
            Listeners\User\RerankPlayerOnDiscord::class,
            Listeners\DpsParse\AnnounceDpsApprovalOnDiscord::class,
        ],
        Events\DpsParse\DpsParseDisapproved::class => [
            Listeners\Cache\DeleteCharacterCache::class,
            Listeners\DpsParse\AnnounceDpsDisapprovalOnDiscord::class,
        ],

        Events\Team\TeamDeleted::class => [
            Listeners\Cache\DeleteTeamCache::class,
        ],
        Events\Team\TeamUpdated::class => [
            Listeners\Cache\DeleteTeamCache::class,
        ],

        Events\File\Uploaded::class => [
            Listeners\File\ValidateUploadRealMimeAgainstAllowedTypes::class,
            Listeners\File\PersistUploadedFile::class
        ],

        Events\Team\MemberInvited::class => [
            Listeners\Cache\DeleteCharacterCache::class,
            Listeners\Cache\DeleteUserCache::class,
            Listeners\Team\DmMemberUponInvitationOnDiscord::class,
        ],
        Events\Team\MemberJoined::class => [
            Listeners\Cache\DeleteCharacterCache::class,
            Listeners\Cache\DeleteUserCache::class,
            Listeners\User\RerankPlayerOnDiscord::class,
            Listeners\Team\AnnounceMemberJoiningOnDiscord::class,
        ],
        Events\Team\MemberRemoved::class => [
            Listeners\Cache\DeleteCharacterCache::class,
            Listeners\Cache\DeleteUserCache::class,
            Listeners\User\RerankPlayerOnDiscord::class,
            Listeners\Team\AnnounceMemberRemovalOnDiscord::class,
        ],

        Events\User\LoggedIn::class => [
            Listeners\User\TrackUserLoginForSqreen::class,
        ],
        Events\User\LoggedOut::class => [],
        Events\User\Registered::class => [
            Listeners\User\TrackUserSignupForSqreen::class,
        ],
        Events\User\NameUpdated::class => [
            Listeners\User\UpdateDiscordAndForumNames::class,
            Listeners\Cache\DeleteUserCache::class,
        ],
        Events\User\OnboardingCompleted::class => [
            Listeners\User\AnnounceOnboardingCompletionOnDiscord::class,
        ],
    ];

    /**
     * Register any other events for your application.
     */
    public function boot(): void
    {
        //
    }
}
