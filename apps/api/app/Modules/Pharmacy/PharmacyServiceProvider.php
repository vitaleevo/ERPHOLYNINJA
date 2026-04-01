<?php

namespace App\Modules\Pharmacy;

use App\Modules\Core\Base\BaseServiceProvider;
use App\Modules\Pharmacy\Domain\Repositories\PharmacySaleRepositoryInterface;
use App\Modules\Pharmacy\Infrastructure\Repositories\PharmacySaleRepository;
use App\Modules\Pharmacy\Infrastructure\Persistence\Models\PharmacySaleModel;
use App\Modules\Pharmacy\Infrastructure\Persistence\Models\PharmacySaleItemModel;
use App\Modules\Pharmacy\Application\Services\PharmacySaleService;
use App\Modules\Pharmacy\Application\Services\PharmacyStockService;
use App\Modules\Pharmacy\Interfaces\Controllers\PharmacySaleController;
use App\Modules\Pharmacy\Domain\Events\SaleCreated;
use App\Modules\Pharmacy\Domain\Listeners\SaleCreatedListener;
use Illuminate\Support\Facades\Event;

class PharmacyServiceProvider extends BaseServiceProvider
{
    /**
     * Registrar repositórios
     */
    protected function registerRepositories(): void
    {
        // Bind de Repository Interface para implementação concreta
        $this->app->bind(
            PharmacySaleRepositoryInterface::class,
            fn($app) => new PharmacySaleRepository(
                new PharmacySaleModel()
            )
        );
    }

    /**
     * Registrar serviços
     */
    protected function registerServices(): void
    {
        // Serviço de estoque (singleton)
        $this->app->singleton(PharmacyStockService::class);

        // Serviço de vendas (depende do repository e stock service)
        $this->app->singleton(PharmacySaleService::class, function ($app) {
            return new PharmacySaleService(
                $app->make(PharmacySaleRepositoryInterface::class),
                $app->make(PharmacyStockService::class)
            );
        });
    }

    /**
     * Registrar rotas
     */
    protected function registerRoutes(): void
    {
        // Verificar se rotas estão em cache
        if (file_exists(base_path('routes/cache.php'))) {
            return;
        }

        $this->app['router']->middleware(['api'])->prefix('api/pharmacy')->group(function () {
            // Rotas de vendas de farmácia
            $this->app['router']->get('/sales', [PharmacySaleController::class, 'index']);
            $this->app['router']->get('/sales/{id}', [PharmacySaleController::class, 'show']);
            $this->app['router']->post('/sales', [PharmacySaleController::class, 'store']);
            $this->app['router']->post('/sales/{id}/cancel', [PharmacySaleController::class, 'cancel']);
            $this->app['router']->get('/sales/summary', [PharmacySaleController::class, 'summary']);
        });
    }

    /**
     * Registrar migrações
     */
    protected function registerMigrations(): void
    {
        // As migrações já existem no database/migrations
        // Este módulo usa as tabelas existentes:
        // - pharmacy_sales
        // - pharmacy_sale_items
        // - medications
        // - medication_batches
        // - pharmacy_stocks
    }

    /**
     * Registrar comandos
     */
    protected function registerCommands(): void
    {
        // Futuros comandos Artisan do módulo Pharmacy podem ser registrados aqui
        // Exemplo: $this->commands([CheckExpirationsCommand::class]);
    }

    /**
     * Registrar eventos de domínio
     */
    protected function registerEvents(): void
    {
        // Registrar listeners para Domain Events
        Event::listen(
            SaleCreated::class,
            SaleCreatedListener::class
        );
    }
}
