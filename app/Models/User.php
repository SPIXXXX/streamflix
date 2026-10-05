<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Services\PublicMediaStorage;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'status', 'avatar_path', 'is_featured', 'last_seen_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_featured' => 'boolean',
            'last_seen_at' => 'immutable_datetime',
            'last_logged_out_at' => 'immutable_datetime',
        ];
    }

    public function getAvatarUrlAttribute(): ?string
    {
        if (! $this->avatar_path) {
            return null;
        }

        if (filter_var($this->avatar_path, FILTER_VALIDATE_URL)) {
            return parse_url($this->avatar_path, PHP_URL_SCHEME) === 'https'
                ? $this->avatar_path
                : null;
        }

        return Storage::disk('public')->exists($this->avatar_path)
            ? Storage::disk('public')->url($this->avatar_path)
            : null;
    }

    protected static function booted(): void
    {
        static::deleting(function (User $user): void {
            app(PublicMediaStorage::class)->delete($user->avatar_path);
        });
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function reviewComments(): HasMany
    {
        return $this->hasMany(ReviewComment::class);
    }

    public function movieLists(): HasMany
    {
        return $this->hasMany(MovieList::class);
    }

    public function favoriteFilms(): BelongsToMany
    {
        return $this->belongsToMany(Film::class, 'film_favorites')->withTimestamps();
    }

    public function reviewReactions(): HasMany
    {
        return $this->hasMany(ReviewReaction::class);
    }

    public function scopePublicMembers(Builder $query): Builder
    {
        return $query->where('status', 'active')
            ->whereDoesntHave('roles', fn (Builder $roles) => $roles->where('name', 'admin'));
    }

    public function scopeWithMemberStats(Builder $query): Builder
    {
        $receivedReactions = DB::table('review_reactions')
            ->join('reviews', 'reviews.id', '=', 'review_reactions.review_id')
            ->selectRaw('COUNT(*)')
            ->whereColumn('reviews.user_id', 'users.id');

        return $query
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->withCount(['movieLists as public_lists_count' => fn (Builder $lists) => $lists->where('is_public', true)])
            ->selectSub($receivedReactions, 'received_reactions_count');
    }

    public function getIsOnlineAttribute(): bool
    {
        return $this->last_seen_at !== null
            && ($this->last_logged_out_at === null || $this->last_seen_at->greaterThan($this->last_logged_out_at))
            && $this->last_seen_at->greaterThanOrEqualTo(now()->subMinutes(2));
    }

    public function getActivityStatusAttribute(): string
    {
        if ($this->is_online) {
            return 'Online';
        }

        return $this->last_seen_at
            ? 'Active '.$this->last_seen_at->diffForHumans()
            : 'Never active';
    }

    public function scopeMostActive(Builder $query, int $limit = 6): Builder
    {
        return $query->publicMembers()->withMemberStats()
            ->has('reviews')
            ->orderByDesc('reviews_count')
            ->orderByDesc('received_reactions_count')
            ->orderBy('name')
            ->limit($limit);
    }

    public function scopePopular(Builder $query, int $limit = 6): Builder
    {
        return $query->publicMembers()->withMemberStats()
            ->where(function (Builder $members): void {
                $members->has('reviews')
                    ->orWhereHas('movieLists', fn (Builder $lists) => $lists->where('is_public', true));
            })
            ->orderByRaw('received_reactions_count + public_lists_count + reviews_count DESC')
            ->orderBy('name')
            ->limit($limit);
    }

    public function scopeFeatured(Builder $query, int $limit = 6): Builder
    {
        return $query->publicMembers()->withMemberStats()
            ->where('is_featured', true)
            ->orderBy('name')
            ->limit($limit);
    }
}
