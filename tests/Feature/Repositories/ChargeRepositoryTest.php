<?php

namespace Tests\Feature\Repositories;

use App\Models\Charge;
use App\Repositories\ChargeRepository;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChargeRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected $chargeRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->chargeRepository = new ChargeRepository();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function test_create()
    {
        $data = Charge::factory()->make()->toArray();

        $result = $this->chargeRepository->create($data);

        $this->assertInstanceOf(Charge::class, $result);
        $this->assertEquals($data['customer_id'], $result->customer_id);
        $this->assertEquals($data['debt_id'], $result->debt_id);
        $this->assertEquals($data['debt_amount'], $result->debt_amount);
        $this->assertEquals($data['debt_due_date'], $result->debt_due_date);
        $this->assertEquals($data['status'], $result->status);
    }

    public function test_createMany()
    {
        $data = Charge::factory()->count(2)->make()->toArray();

        $this->chargeRepository->createMany($data);

        $charges = $this->chargeRepository->findAll();

        $this->assertCount(2, $charges);
        $this->assertEquals($data[0]['customer_id'], $charges[0]['customer_id']);
        $this->assertEquals($data[1]['customer_id'], $charges[1]['customer_id']);
    }

    public function test_createMany_error()
    {

        $data = Charge::factory()->count(2)->make()->toArray();
        $data[1]['customer_id'] = 'fafa';

        $this->expectException(QueryException::class);
        $this->chargeRepository->createMany($data);
    }

    public function test_update()
    {
        $charge = Charge::factory()->create();
        $data = ['debt_amount' => 150.00];

        $result = $this->chargeRepository->update($data, $charge);

        $this->assertInstanceOf(Charge::class, $result);
        $this->assertEquals($data['debt_amount'], $result->debt_amount);
    }

    public function test_delete()
    {
        $charge = Charge::factory()->create();

        $result = $this->chargeRepository->delete(['id' => $charge->id]);

        $this->assertInstanceOf(Charge::class, $result);
        $this->assertDatabaseMissing('charges', ['id' => $charge->id]);
    }

    public function test_find()
    {
        $charge = Charge::factory()->create();

        $result = $this->chargeRepository->find($charge->id);

        $this->assertInstanceOf(Charge::class, $result);
        $this->assertEquals($charge->id, $result->id);
    }

    public function test_find_not_found()
    {
        $result = $this->chargeRepository->find(1);

        $this->assertNull($result);
    }

    public function test_findAll()
    {
        Charge::factory()->count(3)->create();

        $result = $this->chargeRepository->findAll();

        $this->assertCount(3, $result);
    }

    public function test_getPendingChargesByChunk()
    {
        Charge::factory()->count(3)->create(['status' => 'pending']);

        $result = [];
        $this->chargeRepository->getPendingChargesByChunk(2, function ($charges) use (&$result) {
            $result = array_merge($result, $charges->toArray());
        });

        $this->assertCount(3, $result);
    }

    public function test_findByDebtId()
    {
        $charge = Charge::factory()->create();

        $result = $this->chargeRepository->findByDebtId($charge->debt_id);

        $this->assertTrue($result);
    }

    public function test_processRecords()
    {
        $records = Charge::factory()->count(2)->make()->toArray();
        $records[0]['government_id'] = $records[0]['customer_id'];
        $records[1]['government_id'] = $records[1]['customer_id'];

        $this->chargeRepository->processRecords($records);

        $charges = $this->chargeRepository->findAll();

        $this->assertCount(2, $charges);
    }

}
