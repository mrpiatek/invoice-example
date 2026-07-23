<?php

namespace Modules\Invoices\Infrastructure\Persistence\Entity;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class InvoiceProductLine extends Model
{
    use HasUuids;

    protected $fillable = [
        'id',
        'invoice_id',
        'name',
        'price',
        'quantity',
    ];
}
