@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Supplier Payment Management</h4>
    <a href="{{ route('supplier-payments.ledger') }}" class="btn btn-outline-primary btn-sm">View Supplier Ledger</a>
</div>

<!-- Create Payment Request Form - Top/Middle -->
<div class="row mb-4">
    <div class="col-md-8 offset-md-2">
        <h5 class="mb-3">Create Payment Request</h5>
        <form method="POST" action="{{ route('supplier-payments.store') }}" class="card card-body" id="paymentForm">
            @csrf
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Purchase Order <span class="text-danger">*</span></label>
                    <select name="purchase_order_id" id="po_select" class="form-select" required>
                        <option value="">Select Purchase Order</option>
                        @foreach($pos as $po)
                            <option value="{{ $po->id }}" 
                                    data-po-value="{{ $po->total_value }}"
                                    data-paid="{{ $po->totalPaid() }}"
                                    data-balance="{{ $po->balance() }}">
                                {{ $po->po_number }} - {{ $po->supplier->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Payment Amount <span class="text-danger">*</span></label>
                    <input class="form-control" name="amount" id="amount" type="number" step="0.01" placeholder="Enter amount" required>
                </div>
            </div>
            
            <div id="po-details" class="mb-3 p-3 bg-light rounded" style="display: none;">
                <div class="row">
                    <div class="col-md-4">
                        <small><strong>PO Value:</strong> <span id="po-value" class="text-primary">0.00</span></small>
                    </div>
                    <div class="col-md-4">
                        <small><strong>Already Paid:</strong> <span id="po-paid">0.00</span></small>
                    </div>
                    <div class="col-md-4">
                        <small><strong>Balance Due:</strong> <span id="po-balance" class="text-danger fw-bold">0.00</span></small>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Payment Mode <span class="text-danger">*</span></label>
                    <select name="mode" class="form-select" required>
                        <option value="cheque" selected>Cheque</option>
                        <option value="cash">Cash</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label class="form-label">Reference Number</label>
                    <input class="form-control" name="reference_number" placeholder="Cheque number, transaction ID, etc.">
                </div>
                
                <div class="col-md-4 mb-3">
                    <label class="form-label">Payment Date</label>
                    <input class="form-control" type="date" name="paid_at" value="{{ date('Y-m-d') }}">
                </div>
            </div>
            
            <div class="text-center">
                <button type="submit" class="btn btn-primary btn-lg">Record Payment</button>
            </div>
        </form>
    </div>
</div>

<!-- Payment History Table - Below Form -->
<div class="row">
    <div class="col-12">
        <h5 class="mb-3">Payment History</h5>
        @if($payments->isEmpty())
            <div class="alert alert-info">No payments recorded yet.</div>
        @else
            <table class="table table-bordered table-sm">
                <thead class="table-light">
                    <tr>
                        <th>PO Number</th>
                        <th>Supplier</th>
                        <th>PO Value</th>
                        <th>Amount Paid</th>
                        <th>Balance</th>
                        <th>Payment Mode</th>
                        <th>Reference</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payments as $p)
                    @php
                        $po = $p->purchaseOrder;
                    @endphp
                    <tr>
                        <td><strong>{{ $po->po_number }}</strong></td>
                        <td>{{ $po->supplier->name }}</td>
                        <td>{{ number_format($po->total_value, 2) }}</td>
                        <td><strong>{{ number_format($p->amount, 2) }}</strong></td>
                        <td>
                            <span class="{{ $po->balance() > 0 ? 'text-danger fw-bold' : 'text-success' }}">
                                {{ number_format($po->balance(), 2) }}
                            </span>
                        </td>
                        <td><span class="badge text-bg-secondary">{{ ucfirst($p->mode) }}</span></td>
                        <td>{{ $p->reference_number ?? '-' }}</td>
                        <td>{{ $p->paid_at ? \Carbon\Carbon::parse($p->paid_at)->format('Y-m-d') : $p->created_at->format('Y-m-d') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const poSelect = document.getElementById('po_select');
    const poDetails = document.getElementById('po-details');
    const poValue = document.getElementById('po-value');
    const poPaid = document.getElementById('po-paid');
    const poBalance = document.getElementById('po-balance');
    const amountInput = document.getElementById('amount');
    
    poSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            const poValueData = parseFloat(selectedOption.getAttribute('data-po-value') || 0);
            const paidData = parseFloat(selectedOption.getAttribute('data-paid') || 0);
            const balanceData = parseFloat(selectedOption.getAttribute('data-balance') || 0);
            
            poValue.textContent = poValueData.toFixed(2);
            poPaid.textContent = paidData.toFixed(2);
            poBalance.textContent = balanceData.toFixed(2);
            
            // Set default amount to balance
            amountInput.value = balanceData > 0 ? balanceData.toFixed(2) : '';
            
            poDetails.style.display = 'block';
        } else {
            poDetails.style.display = 'none';
            amountInput.value = '';
        }
    });
});
</script>
@endsection

