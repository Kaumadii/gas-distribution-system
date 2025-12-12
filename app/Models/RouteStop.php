<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RouteStop extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_route_id',
        'customer_id',
        'planned_time',
        'actual_time',
        'notes',
    ];

    protected $casts = [
        'planned_time' => 'datetime',
        'actual_time' => 'datetime',
    ];

    public function route()
    {
        return $this->belongsTo(DeliveryRoute::class, 'delivery_route_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}

