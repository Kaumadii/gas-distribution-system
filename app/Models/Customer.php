<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'email',
        'phone',
        'address',
        'credit_limit',
        'outstanding_balance',
    ];

    public function customPrices()
    {
        return $this->hasMany(CustomerGasPrice::class);
    }

    public function orders()
    {
        return $this->hasMany(CustomerOrder::class);
    }

    public function priceForGas(GasType $gasType): float
    {
        // Priority 1: Custom price override (highest priority)
        $override = $this->customPrices()->where('gas_type_id', $gasType->id)->first();
        if ($override && $override->custom_price) {
            return (float) $override->custom_price;
        }

        // Priority 2: Category-based pricing (based on customer type)
        $categoryPrice = match($this->type) {
            'dealer' => $gasType->dealer_price,
            'commercial' => $gasType->commercial_price,
            'individual' => $gasType->individual_price,
            default => null,
        };

        if ($categoryPrice) {
            return (float) $categoryPrice;
        }

        // Priority 3: Default price (fallback)
        return (float) $gasType->default_price;
    }

    public function totalFullCylindersIssued(): int
    {
        return (int) $this->orders()
            ->with('items')
            ->get()
            ->sum(function ($order) {
                return $order->items->sum('quantity');
            });
    }

    public function totalEmptyCylindersReturned(): int
    {
        return (int) $this->orders()
            ->with('items')
            ->get()
            ->sum(function ($order) {
                return $order->items->sum('empty_returned');
            });
    }

    public function cylindersOutstanding(): int
    {
        return $this->totalFullCylindersIssued() - $this->totalEmptyCylindersReturned();
    }
}

