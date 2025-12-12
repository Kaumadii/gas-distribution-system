@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Supplier Ledger</h4>
    <a href="{{ route('supplier-payments.index') }}" class="btn btn-outline-secondary btn-sm">Back to Payments</a>
</div>

<table class="table table-bordered table-sm">
    <thead class="table-light">
        <tr>
            <th>Supplier</th>
            <th>Total PO Value</th>
            <th>Amount Paid</th>
            <th>Balance</th>
            <th>Overpayment</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($ledger as $entry)
        <tr>
            <td>{{ $entry['supplier']->name }}</td>
            <td>{{ number_format($entry['total_po_value'], 2) }}</td>
            <td>{{ number_format($entry['total_paid'], 2) }}</td>
            <td>
                @if($entry['balance'] > 0)
                    <span class="text-danger">{{ number_format($entry['balance'], 2) }}</span>
                @else
                    <span class="text-success">0.00</span>
                @endif
            </td>
            <td>
                @if($entry['overpayment'] > 0)
                    <span class="text-warning">{{ number_format($entry['overpayment'], 2) }}</span>
                @else
                    <span>0.00</span>
                @endif
            </td>
            <td>
                @if($entry['overpayment'] > 0)
                    <span class="badge text-bg-warning">Overpaid</span>
                @elseif($entry['balance'] > 0)
                    <span class="badge text-bg-danger">Outstanding</span>
                @else
                    <span class="badge text-bg-success">Settled</span>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
    <tfoot class="table-light">
        <tr>
            <th>Total</th>
            <th>{{ number_format($ledger->sum('total_po_value'), 2) }}</th>
            <th>{{ number_format($ledger->sum('total_paid'), 2) }}</th>
            <th>{{ number_format($ledger->sum('balance'), 2) }}</th>
            <th>{{ number_format($ledger->sum('overpayment'), 2) }}</th>
            <th></th>
        </tr>
    </tfoot>
</table>
@endsection

