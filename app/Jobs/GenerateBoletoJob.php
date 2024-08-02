<?php

namespace App\Jobs;

use App\Models\Charge;
use App\Repositories\BoletoRepository;
use App\Repositories\ChargeRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateBoletoJob implements ShouldQueue
{
    use Queueable, Dispatchable, InteractsWithQueue, SerializesModels;

    protected $charge;
    

    /**
     * Create a new job instance.
     */
    public function __construct(Charge $charge)
    {

        $this->charge = $charge;
    }

    /**
     * Execute the job.
     */
    public function handle(BoletoRepository $boletoRepository, ChargeRepository $chargeRepository): void
    {
        if($this->charge->boleto_generated_at) {
            Log::info('Boleto already generated for charge', ['charge_id' => $this->charge->id]);
            return;
        }
        [$boleto_bar_code, $boleto_url] = $boletoRepository->generateBoleto($this->charge);
        Log::info('Boleto generated for charge', ['charge_id' => $this->charge->id, 'boleto_bar_code' => $boleto_bar_code, 'boleto_url' => $boleto_url]);
        $chargeRepository->update(['boleto_bar_code' => $boleto_bar_code, 'boleto_url' => $boleto_url, 'boleto_generated_at' => now()], $this->charge);    
    }
}
