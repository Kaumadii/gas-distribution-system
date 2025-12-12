@extends('layouts.app')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-2">
        <div class="card text-bg-light">
            <div class="card-body">
                <div class="fw-bold">PO Pending</div>
                <div class="display-6">{{ $poPending }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-bg-light">
            <div class="card-body">
                <div class="fw-bold">PO Approved</div>
                <div class="display-6">{{ $poApproved }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-bg-light">
            <div class="card-body">
                <div class="fw-bold">PO Completed</div>
                <div class="display-6">{{ $poCompleted }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-bg-light">
            <div class="card-body">
                <div class="fw-bold">GRN Pending</div>
                <div class="display-6">{{ $grnPending }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-bg-light">
            <div class="card-body">
                <div class="fw-bold">Customers</div>
                <div class="display-6">{{ $customers }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-bg-light">
            <div class="card-body">
                <div class="fw-bold">Routes</div>
                <div class="display-6">{{ $routes }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="fw-bold">Open Orders</div>
                <div class="display-6">{{ $openOrders }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="fw-bold">Suppliers</div>
                <div class="display-6">{{ $suppliers }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="fw-bold">Payments</div>
                <div class="display-6">Rs {{ number_format($payments, 2) }}</div>
            </div>
        </div>
    </div>
</div>

<h5 class="mt-4 mb-2">Stock Summary</h5>
<table class="table table-sm table-bordered">
    <thead class="table-light">
        <tr>
            <th>Gas Type</th>
            <th>Full Cylinders</th>
            <th>Empty Cylinders</th>
        </tr>
    </thead>
    <tbody>
        @foreach($stock as $s)
            <tr>
                <td>{{ $s->gasType->name }}</td>
                <td>{{ $s->full_cylinders }}</td>
                <td>{{ $s->empty_cylinders }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
@endsection

