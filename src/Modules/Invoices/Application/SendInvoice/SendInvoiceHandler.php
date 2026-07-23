<?php

declare(strict_types=1);

namespace Modules\Invoices\Application\SendInvoice;

use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Modules\Invoices\Domain\Exception\InvalidInvoiceOperationException;
use Modules\Invoices\Domain\Exception\InvalidInvoiceStatusTransitionException;
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

    /**
     * @throws InvalidInvoiceStatusTransitionException
     */
    public function handle(SendInvoiceCommand $command): void
    {
        $lockName = sprintf(
            'invoice:%s:send',
            (string) $command->invoiceId,
        );

        $lockTtl = Config::integer('notification.notify_lock_ttl_seconds');
        $lockWait = Config::integer('notification.notify_lock_wait_seconds');

        try {
            Cache::lock($lockName, $lockTtl)->block(
                $lockWait,
                function () use ($command): void {
                    $invoice = $this->invoices->get(
                        $command->invoiceId
                    );

                    $invoice->assertSendable();

                    $this->notificationFacade->notify(
                        new NotifyData(
                            Uuid::fromString((string) $invoice->id),
                            $invoice->customer->email,
                            Config::string('notification.subject'),
                            Config::string('notification.message')
                        )
                    );

                    $invoice->markAsSending();

                    $this->invoices->save($invoice);
                }
            );
        } catch (LockTimeoutException $e) {
            throw new InvalidInvoiceOperationException(
                'Invoice sending is already in progress.',
                previous: $e,
            );
        }
    }
}
