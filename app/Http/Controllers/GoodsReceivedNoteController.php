<?php

namespace App\Http\Controllers;

use App\Models\GoodsReceivedNote;
use App\Models\GrnItem;
use App\Models\PurchaseOrder;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GoodsReceivedNoteController extends Controller
{
    public function index()
    {
        $grns = GoodsReceivedNote::with(['purchaseOrder.supplier', 'supplier', 'items.gasType'])
            ->latest()
            ->get();
        return view('grn.index', compact('grns'));
    }

    public function create()
    {
        $pos = PurchaseOrder::with(['supplier', 'items.gasType', 'goodsReceivedNotes.items'])
            ->whereIn('status', ['pending', 'approved'])
            ->get()
            ->map(function ($po) {
                $po->is_partially_received = $po->isPartiallyReceived();
                $po->remaining_quantities = $po->getRemainingQuantities();
                return $po;
            });
        return view('grn.create', compact('pos'));
    }

    public function getPOItems($poId)
    {
        $po = PurchaseOrder::with(['items.gasType', 'goodsReceivedNotes.items'])
            ->findOrFail($poId);
        
        $po->is_partially_received = $po->isPartiallyReceived();
        $po->remaining_quantities = $po->getRemainingQuantities();
        
        return response()->json([
            'po' => $po,
            'items' => $po->items->map(function ($item) use ($po) {
                $remaining = $po->remaining_quantities[$item->id] ?? [
                    'ordered' => $item->quantity,
                    'received' => 0,
                    'remaining' => $item->quantity
                ];
                return [
                    'id' => $item->id,
                    'gas_type_id' => $item->gas_type_id,
                    'gas_type_name' => $item->gasType->name,
                    'ordered_qty' => $item->quantity,
                    'received_qty' => $remaining['received'],
                    'remaining_qty' => $remaining['remaining'],
                ];
            })
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'received_at' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.purchase_order_item_id' => 'required|exists:purchase_order_items,id',
            'items.*.gas_type_id' => 'required|exists:gas_types,id',
            'items.*.ordered_qty' => 'required|integer|min:0',
            'items.*.received_qty' => 'required|integer|min:0',
            'items.*.damaged_qty' => 'nullable|integer|min:0',
            'items.*.rejected_qty' => 'nullable|integer|min:0',
        ]);

        $po = PurchaseOrder::with('items')->findOrFail($data['purchase_order_id']);

        $grn = GoodsReceivedNote::create([
            'grn_number' => 'GRN-' . Str::upper(Str::random(8)),
            'purchase_order_id' => $po->id,
            'supplier_id' => $po->supplier_id,
            'status' => 'pending', // Start as pending, needs approval
            'received_at' => $data['received_at'],
            'notes' => $data['notes'] ?? null,
        ]);

        foreach ($data['items'] as $item) {
            $received = $item['received_qty'];
            $ordered = $item['ordered_qty'];
            $damaged = $item['damaged_qty'] ?? 0;
            $rejected = $item['rejected_qty'] ?? 0;
            $short = max(0, $ordered - $received);

            GrnItem::create([
                'goods_received_note_id' => $grn->id,
                'purchase_order_item_id' => $item['purchase_order_item_id'],
                'gas_type_id' => $item['gas_type_id'],
                'ordered_qty' => $ordered,
                'received_qty' => $received,
                'damaged_qty' => $damaged,
                'rejected_qty' => $rejected,
                'short_qty' => $short,
            ]);
        }

        return redirect()->route('grn.index')->with('success', 'GRN created. Please approve to update stock.');
    }

    public function approve(GoodsReceivedNote $grn)
    {
        if ($grn->status === 'approved') {
            return back()->with('error', 'GRN already approved.');
        }

        $po = $grn->purchaseOrder;
        
        // Update stock for each item
        foreach ($grn->items as $item) {
            $received = $item->received_qty;
            $damaged = $item->damaged_qty ?? 0;
            $rejected = $item->rejected_qty ?? 0;
            
            // Only add good cylinders to stock (received - damaged - rejected)
            $goodCylinders = max(0, $received - $damaged - $rejected);
            
            $stock = Stock::firstOrCreate(
                ['gas_type_id' => $item->gas_type_id],
                ['full_cylinders' => 0, 'empty_cylinders' => 0]
            );

            $stock->increment('full_cylinders', $goodCylinders);
        }

        // Check if PO is fully received
        $fullyReceived = true;
        foreach ($po->items as $poItem) {
            $totalReceived = $po->totalReceivedQuantity($poItem->id);
            if ($totalReceived < $poItem->quantity) {
                $fullyReceived = false;
                break;
            }
        }

        // Update GRN status
        $grn->update(['status' => 'approved']);

        // Auto-close PO if fully received
        if ($fullyReceived) {
            $po->update(['status' => 'completed']);
            return redirect()->route('grn.index')->with('success', 'GRN approved. Stock updated. PO marked as completed.');
        } else {
            // Check if partially received
            $partiallyReceived = false;
            foreach ($po->items as $poItem) {
                $totalReceived = $po->totalReceivedQuantity($poItem->id);
                if ($totalReceived > 0 && $totalReceived < $poItem->quantity) {
                    $partiallyReceived = true;
                    break;
                }
            }
            
            if ($partiallyReceived) {
                return redirect()->route('grn.index')->with('warning', 'GRN approved. Stock updated. PO is partially received.');
            }
        }

        return redirect()->route('grn.index')->with('success', 'GRN approved. Stock updated.');
    }
}

