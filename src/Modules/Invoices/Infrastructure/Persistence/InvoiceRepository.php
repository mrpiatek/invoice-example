<?php

declare(strict_types=1);

namespace Modules\Invoices\Infrastructure\Persistence;

use Illuminate\Support\Facades\DB;
use Modules\Invoices\Domain\Enums\StatusEnum;
use Modules\Invoices\Domain\Model\Customer;
use Modules\Invoices\Domain\Model\Invoice as InvoiceModel;
use Modules\Invoices\Domain\Model\InvoiceId;
use Modules\Invoices\Domain\Model\InvoiceProductLine as InvoiceProductLineModel;
use Modules\Invoices\Domain\Model\InvoiceProductLineId;
use Modules\Invoices\Domain\Repository\InvoiceRepositoryInterface;
use Modules\Invoices\Infrastructure\Exception\InvoiceNotFoundException;
use Modules\Invoices\Infrastructure\Persistence\Entity\Invoice;
use Modules\Invoices\Infrastructure\Persistence\Entity\InvoiceProductLine;

final class InvoiceRepository implements InvoiceRepositoryInterface
{
    public function nextIdentity(): InvoiceId
    {
        return InvoiceId::generate();
    }

    public function save(InvoiceModel $invoice): void
    {
        DB::transaction(function () use ($invoice) {
            $invoiceEntity = Invoice::updateOrCreate(
                [
                    'id' => $invoice->id,
                ],
                [
                    'customer_name' => $invoice->customer->name,
                    'customer_email' => $invoice->customer->email,
                    'status' => $invoice->status,
                ]
            );

            foreach ($invoice->lines as $lineItem) {
                $invoiceEntity->lines()->updateOrCreate(
                    [
                        'id' => $lineItem->id,
                    ],
                    [
                        'invoice_id' => $invoice->id,
                        'name' => $lineItem->productName,
                        'quantity' => $lineItem->quantity,
                        'price' => $lineItem->unitPrice,
                    ]
                );
            }
        });
    }

    public function get(InvoiceId $id): InvoiceModel
    {
        $entity = Invoice::find((string) $id);

        if ($entity === null) {
            throw new InvoiceNotFoundException;
        }

        return InvoiceModel::reconstitute(
            id: new InvoiceId($entity->id),
            status: StatusEnum::from($entity->status),
            customer: new Customer(
                $entity->customer_name,
                $entity->customer_email,
            ),
            lines: $entity->lines->map(
                static fn (InvoiceProductLine $lineItem) => InvoiceProductLineModel::reconstitute(
                    id: new InvoiceProductLineId($lineItem->id),
                    invoiceId: new InvoiceId($lineItem->invoice_id),
                    productName: $lineItem->name,
                    quantity: $lineItem->quantity,
                    unitPrice: $lineItem->price
                )
            )->toArray()
        );
    }
}
