<?php

declare(strict_types=1);

namespace Modules\Invoices\Application\Listeners;

use Modules\Invoices\Application\MarkInvoiceAsSent\MarkInvoiceAsSentCommand;
use Modules\Invoices\Application\MarkInvoiceAsSent\MarkInvoiceAsSentHandler;
use Modules\Invoices\Domain\Model\InvoiceId;
use Modules\Notifications\Api\Events\WebhookDeliveredEvent;

class WebhookDeliveredListener
{
    public function __construct(
        private MarkInvoiceAsSentHandler $handler
    ) {}

    public function handle(WebhookDeliveredEvent $event): void
    {
        $this->handler->handle(
            new MarkInvoiceAsSentCommand(
                new InvoiceId($event->resourceId->toString())
            )
        );
    }
}
