@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Purchase Orders</h4>
    <a href="{{ route('purchase-orders.create') }}" class="btn btn-primary btn-sm">Create Purchase Order</a>
</div>

<!-- Filters -->
<div class="card card-body mb-3">
    <form method="GET" action="{{ route('purchase-orders.index') }}" class="row g-2">
        <div class="col-md-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select form-select-sm">
                <option value="">All</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Supplier</label>
            <select name="supplier_id" class="form-select form-select-sm">
                <option value="">All</option>
                @foreach($suppliers as $supplier)
                    <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Date From</label>
            <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label">Date To</label>
            <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-sm btn-outline-primary me-1">Filter</button>
            <a href="{{ route('purchase-orders.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
        </div>
    </form>
</div>

@if($pos->isEmpty())
    <div class="alert alert-info">
        No purchase orders found. <a href="{{ route('purchase-orders.create') }}">Create your first PO</a>
    </div>
@else
    <table class="table table-bordered table-sm">
        <thead class="table-light">
            <tr>
                <th>PO Number</th>
                <th>Supplier</th>
                <th>Status</th>
                <th>Expected Date</th>
                <th>Total Value</th>
                <th>Created</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pos as $po)
            <tr>
                <td><strong>{{ $po->po_number }}</strong></td>
                <td>{{ $po->supplier->name }}</td>
                <td>
                    <form method="POST" action="{{ route('purchase-orders.update-status', $po) }}" class="d-inline">
                        @csrf
                        @method('PATCH')
                        <select name="status" class="form-select form-select-sm status-select" onchange="this.form.submit()" 
                                data-status="{{ $po->status }}"
                                style="width: auto; display: inline-block;">
                            <option value="pending" {{ $po->status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ $po->status === 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="completed" {{ $po->status === 'completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                    </form>
                </td>
                <td>{{ $po->expected_date ? \Carbon\Carbon::parse($po->expected_date)->format('Y-m-d') : '-' }}</td>
                <td><strong>{{ number_format($po->total_value, 2) }}</strong></td>
                <td>{{ $po->created_at->format('Y-m-d') }}</td>
            </tr>
            <tr>
                <td colspan="6" class="bg-light">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <strong>Items:</strong>
                            @foreach($po->items as $item)
                                <span class="badge text-bg-light me-1">
                                    {{ $item->gasType->name }} × {{ $item->quantity }} @ {{ number_format($item->unit_price, 2) }} = {{ number_format($item->total_price, 2) }}
                                </span>
                            @endforeach
                        </div>
                        <div>
                            <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#invoiceModal{{ $po->id }}">
                                @if($po->invoice_amount)
                                    Invoice: {{ number_format($po->invoice_amount, 2) }}
                                @else
                                    Add Invoice
                                @endif
                            </button>
                        </div>
                    </div>
                </td>
            </tr>
            
            <!-- Invoice Modal -->
            <div class="modal fade" id="invoiceModal{{ $po->id }}" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Invoice Details - {{ $po->po_number }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="POST" action="{{ route('purchase-orders.update-invoice', $po) }}">
                            @csrf
                            @method('PATCH')
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">PO Value</label>
                                    <input type="text" class="form-control" value="{{ number_format($po->total_value, 2) }}" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Invoice Amount</label>
                                    <input type="number" step="0.01" name="invoice_amount" class="form-control" value="{{ $po->invoice_amount ?? '' }}" placeholder="Enter invoice amount">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Invoice Number</label>
                                    <input type="text" name="invoice_number" class="form-control" value="{{ $po->invoice_number ?? '' }}" placeholder="Invoice number">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Invoice Date</label>
                                    <input type="date" name="invoice_date" class="form-control" value="{{ $po->invoice_date ?? '' }}">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary">Save Invoice</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </tbody>
    </table>
@endif

<style>
.status-select {
    font-weight: 600;
    border-width: 2px;
}

.status-select[data-status="pending"] {
    background-color: #6c757d;
    color: white;
    border-color: #6c757d;
}

.status-select[data-status="pending"]:focus {
    background-color: #6c757d;
    color: white;
    border-color: #6c757d;
    box-shadow: 0 0 0 0.25rem rgba(108, 117, 125, 0.25);
}

.status-select[data-status="approved"] {
    background-color: #0dcaf0;
    color: #000;
    border-color: #0dcaf0;
}

.status-select[data-status="approved"]:focus {
    background-color: #0dcaf0;
    color: #000;
    border-color: #0dcaf0;
    box-shadow: 0 0 0 0.25rem rgba(13, 202, 240, 0.25);
}

.status-select[data-status="completed"] {
    background-color: #198754;
    color: white;
    border-color: #198754;
}

.status-select[data-status="completed"]:focus {
    background-color: #198754;
    color: white;
    border-color: #198754;
    box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25);
}

.status-select option[value="pending"] {
    background-color: #6c757d;
    color: white;
}

.status-select option[value="approved"] {
    background-color: #0dcaf0;
    color: #000;
}

.status-select option[value="completed"] {
    background-color: #198754;
    color: white;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update select background color when status changes
    const statusSelects = document.querySelectorAll('.status-select');
    statusSelects.forEach(select => {
        function updateColor() {
            const status = select.value;
            select.setAttribute('data-status', status);
            select.className = 'form-select form-select-sm status-select';
            
            if (status === 'pending') {
                select.style.backgroundColor = '#6c757d';
                select.style.color = 'white';
                select.style.borderColor = '#6c757d';
            } else if (status === 'approved') {
                select.style.backgroundColor = '#0dcaf0';
                select.style.color = '#000';
                select.style.borderColor = '#0dcaf0';
            } else if (status === 'completed') {
                select.style.backgroundColor = '#198754';
                select.style.color = 'white';
                select.style.borderColor = '#198754';
            }
        }
        
        updateColor();
        select.addEventListener('change', updateColor);
    });
});
</script>
@endsection

