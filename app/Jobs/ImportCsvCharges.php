<?php

namespace App\Jobs;

use App\Repositories\ChargeRepository;
use App\Repositories\CsvRepository;
use App\Repositories\CustomerRepository;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;

class ImportCsvCharges implements ShouldQueue, ShouldBeUniqueUntilProcessing
{
    use Dispatchable, Queueable;

    protected $filePath;
    protected $customerRepository;
    protected $chargeRepository;

    protected $customersMap = [];

    public function __construct(string $filePath)
    {
        $customerRepository = new CustomerRepository();
        $chargeRepository = new ChargeRepository();

        $this->filePath = $filePath;
        $this->customerRepository = $customerRepository;
        $this->chargeRepository = $chargeRepository;
    }

    public function handle(CsvRepository $csvRepository): void
    {
        $records = $csvRepository->readCsv($this->filePath);
        foreach ($records as $record) {
            $customer_id = $this->getOrCreateCustomer($record);
            $record['customerId'] = $customer_id;

            $this->processCharges($record);
        }
        // Remove o arquivo CSV após processamento
        $csvRepository->deleteCsv($this->filePath);
    }

    // A ideia aqui é criar um mapa de governmentId para evitar ficar buscando o cliente no banco de dados a cada iteração, o que pode ser custoso no pior caso (ex: quando 100k linhas com o mesmo cliente)
    private function getOrCreateCustomer(array $record): string
    {
        $governmentId = $record['governmentId'];

        if (!isset($this->customersMap[$governmentId])) {
            $customer = $this->customerRepository->findOrCreate(
                ['email' => $record['email'], 'governmentId' => $governmentId],
                ['name' => $record['name'], 'email' => $record['email'], 'governmentId' => $governmentId]
            );
            $this->customersMap[$governmentId] = $customer->id;
        }

        return $this->customersMap[$governmentId];
    }

    private function processCharges(array $record): void
    {
        if (!$this->chargeRepository->findByDebtId($record['debtId'])) {
            $data = [
                'customer_id' => $record['customerId'],
                'debtId' => $record['debtId'],
                'debtAmount' => $record['debtAmount'],
                'debtDueDate' => $record['debtDueDate'],
                'status' => 'pending',
            ];
            $this->chargeRepository->create($data);
        }
         
    }

}
