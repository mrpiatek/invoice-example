<?php

namespace Tests\Unit\Invoices\Domain\Model;

use Modules\Invoices\Domain\Enums\StatusEnum;
use Modules\Invoices\Domain\Exception\InvalidInvoiceOperationException;
use Modules\Invoices\Domain\Exception\InvalidInvoiceStatusTransitionException;
use Modules\Invoices\Domain\Model\Customer;
use Modules\Invoices\Domain\Model\Invoice;
use Modules\Invoices\Domain\Model\InvoiceId;
use Modules\Invoices\Domain\Model\InvoiceProductLine;
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

    public function test_invoice_can_only_be_sent_in_draft_status(): void
    {
        $invoice = Invoice::create(
            InvoiceId::generate(),
            new Customer('Jane', 'jane@example.com'),
            [
                new InvoiceProductLine(
                    'Laptop',
                    1,
                    1200
                ),
            ],
        );

        $invoice->send();

        $this->assertSame(
            StatusEnum::Sending,
            $invoice->status
        );

        $this->expectException(
            InvalidInvoiceStatusTransitionException::class
        );

        $invoice->send();

        $this->assertSame(
            StatusEnum::Sending,
            $invoice->status
        );

        $invoice->markAsSentToClient();

        $this->expectException(
            InvalidInvoiceStatusTransitionException::class
        );

        $invoice->send();

        $this->assertSame(
            StatusEnum::SentToClient,
            $invoice->status
        );
    }

    public function test_only_sending_invoice_can_be_marked_as_sent(): void
    {
        $invoice = Invoice::create(
            InvoiceId::generate(),
            new Customer('Jane', 'jane@example.com'),
            [
                new InvoiceProductLine(
                    'Laptop',
                    1,
                    1200
                ),
            ],
        );

        $this->expectException(
            InvalidInvoiceOperationException::class
        );

        $invoice->markAsSentToClient();

        $invoice->send();
        $invoice->markAsSentToClient();

        $this->assertSame(
            StatusEnum::SentToClient,
            $invoice->status
        );
    }

    public function test_empty_invoice_cannot_be_sent(): void
    {
        $invoice = Invoice::create(
            InvoiceId::generate(),
            new Customer('Jane', 'jane@example.com'),
        );

        $this->expectException(InvalidInvoiceStatusTransitionException::class);

        $invoice->send();

        $invoice->addLine(new InvoiceProductLine(
            'Laptop',
            1,
            1200
        ));

        $invoice->send();

        $this->assertSame(
            StatusEnum::Sending,
            $invoice->status
        );
    }

    public function test_invoice_can_be_sent_only_with_lines_both_quantity_and_price_as_positive_numbers(): void
    {
        $invoice = Invoice::create(
            InvoiceId::generate(),
            new Customer('Jane', 'jane@example.com'),
            [new InvoiceProductLine(
                'Schrodinger\'s Laptop',
                0,
                1200
            )]
        );

        $this->expectException(InvalidInvoiceOperationException::class);

        $invoice->send();

        $invoice = Invoice::create(
            InvoiceId::generate(),
            new Customer('Jane', 'jane@example.com'),
            [new InvoiceProductLine(
                'Free Laptop',
                1,
                0
            )]
        );

        $this->expectException(InvalidInvoiceOperationException::class);

        $invoice->send();
    }

    public function test_lines_can_be_added_only_to_draft_invoice(): void
    {
        $invoice = Invoice::create(
            InvoiceId::generate(),
            new Customer('Jane', 'jane@example.com'),
            [
                new InvoiceProductLine(
                    'Laptop',
                    1,
                    1200
                ),
            ]
        );

        $invoice->send();

        $this->expectException(InvalidInvoiceOperationException::class);

        $invoice->addLine(new InvoiceProductLine(
            'Another Laptop',
            1,
            1300
        ));
    }

    public function test_total_price(): void
    {
        $invoice = Invoice::create(
            InvoiceId::generate(),
            new Customer('Jane', 'jane@example.com'),
            [
                new InvoiceProductLine(
                    'Accessories',
                    4,
                    45
                ),
                new InvoiceProductLine(
                    'Laptop',
                    1,
                    1200
                ),
            ],
        );

        $this->assertSame(1380, $invoice->totalPrice);
    }
}
