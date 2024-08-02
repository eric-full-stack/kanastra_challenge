<?php

namespace Tests\Unit\Jobs;

use App\Jobs\ProcessCsvBatch;
use App\Repositories\ChargeRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ProcessCsvBatchTest extends TestCase
{
    use RefreshDatabase;

    public function test_handle_processes_records_and_calls_createMany()
    {
        // Arrange
        $records = [
            [
                'government_id' => '123456789',
                'debt_id' => '1',
                'debt_amount' => 100.00,
                'debt_due_date' => '2023-12-31',
            ],
            [
                'government_id' => '987654321',
                'debt_id' => '2',
                'debt_amount' => 200.00,
                'debt_due_date' => '2023-11-30',
            ],
        ];

        $expectedData = [
            [
                'customer_id' => '123456789',
                'debt_id' => '1',
                'debt_amount' => 100.00,
                'debt_due_date' => '2023-12-31',
                'status' => 'pending',
            ],
            [
                'customer_id' => '987654321',
                'debt_id' => '2',
                'debt_amount' => 200.00,
                'debt_due_date' => '2023-11-30',
                'status' => 'pending',
            ],
        ];

        $chargeRepositoryMock = $this->createMock(ChargeRepository::class);
        $chargeRepositoryMock->expects($this->once())
            ->method('createMany')
            ->with($expectedData);

        // Act
        $job = new ProcessCsvBatch($records);
        $job->handle($chargeRepositoryMock);

        // Assert
        // The mock's expectations will automatically be verified
    }
}