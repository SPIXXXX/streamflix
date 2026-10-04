<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MemberController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => 'nullable|string|max:100',
            'sort' => 'nullable|in:all,active,popular,featured',
        ]);
        $sort = $filters['sort'] ?? 'all';

        $mostActive = User::mostActive()->get();
        $featured = User::featured()->get();
        $popular = User::popular()->get();

        $membersQuery = User::publicMembers()->withMemberStats()
            ->when($filters['q'] ?? null, function (Builder $query, string $search): void {
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($sort === 'featured', fn (Builder $query) => $query->where('is_featured', true));

        match ($sort) {
            'active' => $membersQuery->orderByDesc('reviews_count')->orderByDesc('received_reactions_count'),
            'popular' => $membersQuery->orderByRaw('received_reactions_count + public_lists_count + reviews_count DESC'),
            default => $membersQuery->orderBy('name'),
        };

        $members = $membersQuery->paginate(12)->withQueryString();

        return view('members.index', compact('mostActive', 'featured', 'popular', 'members', 'filters', 'sort'));
    }

    public function show(User $user): View
    {
        $user = User::publicMembers()->withMemberStats()->whereKey($user->getKey())->firstOrFail();
        $reviews = Review::query()
            ->where('user_id', $user->id)
            ->with(['film', 'comments.user'])
            ->withCount([
                'reactions as agree_count' => fn (Builder $reactions) => $reactions->where('reaction', 'agree'),
                'reactions as disagree_count' => fn (Builder $reactions) => $reactions->where('reaction', 'disagree'),
            ])
            ->latest()
            ->paginate(10, ['*'], 'reviews_page')
            ->withQueryString()
            ->appends(['tab' => 'reviews']);
        $publicLists = $user->movieLists()
            ->where('is_public', true)
            ->withCount('films')
            ->with(['films' => fn ($films) => $films
                ->select('films.id', 'films.title', 'films.poster_path')
                ->orderBy('list_films.created_at', 'desc')
                ->limit(4)])
            ->latest()
            ->paginate(8, ['*'], 'lists_page')
            ->withQueryString()
            ->appends(['tab' => 'lists']);

        return view('members.show', compact('user', 'reviews', 'publicLists'));
    }
}
