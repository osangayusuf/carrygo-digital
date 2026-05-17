import { useNow } from '@vueuse/core';
import { clsx } from 'clsx';
import type { ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';
import type { Bid } from '@/pages/Home.vue';

const now = useNow();

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function formatPrice(price: string | number): string {
    if (!price && price !== 0) {
        return '₦ 0';
    }

    const cleanPrice =
        typeof price === 'string' ? price.replace(/,/g, '') : price;
    const num = parseFloat(cleanPrice.toString());

    return isNaN(num) ? price.toString() : `₦ ${num.toLocaleString()}`;
}

export function calcProgress(bid: Bid): number {
    if (!bid.open_points) {
        return 0;
    }

    const progress = ((bid.bid_entry_points || 0) / bid.open_points) * 100;

    return Math.round(Math.min(100, progress));
}

export function formatDate(dateStr: string): string {
    const date = new Date(dateStr);

    return date.toLocaleDateString('en-GB', { day: 'numeric', month: 'short' });
}

export function getRemainingTime(endsAt: string | null | undefined): string {
    if (!endsAt) {
        return '';
    }

    const diffInMs = new Date(endsAt).getTime() - now.value.getTime();

    if (diffInMs <= 0) {
        return '0 hour(s), 0 minute(s)';
    }

    const totalMinutes = Math.floor(diffInMs / (1000 * 60));
    const hours = Math.floor(totalMinutes / 60);
    const minutes = totalMinutes % 60;

    return `${hours} hour(s), ${minutes} minute(s)`;
}

export function formatMsisdn(msisdn: string): string {
    if (!msisdn || msisdn.length < 5) {
        return 'Unknown';
    }

    const start = Math.floor((msisdn.length - 5) / 2);

    return msisdn.slice(0, start) + '*****' + msisdn.slice(start + 5);
}

export function getDaysAgo(dateStr: string): string {
    const date = new Date(dateStr);
    const diffInMs = now.value.getTime() - date.getTime();
    const diffInDays = Math.floor(diffInMs / (1000 * 60 * 60 * 24));

    if (diffInDays === 0) {
        return 'Today';
    } else if (diffInDays === 1) {
        return '1 day ago';
    } else {
        return `${diffInDays} days ago`;
    }
}
