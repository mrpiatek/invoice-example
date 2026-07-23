<?php

declare(strict_types=1);

namespace Modules\Invoices\Presentation\Http;

use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\PathParameter;
use Illuminate\Http\JsonResponse;
use Modules\Invoices\Application\SendInvoice\SendInvoiceCommand;
use Modules\Invoices\Application\SendInvoice\SendInvoiceHandler;
use Modules\Invoices\Domain\Exception\InvalidInvoiceOperationException;
use Modules\Invoices\Domain\Exception\InvalidInvoiceStatusTransitionException;
use Modules\Invoices\Domain\Model\InvoiceId;
use Modules\Invoices\Infrastructure\Exception\InvoiceNotFoundException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[Group('Invoice')]
final readonly class InvoiceSendController
{
    public function __construct(
        private SendInvoiceHandler $sendInvoiceHandler
    ) {}

    /**
     * Send Invoice
     *
     * Send the given Invoice to the Customer. Status transition rules may apply.
     */
    #[PathParameter('invoiceId', description: 'Invoice UUID', example: '019f90e0-2f52-7372-b227-ab4b724853a0')]
    public function __invoke(string $invoiceId): JsonResponse
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
