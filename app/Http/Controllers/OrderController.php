<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerOrder;
use App\Models\DeliveryRoute;
use App\Models\GasType;
use App\Models\OrderItem;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index()
    {
        $orders = CustomerOrder::with('customer', 'route', 'items.gasType')->latest()->get();
        return view('orders.index', compact('orders'));
    }

    public function create()
    {
        $stocks = Stock::with('gasType')->get()->keyBy('gas_type_id');
        return view('orders.create', [
            'customers' => Customer::orderBy('name')->get(),
            'routes' => DeliveryRoute::orderBy('name')->get(),
            'gasTypes' => GasType::orderBy('name')->get(),
            'stocks' => $stocks,
        ]);
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
            'customer_id' => 'required|exists:customers,id',
            'delivery_route_id' => 'nullable|exists:delivery_routes,id',
            'scheduled_date' => 'nullable|date',
            'urgent' => 'nullable|boolean',
            'items' => 'required|array|min:1',
            'items.*.gas_type_id' => 'required|exists:gas_types,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.empty_returned' => 'nullable|integer|min:0',
        ]);

        $customer = Customer::findOrFail($data['customer_id']);

        // Check stock availability
        $stockIssues = [];
        foreach ($data['items'] as $item) {
            $stock = Stock::firstOrCreate(
                ['gas_type_id' => $item['gas_type_id']],
                ['full_cylinders' => 0, 'empty_cylinders' => 0]
            );
            
            if ($stock->full_cylinders < $item['quantity']) {
                $gasType = GasType::find($item['gas_type_id']);
                $stockIssues[] = "Insufficient stock for {$gasType->name}: Required {$item['quantity']}, Available {$stock->full_cylinders}";
            }
        }

        if (!empty($stockIssues)) {
            return back()
                ->withErrors(['stock' => $stockIssues])
                ->withInput();
        }

        $order = CustomerOrder::create([
            'order_number' => 'ORD-' . Str::upper(Str::random(8)),
            'customer_id' => $customer->id,
            'delivery_route_id' => $data['delivery_route_id'] ?? null,
            'status' => 'pending',
            'urgent' => $request->boolean('urgent'),
            'scheduled_date' => $data['scheduled_date'] ?? null,
        ]);

        foreach ($data['items'] as $item) {
            $gasType = GasType::find($item['gas_type_id']);
            $unit = $customer->priceForGas($gasType);
            $total = $unit * $item['quantity'];

            OrderItem::create([
                'customer_order_id' => $order->id,
                'gas_type_id' => $item['gas_type_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $unit,
                'total_price' => $total,
                'empty_returned' => $item['empty_returned'] ?? 0,
            ]);

            $stock = Stock::firstOrCreate(
                ['gas_type_id' => $item['gas_type_id']],
                ['full_cylinders' => 0, 'empty_cylinders' => 0]
            );

            $stock->decrement('full_cylinders', $item['quantity']);
            $stock->increment('empty_cylinders', $item['empty_returned'] ?? 0);
        }

        return redirect()->route('orders.index')->with('success', 'Order created');
    }

    public function updateStatus(CustomerOrder $order, Request $request)
    {
        $request->validate([
            'status' => 'required|in:pending,loaded,delivered,completed',
        ]);

        $order->update(['status' => $request->status]);
        return back()->with('success', 'Order status updated');
    }
}

