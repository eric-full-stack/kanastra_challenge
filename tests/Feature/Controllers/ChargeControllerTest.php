<?php

namespace Tests\Feature\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use App\Repositories\ChargeRepository;
use App\Repositories\CsvRepository;
use App\Repositories\CustomerRepository;
use Mockery;

class ChargeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_csv_import_successful()
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->create('charges.csv', 1024, 'text/csv');

        $records = [
            ['government_id' => '123', 'name' => 'John Doe', 'email' => 'john@example.com'],
            ['government_id' => '456', 'name' => 'Jane Doe', 'email' => 'jane@example.com']
        ];

        $csvRepositoryMock = Mockery::mock(CsvRepository::class);
        $csvRepositoryMock->shouldReceive('storeCsv')->andReturn('path/to/csv');
        $csvRepositoryMock->shouldReceive('readCsv')->andReturn($records);

        $this->app->instance(CsvRepository::class, $csvRepositoryMock);

        $customerRepositoryMock = Mockery::mock(CustomerRepository::class);
        $customerRepositoryMock->shouldReceive('createMany')->with([
            ['name' => 'John Doe', 'email' => 'john@example.com', 'government_id' => '123'],
            ['name' => 'Jane Doe', 'email' => 'jane@example.com', 'government_id' => '456']
        ]);

        $this->app->instance(CustomerRepository::class, $customerRepositoryMock);

        $chargeRepositoryMock = Mockery::mock(ChargeRepository::class);
        $chargeRepositoryMock->shouldReceive('processRecords')->with($records);
        $chargeRepositoryMock->shouldReceive('emitEvents');

        $this->app->instance(ChargeRepository::class, $chargeRepositoryMock);

        Log::shouldReceive('info')->times(9);

        // Act
        $response = $this->post('api/charges/csv-import', [
            'file' => $file,
        ]);

        // Assert
        $response->assertStatus(200);
        $response->assertJson(['message' => 'File uploaded successfully']);
    }

    public function test_csv_import_exception()
    {
        Storage::fake('local');

        // Arrange
       
        $file = UploadedFile::fake()->create('charges.csv', 1024, 'text/csv');

        $csvRepositoryMock = Mockery::mock(CsvRepository::class);
        $csvRepositoryMock->shouldReceive('storeCsv')->andThrow(new \Exception('Test Exception'));

        $this->app->instance(CsvRepository::class, $csvRepositoryMock);

        Log::shouldReceive('info')->once();
        Log::shouldReceive('error')->once();

        // Act
        $response = $this->post('api/charges/csv-import', [
            'file' => $file,
        ]);

        // Assert
        $response->assertStatus(500);
        $response->assertJson(['message' => 'Test Exception']);
    }
}
