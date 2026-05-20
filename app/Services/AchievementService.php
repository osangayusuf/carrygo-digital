<?php

namespace App\Services;

use App\Enums\RewardSource;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Auction;
use App\Models\Bid;
use App\Models\User;
use App\Models\UserAchievement;
use App\Notifications\AchievementUnlocked;
use Illuminate\Support\Facades\DB;

class AchievementService
{
    public function __construct(
        private readonly WalletService $walletService,
    ) {}

    public function evaluateAfterBid(User $user, Bid $bid, Auction $auction): void
    {
        $this->evaluateFirstBid($user);
        $this->evaluateExplorer($user);
        $this->evaluateBigSpender($user);
    }

    public function evaluateAfterWin(User $user): void
    {
        $this->evaluateFirstWin($user);
    }

    private function evaluateFirstBid(User $user): void
    {
        $this->incrementProgress($user, 'first_bid', 1);
    }

    private function evaluateFirstWin(User $user): void
    {
        $this->incrementProgress($user, 'first_win', 1);
    }

    private function evaluateExplorer(User $user): void
    {
        $target = (int) config('rewards.achievements.explorer.target', 3);

        $count = DB::table('bids')
            ->join('auctions', 'bids.auction_id', '=', 'auctions.id')
            ->where('bids.user_id', $user->id)
            ->distinct()
            ->count('auctions.category');

        $this->setProgress($user, 'explorer', min($count, $target));
    }

    private function evaluateBigSpender(User $user): void
    {
        $target = (int) config('rewards.achievements.big_spender.target', 500);

        $total = (int) DB::table('point_transactions')
            ->where('user_id', $user->id)
            ->where('type', TransactionType::BID_DEBIT->value)
            ->where('status', TransactionStatus::COMPLETED->value)
            ->sum('amount');

        $this->setProgress($user, 'big_spender', min($total, $target));
    }

    private function incrementProgress(User $user, string $key, int $amount): void
    {
        $definition = config("rewards.achievements.{$key}");

        if ($definition === null) {
            return;
        }

        $target = (int) $definition['target'];
        $record = $this->getOrCreate($user, $key);

        if ($record->completed_at !== null) {
            return;
        }

        $record->progress = min($record->progress + $amount, $target);
        $record->save();

        if ($record->progress >= $target) {
            $this->complete($user, $key, $record, $definition);
        }
    }

    private function setProgress(User $user, string $key, int $progress): void
    {
        $definition = config("rewards.achievements.{$key}");

        if ($definition === null) {
            return;
        }

        $target = (int) $definition['target'];
        $record = $this->getOrCreate($user, $key);

        if ($record->completed_at !== null) {
            return;
        }

        $record->progress = min($progress, $target);
        $record->save();

        if ($record->progress >= $target) {
            $this->complete($user, $key, $record, $definition);
        }
    }

    /**
     * @param  array<string, mixed>  $definition
     */
    private function complete(User $user, string $key, UserAchievement $record, array $definition): void
    {
        $record->completed_at = now();
        $record->save();

        $points = (int) $definition['points'];
        $label = (string) $definition['label'];

        $transaction = $this->walletService->awardBonusPoints(
            $user,
            $points,
            [
                'source' => RewardSource::Achievement->value,
                'description' => "Achievement: {$label}",
                'achievement_key' => $key,
            ],
            sendBonusAwardedNotification: false,
        );

        $user->notify(new AchievementUnlocked($transaction, $label));
    }

    private function getOrCreate(User $user, string $key): UserAchievement
    {
        return UserAchievement::firstOrCreate(
            [
                'user_id' => $user->id,
                'achievement_key' => $key,
            ],
            [
                'progress' => 0,
            ],
        );
    }
}
