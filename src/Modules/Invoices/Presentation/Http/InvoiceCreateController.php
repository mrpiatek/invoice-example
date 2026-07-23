<?php

declare(strict_types=1);

namespace Modules\Invoices\Presentation\Http;

use Dedoc\Scramble\Attributes\BodyParameter;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Invoices\Application\CreateInvoice\CreateInvoiceCommand;
use Modules\Invoices\Application\CreateInvoice\CreateInvoiceHandler;
use Symfony\Component\HttpFoundation\Response;

#[Group('Invoice')]
final readonly class InvoiceCreateController
{
    public function __construct(
        private CreateInvoiceHandler $createInvoiceHandler,
    ) {}

    /**
     * Create new Invoice
     *
     * New Invoices are created in *draft* status and may or may not contain any *product line items*.
     */
    #[BodyParameter('customer_name', description: 'Customer name', example: 'Taylor Otwell')]
    #[BodyParameter('customer_email', description: 'Customer email', example: 'taylor@laravel.com')]
    #[BodyParameter('lines.*.product_name', description: 'Product name', example: 'Awesome Framework')]
    #[BodyParameter('lines.*.quantity', description: 'Quantity', example: 10)]
    #[BodyParameter('lines.*.unit_price', description: 'Unit price', example: 120)]
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|string|max:255|email',
            'lines' => 'array',
            'lines.*.product_name' => 'required|string|max:255',
            'lines.*.quantity' => 'required|integer|between:1,2147483647', // 4 byte signed int
            'lines.*.unit_price' => 'required|integer|between:1,2147483647', // 4 byte signed int
        ]);

        $invoiceId = $this->createInvoiceHandler->handle(
            new CreateInvoiceCommand(
                $validated['customer_name'],
                $validated['customer_email'],
                $validated['lines'] ?? []
            )
        );

        return new JsonResponse(data: [
            /**
             * Created Invoice UUID
             *
             * @example "019f90e0-2f52-7372-b227-ab4b724853a0"
             */
            'invoice_id' => (string) $invoiceId,
        ], status: Response::HTTP_CREATED);
    }
}
