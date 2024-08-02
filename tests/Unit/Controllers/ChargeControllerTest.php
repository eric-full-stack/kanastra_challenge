<?php

namespace Tests\Unit\Controllers;

use App\Http\Controllers\ChargeController;
use App\Http\Requests\UploadFileRequest;
use App\Repositories\ChargeRepository;
use App\Repositories\CsvRepository;
use App\Repositories\CustomerRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

class ChargeControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $chargeRepositoryMock;
    protected $csvRepositoryMock;
    protected $customerRepositoryMock;
    protected $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->chargeRepositoryMock = Mockery::mock(ChargeRepository::class);
        $this->csvRepositoryMock = Mockery::mock(CsvRepository::class);
        $this->customerRepositoryMock = Mockery::mock(CustomerRepository::class);

        $this->controller = new ChargeController(
            $this->chargeRepositoryMock,
            $this->csvRepositoryMock,
            $this->customerRepositoryMock
        );
    }

    public function test_csv_import_successful()
    {
        $requestMock = Mockery::mock(UploadFileRequest::class);
        $fileMock = Mockery::mock(\Illuminate\Http\UploadedFile::class);
        $requestMock->shouldReceive('file')->with('file')->andReturn($fileMock);

        $filePath = 'path/to/csv';
        $records = [
            ['government_id' => '123', 'name' => 'John Doe', 'email' => 'john@example.com'],
            ['government_id' => '456', 'name' => 'Jane Doe', 'email' => 'jane@example.com']
        ];

        $this->csvRepositoryMock->shouldReceive('storeCsv')->with($fileMock)->andReturn($filePath);
        $this->csvRepositoryMock->shouldReceive('readCsv')->with($filePath)->andReturn($records);

        $this->customerRepositoryMock->shouldReceive('createMany')->with([
            ['name' => 'John Doe', 'email' => 'john@example.com', 'government_id' => '123'],
            ['name' => 'Jane Doe', 'email' => 'jane@example.com', 'government_id' => '456']
        ]);

        $this->chargeRepositoryMock->shouldReceive('processRecords')->with($records);
        $this->chargeRepositoryMock->shouldReceive('emitEvents');

        Log::shouldReceive('info')->times(9);

        $response = $this->controller->csvImport($requestMock);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(['message' => 'File uploaded successfully'], $response->getData(true));
    }

    public function test_csv_import_exception()
    {
        $requestMock = Mockery::mock(UploadFileRequest::class);
        $fileMock = Mockery::mock(\Illuminate\Http\UploadedFile::class);
        $requestMock->shouldReceive('file')->with('file')->andReturn($fileMock);

        $this->csvRepositoryMock->shouldReceive('storeCsv')->andThrow(new \Exception('Test Exception'));

        Log::shouldReceive('info')->once();
        Log::shouldReceive('error')->once();

        $response = $this->controller->csvImport($requestMock);

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals(['message' => 'Test Exception'], $response->getData(true));
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}