<?php

namespace Tests\Unit\Repositories;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Repositories\BoletoRepository;
use App\Models\Charge;
use Mockery;

class BoletoRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private $boletoRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->boletoRepository = new BoletoRepository();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testGenerateBoleto()
    {
        // Criando um mock para a classe Charge
        $charge = $this->createMock(Charge::class);
        $charge->method('getAttribute')->with('id')->willReturn(1);

        // Chamando o método a ser testado
        $result = $this->boletoRepository->generateBoleto($charge);

        // Verificando o formato do resultado
        $this->assertIsArray($result);
        $this->assertCount(2, $result);

        // Verificando o primeiro elemento do array
        $this->assertEquals(44, strlen($result[0]));

        // Verificando o segundo elemento do array
        $this->assertEquals('https://example.com/boleto', $result[1]);
    }
}
