<?php

namespace App\Http\Controllers;

use App\Models\DeliveryRoute;
use App\Models\RouteStop;
use Illuminate\Http\Request;

class DeliveryRouteController extends Controller
{
    public function index()
    {
        $routes = DeliveryRoute::with(['stops.customer', 'orders.customer', 'orders.items.gasType'])
            ->latest()
            ->get()
            ->map(function ($route) {
                $route->pending_count = $route->pendingDeliveriesCount();
                $route->time_diff = $route->getTimeDifference();
                return $route;
            });
        $customers = \App\Models\Customer::orderBy('name')->get();
        return view('routes.index', compact('routes', 'customers'));
    }

    public function updateActualStart(Request $request, DeliveryRoute $route)
    {
        $data = $request->validate([
            'actual_start' => 'required|date',
        ]);

        $route->update(['actual_start' => $data['actual_start']]);

        return back()->with('success', 'Actual start time recorded');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'driver' => 'nullable|string|max:255',
            'assistant' => 'nullable|string|max:255',
            'planned_start' => 'nullable|date',
        ]);

        DeliveryRoute::create($data);

        return back()->with('success', 'Route created');
    }

    public function show(DeliveryRoute $route)
    {
        $route->load(['stops.customer', 'orders.customer', 'orders.items.gasType']);
        $customers = \App\Models\Customer::orderBy('name')->get();
        return view('routes.show', compact('route', 'customers'));
    }

    public function addStop(Request $request, DeliveryRoute $route)
    {
        $data = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'planned_time' => 'nullable|date',
            'notes' => 'nullable|string|max:500',
        ]);

        RouteStop::create([
            'delivery_route_id' => $route->id,
            'customer_id' => $data['customer_id'] ?? null,
            'planned_time' => $data['planned_time'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        return back()->with('success', 'Stop added');
    }

    public function updateStopTime(Request $request, RouteStop $stop)
    {
        $data = $request->validate([
            'actual_time' => 'required|date',
        ]);

        $stop->update(['actual_time' => $data['actual_time']]);

        return back()->with('success', 'Actual time recorded');
    }

    public function destroy(DeliveryRoute $route)
    {
        // Check if route has orders
        if ($route->orders()->count() > 0) {
            return back()->with('error', "Cannot delete route '{$route->name}'. This route has orders associated with it.");
        }

        $route->delete();
        return redirect()->route('routes.index')->with('success', 'Route deleted successfully.');
    }
}

