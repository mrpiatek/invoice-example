<?php

namespace Modules\Invoices\Infrastructure\Persistence\Entity;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasUuids;

    protected $fillable = [
        'id',
        'customer_name',
        'customer_email',
        'status',
    ];

    public function lines(): HasMany
    {
        return $this->hasMany(InvoiceProductLine::class);
    }
}
