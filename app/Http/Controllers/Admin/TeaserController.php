<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Film;
use App\Models\Teaser;
use Illuminate\Http\Request;

class TeaserController extends Controller
{
    public function index()
    {
        $teasers = Teaser::with('film')->latest()->paginate(20);

        return view('admin.teasers.index', compact('teasers'));
    }

    public function create()
    {
        $films = Film::orderBy('title')->get();
        $teaser = null;

        return view('admin.teasers.create', compact('films', 'teaser'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'film_id' => 'required|exists:films,id',
            'video_url' => 'required|url',
            'description' => 'nullable|string',
            'release_date' => 'nullable|date',
        ]);

        Teaser::create($validated);

        return redirect()->route('admin.teasers.index')->with('status', 'Teaser added!');
    }

    public function edit(Teaser $teaser)
    {
        $films = Film::orderBy('title')->get();

        return view('admin.teasers.edit', compact('teaser', 'films'));
    }

    public function update(Request $request, Teaser $teaser)
    {
        $validated = $request->validate([
            'film_id' => 'required|exists:films,id',
            'video_url' => 'required|url',
            'description' => 'nullable|string',
            'release_date' => 'nullable|date',
        ]);

        $teaser->update($validated);

        return redirect()->route('admin.teasers.index')->with('status', 'Teaser updated!');
    }

    public function destroy(Teaser $teaser)
    {
        $teaser->delete();

        return back()->with('status', 'Teaser deleted.');
    }
}
