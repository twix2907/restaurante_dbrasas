<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'description',
        'icon',
        'image',
        'slug',
        'is_active',
        'sort_order',
        'parent_id',
        'meta_title',
        'meta_description',
        'color'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'parent_id' => 'integer',
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

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
            if (empty($category->sort_order)) {
                $maxOrder = static::max('sort_order') ?? 0;
                $category->sort_order = $maxOrder + 1;
            }
        });

        static::updating(function ($category) {
            if ($category->isDirty('name') && empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    // ACCESSORS

    /**
     * Get the category's image URL.
     */
    public function getImageUrlAttribute(): string
    {
        if ($this->image) {
            return asset('storage/' . $this->image);
        }
        
        return asset('images/category-placeholder.jpg');
    }

    /**
     * Get the category's thumbnail URL.
     */
    public function getThumbnailUrlAttribute(): string
    {
        if ($this->image) {
            $pathInfo = pathinfo($this->image);
            $thumbnailPath = $pathInfo['dirname'] . '/thumb_' . $pathInfo['basename'];
            return asset('storage/' . $thumbnailPath);
        }
        
        return asset('images/category-placeholder-thumb.jpg');
    }

    /**
     * Get the category's full path name.
     */
    public function getFullPathAttribute(): string
    {
        if ($this->parent) {
            return $this->parent->name . ' > ' . $this->name;
        }
        
        return $this->name;
    }

    /**
     * Get the category's products count.
     */
    public function getProductsCountAttribute(): int
    {
        return $this->products()->count();
    }

    /**
     * Get the category's active products count.
     */
    public function getActiveProductsCountAttribute(): int
    {
        return $this->products()->active()->count();
    }

    // MUTATORS

    /**
     * Set the category's name and generate slug.
     */
    public function setNameAttribute($value): void
    {
        $this->attributes['name'] = ucfirst(trim($value));
    }

    /**
     * Set the category's slug.
     */
    public function setSlugAttribute($value): void
    {
        $this->attributes['slug'] = Str::slug($value);
    }

    // METHODS

    /**
     * Check if category is active.
     */
    public function isActive(): bool
    {
        return $this->is_active === true;
    }

    /**
     * Check if category has parent.
     */
    public function hasParent(): bool
    {
        return !is_null($this->parent_id);
    }

    /**
     * Check if category has children.
     */
    public function hasChildren(): bool
    {
        return $this->children()->count() > 0;
    }

    /**
     * Check if category has products.
     */
    public function hasProducts(): bool
    {
        return $this->products()->count() > 0;
    }

    /**
     * Get all descendants of the category.
     */
    public function getAllDescendants(): \Illuminate\Database\Eloquent\Collection
    {
        $descendants = collect();
        
        foreach ($this->children as $child) {
            $descendants->push($child);
            $descendants = $descendants->merge($child->getAllDescendants());
        }
        
        return $descendants;
    }

    /**
     * Get all ancestors of the category.
     */
    public function getAllAncestors(): \Illuminate\Database\Eloquent\Collection
    {
        $ancestors = collect();
        $current = $this->parent;
        
        while ($current) {
            $ancestors->prepend($current);
            $current = $current->parent;
        }
        
        return $ancestors;
    }

    /**
     * Get the category's depth level.
     */
    public function getDepthLevel(): int
    {
        return $this->getAllAncestors()->count();
    }

    /**
     * Get the category's breadcrumb.
     */
    public function getBreadcrumb(): array
    {
        $breadcrumb = [];
        $ancestors = $this->getAllAncestors();
        
        foreach ($ancestors as $ancestor) {
            $breadcrumb[] = [
                'name' => $ancestor->name,
                'slug' => $ancestor->slug,
                'url' => route('categories.display', $ancestor)
            ];
        }
        
        $breadcrumb[] = [
            'name' => $this->name,
            'slug' => $this->slug,
            'url' => route('categories.display', $this)
        ];
        
        return $breadcrumb;
    }

    // RELATIONSHIPS

    /**
     * Get all products for the category.
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get active products for the category.
     */
    public function activeProducts()
    {
        return $this->products()->active();
    }

    /**
     * Get the parent category.
     */
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Get the child categories.
     */
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * Get all child categories recursively.
     */
    public function allChildren()
    {
        return $this->children()->with('allChildren');
    }

    // SCOPES

    /**
     * Scope a query to only include active categories.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include root categories.
     */
    public function scopeRoot(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope a query to only include child categories.
     */
    public function scopeChildren(Builder $query): Builder
    {
        return $query->whereNotNull('parent_id');
    }

    /**
     * Scope a query to search categories by name or description.
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhere('slug', 'like', "%{$search}%");
        });
    }

    /**
     * Scope a query to order by sort order.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order', 'asc');
    }

    /**
     * Scope a query to include categories with products.
     */
    public function scopeWithProducts(Builder $query): Builder
    {
        return $query->whereHas('products');
    }

    /**
     * Scope a query to include categories with active products.
     */
    public function scopeWithActiveProducts(Builder $query): Builder
    {
        return $query->whereHas('products', function ($q) {
            $q->active();
        });
    }

    // STATIC METHODS

    /**
     * Get categories statistics.
     */
    public static function getStats(): array
    {
        return [
            'total' => static::count(),
            'active' => static::active()->count(),
            'root' => static::root()->count(),
            'children' => static::children()->count(),
            'with_products' => static::withProducts()->count(),
            'with_active_products' => static::withActiveProducts()->count()
        ];
    }

    /**
     * Get category tree structure.
     */
    public static function getTree(): \Illuminate\Database\Eloquent\Collection
    {
        return static::root()
                    ->with(['children', 'products'])
                    ->ordered()
                    ->get();
    }

    /**
     * Get categories for navigation.
     */
    public static function getForNavigation(): \Illuminate\Database\Eloquent\Collection
    {
        return static::active()
                    ->withActiveProducts()
                    ->with(['children' => function ($query) {
                        $query->active()->withActiveProducts();
                    }])
                    ->root()
                    ->ordered()
                    ->get();
    }

    /**
     * Create a new category with slug generation.
     */
    public static function createWithSlug(array $data): self
    {
        if (!isset($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }
        
        return static::create($data);
    }

    /**
     * Find category by slug.
     */
    public static function findBySlug(string $slug): ?self
    {
        return static::where('slug', $slug)->first();
    }
}
