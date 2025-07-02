<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class Item extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'description',
        'image',
        'price',
        'qty',
        'fecha',
        'product_id',
        'sku',
        'weight',
        'options'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'price' => 'decimal:2',
        'qty' => 'integer',
        'fecha' => 'date',
        'weight' => 'decimal:2',
        'options' => 'array',
        'deleted_at' => 'datetime'
    ];

    /**
     * The attributes that should be mutated to dates.
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'fecha'
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            if (empty($item->fecha)) {
                $item->fecha = now()->toDateString();
            }
        });
    }

    // ACCESSORS

    /**
     * Get the item's formatted price.
     */
    public function getFormattedPriceAttribute(): string
    {
        return '$' . number_format($this->price, 2);
    }

    /**
     * Get the item's total price.
     */
    public function getTotalPriceAttribute(): float
    {
        return $this->price * $this->qty;
    }

    /**
     * Get the item's formatted total price.
     */
    public function getFormattedTotalPriceAttribute(): string
    {
        return '$' . number_format($this->total_price, 2);
    }

    /**
     * Get the item's image URL.
     */
    public function getImageUrlAttribute(): string
    {
        if ($this->image) {
            return asset('storage/' . $this->image);
        }
        
        if ($this->product && $this->product->image) {
            return $this->product->image_url;
        }
        
        return asset('images/item-placeholder.jpg');
    }

    /**
     * Get the item's thumbnail URL.
     */
    public function getThumbnailUrlAttribute(): string
    {
        if ($this->image) {
            $pathInfo = pathinfo($this->image);
            $thumbnailPath = $pathInfo['dirname'] . '/thumb_' . $pathInfo['basename'];
            return asset('storage/' . $thumbnailPath);
        }
        
        if ($this->product) {
            return $this->product->thumbnail_url;
        }
        
        return asset('images/item-placeholder-thumb.jpg');
    }

    /**
     * Get the item's SKU.
     */
    public function getSkuAttribute(): string
    {
        if ($this->attributes['sku']) {
            return $this->attributes['sku'];
        }
        
        if ($this->product) {
            return $this->product->sku;
        }
        
        return 'ITEM-' . $this->id;
    }

    // MUTATORS

    /**
     * Set the item's name.
     */
    public function setNameAttribute($value): void
    {
        $this->attributes['name'] = ucfirst(trim($value));
    }

    /**
     * Set the item's price.
     */
    public function setPriceAttribute($value): void
    {
        $this->attributes['price'] = (float) $value;
    }

    /**
     * Set the item's quantity.
     */
    public function setQtyAttribute($value): void
    {
        $this->attributes['qty'] = (int) max(1, $value);
    }

    // METHODS

    /**
     * Check if item has image.
     */
    public function hasImage(): bool
    {
        return !empty($this->image);
    }

    /**
     * Check if item has product.
     */
    public function hasProduct(): bool
    {
        return !is_null($this->product_id);
    }

    /**
     * Get item weight total.
     */
    public function getWeightTotal(): float
    {
        return ($this->weight ?? 0) * $this->qty;
    }

    /**
     * Update item from product.
     */
    public function updateFromProduct(Product $product): void
    {
        $this->update([
            'name' => $product->name,
            'description' => $product->description,
            'image' => $product->image,
            'price' => $product->price,
            'sku' => $product->sku,
            'weight' => $product->weight,
            'options' => [
                'product_id' => $product->id,
                'category_id' => $product->category_id
            ]
        ]);
    }

    /**
     * Create item from product.
     */
    public static function createFromProduct(Product $product, int $quantity = 1, array $options = []): self
    {
        return static::create([
            'name' => $product->name,
            'description' => $product->description,
            'image' => $product->image,
            'price' => $product->price,
            'qty' => $quantity,
            'product_id' => $product->id,
            'sku' => $product->sku,
            'weight' => $product->weight,
            'options' => array_merge([
                'product_id' => $product->id,
                'category_id' => $product->category_id
            ], $options)
        ]);
    }

    // RELATIONSHIPS

    /**
     * Get the product that owns the item.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get all orders that contain this item.
     */
    public function orders()
    {
        return $this->belongsToMany(Order::class)->withPivot(['qty', 'fecha']);
    }

    /**
     * Get the category through product.
     */
    public function category()
    {
        return $this->hasOneThrough(Category::class, Product::class);
    }

    // SCOPES

    /**
     * Scope a query to only include items with products.
     */
    public function scopeWithProduct(Builder $query): Builder
    {
        return $query->whereNotNull('product_id');
    }

    /**
     * Scope a query to only include items without products.
     */
    public function scopeWithoutProduct(Builder $query): Builder
    {
        return $query->whereNull('product_id');
    }

    /**
     * Scope a query to search items by name or description.
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
     * Scope a query to filter by quantity range.
     */
    public function scopeQuantityRange(Builder $query, int $min, int $max): Builder
    {
        return $query->whereBetween('qty', [$min, $max]);
    }

    /**
     * Scope a query to order by most recent.
     */
    public function scopeRecent(Builder $query): Builder
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Scope a query to order by price.
     */
    public function scopeByPrice(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderBy('price', $direction);
    }

    /**
     * Scope a query to order by quantity.
     */
    public function scopeByQuantity(Builder $query, string $direction = 'desc'): Builder
    {
        return $query->orderBy('qty', $direction);
    }

    // STATIC METHODS

    /**
     * Get items statistics.
     */
    public static function getStats(): array
    {
        return [
            'total' => static::count(),
            'with_product' => static::withProduct()->count(),
            'without_product' => static::withoutProduct()->count(),
            'total_quantity' => static::sum('qty'),
            'total_value' => static::sum(DB::raw('price * qty')),
            'average_price' => static::avg('price'),
            'average_quantity' => static::avg('qty')
        ];
    }

    /**
     * Get top selling items.
     */
    public static function getTopSelling(int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return static::with('product')
                    ->select('name', 'product_id', DB::raw('SUM(qty) as total_sold'))
                    ->groupBy('name', 'product_id')
                    ->orderBy('total_sold', 'desc')
                    ->limit($limit)
                    ->get();
    }

    /**
     * Get items by date range.
     */
    public static function getByDateRange(string $startDate, string $endDate): \Illuminate\Database\Eloquent\Collection
    {
        return static::with(['product', 'orders'])
                    ->whereBetween('fecha', [$startDate, $endDate])
                    ->get();
    }

    /**
     * Get items summary by product.
     */
    public static function getSummaryByProduct(): \Illuminate\Database\Eloquent\Collection
    {
        return static::with('product')
                    ->select('product_id', DB::raw('COUNT(*) as order_count'), DB::raw('SUM(qty) as total_quantity'), DB::raw('SUM(price * qty) as total_value'))
                    ->groupBy('product_id')
                    ->orderBy('total_value', 'desc')
                    ->get();
    }
}
