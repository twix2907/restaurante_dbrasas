<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class Slider extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'title',
        'subtitle',
        'description',
        'image',
        'link',
        'button_text',
        'button_url',
        'is_active',
        'sort_order',
        'start_date',
        'end_date',
        'target_blank',
        'animation_type',
        'text_color',
        'overlay_color',
        'overlay_opacity'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'target_blank' => 'boolean',
        'overlay_opacity' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'deleted_at' => 'datetime'
    ];

    /**
     * The attributes that should be mutated to dates.
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'start_date',
        'end_date'
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($slider) {
            if (empty($slider->sort_order)) {
                $slider->sort_order = static::max('sort_order') + 1;
            }
        });
    }

    // ACCESSORS

    /**
     * Get the slider's image URL.
     */
    public function getImageUrlAttribute(): string
    {
        if ($this->image) {
            return asset('storage/' . $this->image);
        }
        
        return asset('images/slider-placeholder.jpg');
    }

    /**
     * Get the slider's thumbnail URL.
     */
    public function getThumbnailUrlAttribute(): string
    {
        if ($this->image) {
            $pathInfo = pathinfo($this->image);
            $thumbnailPath = $pathInfo['dirname'] . '/thumb_' . $pathInfo['basename'];
            return asset('storage/' . $thumbnailPath);
        }
        
        return asset('images/slider-placeholder-thumb.jpg');
    }

    /**
     * Get the slider's status text.
     */
    public function getStatusTextAttribute(): string
    {
        if (!$this->is_active) {
            return 'Inactivo';
        }
        
        if ($this->isExpired()) {
            return 'Expirado';
        }
        
        if ($this->isScheduled()) {
            return 'Programado';
        }
        
        return 'Activo';
    }

    /**
     * Get the slider's status badge class.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        if (!$this->is_active) {
            return 'badge-secondary';
        }
        
        if ($this->isExpired()) {
            return 'badge-danger';
        }
        
        if ($this->isScheduled()) {
            return 'badge-warning';
        }
        
        return 'badge-success';
    }

    /**
     * Get the slider's overlay style.
     */
    public function getOverlayStyleAttribute(): string
    {
        if (!$this->overlay_color) {
            return '';
        }
        
        $opacity = $this->overlay_opacity ?? 0.5;
        return "background-color: {$this->overlay_color}; opacity: {$opacity};";
    }

    /**
     * Get the slider's text style.
     */
    public function getTextStyleAttribute(): string
    {
        if (!$this->text_color) {
            return '';
        }
        
        return "color: {$this->text_color};";
    }

    /**
     * Get the slider's animation class.
     */
    public function getAnimationClassAttribute(): string
    {
        return $this->animation_type ?? 'fadeIn';
    }

    // MUTATORS

    /**
     * Set the slider's title.
     */
    public function setTitleAttribute($value): void
    {
        $this->attributes['title'] = ucfirst(trim($value));
    }

    /**
     * Set the slider's subtitle.
     */
    public function setSubtitleAttribute($value): void
    {
        $this->attributes['subtitle'] = ucfirst(trim($value));
    }

    /**
     * Set the slider's description.
     */
    public function setDescriptionAttribute($value): void
    {
        $this->attributes['description'] = trim($value);
    }

    /**
     * Set the slider's button text.
     */
    public function setButtonTextAttribute($value): void
    {
        $this->attributes['button_text'] = ucfirst(trim($value));
    }

    // METHODS

    /**
     * Check if slider is active.
     */
    public function isActive(): bool
    {
        return $this->is_active === true;
    }

    /**
     * Check if slider is scheduled.
     */
    public function isScheduled(): bool
    {
        return $this->start_date && $this->start_date->isFuture();
    }

    /**
     * Check if slider is expired.
     */
    public function isExpired(): bool
    {
        return $this->end_date && $this->end_date->isPast();
    }

    /**
     * Check if slider is currently active.
     */
    public function isCurrentlyActive(): bool
    {
        if (!$this->isActive()) {
            return false;
        }
        
        if ($this->isExpired()) {
            return false;
        }
        
        if ($this->isScheduled()) {
            return false;
        }
        
        return true;
    }

    /**
     * Check if slider has button.
     */
    public function hasButton(): bool
    {
        return !empty($this->button_text) && !empty($this->button_url);
    }

    /**
     * Check if slider has link.
     */
    public function hasLink(): bool
    {
        return !empty($this->link);
    }

    /**
     * Check if slider has overlay.
     */
    public function hasOverlay(): bool
    {
        return !empty($this->overlay_color);
    }

    /**
     * Get slider duration in days.
     */
    public function getDurationInDays(): ?int
    {
        if (!$this->start_date || !$this->end_date) {
            return null;
        }
        
        return $this->start_date->diffInDays($this->end_date);
    }

    /**
     * Get slider remaining days.
     */
    public function getRemainingDays(): ?int
    {
        if (!$this->end_date) {
            return null;
        }
        
        return now()->diffInDays($this->end_date, false);
    }

    /**
     * Get slider days until start.
     */
    public function getDaysUntilStart(): ?int
    {
        if (!$this->start_date) {
            return null;
        }
        
        return now()->diffInDays($this->start_date, false);
    }

    // SCOPES

    /**
     * Scope a query to only include active sliders.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include currently active sliders.
     */
    public function scopeCurrentlyActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
                    ->where(function ($q) {
                        $q->whereNull('start_date')
                          ->orWhere('start_date', '<=', now());
                    })
                    ->where(function ($q) {
                        $q->whereNull('end_date')
                          ->orWhere('end_date', '>=', now());
                    });
    }

    /**
     * Scope a query to only include scheduled sliders.
     */
    public function scopeScheduled(Builder $query): Builder
    {
        return $query->where('is_active', true)
                    ->where('start_date', '>', now());
    }

    /**
     * Scope a query to only include expired sliders.
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('is_active', true)
                    ->where('end_date', '<', now());
    }

    /**
     * Scope a query to search sliders by title or description.
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('subtitle', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
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
     * Scope a query to order by most recent.
     */
    public function scopeRecent(Builder $query): Builder
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Scope a query to filter by date range.
     */
    public function scopeDateRange(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('start_date', [$startDate, $endDate])
              ->orWhereBetween('end_date', [$startDate, $endDate])
              ->orWhere(function ($subQ) use ($startDate, $endDate) {
                  $subQ->where('start_date', '<=', $startDate)
                       ->where('end_date', '>=', $endDate);
              });
        });
    }

    // STATIC METHODS

    /**
     * Get sliders statistics.
     */
    public static function getStats(): array
    {
        return [
            'total' => static::count(),
            'active' => static::active()->count(),
            'currently_active' => static::currentlyActive()->count(),
            'scheduled' => static::scheduled()->count(),
            'expired' => static::expired()->count()
        ];
    }

    /**
     * Get active sliders for frontend.
     */
    public static function getActiveSliders(): \Illuminate\Database\Eloquent\Collection
    {
        return static::currentlyActive()
                    ->ordered()
                    ->get();
    }

    /**
     * Get sliders for admin dashboard.
     */
    public static function getForDashboard(): \Illuminate\Database\Eloquent\Collection
    {
        return static::withCount('views')
                    ->recent()
                    ->limit(10)
                    ->get();
    }

    /**
     * Create slider with default values.
     */
    public static function createSlider(array $data): self
    {
        $defaults = [
            'is_active' => true,
            'sort_order' => static::max('sort_order') + 1,
            'animation_type' => 'fadeIn',
            'overlay_opacity' => 0.5
        ];
        
        return static::create(array_merge($defaults, $data));
    }

    /**
     * Get sliders that need attention.
     */
    public static function getSlidersNeedingAttention(): \Illuminate\Database\Eloquent\Collection
    {
        return static::where(function ($query) {
            $query->where('is_active', true)
                  ->where(function ($q) {
                      $q->where('end_date', '<=', now()->addDays(7))
                        ->orWhere('start_date', '<=', now()->addDays(1));
                  });
        })->get();
    }
}
