@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <h4 class="mb-3">Add Customer</h4>
        <form method="POST" action="{{ route('customers.store') }}" class="card card-body mb-4">
            @csrf
            <div class="mb-2">
                <label class="form-label">Name <span class="text-danger">*</span></label>
                <input class="form-control" name="name" placeholder="Customer Name" required>
            </div>
            <div class="mb-2">
                <label class="form-label">Customer Type <span class="text-danger">*</span></label>
                <select class="form-select" name="type" required id="customer_type">
                    <option value="">Select Type</option>
                    <option value="dealer">Dealer</option>
                    <option value="commercial">Commercial</option>
                    <option value="individual">Individual</option>
                </select>
                <small class="text-muted">Category-based pricing will apply automatically</small>
            </div>
            <div class="mb-2">
                <label class="form-label">Phone</label>
                <input class="form-control" name="phone" placeholder="Phone Number">
            </div>
            <div class="mb-2">
                <label class="form-label">Email</label>
                <input class="form-control" type="email" name="email" placeholder="Email Address">
            </div>
            <div class="mb-2">
                <label class="form-label">Address</label>
                <input class="form-control" name="address" placeholder="Address">
            </div>
            <div class="mb-2">
                <label class="form-label">Credit Limit <span class="text-danger">*</span></label>
                <input class="form-control" type="number" step="0.01" name="credit_limit" placeholder="0.00" required>
            </div>
            <div class="mb-3">
                <label class="form-label">
                    Custom Price Overrides <small class="text-muted">(Optional)</small>
                </label>
                <small class="d-block text-muted mb-2">Leave blank to use category-based pricing. Enter values to override.</small>
                @foreach($gasTypes as $gas)
                    <div class="input-group mb-1">
                        <span class="input-group-text" style="min-width: 80px;">{{ $gas->name }}</span>
                        <input type="hidden" name="prices[{{ $loop->index }}][gas_type_id]" value="{{ $gas->id }}">
                        <input type="number" step="0.01" class="form-control" name="prices[{{ $loop->index }}][custom_price]" 
                               placeholder="Override price">
                    </div>
                @endforeach
            </div>
            <button class="btn btn-primary">Save Customer</button>
        </form>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <h4 class="mb-3">Customers</h4>
        <table class="table table-bordered table-sm">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Credit Limit</th>
                    <th>Outstanding Balance</th>
                    <th>Cylinders</th>
                    <th>Pricing</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($customers as $c)
                @php
                    $creditUsed = ($c->outstanding_balance / $c->credit_limit) * 100;
                    $creditStatus = $creditUsed >= 90 ? 'danger' : ($creditUsed >= 70 ? 'warning' : 'success');
                @endphp
                <tr>
                    <td><strong>{{ $c->name }}</strong><br>
                        <small class="text-muted">{{ $c->phone ?? 'No phone' }}</small>
                    </td>
                    <td>
                        <span class="badge 
                            @if($c->type === 'dealer') text-bg-primary
                            @elseif($c->type === 'commercial') text-bg-info
                            @else text-bg-secondary
                            @endif">
                            {{ ucfirst($c->type) }}
                        </span>
                    </td>
                    <td>{{ number_format($c->credit_limit, 2) }}</td>
                    <td>
                        <span class="badge text-bg-{{ $creditStatus }}">
                            {{ number_format($c->outstanding_balance, 2) }}
                        </span>
                        @if($c->credit_limit > 0)
                            <br><small class="text-muted">{{ number_format($creditUsed, 1) }}% used</small>
                        @endif
                    </td>
                    <td>
                        <small>
                            <strong>Full Issued:</strong> {{ $c->totalFullCylindersIssued() }}<br>
                            <strong>Empty Returned:</strong> {{ $c->totalEmptyCylindersReturned() }}<br>
                            <span class="badge text-bg-{{ $c->cylindersOutstanding() > 0 ? 'warning' : 'success' }}">
                                Outstanding: {{ $c->cylindersOutstanding() }}
                            </span>
                        </small>
                    </td>
                    <td>
                        @if($c->customPrices->count() > 0)
                            <small><strong>Custom Overrides:</strong><br></small>
                            @foreach($c->customPrices as $price)
                                <span class="badge text-bg-warning">{{ $price->gasType->name }}: {{ number_format($price->custom_price,2) }}</span>
                            @endforeach
                        @else
                            <small class="text-muted">Using category pricing</small>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('customers.edit', $c) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                            <form method="POST" action="{{ route('customers.destroy', $c) }}" class="d-inline" 
                                  onsubmit="return confirm('Are you sure you want to delete this customer?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

