<?php

namespace App\Http\Controllers\Api\V1\ResortOwner;

use App\Events\ReviewReplied;
use App\Http\Controllers\Controller;
use App\Http\Requests\Review\ReplyReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Review;

class ReviewController extends Controller
{
    /**
     * List reviews for owner's resorts and units.
     */
    public function index()
    {
        $ownerId = auth()->id();

        // Get owner's resort IDs 
        $resortIds = \App\Models\Resort::where('owner_id', $ownerId)->pluck('id');
        // Get unit IDs for owner's resorts
        $unitIds = \App\Models\Unit::whereIn('resort_id', $resortIds)->pluck('id');

        $reviews = Review::with(['user', 'reviewable'])
            ->where(function ($query) use ($resortIds) {
                $query->where('reviewable_type', 'App\\Models\\Resort')
                    ->whereIn('reviewable_id', $resortIds);
            })
            ->orWhere(function ($query) use ($unitIds) {
                $query->where('reviewable_type', 'App\\Models\\Unit')
                    ->whereIn('reviewable_id', $unitIds);
            })
            ->latest()
            ->paginate();

        return $this->successCollection(ReviewResource::collection($reviews));
    }

    /**
     * Reply to a review.
     */
    public function update(ReplyReviewRequest $request, Review $review)
    {
        $this->authorize('reply', $review);

        if ($review->owner_reply) {
            return $this->error('You have already replied to this review.', 422);
        }

        $review->update(['owner_reply' => $request->owner_reply]);

        // Dispatch event to notify user
        ReviewReplied::dispatch($review);

        return $this->success(new ReviewResource($review->load('user')), 'Reply added successfully.');
    }
}
