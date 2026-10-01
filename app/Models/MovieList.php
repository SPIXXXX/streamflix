<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MovieList extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'title', 'description', 'is_public', 'is_official', 'is_featured', 'classification_type', 'classification', 'classifications'];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
            'is_official' => 'boolean',
            'is_featured' => 'boolean',
            'classifications' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function films(): BelongsToMany
    {
        return $this->belongsToMany(Film::class, 'list_films')->withTimestamps();
    }
}
