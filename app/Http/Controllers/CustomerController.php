<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerGasPrice;
use App\Models\GasType;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::with(['customPrices.gasType', 'orders.items'])->orderBy('name')->get();
        $gasTypes = GasType::orderBy('name')->get();
        return view('customers.index', compact('customers', 'gasTypes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:dealer,commercial,individual',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'credit_limit' => 'required|numeric|min:0',
            'prices' => 'array',
            'prices.*.gas_type_id' => 'required_with:prices.*.custom_price|exists:gas_types,id',
            'prices.*.custom_price' => 'required_with:prices.*.gas_type_id|numeric|min:0',
        ]);

        $customer = Customer::create([
            'name' => $data['name'],
            'type' => $data['type'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'credit_limit' => $data['credit_limit'],
            'outstanding_balance' => 0,
        ]);

        if (!empty($data['prices'])) {
            foreach ($data['prices'] as $price) {
                CustomerGasPrice::create([
                    'customer_id' => $customer->id,
                    'gas_type_id' => $price['gas_type_id'],
                    'custom_price' => $price['custom_price'],
                ]);
            }
        }

        return back()->with('success', 'Customer created');
    }

    public function edit(Customer $customer)
    {
        $gasTypes = GasType::orderBy('name')->get();
        $customer->load('customPrices.gasType');
        return view('customers.edit', compact('customer', 'gasTypes'));
    }

    public function update(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:dealer,commercial,individual',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'credit_limit' => 'required|numeric|min:0',
            'prices' => 'array',
            'prices.*.gas_type_id' => 'required_with:prices.*.custom_price|exists:gas_types,id',
            'prices.*.custom_price' => 'required_with:prices.*.gas_type_id|numeric|min:0',
        ]);

        $customer->update([
            'name' => $data['name'],
            'type' => $data['type'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'credit_limit' => $data['credit_limit'],
        ]);

        // Update custom prices
        CustomerGasPrice::where('customer_id', $customer->id)->delete();
        if (!empty($data['prices'])) {
            foreach ($data['prices'] as $price) {
                if (!empty($price['custom_price'])) {
                    CustomerGasPrice::create([
                        'customer_id' => $customer->id,
                        'gas_type_id' => $price['gas_type_id'],
                        'custom_price' => $price['custom_price'],
                    ]);
                }
            }
        }

        return redirect()->route('customers.index')->with('success', 'Customer updated');
    }

    public function destroy(Customer $customer)
    {
        // Check if customer has non-completed orders
        $totalOrders = $customer->orders()->count();
        $incompleteOrders = $customer->orders()->where('status', '!=', 'completed')->count();
        
        if ($totalOrders > 0 && $incompleteOrders > 0) {
            return back()->with('error', "Cannot delete customer '{$customer->name}'. This customer has {$incompleteOrders} incomplete order(s) (status: pending, loaded, or delivered). Please complete all orders first before deleting this customer.");
        }

        // If all orders are completed, allow deletion (orders will be cascade deleted)
        // Delete custom prices
        CustomerGasPrice::where('customer_id', $customer->id)->delete();

        // Delete customer (this will cascade delete completed orders)
        $customer->delete();

        $message = $totalOrders > 0 
            ? "Customer and {$totalOrders} completed order(s) deleted successfully."
            : 'Customer deleted successfully.';

        return redirect()->route('customers.index')->with('success', $message);
    }
}

