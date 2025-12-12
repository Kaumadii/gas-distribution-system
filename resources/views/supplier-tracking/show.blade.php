@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Supplier Report: {{ $supplier->name }}</h4>
    <a href="{{ route('supplier-tracking.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
</div>

<div class="row mb-3">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted">Total POs</h6>
                <h3>{{ $report['total_pos'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted">Total PO Value</h6>
                <h3>{{ number_format($report['total_po_value'], 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted">Total Paid</h6>
                <h3>{{ number_format($report['total_paid'], 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted">Balance</h6>
                <h3 class="{{ ($report['total_po_value'] - $report['total_paid']) > 0 ? 'text-danger' : 'text-success' }}">
                    {{ number_format($report['total_po_value'] - $report['total_paid'], 2) }}
                </h3>
            </div>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted">Cylinders Ordered</h6>
                <h3>{{ $report['total_cylinders_ordered'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted">Cylinders Received</h6>
                <h3>{{ $report['total_cylinders_received'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted">Gas Refills Charged</h6>
                <h3>{{ number_format($report['total_gas_refills_charged'], 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted">Total Invoice Amount</h6>
                <h3>{{ number_format($report['total_invoice_amount'], 2) }}</h3>
            </div>
        </div>
    </div>
</div>

<!-- Gas Refills by Cylinder Type -->
@if(!empty($refillsByType))
<div class="card card-body mb-3">
    <h5>Total Gas Refills by Cylinder Type</h5>
    <table class="table table-bordered table-sm">
        <thead class="table-light">
            <tr>
                <th>Cylinder Type</th>
                <th>Ordered</th>
                <th>Received</th>
                <th>Gas Refills Charged</th>
            </tr>
        </thead>
        <tbody>
            @foreach($refillsByType as $gasType => $data)
            <tr>
                <td><strong>{{ $gasType }}</strong></td>
                <td>{{ $data['ordered'] }}</td>
                <td>{{ $data['received'] }}</td>
                <td>{{ number_format($data['charged'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

<!-- PO vs Invoice Comparison -->
<div class="card card-body mb-3">
    <h5>Purchase Order vs Invoice Comparison</h5>
    <table class="table table-bordered table-sm">
        <thead class="table-light">
            <tr>
                <th>PO Number</th>
                <th>PO Value</th>
                <th>Invoice Amount</th>
                <th>Invoice Number</th>
                <th>Invoice Date</th>
                <th>Difference</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($report['pos'] as $poData)
                <tr>
                    <td><strong>{{ $poData['po']->po_number }}</strong></td>
                    <td>{{ number_format($poData['po_value'], 2) }}</td>
                    <td>
                        @if($poData['invoice_amount'])
                            {{ number_format($poData['invoice_amount'], 2) }}
                        @else
                            <span class="text-muted">Not recorded</span>
                        @endif
                    </td>
                    <td>{{ $poData['po']->invoice_number ?? '-' }}</td>
                    <td>{{ $poData['po']->invoice_date ? \Carbon\Carbon::parse($poData['po']->invoice_date)->format('Y-m-d') : '-' }}</td>
                    <td>
                        @if($poData['invoice_amount'])
                            @php
                                $diff = $poData['invoice_difference'];
                            @endphp
                            @if($diff > 0)
                                <span class="text-warning">+{{ number_format($diff, 2) }} (Over)</span>
                            @elseif($diff < 0)
                                <span class="text-danger">{{ number_format($diff, 2) }} (Under)</span>
                            @else
                                <span class="text-success">0.00 (Match)</span>
                            @endif
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        @if($poData['invoice_amount'])
                            @if(abs($poData['invoice_difference']) < 0.01)
                                <span class="badge text-bg-success">Matched</span>
                            @else
                                <span class="badge text-bg-warning">Mismatch</span>
                            @endif
                        @else
                            <span class="badge text-bg-secondary">No Invoice</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Purchase Orders Summary -->
<div class="card card-body mb-3">
    <h5>Purchase Orders Summary</h5>
    <table class="table table-bordered table-sm">
        <thead class="table-light">
            <tr>
                <th>PO Number</th>
                <th>Status</th>
                <th>Cylinders Ordered</th>
                <th>Cylinders Received</th>
                <th>PO Value</th>
                <th>Paid</th>
                <th>Balance</th>
            </tr>
        </thead>
        <tbody>
            @foreach($report['pos'] as $poData)
                <tr>
                    <td><strong>{{ $poData['po']->po_number }}</strong></td>
                    <td><span class="badge text-bg-{{ $poData['po']->status === 'completed' ? 'success' : ($poData['po']->status === 'approved' ? 'info' : 'secondary') }}">{{ ucfirst($poData['po']->status) }}</span></td>
                    <td>{{ $poData['ordered_qty'] }}</td>
                    <td>{{ $poData['received_qty'] }}</td>
                    <td>{{ number_format($poData['po_value'], 2) }}</td>
                    <td>{{ number_format($poData['paid'], 2) }}</td>
                    <td>
                        <span class="{{ ($poData['po_value'] - $poData['paid']) > 0 ? 'text-danger' : 'text-success' }}">
                            {{ number_format($poData['po_value'] - $poData['paid'], 2) }}
                        </span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Payment History -->
@if($paymentHistory->isNotEmpty())
<div class="card card-body">
    <h5>Supplier Payment History</h5>
    <table class="table table-bordered table-sm">
        <thead class="table-light">
            <tr>
                <th>Date</th>
                <th>PO Number</th>
                <th>Amount</th>
                <th>Mode</th>
                <th>Reference</th>
            </tr>
        </thead>
        <tbody>
            @foreach($paymentHistory as $payment)
                <tr>
                    <td>{{ $payment->paid_at ? \Carbon\Carbon::parse($payment->paid_at)->format('Y-m-d') : $payment->created_at->format('Y-m-d') }}</td>
                    <td><strong>{{ $payment->purchaseOrder->po_number }}</strong></td>
                    <td><strong>{{ number_format($payment->amount, 2) }}</strong></td>
                    <td><span class="badge text-bg-secondary">{{ ucfirst($payment->mode) }}</span></td>
                    <td>{{ $payment->reference_number ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot class="table-light">
            <tr>
                <th colspan="2">Total</th>
                <th>{{ number_format($paymentHistory->sum('amount'), 2) }}</th>
                <th colspan="2"></th>
            </tr>
        </tfoot>
    </table>
</div>
@endif
@endsection

