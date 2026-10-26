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

export function formatKwhRange(min: number, max: number | null): string {
    if (max === null) {
        return `${formatNumber(min)} kWh and above`;
    }

    return `${formatNumber(min)} – ${formatNumber(max)} kWh`;
}
