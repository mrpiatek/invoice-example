<?php

declare(strict_types=1);

namespace Modules\Invoices\Application\Listeners;

use Illuminate\Support\Facades\Log;
use Modules\Invoices\Application\MarkInvoiceAsSent\MarkInvoiceAsSentCommand;
use Modules\Invoices\Application\MarkInvoiceAsSent\MarkInvoiceAsSentHandler;
use Modules\Invoices\Domain\Model\InvoiceId;
use Modules\Invoices\Infrastructure\Exception\InvoiceNotFoundException;
use Modules\Notifications\Api\Events\WebhookDeliveredEvent;

class WebhookDeliveredListener
{
    public function __construct(
        private MarkInvoiceAsSentHandler $handler
    ) {}

    public function handle(WebhookDeliveredEvent $event): void
    {
        try {
            $this->handler->handle(
                new MarkInvoiceAsSentCommand(
                    new InvoiceId($event->resourceId->toString())
                )
            );
        } catch (InvoiceNotFoundException) {
            Log::warning('Notification webhook recieved a non-existing Invoice ID', [
                'resourceId' => $event->resourceId->toString(),
            ]);
        }
    }
}
