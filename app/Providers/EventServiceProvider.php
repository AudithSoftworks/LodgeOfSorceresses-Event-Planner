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
            Listeners\DpsParse\DeleteDiscordMessagesWhenCharacterIsDeleting::class
        ],
        Events\Character\CharacterDeleted::class => [
            Listeners\Cache\DeleteCharacterCache::class,
            Listeners\RerankPlayerOnIpsAndDiscord::class
        ],
        Events\Character\CharacterDemoted::class => [
            Listeners\Cache\DeleteCharacterCache::class,
            Listeners\RerankPlayerOnIpsAndDiscord::class
        ],
        Events\Character\CharacterPromoted::class => [
            Listeners\Cache\DeleteCharacterCache::class,
            Listeners\RerankPlayerOnIpsAndDiscord::class
        ],
        Events\Character\CharacterUpdated::class => [
            Listeners\Cache\DeleteCharacterCache::class,
        ],

        Events\DpsParse\DpsParseSubmitted::class => [
            Listeners\Cache\DeleteCharacterCache::class,
            Listeners\DpsParse\PostNewDpsParseToDiscord::class
        ],
        Events\DpsParse\DpsParseDeleted::class => [
            Listeners\Cache\DeleteCharacterCache::class,
            Listeners\DpsParse\DeleteDiscordMessagesWhenDpsParseIsDeleted::class
        ],
        Events\DpsParse\DpsParseApproved::class => [
            Listeners\Cache\DeleteCharacterCache::class,
            Listeners\DpsParse\ProcessDpsParse::class,
            Listeners\RerankPlayerOnIpsAndDiscord::class,
            Listeners\DpsParse\AnnounceDpsApprovalOnDiscord::class,
        ],
        Events\DpsParse\DpsParseDisapproved::class => [
            Listeners\Cache\DeleteCharacterCache::class,
            Listeners\DpsParse\AnnounceDpsDisapprovalOnDiscord::class,
        ],

        Events\File\Uploaded::class => [
            Listeners\File\ValidateUploadRealMimeAgainstAllowedTypes::class,
            Listeners\File\PersistUploadedFile::class
        ],

        Events\User\LoggedIn::class => [
            Listeners\User\UpdateOauthUserData::class,
        ],
        Events\User\LoggedOut::class => [],
        Events\User\Registered::class => [],
        Events\User\Updated::class => [
            Listeners\User\UpdateDiscordAndForumNames::class,
        ],
    ];

    /**
     * Register any other events for your application.
     *
     * @return void
     */
    public function boot(): void
    {
        parent::boot();

        //
    }
}
