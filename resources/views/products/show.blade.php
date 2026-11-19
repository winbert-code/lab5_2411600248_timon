@extends('layouts.app')

@section('title', $product->name)

@section('content')
    <div class="mb-4">
        <a href="{{ route('products.index') }}" class="text-decoration-none small text-muted">
            <i class="bi bi-arrow-left"></i> Back to inventory
        </a>
    </div>

    @if ($product->isInactive())
        <div class="alert alert-warning border-0 small">
            <i class="bi bi-archive"></i>
            This product is inactive. It is hidden from the active inventory, but its record and stock history are preserved.
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card app-card h-100">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div>
                            <h1 class="h4 fw-bold mb-1">{{ $product->name }}</h1>
                            <code>{{ $product->sku }}</code>
                        </div>
                        <x-stock-badge :status="$product->availabilityStatus()" />
                    </div>
                </div>
                <div class="card-body px-4">
                    <p class="text-muted">{{ $product->description ?: 'No description provided.' }}</p>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Brand</span><strong>{{ $product->brand?->name ?? '—' }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Category</span>
                            <strong>{{ $product->category?->name ?? '—' }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Supplier</span><strong>{{ $product->supplier ?? '—' }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Quantity</span><strong>{{ number_format($product->quantity) }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Reorder level</span><strong>{{ number_format($product->reorder_level) }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Unit price</span>
                            <strong>&#8369;{{ number_format((float) $product->unit_price, 2) }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Stock value</span>
                            <strong>&#8369;{{ number_format($product->stockValue(), 2) }}</strong>
                        </li>
                    </ul>
                </div>
                <div class="card-footer bg-transparent border-0 px-4 pb-4 d-flex gap-2">
                    <a href="{{ route('products.edit', $product) }}" class="btn btn-primary">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    @if ($product->isActive())
                        <form method="POST" action="{{ route('products.destroy', $product) }}"
                              onsubmit="return confirm('Mark {{ addslashes($product->name) }} as inactive? The product and its inventory records will be kept.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger">
                                <i class="bi bi-x-circle"></i> Mark inactive
                            </button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('products.restore', $product) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-outline-success">
                                <i class="bi bi-arrow-counterclockwise"></i> Restore
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card app-card">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <h2 class="h5 fw-bold mb-0">Stock movements</h2>
                    <p class="text-muted small mb-0">Audit trail recorded when stock is added or adjusted.</p>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th class="text-end">Quantity</th>
                                <th>Reference</th>
                                <th>User</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($product->transactions as $transaction)
                                <tr>
                                    <td>{{ $transaction->created_at->format('M d, Y h:i A') }}</td>
                                    <td>
                                        <span class="stock-badge {{ $transaction->type === 'in' ? 'badge-in' : 'badge-out' }}">
                                            <i class="bi {{ $transaction->type === 'in' ? 'bi-arrow-down-left' : 'bi-arrow-up-right' }}"></i>
                                            {{ $transaction->type === 'in' ? 'Stock in' : 'Stock out' }}
                                        </span>
                                    </td>
                                    <td class="text-end">{{ number_format($transaction->quantity) }}</td>
                                    <td>{{ $transaction->reference ?? '—' }}</td>
                                    <td>{{ $transaction->user?->name ?? 'System' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No stock movements recorded.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
