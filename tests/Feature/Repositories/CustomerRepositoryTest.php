<?php

namespace Tests\Feature\Repositories;

use App\Models\Customer;
use App\Repositories\CustomerRepository;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected $customerRepository;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->customerRepository = new CustomerRepository();

    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function test_findOrCreate()
    {
        $fields = ['email' => 'test@example.com'];
        $data = ['name' => 'Test User', 'email' => 'test@example.com', 'government_id' => '123'];

        $customer = Customer::factory()->create($fields);

        $result = $this->customerRepository->findOrCreate($fields, $data);

        $this->assertInstanceOf(Customer::class, $result);
        $this->assertEquals($customer->name, $result->name);
    }

    public function test_findOrCreate_not_found()
    {
        $fields = ['email' => 'test@example.com'];
        $data = ['name' => 'Test User', 'email' => 'test@example.com', 'government_id' => '123'];

        $result = $this->customerRepository->findOrCreate($fields, $data);

        $this->assertInstanceOf(Customer::class, $result);
        $this->assertEquals($data['name'], $result->name);
    }

    public function test_createMany()
    {
        $data = [
            ['name' => 'Test User 1', 'email' => 'test1@example.com', 'government_id' => '123'],
            ['name' => 'Test User 2', 'email' => 'test2@example.com', 'government_id' => '456']
        ];
        
        $this->customerRepository->createMany($data);

        $customers = $this->customerRepository->findAll();

        $this->assertInstanceOf(Customer::class, $customers->first());
        $this->assertEquals($data[0]['name'], $customers[0]['name']);
        $this->assertEquals($data[1]['name'], $customers[1]['name']);
        $this->assertEquals($data[0]['email'], $customers[0]['email']);
        $this->assertEquals($data[1]['email'], $customers[1]['email']);
        $this->assertEquals($data[0]['government_id'], $customers[0]['government_id']);
        $this->assertEquals($data[1]['government_id'], $customers[1]['government_id']);
    }

    public function test_createMany_error()
    {
        $data = [
            ['name' => 'Test User 1', 'email' => 'test1@example.com', 'government_id' => '123'],
            ['name' => 'Test User 2', 'email' => 'test2@example.com']
        ];

        $this->expectException(QueryException::class);
        $this->customerRepository->createMany($data);
    }

    public function test_create()
    {
        $data = ['name' => 'Test User', 'email' => 'test@example.com', 'government_id' => '123'];

        $result = $this->customerRepository->create($data);

        $this->assertInstanceOf(Customer::class, $result);
        $this->assertEquals($data['name'], $result->name);
        $this->assertEquals($data['email'], $result->email);
        $this->assertEquals($data['government_id'], $result->government_id);
    }

    public function test_update()
    {
        $data = ['name' => 'Updated User'];
       
        $customer = Customer::factory()->create();

        $result = $this->customerRepository->update($data, $customer);

        $this->assertInstanceOf(Customer::class, $result);
        $this->assertEquals($data['name'], $result->name);
        $this->assertEquals($customer->email, $result->email);
    }

    public function test_delete()
    {
        $customer = Customer::factory()->create();
        
        $result = $this->customerRepository->delete($customer);

        $this->assertInstanceOf(Customer::class, $result);
        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
    }

    public function test_find()
    {
        $customer = Customer::factory()->create();
        $result = $this->customerRepository->find($customer->id);

        $this->assertInstanceOf(Customer::class, $result);
        $this->assertEquals($customer->id, $result->id);
        $this->assertDatabaseHas('customers', ['id' => $customer->id]);
    }

    public function test_find_not_found()
    {
        $result = $this->customerRepository->find(1);

        $this->assertNull($result);
    }

    public function test_findAll()
    {
        $customers = Customer::factory()->count(3)->create();

        $result = $this->customerRepository->findAll();

        $this->assertIsArray($result->toArray());
        $this->assertCount(3, $result);
    }
}