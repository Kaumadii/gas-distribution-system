<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'customer_id',
        'delivery_route_id',
        'status',
        'urgent',
        'scheduled_date',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function route()
    {
        return $this->belongsTo(DeliveryRoute::class, 'delivery_route_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'customer_order_id');
    }

    public function totalValue(): float
    {
        return (float) $this->items()->sum('total_price');
    }
}

