@extends('layouts.app')

@section('title', 'Staff')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Staff Accounts</h1>
            <p class="text-muted mb-0">Add and manage the users who can access the inventory system.</p>
        </div>
        <a href="{{ route('staff.create') }}" class="btn btn-primary">
            <i class="bi bi-person-plus"></i> Add Staff
        </a>
    </div>

    <div class="card app-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Date added</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($staff as $member)
                        <tr>
                            <td>
                                <i class="bi bi-person-circle text-success"></i>
                                <span class="fw-semibold">{{ $member->name }}</span>
                                @if ($member->id === auth()->id())
                                    <span class="badge badge-inactive ms-1">You</span>
                                @endif
                            </td>
                            <td>{{ $member->email }}</td>
                            <td>{{ $member->created_at?->format('M d, Y') ?? '—' }}</td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('staff.edit', $member) }}" class="btn btn-outline-primary"
                                       title="Edit" aria-label="Edit staff member">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @if ($member->id !== auth()->id())
                                        <form method="POST" action="{{ route('staff.destroy', $member) }}"
                                              onsubmit="return confirm('Remove {{ addslashes($member->name) }} from staff?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger"
                                                    title="Remove" aria-label="Remove staff member">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                No staff accounts found. <a href="{{ route('staff.create') }}">Add the first one</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body border-top">
            {{ $staff->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection
