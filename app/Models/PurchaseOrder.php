<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'po_number',
        'supplier_id',
        'status',
        'total_value',
        'invoice_amount',
        'invoice_number',
        'invoice_date',
        'expected_date',
        'notes',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function payments()
    {
        return $this->hasMany(SupplierPayment::class);
    }

    public function goodsReceivedNotes()
    {
        return $this->hasMany(GoodsReceivedNote::class);
    }

    public function totalPaid(): float
    {
        return (float) $this->payments()->sum('amount');
    }

    public function balance(): float
    {
        return (float) max(0, $this->total_value - $this->totalPaid());
    }

    public function totalReceivedQuantity($poItemId): int
    {
        return (int) $this->goodsReceivedNotes()
            ->where('status', 'approved') // Only count approved GRNs
            ->with('items')
            ->get()
            ->sum(function ($grn) use ($poItemId) {
                return $grn->items()
                    ->where('purchase_order_item_id', $poItemId)
                    ->sum('received_qty');
            });
    }

    public function isPartiallyReceived(): bool
    {
        if ($this->status === 'completed') {
            return false;
        }

        foreach ($this->items as $item) {
            $received = $this->totalReceivedQuantity($item->id);
            if ($received > 0 && $received < $item->quantity) {
                return true;
            }
        }

        return false;
    }

    public function getRemainingQuantities(): array
    {
        $remaining = [];
        foreach ($this->items as $item) {
            $received = $this->totalReceivedQuantity($item->id);
            $remaining[$item->id] = [
                'ordered' => $item->quantity,
                'received' => $received,
                'remaining' => max(0, $item->quantity - $received),
            ];
        }
        return $remaining;
    }

    public function invoiceDifference(): float
    {
        if (!$this->invoice_amount) {
            return 0;
        }
        return (float) ($this->invoice_amount - $this->total_value);
    }

    public function hasInvoice(): bool
    {
        return !is_null($this->invoice_amount) && $this->invoice_amount > 0;
    }
}

