<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\ReviewReaction;
use Illuminate\Http\Request;

class ReviewReactionController extends Controller
{
    /**
     * Store or toggle a reaction for a review.
     */
    public function store(Request $request, Review $review)
    {
        $request->validate([
            'reaction' => 'required|in:agree,disagree',
        ]);

        $userId = $request->user()->id;

        $existing = ReviewReaction::where('review_id', $review->id)
            ->where('user_id', $userId)
            ->first();

        if ($existing) {
            if ($existing->reaction === $request->reaction) {
                // Same reaction — remove (undo)
                $existing->delete();
                $message = 'removed';
            } else {
                // Different reaction — update
                $existing->reaction = $request->reaction;
                $existing->save();
                $message = 'updated';
            }
        } else {
            // Create new reaction
            ReviewReaction::create([
                'review_id' => $review->id,
                'user_id' => $userId,
                'reaction' => $request->reaction,
            ]);
            $message = 'created';
        }

        return response()->json([
            'agree' => $review->agreeCount(),
            'disagree' => $review->disagreeCount(),
            'status' => $message,
        ]);
    }
}
