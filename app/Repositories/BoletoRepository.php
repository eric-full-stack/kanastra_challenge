<?php

namespace App\Repositories;

use App\Models\Charge;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BoletoRepository
{
    public function generateBoleto(Charge $charge): array
    {
        Log::info('Generating boleto for charge: ' . $charge['id']);
        return [
            Str::random(44),
            'https://example.com/boleto',
        ];
    }
}