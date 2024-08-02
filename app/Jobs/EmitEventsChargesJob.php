<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Jobs\GenerateBoletoJob;
use App\Jobs\SendChargeEmailJob;
use App\Repositories\ChargeRepository;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

class EmitEventsChargesJob implements ShouldQueue
{
    use Queueable, Dispatchable, InteractsWithQueue, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(ChargeRepository $chargeRepository): void
    {
        $chargeRepository->getPendingChargesByChunk(1000, function ($charges) use($chargeRepository) {
            foreach ($charges as $charge) {
                Bus::chain([
                    new GenerateBoletoJob($charge),
                    new SendChargeEmailJob($charge),
                    function () use ($chargeRepository, $charge) {
                        Log::info('Charge ' . $charge->id . ' has been completed');
                        $chargeRepository->update(['mailed_at' => \Carbon\Carbon::now(), 'status' => 'completed'], $charge);
                    }
                ])->catch(function (\Throwable $e) use ($chargeRepository, $charge) {
                    $chargeRepository->update(['status' => 'failed', 'error_message' => $e->getMessage()], $charge);
                })->onQueue('charges')->dispatch();
            }
        });
    }
}
