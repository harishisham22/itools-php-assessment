export function formatCurrency(value: number): string {
    return new Intl.NumberFormat('en-MY', {
        style: 'currency',
        currency: 'MYR',
        minimumFractionDigits: 2,
    }).format(value);
}

export function formatNumber(value: number): string {
    return new Intl.NumberFormat('en-MY', {
        maximumFractionDigits: 2,
    }).format(value);
}
