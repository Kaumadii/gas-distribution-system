<?php

namespace App\Http\Controllers;

use App\Models\GasType;
use Illuminate\Http\Request;

class GasTypeController extends Controller
{
    public function index()
    {
        $gasTypes = GasType::orderBy('name')->get();
        return view('gas-types.index', compact('gasTypes'));
    }

    public function create()
    {
        return view('gas-types.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'weight_kg' => 'nullable|numeric|min:0',
            'default_price' => 'required|numeric|min:0',
        ]);

        GasType::create($data);

        return redirect()->route('gas-types.index')->with('success', 'Gas type created');
    }
}

