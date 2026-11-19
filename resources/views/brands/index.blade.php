@extends('layouts.app')

@section('title', 'Brands')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Brands</h1>
            <p class="text-muted mb-0">Manage the manufacturers of your computer parts.</p>
        </div>
        <a href="{{ route('brands.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Add Brand
        </a>
    </div>

    <div class="card app-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Slug</th>
                        <th class="text-end">Products</th>
                        <th>Description</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($brands as $brand)
                        <tr>
                            <td class="fw-semibold">{{ $brand->name }}</td>
                            <td><code>{{ $brand->slug }}</code></td>
                            <td class="text-end">{{ $brand->products_count }}</td>
                            <td>{{ $brand->description ?? '—' }}</td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('brands.edit', $brand) }}" class="btn btn-outline-primary"
                                       title="Edit" aria-label="Edit brand"><i class="bi bi-pencil"></i></a>
                                    <form method="POST" action="{{ route('brands.destroy', $brand) }}"
                                          onsubmit="return confirm('Remove {{ addslashes($brand->name) }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger"
                                                title="Remove" aria-label="Remove brand"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                No brands found. <a href="{{ route('brands.create') }}">Add the first one</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body border-top">
            {{ $brands->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection
