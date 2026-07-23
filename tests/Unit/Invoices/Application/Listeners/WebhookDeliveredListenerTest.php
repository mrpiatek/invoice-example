<?php

declare(strict_types=1);

namespace Tests\Unit\Invoices\Application\Listeners;

use Illuminate\Support\Facades\Log;
use Modules\Invoices\Application\Listeners\WebhookDeliveredListener;
use Modules\Invoices\Application\MarkInvoiceAsSent\MarkInvoiceAsSentHandler;
use Modules\Invoices\Domain\Enums\StatusEnum;
use Modules\Invoices\Domain\Exception\InvalidInvoiceStatusTransitionException;
use Modules\Invoices\Domain\Model\Customer;
use Modules\Invoices\Domain\Model\Invoice;
use Modules\Invoices\Domain\Model\InvoiceId;
use Modules\Invoices\Domain\Model\InvoiceProductLine;
use Modules\Invoices\Domain\Model\InvoiceProductLineId;
use Modules\Notifications\Api\Events\WebhookDeliveredEvent;
use Ramsey\Uuid\Uuid;
use Tests\Support\InMemoryInvoiceRepository;
use Tests\TestCase;

final class WebhookDeliveredListenerTest extends TestCase
{
    public function test_delivery_marks_a_sending_invoice_as_sent_to_client(): void
    {
        $invoice = $this->sendingInvoice();
        $repository = new InMemoryInvoiceRepository;
        $repository->seed($invoice);

        $this->listener($repository)->handle($this->event($invoice->id));

        self::assertSame(StatusEnum::SentToClient, $repository->get($invoice->id)->status);
        self::assertSame(1, $repository->saveCount);
    }

    public function test_duplicate_delivery_is_ignored_without_an_additional_save(): void
    {
        Log::spy();
        $invoice = $this->sendingInvoice();
        $invoice->markAsSentToClient();
        $repository = new InMemoryInvoiceRepository;
        $repository->seed($invoice);

        $this->listener($repository)->handle($this->event($invoice->id));

        self::assertSame(StatusEnum::SentToClient, $invoice->status);
        self::assertSame(0, $repository->saveCount);
    }

    public function test_delivery_for_an_unknown_invoice_is_ignored(): void
    {
        Log::spy();
        $repository = new InMemoryInvoiceRepository;

        $this->listener($repository)->handle(
            $this->event(InvoiceId::generate())
        );

        self::assertSame(0, $repository->saveCount);
        Log::shouldHaveReceived('warning')->once();
    }

    public function test_early_delivery_for_a_draft_invoice_is_rejected_for_retry(): void
    {
        $invoiceId = InvoiceId::generate();
        $invoice = Invoice::create(
            $invoiceId,
            new Customer('Jane', 'jane@example.com'),
        );
        $repository = new InMemoryInvoiceRepository;
        $repository->seed($invoice);

        $this->expectException(InvalidInvoiceStatusTransitionException::class);

        $this->listener($repository)->handle($this->event($invoiceId));
    }

    private function listener(InMemoryInvoiceRepository $repository): WebhookDeliveredListener
    {
        return new WebhookDeliveredListener(
            new MarkInvoiceAsSentHandler($repository)
        );
    }

    private function event(InvoiceId $invoiceId): WebhookDeliveredEvent
    {
        return new WebhookDeliveredEvent(
            Uuid::fromString((string) $invoiceId)
        );
    }

    private function sendingInvoice(): Invoice
    {
        $invoiceId = InvoiceId::generate();
        $invoice = Invoice::create(
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
        $invoice->markAsSending();

        return $invoice;
    }
}
