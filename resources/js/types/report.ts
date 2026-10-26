export type ReportFilters = {
    type: string | null;
    state: string | null;
    month: number | null;
};

export type ReportSummary = {
    buildings: number;
    usability: number;
    bill: number;
    average_bill: number;
};

export type ReportRow = {
    label: string;
    bill: number;
    buildings: number;
    usability: number;
};

export type ReportMonthRow = ReportRow & {
    month: number;
};

export type ReportPayload = {
    summary: ReportSummary;
    by_month: ReportMonthRow[];
    by_type: ReportRow[];
    by_state: ReportRow[];
};
