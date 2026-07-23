<?php

declare(strict_types=1);

namespace Modules\Invoices\Infrastructure\Providers;

use Carbon\Laravel\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Modules\Invoices\Application\Listeners\WebhookDeliveredListener;
use Modules\Notifications\Api\Events\WebhookDeliveredEvent;

final class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Event::listen(
            WebhookDeliveredEvent::class,
            WebhookDeliveredListener::class,
        );
    }
}
