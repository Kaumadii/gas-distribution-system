<?php

namespace App\Http\Controllers;

use App\Models\GasType;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\SupplierRate;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['supplier', 'items.gasType']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by supplier
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $pos = $query->latest()->get();
        $suppliers = Supplier::orderBy('name')->get();
        
        return view('purchase-orders.index', compact('pos', 'suppliers'));
    }

    public function create()
    {
        $suppliers = Supplier::with('rates')->orderBy('name')->get();
        $gasTypes = GasType::orderBy('name')->get();
        return view('purchase-orders.create', compact('suppliers', 'gasTypes'));
    }

    public function store(Request $request)
    {
        $filteredItems = collect($request->input('items', []))
            ->filter(fn ($item) => !empty($item['gas_type_id']) && !empty($item['quantity']))
            ->values();

        if ($filteredItems->isEmpty()) {
            return back()->withErrors(['items' => 'Add at least one item'])->withInput();
        }

        $request->merge(['items' => $filteredItems->toArray()]);

        $data = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'expected_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.gas_type_id' => 'required|exists:gas_types,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $po = PurchaseOrder::create([
            'po_number' => 'PO-' . Str::upper(Str::random(8)),
            'supplier_id' => $data['supplier_id'],
            'status' => 'pending',
            'expected_date' => $data['expected_date'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        $total = 0;
        foreach ($data['items'] as $item) {
            $rate = SupplierRate::where('supplier_id', $data['supplier_id'])
                ->where('gas_type_id', $item['gas_type_id'])
                ->value('rate');

            if (!$rate) {
                $gasType = GasType::find($item['gas_type_id']);
                return back()->withErrors(['items' => "No supplier rate found for {$gasType->name}. Please set rates for this supplier first."])->withInput();
            }

            $unit = $rate;
            $lineTotal = $unit * $item['quantity'];
            $total += $lineTotal;

            PurchaseOrderItem::create([
                'purchase_order_id' => $po->id,
                'gas_type_id' => $item['gas_type_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $unit,
                'total_price' => $lineTotal,
            ]);
        }

        $po->update(['total_value' => $total]);

        return redirect()->route('purchase-orders.index')->with('success', 'PO created');
    }

    public function updateStatus(Request $request, PurchaseOrder $purchaseOrder)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,completed',
        ]);

        $purchaseOrder->update(['status' => $request->status]);
        
        $statusMessages = [
            'pending' => 'PO status set to Pending',
            'approved' => 'PO approved',
            'completed' => 'PO marked as Completed',
        ];

        return back()->with('success', $statusMessages[$request->status]);
    }

    public function updateInvoice(Request $request, PurchaseOrder $purchaseOrder)
    {
        $data = $request->validate([
            'invoice_amount' => 'nullable|numeric|min:0',
            'invoice_number' => 'nullable|string|max:255',
            'invoice_date' => 'nullable|date',
        ]);

        $purchaseOrder->update($data);

        return back()->with('success', 'Invoice information updated');
    }
}

