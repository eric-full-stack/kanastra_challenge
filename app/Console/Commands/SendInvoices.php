<?php

namespace App\Console\Commands;

use App\Repositories\ChargeRepository;
use Illuminate\Console\Command;

class SendInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-invoices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send boleto and email to pending charges';

    /**
     * Execute the console command.
     */
    public function handle(ChargeRepository $chargeRepository): void
    {
        $chargeRepository->emitEvents();
    }
}
