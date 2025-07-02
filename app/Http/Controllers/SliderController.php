<?php

namespace App\Http\Controllers;

use App\Models\Slider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Exception;

class SliderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Slider::orderBy('sort_order', 'asc')->orderByDesc('created_at');

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

        $sliders = $query->paginate(15)->withQueryString();
        $stats = Slider::getStats();

        return view('sliders.index', compact('sliders', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('sliders.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'link' => 'nullable|url|max:255',
            'text_link' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
            'mobile_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'alt_text' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date'
        ]);

        DB::beginTransaction();
        try {
            $slider = new Slider();
            $slider->title = $validated['title'];
            $slider->description = $validated['description'] ?? null;
            $slider->link = $validated['link'] ?? null;
            $slider->text_link = $validated['text_link'] ?? null;
            $slider->is_active = $validated['is_active'] ?? true;
            $slider->sort_order = $validated['sort_order'] ?? Slider::max('sort_order') + 1;
            $slider->alt_text = $validated['alt_text'] ?? null;
            $slider->start_date = $validated['start_date'] ?? null;
            $slider->end_date = $validated['end_date'] ?? null;

            // Handle main image upload
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = 'sliders/' . uniqid('slider_') . '.' . $image->getClientOriginalExtension();
                $image->storeAs('public', $imageName);
                $slider->image = $imageName;
            }

            // Handle mobile image upload
            if ($request->hasFile('mobile_image')) {
                $mobileImage = $request->file('mobile_image');
                $mobileImageName = 'sliders/' . uniqid('slider_mobile_') . '.' . $mobileImage->getClientOriginalExtension();
                $mobileImage->storeAs('public', $mobileImageName);
                $slider->mobile_image = $mobileImageName;
            }

            $slider->save();
            DB::commit();

            Log::info("Slider creado: {$slider->title} por " . Auth::user()->email);
            return redirect()->route('sliders.index')->with('success', 'Slider creado correctamente.');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating slider: ' . $e->getMessage());
            return back()->withErrors('Error al crear el slider.')->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Slider $slider)
    {
        return view('sliders.show', compact('slider'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Slider $slider)
    {
        return view('sliders.edit', compact('slider'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Slider $slider)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'link' => 'nullable|url|max:255',
            'text_link' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'mobile_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'alt_text' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date'
        ]);

        DB::beginTransaction();
        try {
            $slider->title = $validated['title'];
            $slider->description = $validated['description'] ?? null;
            $slider->link = $validated['link'] ?? null;
            $slider->text_link = $validated['text_link'] ?? null;
            $slider->is_active = $validated['is_active'] ?? true;
            $slider->sort_order = $validated['sort_order'] ?? $slider->sort_order;
            $slider->alt_text = $validated['alt_text'] ?? null;
            $slider->start_date = $validated['start_date'] ?? null;
            $slider->end_date = $validated['end_date'] ?? null;

            // Handle main image upload
            if ($request->hasFile('image')) {
                // Delete old image
                if ($slider->image && Storage::disk('public')->exists($slider->image)) {
                    Storage::disk('public')->delete($slider->image);
                }

                $image = $request->file('image');
                $imageName = 'sliders/' . uniqid('slider_') . '.' . $image->getClientOriginalExtension();
                $image->storeAs('public', $imageName);
                $slider->image = $imageName;
            }

            // Handle mobile image upload
            if ($request->hasFile('mobile_image')) {
                // Delete old mobile image
                if ($slider->mobile_image && Storage::disk('public')->exists($slider->mobile_image)) {
                    Storage::disk('public')->delete($slider->mobile_image);
                }

                $mobileImage = $request->file('mobile_image');
                $mobileImageName = 'sliders/' . uniqid('slider_mobile_') . '.' . $mobileImage->getClientOriginalExtension();
                $mobileImage->storeAs('public', $mobileImageName);
                $slider->mobile_image = $mobileImageName;
            }

            $slider->save();
            DB::commit();

            Log::info("Slider actualizado: {$slider->title} por " . Auth::user()->email);
            return redirect()->route('sliders.index')->with('success', 'Slider actualizado correctamente.');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error updating slider: ' . $e->getMessage());
            return back()->withErrors('Error al actualizar el slider.')->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Slider $slider)
    {
        DB::beginTransaction();
        try {
            // Delete images
            if ($slider->image && Storage::disk('public')->exists($slider->image)) {
                Storage::disk('public')->delete($slider->image);
            }

            if ($slider->mobile_image && Storage::disk('public')->exists($slider->mobile_image)) {
                Storage::disk('public')->delete($slider->mobile_image);
            }

            $slider->delete();
            DB::commit();

            Log::info("Slider eliminado: {$slider->title} por " . Auth::user()->email);
            return redirect()->route('sliders.index')->with('success', 'Slider eliminado correctamente.');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error deleting slider: ' . $e->getMessage());
            return back()->withErrors('Error al eliminar el slider.');
        }
    }

    /**
     * Toggle slider active status.
     */
    public function toggleStatus(Slider $slider)
    {
        DB::beginTransaction();
        try {
            $slider->is_active = !$slider->is_active;
            $slider->save();
            DB::commit();

            $status = $slider->is_active ? 'activado' : 'desactivado';
            Log::info("Slider {$status}: {$slider->title} por " . Auth::user()->email);
            
            return redirect()->back()->with('success', "Slider {$status} correctamente.");

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error toggling slider status: ' . $e->getMessage());
            return back()->withErrors('Error al cambiar el estado del slider.');
        }
    }

    /**
     * Update slider order.
     */
    public function updateOrder(Request $request)
    {
        $validated = $request->validate([
            'sliders' => 'required|array',
            'sliders.*.id' => 'required|exists:sliders,id',
            'sliders.*.sort_order' => 'required|integer|min:0'
        ]);

        DB::beginTransaction();
        try {
            foreach ($validated['sliders'] as $sliderData) {
                Slider::where('id', $sliderData['id'])
                     ->update(['sort_order' => $sliderData['sort_order']]);
            }

            DB::commit();
            Log::info("Orden de sliders actualizado por " . Auth::user()->email);
            
            return response()->json(['success' => true, 'message' => 'Orden actualizado correctamente.']);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error updating slider order: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al actualizar el orden.'], 500);
        }
    }

    /**
     * Get active sliders for frontend.
     */
    public function getActive()
    {
        $sliders = Slider::active()
                        ->current()
                        ->ordered()
                        ->get();

        return response()->json($sliders);
    }

    /**
     * Export sliders to CSV.
     */
    public function export(Request $request)
    {
        $query = Slider::query();

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } else {
                $query->where('is_active', false);
            }
        }

        $sliders = $query->get();

        $filename = 'sliders_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function() use ($sliders) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'ID', 'Título', 'Descripción', 'Enlace', 'Texto Enlace', 
                'Activo', 'Orden', 'Fecha Inicio', 'Fecha Fin', 'Fecha Creación'
            ]);

            foreach ($sliders as $slider) {
                fputcsv($file, [
                    $slider->id,
                    $slider->title,
                    $slider->description ?? 'N/A',
                    $slider->link ?? 'N/A',
                    $slider->text_link ?? 'N/A',
                    $slider->is_active ? 'Sí' : 'No',
                    $slider->sort_order,
                    $slider->start_date ? $slider->start_date->format('Y-m-d') : 'N/A',
                    $slider->end_date ? $slider->end_date->format('Y-m-d') : 'N/A',
                    $slider->created_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
