<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Exception;

class ReviewController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['index', 'show']);
    }

    /**
     * Display a listing of reviews (admin).
     */
    public function index(Request $request)
    {
        $query = Review::with(['user', 'product'])
                      ->orderByDesc('created_at');

        // Apply filters
        if ($request->filled('status')) {
            if ($request->status === 'approved') {
                $query->approved();
            } elseif ($request->status === 'pending') {
                $query->pending();
            } elseif ($request->status === 'rejected') {
                $query->rejected();
            }
        }

        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        $reviews = $query->paginate(15)->withQueryString();
        $stats = Review::getStats();

        return view('admin.reviews.index', compact('reviews', 'stats'));
    }

    /**
     * Store a newly created review.
     */
    public function store(Request $request, Product $product)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|between:1,5',
            'comment' => 'nullable|string|max:1000',
            'order_id' => 'nullable|exists:orders,id',
            'title' => 'nullable|string|max:255',
            'pros' => 'nullable|string|max:500',
            'cons' => 'nullable|string|max:500'
        ]);

        try {
            // Check if product is active
            if (!$product->isActive()) {
                return back()->withErrors('No puedes reseñar un producto inactivo.');
            }

            // Check if user has purchased the product
            if ($validated['order_id']) {
                $order = Order::where('id', $validated['order_id'])
                             ->where('user_id', Auth::id())
                             ->where('status', 'Complete')
                             ->first();

                if (!$order) {
                    return back()->withErrors('No puedes reseñar un producto que no has comprado.');
                }

                // Check if order contains this product
                $orderItem = $order->items()->where('product_id', $product->id)->first();
                if (!$orderItem) {
                    return back()->withErrors('Este producto no está en la orden especificada.');
                }
            }

            // Check if user already reviewed this product
            $existingReview = Review::where('user_id', Auth::id())
                                   ->where('product_id', $product->id)
                                   ->first();

            if ($existingReview) {
                return back()->withErrors('Ya has reseñado este producto.');
            }

            DB::beginTransaction();

            $review = new Review();
            $review->user_id = Auth::id();
            $review->product_id = $product->id;
            $review->order_id = $validated['order_id'] ?? null;
            $review->rating = $validated['rating'];
            $review->comment = $validated['comment'] ?? null;
            $review->title = $validated['title'] ?? null;
            $review->pros = $validated['pros'] ?? null;
            $review->cons = $validated['cons'] ?? null;
            $review->is_approved = config('reviews.auto_approve', false);
            $review->save();

            // Update product average rating
            $product->updateAverageRating();

            DB::commit();

            Log::info("Reseña creada: Producto {$product->name} por " . Auth::user()->email);

            // Send notification to admin if manual approval required
            if (!$review->is_approved) {
                try {
                    // Send email notification to admin
                    $admins = \App\Models\User::admins()->get();
                    foreach ($admins as $admin) {
                        Mail::to($admin->email)->send(new \App\Mail\NewReviewNotification($review));
                    }
                } catch (Exception $e) {
                    Log::warning('Error sending review notification: ' . $e->getMessage());
                }
            }

            $message = $review->is_approved 
                ? 'Reseña publicada correctamente.' 
                : 'Reseña enviada. Será revisada por nuestro equipo.';

            return back()->with('success', $message);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating review: ' . $e->getMessage());
            return back()->withErrors('Error al crear la reseña.')->withInput();
        }
    }

    /**
     * Display the specified review.
     */
    public function show(Review $review)
    {
        $review->load(['user', 'product', 'order']);
        return view('reviews.show', compact('review'));
    }

    /**
     * Approve a review (admin).
     */
    public function approve(Review $review)
    {
        try {
            DB::beginTransaction();

            $review->is_approved = true;
            $review->approved_at = now();
            $review->approved_by = Auth::id();
            $review->save();

            // Update product average rating
            $review->product->updateAverageRating();

            DB::commit();

            Log::info("Reseña aprobada: ID {$review->id} por " . Auth::user()->email);

            // Send notification to user
            try {
                Mail::to($review->user->email)->send(new \App\Mail\ReviewApproved($review));
            } catch (Exception $e) {
                Log::warning('Error sending approval notification: ' . $e->getMessage());
            }

            return back()->with('success', 'Reseña aprobada correctamente.');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error approving review: ' . $e->getMessage());
            return back()->withErrors('Error al aprobar la reseña.');
        }
    }

    /**
     * Reject a review (admin).
     */
    public function reject(Review $review)
    {
        try {
            DB::beginTransaction();

            $review->is_approved = false;
            $review->rejected_at = now();
            $review->rejected_by = Auth::id();
            $review->save();

            // Update product average rating
            $review->product->updateAverageRating();

            DB::commit();

            Log::info("Reseña rechazada: ID {$review->id} por " . Auth::user()->email);

            // Send notification to user
            try {
                Mail::to($review->user->email)->send(new \App\Mail\ReviewRejected($review));
            } catch (Exception $e) {
                Log::warning('Error sending rejection notification: ' . $e->getMessage());
            }

            return back()->with('success', 'Reseña rechazada correctamente.');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error rejecting review: ' . $e->getMessage());
            return back()->withErrors('Error al rechazar la reseña.');
        }
    }

    /**
     * Delete a review.
     */
    public function destroy(Review $review)
    {
        // Only allow user to delete their own review or admin to delete any
        if ($review->user_id !== Auth::id() && !Auth::user()->admin) {
            return back()->withErrors('No tienes permisos para eliminar esta reseña.');
        }

        try {
            DB::beginTransaction();

            $product = $review->product;
            $review->delete();

            // Update product average rating
            $product->updateAverageRating();

            DB::commit();

            Log::info("Reseña eliminada: ID {$review->id} por " . Auth::user()->email);
            return back()->with('success', 'Reseña eliminada correctamente.');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error deleting review: ' . $e->getMessage());
            return back()->withErrors('Error al eliminar la reseña.');
        }
    }

    /**
     * Update a review.
     */
    public function update(Request $request, Review $review)
    {
        // Only allow user to update their own review
        if ($review->user_id !== Auth::id()) {
            return back()->withErrors('No tienes permisos para editar esta reseña.');
        }

        // Only allow updates if review is not approved
        if ($review->is_approved) {
            return back()->withErrors('No puedes editar una reseña ya aprobada.');
        }

        $validated = $request->validate([
            'rating' => 'required|integer|between:1,5',
            'comment' => 'nullable|string|max:1000',
            'title' => 'nullable|string|max:255',
            'pros' => 'nullable|string|max:500',
            'cons' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            $review->rating = $validated['rating'];
            $review->comment = $validated['comment'] ?? null;
            $review->title = $validated['title'] ?? null;
            $review->pros = $validated['pros'] ?? null;
            $review->cons = $validated['cons'] ?? null;
            $review->save();

            // Update product average rating
            $review->product->updateAverageRating();

            DB::commit();

            Log::info("Reseña actualizada: ID {$review->id} por " . Auth::user()->email);
            return back()->with('success', 'Reseña actualizada correctamente.');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error updating review: ' . $e->getMessage());
            return back()->withErrors('Error al actualizar la reseña.')->withInput();
        }
    }

    /**
     * Get reviews for a product (AJAX).
     */
    public function getProductReviews(Product $product, Request $request)
    {
        try {
            $query = $product->reviews()->approved()->with('user');

            if ($request->filled('rating')) {
                $query->where('rating', $request->rating);
            }

            if ($request->filled('sort')) {
                switch ($request->sort) {
                    case 'newest':
                        $query->orderByDesc('created_at');
                        break;
                    case 'oldest':
                        $query->orderBy('created_at');
                        break;
                    case 'highest':
                        $query->orderByDesc('rating');
                        break;
                    case 'lowest':
                        $query->orderBy('rating');
                        break;
                    default:
                        $query->orderByDesc('created_at');
                }
            } else {
                $query->orderByDesc('created_at');
            }

            $reviews = $query->paginate(5);

            return response()->json([
                'success' => true,
                'data' => $reviews
            ]);

        } catch (Exception $e) {
            Log::error('Error getting product reviews: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las reseñas.'
            ], 500);
        }
    }

    /**
     * Export reviews to CSV.
     */
    public function export(Request $request)
    {
        $query = Review::with(['user', 'product']);

        if ($request->filled('status')) {
            if ($request->status === 'approved') {
                $query->approved();
            } elseif ($request->status === 'pending') {
                $query->pending();
            }
        }

        $reviews = $query->get();

        $filename = 'reviews_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function() use ($reviews) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'ID', 'Usuario', 'Producto', 'Calificación', 'Título', 'Comentario', 
                'Pros', 'Contras', 'Aprobada', 'Fecha Creación'
            ]);

            foreach ($reviews as $review) {
                fputcsv($file, [
                    $review->id,
                    $review->user->name ?? 'N/A',
                    $review->product->name ?? 'N/A',
                    $review->rating,
                    $review->title ?? 'N/A',
                    $review->comment ?? 'N/A',
                    $review->pros ?? 'N/A',
                    $review->cons ?? 'N/A',
                    $review->is_approved ? 'Sí' : 'No',
                    $review->created_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
} 