<?php

namespace App\Http\Controllers;

use App\Enums\AuctionStatus;
use App\Models\Auction;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuctionReviewController extends Controller
{
    /**
     * Store a new review for a completed auction.
     *
     * @throws ValidationException
     */
    public function store(Request $request, Auction $auction): RedirectResponse
    {
        $user = $request->user();

        if ($auction->status !== AuctionStatus::CLOSED) {
            throw ValidationException::withMessages([
                'comment' => 'Reviews can only be submitted for closed auctions.',
            ]);
        }

        $hasBid = $auction->bids()->where('user_id', $user->id)->exists();
        if (! $hasBid) {
            abort(403, 'Only users who participated in this auction can submit a review.');
        }

        $hasReviewed = $auction->reviews()->where('user_id', $user->id)->exists();
        if ($hasReviewed) {
            throw ValidationException::withMessages([
                'comment' => 'You have already submitted a review for this auction.',
            ]);
        }

        $isWinner = $auction->winner_id === $user->id;

        $rules = [
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|min:5|max:2000',
            'social_platform' => 'nullable|string|in:Instagram,Twitter,X,TikTok,Facebook|required_with:social_handle',
            'social_handle' => 'nullable|string|max:100|required_with:social_platform',
        ];

        if ($isWinner) {
            $rules['photos'] = 'nullable|array|max:3';
            $rules['photos.*'] = 'required|image|max:5120';
            $rules['video'] = 'nullable|file|mimes:mp4,mov,quicktime|max:25600';
        } else {
            if ($request->hasFile('photos') || $request->hasFile('video')) {
                throw ValidationException::withMessages([
                    'comment' => 'Only winners are allowed to upload media as proof of delivery.',
                ]);
            }
        }

        $validated = $request->validate($rules);

        $photoPaths = [];
        if ($isWinner && $request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $photoPaths[] = $photo->store('reviews/photos', 'public');
            }
        }

        $videoPath = null;
        if ($isWinner && $request->hasFile('video')) {
            $videoPath = $request->file('video')->store('reviews/videos', 'public');
        }

        Review::create([
            'auction_id' => $auction->id,
            'user_id' => $user->id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
            'social_platform' => $validated['social_platform'] ?? null,
            'social_handle' => $validated['social_handle'] ?? null,
            'photos' => $isWinner && ! empty($photoPaths) ? $photoPaths : null,
            'video' => $isWinner ? $videoPath : null,
            'is_visible' => false,
        ]);

        return back()->with('success', 'Thank you for your feedback!');
    }
}
