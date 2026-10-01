<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Teaser extends Model
{
    use HasFactory;

    protected $fillable = ['film_id', 'video_url', 'description', 'release_date'];

    protected function casts(): array
    {
        return [
            'release_date' => 'date',
        ];
    }

    public function film(): BelongsTo
    {
        return $this->belongsTo(Film::class);
    }
}