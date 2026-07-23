<?php

declare(strict_types=1);

namespace Modules\Invoices\Application\SendInvoice;

use Illuminate\Support\Facades\Config;
use Modules\Invoices\Domain\Repository\InvoiceRepositoryInterface;
use Modules\Notifications\Api\Dtos\NotifyData;
use Modules\Notifications\Api\NotificationFacadeInterface;
use Ramsey\Uuid\Uuid;

final readonly class SendInvoiceHandler
{
    public function __construct(
        private InvoiceRepositoryInterface $invoices,
        private NotificationFacadeInterface $notificationFacade
    ) {}

    public function handle(SendInvoiceCommand $command): void
    {
        $invoice = $this->invoices->get(
            $command->invoiceId
        );

        $this->notificationFacade->notify(
            new NotifyData(
                Uuid::fromString((string) $invoice->id),
                $invoice->customer->email,
                Config::get('notification.from'),
                Config::get('notification.message')
            )
        );

        $invoice->send();

        $this->invoices->save($invoice);
    }
}
