<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Film;
use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalUsers = User::count();
        $totalFilms = Film::count();
        $reviewsThisWeek = Review::where('created_at', '>=', now()->subWeek())->count();
        $activeMembers = User::where('status', 'active')->count();

        $popularGenres = Film::select('genre', DB::raw('count(*) as total'))
            ->whereNotNull('genre')
            ->groupBy('genre')
            ->orderByDesc('total')
            ->take(8)
            ->get();

        $popularDecades = Film::selectRaw("CONCAT(FLOOR(release_year / 10) * 10, 's') as decade, count(*) as total")
            ->whereNotNull('release_year')
            ->groupBy('decade')
            ->orderBy('decade')
            ->get();

        $weeklyActivity = Review::selectRaw('DATE(created_at) as day, count(*) as total')
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $monthlyActivity = Review::selectRaw('DATE(created_at) as day, count(*) as total')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $yearlyActivity = Review::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, count(*) as total")
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $recentActivity = Review::with(['user', 'film'])->latest()->take(8)->get();

        return view('admin.dashboard', compact(
            'totalUsers', 'totalFilms', 'reviewsThisWeek', 'activeMembers',
            'popularGenres', 'popularDecades',
            'weeklyActivity', 'monthlyActivity', 'yearlyActivity',
            'recentActivity'
        ));
    }
}
