<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TmdbKeyword extends Model
{
    protected $fillable = ['tmdb_id', 'name'];

    public function films(): BelongsToMany
    {
        return $this->belongsToMany(Film::class, 'film_tmdb_keyword');
    }
}
