export type Bidder = {
    msisdn: string;
    total_points: number;
};

export type Bid = {
    id: number;
    name: string;
    image: string | null;
    description: string | null;
    url: string;
    price: string;
    opening_points: number;
    rating: string | null;
    open_date: number;
    status: 0 | 1 | 2;
    created_at: string;
    current_points: number | null;
    expires_at: string | null;
    bid_count: number;
    external_url?: string | null;
    top_bidders?: Bidder[];
};

export type WinnerListing = {
    id: number;
    msisdn: string;
    winner_name: string;
    winning_pts: number;
    total_pts_bid: number;
    bid_count: number;
    created_at: string;
    bid: {
        id: number;
        name: string;
        image: string | null;
        url: string;
        price: string;
    } | null;
};

export type LeaderboardAuction = Bid & {
    top_bidders: Bidder[];
};

export type LengthAwarePaginator<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    first_page_url: string;
    last_page_url: string;
    next_page_url: string | null;
    prev_page_url: string | null;
    path: string;
    links: {
        url: string | null;
        label: string;
        page: number | null;
        active: boolean;
    }[];
};
