@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <h4 class="mb-3">Edit Supplier</h4>
        <form method="POST" action="{{ route('suppliers.update', $supplier) }}" class="card card-body">
            @csrf
            @method('PUT')
            <div class="mb-2">
                <label class="form-label">Name</label>
                <input class="form-control" name="name" value="{{ old('name', $supplier->name) }}" required>
            </div>
            <div class="mb-2">
                <label class="form-label">Contact Person</label>
                <input class="form-control" name="contact_person" value="{{ old('contact_person', $supplier->contact_person) }}">
            </div>
            <div class="mb-2">
                <label class="form-label">Phone</label>
                <input class="form-control" name="phone" value="{{ old('phone', $supplier->phone) }}">
            </div>
            <div class="mb-2">
                <label class="form-label">Email</label>
                <input class="form-control" type="email" name="email" value="{{ old('email', $supplier->email) }}">
            </div>
            <div class="mb-2">
                <label class="form-label">Address</label>
                <input class="form-control" name="address" value="{{ old('address', $supplier->address) }}">
            </div>
            <div class="mb-3">
                <label class="form-label">Rates</label>
                @foreach($gasTypes as $gas)
                    @php
                        $existingRate = $supplier->rates->where('gas_type_id', $gas->id)->first();
                    @endphp
                    <div class="input-group mb-1">
                        <span class="input-group-text">{{ $gas->name }}</span>
                        <input type="hidden" name="rates[{{ $loop->index }}][gas_type_id]" value="{{ $gas->id }}">
                        <input type="number" step="0.01" class="form-control" name="rates[{{ $loop->index }}][rate]" 
                               value="{{ old("rates.{$loop->index}.rate", $existingRate->rate ?? '') }}" 
                               placeholder="Rate">
                    </div>
                @endforeach
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-primary">Update Supplier</button>
                <a href="{{ route('suppliers.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

