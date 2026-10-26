import type { ReportMonthRow, ReportRow, ReportSummary } from './report';

export type DashboardSummary = ReportSummary & {
    customers: number;
    tariffs: number;
};

export type DashboardData = {
    summary: DashboardSummary;
    byMonth: ReportMonthRow[];
    byType: ReportRow[];
};
