<?php

namespace App\Modules\Pharmacy\Domain\Repositories;

use App\Modules\Pharmacy\Domain\Entities\PharmacySale;
use Illuminate\Support\Collection;

interface PharmacySaleRepositoryInterface
{
    /**
     * Find a sale by ID
     */
    public function find(int $id): ?PharmacySale;

    /**
     * Find all sales with optional filters
     */
    public function findAll(array $filters = []): Collection;

    /**
     * Find sales by clinic ID
     */
    public function findByClinic(int $clinicId, array $filters = []): Collection;

    /**
     * Find sales by patient ID
     */
    public function findByPatient(int $patientId): Collection;

    /**
     * Create a new sale
     */
    public function create(PharmacySale $entity): PharmacySale;

    /**
     * Update an existing sale
     */
    public function update(PharmacySale $entity): PharmacySale;

    /**
     * Delete a sale
     */
    public function delete(int $id): void;

    /**
     * Find sales by status
     */
    public function findByStatus(string $status): Collection;

    /**
     * Find sales by date range
     */
    public function findByDateRange(string $startDate, string $endDate): Collection;

    /**
     * Get total sales amount for a period
     */
    public function getTotalSalesByPeriod(string $startDate, string $endDate): float;
}
