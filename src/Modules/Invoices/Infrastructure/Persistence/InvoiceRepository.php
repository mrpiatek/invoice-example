<?php

declare(strict_types=1);

namespace Modules\Invoices\Infrastructure\Persistence;

use App\Invoice;
use Modules\Invoices\Domain\Enums\StatusEnum;
use Modules\Invoices\Domain\Model\Customer;
use Modules\Invoices\Domain\Model\Invoice as InvoiceModel;
use Modules\Invoices\Domain\Model\InvoiceId;
use Modules\Invoices\Domain\Repository\InvoiceRepositoryInterface;
use Modules\Invoices\Infrastructure\Exception\InvoiceNotFoundException;

final class InvoiceRepository implements InvoiceRepositoryInterface
{
    public function nextIdentity(): InvoiceId
    {
        return InvoiceId::generate();
    }

    public function save(InvoiceModel $invoice): void
    {
        $entity = Invoice::create(
            [
                'id' => $invoice->id,
                'customer_name' => $invoice->customer->name,
                'customer_email' => $invoice->customer->email,
                'status' => $invoice->status,
            ]
        );

        $entity->save();
    }

    public function get(InvoiceId $id): InvoiceModel
    {
        $entity = Invoice::find((string) $id);

        if ($entity === null) {
            throw new InvoiceNotFoundException;
        }

        return InvoiceModel::reconstitute(
            new InvoiceId($entity->id),
            StatusEnum::from($entity->status),
            new Customer(
                $entity->customer_name,
                $entity->customer_email,
            ),
            // TODO load lines
            []
        );
    }
}
