@extends('layouts.app')

@section('title', 'Edit Staff')

@section('content')
    <div class="mb-4">
        <a href="{{ route('staff.index') }}" class="text-decoration-none small text-muted">
            <i class="bi bi-arrow-left"></i> Back to staff
        </a>
        <h1 class="h3 fw-bold mb-1 mt-2">Edit Staff Account</h1>
        <p class="text-muted mb-0">Update {{ $staffMember->name }}'s login details.</p>
    </div>

    <div class="card app-card">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('staff.update', $staffMember) }}" novalidate>
                @csrf
                @method('PUT')
                @include('staff._form', ['staffMember' => $staffMember])
            </form>
        </div>
    </div>
@endsection
