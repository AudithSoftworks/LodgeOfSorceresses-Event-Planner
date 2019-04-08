<?php namespace App\Providers;

use App\Events as Events;
use App\Listeners as Listeners;
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
        Events\DpsParses\DpsParseSubmitted::class => [
            Listeners\DpsParses\PostNewDpsParseToDiscord::class
        ],
        Events\DpsParses\DpsParseDeleted::class => [
            Listeners\DpsParses\DeleteDiscordMessagesWhenDpsParseIsDeleted::class
        ],
        Events\Files\Uploaded::class => [
            Listeners\Files\ValidateUploadRealMimeAgainstAllowedTypes::class,
            Listeners\Files\PersistUploadedFile::class
        ],
        Events\Users\LoggedIn::class => [],
        Events\Users\LoggedInViaIpsOauth::class => [
            Listeners\User\UpdateOauthUserDataViaIpsApi::class
        ],
        Events\Users\LoggedOut::class => [],
        Events\Users\Registered::class => [],
        Events\Users\RequestedActivationLink::class => [],
        Events\Users\RequestedResetPasswordLink::class => [],
        CacheMissed::class => [
            Listeners\Cache\Recache::class
        ]
    ];

    /**
     * Register any other events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
