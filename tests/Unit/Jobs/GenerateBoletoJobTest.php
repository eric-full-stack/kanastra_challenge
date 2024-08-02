<?php

namespace Tests\Unit\Jobs;

use App\Jobs\GenerateBoletoJob;
use App\Models\Charge;
use App\Repositories\BoletoRepository;
use App\Repositories\ChargeRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

class GenerateBoletoJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_boleto_already_generated()
    {
        // Arrange
        $charge = Mockery::mock(Charge::class)->makePartial();
        $charge->shouldReceive('getAttribute')->with('boleto_generated_at')->andReturn(now());
        $charge->shouldReceive('getAttribute')->with('id')->andReturn(1);

        $boletoRepository = Mockery::mock(BoletoRepository::class);
        $chargeRepository = Mockery::mock(ChargeRepository::class);

        Log::shouldReceive('info')->once()->with('Boleto already generated for charge', ['charge_id' => 1]);

        $job = new GenerateBoletoJob($charge);

        // Act
        $job->handle($boletoRepository, $chargeRepository);

        $this->assertEquals(1, $charge->id);
    }

    public function test_boleto_generation()
    {
        // Arrange
        $charge = Mockery::mock(Charge::class);
        $charge->shouldReceive('getAttribute')->with('boleto_generated_at')->andReturn(null);
        $charge->shouldReceive('getAttribute')->with('id')->andReturn(1);

        $boletoRepository = Mockery::mock(BoletoRepository::class);
        $boletoRepository->shouldReceive('generateBoleto')->with($charge)->andReturn(['1234567890', 'http://example.com/boleto']);

        $chargeRepository = Mockery::mock(ChargeRepository::class);
        $chargeRepository->shouldReceive('update')->with(Mockery::on(function($arg) {
            return $arg['boleto_bar_code'] === '1234567890' &&
                   $arg['boleto_url'] === 'http://example.com/boleto' &&
                   isset($arg['boleto_generated_at']);
        }), $charge);

        Log::shouldReceive('info')->once()->with('Boleto generated for charge', [
            'charge_id' => 1,
            'boleto_bar_code' => '1234567890',
            'boleto_url' => 'http://example.com/boleto'
        ]);

        $job = new GenerateBoletoJob($charge);

        // Act
        $job->handle($boletoRepository, $chargeRepository);

        // Assert
        // No further assertions needed as we are only checking the log message and repository calls
        $this->expectNotToPerformAssertions();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
