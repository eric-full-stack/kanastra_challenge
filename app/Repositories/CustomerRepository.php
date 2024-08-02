<?php

namespace App\Repositories;
 
use App\Models\Customer;
use Illuminate\Database\Eloquent\Collection;

class CustomerRepository
{
    public function findOrCreate(array $fields, array $data): Customer
    {
        return Customer::firstOrCreate($fields, $data);
    }

    public function createMany(array $data): void
    {
        Customer::insertOrIgnore($data);
    }

    public function create(array $data): Customer
    {
        return Customer::create($data);
    }

    public function update(array $data, Customer $customer): Customer
    {
        $customer->update($data);

        return $customer;
    }

    public function delete(Customer $customer): Customer
    {
        $customer->delete();

        return $customer;
    }

    public function find(int $id): Customer | null
    {
        return Customer::find($id);
    }

    public function findAll(): Collection
    {
        return Customer::all();
    }

}