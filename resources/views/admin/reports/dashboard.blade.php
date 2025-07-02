@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard de Reportes</h1>
        <div class="btn-group" role="group">
            <a href="{{ route('reports.sales') }}" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-chart-line me-2"></i>Reporte de Ventas
            </a>
            <a href="{{ route('reports.inventory') }}" class="btn btn-sm btn-outline-info">
                <i class="fas fa-boxes me-2"></i>Reporte de Inventario
            </a>
            <a href="{{ route('reports.export-sales') }}" class="btn btn-sm btn-outline-success">
                <i class="fas fa-download me-2"></i>Exportar
            </a>
        </div>
    </div>

    <!-- Estadísticas Generales -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row g-0 align-items-center">
                        <div class="col me-2">
                            <div class="text-xs fw-bold text-primary text-uppercase mb-1">
                                Total Pedidos</div>
                            <div class="h5 mb-0 fw-bold text-gray-800">{{ $totalOrders }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row g-0 align-items-center">
                        <div class="col me-2">
                            <div class="text-xs fw-bold text-success text-uppercase mb-1">
                                Ingresos Totales</div>
                            <div class="h5 mb-0 fw-bold text-gray-800">${{ number_format($totalRevenue, 2) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row g-0 align-items-center">
                        <div class="col me-2">
                            <div class="text-xs fw-bold text-info text-uppercase mb-1">
                                Usuarios Registrados</div>
                            <div class="h5 mb-0 fw-bold text-gray-800">{{ $totalUsers }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row g-0 align-items-center">
                        <div class="col me-2">
                            <div class="text-xs fw-bold text-warning text-uppercase mb-1">
                                Productos</div>
                            <div class="h5 mb-0 fw-bold text-gray-800">{{ $totalProducts }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-box fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ventas del Mes -->
    <div class="row">
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 fw-bold text-primary">Ventas del Mes Actual</h6>
                    <i class="fas fa-calendar-alt text-gray-400"></i>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-shopping-bag fa-2x text-primary"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-0">Pedidos</h6>
                                    <h4 class="mb-0 text-primary">{{ $currentMonthOrders }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-dollar-sign fa-2x text-success"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-0">Ingresos</h6>
                                    <h4 class="mb-0 text-success">${{ number_format($currentMonthRevenue, 2) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 fw-bold text-primary">Productos con Bajo Stock</h6>
                    <i class="fas fa-exclamation-triangle text-warning"></i>
                </div>
                <div class="card-body">
                    @if($lowStockProducts->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($lowStockProducts as $product)
                            <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0">
                                <div>
                                    <h6 class="mb-1">{{ $product->name }}</h6>
                                    <small class="text-muted">Categoría: {{ $product->category->name ?? 'Sin categoría' }}</small>
                                </div>
                                <span class="badge bg-danger rounded-pill">Stock: {{ $product->stock }}</span>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-3">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <p class="text-success mb-0">Todos los productos tienen stock suficiente</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Productos Más Vendidos -->
    <div class="row">
        <div class="col-xl-12 col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 fw-bold text-primary">Productos Más Vendidos</h6>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" 
                                data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-filter me-1"></i>Filtrar
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#">Este mes</a></li>
                            <li><a class="dropdown-item" href="#">Este año</a></li>
                            <li><a class="dropdown-item" href="#">Todos los tiempos</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Producto</th>
                                    <th>Categoría</th>
                                    <th class="text-center">Unidades Vendidas</th>
                                    <th class="text-center">Ingresos Generados</th>
                                    <th class="text-center">Stock Actual</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topProducts as $index => $product)
                                <tr>
                                    <td>
                                        <span class="badge bg-primary">{{ $index + 1 }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="{{ asset($product->image) }}" 
                                                 alt="{{ $product->name }}" 
                                                 class="rounded me-3" 
                                                 style="width: 40px; height: 40px; object-fit: cover;">
                                            <div>
                                                <h6 class="mb-0">{{ $product->name }}</h6>
                                                <small class="text-muted">{{ Str::limit($product->description, 50) }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $product->category->name ?? 'Sin categoría' }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="fw-bold text-primary">{{ $product->total_sold }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="fw-bold text-success">${{ number_format($product->total_sold * $product->price, 2) }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-{{ $product->stock > 10 ? 'success' : ($product->stock > 0 ? 'warning' : 'danger') }}">
                                            {{ $product->stock }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico de Ventas -->
    <div class="row">
        <div class="col-xl-12 col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 fw-bold text-primary">Ventas de los Últimos 30 Días</h6>
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-primary active">30 días</button>
                        <button type="button" class="btn btn-outline-primary">7 días</button>
                        <button type="button" class="btn btn-outline-primary">Este mes</button>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="salesChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('salesChart').getContext('2d');
    const salesData = @json($dailySales);

    const chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: salesData.map(item => item.date),
            datasets: [{
                label: 'Ventas Diarias ($)',
                data: salesData.map(item => item.total),
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                tension: 0.4,
                fill: true,
                pointBackgroundColor: 'rgb(75, 192, 192)',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: function(context) {
                            return 'Ventas: $' + context.parsed.y.toFixed(2);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toFixed(2);
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            },
            interaction: {
                mode: 'nearest',
                axis: 'x',
                intersect: false
            }
        }
    });
});
</script>
@endsection 
