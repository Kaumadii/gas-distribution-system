@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Create Goods Received Note (GRN)</h4>
            <a href="{{ route('grn.index') }}" class="btn btn-secondary btn-sm">Back</a>
        </div>
        
        <!-- Alerts for Partially Received POs -->
        <div id="partialAlerts"></div>

        <form method="POST" action="{{ route('grn.store') }}" class="card card-body" id="grnForm">
            @csrf
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Supplier <span class="text-danger">*</span></label>
                    <select name="supplier_id" id="supplier_select" class="form-select" required>
                        <option value="">Select Supplier</option>
                        @foreach($pos->pluck('supplier')->unique('id') as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Purchase Order <span class="text-danger">*</span></label>
                    <select name="purchase_order_id" id="po_select" class="form-select" required>
                        <option value="">Select PO</option>
                        @foreach($pos as $po)
                            <option value="{{ $po->id }}" 
                                    data-supplier="{{ $po->supplier_id }}"
                                    data-partial="{{ $po->is_partially_received ? '1' : '0' }}">
                                {{ $po->po_number }} - {{ $po->supplier->name }}
                                @if($po->is_partially_received)
                                    (Partially Received)
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Received Date <span class="text-danger">*</span></label>
                    <input type="date" name="received_at" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
            </div>

            <h6 class="mb-3">PO Items</h6>
            <div id="po_items_container">
                <p class="text-muted">Please select a Purchase Order to load items.</p>
            </div>

            <div class="mb-3">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="3" placeholder="Additional notes about this GRN"></textarea>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Create GRN</button>
                <a href="{{ route('grn.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const supplierSelect = document.getElementById('supplier_select');
    const poSelect = document.getElementById('po_select');
    const itemsContainer = document.getElementById('po_items_container');
    const partialAlerts = document.getElementById('partialAlerts');

    // Filter POs by supplier
    supplierSelect.addEventListener('change', function() {
        const supplierId = this.value;
        poSelect.innerHTML = '<option value="">Select PO</option>';
        
        poSelect.querySelectorAll('option').forEach(option => {
            if (option.value && option.dataset.supplier === supplierId) {
                poSelect.appendChild(option.cloneNode(true));
            }
        });
        
        itemsContainer.innerHTML = '<p class="text-muted">Please select a Purchase Order to load items.</p>';
    });

    // Load PO items when PO is selected
    poSelect.addEventListener('change', function() {
        const poId = this.value;
        if (!poId) {
            itemsContainer.innerHTML = '<p class="text-muted">Please select a Purchase Order to load items.</p>';
            partialAlerts.innerHTML = '';
            return;
        }

        // Show partial alert if needed
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.dataset.partial === '1') {
            partialAlerts.innerHTML = `
                <div class="alert alert-warning alert-dismissible fade show">
                    <strong>⚠️ Partially Received PO:</strong> This PO has been partially received. Please enter remaining quantities.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
        } else {
            partialAlerts.innerHTML = '';
        }

        // Fetch PO items
        fetch(`/api/po/${poId}/items`)
            .then(response => response.json())
            .then(data => {
                let html = '<table class="table table-bordered table-sm mb-3"><thead class="table-light"><tr>';
                html += '<th>Gas Type</th><th>Ordered</th><th>Previously Received</th><th>Remaining</th>';
                html += '<th>Received (This GRN)</th><th>Damaged</th><th>Rejected</th><th>Short</th>';
                html += '</tr></thead><tbody>';

                data.items.forEach((item, index) => {
                    const defaultReceived = item.remaining_qty;
                    html += `<tr>
                        <td><strong>${item.gas_type_name}</strong></td>
                        <td>${item.ordered_qty}</td>
                        <td>${item.received_qty}</td>
                        <td><strong>${item.remaining_qty}</strong></td>
                        <td>
                            <input type="number" class="form-control form-control-sm received-qty" 
                                   name="items[${index}][received_qty]" 
                                   min="0" max="${item.remaining_qty}" 
                                   value="${defaultReceived}" 
                                   data-ordered="${item.ordered_qty}"
                                   data-received="${item.received_qty}"
                                   required>
                            <input type="hidden" name="items[${index}][purchase_order_item_id]" value="${item.id}">
                            <input type="hidden" name="items[${index}][gas_type_id]" value="${item.gas_type_id}">
                            <input type="hidden" name="items[${index}][ordered_qty]" value="${item.ordered_qty}">
                        </td>
                        <td>
                            <input type="number" class="form-control form-control-sm damaged-qty" 
                                   name="items[${index}][damaged_qty]" 
                                   min="0" value="0">
                        </td>
                        <td>
                            <input type="number" class="form-control form-control-sm rejected-qty" 
                                   name="items[${index}][rejected_qty]" 
                                   min="0" value="0">
                        </td>
                        <td>
                            <input type="number" class="form-control form-control-sm short-qty" 
                                   readonly value="0">
                        </td>
                    </tr>`;
                });

                html += '</tbody></table>';
                itemsContainer.innerHTML = html;

                // Calculate short quantity dynamically
                itemsContainer.querySelectorAll('.received-qty, .damaged-qty, .rejected-qty').forEach(input => {
                    input.addEventListener('input', function() {
                        const row = this.closest('tr');
                        const receivedInput = row.querySelector('.received-qty');
                        const damagedInput = row.querySelector('.damaged-qty');
                        const rejectedInput = row.querySelector('.rejected-qty');
                        const shortInput = row.querySelector('.short-qty');
                        
                        const ordered = parseFloat(receivedInput.dataset.ordered);
                        const received = parseFloat(receivedInput.value) || 0;
                        const damaged = parseFloat(damagedInput.value) || 0;
                        const rejected = parseFloat(rejectedInput.value) || 0;
                        
                        const short = Math.max(0, ordered - received);
                        shortInput.value = short;
                        
                        // Validate: received + damaged + rejected shouldn't exceed ordered
                        if (received + damaged + rejected > ordered) {
                            this.setCustomValidity('Total (received + damaged + rejected) cannot exceed ordered quantity');
                        } else {
                            this.setCustomValidity('');
                        }
                    });
                });
            })
            .catch(error => {
                console.error('Error:', error);
                itemsContainer.innerHTML = '<p class="text-danger">Error loading PO items. Please try again.</p>';
            });
    });
});
</script>
@endsection
