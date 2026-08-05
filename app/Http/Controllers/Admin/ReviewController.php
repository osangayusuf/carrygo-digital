<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ReviewController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->filled('search')
            ? trim($request->string('search')->toString())
            : null;

        $visibilityFilter = $request->filled('visibility')
            ? $request->string('visibility')->toString()
            : null;

        $query = Review::query()
            ->with(['user', 'auction'])
            ->orderBy('id', 'desc');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($uq) use ($search) {
                    $uq->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                })->orWhere('comment', 'like', "%{$search}%");
            });
        }

        if ($visibilityFilter !== null) {
            $query->where('is_visible', filter_var($visibilityFilter, FILTER_VALIDATE_BOOLEAN));
        }

        $reviews = $query->paginate(20)->withQueryString();

        return Inertia::render('Admin/Reviews/Index', [
            'reviews' => $reviews,
            'filters' => [
                'search' => $search,
                'visibility' => $visibilityFilter,
            ],
        ]);
    }

    public function toggleVisibility(Review $review): RedirectResponse
    {
        $review->is_visible = ! $review->is_visible;
        $review->save();

        return back()->with('success', $review->is_visible
            ? __('Review is now visible to the public feed.')
            : __('Review is now hidden from the public feed.')
        );
    }

    public function destroy(Review $review): RedirectResponse
    {
        if (! empty($review->photos)) {
            foreach ($review->photos as $photo) {
                Storage::disk('public')->delete($photo);
            }
        }

        if (! empty($review->video)) {
            Storage::disk('public')->delete($review->video);
        }

        $review->delete();

        return back()->with('success', __('Review deleted successfully.'));
    }
}
