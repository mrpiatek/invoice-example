<?php

declare(strict_types=1);

namespace Modules\Invoices\Presentation\Http;

use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\PathParameter;
use Illuminate\Http\JsonResponse;
use Modules\Invoices\Application\GetInvoice\GetInvoiceHandler;
use Modules\Invoices\Application\GetInvoice\GetInvoiceQuery;
use Modules\Invoices\Domain\Model\InvoiceId;
use Modules\Invoices\Infrastructure\Exception\InvoiceNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[Group('Invoice')]
final readonly class InvoiceViewController
{
    public function __construct(
        private GetInvoiceHandler $getInvoiceHandler,
    ) {}

    /**
     * View Invoice
     */
    #[PathParameter('invoiceId', description: 'Invoice UUID', example: '019f90e0-2f52-7372-b227-ab4b724853a0')]
    public function __invoke(string $invoiceId): JsonResponse
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
}
