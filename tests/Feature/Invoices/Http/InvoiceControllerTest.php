<?php

declare(strict_types=1);

namespace Tests\Feature\Notification\Http;

use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class InvoiceControllerTest extends TestCase
{
    #[DataProvider('createInvoiceDataProvider')]
    public function test_create(array $createData): void
    {
        $response = $this->postJson(
            route('invoice.create'),
            $createData
        );
        $response
            ->assertCreated()
            ->dump();

        $invoiceId = $response['invoice_id'];

        $response = $this->postJson(
            route('invoice.send', ['invoiceId' => $invoiceId]),
        )->assertOk();

        // Simulate webhook call
        $response = $this->getJson(
            route('notification.hook', [
                'action' => 'delivered',
                'reference' => $invoiceId,
            ]),
        )->assertOk();

        $response = $this->getJson(
            route('invoice.get', ['invoiceId' => $invoiceId]),
        )->assertOk()
            ->dump();
    }

    public static function createInvoiceDataProvider(): Generator
    {
        yield [
            [
                'customer_name' => 'Taylor Otwell',
                'customer_email' => 'taylor@laravel.com',
                'lines' => [
                    [
                        'product_name' => 'Awesome Framework',
                        'quantity' => 1,
                        'unit_price' => 10,
                    ],
                    [
                        'product_name' => 'Great Ecosystem',
                        'quantity' => 23,
                        'unit_price' => 2,
                    ],
                ],
            ],
        ];
    }
}
