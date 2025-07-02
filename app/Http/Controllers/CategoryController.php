<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Exception;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Category::withCount(['products' => function ($q) {
            $q->active();
        }])->orderByDesc('created_at');

        // Apply filters
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } else {
                $query->where('is_active', false);
            }
        }

        if ($request->filled('type')) {
            if ($request->type === 'root') {
                $query->root();
            } else {
                $query->children();
            }
        }

        $categories = $query->paginate(15)->withQueryString();
        $stats = Category::getStats();

        return view('categories.index', compact('categories', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $parentCategories = Category::active()->root()->ordered()->get();
        return view('categories.create', compact('parentCategories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'icon' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'color' => 'nullable|string|max:7'
        ]);

        DB::beginTransaction();
        try {
            $category = new Category();
            $category->name = $validated['name'];
            $category->description = $validated['description'] ?? null;
            $category->icon = $validated['icon'] ?? null;
            $category->parent_id = $validated['parent_id'] ?? null;
            $category->is_active = $validated['is_active'] ?? true;
            $category->sort_order = $validated['sort_order'] ?? Category::max('sort_order') + 1;
            $category->meta_title = $validated['meta_title'] ?? null;
            $category->meta_description = $validated['meta_description'] ?? null;
            $category->color = $validated['color'] ?? null;

            // Handle image upload
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = 'categories/' . uniqid('cat_') . '.' . $image->getClientOriginalExtension();
                $image->storeAs('public', $imageName);
                $category->image = $imageName;
            }

            $category->save();
            DB::commit();

            Log::info("Categoría creada: {$category->name} por " . Auth::user()->email);
            return redirect()->route('categories.index')->with('success', 'Categoría creada correctamente.');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating category: ' . $e->getMessage());
            return back()->withErrors('Error al crear la categoría.')->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        $category->load(['products' => function ($q) {
            $q->active()->with('reviews');
        }, 'children', 'parent']);

        $stats = [
            'total_products' => $category->products()->count(),
            'active_products' => $category->products()->active()->count(),
            'total_children' => $category->children()->count(),
            'average_rating' => $category->products()->with('reviews')->get()->avg(function ($product) {
                return $product->reviews->avg('rating');
            })
        ];

        return view('categories.show', compact('category', 'stats'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        $parentCategories = Category::active()
                                  ->where('id', '!=', $category->id)
                                  ->root()
                                  ->ordered()
                                  ->get();

        return view('categories.edit', compact('category', 'parentCategories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'icon' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'color' => 'nullable|string|max:7'
        ]);

        // Prevent circular reference
        if ($validated['parent_id'] == $category->id) {
            return back()->withErrors('Una categoría no puede ser padre de sí misma.')->withInput();
        }

        DB::beginTransaction();
        try {
            $category->name = $validated['name'];
            $category->description = $validated['description'] ?? null;
            $category->icon = $validated['icon'] ?? null;
            $category->parent_id = $validated['parent_id'] ?? null;
            $category->is_active = $validated['is_active'] ?? true;
            $category->sort_order = $validated['sort_order'] ?? $category->sort_order;
            $category->meta_title = $validated['meta_title'] ?? null;
            $category->meta_description = $validated['meta_description'] ?? null;
            $category->color = $validated['color'] ?? null;

            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image
                if ($category->image && Storage::disk('public')->exists($category->image)) {
                    Storage::disk('public')->delete($category->image);
                }

                $image = $request->file('image');
                $imageName = 'categories/' . uniqid('cat_') . '.' . $image->getClientOriginalExtension();
                $image->storeAs('public', $imageName);
                $category->image = $imageName;
            }

            $category->save();
            DB::commit();

            Log::info("Categoría actualizada: {$category->name} por " . Auth::user()->email);
            return redirect()->route('categories.index')->with('success', 'Categoría actualizada correctamente.');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error updating category: ' . $e->getMessage());
            return back()->withErrors('Error al actualizar la categoría.')->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        // Check if category has products
        if ($category->products()->count() > 0) {
            return back()->withErrors('No se puede eliminar una categoría que tiene productos asociados.');
        }

        // Check if category has children
        if ($category->children()->count() > 0) {
            return back()->withErrors('No se puede eliminar una categoría que tiene subcategorías.');
        }

        DB::beginTransaction();
        try {
            // Delete category image
            if ($category->image && Storage::disk('public')->exists($category->image)) {
                Storage::disk('public')->delete($category->image);
            }

            $category->delete();
            DB::commit();

            Log::info("Categoría eliminada: {$category->name} por " . Auth::user()->email);
            return redirect()->route('categories.index')->with('success', 'Categoría eliminada correctamente.');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error deleting category: ' . $e->getMessage());
            return back()->withErrors('Error al eliminar la categoría.');
        }
    }

    /**
     * Toggle category active status.
     */
    public function toggleStatus(Category $category)
    {
        DB::beginTransaction();
        try {
            $category->is_active = !$category->is_active;
            $category->save();
            DB::commit();

            $status = $category->is_active ? 'activada' : 'desactivada';
            Log::info("Categoría {$status}: {$category->name} por " . Auth::user()->email);
            
            return redirect()->back()->with('success', "Categoría {$status} correctamente.");

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error toggling category status: ' . $e->getMessage());
            return back()->withErrors('Error al cambiar el estado de la categoría.');
        }
    }

    /**
     * Display category for frontend.
     */
    public function display(Category $category)
    {
        if (!$category->isActive()) {
            abort(404);
        }

        $products = $category->products()
                            ->active()
                            ->inStock()
                            ->with(['reviews', 'category'])
                            ->paginate(12);

        $subcategories = $category->children()->active()->withActiveProducts()->get();

        return view('categories.display', compact('category', 'products', 'subcategories'));
    }

    /**
     * Export categories to CSV.
     */
    public function export(Request $request)
    {
        $query = Category::with('parent');

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } else {
                $query->where('is_active', false);
            }
        }

        $categories = $query->get();

        $filename = 'categories_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function() use ($categories) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'ID', 'Nombre', 'Descripción', 'Padre', 'Activa', 'Orden', 
                'Productos', 'Fecha Creación'
            ]);

            foreach ($categories as $category) {
                fputcsv($file, [
                    $category->id,
                    $category->name,
                    $category->description ?? 'N/A',
                    $category->parent->name ?? 'N/A',
                    $category->is_active ? 'Sí' : 'No',
                    $category->sort_order,
                    $category->products()->count(),
                    $category->created_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
