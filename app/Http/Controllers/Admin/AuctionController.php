<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAuctionRequest;
use App\Http\Requests\Admin\UpdateAuctionRequest;
use App\Models\Auction;
use App\Services\AuctionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AuctionController extends Controller
{
    public function __construct(private readonly AuctionService $auctionService) {}

    public function index(Request $request): Response
    {
        $search = $request->filled('search')
            ? trim($request->string('search')->toString())
            : null;

        $statusFilter = $request->filled('status')
            ? $request->string('status')->toString()
            : null;

        $enabledFilter = $request->filled('enabled')
            ? $request->string('enabled')->toString()
            : null;

        $eventFilter = $request->filled('event')
            ? $request->string('event')->toString()
            : null;

        $query = Auction::query()->latest();

        if ($search) {
            $query->search($search);
        }

        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }

        if ($enabledFilter === 'enabled') {
            $query->where('enabled', true);
        } elseif ($enabledFilter === 'disabled') {
            $query->where('enabled', false);
        }

        if ($eventFilter === 'event') {
            $query->where('event', true);
        } elseif ($eventFilter === 'regular') {
            $query->where('event', false);
        }

        $categories = Auction::distinct()
            ->orderBy('category')
            ->pluck('category')
            ->filter()
            ->values()
            ->all();

        return Inertia::render('Admin/Auctions/Index', [
            'auctions' => $query->paginate(20)->withQueryString(),
            'categories' => $categories,
            'filters' => [
                'search' => $search,
                'status' => $statusFilter,
                'enabled' => $enabledFilter,
                'event' => $eventFilter,
            ],
        ]);
    }

    public function store(StoreAuctionRequest $request): RedirectResponse
    {
        $auction = $this->auctionService->create($request->validated());

        if ($request->boolean('publish')) {
            $this->auctionService->publish($auction);
        }

        return redirect()->route('admin.auctions.index')
            ->with('success', $request->boolean('publish')
                ? 'Auction created and published successfully.'
                : 'Auction created successfully.');
    }

    public function update(UpdateAuctionRequest $request, Auction $auction): RedirectResponse
    {
        $this->auctionService->update($auction, $request->validated());

        if ($request->boolean('publish')) {
            $this->auctionService->publish($auction);
        }

        return redirect()->route('admin.auctions.index')
            ->with('success', $request->boolean('publish')
                ? 'Auction updated and published successfully.'
                : 'Auction updated successfully.');
    }

    public function destroy(Auction $auction): RedirectResponse
    {
        $this->auctionService->delete($auction);

        return redirect()->route('admin.auctions.index')
            ->with('success', 'Auction deleted successfully.');
    }

    public function publish(Auction $auction): RedirectResponse
    {
        $this->auctionService->publish($auction);

        return redirect()->route('admin.auctions.index')
            ->with('success', 'Auction published successfully.');
    }

    public function close(Auction $auction): RedirectResponse
    {
        $this->auctionService->manualClose($auction);

        return redirect()->route('admin.auctions.index')
            ->with('success', 'Auction closed successfully.');
    }

    public function toggleEnabled(Auction $auction): RedirectResponse
    {
        $auction->enabled
            ? $this->auctionService->disable($auction)
            : $this->auctionService->enable($auction);

        return redirect()->route('admin.auctions.index')
            ->with('success', $auction->enabled
                ? 'Auction enabled successfully.'
                : 'Auction disabled successfully.');
    }

    public function toggleEvent(Auction $auction): RedirectResponse
    {
        $this->auctionService->toggleEvent($auction);

        return redirect()->route('admin.auctions.index')
            ->with('success', $auction->event
                ? 'Auction marked as an event item.'
                : 'Auction unmarked as an event item.');
    }
}
