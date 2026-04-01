<?php

namespace App\Modules\Core\Base;

use Illuminate\Support\ServiceProvider;

abstract class BaseServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->registerRepositories();
        $this->registerServices();
        $this->registerEvents();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerRoutes();
        $this->registerMigrations();
        $this->registerCommands();
    }

    /**
     * Registrar repositórios (implementar nas classes filhas)
     */
    protected function registerRepositories(): void
    {
        // Implementar nas classes específicas de cada módulo
    }

    /**
     * Registrar serviços (implementar nas classes filhas)
     */
    protected function registerServices(): void
    {
        // Implementar nas classes específicas de cada módulo
    }

    /**
     * Registrar listeners de eventos (implementar nas classes filhas)
     */
    protected function registerEvents(): void
    {
        // Implementar nas classes específicas de cada módulo
    }

    /**
     * Registrar rotas (implementar nas classes filhas)
     */
    protected function registerRoutes(): void
    {
        // Implementar nas classes específicas de cada módulo
    }

    /**
     * Registrar migrações (implementar nas classes filhas)
     */
    protected function registerMigrations(): void
    {
        // Implementar nas classes específicas de cada módulo
    }

    /**
     * Registrar comandos Artisan (implementar nas classes filhas)
     */
    protected function registerCommands(): void
    {
        // Implementar nas classes específicas de cada módulo
    }
}
