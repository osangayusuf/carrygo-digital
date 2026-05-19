import type { LengthAwarePaginator } from '@/types/auction';

export type WalletBalances = {
    points_balance: number;
    bonus_points: number;
};

export type WalletConfig = {
    points_per_naira: number;
    bonus_conversion_rate: number;
    min_deposit_naira: number;
    max_deposit_naira: number;
    deposit_presets: number[];
    paystack_public_key: string;
};

export type PointTransactionListing = {
    id: number;
    type: string;
    type_label: string;
    direction: 'credit' | 'debit';
    amount: number;
    naira_amount: number | null;
    exchange_rate: number;
    status: string;
    provider_reference: string | null;
    auction_id: number | null;
    metadata: Record<string, unknown> | null;
    created_at: string;
};

export type PaystackInit = {
    reference: string;
    access_code: string | null;
    amount_kobo: number;
    email: string;
    public_key: string;
};

export type WalletTransactionsPaginator = LengthAwarePaginator<PointTransactionListing>;
