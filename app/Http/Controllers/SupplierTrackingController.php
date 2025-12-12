<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\PurchaseOrder;
use App\Models\GoodsReceivedNote;
use App\Models\GasType;
use Illuminate\Http\Request;

class SupplierTrackingController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::with(['purchaseOrders.items.gasType', 'purchaseOrders.goodsReceivedNotes.items'])->get();
        
        $refillSummary = [];
        foreach ($suppliers as $supplier) {
            foreach ($supplier->purchaseOrders as $po) {
                foreach ($po->items as $item) {
                    $gasTypeName = $item->gasType->name;
                    if (!isset($refillSummary[$supplier->id][$gasTypeName])) {
                        $refillSummary[$supplier->id][$gasTypeName] = [
                            'ordered' => 0,
                            'received' => 0,
                            'charged' => 0,
                        ];
                    }
                    
                    $refillSummary[$supplier->id][$gasTypeName]['ordered'] += $item->quantity;
                    $refillSummary[$supplier->id][$gasTypeName]['charged'] += $item->quantity * $item->unit_price;
                    
                    // Calculate received from GRNs
                    $received = 0;
                    foreach ($po->goodsReceivedNotes as $grn) {
                        foreach ($grn->items as $grnItem) {
                            if ($grnItem->gas_type_id === $item->gas_type_id) {
                                $received += $grnItem->received_qty;
                            }
                        }
                    }
                    $refillSummary[$supplier->id][$gasTypeName]['received'] += $received;
                }
            }
        }
        
        return view('supplier-tracking.index', compact('suppliers', 'refillSummary'));
    }

    public function show(Supplier $supplier)
    {
        $supplier->load([
            'purchaseOrders.items.gasType',
            'purchaseOrders.goodsReceivedNotes.items',
            'purchaseOrders.payments'
        ]);
        
        // Calculate gas refills by cylinder type
        $refillsByType = [];
        foreach ($supplier->purchaseOrders as $po) {
            foreach ($po->items as $item) {
                $gasTypeName = $item->gasType->name;
                if (!isset($refillsByType[$gasTypeName])) {
                    $refillsByType[$gasTypeName] = [
                        'ordered' => 0,
                        'received' => 0,
                        'charged' => 0,
                    ];
                }
                
                $refillsByType[$gasTypeName]['ordered'] += $item->quantity;
                $refillsByType[$gasTypeName]['charged'] += $item->quantity * $item->unit_price;
                
                // Calculate received
                $received = 0;
                foreach ($po->goodsReceivedNotes as $grn) {
                    foreach ($grn->items as $grnItem) {
                        if ($grnItem->gas_type_id === $item->gas_type_id) {
                            $received += $grnItem->received_qty;
                        }
                    }
                }
                $refillsByType[$gasTypeName]['received'] += $received;
            }
        }
        
        // Payment history
        $paymentHistory = \App\Models\SupplierPayment::whereHas('purchaseOrder', function($query) use ($supplier) {
            $query->where('supplier_id', $supplier->id);
        })
        ->with('purchaseOrder')
        ->orderByDesc('paid_at')
        ->orderByDesc('created_at')
        ->get();
        
        $report = [
            'total_pos' => $supplier->purchaseOrders->count(),
            'total_po_value' => $supplier->purchaseOrders->sum('total_value'),
            'total_invoice_amount' => $supplier->purchaseOrders->sum('invoice_amount') ?? 0,
            'total_paid' => $supplier->purchaseOrders->sum(function ($po) {
                return $po->payments->sum('amount');
            }),
            'total_cylinders_ordered' => $supplier->purchaseOrders->sum(function ($po) {
                return $po->items->sum('quantity');
            }),
            'total_cylinders_received' => 0,
            'total_gas_refills_charged' => $supplier->purchaseOrders->sum(function ($po) {
                return $po->items->sum('total_price');
            }),
            'pos' => [],
        ];
        
        foreach ($supplier->purchaseOrders as $po) {
            $received = 0;
            foreach ($po->goodsReceivedNotes as $grn) {
                $received += $grn->items->sum('received_qty');
            }
            $report['total_cylinders_received'] += $received;
            
            $report['pos'][] = [
                'po' => $po,
                'ordered_qty' => $po->items->sum('quantity'),
                'received_qty' => $received,
                'po_value' => $po->total_value,
                'invoice_amount' => $po->invoice_amount,
                'invoice_difference' => $po->invoiceDifference(),
                'paid' => $po->payments->sum('amount'),
            ];
        }
        
        return view('supplier-tracking.show', compact('supplier', 'report', 'refillsByType', 'paymentHistory'));
    }
}

