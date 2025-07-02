<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'description',
        'label',
        'price',
        'image',
        'category_id',
        'stock',
        'min_stock',
        'is_active',
        'sku',
        'weight',
        'dimensions',
        'tags',
        'featured',
        'sort_order'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:2',
        'stock' => 'integer',
        'min_stock' => 'integer',
        'weight' => 'decimal:2',
        'featured' => 'boolean',
        'sort_order' => 'integer',
        'tags' => 'array',
        'deleted_at' => 'datetime'
    ];

    /**
     * The attributes that should be mutated to dates.
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->sku)) {
                $product->sku = static::generateSku($product->name);
            }
        });

        static::updating(function ($product) {
            if ($product->isDirty('stock') && $product->stock <= $product->min_stock) {
                // Aquí podrías enviar una notificación de bajo stock
            }
        });
    }

    // ACCESSORS

    /**
     * Get the product's formatted price.
     */
    public function getFormattedPriceAttribute(): string
    {
        return '$' . number_format($this->price, 2);
    }

    /**
     * Get the product's image URL.
     */
    public function getImageUrlAttribute(): string
    {
        if ($this->image) {
            return asset('storage/' . $this->image);
        }
        
        return asset('images/product-placeholder.jpg');
    }

    /**
     * Get the product's thumbnail URL.
     */
    public function getThumbnailUrlAttribute(): string
    {
        if ($this->image) {
            $pathInfo = pathinfo($this->image);
            $thumbnailPath = $pathInfo['dirname'] . '/thumb_' . $pathInfo['basename'];
            return asset('storage/' . $thumbnailPath);
        }
        
        return asset('images/product-placeholder-thumb.jpg');
    }

    /**
     * Get the product's slug.
     */
    public function getSlugAttribute(): string
    {
        return Str::slug($this->name);
    }

    /**
     * Get the product's status text.
     */
    public function getStatusTextAttribute(): string
    {
        if (!$this->is_active) {
            return 'Inactivo';
        }
        
        if ($this->isOutOfStock()) {
            return 'Sin Stock';
        }
        
        if ($this->isLowStock()) {
            return 'Stock Bajo';
        }
        
        return 'Disponible';
    }

    /**
     * Get the product's status color.
     */
    public function getStatusColorAttribute(): string
    {
        if (!$this->is_active) {
            return 'secondary';
        }
        
        if ($this->isOutOfStock()) {
            return 'danger';
        }
        
        if ($this->isLowStock()) {
            return 'warning';
        }
        
        return 'success';
    }

    // MUTATORS

    /**
     * Set the product's name and generate slug.
     */
    public function setNameAttribute($value): void
    {
        $this->attributes['name'] = ucfirst(trim($value));
    }

    /**
     * Set the product's price.
     */
    public function setPriceAttribute($value): void
    {
        $this->attributes['price'] = (float) $value;
    }

    // METHODS

    /**
     * Check if product is out of stock.
     */
    public function isOutOfStock(): bool
    {
        return $this->stock <= 0;
    }

    /**
     * Check if product has low stock.
     */
    public function isLowStock(): bool
    {
        return $this->stock <= $this->min_stock && $this->stock > 0;
    }

    /**
     * Check if product is available.
     */
    public function isAvailable(): bool
    {
        return $this->is_active && !$this->isOutOfStock();
    }

    /**
     * Update product stock.
     */
    public function updateStock(int $quantity, string $action = 'remove', string $notes = null): void
    {
        $previousStock = $this->stock;
        
        if ($action === 'remove') {
            $this->stock = max(0, $this->stock - $quantity);
        } else {
            $this->stock += $quantity;
        }
        
        $this->save();

        // Log inventory movement
        InventoryLog::create([
            'product_id' => $this->id,
            'action' => $action,
            'quantity' => $quantity,
            'previous_stock' => $previousStock,
            'new_stock' => $this->stock,
            'user_id' => auth()->id(),
            'notes' => $notes ?? "Stock {$action} by " . (auth()->user()->name ?? 'System')
        ]);
    }

    /**
     * Get average rating.
     */
    public function getAverageRating(): float
    {
        return $this->approvedReviews()->avg('rating') ?? 0.0;
    }

    /**
     * Get total reviews count.
     */
    public function getTotalReviewsCount(): int
    {
        return $this->approvedReviews()->count();
    }

    /**
     * Generate unique SKU.
     */
    public static function generateSku(string $name): string
    {
        $base = Str::upper(Str::slug($name, ''));
        $sku = substr($base, 0, 6) . '-' . Str::random(4);
        
        while (static::where('sku', $sku)->exists()) {
            $sku = substr($base, 0, 6) . '-' . Str::random(4);
        }
        
        return $sku;
    }

    // RELATIONSHIPS

    /**
     * Get the category that owns the product.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get all reviews for the product.
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get approved reviews for the product.
     */
    public function approvedReviews()
    {
        return $this->reviews()->approved();
    }

    /**
     * Get inventory logs for the product.
     */
    public function inventoryLogs()
    {
        return $this->hasMany(InventoryLog::class);
    }

    /**
     * Get order items for the product.
     */
    public function orderItems()
    {
        return $this->hasMany(Item::class);
    }

    // SCOPES

    /**
     * Scope a query to only include active products.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include products in stock.
     */
    public function scopeInStock(Builder $query): Builder
    {
        return $query->where('stock', '>', 0);
    }

    /**
     * Scope a query to only include products with low stock.
     */
    public function scopeLowStock(Builder $query): Builder
    {
        return $query->where('stock', '<=', DB::raw('min_stock'))
                    ->where('stock', '>', 0);
    }

    /**
     * Scope a query to only include out of stock products.
     */
    public function scopeOutOfStock(Builder $query): Builder
    {
        return $query->where('stock', '<=', 0);
    }

    /**
     * Scope a query to only include featured products.
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('featured', true);
    }

    /**
     * Scope a query to search products by name or description.
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhere('sku', 'like', "%{$search}%");
        });
    }

    /**
     * Scope a query to filter by price range.
     */
    public function scopePriceRange(Builder $query, float $min, float $max): Builder
    {
        return $query->whereBetween('price', [$min, $max]);
    }

    /**
     * Scope a query to order by most popular.
     */
    public function scopePopular(Builder $query): Builder
    {
        return $query->withCount('orderItems')
                    ->orderBy('order_items_count', 'desc');
    }

    // STATIC METHODS

    /**
     * Get products statistics.
     */
    public static function getStats(): array
    {
        return [
            'total' => static::count(),
            'active' => static::active()->count(),
            'in_stock' => static::inStock()->count(),
            'low_stock' => static::lowStock()->count(),
            'out_of_stock' => static::outOfStock()->count(),
            'featured' => static::featured()->count(),
            'total_value' => static::sum(DB::raw('price * stock'))
        ];
    }

    /**
     * Get low stock alerts.
     */
    public static function getLowStockAlerts(): \Illuminate\Database\Eloquent\Collection
    {
        return static::lowStock()->with('category')->get();
    }
}
