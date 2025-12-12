@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Route: {{ $route->name }}</h4>
            <a href="{{ route('routes.index') }}" class="btn btn-secondary btn-sm">Back to Routes</a>
        </div>

        <!-- Route Information Card -->
        <div class="card card-body mb-4">
            <div class="row">
                <div class="col-md-3">
                    <strong>Driver:</strong> {{ $route->driver ?? 'Not assigned' }}
                </div>
                <div class="col-md-3">
                    <strong>Assistant:</strong> {{ $route->assistant ?? 'Not assigned' }}
                </div>
                <div class="col-md-3">
                    <strong>Planned Start:</strong> 
                    @if($route->planned_start)
                        {{ \Carbon\Carbon::parse($route->planned_start)->format('Y-m-d H:i') }}
                    @else
                        <span class="text-muted">Not set</span>
                    @endif
                </div>
                <div class="col-md-3">
                    <strong>Actual Start:</strong>
                    @if($route->actual_start)
                        {{ \Carbon\Carbon::parse($route->actual_start)->format('Y-m-d H:i') }}
                        @php
                            $diff = $route->getTimeDifference();
                        @endphp
                        @if($diff !== null)
                            @if($diff > 0)
                                <span class="badge text-bg-danger ms-2">+{{ $diff }} min late</span>
                            @elseif($diff < 0)
                                <span class="badge text-bg-success ms-2">{{ abs($diff) }} min early</span>
                            @else
                                <span class="badge text-bg-success ms-2">On time</span>
                            @endif
                        @endif
                    @else
                        <form method="POST" action="{{ route('routes.update-actual', $route) }}" class="d-inline">
                            @csrf
                            <input type="datetime-local" name="actual_start" class="form-control form-control-sm d-inline-block" style="width: auto;" required>
                            <button type="submit" class="btn btn-sm btn-outline-primary">Set</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Customer Stops Section -->
            <div class="col-md-6">
                <h5 class="mb-3">Customer Stops</h5>
                
                <!-- Add Stop Form -->
                <div class="card card-body mb-3">
                    <h6>Add Customer Stop</h6>
                    <form method="POST" action="{{ route('routes.stops.store', $route) }}">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label">Customer</label>
                            <select class="form-select" name="customer_id">
                                <option value="">Select Customer</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Planned Time</label>
                            <input type="datetime-local" class="form-control" name="planned_time">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" name="notes" rows="2" placeholder="Optional notes"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">Add Stop</button>
                    </form>
                </div>

                <!-- Stops List -->
                <table class="table table-bordered table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Customer</th>
                            <th>Planned Time</th>
                            <th>Actual Time</th>
                            <th>Time Diff</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($route->stops as $stop)
                        <tr>
                            <td>
                                <strong>{{ optional($stop->customer)->name ?? 'Stop #' . $stop->id }}</strong>
                                @if($stop->notes)
                                    <br><small class="text-muted">{{ $stop->notes }}</small>
                                @endif
                            </td>
                            <td>
                                @if($stop->planned_time)
                                    {{ \Carbon\Carbon::parse($stop->planned_time)->format('Y-m-d H:i') }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($stop->actual_time)
                                    {{ \Carbon\Carbon::parse($stop->actual_time)->format('Y-m-d H:i') }}
                                @else
                                    <form method="POST" action="{{ route('route-stops.update-time', $stop) }}" class="d-inline">
                                        @csrf
                                        <input type="datetime-local" name="actual_time" class="form-control form-control-sm d-inline-block" style="width: auto;" required>
                                        <button type="submit" class="btn btn-sm btn-outline-success">Record</button>
                                    </form>
                                @endif
                            </td>
                            <td>
                                @if($stop->planned_time && $stop->actual_time)
                                    @php
                                        $stopDiff = \Carbon\Carbon::parse($stop->actual_time)->diffInMinutes(\Carbon\Carbon::parse($stop->planned_time));
                                    @endphp
                                    @if($stopDiff > 0)
                                        <span class="badge text-bg-danger">+{{ $stopDiff }} min</span>
                                    @elseif($stopDiff < 0)
                                        <span class="badge text-bg-success">{{ abs($stopDiff) }} min</span>
                                    @else
                                        <span class="badge text-bg-success">On time</span>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted">Stop #{{ $stop->id }}</small>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">No stops added yet</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pending Deliveries Section -->
            <div class="col-md-6">
                <h5 class="mb-3">Pending Deliveries</h5>
                <table class="table table-bordered table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Status</th>
                            <th>Items</th>
                            <th>Urgent</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $pendingOrders = $route->orders()->whereIn('status', ['pending', 'loaded'])->with('customer', 'items.gasType')->get();
                        @endphp
                        @forelse($pendingOrders as $order)
                        <tr>
                            <td><strong>{{ $order->order_number }}</strong></td>
                            <td>{{ $order->customer->name }}</td>
                            <td>
                                <span class="badge text-bg-warning">{{ ucfirst($order->status) }}</span>
                            </td>
                            <td>
                                @foreach($order->items as $item)
                                    <span class="badge text-bg-light">{{ $item->gasType->name }} x {{ $item->quantity }}</span>
                                @endforeach
                            </td>
                            <td>
                                @if($order->urgent)
                                    <span class="badge text-bg-danger">Urgent</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">No pending deliveries</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                <!-- All Orders Summary -->
                <div class="card card-body mt-3">
                    <h6>Orders Summary</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Total Orders:</strong> {{ $route->orders->count() }}
                        </div>
                        <div class="col-md-6">
                            <strong>Pending:</strong> 
                            <span class="badge text-bg-warning">{{ $route->pendingDeliveriesCount() }}</span>
                        </div>
                        <div class="col-md-6 mt-2">
                            <strong>Completed:</strong> 
                            <span class="badge text-bg-success">{{ $route->orders()->whereIn('status', ['delivered', 'completed'])->count() }}</span>
                        </div>
                        <div class="col-md-6 mt-2">
                            <strong>Total Stops:</strong> 
                            <span class="badge text-bg-info">{{ $route->stops->count() }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

