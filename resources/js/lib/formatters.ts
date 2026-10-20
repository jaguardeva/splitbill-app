/**
 * Format integer minor units (whole Rupiah) into localized Indonesian currency string.
 */
export function formatRupiah(amountMinor: number): string {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
        minimumFractionDigits: 0,
    }).format(amountMinor || 0);
}

/**
 * Format raw numbers with thousands separator (e.g. 50.000).
 */
export function formatNumber(val: number): string {
    return new Intl.NumberFormat('id-ID').format(val || 0);
}

/**
 * Format percentage rate (e.g. 10%).
 */
export function formatPercent(rate: number | null | undefined): string {
    if (rate === null || rate === undefined) return '0%';
    return `${rate}%`;
}

/**
 * Format ISO datetime string to Indonesian readable date.
 */
export function formatDate(isoDate: string): string {
    if (!isoDate) return '-';
    const date = new Date(isoDate);
    return new Intl.DateTimeFormat('id-ID', {
        timeZone: 'Asia/Jakarta',
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(date);
}
