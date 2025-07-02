@extends('layouts.admin')

@section('content')
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Editar Slider</h1>
        <a href="{{ route('sliders.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50 me-2"></i>Volver
        </a>
    </div>

    <!-- Content Row -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 fw-bold text-primary">
                        <i class="fas fa-edit me-2"></i>Editar Información del Slider
                    </h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('sliders.update', $slider->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Título <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('title') is-invalid @enderror" 
                                           id="title" 
                                           name="title" 
                                           value="{{ old('title', $slider->title) }}" 
                                           required>
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="subtitle" class="form-label">Subtítulo</label>
                                    <input type="text" 
                                           class="form-control @error('subtitle') is-invalid @enderror" 
                                           id="subtitle" 
                                           name="subtitle" 
                                           value="{{ old('subtitle', $slider->subtitle) }}">
                                    @error('subtitle')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">Descripción</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" 
                                              name="description" 
                                              rows="4">{{ old('description', $slider->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="link" class="form-label">Enlace</label>
                                            <input type="url" 
                                                   class="form-control @error('link') is-invalid @enderror" 
                                                   id="link" 
                                                   name="link" 
                                                   value="{{ old('link', $slider->link) }}">
                                            @error('link')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="text_link" class="form-label">Texto del Enlace</label>
                                            <input type="text" 
                                                   class="form-control @error('text_link') is-invalid @enderror" 
                                                   id="text_link" 
                                                   name="text_link" 
                                                   value="{{ old('text_link', $slider->text_link) }}">
                                            @error('text_link')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="image" class="form-label">Imagen</label>
                                    <input type="file" 
                                           class="form-control @error('image') is-invalid @enderror" 
                                           id="image" 
                                           name="image" 
                                           accept="image/*">
                                    @error('image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        <small>Formatos permitidos: JPG, PNG, GIF. Tamaño máximo: 2MB</small>
                                    </div>
                                    
                                    @if($slider->image)
                                    <div class="mt-3">
                                        <label class="form-label">Imagen Actual</label>
                                        <div class="border rounded p-2">
                                            <img src="{{ asset($slider->image) }}" 
                                                 alt="{{ $slider->title }}" 
                                                 class="img-fluid rounded" 
                                                 style="max-height: 150px;">
                                        </div>
                                    </div>
                                    @endif
                                </div>

                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               id="is_active" 
                                               name="is_active" 
                                               value="1" 
                                               {{ old('is_active', $slider->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            Slider Activo
                                        </label>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="order" class="form-label">Orden</label>
                                    <input type="number" 
                                           class="form-control @error('order') is-invalid @enderror" 
                                           id="order" 
                                           name="order" 
                                           value="{{ old('order', $slider->order ?? 0) }}" 
                                           min="0">
                                    @error('order')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('sliders.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Actualizar Slider
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Image preview
            const imageInput = document.getElementById('image');
            imageInput.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        // You can add image preview functionality here if needed
                        console.log('New image selected:', file.name);
                    };
                    reader.readAsDataURL(file);
                }
            });
        });
    </script>
@endsection
