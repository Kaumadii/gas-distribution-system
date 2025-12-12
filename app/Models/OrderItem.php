<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_order_id',
        'gas_type_id',
        'quantity',
        'unit_price',
        'total_price',
        'empty_returned',
    ];

    public function order()
    {
        return $this->belongsTo(CustomerOrder::class, 'customer_order_id');
    }

    public function gasType()
    {
        return $this->belongsTo(GasType::class);
    }
}

