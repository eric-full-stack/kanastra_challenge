<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
 
class Charge extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'debt_id',
        'debt_amount',
        'debt_due_date',
        'status',
        'boleto_bar_code',
        'boleto_url',
        'mailed_at',
        'boleto_generated_at',
        'error_message',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'government_id');
    }
 
}
