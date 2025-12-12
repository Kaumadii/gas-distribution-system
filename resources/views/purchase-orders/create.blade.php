@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Create Purchase Order</h4>
    <a href="{{ route('purchase-orders.index') }}" class="btn btn-secondary btn-sm">Back</a>
</div>
<form method="POST" action="{{ route('purchase-orders.store') }}" class="card card-body" id="poForm">
    @csrf
    <div class="row mb-3">
        <div class="col-md-4">
            <label class="form-label">Supplier <span class="text-danger">*</span></label>
            <select name="supplier_id" id="supplier_id" class="form-select" required>
                <option value="">Select Supplier</option>
                @foreach($suppliers as $s)
                    <option value="{{ $s->id }}" data-rates="{{ $s->rates->mapWithKeys(fn($r) => [$r->gas_type_id => $r->rate])->toJson() }}">
                        {{ $s->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Expected Date</label>
            <input type="date" name="expected_date" class="form-control" value="{{ date('Y-m-d') }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">PO Number</label>
            <input type="text" class="form-control" value="Auto-generated" readonly style="background-color: #e9ecef;">
        </div>
    </div>
    <div class="mb-3">
        <label class="form-label">Notes</label>
        <textarea name="notes" class="form-control" rows="2" placeholder="Optional notes..."></textarea>
    </div>
    
    <h6 class="mb-3">Items - Select Gas Types and Quantities</h6>
    <div id="items-container">
        @for($i=0; $i<3; $i++)
            <div class="row mb-2 item-row">
                <div class="col-md-5">
                    <label class="form-label small">Gas Type</label>
                    <select name="items[{{ $i }}][gas_type_id]" class="form-select gas-type-select" @if($i==0) required @endif>
                        <option value="">Select Gas Type</option>
                        @foreach($gasTypes as $g)
                            <option value="{{ $g->id }}" data-name="{{ $g->name }}">{{ $g->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Quantity</label>
                    <input type="number" name="items[{{ $i }}][quantity]" class="form-control quantity-input" placeholder="Qty" min="1" @if($i==0) required @endif>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Unit Price</label>
                    <input type="text" class="form-control unit-price-display" readonly style="background-color: #e9ecef;" placeholder="Auto">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Total</label>
                    <input type="text" class="form-control line-total-display" readonly style="background-color: #e9ecef;" placeholder="0.00">
                </div>
            </div>
        @endfor
    </div>
    
    <div class="row mt-3">
        <div class="col-md-8"></div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <strong>Grand Total:</strong>
                        <strong id="grand-total">0.00</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="mt-3">
        <button type="submit" class="btn btn-primary">Create Purchase Order</button>
        <a href="{{ route('purchase-orders.index') }}" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const supplierSelect = document.getElementById('supplier_id');
    const itemRows = document.querySelectorAll('.item-row');
    let supplierRates = {};

    supplierSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            supplierRates = JSON.parse(selectedOption.getAttribute('data-rates') || '{}');
            updateAllPrices();
        } else {
            supplierRates = {};
            clearAllPrices();
        }
    });

    itemRows.forEach(row => {
        const gasTypeSelect = row.querySelector('.gas-type-select');
        const quantityInput = row.querySelector('.quantity-input');
        const unitPriceDisplay = row.querySelector('.unit-price-display');
        const lineTotalDisplay = row.querySelector('.line-total-display');

        function updateLineTotal() {
            const gasTypeId = gasTypeSelect.value;
            const quantity = parseInt(quantityInput.value) || 0;
            
            if (gasTypeId && supplierRates[gasTypeId]) {
                const unitPrice = parseFloat(supplierRates[gasTypeId]);
                unitPriceDisplay.value = unitPrice.toFixed(2);
                const lineTotal = unitPrice * quantity;
                lineTotalDisplay.value = lineTotal.toFixed(2);
            } else {
                unitPriceDisplay.value = '';
                lineTotalDisplay.value = '';
            }
            updateGrandTotal();
        }

        gasTypeSelect.addEventListener('change', updateLineTotal);
        quantityInput.addEventListener('input', updateLineTotal);
    });

    function updateAllPrices() {
        itemRows.forEach(row => {
            const gasTypeSelect = row.querySelector('.gas-type-select');
            const quantityInput = row.querySelector('.quantity-input');
            const unitPriceDisplay = row.querySelector('.unit-price-display');
            const lineTotalDisplay = row.querySelector('.line-total-display');

            const gasTypeId = gasTypeSelect.value;
            const quantity = parseInt(quantityInput.value) || 0;

            if (gasTypeId && supplierRates[gasTypeId]) {
                const unitPrice = parseFloat(supplierRates[gasTypeId]);
                unitPriceDisplay.value = unitPrice.toFixed(2);
                const lineTotal = unitPrice * quantity;
                lineTotalDisplay.value = lineTotal.toFixed(2);
            }
        });
        updateGrandTotal();
    }

    function clearAllPrices() {
        itemRows.forEach(row => {
            row.querySelector('.unit-price-display').value = '';
            row.querySelector('.line-total-display').value = '';
        });
        updateGrandTotal();
    }

    function updateGrandTotal() {
        let total = 0;
        itemRows.forEach(row => {
            const lineTotal = parseFloat(row.querySelector('.line-total-display').value) || 0;
            total += lineTotal;
        });
        document.getElementById('grand-total').textContent = total.toFixed(2);
    }
});
</script>
@endsection

