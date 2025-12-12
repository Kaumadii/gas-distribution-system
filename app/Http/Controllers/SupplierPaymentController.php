<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\SupplierPayment;
use Illuminate\Http\Request;

class SupplierPaymentController extends Controller
{
    public function index()
    {
        $payments = SupplierPayment::with('purchaseOrder.supplier', 'purchaseOrder.payments')
            ->latest()
            ->get();
        $pos = PurchaseOrder::with(['supplier', 'payments'])
            ->orderByDesc('created_at')
            ->get();
        return view('supplier-payments.index', compact('payments', 'pos'));
    }

    public function ledger()
    {
        $suppliers = \App\Models\Supplier::with(['purchaseOrders.items', 'purchaseOrders.payments'])->get();
        
        $ledger = $suppliers->map(function ($supplier) {
            $totalPOValue = $supplier->purchaseOrders->sum('total_value');
            $totalPaid = $supplier->purchaseOrders->sum(function ($po) {
                return $po->payments->sum('amount');
            });
            $overpayment = max(0, $totalPaid - $totalPOValue);
            $balance = max(0, $totalPOValue - $totalPaid);
            
            return [
                'supplier' => $supplier,
                'total_po_value' => $totalPOValue,
                'total_paid' => $totalPaid,
                'balance' => $balance,
                'overpayment' => $overpayment,
            ];
        });
        
        return view('supplier-payments.ledger', compact('ledger'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'amount' => 'required|numeric|min:0.01',
            'mode' => 'required|string|max:50',
            'reference_number' => 'nullable|string|max:255',
            'paid_at' => 'nullable|date',
        ]);

        $po = PurchaseOrder::findOrFail($data['purchase_order_id']);
        
        // Check if payment exceeds PO value
        $totalPaid = $po->totalPaid() + $data['amount'];
        if ($totalPaid > $po->total_value) {
            $overpayment = $totalPaid - $po->total_value;
            // Still allow but show warning
        }

        SupplierPayment::create($data);

        return back()->with('success', 'Payment recorded successfully');
    }
}

