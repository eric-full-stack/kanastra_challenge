<?php

namespace App\Jobs;

use App\Repositories\ChargeRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Bus\Batchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessCsvBatch implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $records;
    protected $data = [];

    public function __construct(array $records)
    {
        $this->records = $records;
    }

    public function handle(ChargeRepository $chargeRepository): void
    {        
        foreach($this->records as $record)
            $this->processCharges($record);

        $chargeRepository->createMany($this->data);
    }

    private function processCharges(array $record): void
    {
        // A verificação de existência do registro está sendo feita no banco de dados e lidada pelo método insertOrIgnore do Eloquent
        $this->data[] = [
            'customer_id' => $record['government_id'],
            'debt_id' => $record['debt_id'],
            'debt_amount' => $record['debt_amount'],
            'debt_due_date' => $record['debt_due_date'],
            'status' => 'pending',
        ];
    }
}
