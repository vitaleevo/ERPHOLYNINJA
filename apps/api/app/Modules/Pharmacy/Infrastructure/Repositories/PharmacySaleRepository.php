<?php

namespace App\Modules\Pharmacy\Infrastructure\Repositories;

use App\Modules\Pharmacy\Domain\Entities\PharmacySale;
use App\Modules\Pharmacy\Domain\Repositories\PharmacySaleRepositoryInterface;
use App\Modules\Pharmacy\Infrastructure\Persistence\Models\PharmacySaleModel;
use Illuminate\Support\Collection;

class PharmacySaleRepository implements PharmacySaleRepositoryInterface
{
    public function __construct(
        private PharmacySaleModel $model
    ) {}

    public function find(int $id): ?PharmacySale
    {
        $model = $this->model
            ->with(['items', 'clinic', 'patient', 'user', 'prescription'])
            ->find($id);

        return $model ? PharmacySale::fromModel($model) : null;
    }

    public function findAll(array $filters = []): Collection
    {
        $query = $this->model->with(['items', 'clinic', 'patient', 'user']);

        // Aplicar filtros
        if (isset($filters['clinic_id'])) {
            $query->where('clinic_id', $filters['clinic_id']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['patient_id'])) {
            $query->where('patient_id', $filters['patient_id']);
        }

        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->whereBetween('created_at', [
                $filters['start_date'],
                $filters['end_date']
            ]);
        }

        $models = $query->orderBy('created_at', 'desc')->get();

        return $models->map(fn($model) => PharmacySale::fromModel($model));
    }

    public function findByClinic(int $clinicId, array $filters = []): Collection
    {
        $filters['clinic_id'] = $clinicId;
        return $this->findAll($filters);
    }

    public function findByPatient(int $patientId): Collection
    {
        $models = $this->model
            ->with(['items', 'clinic', 'patient', 'user'])
            ->where('patient_id', $patientId)
            ->orderBy('created_at', 'desc')
            ->get();

        return $models->map(fn($model) => PharmacySale::fromModel($model));
    }

    public function create(PharmacySale $entity): PharmacySale
    {
        // Gerar número da fatura
        $invoiceNumber = PharmacySaleModel::generateInvoiceNumber();

        $data = [
            'clinic_id' => $entity->clinicId,
            'patient_id' => $entity->patientId,
            'prescription_id' => $entity->prescriptionId,
            'user_id' => $entity->userId,
            'invoice_number' => $invoiceNumber,
            'subtotal' => $entity->subtotal->getAmount(),
            'discount' => $entity->discount->getAmount(),
            'total' => $entity->total->getAmount(),
            'payment_method' => $entity->paymentMethod,
            'status' => $entity->status,
            'observations' => $entity->observations,
        ];

        $model = $this->model->create($data);

        // Criar itens
        foreach ($entity->items as $item) {
            $model->items()->create([
                'medication_id' => $item->medicationId,
                'medication_batch_id' => $item->medicationBatchId,
                'medication_name' => $item->medicationName,
                'quantity' => $item->quantity,
                'unit_price' => $item->unitPrice->getAmount(),
                'discount' => $item->discount->getAmount(),
                'total' => $item->total->getAmount(),
                'dosage' => $item->dosage,
                'instructions' => $item->instructions,
            ]);
        }

        // Recarregar com itens
        $model->load(['items', 'clinic', 'patient', 'user']);

        return PharmacySale::fromModel($model);
    }

    public function update(PharmacySale $entity): PharmacySale
    {
        $model = $this->model->findOrFail($entity->id);

        $data = [
            'clinic_id' => $entity->clinicId,
            'patient_id' => $entity->patientId,
            'prescription_id' => $entity->prescriptionId,
            'subtotal' => $entity->subtotal->getAmount(),
            'discount' => $entity->discount->getAmount(),
            'total' => $entity->total->getAmount(),
            'payment_method' => $entity->paymentMethod,
            'status' => $entity->status,
            'observations' => $entity->observations,
        ];

        $model->update($data);

        // Atualizar itens (simplificado - na prática precisaria de lógica mais complexa)
        foreach ($entity->items as $item) {
            $existingItem = $model->items()->find($item->saleId);
            
            if ($existingItem) {
                $existingItem->update([
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unitPrice->getAmount(),
                    'discount' => $item->discount->getAmount(),
                    'total' => $item->total->getAmount(),
                ]);
            }
        }

        $model->load(['items', 'clinic', 'patient', 'user']);

        return PharmacySale::fromModel($model);
    }

    public function delete(int $id): void
    {
        $model = $this->model->findOrFail($id);
        
        // Deletar itens primeiro
        $model->items()->delete();
        
        // Deletar venda
        $model->delete();
    }

    public function findByStatus(string $status): Collection
    {
        $models = $this->model
            ->with(['items', 'clinic', 'patient', 'user'])
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->get();

        return $models->map(fn($model) => PharmacySale::fromModel($model));
    }

    public function findByDateRange(string $startDate, string $endDate): Collection
    {
        $models = $this->model
            ->with(['items', 'clinic', 'patient', 'user'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->get();

        return $models->map(fn($model) => PharmacySale::fromModel($model));
    }

    public function getTotalSalesByPeriod(string $startDate, string $endDate): float
    {
        return $this->model
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total');
    }
}
