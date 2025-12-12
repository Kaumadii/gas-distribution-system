@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Supplier & Refill Tracking</h4>
</div>

<!-- Total Gas Refills by Cylinder Type -->
<div class="card card-body mb-3">
    <h5>Total Gas Refills by Cylinder Type</h5>
    @if(empty($refillSummary))
        <div class="alert alert-info">No gas refills recorded yet.</div>
    @else
        <table class="table table-bordered table-sm">
            <thead class="table-light">
                <tr>
                    <th>Supplier</th>
                    <th>Cylinder Type</th>
                    <th>Ordered</th>
                    <th>Received</th>
                    <th>Gas Refills Charged</th>
                </tr>
            </thead>
            <tbody>
                @foreach($suppliers as $supplier)
                    @if(isset($refillSummary[$supplier->id]))
                        @foreach($refillSummary[$supplier->id] as $gasType => $data)
                            <tr>
                                <td>{{ $supplier->name }}</td>
                                <td><strong>{{ $gasType }}</strong></td>
                                <td>{{ $data['ordered'] }}</td>
                                <td>{{ $data['received'] }}</td>
                                <td><strong>{{ number_format($data['charged'], 2) }}</strong></td>
                            </tr>
                        @endforeach
                    @endif
                @endforeach
            </tbody>
        </table>
    @endif
</div>

<div class="card card-body">
    <h5>Suppliers Summary</h5>
    <table class="table table-bordered table-sm">
        <thead class="table-light">
            <tr>
                <th>Supplier</th>
                <th>Total POs</th>
                <th>Total PO Value</th>
                <th>Total Paid</th>
                <th>Cylinders Ordered</th>
                <th>Cylinders Received</th>
                <th>Gas Refills Charged</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($suppliers as $supplier)
                @php
                    $totalPOValue = $supplier->purchaseOrders->sum('total_value');
                    $totalPaid = $supplier->purchaseOrders->sum(function($po) {
                        return $po->payments->sum('amount');
                    });
                    $cylindersOrdered = $supplier->purchaseOrders->sum(function($po) {
                        return $po->items->sum('quantity');
                    });
                    $cylindersReceived = 0;
                    $gasRefillsCharged = 0;
                    foreach($supplier->purchaseOrders as $po) {
                        foreach($po->goodsReceivedNotes as $grn) {
                            $cylindersReceived += $grn->items->sum('received_qty');
                        }
                        $gasRefillsCharged += $po->items->sum('total_price');
                    }
                @endphp
                <tr>
                    <td><strong>{{ $supplier->name }}</strong></td>
                    <td>{{ $supplier->purchaseOrders->count() }}</td>
                    <td>{{ number_format($totalPOValue, 2) }}</td>
                    <td>{{ number_format($totalPaid, 2) }}</td>
                    <td>{{ $cylindersOrdered }}</td>
                    <td>{{ $cylindersReceived }}</td>
                    <td><strong>{{ number_format($gasRefillsCharged, 2) }}</strong></td>
                    <td>
                        <a href="{{ route('supplier-tracking.show', $supplier) }}" class="btn btn-sm btn-outline-primary">View Report</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

