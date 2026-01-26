<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Review\StoreReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Resort;
use App\Models\Review;
use App\Models\Unit;

class ReviewController extends Controller
{
    /**
     * List user's own reviews.
     */
    public function index()
    {
        $reviews = auth()->user()->reviews()->with('reviewable')->latest()->paginate();

        return $this->successCollection(ReviewResource::collection($reviews));
    }

    /**
     * Create a new review.
     */
    public function store(StoreReviewRequest $request)
    {
        $user = auth()->user();

        // Resolve reviewable model
        $reviewableClass = $request->reviewable_type === 'resort' ? Resort::class : Unit::class;
        $reviewable = $reviewableClass::findOrFail($request->reviewable_id);

        // Check if already reviewed
        if ($user->hasReviewed($reviewable)) {
            return $this->error('You have already reviewed this.', 422);
        }

        $review = Review::create([
            'user_id' => $user->id,
            'reviewable_type' => $reviewableClass,
            'reviewable_id' => $reviewable->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return $this->created(new ReviewResource($review->load('user')), 'Review created.');
    }

    /**
     * Delete user's own review.
     */
    public function destroy(Review $review)
    {
        $this->authorize('delete', $review);

        $review->delete();

        return $this->success(null, 'Review deleted.');
    }
}
