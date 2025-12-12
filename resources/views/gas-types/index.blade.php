@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Gas Types</h4>
    <a href="{{ route('gas-types.create') }}" class="btn btn-primary btn-sm">Add</a>
</div>

<table class="table table-bordered table-sm">
    <thead class="table-light">
        <tr>
            <th>Name</th>
            <th>Weight (kg)</th>
            <th>Default Price</th>
        </tr>
    </thead>
    <tbody>
        @foreach($gasTypes as $g)
        <tr>
            <td>{{ $g->name }}</td>
            <td>{{ $g->weight_kg }}</td>
            <td>{{ number_format($g->default_price, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection

