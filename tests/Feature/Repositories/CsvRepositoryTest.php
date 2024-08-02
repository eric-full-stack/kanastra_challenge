<?php

namespace Tests\Unit\Repositories;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Repositories\CsvRepository;

class CsvRepositoryTest extends TestCase
{
    use RefreshDatabase;
    public function testStoreCsv()
    {
        $file = $this->createMock(UploadedFile::class);
        $filePath = 'csv/test.csv';

        $file->expects($this->once())
            ->method('store')
            ->with('csv')
            ->willReturn($filePath);

        $repository = new CsvRepository();
        $result = $repository->storeCsv($file);

        $this->assertEquals($filePath, $result);
    }

    public function testReadCsv()
    {
        $filePath = 'csv/test.csv';
        $csvContent = "name,government_id,email,debt_amount,debt_due_date,debt_id\nJohn Doe,123456789,john@example.com,1000,2023-01-01,1";

        Storage::shouldReceive('get')
            ->with($filePath)
            ->andReturn($csvContent);

        $repository = new CsvRepository();
        $result = $repository->readCsv($filePath);

        $expected = [
            1 => [
                'name' => 'John Doe',
                'government_id' => '123456789',
                'email' => 'john@example.com',
                'debt_amount' => '1000',
                'debt_due_date' => '2023-01-01',
                'debt_id' => '1'
            ]
        ];

        $this->assertEquals($expected, $result);
    }

    public function testDeleteCsv()
    {
        $filePath = 'csv/test.csv';

        Storage::shouldReceive('delete')
            ->with($filePath)
            ->andReturn(true);

        $repository = new CsvRepository();
        $repository->deleteCsv($filePath);

        Storage::shouldHaveReceived('delete')
            ->with($filePath)
            ->once();
    }
}