<?php

namespace Tests\Feature\Repositories;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Repositories\BoletoRepository;
use App\Repositories\ChargeRepository;
use App\Models\Charge;
use Mockery;

class BoletoRepositoryTest extends TestCase
{
    use RefreshDatabase;
    
    private $boletoRepository;
    private $chargeRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->boletoRepository = new BoletoRepository();
        $this->chargeRepository = new ChargeRepository();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_generate_boleto_and_update_charge()
    {
        $charge = Charge::factory()->make();

        $charge = $this->chargeRepository->create($charge->toArray());
        
        $result = $this->boletoRepository->generateBoleto($charge);

        $this->chargeRepository->update(['boleto_bar_code' => $result[0], 'boleto_url' => $result[1], 'boleto_generated_at' => now()], $charge);
 
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals(44, strlen($result[0]));
        $this->assertEquals('https://example.com/boleto', $result[1]);

        $this->assertDatabaseHas('charges', [
            'boleto_bar_code' => $result[0],
            'boleto_url' => $result[1],
            'boleto_generated_at' => now()
        ]);
    }
}
