@extends('layouts.app')

@section('content')
<h4 class="mb-3">New Customer Order</h4>
<form method="POST" action="{{ route('orders.store') }}" class="card card-body">
    @csrf
    <div class="row mb-3">
        <div class="col-md-4">
            <label class="form-label">Customer</label>
            <select name="customer_id" class="form-select" required>
                <option value="">Select</option>
                @foreach($customers as $c)
                    <option value="{{ $c->id }}">{{ $c->name }} ({{ ucfirst($c->type) }})</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Delivery Route</label>
            <select name="delivery_route_id" class="form-select">
                <option value="">None</option>
                @foreach($routes as $r)
                    <option value="{{ $r->id }}">{{ $r->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Scheduled Date</label>
            <input type="date" name="scheduled_date" class="form-control">
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="urgent" value="1" id="urgent">
                <label class="form-check-label" for="urgent">Urgent</label>
            </div>
        </div>
    </div>
    @if($errors->has('stock'))
        <div class="alert alert-danger">
            <strong>Stock Issues:</strong>
            <ul class="mb-0">
                @foreach($errors->get('stock') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <h6>Items</h6>
    <div class="mb-2">
        <small class="text-muted">Stock Availability:</small>
        @foreach($gasTypes as $g)
            @php
                $stock = $stocks->get($g->id);
                $available = $stock ? $stock->full_cylinders : 0;
            @endphp
            <span class="badge text-bg-{{ $available > 0 ? 'success' : 'danger' }} ms-1">{{ $g->name }}: {{ $available }}</span>
        @endforeach
    </div>
    @for($i=0; $i<3; $i++)
        <div class="row mb-2">
            <div class="col-md-6">
                <select name="items[{{ $i }}][gas_type_id]" class="form-select" @if($i==0) required @endif>
                    <option value="">Gas Type</option>
                    @foreach($gasTypes as $g)
                        @php
                            $stock = $stocks->get($g->id);
                            $available = $stock ? $stock->full_cylinders : 0;
                        @endphp
                        <option value="{{ $g->id }}" data-stock="{{ $available }}">
                            {{ $g->name }} @if($available >= 0) (Stock: {{ $available }}) @endif
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <input type="number" name="items[{{ $i }}][quantity]" class="form-control" placeholder="Qty" min="1" @if($i==0) required @endif>
            </div>
            <div class="col-md-3">
                <input type="number" name="items[{{ $i }}][empty_returned]" class="form-control" placeholder="Empty returned" min="0">
            </div>
        </div>
    @endfor
    <button class="btn btn-primary mt-2">Create Order</button>
</form>
@endsection

