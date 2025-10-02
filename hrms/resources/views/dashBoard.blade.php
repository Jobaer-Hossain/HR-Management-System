@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Dashboard</h1>
    <div class="alert alert-success">
        Welcome,
    </div>

    <div class="row">
        <div class="col-md-4">
            <a href="{{ route('employees.index') }}" class="btn btn-primary w-100">Manage Employees</a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('leaves.index') }}" class="btn btn-warning w-100">Manage Leaves</a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('payrolls.index') }}" class="btn btn-success w-100">Manage Payrolls</a>
        </div>
    </div>
</div>
@endsection
