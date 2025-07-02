@extends('layouts.admin')

@section('content')
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Gestión de Usuarios</h1>
        <a href="{{ route('users.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50 me-2"></i>Crear Usuario
        </a>
    </div>

    <!-- Content Row -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 fw-bold text-primary">
                        <i class="fas fa-users me-2"></i>Lista de Usuarios
                    </h6>
                    <div class="d-flex gap-2">
                        <input type="text" 
                               id="searchInput" 
                               class="form-control form-control-sm" 
                               placeholder="Buscar usuarios...">
                        <select id="roleFilter" class="form-select form-select-sm" style="width: auto;">
                            <option value="">Todos los roles</option>
                            <option value="admin">Administradores</option>
                            <option value="user">Usuarios</option>
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="usersTable">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" class="text-center" style="width: 60px;">#</th>
                                    <th scope="col" class="text-center" style="width: 80px;">Avatar</th>
                                    <th scope="col">Nombre</th>
                                    <th scope="col">Email</th>
                                    <th scope="col" class="text-center" style="width: 100px;">Rol</th>
                                    <th scope="col" class="text-center" style="width: 120px;">Estado</th>
                                    <th scope="col" class="text-center" style="width: 150px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($users as $user)
                                <tr data-user-id="{{ $user->id }}" 
                                    data-user-name="{{ strtolower($user->name) }}"
                                    data-user-email="{{ strtolower($user->email) }}"
                                    data-user-role="{{ $user->is_admin ? 'admin' : 'user' }}">
                                    <td class="text-center">
                                        <span class="badge bg-secondary">{{ $user->id }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if($user->image)
                                            <img src="{{ asset($user->image) }}" 
                                                 alt="{{ $user->name }}" 
                                                 class="rounded-circle" 
                                                 style="width: 40px; height: 40px; object-fit: cover;">
                                        @else
                                            <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center" 
                                                 style="width: 40px; height: 40px;">
                                                <i class="fas fa-user text-white"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <h6 class="mb-1">{{ $user->name }}</h6>
                                        @if($user->phone)
                                            <small class="text-muted">{{ $user->phone }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-break">{{ $user->email }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-{{ $user->is_admin ? 'danger' : 'info' }}">
                                            {{ $user->is_admin ? 'Admin' : 'Usuario' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-{{ $user->email_verified_at ? 'success' : 'warning' }}">
                                            {{ $user->email_verified_at ? 'Verificado' : 'Pendiente' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a class="btn btn-outline-primary" 
                                               href="{{ route('users.edit', $user->id) }}"
                                               data-bs-toggle="tooltip" 
                                               title="Editar usuario">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @if($user->id !== auth()->id())
                                            <button type="button" 
                                                    class="btn btn-outline-danger confirm-delete"
                                                    data-user-id="{{ $user->id }}"
                                                    data-user-name="{{ $user->name }}"
                                                    data-bs-toggle="tooltip" 
                                                    title="Eliminar usuario">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-users fa-3x mb-3"></i>
                                            <h5>No hay usuarios registrados</h5>
                                            <p>Comienza creando tu primer usuario</p>
                                            <a href="{{ route('users.create') }}" class="btn btn-primary">
                                                <i class="fas fa-plus me-2"></i>Crear Usuario
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($users->hasPages())
                <div class="card-footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted">
                            Mostrando {{ $users->firstItem() }} a {{ $users->lastItem() }} de {{ $users->total() }} usuarios
                        </div>
                        {{ $users->links() }}
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
                    <p>¿Estás seguro de que quieres eliminar al usuario "<strong id="deleteUserName"></strong>"?</p>
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
            // Search functionality
            const searchInput = document.getElementById('searchInput');
            const tableRows = document.querySelectorAll('#usersTable tbody tr');

            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                
                tableRows.forEach(row => {
                    const userName = row.getAttribute('data-user-name');
                    const userEmail = row.getAttribute('data-user-email');
                    
                    if (userName.includes(searchTerm) || userEmail.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });

            // Role filter functionality
            document.getElementById('roleFilter').addEventListener('change', function() {
                const filter = this.value;
                
                tableRows.forEach(row => {
                    const userRole = row.getAttribute('data-user-role');
                    
                    if (filter === '' || userRole === filter) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });

            // Delete confirmation
            document.querySelectorAll('.confirm-delete').forEach(btn => {
                btn.addEventListener('click', function() {
                    const userId = this.getAttribute('data-user-id');
                    const userName = this.getAttribute('data-user-name');
                    
                    document.getElementById('deleteUserName').textContent = userName;
                    document.getElementById('deleteForm').action = `/admin/users/${userId}`;
                    
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
