<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerGasPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'gas_type_id',
        'custom_price',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function gasType()
    {
        return $this->belongsTo(GasType::class);
    }
}

