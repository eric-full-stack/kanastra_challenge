<?php

namespace Tests\Unit\Jobs;

use App\Jobs\SendChargeEmailJob;
use App\Models\Charge;
use App\Models\Customer;
use App\Repositories\ChargeRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

class SendChargeEmailJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_already_sent()
    {
        // Arrange
        $charge = Mockery::mock(Charge::class)->makePartial();
        $charge->shouldReceive('getAttribute')->with('email_sent_at')->andReturn(now());
        $charge->shouldReceive('getAttribute')->with('id')->andReturn(1);

        $chargeRepository = Mockery::mock(ChargeRepository::class);

        Log::shouldReceive('info')->once()->with('Email already sent for charge', ['charge_id' => 1]);

        $job = new SendChargeEmailJob($charge);

        // Act
        $job->handle($chargeRepository);

        // Assert
        // No further assertions needed as we are only checking the log message
        $this->expectNotToPerformAssertions();
    }

    public function test_email_sending()
    {
        // Arrange
        $charge = Mockery::mock(Charge::class);
        $charge->shouldReceive('getAttribute')->with('email_sent_at')->andReturn(null);
        $charge->shouldReceive('getAttribute')->with('id')->andReturn(1);

        $customer = Mockery::mock(Customer::class);
        $customer->shouldReceive('getAttribute')->with('email')->andReturn('customer@example.com');

        $charge->shouldReceive('getAttribute')->with('customer')->andReturn($customer);

        $chargeRepository = Mockery::mock(ChargeRepository::class);
        $chargeRepository->shouldReceive('update')->with(Mockery::on(function($arg) {
            return isset($arg['email_sent_at']);
        }), $charge);

        Log::shouldReceive('info')->once()->with('Sending email to customer@example.com');

        $job = new SendChargeEmailJob($charge);

        // Act
        $job->handle($chargeRepository);

        // Assert
        // No further assertions needed as we are only checking the log message and repository call
        $this->expectNotToPerformAssertions();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
