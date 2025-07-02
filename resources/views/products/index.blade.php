@extends("layouts.admin")

@section("content")
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Gesti�n de Productos</h1>
        <a href="{{ route("products.create") }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50 me-2"></i>Crear Producto
        </a>
    </div>

    <!-- Content Row -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 fw-bold text-primary">
                        <i class="fas fa-box me-2"></i>Lista de Productos
                    </h6>
                    <div class="d-flex gap-2">
                        <div class="input-group input-group-sm" style="width: 250px;">
                            <input type="text" class="form-control" id="searchInput" placeholder="Buscar productos...">
                            <button class="btn btn-outline-secondary" type="button">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" 
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-filter me-1"></i>Filtrar
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" data-filter="all">Todos</a></li>
                                <li><a class="dropdown-item" href="#" data-filter="in-stock">En Stock</a></li>
                                <li><a class="dropdown-item" href="#" data-filter="low-stock">Stock Bajo</a></li>
                                <li><a class="dropdown-item" href="#" data-filter="out-of-stock">Sin Stock</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="productsTable">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" class="text-center" style="width: 60px;">#</th>
                                    <th scope="col" class="text-center" style="width: 100px;">Imagen</th>
                                    <th scope="col">Producto</th>
                                    <th scope="col" class="text-center" style="width: 120px;">Precio</th>
                                    <th scope="col" class="text-center" style="width: 100px;">Stock</th>
                                    <th scope="col" class="text-center" style="width: 120px;">Categor�a</th>
                                    <th scope="col" class="text-center" style="width: 100px;">Estado</th>
                                    <th scope="col" class="text-center" style="width: 150px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($products as $product)
                                <tr data-product-id="{{ $product->id }}" 
                                    data-product-name="{{ strtolower($product->name) }}"
                                    data-product-category="{{ strtolower($product->category->name ?? "") }}"
                                    data-product-stock="{{ $product->stock }}">
                                    <td class="text-center">
                                        <span class="badge bg-secondary">{{ $product->id }}</span>
                                    </td>
                                    <td class="text-center">
                                        <img src="{{ asset($product->image) }}" 
                                             alt="{{ $product->name }}" 
                                             class="img-thumbnail" 
                                             style="width: 60px; height: 60px; object-fit: cover;"
                                             data-bs-toggle="modal" 
                                             data-bs-target="#imageModal"
                                             data-bs-image="{{ asset($product->image) }}"
                                             data-bs-title="{{ $product->name }}">
                                    </td>
                                    <td>
                                        <div>
                                            <h6 class="mb-1">{{ $product->name }}</h6>
                                            <small class="text-muted">{{ Str::limit($product->description, 80) }}</small>
                                            @if($product->label)
                                                <br><span class="badge bg-info">{{ $product->label }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="fw-bold text-primary">${{ number_format($product->price, 2) }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-{{ $product->stock > 10 ? "success" : ($product->stock > 0 ? "warning" : "danger") }}">
                                            {{ $product->stock }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary">{{ $product->category->name ?? "Sin categor�a" }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if($product->stock > 10)
                                            <span class="badge bg-success">Disponible</span>
                                        @elseif($product->stock > 0)
                                            <span class="badge bg-warning">Stock Bajo</span>
                                        @else
                                            <span class="badge bg-danger">Agotado</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a class="btn btn-outline-primary" 
                                               href="{{ route("products.edit", $product->id) }}"
                                               data-bs-toggle="tooltip" 
                                               title="Editar producto">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a class="btn btn-outline-info" 
                                               href="{{ route("products.display", $product->id) }}"
                                               target="_blank"
                                               data-bs-toggle="tooltip" 
                                               title="Ver en tienda">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-outline-danger confirm-delete"
                                                    data-product-id="{{ $product->id }}"
                                                    data-product-name="{{ $product->name }}"
                                                    data-bs-toggle="tooltip" 
                                                    title="Eliminar producto">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-box fa-3x mb-3"></i>
                                            <h5>No hay productos registrados</h5>
                                            <p>Comienza creando tu primer producto</p>
                                            <a href="{{ route("products.create") }}" class="btn btn-primary">
                                                <i class="fas fa-plus me-2"></i>Crear Producto
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($products->hasPages())
                <div class="card-footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted">
                            Mostrando {{ $products->firstItem() }} a {{ $products->lastItem() }} de {{ $products->total() }} productos
                        </div>
                        {{ $products->links() }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Image Modal -->
    <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imageModalLabel">Vista Previa de Imagen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalImage" src="" alt="" class="img-fluid">
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirmar Eliminaci�n</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>�Est�s seguro de que quieres eliminar el producto "<strong id="deleteProductName"></strong>"?</p>
                    <p class="text-danger"><small>Esta acci�n no se puede deshacer.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form id="deleteForm" method="POST" style="display: inline;">
                        @csrf
                        @method("DELETE")
                        <button type="submit" class="btn btn-danger">Eliminar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Search functionality
            const searchInput = document.getElementById("searchInput");
            const tableRows = document.querySelectorAll("#productsTable tbody tr");

            searchInput.addEventListener("input", function() {
                const searchTerm = this.value.toLowerCase();
                
                tableRows.forEach(row => {
                    const productName = row.getAttribute("data-product-name");
                    const productCategory = row.getAttribute("data-product-category");
                    
                    if (productName.includes(searchTerm) || productCategory.includes(searchTerm)) {
                        row.style.display = "";
                    } else {
                        row.style.display = "none";
                    }
                });
            });

            // Filter functionality
            document.querySelectorAll("[data-filter]").forEach(filterBtn => {
                filterBtn.addEventListener("click", function(e) {
                    e.preventDefault();
                    const filter = this.getAttribute("data-filter");
                    
                    tableRows.forEach(row => {
                        const stock = parseInt(row.getAttribute("data-product-stock"));
                        
                        switch(filter) {
                            case "in-stock":
                                row.style.display = stock > 10 ? "" : "none";
                                break;
                            case "low-stock":
                                row.style.display = stock > 0 && stock <= 10 ? "" : "none";
                                break;
                            case "out-of-stock":
                                row.style.display = stock === 0 ? "" : "none";
                                break;
                            default:
                                row.style.display = "";
                        }
                    });
                });
            });

            // Image modal
            const imageModal = document.getElementById("imageModal");
            imageModal.addEventListener("show.bs.modal", function(event) {
                const button = event.relatedTarget;
                const image = button.getAttribute("data-bs-image");
                const title = button.getAttribute("data-bs-title");
                
                document.getElementById("modalImage").src = image;
                document.getElementById("modalImage").alt = title;
                document.getElementById("imageModalLabel").textContent = title;
            });

            // Delete confirmation
            document.querySelectorAll(".confirm-delete").forEach(btn => {
                btn.addEventListener("click", function() {
                    const productId = this.getAttribute("data-product-id");
                    const productName = this.getAttribute("data-product-name");
                    
                    document.getElementById("deleteProductName").textContent = productName;
                    document.getElementById("deleteForm").action = `/admin/products/${productId}`;
                    
                    const deleteModal = new bootstrap.Modal(document.getElementById("deleteModal"));
                    deleteModal.show();
                });
            });

            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll("[data-bs-toggle=\"tooltip\"]"));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
@endsection
