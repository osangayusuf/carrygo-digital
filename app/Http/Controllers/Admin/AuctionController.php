<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAuctionRequest;
use App\Http\Requests\Admin\UpdateAuctionRequest;
use App\Models\Auction;
use App\Services\AuctionService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AuctionController extends Controller
{
    public function __construct(private readonly AuctionService $auctionService) {}

    public function index(): Response
    {
        return Inertia::render('Admin/Auctions/Index', [
            'auctions' => Auction::latest()->paginate(20),
        ]);
    }

    public function store(StoreAuctionRequest $request): RedirectResponse
    {
        $this->auctionService->create($request->validated());

        return redirect()->route('admin.auctions.index')
            ->with('success', 'Auction created successfully.');
    }

    public function update(UpdateAuctionRequest $request, Auction $auction): RedirectResponse
    {
        $this->auctionService->update($auction, $request->validated());

        return redirect()->route('admin.auctions.index')
            ->with('success', 'Auction updated successfully.');
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
}
