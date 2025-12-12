<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GasType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'weight_kg',
        'default_price',
        'dealer_price',
        'commercial_price',
        'individual_price',
    ];

    public function supplierRates()
    {
        return $this->hasMany(SupplierRate::class);
    }

    public function purchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function stock()
    {
        return $this->hasOne(Stock::class);
    }
}

