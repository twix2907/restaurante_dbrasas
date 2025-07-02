<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class InventoryLog extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Action constants.
     */
    const ACTION_ADD = 'add';
    const ACTION_REMOVE = 'remove';
    const ACTION_ADJUST = 'adjust';
    const ACTION_RETURN = 'return';
    const ACTION_DAMAGED = 'damaged';
    const ACTION_EXPIRED = 'expired';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'product_id',
        'action',
        'quantity',
        'previous_stock',
        'new_stock',
        'user_id',
        'notes',
        'reference_number',
        'cost_per_unit',
        'total_cost',
        'location',
        'expiry_date',
        'batch_number'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'quantity' => 'integer',
        'previous_stock' => 'integer',
        'new_stock' => 'integer',
        'cost_per_unit' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'expiry_date' => 'date',
        'deleted_at' => 'datetime'
    ];

    /**
     * The attributes that should be mutated to dates.
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'expiry_date'
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($log) {
            if (empty($log->reference_number)) {
                $log->reference_number = static::generateReferenceNumber();
            }
        });
    }

    // ACCESSORS

    /**
     * Get the log's action text.
     */
    public function getActionTextAttribute(): string
    {
        return match($this->action) {
            self::ACTION_ADD => 'Agregado',
            self::ACTION_REMOVE => 'Removido',
            self::ACTION_ADJUST => 'Ajustado',
            self::ACTION_RETURN => 'Retornado',
            self::ACTION_DAMAGED => 'Dañado',
            self::ACTION_EXPIRED => 'Expirado',
            default => 'Desconocido'
        };
    }

    /**
     * Get the log's action badge class.
     */
    public function getActionBadgeClassAttribute(): string
    {
        return match($this->action) {
            self::ACTION_ADD => 'badge-success',
            self::ACTION_REMOVE => 'badge-danger',
            self::ACTION_ADJUST => 'badge-warning',
            self::ACTION_RETURN => 'badge-info',
            self::ACTION_DAMAGED => 'badge-dark',
            self::ACTION_EXPIRED => 'badge-secondary',
            default => 'badge-secondary'
        };
    }

    /**
     * Get the log's formatted cost per unit.
     */
    public function getFormattedCostPerUnitAttribute(): string
    {
        if ($this->cost_per_unit) {
            return '$' . number_format($this->cost_per_unit, 2);
        }
        
        return 'N/A';
    }

    /**
     * Get the log's formatted total cost.
     */
    public function getFormattedTotalCostAttribute(): string
    {
        if ($this->total_cost) {
            return '$' . number_format($this->total_cost, 2);
        }
        
        return 'N/A';
    }

    /**
     * Get the log's stock change.
     */
    public function getStockChangeAttribute(): int
    {
        return $this->new_stock - $this->previous_stock;
    }

    /**
     * Get the log's stock change text.
     */
    public function getStockChangeTextAttribute(): string
    {
        $change = $this->stock_change;
        
        if ($change > 0) {
            return '+' . $change;
        }
        
        return (string) $change;
    }

    /**
     * Get the log's stock change color.
     */
    public function getStockChangeColorAttribute(): string
    {
        $change = $this->stock_change;
        
        if ($change > 0) {
            return 'text-success';
        } elseif ($change < 0) {
            return 'text-danger';
        }
        
        return 'text-muted';
    }

    // MUTATORS

    /**
     * Set the log's quantity.
     */
    public function setQuantityAttribute($value): void
    {
        $this->attributes['quantity'] = abs((int) $value);
    }

    /**
     * Set the log's cost per unit.
     */
    public function setCostPerUnitAttribute($value): void
    {
        $this->attributes['cost_per_unit'] = (float) $value;
        
        if ($this->cost_per_unit && $this->quantity) {
            $this->attributes['total_cost'] = $this->cost_per_unit * $this->quantity;
        }
    }

    /**
     * Set the log's notes.
     */
    public function setNotesAttribute($value): void
    {
        $this->attributes['notes'] = trim($value);
    }

    // METHODS

    /**
     * Check if log is an addition.
     */
    public function isAddition(): bool
    {
        return $this->action === self::ACTION_ADD;
    }

    /**
     * Check if log is a removal.
     */
    public function isRemoval(): bool
    {
        return $this->action === self::ACTION_REMOVE;
    }

    /**
     * Check if log is an adjustment.
     */
    public function isAdjustment(): bool
    {
        return $this->action === self::ACTION_ADJUST;
    }

    /**
     * Check if log is a return.
     */
    public function isReturn(): bool
    {
        return $this->action === self::ACTION_RETURN;
    }

    /**
     * Check if log is for damaged items.
     */
    public function isDamaged(): bool
    {
        return $this->action === self::ACTION_DAMAGED;
    }

    /**
     * Check if log is for expired items.
     */
    public function isExpired(): bool
    {
        return $this->hasExpiryDate() && $this->expiry_date->isPast();
    }

    /**
     * Check if log has cost information.
     */
    public function hasCostInfo(): bool
    {
        return !is_null($this->cost_per_unit) && !is_null($this->total_cost);
    }

    /**
     * Check if log has expiry date.
     */
    public function hasExpiryDate(): bool
    {
        return !is_null($this->expiry_date);
    }

    /**
     * Get log summary.
     */
    public function getSummary(): string
    {
        $summary = "{$this->action_text}: {$this->quantity} unidades";
        
        if ($this->hasCostInfo()) {
            $summary .= " (Costo: {$this->formatted_total_cost})";
        }
        
        if ($this->notes) {
            $summary .= " - {$this->notes}";
        }
        
        return $summary;
    }

    // RELATIONSHIPS

    /**
     * Get the product that owns the log.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user that created the log.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // SCOPES

    /**
     * Scope a query to only include additions.
     */
    public function scopeAdditions(Builder $query): Builder
    {
        return $query->where('action', self::ACTION_ADD);
    }

    /**
     * Scope a query to only include removals.
     */
    public function scopeRemovals(Builder $query): Builder
    {
        return $query->where('action', self::ACTION_REMOVE);
    }

    /**
     * Scope a query to only include adjustments.
     */
    public function scopeAdjustments(Builder $query): Builder
    {
        return $query->where('action', self::ACTION_ADJUST);
    }

    /**
     * Scope a query to only include returns.
     */
    public function scopeReturns(Builder $query): Builder
    {
        return $query->where('action', self::ACTION_RETURN);
    }

    /**
     * Scope a query to only include damaged items.
     */
    public function scopeDamaged(Builder $query): Builder
    {
        return $query->where('action', self::ACTION_DAMAGED);
    }

    /**
     * Scope a query to only include expired items.
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('action', self::ACTION_EXPIRED);
    }

    /**
     * Scope a query to only include logs for a specific product.
     */
    public function scopeForProduct(Builder $query, int $productId): Builder
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope a query to only include logs by a specific user.
     */
    public function scopeByUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to search logs by notes or reference number.
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('notes', 'like', "%{$search}%")
              ->orWhere('reference_number', 'like', "%{$search}%")
              ->orWhere('batch_number', 'like', "%{$search}%");
        });
    }

    /**
     * Scope a query to filter by date range.
     */
    public function scopeDateRange(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope a query to filter by quantity range.
     */
    public function scopeQuantityRange(Builder $query, int $min, int $max): Builder
    {
        return $query->whereBetween('quantity', [$min, $max]);
    }

    /**
     * Scope a query to order by most recent.
     */
    public function scopeRecent(Builder $query): Builder
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Scope a query to order by quantity.
     */
    public function scopeByQuantity(Builder $query, string $direction = 'desc'): Builder
    {
        return $query->orderBy('quantity', $direction);
    }

    // STATIC METHODS

    /**
     * Generate unique reference number.
     */
    public static function generateReferenceNumber(): string
    {
        $prefix = 'INV';
        $date = now()->format('Ymd');
        $random = strtoupper(substr(md5(uniqid()), 0, 6));
        
        return "{$prefix}-{$date}-{$random}";
    }

    /**
     * Get inventory logs statistics.
     */
    public static function getStats(): array
    {
        return [
            'total' => static::count(),
            'additions' => static::additions()->count(),
            'removals' => static::removals()->count(),
            'adjustments' => static::adjustments()->count(),
            'returns' => static::returns()->count(),
            'damaged' => static::damaged()->count(),
            'expired' => static::expired()->count(),
            'total_quantity_added' => static::additions()->sum('quantity'),
            'total_quantity_removed' => static::removals()->sum('quantity'),
            'total_cost' => static::sum('total_cost')
        ];
    }

    /**
     * Get recent inventory logs.
     */
    public static function getRecentLogs(int $limit = 20): \Illuminate\Database\Eloquent\Collection
    {
        return static::with(['product', 'user'])
                    ->recent()
                    ->limit($limit)
                    ->get();
    }

    /**
     * Get inventory logs by product.
     */
    public static function getByProduct(int $productId, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return static::with(['user'])
                    ->forProduct($productId)
                    ->recent()
                    ->limit($limit)
                    ->get();
    }

    /**
     * Get inventory movement summary.
     */
    public static function getMovementSummary(string $startDate = null, string $endDate = null): array
    {
        $query = static::query();
        
        if ($startDate && $endDate) {
            $query->dateRange($startDate, $endDate);
        }
        
        return [
            'additions' => $query->additions()->sum('quantity'),
            'removals' => $query->removals()->sum('quantity'),
            'adjustments' => $query->adjustments()->sum('quantity'),
            'returns' => $query->returns()->sum('quantity'),
            'damaged' => $query->damaged()->sum('quantity'),
            'expired' => $query->expired()->sum('quantity'),
            'net_change' => $query->additions()->sum('quantity') - $query->removals()->sum('quantity')
        ];
    }

    /**
     * Create inventory log with automatic stock calculation.
     */
    public static function createLog(array $data): self
    {
        $product = Product::findOrFail($data['product_id']);
        $previousStock = $product->stock;
        
        // Calculate new stock based on action
        $quantity = abs($data['quantity']);
        
        switch ($data['action']) {
            case self::ACTION_ADD:
            case self::ACTION_RETURN:
                $newStock = $previousStock + $quantity;
                break;
            case self::ACTION_REMOVE:
            case self::ACTION_DAMAGED:
            case self::ACTION_EXPIRED:
                $newStock = max(0, $previousStock - $quantity);
                break;
            case self::ACTION_ADJUST:
                $newStock = $quantity;
                break;
            default:
                $newStock = $previousStock;
        }
        
        // Update product stock
        $product->update(['stock' => $newStock]);
        
        // Create log
        return static::create(array_merge($data, [
            'previous_stock' => $previousStock,
            'new_stock' => $newStock
        ]));
    }
} 