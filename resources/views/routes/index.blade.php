@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <h4 class="mb-3">Create New Route</h4>
        <form method="POST" action="{{ route('routes.store') }}" class="card card-body mb-4">
            @csrf
            <div class="mb-2">
                <label class="form-label">Route Name <span class="text-danger">*</span></label>
                <input class="form-control" name="name" placeholder="Route Name" required>
            </div>
            <div class="mb-2">
                <label class="form-label">Driver</label>
                <input class="form-control" name="driver" placeholder="Driver Name">
            </div>
            <div class="mb-2">
                <label class="form-label">Assistant</label>
                <input class="form-control" name="assistant" placeholder="Assistant Name">
            </div>
            <div class="mb-3">
                <label class="form-label">Planned Start Time</label>
                <input class="form-control" type="datetime-local" name="planned_start">
            </div>
            <button class="btn btn-primary">Create Route</button>
        </form>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <h4 class="mb-3">Delivery Routes</h4>
        <table class="table table-bordered table-sm">
            <thead class="table-light">
                <tr>
                    <th>Route Name</th>
                    <th>Driver</th>
                    <th>Assistant</th>
                    <th>Planned Start</th>
                    <th>Actual Start</th>
                    <th>Time Difference</th>
                    <th>Pending Deliveries</th>
                    <th>Total Stops</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($routes as $r)
                <tr>
                    <td><strong>{{ $r->name }}</strong></td>
                    <td>{{ $r->driver ?? '-' }}</td>
                    <td>{{ $r->assistant ?? '-' }}</td>
                    <td>
                        @if($r->planned_start)
                            <small>{{ \Carbon\Carbon::parse($r->planned_start)->format('Y-m-d H:i') }}</small>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        @if($r->actual_start)
                            <small>{{ \Carbon\Carbon::parse($r->actual_start)->format('Y-m-d H:i') }}</small>
                        @else
                            <form method="POST" action="{{ route('routes.update-actual', $r) }}" class="d-inline">
                                @csrf
                                <input type="datetime-local" name="actual_start" class="form-control form-control-sm d-inline-block" style="width: auto;" required>
                                <button type="submit" class="btn btn-sm btn-outline-primary">Set</button>
                            </form>
                        @endif
                    </td>
                    <td>
                        @if($r->time_diff !== null)
                            @php
                                // Fix time calculation: positive = late, negative = early
                                $planned = \Carbon\Carbon::parse($r->planned_start);
                                $actual = \Carbon\Carbon::parse($r->actual_start);
                                $diff = $actual->diffInMinutes($planned, false);
                            @endphp
                            @if($diff > 0)
                                <span class="badge text-bg-danger">+{{ $diff }} min late</span>
                            @elseif($diff < 0)
                                <span class="badge text-bg-success">{{ abs($diff) }} min early</span>
                            @else
                                <span class="badge text-bg-success">On time</span>
                            @endif
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        @if($r->pending_count > 0)
                            <span class="badge text-bg-warning">{{ $r->pending_count }} pending</span>
                        @else
                            <span class="badge text-bg-success">0</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge text-bg-info">{{ $r->stops->count() }} stops</span>
                    </td>
                    <td>
                        <form method="POST" action="{{ route('routes.destroy', $r) }}" class="d-inline" 
                              onsubmit="return confirm('Are you sure you want to delete this route?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

