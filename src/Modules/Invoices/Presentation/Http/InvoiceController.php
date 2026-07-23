<?php

declare(strict_types=1);

namespace Modules\Invoices\Presentation\Http;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Invoices\Application\CreateInvoice\CreateInvoiceCommand;
use Modules\Invoices\Application\CreateInvoice\CreateInvoiceHandler;
use Modules\Invoices\Application\GetInvoice\GetInvoiceHandler;
use Modules\Invoices\Application\GetInvoice\GetInvoiceQuery;
use Modules\Invoices\Application\SendInvoice\SendInvoiceCommand;
use Modules\Invoices\Application\SendInvoice\SendInvoiceHandler;
use Modules\Invoices\Domain\Exception\InvalidInvoiceOperationException;
use Modules\Invoices\Domain\Exception\InvalidInvoiceStatusTransitionException;
use Modules\Invoices\Domain\Model\InvoiceId;
use Modules\Invoices\Infrastructure\Exception\InvoiceNotFoundException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class InvoiceController
{
    public function __construct(
        private CreateInvoiceHandler $createInvoiceHandler,
        private GetInvoiceHandler $getInvoiceHandler,
        private SendInvoiceHandler $sendInvoiceHandler
    ) {}

    public function create(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|string|max:255|email',
            'lines' => 'array',
            'lines.*.product_name' => 'required|string|max:255',
            'lines.*.quantity' => 'required|integer|between:0,2147483647', // 4 byte signed int
            'lines.*.unit_price' => 'required|integer|between:0,2147483647', // 4 byte signed int
        ]);

        $invoiceId = $this->createInvoiceHandler->handle(
            new CreateInvoiceCommand(
                $validated['customer_name'],
                $validated['customer_email'],
                $validated['lines'] ?? []
            )
        );

        return new JsonResponse(data: ['invoice_id' => (string) $invoiceId], status: Response::HTTP_CREATED);
    }

    public function get(string $invoiceId): JsonResponse
    {
        try {
            $invoiceView = $this->getInvoiceHandler->handle(
                new GetInvoiceQuery(new InvoiceId($invoiceId))
            );
        } catch (InvoiceNotFoundException) {
            throw new NotFoundHttpException("Invoice with ID {$invoiceId} does not exist.");
        }

        return new JsonResponse($invoiceView->data);
    }

    public function send(string $invoiceId): JsonResponse
    {
        try {
            $this->sendInvoiceHandler->handle(
                new SendInvoiceCommand(new InvoiceId($invoiceId))
            );
        } catch (InvoiceNotFoundException) {
            throw new NotFoundHttpException("Invoice with ID {$invoiceId} does not exist.");
        } catch (InvalidInvoiceStatusTransitionException|InvalidInvoiceOperationException) {
            throw new ConflictHttpException("Invoice with ID {$invoiceId} cannot be sent because of its status");
        }

        return new JsonResponse(data: null, status: Response::HTTP_OK);
    }
}
