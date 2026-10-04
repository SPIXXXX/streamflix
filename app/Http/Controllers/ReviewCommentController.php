<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\ReviewComment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewCommentController extends Controller
{
    public function store(Request $request, Review $review): RedirectResponse
    {
        $validated = $request->validate([
            'body' => 'required|string|max:2000',
        ]);

        $review->comments()->create([
            'user_id' => $request->user()->id,
            'body' => $validated['body'],
        ]);

        return back()
            ->withFragment('review-'.$review->id)
            ->with('open_review_comments', $review->id)
            ->with('status', 'Your comment was posted.');
    }

    public function update(Request $request, ReviewComment $reviewComment): RedirectResponse
    {
        abort_unless($reviewComment->user_id === $request->user()->id, 403);

        $validated = $request->validate([
            'body' => 'required|string|max:2000',
        ]);

        $reviewComment->update($validated);

        return back()
            ->withFragment('review-'.$reviewComment->review_id)
            ->with('open_review_comments', $reviewComment->review_id)
            ->with('status', 'Your comment was updated.');
    }

    public function destroy(Request $request, ReviewComment $reviewComment): RedirectResponse
    {
        abort_unless($reviewComment->user_id === $request->user()->id, 403);

        $reviewId = $reviewComment->review_id;
        $reviewComment->delete();

        return back()
            ->withFragment('review-'.$reviewId)
            ->with('open_review_comments', $reviewId)
            ->with('status', 'Your comment was deleted.');
    }
}
