<?php

namespace App\Repositories;

use App\Jobs\EmitEventsChargesJob;
use App\Jobs\ImportCsvCharges;
use App\Models\Charge;
use Illuminate\Support\Facades\DB;

class ChargeRepository
{
    public function create(array $data): Charge
    {
        return Charge::create($data);
    }

    public function createMany(array $data): void
    {
        // Removendo o eloquent para melhorar a performance, ganhando 10s de tempo de execução
        // Charge::insertOrIgnore($data);
        $sql = 'INSERT IGNORE INTO charges (customer_id, debt_id, debt_amount, debt_due_date, status) VALUES ';
        $values = [];
        foreach ($data as $row) {
            $values[] = '(' . $row['customer_id'] . ', "' . $row['debt_id'] . '", ' . $row['debt_amount'] . ', "' . $row['debt_due_date'] . '", "' . $row['status'] . '")';
        }
        $sql .= implode(', ', $values);
        DB::insert($sql);
    
    }

    public function update(array $data, Charge $charge): Charge
    {
        $charge->update($data);

        return $charge;
    }

    public function delete(array $data): Charge
    {
        $charge = Charge::find($data['id']);
        $charge->delete();

        return $charge;
    }

    public function find(int $id): Charge | null
    {
        return Charge::find($id);
    }

    public function findAll(): array
    {
        return Charge::all()->toArray();
    }

    public function getPendingChargesByChunk(int $chunkSize = 1000, $callback = null)
    {
        return Charge::where('status', 'pending')->with('customer')->chunk($chunkSize, $callback);
    }

    public function findByDebtId(string $debtId): bool
    {
        return Charge::where('debt_id', $debtId)->exists();
    }

    public function processCsv(string $filePath): void
    {
        ImportCsvCharges::dispatch($filePath)->onQueue('csv-uploads');
    }

    public function processRecords(array $records, $batchSize = 5000): void
    {
        // Apesar da fila e jobs ser um método mais robusto, demora mais do que fazer diretamente no local
        // ProcessCsvBatch::dispatch($records)->onQueue('csv-chunks'); 
        $chunks = array_chunk($records, $batchSize);
        foreach ($chunks as $chunk) {
            $data = [];
            foreach ($chunk as $record)
                $data[] = [
                    'customer_id' => $record['government_id'],
                    'debt_id' => $record['debt_id'],
                    'debt_amount' => $record['debt_amount'],
                    'debt_due_date' => $record['debt_due_date'],
                    'status' => 'pending',
                ];

            $this->createMany($data);
        }
    }

    public function emitEvents(): void
    {
        EmitEventsChargesJob::dispatch()->onQueue('charges');
    }
}