<?php

namespace Tests\Unit\Invoices\Domain\Model;

use Modules\Invoices\Domain\Enums\StatusEnum;
use Modules\Invoices\Domain\Exception\InvalidInvoiceOperationException;
use Modules\Invoices\Domain\Exception\InvalidInvoiceStatusTransitionException;
use Modules\Invoices\Domain\Model\Customer;
use Modules\Invoices\Domain\Model\Invoice;
use Modules\Invoices\Domain\Model\InvoiceId;
use Modules\Invoices\Domain\Model\InvoiceProductLine;
use Modules\Invoices\Domain\Model\InvoiceProductLineId;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class InvoiceTest extends TestCase
{
    public function test_invoice_can_only_be_created_in_draft_status(): void
    {
        $invoice = Invoice::create(
            InvoiceId::generate(),
            new Customer('Jane', 'jane@example.com'),
        );

        $this->assertSame(
            StatusEnum::Draft,
            $invoice->status
        );
    }

    public function test_invoice_can_be_created_with_empty_product_lines(): void
    {
        $invoice = Invoice::create(
            InvoiceId::generate(),
            new Customer('Jane', 'jane@example.com'),
            [],
        );

        $this->assertSame([], $invoice->lines);
        $this->assertSame(0, $invoice->totalPrice);
    }

    public function test_invoice_can_be_sent_in_draft_status(): void
    {
        $invoice = $this->createInvoiceWithValidLine();

        $invoice->markAsSending();

        $this->assertSame(
            StatusEnum::Sending,
            $invoice->status
        );
    }

    public function test_invoice_cannot_be_sent_when_already_sending(): void
    {
        $invoice = $this->createInvoiceWithValidLine();

        $invoice->markAsSending();

        $this->expectException(
            InvalidInvoiceStatusTransitionException::class
        );

        $invoice->markAsSending();
    }

    public function test_invoice_cannot_be_sent_when_already_sent_to_client(): void
    {
        $invoice = $this->createInvoiceWithValidLine();

        $invoice->markAsSending();
        $invoice->markAsSentToClient();

        $this->assertSame(
            StatusEnum::SentToClient,
            $invoice->status
        );

        $this->expectException(
            InvalidInvoiceStatusTransitionException::class
        );

        $invoice->markAsSending();
    }

    public function test_sending_invoice_can_be_marked_as_sent_to_client(): void
    {
        $invoice = $this->createInvoiceWithValidLine();

        $invoice->markAsSending();
        $invoice->markAsSentToClient();

        $this->assertSame(
            StatusEnum::SentToClient,
            $invoice->status
        );
    }

    public function test_invoice_cannot_be_marked_as_sent_to_client_when_invoice_is_in_draft_status(): void
    {
        $invoice = $this->createInvoiceWithValidLine();

        $this->expectException(
            InvalidInvoiceStatusTransitionException::class
        );

        $invoice->markAsSentToClient();
    }

    public function test_empty_invoice_cannot_be_sent(): void
    {
        $invoice = Invoice::create(
            InvoiceId::generate(),
            new Customer('Jane', 'jane@example.com'),
        );

        $this->expectException(
            InvalidInvoiceOperationException::class
        );

        $invoice->markAsSending();
    }

    #[DataProvider('invalidProductLineProvider')]
    public function test_invoice_can_be_sent_only_with_lines_both_quantity_and_price_as_positive_numbers(
        int $quantity,
        int $price
    ): void {
        $invoiceId = InvoiceId::generate();

        $invoice = Invoice::create(
            $invoiceId,
            new Customer('Jane', 'jane@example.com'),
            [
                InvoiceProductLine::create(
                    InvoiceProductLineId::generate(),
                    $invoiceId,
                    'Schrodinger\'s Laptop',
                    $quantity,
                    $price
                ),
            ]
        );

        $this->expectException(
            InvalidInvoiceOperationException::class
        );

        $invoice->markAsSending();
    }

    public static function invalidProductLineProvider(): iterable
    {
        yield 'quantity is zero' => [
            'quantity' => 0,
            'price' => 1200,
        ];

        yield 'price is zero' => [
            'quantity' => 1,
            'price' => 0,
        ];

        yield 'quantity is negative' => [
            'quantity' => -1,
            'price' => 1200,
        ];

        yield 'price is negative' => [
            'quantity' => 1,
            'price' => -1200,
        ];
    }

    public function test_lines_can_be_added_to_draft_invoice(): void
    {
        $invoiceId = InvoiceId::generate();

        $invoice = Invoice::create(
            $invoiceId,
            new Customer('Jane', 'jane@example.com'),
        );

        $invoice->addLine(
            InvoiceProductLine::create(
                InvoiceProductLineId::generate(),
                $invoiceId,
                'Laptop',
                1,
                1200
            )
        );

        $this->assertCount(1, $invoice->lines);
        $this->assertSame(1200, $invoice->totalPrice);
    }

    public function test_lines_cannot_be_added_when_invoice_is_sending(): void
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
                    1200
                ),
            ]
        );

        $invoice->markAsSending();

        $this->expectException(
            InvalidInvoiceOperationException::class
        );

        $invoice->addLine(
            InvoiceProductLine::create(
                InvoiceProductLineId::generate(),
                $invoiceId,
                'Another Laptop',
                1,
                1300
            )
        );
    }

    public function test_total_price(): void
    {
        $invoiceId = InvoiceId::generate();

        $invoice = Invoice::create(
            $invoiceId,
            new Customer('Jane', 'jane@example.com'),
            [
                InvoiceProductLine::create(
                    InvoiceProductLineId::generate(),
                    $invoiceId,
                    'Accessories',
                    4,
                    45
                ),
                InvoiceProductLine::create(
                    InvoiceProductLineId::generate(),
                    $invoiceId,
                    'Laptop',
                    1,
                    1200
                ),
            ],
        );

        $this->assertSame(1380, $invoice->totalPrice);
    }

    private function createInvoiceWithValidLine(): Invoice
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
                    1200
                ),
            ],
        );
    }
}
