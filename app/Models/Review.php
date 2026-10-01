<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Review extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'film_id', 'rating', 'comment'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function film(): BelongsTo
    {
        return $this->belongsTo(Film::class);
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(ReviewReaction::class);
    }

    public function agreeCount(): int
    {
        return $this->reactions()->where('reaction', 'agree')->count();
    }

    public function disagreeCount(): int
    {
        return $this->reactions()->where('reaction', 'disagree')->count();
    }
}