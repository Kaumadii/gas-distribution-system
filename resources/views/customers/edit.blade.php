@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <h4 class="mb-3">Edit Customer</h4>
        <form method="POST" action="{{ route('customers.update', $customer) }}" class="card card-body">
            @csrf
            @method('PUT')
            <div class="mb-2">
                <label class="form-label">Name</label>
                <input class="form-control" name="name" value="{{ old('name', $customer->name) }}" required>
            </div>
            <div class="mb-2">
                <label class="form-label">Type</label>
                <select class="form-select" name="type" required>
                    <option value="dealer" {{ old('type', $customer->type) == 'dealer' ? 'selected' : '' }}>Dealer</option>
                    <option value="commercial" {{ old('type', $customer->type) == 'commercial' ? 'selected' : '' }}>Commercial</option>
                    <option value="individual" {{ old('type', $customer->type) == 'individual' ? 'selected' : '' }}>Individual</option>
                </select>
            </div>
            <div class="mb-2">
                <label class="form-label">Phone</label>
                <input class="form-control" name="phone" value="{{ old('phone', $customer->phone) }}">
            </div>
            <div class="mb-2">
                <label class="form-label">Email</label>
                <input class="form-control" type="email" name="email" value="{{ old('email', $customer->email) }}">
            </div>
            <div class="mb-2">
                <label class="form-label">Address</label>
                <input class="form-control" name="address" value="{{ old('address', $customer->address) }}">
            </div>
            <div class="mb-2">
                <label class="form-label">Credit Limit</label>
                <input class="form-control" type="number" step="0.01" name="credit_limit" value="{{ old('credit_limit', $customer->credit_limit) }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">
                    Custom Price Overrides <small class="text-muted">(Optional)</small>
                </label>
                <small class="d-block text-muted mb-2">
                    Current category pricing for <strong>{{ ucfirst($customer->type) }}</strong>:
                    @foreach($gasTypes as $gas)
                        @php
                            $categoryPrice = match($customer->type) {
                                'dealer' => $gas->dealer_price,
                                'commercial' => $gas->commercial_price,
                                'individual' => $gas->individual_price,
                                default => $gas->default_price,
                            };
                        @endphp
                        <span class="badge text-bg-info">{{ $gas->name }}: {{ number_format($categoryPrice ?? $gas->default_price, 2) }}</span>
                    @endforeach
                </small>
                <small class="d-block text-muted mb-2">Leave blank to use category-based pricing. Enter values to override.</small>
                @foreach($gasTypes as $gas)
                    @php
                        $existingPrice = $customer->customPrices->where('gas_type_id', $gas->id)->first();
                        $categoryPrice = match($customer->type) {
                            'dealer' => $gas->dealer_price,
                            'commercial' => $gas->commercial_price,
                            'individual' => $gas->individual_price,
                            default => $gas->default_price,
                        };
                    @endphp
                    <div class="input-group mb-1">
                        <span class="input-group-text" style="min-width: 80px;">{{ $gas->name }}</span>
                        <input type="hidden" name="prices[{{ $loop->index }}][gas_type_id]" value="{{ $gas->id }}">
                        <input type="number" step="0.01" class="form-control" name="prices[{{ $loop->index }}][custom_price]" 
                               value="{{ old("prices.{$loop->index}.custom_price", $existingPrice->custom_price ?? '') }}" 
                               placeholder="Override (Cat: {{ number_format($categoryPrice ?? $gas->default_price, 2) }})">
                    </div>
                @endforeach
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-primary">Update Customer</button>
                <a href="{{ route('customers.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

