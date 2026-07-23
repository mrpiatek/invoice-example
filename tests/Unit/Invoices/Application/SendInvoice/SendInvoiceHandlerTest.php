<?php

declare(strict_types=1);

namespace Tests\Unit\Invoices\Application\SendInvoice;

use Illuminate\Contracts\Cache\Lock;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Modules\Invoices\Application\SendInvoice\SendInvoiceCommand;
use Modules\Invoices\Application\SendInvoice\SendInvoiceHandler;
use Modules\Invoices\Domain\Enums\StatusEnum;
use Modules\Invoices\Domain\Exception\InvalidInvoiceOperationException;
use Modules\Invoices\Domain\Exception\InvalidInvoiceStatusTransitionException;
use Modules\Invoices\Domain\Model\Customer;
use Modules\Invoices\Domain\Model\Invoice;
use Modules\Invoices\Domain\Model\InvoiceId;
use Modules\Invoices\Domain\Model\InvoiceProductLine;
use Modules\Invoices\Domain\Model\InvoiceProductLineId;
use Modules\Notifications\Api\Dtos\NotifyData;
use Modules\Notifications\Api\NotificationFacadeInterface;
use RuntimeException;
use Tests\Support\InMemoryInvoiceRepository;
use Tests\TestCase;

final class SendInvoiceHandlerTest extends TestCase
{
    public function test_it_notifies_the_customer_and_persists_sending_status(): void
    {
        $invoice = $this->validInvoice();
        $repository = new InMemoryInvoiceRepository;
        $repository->seed($invoice);
        $notifications = Mockery::mock(NotificationFacadeInterface::class);
        $notifications->shouldReceive('notify')
            ->once()
            ->with(Mockery::on(
                static fn (NotifyData $data): bool => $data->resourceId->toString() === (string) $invoice->id
                    && $data->toEmail === 'jane@example.com'
                    && $data->subject === 'Your invoice is ready'
            ));

        $this->handler($repository, $notifications)->handle(
            new SendInvoiceCommand($invoice->id)
        );

        self::assertSame(StatusEnum::Sending, $repository->get($invoice->id)->status);
        self::assertSame(1, $repository->saveCount);
    }

    public function test_an_empty_invoice_is_not_notified_or_saved(): void
    {
        $invoice = Invoice::create(
            InvoiceId::generate(),
            new Customer('Jane', 'jane@example.com'),
        );
        $repository = new InMemoryInvoiceRepository;
        $repository->seed($invoice);
        $notifications = Mockery::mock(NotificationFacadeInterface::class);
        $notifications->shouldNotReceive('notify');

        try {
            $this->handler($repository, $notifications)->handle(
                new SendInvoiceCommand($invoice->id)
            );
            self::fail('Expected the invalid invoice to be rejected.');
        } catch (InvalidInvoiceOperationException) {
            self::assertSame(StatusEnum::Draft, $invoice->status);
            self::assertSame(0, $repository->saveCount);
        }
    }

    public function test_a_non_draft_invoice_is_not_notified_or_saved_again(): void
    {
        $invoice = $this->validInvoice();
        $invoice->markAsSending();
        $repository = new InMemoryInvoiceRepository;
        $repository->seed($invoice);
        $notifications = Mockery::mock(NotificationFacadeInterface::class);
        $notifications->shouldNotReceive('notify');

        $this->expectException(InvalidInvoiceStatusTransitionException::class);

        try {
            $this->handler($repository, $notifications)->handle(
                new SendInvoiceCommand($invoice->id)
            );
        } finally {
            self::assertSame(0, $repository->saveCount);
        }
    }

    public function test_notification_failure_leaves_the_invoice_in_draft_and_unsaved(): void
    {
        $invoice = $this->validInvoice();
        $repository = new InMemoryInvoiceRepository;
        $repository->seed($invoice);
        $notifications = Mockery::mock(NotificationFacadeInterface::class);
        $notifications->shouldReceive('notify')
            ->once()
            ->andThrow(new RuntimeException('Provider unavailable'));

        $this->expectException(RuntimeException::class);

        try {
            $this->handler($repository, $notifications)->handle(
                new SendInvoiceCommand($invoice->id)
            );
        } finally {
            self::assertSame(StatusEnum::Draft, $invoice->status);
            self::assertSame(0, $repository->saveCount);
        }
    }

    public function test_lock_contention_is_reported_and_does_not_notify(): void
    {
        $invoice = $this->validInvoice();
        $repository = new InMemoryInvoiceRepository;
        $repository->seed($invoice);
        $notifications = Mockery::mock(NotificationFacadeInterface::class);
        $notifications->shouldNotReceive('notify');
        $lock = Mockery::mock(Lock::class);
        $lock->shouldReceive('block')
            ->once()
            ->andThrow(new LockTimeoutException);
        Cache::shouldReceive('lock')
            ->once()
            ->andReturn($lock);

        $this->expectException(InvalidInvoiceOperationException::class);

        try {
            $this->handler($repository, $notifications)->handle(
                new SendInvoiceCommand($invoice->id)
            );
        } finally {
            self::assertSame(StatusEnum::Draft, $invoice->status);
            self::assertSame(0, $repository->saveCount);
        }
    }

    private function handler(
        InMemoryInvoiceRepository $repository,
        NotificationFacadeInterface $notifications,
    ): SendInvoiceHandler {
        return new SendInvoiceHandler($repository, $notifications);
    }

    private function validInvoice(): Invoice
    {
        $invoiceId = InvoiceId::generate();

        return Invoice::create(
            $invoiceId,
            new Customer('Jane', 'jane@example.com'),
            [
                InvoiceProductLine::create(
                    InvoiceProductLineId::generate(),
                    $invoiceId,
                    'Laptop',
                    1,
                    1200,
                ),
            ],
        );
    }
}
