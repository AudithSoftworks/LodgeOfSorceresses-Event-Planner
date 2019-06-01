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

        Events\Character\CharacterPromoted::class => [],
        Events\Character\CharacterDemoted::class => [],
        Events\Character\CharacterDeleting::class => [
            Listeners\DpsParse\DeleteDpsParsesOfDeletingCharacter::class
        ],
        Events\Character\CharacterDeleted::class => [
            Listeners\Character\RerankPlayerOnIpsAndDiscord::class
        ],

        Events\DpsParse\DpsParseSubmitted::class => [
            Listeners\DpsParse\PostNewDpsParseToDiscord::class
        ],
        Events\DpsParse\DpsParseDeleted::class => [
            Listeners\DpsParse\DeleteDiscordMessagesWhenDpsParseIsDeleted::class
        ],
        Events\DpsParse\DpsParseApproved::class => [
            Listeners\DpsParse\ProcessDpsParse::class,
            Listeners\DpsParse\RerankPlayerOnIpsAndDiscordUponDpsParseApproval::class,
            Listeners\DpsParse\AnnounceDpsApprovalOnDiscord::class,
            Listeners\DpsParse\AnnouncePromotionsOnDiscordOfficerChannel::class,
        ],
        Events\DpsParse\DpsParseDisapproved::class => [
            Listeners\DpsParse\AnnounceDpsDisapprovalOnDiscord::class
        ],

        Events\File\Uploaded::class => [
            Listeners\File\ValidateUploadRealMimeAgainstAllowedTypes::class,
            Listeners\File\PersistUploadedFile::class
        ],

        Events\User\LoggedInViaOauth::class => [
            Listeners\User\UpdateOauthUserData::class,
        ],
        Events\User\LoggedOut::class => [],
        Events\User\Registered::class => [],
        Events\User\RequestedActivationLink::class => [],
        Events\User\RequestedResetPasswordLink::class => [],
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
