<?php

declare(strict_types=1);

namespace Modules\Invoices\Infrastructure\Providers;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Modules\Invoices\Domain\Repository\InvoiceRepositoryInterface;
use Modules\Invoices\Infrastructure\Persistence\InvoiceRepository;

final class InvoiceRepositoryServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function register(): void
    {
        $this->app->scoped(InvoiceRepositoryInterface::class, InvoiceRepository::class);

        // $this->app->singleton(InvoiceRepository::class, static fn ($app) => new InvoiceRepository(
        // ));
    }

    /** @return array<class-string> */
    public function provides(): array
    {
        return [
            InvoiceRepositoryInterface::class,
        ];
    }
}
