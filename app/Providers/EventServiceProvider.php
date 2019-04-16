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

        Events\Users\LoggedIn::class => [],
        Events\Users\LoggedInViaIpsOauth::class => [
            Listeners\User\UpdateOauthUserDataViaIpsApi::class,
            Listeners\User\UpdateOauthUserDataViaDiscordApi::class,
        ],
        Events\Users\LoggedOut::class => [],
        Events\Users\Registered::class => [],
        Events\Users\RequestedActivationLink::class => [],
        Events\Users\RequestedResetPasswordLink::class => [],

        Events\DpsParses\DpsParseSubmitted::class => [
            Listeners\DpsParses\PostNewDpsParseToDiscord::class
        ],
        Events\DpsParses\DpsParseDeleted::class => [
            Listeners\DpsParses\DeleteDiscordMessagesWhenDpsParseIsDeleted::class
        ],
        Events\DpsParses\DpsParseApproved::class => [
            Listeners\DpsParses\ProcessDpsParse::class,
            Listeners\DpsParses\RerankPlayerOnIpsAndDiscord::class,
            Listeners\DpsParses\AnnounceDpsApprovalOnDiscord::class,
        ],
        Events\DpsParses\DpsParseDisapproved::class => [
            Listeners\DpsParses\PostAnnouncementToDiscordRegardingDpsDisapproval::class
        ],

        Events\Files\Uploaded::class => [
            Listeners\Files\ValidateUploadRealMimeAgainstAllowedTypes::class,
            Listeners\Files\PersistUploadedFile::class
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
