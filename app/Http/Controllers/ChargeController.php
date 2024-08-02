<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadFileRequest;
use App\Jobs\ProcessCsvBatch;
use App\Repositories\ChargeRepository;
use App\Repositories\CsvRepository;
use App\Repositories\CustomerRepository;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

class ChargeController extends Controller
{
    public function __construct(protected ChargeRepository $charges, protected CsvRepository $csv, protected CustomerRepository $customers)
    {

    }

    public function csvImport(UploadFileRequest $request)
    {
        try {
            $start = now();
            Log::info('File upload started');
            $filePath = $this->csv->storeCsv($request->file('file'));
            $records = $this->csv->readCsv($filePath);
            Log::info('File upload finished');

            Log::info('Getting unique customers');
            $uniqueCustomers = [];
            foreach ($records as $record) {
                $uniqueCustomers[$record['government_id']] = [
                    'name' => $record['name'],
                    'email' => $record['email'],
                    'government_id' => $record['government_id']
                ];
            }
            Log::info('Getting unique customers End');

            Log::info('Creating customers');
            $this->customers->createMany(array_values($uniqueCustomers));
            Log::info('Creating customers End');

            Log::info('Inserting records');
            $this->charges->processRecords($records);
            Log::info('Inserting records End');
            $end = now();
            Log::info('File upload finished in ' . $start->diffInSeconds($end) . ' seconds');
            return response()->json(['message' => 'File uploaded successfully']);
        } catch (\Exception $e) {
            Log::error('Error uploading file: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
