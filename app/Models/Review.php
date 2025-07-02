<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class Review extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Review status constants.
     */
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    /**
     * Rating constants.
     */
    const MIN_RATING = 1;
    const MAX_RATING = 5;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'product_id',
        'order_id',
        'rating',
        'comment',
        'is_approved',
        'status',
        'title',
        'helpful_votes',
        'unhelpful_votes',
        'admin_response',
        'response_date'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'rating' => 'integer',
        'is_approved' => 'boolean',
        'helpful_votes' => 'integer',
        'unhelpful_votes' => 'integer',
        'response_date' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    /**
     * The attributes that should be mutated to dates.
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'response_date'
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($review) {
            if (empty($review->status)) {
                $review->status = self::STATUS_PENDING;
            }
            if (empty($review->is_approved)) {
                $review->is_approved = false;
            }
        });
    }

    // ACCESSORS

    /**
     * Get the review's rating stars.
     */
    public function getRatingStarsAttribute(): string
    {
        $stars = '';
        for ($i = 1; $i <= self::MAX_RATING; $i++) {
            if ($i <= $this->rating) {
                $stars .= '★';
            } else {
                $stars .= '☆';
            }
        }
        return $stars;
    }

    /**
     * Get the review's rating percentage.
     */
    public function getRatingPercentageAttribute(): float
    {
        return ($this->rating / self::MAX_RATING) * 100;
    }

    /**
     * Get the review's status text.
     */
    public function getStatusTextAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'Pendiente',
            self::STATUS_APPROVED => 'Aprobado',
            self::STATUS_REJECTED => 'Rechazado',
            default => 'Desconocido'
        };
    }

    /**
     * Get the review's status badge class.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'badge-warning',
            self::STATUS_APPROVED => 'badge-success',
            self::STATUS_REJECTED => 'badge-danger',
            default => 'badge-secondary'
        };
    }

    /**
     * Get the review's helpful score.
     */
    public function getHelpfulScoreAttribute(): int
    {
        return $this->helpful_votes - $this->unhelpful_votes;
    }

    /**
     * Get the review's total votes.
     */
    public function getTotalVotesAttribute(): int
    {
        return $this->helpful_votes + $this->unhelpful_votes;
    }

    /**
     * Get the review's helpful percentage.
     */
    public function getHelpfulPercentageAttribute(): float
    {
        if ($this->total_votes === 0) {
            return 0;
        }
        
        return ($this->helpful_votes / $this->total_votes) * 100;
    }

    // MUTATORS

    /**
     * Set the review's rating.
     */
    public function setRatingAttribute($value): void
    {
        $this->attributes['rating'] = max(self::MIN_RATING, min(self::MAX_RATING, (int) $value));
    }

    /**
     * Set the review's comment.
     */
    public function setCommentAttribute($value): void
    {
        $this->attributes['comment'] = trim($value);
    }

    /**
     * Set the review's title.
     */
    public function setTitleAttribute($value): void
    {
        $this->attributes['title'] = ucfirst(trim($value));
    }

    // METHODS

    /**
     * Check if review is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check if review is pending.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if review is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Check if review has admin response.
     */
    public function hasAdminResponse(): bool
    {
        return !empty($this->admin_response);
    }

    /**
     * Check if review can be voted on.
     */
    public function canBeVoted(): bool
    {
        return $this->isApproved() && auth()->check() && auth()->id() !== $this->user_id;
    }

    /**
     * Approve the review.
     */
    public function approve(): void
    {
        $this->update([
            'status' => self::STATUS_APPROVED,
            'is_approved' => true
        ]);
    }

    /**
     * Reject the review.
     */
    public function reject(string $reason = null): void
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
            'is_approved' => false
        ]);
    }

    /**
     * Add admin response.
     */
    public function addAdminResponse(string $response): void
    {
        $this->update([
            'admin_response' => $response,
            'response_date' => now()
        ]);
    }

    /**
     * Vote on review helpfulness.
     */
    public function vote(bool $isHelpful): void
    {
        if ($isHelpful) {
            $this->increment('helpful_votes');
        } else {
            $this->increment('unhelpful_votes');
        }
    }

    /**
     * Check if user can review this product.
     */
    public static function canUserReview(int $userId, int $productId): bool
    {
        return !static::where('user_id', $userId)
                     ->where('product_id', $productId)
                     ->exists();
    }

    // RELATIONSHIPS

    /**
     * Get the user that owns the review.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the product that owns the review.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the order that owns the review.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // SCOPES

    /**
     * Scope a query to only include approved reviews.
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Scope a query to only include pending reviews.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope a query to only include rejected reviews.
     */
    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    /**
     * Scope a query to only include reviews for a specific product.
     */
    public function scopeForProduct(Builder $query, int $productId): Builder
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope a query to only include reviews by a specific user.
     */
    public function scopeByUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to filter by rating.
     */
    public function scopeByRating(Builder $query, int $rating): Builder
    {
        return $query->where('rating', $rating);
    }

    /**
     * Scope a query to filter by rating range.
     */
    public function scopeRatingRange(Builder $query, int $min, int $max): Builder
    {
        return $query->whereBetween('rating', [$min, $max]);
    }

    /**
     * Scope a query to search reviews by comment or title.
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('comment', 'like', "%{$search}%")
              ->orWhere('title', 'like', "%{$search}%");
        });
    }

    /**
     * Scope a query to order by most helpful.
     */
    public function scopeMostHelpful(Builder $query): Builder
    {
        return $query->orderBy('helpful_votes', 'desc');
    }

    /**
     * Scope a query to order by highest rating.
     */
    public function scopeHighestRating(Builder $query): Builder
    {
        return $query->orderBy('rating', 'desc');
    }

    /**
     * Scope a query to order by lowest rating.
     */
    public function scopeLowestRating(Builder $query): Builder
    {
        return $query->orderBy('rating', 'asc');
    }

    /**
     * Scope a query to order by most recent.
     */
    public function scopeRecent(Builder $query): Builder
    {
        return $query->orderBy('created_at', 'desc');
    }

    // STATIC METHODS

    /**
     * Get reviews statistics.
     */
    public static function getStats(): array
    {
        return [
            'total' => static::count(),
            'approved' => static::approved()->count(),
            'pending' => static::pending()->count(),
            'rejected' => static::rejected()->count(),
            'average_rating' => static::approved()->avg('rating'),
            'total_helpful_votes' => static::sum('helpful_votes'),
            'total_unhelpful_votes' => static::sum('unhelpful_votes')
        ];
    }

    /**
     * Get rating distribution.
     */
    public static function getRatingDistribution(): array
    {
        $distribution = [];
        
        for ($i = self::MIN_RATING; $i <= self::MAX_RATING; $i++) {
            $distribution[$i] = static::approved()->byRating($i)->count();
        }
        
        return $distribution;
    }

    /**
     * Get recent reviews.
     */
    public static function getRecentReviews(int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return static::with(['user', 'product'])
                    ->approved()
                    ->recent()
                    ->limit($limit)
                    ->get();
    }

    /**
     * Get top rated products.
     */
    public static function getTopRatedProducts(int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return static::with('product')
                    ->approved()
                    ->select('product_id', DB::raw('AVG(rating) as average_rating'), DB::raw('COUNT(*) as review_count'))
                    ->groupBy('product_id')
                    ->having('review_count', '>=', 3)
                    ->orderBy('average_rating', 'desc')
                    ->limit($limit)
                    ->get();
    }

    /**
     * Create review with validation.
     */
    public static function createReview(array $data): self
    {
        // Validate that user hasn't already reviewed this product
        if (static::where('user_id', $data['user_id'])
                  ->where('product_id', $data['product_id'])
                  ->exists()) {
            throw new \Exception('Ya has reseñado este producto');
        }

        return static::create($data);
    }
} 