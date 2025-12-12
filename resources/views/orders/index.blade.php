@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Orders</h4>
    <a href="{{ route('orders.create') }}" class="btn btn-primary btn-sm">New Order</a>
</div>
<table class="table table-bordered table-sm">
    <thead class="table-light">
        <tr>
            <th>Order #</th>
            <th>Customer</th>
            <th>Route</th>
            <th>Status</th>
            <th>Urgent</th>
            <th>Items</th>
            <th>Change Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($orders as $o)
        <tr>
            <td>{{ $o->order_number }}</td>
            <td>{{ $o->customer->name }}</td>
            <td>{{ optional($o->route)->name }}</td>
            <td><span class="badge text-bg-secondary">{{ ucfirst($o->status) }}</span></td>
            <td>@if($o->urgent)<span class="badge text-bg-danger">Urgent</span>@endif</td>
            <td>
                @foreach($o->items as $item)
                    <span class="badge text-bg-light">{{ $item->gasType->name }} x {{ $item->quantity }}</span>
                @endforeach
            </td>
            <td>
                <form method="POST" action="{{ route('orders.status', $o) }}" class="d-flex gap-1">
                    @csrf
                    <select name="status" class="form-select form-select-sm">
                        @foreach(['pending','loaded','delivered','completed'] as $status)
                            <option value="{{ $status }}" @selected($o->status === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                    <button class="btn btn-sm btn-outline-primary">Save</button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection

