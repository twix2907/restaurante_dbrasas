@extends('layouts.admin')

@section('content')
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Gestión de Sliders</h1>
        <a href="{{ route('sliders.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50 me-2"></i>Crear Slider
        </a>
    </div>

    <!-- Content Row -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 fw-bold text-primary">
                        <i class="fas fa-images me-2"></i>Lista de Sliders
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" class="text-center" style="width: 60px;">#</th>
                                    <th scope="col" class="text-center" style="width: 150px;">Imagen</th>
                                    <th scope="col">Título</th>
                                    <th scope="col">Descripción</th>
                                    <th scope="col" class="text-center" style="width: 100px;">Estado</th>
                                    <th scope="col" class="text-center" style="width: 150px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($sliders as $slider)
                                <tr>
                                    <td class="text-center">
                                        <span class="badge bg-secondary">{{ $slider->id }}</span>
                                    </td>
                                    <td class="text-center">
                                        <img src="{{ asset($slider->image) }}" 
                                             alt="{{ $slider->title }}" 
                                             class="img-thumbnail" 
                                             style="width: 120px; height: 80px; object-fit: cover;">
                                    </td>
                                    <td>
                                        <h6 class="mb-1">{{ $slider->title }}</h6>
                                        @if($slider->subtitle)
                                            <small class="text-muted">{{ $slider->subtitle }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ Str::limit($slider->description, 100) }}</small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-{{ $slider->is_active ? 'success' : 'secondary' }}">
                                            {{ $slider->is_active ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a class="btn btn-outline-primary" 
                                               href="{{ route('sliders.edit', $slider->id) }}"
                                               data-bs-toggle="tooltip" 
                                               title="Editar slider">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-outline-danger confirm-delete"
                                                    data-slider-id="{{ $slider->id }}"
                                                    data-slider-title="{{ $slider->title }}"
                                                    data-bs-toggle="tooltip" 
                                                    title="Eliminar slider">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-images fa-3x mb-3"></i>
                                            <h5>No hay sliders registrados</h5>
                                            <p>Comienza creando tu primer slider</p>
                                            <a href="{{ route('sliders.create') }}" class="btn btn-primary">
                                                <i class="fas fa-plus me-2"></i>Crear Slider
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($sliders->hasPages())
                <div class="card-footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted">
                            Mostrando {{ $sliders->firstItem() }} a {{ $sliders->lastItem() }} de {{ $sliders->total() }} sliders
                        </div>
                        {{ $sliders->links() }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirmar Eliminación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro de que quieres eliminar el slider "<strong id="deleteSliderTitle"></strong>"?</p>
                    <p class="text-danger"><small>Esta acción no se puede deshacer.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form id="deleteForm" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Eliminar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Delete confirmation
            document.querySelectorAll('.confirm-delete').forEach(btn => {
                btn.addEventListener('click', function() {
                    const sliderId = this.getAttribute('data-slider-id');
                    const sliderTitle = this.getAttribute('data-slider-title');
                    
                    document.getElementById('deleteSliderTitle').textContent = sliderTitle;
                    document.getElementById('deleteForm').action = `/admin/sliders/${sliderId}`;
                    
                    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
                    deleteModal.show();
                });
            });

            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
@endsection
