<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerOrder;
use App\Models\DeliveryRoute;
use App\Models\GoodsReceivedNote;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Models\Stock;

class DashboardController extends Controller
{
    public function __invoke()
    {
        return view('dashboard', [
            'poPending' => PurchaseOrder::where('status', 'pending')->count(),
            'poApproved' => PurchaseOrder::where('status', 'approved')->count(),
            'poCompleted' => PurchaseOrder::where('status', 'completed')->count(),
            'grnPending' => GoodsReceivedNote::where('status', 'pending')->count(),
            'suppliers' => Supplier::count(),
            'customers' => Customer::count(),
            'routes' => DeliveryRoute::count(),
            'openOrders' => CustomerOrder::whereIn('status', ['pending', 'loaded', 'delivered'])->count(),
            'payments' => SupplierPayment::sum('amount'),
            'stock' => Stock::with('gasType')->get(),
        ]);
    }
}

