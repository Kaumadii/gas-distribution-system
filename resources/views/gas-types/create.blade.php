@extends('layouts.app')

@section('content')
<h4 class="mb-3">Add Gas Type</h4>
<form method="POST" action="{{ route('gas-types.store') }}" class="card card-body">
    @csrf
    <div class="mb-3">
        <label class="form-label">Name</label>
        <input type="text" name="name" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Weight (kg)</label>
        <input type="number" step="0.1" name="weight_kg" class="form-control">
    </div>
    <div class="mb-3">
        <label class="form-label">Default Price</label>
        <input type="number" step="0.01" name="default_price" class="form-control" required>
    </div>
    <button class="btn btn-primary">Save</button>
</form>
@endsection

