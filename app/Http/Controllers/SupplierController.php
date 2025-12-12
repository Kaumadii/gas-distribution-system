<?php

namespace App\Http\Controllers;

use App\Models\GasType;
use App\Models\Supplier;
use App\Models\SupplierRate;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::with('rates.gasType')->orderBy('name')->get();
        $gasTypes = GasType::orderBy('name')->get();
        return view('suppliers.index', compact('suppliers', 'gasTypes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'address' => 'nullable|string|max:500',
            'rates' => 'array',
            'rates.*.gas_type_id' => 'required_with:rates.*.rate|exists:gas_types,id',
            'rates.*.rate' => 'required_with:rates.*.gas_type_id|numeric|min:0',
        ]);

        $supplier = Supplier::create($data);

        if (!empty($data['rates'])) {
            foreach ($data['rates'] as $rate) {
                SupplierRate::create([
                    'supplier_id' => $supplier->id,
                    'gas_type_id' => $rate['gas_type_id'],
                    'rate' => $rate['rate'],
                ]);
            }
        }

        return back()->with('success', 'Supplier created');
    }

    public function edit(Supplier $supplier)
    {
        $supplier->load('rates.gasType');
        $gasTypes = GasType::orderBy('name')->get();
        return view('suppliers.edit', compact('supplier', 'gasTypes'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'address' => 'nullable|string|max:500',
            'rates' => 'array',
            'rates.*.gas_type_id' => 'required_with:rates.*.rate|exists:gas_types,id',
            'rates.*.rate' => 'required_with:rates.*.gas_type_id|numeric|min:0',
        ]);

        $supplier->update($data);

        // Update rates
        if (!empty($data['rates'])) {
            // Delete existing rates
            SupplierRate::where('supplier_id', $supplier->id)->delete();
            
            // Create new rates
            foreach ($data['rates'] as $rate) {
                if (!empty($rate['rate'])) {
                    SupplierRate::create([
                        'supplier_id' => $supplier->id,
                        'gas_type_id' => $rate['gas_type_id'],
                        'rate' => $rate['rate'],
                    ]);
                }
            }
        }

        return redirect()->route('suppliers.index')->with('success', 'Supplier updated');
    }

    public function destroy(Supplier $supplier)
    {
        // Check if supplier has any purchase orders
        if ($supplier->purchaseOrders()->count() > 0) {
            return back()->withErrors(['error' => 'Cannot delete supplier with existing purchase orders']);
        }

        $supplier->delete();
        return redirect()->route('suppliers.index')->with('success', 'Supplier deleted');
    }
}

