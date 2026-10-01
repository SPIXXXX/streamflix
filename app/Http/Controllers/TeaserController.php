<?php

namespace App\Http\Controllers;

use App\Models\Teaser;

class TeaserController extends Controller
{
    public function index()
    {
        $teasers = Teaser::with('film')
            ->where(fn ($query) => $query->whereNull('release_date')->orWhereDate('release_date', '>=', today()))
            ->orderByRaw('release_date IS NULL')
            ->orderBy('release_date')
            ->paginate(12);

        return view('teasers.index', compact('teasers'));
    }
}
