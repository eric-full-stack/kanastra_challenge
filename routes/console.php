<?php

use App\Console\Commands\SendInvoices;
use Illuminate\Support\Facades\Schedule;

Schedule::command(SendInvoices::class)->everyTenMinutes();