<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Exception;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = User::orderByDesc('created_at');

        // Apply filters
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('role')) {
            if ($request->role === 'admin') {
                $query->admins();
            } else {
                $query->regular();
            }
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } else {
                $query->where('is_active', false);
            }
        }

        $users = $query->paginate(15)->withQueryString();
        $stats = User::getStats();

        return view('users.index', compact('users', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'admin' => 'boolean',
            'is_active' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048'
        ]);

        DB::beginTransaction();
        try {
            $user = new User();
            $user->name = $validated['name'];
            $user->last_name = $validated['last_name'] ?? null;
            $user->email = $validated['email'];
            $user->phone = $validated['phone'] ?? null;
            $user->address = $validated['address'] ?? null;
            $user->password = Hash::make($validated['password']);
            $user->admin = $validated['admin'] ?? false;
            $user->is_active = $validated['is_active'] ?? true;

            // Handle image upload
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = 'users/' . uniqid('user_') . '.' . $image->getClientOriginalExtension();
                $image->storeAs('public', $imageName);
                $user->image = $imageName;
            }

            $user->save();
            DB::commit();

            Log::info("Usuario creado: {$user->email} por " . Auth::user()->email);
            return redirect()->route('users.index')->with('success', 'Usuario creado correctamente.');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating user: ' . $e->getMessage());
            return back()->withErrors('Error al crear el usuario.')->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $user->load(['orders', 'reviews']);
        $stats = [
            'total_orders' => $user->orders()->count(),
            'total_spent' => $user->orders()->paid()->sum('total'),
            'total_reviews' => $user->reviews()->count(),
            'last_order' => $user->orders()->latest()->first(),
            'last_login' => $user->last_login_at
        ];

        return view('users.show', compact('user', 'stats'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'admin' => 'boolean',
            'is_active' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048'
        ]);

        DB::beginTransaction();
        try {
            $user->name = $validated['name'];
            $user->last_name = $validated['last_name'] ?? null;
            $user->email = $validated['email'];
            $user->phone = $validated['phone'] ?? null;
            $user->address = $validated['address'] ?? null;
            $user->admin = $validated['admin'] ?? false;
            $user->is_active = $validated['is_active'] ?? true;

            // Update password if provided
            if (!empty($validated['password'])) {
                $user->password = Hash::make($validated['password']);
            }

            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image
                if ($user->image && Storage::disk('public')->exists($user->image)) {
                    Storage::disk('public')->delete($user->image);
                }

                $image = $request->file('image');
                $imageName = 'users/' . uniqid('user_') . '.' . $image->getClientOriginalExtension();
                $image->storeAs('public', $imageName);
                $user->image = $imageName;
            }

            $user->save();
            DB::commit();

            Log::info("Usuario actualizado: {$user->email} por " . Auth::user()->email);
            return redirect()->route('users.index')->with('success', 'Usuario actualizado correctamente.');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error updating user: ' . $e->getMessage());
            return back()->withErrors('Error al actualizar el usuario.')->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        // Prevent self-deletion
        if ($user->id === Auth::id()) {
            return back()->withErrors('No puedes eliminar tu propia cuenta.');
        }

        DB::beginTransaction();
        try {
            // Delete user image
            if ($user->image && Storage::disk('public')->exists($user->image)) {
                Storage::disk('public')->delete($user->image);
            }

            // Soft delete user
            $user->delete();
            DB::commit();

            Log::info("Usuario eliminado: {$user->email} por " . Auth::user()->email);
            return redirect()->route('users.index')->with('success', 'Usuario eliminado correctamente.');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error deleting user: ' . $e->getMessage());
            return back()->withErrors('Error al eliminar el usuario.');
        }
    }

    /**
     * Toggle user active status.
     */
    public function toggleStatus(User $user)
    {
        DB::beginTransaction();
        try {
            $user->is_active = !$user->is_active;
            $user->save();
            DB::commit();

            $status = $user->is_active ? 'activado' : 'desactivado';
            Log::info("Usuario {$status}: {$user->email} por " . Auth::user()->email);
            
            return redirect()->back()->with('success', "Usuario {$status} correctamente.");

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error toggling user status: ' . $e->getMessage());
            return back()->withErrors('Error al cambiar el estado del usuario.');
        }
    }

    /**
     * Toggle admin role.
     */
    public function toggleAdmin(User $user)
    {
        // Prevent self-admin-removal
        if ($user->id === Auth::id()) {
            return back()->withErrors('No puedes cambiar tu propio rol de administrador.');
        }

        DB::beginTransaction();
        try {
            $user->admin = !$user->admin;
            $user->save();
            DB::commit();

            $role = $user->admin ? 'administrador' : 'usuario regular';
            Log::info("Usuario promovido a {$role}: {$user->email} por " . Auth::user()->email);
            
            return redirect()->back()->with('success', "Usuario promovido a {$role} correctamente.");

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error toggling admin role: ' . $e->getMessage());
            return back()->withErrors('Error al cambiar el rol del usuario.');
        }
    }

    /**
     * Export users to CSV.
     */
    public function export(Request $request)
    {
        $query = User::query();

        if ($request->filled('role')) {
            if ($request->role === 'admin') {
                $query->admins();
            } else {
                $query->regular();
            }
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } else {
                $query->where('is_active', false);
            }
        }

        $users = $query->get();

        $filename = 'users_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function() use ($users) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'ID', 'Nombre', 'Apellido', 'Email', 'Teléfono', 'Dirección', 
                'Admin', 'Activo', 'Fecha Registro', 'Último Login'
            ]);

            foreach ($users as $user) {
                fputcsv($file, [
                    $user->id,
                    $user->name,
                    $user->last_name ?? 'N/A',
                    $user->email,
                    $user->phone ?? 'N/A',
                    $user->address ?? 'N/A',
                    $user->admin ? 'Sí' : 'No',
                    $user->is_active ? 'Sí' : 'No',
                    $user->created_at->format('Y-m-d H:i:s'),
                    $user->last_login_at ? $user->last_login_at->format('Y-m-d H:i:s') : 'N/A'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
