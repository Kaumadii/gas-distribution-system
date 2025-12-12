<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'gas_type_id',
        'rate',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function gasType()
    {
        return $this->belongsTo(GasType::class);
    }
}

