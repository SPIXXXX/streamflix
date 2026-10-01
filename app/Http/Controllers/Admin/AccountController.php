<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

class AccountController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:active,suspended,banned'],
            'role' => ['nullable', 'in:admin,client'],
        ]);

        $clientAccounts = User::query()->whereDoesntHave('roles', fn ($roles) => $roles->where('name', 'admin'));
        $stats = [
            'total' => (clone $clientAccounts)->count(),
            'active' => (clone $clientAccounts)->where('status', 'active')->count(),
            'suspended' => (clone $clientAccounts)->where('status', 'suspended')->count(),
            'recent' => (clone $clientAccounts)->where('created_at', '>=', now()->subDays(30))->count(),
        ];

        $users = User::query()
            ->with('roles')
            ->withCount([
                'reviews',
                'favoriteFilms',
                'movieLists as public_lists_count' => fn ($lists) => $lists->where('is_public', true),
            ])
            ->withAvg('reviews', 'rating')
            ->when($filters['q'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when(($filters['role'] ?? null) === 'admin', fn ($query) => $query->whereHas('roles', fn ($roles) => $roles->where('name', 'admin')))
            ->when(($filters['role'] ?? null) === 'client', fn ($query) => $query->whereDoesntHave('roles', fn ($roles) => $roles->where('name', 'admin')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.accounts.index', compact('users', 'stats', 'filters'));
    }

    public function show(Request $request, User $user): View
    {
        $user->load('roles')->loadCount([
            'reviews',
            'favoriteFilms',
            'movieLists as public_lists_count' => fn ($lists) => $lists->where('is_public', true),
        ])->loadAvg('reviews', 'rating');

        $reviews = $user->reviews()
            ->with('film')
            ->withCount([
                'reactions as agree_count' => fn ($query) => $query->where('reaction', 'agree'),
                'reactions as disagree_count' => fn ($query) => $query->where('reaction', 'disagree'),
            ])
            ->latest()
            ->paginate(10, ['*'], 'reviews_page')
            ->withQueryString()
            ->appends(['tab' => 'reviews']);

        // Private lists and activity derived from them are excluded, even for admins.
        $publicLists = $user->movieLists()
            ->where('is_public', true)
            ->withCount('films')
            ->with(['films' => fn ($query) => $query
                ->select('films.id', 'films.title', 'films.poster_path')
                ->orderBy('list_films.created_at', 'desc')
                ->limit(4)])
            ->latest()
            ->paginate(8, ['*'], 'lists_page')
            ->withQueryString()
            ->appends(['tab' => 'lists']);

        $activities = $this->activityQuery($user)
            ->orderByDesc('occurred_at')
            ->paginate(15, ['*'], 'activity_page')
            ->withQueryString()
            ->appends(['tab' => 'activity']);

        return view('admin.accounts.show', compact('user', 'reviews', 'publicLists', 'activities'));
    }

    private function activityQuery(User $user): QueryBuilder
    {
        $reviews = DB::table('reviews')
            ->join('films', 'films.id', '=', 'reviews.film_id')
            ->where('reviews.user_id', $user->id)
            ->selectRaw("'review' as type, reviews.id as event_id, films.title as subject, CAST(reviews.rating AS CHAR) as detail, NULL as context, reviews.created_at as occurred_at");

        $lists = DB::table('movie_lists')
            ->where('movie_lists.user_id', $user->id)
            ->where('movie_lists.is_public', true)
            ->selectRaw("'list' as type, movie_lists.id as event_id, movie_lists.title as subject, NULL as detail, NULL as context, movie_lists.created_at as occurred_at");

        $favorites = DB::table('film_favorites')
            ->join('films', 'films.id', '=', 'film_favorites.film_id')
            ->where('film_favorites.user_id', $user->id)
            ->selectRaw("'favorite' as type, film_favorites.id as event_id, films.title as subject, NULL as detail, NULL as context, film_favorites.created_at as occurred_at");

        $reactions = DB::table('review_reactions')
            ->join('reviews', 'reviews.id', '=', 'review_reactions.review_id')
            ->join('films', 'films.id', '=', 'reviews.film_id')
            ->where('reviews.user_id', $user->id)
            ->selectRaw("'reaction' as type, review_reactions.id as event_id, films.title as subject, review_reactions.reaction as detail, NULL as context, review_reactions.created_at as occurred_at");

        $listFilmAdds = DB::table('list_films')
            ->join('movie_lists', 'movie_lists.id', '=', 'list_films.movie_list_id')
            ->join('films', 'films.id', '=', 'list_films.film_id')
            ->where('movie_lists.user_id', $user->id)
            ->where('movie_lists.is_public', true)
            ->selectRaw("'list_add' as type, list_films.id as event_id, films.title as subject, NULL as detail, movie_lists.title as context, list_films.created_at as occurred_at");

        $activity = $reviews->unionAll($lists)
            ->unionAll($favorites)
            ->unionAll($reactions)
            ->unionAll($listFilmAdds);

        return DB::query()->fromSub($activity, 'account_activity');
    }

    public function feature(Request $request, User $user): RedirectResponse
    {
        abort_if($user->hasRole('admin'), 403, 'Cannot feature an admin account.');
        $validated = $request->validate(['is_featured' => 'required|boolean']);
        $user->update(['is_featured' => (bool) $validated['is_featured']]);

        return back()->with('status', $user->is_featured
            ? "{$user->name} is now a featured member."
            : "{$user->name} is no longer featured.");
    }

    public function suspend(User $user): RedirectResponse
    {
        abort_if($user->hasRole('admin'), 403, 'Cannot suspend an admin account.');
        abort_unless($user->status === 'active', 409, 'Only active accounts can be suspended.');
        try {
            $user->update(['status' => 'suspended']);
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('error', 'Unable to suspend account. Please try again.');
        }

        return back()->with('status', 'Account suspended successfully.');
    }

    public function ban(User $user): RedirectResponse
    {
        abort_if($user->hasRole('admin'), 403, 'Cannot ban an admin account.');
        abort_unless($user->status === 'active', 409, 'Only active accounts can be banned.');
        try {
            $user->update(['status' => 'banned']);
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('error', 'Unable to ban account. Please try again.');
        }

        return back()->with('status', 'Account banned successfully.');
    }

    public function reactivate(User $user): RedirectResponse
    {
        abort_if($user->hasRole('admin'), 403, 'Cannot reactivate an admin account.');
        abort_unless(in_array($user->status, ['suspended', 'banned'], true), 409, 'This account is already active.');
        $wasSuspended = $user->status === 'suspended';

        try {
            $user->update(['status' => 'active']);
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('error', 'Unable to restore account. Please try again.');
        }

        return back()->with('status', $wasSuspended
            ? 'Account unsuspended successfully.'
            : 'Account reactivated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_if($user->hasRole('admin'), 403, 'Cannot delete an admin account.');

        try {
            $user->delete();
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('error', 'Unable to delete account. Please try again.');
        }

        return redirect()->route('admin.accounts.index')->with('status', 'Account deleted successfully.');
    }
}
