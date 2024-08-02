<?php

namespace App\Repositories;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use League\Csv\Reader;

class CsvRepository
{

    /**
     * Armazena o arquivo CSV no sistema de armazenamento configurado e retorna o caminho do arquivo.
     *
     * @param UploadedFile $file
     * @return string
     */
    public function storeCsv(UploadedFile $file): string
    {
        // Idealmente o arquivo seria armazenado em um local seguro, como um bucket S3, garantindo que esteja disponível em outras instâncias da aplicação
        return $file->store('csv');
    }

    /**
     * Lê os dados de um arquivo CSV armazenado.
     *
     * @param string $filePath
     * @return array
     */
    public function readCsv(string $filePath): array
    {
        Log::info('Reading CSV file: ' . $filePath);
        // Idealmente o arquivo seria armazenado em um local seguro, como um bucket S3, garantindo que esteja disponível em outras instâncias da aplicação
        $fileContent = Storage::get($filePath);
        $tempFilePath = tempnam(sys_get_temp_dir(), 'csv_');
        file_put_contents($tempFilePath, $fileContent);
        Log::info('CSV file copied to temp file: ' . $tempFilePath);

        Log::info('Parsing CSV file');
        $csv = Reader::createFromPath($tempFilePath, 'r');
        $csv->setHeaderOffset(0);

        $records = iterator_to_array($csv->getRecords(['name', 'government_id', 'email', 'debt_amount', 'debt_due_date', 'debt_id']));
        Log::info('CSV file parsed');
        
        unlink($tempFilePath);

        return $records;
    }

    /**
     * Remove um arquivo CSV do sistema de armazenamento configurado.
     *
     * @param string $filePath
     * @return void
     */
    public function deleteCsv(string $filePath): void
    {
        Storage::delete($filePath);
    }
 
}
