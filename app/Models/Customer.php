<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

     protected $fillable = [
        "name",
        "email",
        "government_id"
    ];

    public function charges()
    {
        return $this->hasMany(Charge::class);
    }
}
