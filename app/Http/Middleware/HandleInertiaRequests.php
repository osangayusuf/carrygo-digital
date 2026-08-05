<?php

namespace App\Http\Middleware;

use App\Enums\AuctionStatus;
use App\Models\Auction;
use App\Models\User;
use App\Services\NotificationFeedService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        // Fetch marquee items
        $marqueeItems = [];
        $triggeredAuctionsData = [];

        try {
            // 1. Get live (triggered) auctions that are not yet expired
            $triggeredAuctions = Auction::where('status', AuctionStatus::TRIGGERED)
                ->where('expires_at', '>', now())
                ->orderBy('expires_at', 'asc')
                ->get();

            foreach ($triggeredAuctions->take(3) as $auction) {
                $marqueeItems[] = [
                    'text' => "LIVE AUCTION: {$auction->name} @ ₦".number_format($auction->price),
                    'icon' => 'pi pi-bolt',
                ];
            }

            foreach ($triggeredAuctions as $auction) {
                $triggeredAuctionsData[] = [
                    'id' => $auction->id,
                    'name' => $auction->name,
                    'expires_at' => $auction->expires_at?->toISOString(),
                    'url' => '/auctions/'.$auction->id,
                ];
            }

            // 2. Get active auctions
            $activeAuctions = Auction::where('status', AuctionStatus::ACTIVE)
                ->orderByDesc('bid_count')
                ->limit(3)
                ->get();
            foreach ($activeAuctions as $auction) {
                $progress = $auction->opening_points > 0
                    ? (int) min(100, round(($auction->current_points / $auction->opening_points) * 100))
                    : 0;
                $marqueeItems[] = [
                    'text' => "{$auction->name} – {$progress}% progress!",
                    'icon' => 'pi pi-chart-line',
                ];
                if ($auction->bid_count > 0) {
                    $marqueeItems[] = [
                        'text' => "{$auction->name} – {$auction->bid_count} bids so far",
                        'icon' => 'pi pi-star-fill',
                    ];
                }
            }

            // 3. Get recent winners
            $recentWinners = Auction::where('status', AuctionStatus::CLOSED)
                ->whereNotNull('winner_id')
                ->with('winner')
                ->orderBy('updated_at', 'desc')
                ->limit(3)
                ->get();
            foreach ($recentWinners as $auction) {
                $phone = $auction->winner?->phone;
                $maskedPhone = $this->maskPhone($phone);
                $marqueeItems[] = [
                    'text' => "Winner: {$maskedPhone} won {$auction->name}!",
                    'icon' => 'pi pi-trophy',
                ];
            }
        } catch (\Throwable $e) {
            // Safe fallback if database is not ready, migrated, or connected (e.g. during some unit tests)
        }

        // 4. Default weekly announcement
        $marqueeItems[] = [
            'text' => 'New auction drops every Monday!',
            'icon' => 'pi pi-send',
        ];

        // Ensure there are at least a few items
        if (count($marqueeItems) <= 1) {
            array_unshift(
                $marqueeItems,
                ['text' => 'Bid on premium luxury items with points!', 'icon' => 'pi pi-gift'],
                ['text' => 'Complete tasks in the Task Center to earn free points!', 'icon' => 'pi pi-check-square'],
                ['text' => 'Check the leaderboard to see top bidders of the week!', 'icon' => 'pi pi-chart-bar']
            );
        }

        $termsPath = public_path('terms');
        $termsDocuments = [];
        if (File::isDirectory($termsPath)) {
            $files = File::files($termsPath);
            foreach ($files as $file) {
                if (in_array(strtolower($file->getExtension()), ['docx', 'pdf', 'doc', 'txt', 'md'])) {
                    $filename = $file->getFilename();
                    $name = pathinfo($filename, PATHINFO_FILENAME);
                    $slug = Str::slug($name);
                    $termsDocuments[] = [
                        'name' => $name,
                        'slug' => $slug,
                        'filename' => $filename,
                        'url' => route('terms.show', $slug),
                        'download_url' => asset('terms/'.rawurlencode($filename)),
                    ];
                }
            }
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $user ? $user->loadMissing('agentStatus') : null,
                'pending_agents_count' => ($user && $user->hasRole('admin'))
                    ? User::role('agent')->whereNull('agent_approved_at')->whereNull('agent_rejected_at')->count()
                    : 0,
            ],
            'asset_url' => asset(''),
            'notifications' => $user
                ? app(NotificationFeedService::class)->forUser($user)
                : [],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'marquee_items' => $marqueeItems,
            'triggeredAuctions' => $triggeredAuctionsData,
            'terms_documents' => $termsDocuments,
        ];
    }

    /**
     * Securely mask phone numbers on the backend.
     */
    private function maskPhone(?string $phone): string
    {
        if (blank($phone)) {
            return 'Unknown';
        }

        $digits = preg_replace('/\D/', '', $phone);
        if (strlen($digits) < 7) {
            return '***'.substr($digits, -4);
        }

        $prefix = '+'.substr($digits, 0, 3).' '.substr($digits, 3, 3);
        $suffix = substr($digits, -4);

        return $prefix.'***'.$suffix;
    }
}
