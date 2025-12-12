@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Goods Received Notes (GRN)</h4>
            <a href="{{ route('grn.create') }}" class="btn btn-primary btn-sm">Create New GRN</a>
        </div>

        <table class="table table-bordered table-sm">
            <thead class="table-light">
                <tr>
                    <th>GRN Number</th>
                    <th>PO Number</th>
                    <th>Supplier</th>
                    <th>Received Date</th>
                    <th>Status</th>
                    <th>Items</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($grns as $grn)
                <tr>
                    <td><strong>{{ $grn->grn_number }}</strong></td>
                    <td>{{ $grn->purchaseOrder->po_number }}</td>
                    <td>{{ $grn->supplier->name }}</td>
                    <td>{{ $grn->received_at ? \Carbon\Carbon::parse($grn->received_at)->format('Y-m-d') : '-' }}</td>
                    <td>
                        @if($grn->status === 'approved')
                            <span class="badge text-bg-success">Approved</span>
                        @else
                            <span class="badge text-bg-warning">Pending Approval</span>
                        @endif
                    </td>
                    <td>
                        @foreach($grn->items as $item)
                            <div class="small">
                                <strong>{{ $item->gasType->name }}:</strong>
                                Ordered: {{ $item->ordered_qty }}, 
                                Received: {{ $item->received_qty }}
                                @if($item->damaged_qty > 0)
                                    <span class="text-danger">(Damaged: {{ $item->damaged_qty }})</span>
                                @endif
                                @if($item->rejected_qty > 0)
                                    <span class="text-danger">(Rejected: {{ $item->rejected_qty }})</span>
                                @endif
                                @if($item->short_qty > 0)
                                    <span class="text-warning">(Short: {{ $item->short_qty }})</span>
                                @endif
                            </div>
                        @endforeach
                    </td>
                    <td>
                        @if($grn->status === 'pending')
                            <form method="POST" action="{{ route('grn.approve', $grn) }}" class="d-inline" 
                                  onsubmit="return confirm('Approve this GRN? This will update stock and may close the PO if fully received.');">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success">Approve</button>
                            </form>
                        @else
                            <span class="text-muted small">Approved</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

