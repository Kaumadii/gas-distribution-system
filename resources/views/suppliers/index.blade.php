@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-7">
        <h4 class="mb-3">Suppliers</h4>
        <table class="table table-bordered table-sm">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Contact</th>
                    <th>Rates</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($suppliers as $s)
                <tr>
                    <td>{{ $s->name }}</td>
                    <td>{{ $s->contact_person }}<br>{{ $s->phone }}</td>
                    <td>
                        @foreach($s->rates as $rate)
                            <span class="badge text-bg-secondary">{{ $rate->gasType->name }}: {{ number_format($rate->rate, 2) }}</span>
                        @endforeach
                    </td>
                    <td>
                        <a href="{{ route('suppliers.edit', $s) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                        <form method="POST" action="{{ route('suppliers.destroy', $s) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this supplier?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="col-md-5">
        <h4 class="mb-3">Add Supplier</h4>
        <form method="POST" action="{{ route('suppliers.store') }}" class="card card-body">
            @csrf
            <div class="mb-2"><input class="form-control" name="name" placeholder="Name" required></div>
            <div class="mb-2"><input class="form-control" name="contact_person" placeholder="Contact person"></div>
            <div class="mb-2"><input class="form-control" name="phone" placeholder="Phone"></div>
            <div class="mb-2"><input class="form-control" name="email" placeholder="Email"></div>
            <div class="mb-2"><input class="form-control" name="address" placeholder="Address"></div>
            <div class="mb-3">
                <label class="form-label">Rates</label>
                @foreach($gasTypes as $gas)
                    <div class="input-group mb-1">
                        <span class="input-group-text">{{ $gas->name }}</span>
                        <input type="hidden" name="rates[{{ $loop->index }}][gas_type_id]" value="{{ $gas->id }}">
                        <input type="number" step="0.01" class="form-control" name="rates[{{ $loop->index }}][rate]" placeholder="Rate">
                    </div>
                @endforeach
            </div>
            <button class="btn btn-primary">Save</button>
        </form>
    </div>
</div>
@endsection

