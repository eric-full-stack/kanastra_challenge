<?php

namespace App\Jobs;

use App\Models\Charge;
use App\Repositories\ChargeRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendChargeEmailJob implements ShouldQueue
{
    use Queueable, Dispatchable, InteractsWithQueue;

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
    public function handle(ChargeRepository $chargeRepository): void
    {
        if($this->charge->email_sent_at) {
            Log::info('Email already sent for charge', ['charge_id' => $this->charge->id]);
            return;
        }
        Log::info("Sending email to {$this->charge->customer->email}");
        $chargeRepository->update(['email_sent_at' => now()], $this->charge);
        // Mail::send([], [], function ($message) {
        //     $message->to($this->email)
        //         ->subject($this->subject)
        //         ->setBody($this->body);
        // });         
    }
}
