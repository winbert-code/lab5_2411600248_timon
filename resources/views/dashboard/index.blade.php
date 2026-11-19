@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Dashboard</h1>
            <p class="text-muted mb-0">Real-time overview of your computer parts inventory.</p>
        </div>
        <a href="{{ route('products.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Add Product
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="stat-label mb-1">Active products</p>
                            <h2 class="stat-value mb-0">{{ number_format($stats->total_products) }}</h2>
                        </div>
                        <span class="stat-icon icon-primary"><i class="bi bi-box-seam"></i></span>
                    </div>
                    <a href="{{ route('products.index') }}" class="stat-link">View inventory <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="stat-label mb-1">Low stock items</p>
                            <h2 class="stat-value mb-0">{{ number_format($stats->low_stock) }}</h2>
                        </div>
                        <span class="stat-icon icon-warning"><i class="bi bi-exclamation-triangle"></i></span>
                    </div>
                    <span class="stat-link text-warning">At or below reorder level</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="stat-label mb-1">Out of stock</p>
                            <h2 class="stat-value mb-0">{{ number_format($stats->out_of_stock) }}</h2>
                        </div>
                        <span class="stat-icon icon-danger"><i class="bi bi-x-octagon"></i></span>
                    </div>
                    <span class="stat-link text-danger">Needs restocking</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="stat-label mb-1">Inventory value</p>
                            <h2 class="stat-value mb-0">&#8369;{{ number_format((float) $stats->inventory_value, 2) }}</h2>
                        </div>
                        <span class="stat-icon icon-success"><i class="bi bi-graph-up"></i></span>
                    </div>
                    <span class="stat-link text-success">Based on current stock</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card app-card h-100">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <h2 class="h5 fw-bold mb-0">Products per category</h2>
                    <p class="text-muted small mb-0">Distribution of stocked computer parts.</p>
                </div>
                <div class="card-body px-4 pb-4">
                    <canvas id="categoryChart" height="220" aria-label="Products per category chart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card app-card h-100">
                <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between">
                    <div>
                        <h2 class="h5 fw-bold mb-0">Low stock alerts</h2>
                        <p class="text-muted small mb-0">Reorder these items soon.</p>
                    </div>
                    <a href="{{ route('products.index') }}" class="small text-decoration-none">See all</a>
                </div>
                <div class="list-group list-group-flush px-4">
                    @forelse ($lowStockProducts as $product)
                        <a href="{{ route('products.show', $product) }}"
                           class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-0">
                            <span>
                                <span class="fw-semibold d-block">{{ $product->name }}</span>
                                <span class="small text-muted">{{ $product->sku }}</span>
                            </span>
                            <x-stock-badge :status="$product->availabilityStatus()" />
                        </a>
                    @empty
                        <p class="text-muted px-0 py-3 mb-0">No low stock items. Everything is well stocked.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card app-card">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <h2 class="h5 fw-bold mb-0">Recently added products</h2>
                    <p class="text-muted small mb-0">The latest items saved to the database.</p>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>SKU</th>
                                <th>Category</th>
                                <th class="text-end">Stock</th>
                                <th>Status</th>
                                <th>Added</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentProducts as $product)
                                <tr>
                                    <td>
                                        <a href="{{ route('products.show', $product) }}" class="text-decoration-none fw-semibold">
                                            {{ $product->name }}
                                        </a>
                                    </td>
                                    <td><code>{{ $product->sku }}</code></td>
                                    <td>{{ $product->category?->name ?? '—' }}</td>
                                    <td class="text-end">{{ number_format($product->quantity) }}</td>
                                    <td><x-stock-badge :status="$product->availabilityStatus()" /></td>
                                    <td>{{ $product->created_at?->diffForHumans() }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No products have been added yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card app-card">
                <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between">
                    <div>
                        <h2 class="h5 fw-bold mb-0">Recent activity</h2>
                        <p class="text-muted small mb-0">The latest actions recorded in the system.</p>
                    </div>
                    <a href="{{ route('activity-logs.index') }}" class="small text-decoration-none">See all</a>
                </div>
                <div class="list-group list-group-flush px-4">
                    @forelse ($recentActivity as $log)
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span>
                                <span class="fw-semibold d-block">{{ $log->description }}</span>
                                <span class="small text-muted">
                                    {{ $log->user?->name ?? 'System' }} &middot; {{ $log->created_at?->diffForHumans() }}
                                </span>
                            </span>
                            <span class="badge {{ $log->badgeClass() }}">{{ $log->action }}</span>
                        </div>
                    @empty
                        <p class="text-muted px-0 py-3 mb-0">No activity recorded yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script>
        const categoryData = {{ Js::from($chart) }};

        new Chart(document.getElementById('categoryChart'), {
            type: 'bar',
            data: {
                labels: categoryData.labels,
                datasets: [{
                    label: 'Products',
                    data: categoryData.values,
                    backgroundColor: '#1DBF04',
                    hoverBackgroundColor: '#A7F205',
                    borderRadius: 6,
                    borderSkipped: false,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#e0e0e0' } },
                    x: { grid: { display: false } },
                },
            },
        });
    </script>
@endpush
