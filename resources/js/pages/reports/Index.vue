<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Building2, ChartColumn, CircleDollarSign, Gauge } from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import Heading from '@/components/Heading.vue';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { formatCurrency, formatNumber } from '@/lib/format';
import { index } from '@/routes/reports';
import type { ReportFilters, ReportPayload, SelectOption } from '@/types';

const props = defineProps<{
    report: ReportPayload;
    filters: ReportFilters;
    types: SelectOption[];
    states: SelectOption[];
    months: SelectOption<number>[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Reports',
                href: index(),
            },
        ],
    },
});

const selectedType = ref(props.filters.type ?? 'all');
const selectedState = ref(props.filters.state ?? 'all');
const selectedMonth = ref<number | 'all'>(props.filters.month ?? 'all');

watch(
    () => props.filters,
    (filters) => {
        selectedType.value = filters.type ?? 'all';
        selectedState.value = filters.state ?? 'all';
        selectedMonth.value = filters.month ?? 'all';
    },
    { deep: true },
);

const applyFilters = () => {
    const query: Record<string, string> = {};

    if (selectedType.value !== 'all') {
        query.type = selectedType.value;
    }

    if (selectedState.value !== 'all') {
        query.state = selectedState.value;
    }

    if (selectedMonth.value !== 'all') {
        query.month = String(selectedMonth.value);
    }

    router.get(index.url(), query, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const updateType = (value: unknown) => {
    selectedType.value = String(value);
    applyFilters();
};

const updateState = (value: unknown) => {
    selectedState.value = String(value);
    applyFilters();
};

const updateMonth = (value: unknown) => {
    selectedMonth.value = value === 'all' ? 'all' : Number(value);
    applyFilters();
};

const hasData = computed(() => props.report.summary.buildings > 0);

const maxMonthlyBill = computed(() =>
    Math.max(...props.report.by_month.map((month) => month.bill), 1),
);

const maxTypeBill = computed(() =>
    Math.max(...props.report.by_type.map((type) => type.bill), 1),
);

const stateRows = computed(() =>
    props.report.by_state.filter((state) => state.buildings > 0),
);

const monthlyBarHeight = (bill: number): string =>
    bill <= 0 ? '0%' : `${Math.max((bill / maxMonthlyBill.value) * 100, 2)}%`;

const typeBarWidth = (bill: number): string =>
    bill <= 0 ? '0%' : `${Math.max((bill / maxTypeBill.value) * 100, 2)}%`;

const shortMonth = (label: string): string => label.slice(0, 3);
</script>

<template>
    <Head title="Reports" />

    <div class="flex flex-col gap-6 p-4">
        <Heading
            title="Reports"
            description="Billing summaries for the current filters."
        />

        <Card>
            <CardContent class="p-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                    <div class="grid gap-2">
                        <Label for="report-type" class="sr-only">
                            Filter by building type
                        </Label>
                        <Select
                            :model-value="selectedType"
                            @update:model-value="updateType"
                        >
                            <SelectTrigger
                                id="report-type"
                                class="w-full sm:w-44"
                            >
                                <SelectValue placeholder="All types" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All types</SelectItem>
                                <SelectItem
                                    v-for="type in types"
                                    :key="type.value"
                                    :value="type.value"
                                >
                                    {{ type.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="grid gap-2">
                        <Label for="report-state" class="sr-only">
                            Filter by state
                        </Label>
                        <Select
                            :model-value="selectedState"
                            @update:model-value="updateState"
                        >
                            <SelectTrigger
                                id="report-state"
                                class="w-full sm:w-44"
                            >
                                <SelectValue placeholder="All states" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All states</SelectItem>
                                <SelectItem
                                    v-for="state in states"
                                    :key="state.value"
                                    :value="state.value"
                                >
                                    {{ state.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="grid gap-2">
                        <Label for="report-month" class="sr-only">
                            Filter by month
                        </Label>
                        <Select
                            :model-value="selectedMonth"
                            @update:model-value="updateMonth"
                        >
                            <SelectTrigger
                                id="report-month"
                                class="w-full sm:w-44"
                            >
                                <SelectValue placeholder="All months" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All months</SelectItem>
                                <SelectItem
                                    v-for="month in months"
                                    :key="month.value"
                                    :value="month.value"
                                >
                                    {{ month.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>
            </CardContent>
        </Card>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <Card>
                <CardHeader
                    class="flex flex-row items-start justify-between gap-2"
                >
                    <div class="space-y-1">
                        <CardDescription>Total billed</CardDescription>
                        <CardTitle class="text-2xl tabular-nums">
                            {{ formatCurrency(report.summary.bill) }}
                        </CardTitle>
                    </div>
                    <CircleDollarSign class="size-5 text-muted-foreground" />
                </CardHeader>
            </Card>

            <Card>
                <CardHeader
                    class="flex flex-row items-start justify-between gap-2"
                >
                    <div class="space-y-1">
                        <CardDescription>Buildings</CardDescription>
                        <CardTitle class="text-2xl tabular-nums">
                            {{ formatNumber(report.summary.buildings) }}
                        </CardTitle>
                    </div>
                    <Building2 class="size-5 text-muted-foreground" />
                </CardHeader>
            </Card>

            <Card>
                <CardHeader
                    class="flex flex-row items-start justify-between gap-2"
                >
                    <div class="space-y-1">
                        <CardDescription>Total usage</CardDescription>
                        <CardTitle class="text-2xl tabular-nums">
                            {{ formatNumber(report.summary.usability) }} kWh
                        </CardTitle>
                    </div>
                    <Gauge class="size-5 text-muted-foreground" />
                </CardHeader>
            </Card>

            <Card>
                <CardHeader
                    class="flex flex-row items-start justify-between gap-2"
                >
                    <div class="space-y-1">
                        <CardDescription>Average bill</CardDescription>
                        <CardTitle class="text-2xl tabular-nums">
                            {{ formatCurrency(report.summary.average_bill) }}
                        </CardTitle>
                    </div>
                    <ChartColumn class="size-5 text-muted-foreground" />
                </CardHeader>
            </Card>
        </div>

        <Card>
            <CardHeader>
                <CardTitle>Bills by month</CardTitle>
                <CardDescription>
                    Total charges for each month in the current view.
                </CardDescription>
            </CardHeader>

            <CardContent>
                <p
                    v-if="!hasData"
                    class="py-10 text-center text-sm text-muted-foreground"
                >
                    No buildings match the current filters.
                </p>

                <div v-else class="flex gap-1 sm:gap-2">
                    <div
                        v-for="month in report.by_month"
                        :key="month.month"
                        class="flex min-w-0 flex-1 flex-col items-center"
                    >
                        <div class="flex h-48 w-full items-end">
                            <div
                                class="w-full rounded-t-md bg-primary/70 transition-colors hover:bg-primary"
                                :style="{
                                    height: monthlyBarHeight(month.bill),
                                }"
                                :title="`${month.label}: ${formatCurrency(month.bill)}`"
                            ></div>
                        </div>
                        <span
                            class="mt-2 text-[10px] text-muted-foreground sm:text-xs"
                        >
                            {{ shortMonth(month.label) }}
                        </span>
                    </div>
                </div>

                <p
                    v-if="hasData"
                    class="mt-2 text-right text-xs text-muted-foreground"
                >
                    Hover a bar to see its exact total.
                </p>
            </CardContent>
        </Card>

        <div class="grid gap-4 lg:grid-cols-5">
            <Card class="lg:col-span-2">
                <CardHeader>
                    <CardTitle>By building type</CardTitle>
                    <CardDescription>
                        Where the charges come from.
                    </CardDescription>
                </CardHeader>

                <CardContent class="space-y-5">
                    <div
                        v-for="type in report.by_type"
                        :key="type.label"
                        class="space-y-2"
                    >
                        <div class="flex items-baseline justify-between gap-2">
                            <span class="text-sm font-medium">
                                {{ type.label }}
                            </span>
                            <span class="text-sm font-medium tabular-nums">
                                {{ formatCurrency(type.bill) }}
                            </span>
                        </div>
                        <div class="h-2 overflow-hidden rounded-full bg-muted">
                            <div
                                class="h-full rounded-full bg-primary"
                                :style="{ width: typeBarWidth(type.bill) }"
                            ></div>
                        </div>
                        <div
                            class="flex justify-between gap-2 text-xs text-muted-foreground"
                        >
                            <span>
                                {{ formatNumber(type.buildings) }}
                                {{
                                    type.buildings === 1
                                        ? 'building'
                                        : 'buildings'
                                }}
                            </span>
                            <span class="tabular-nums">
                                {{ formatNumber(type.usability) }} kWh
                            </span>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card class="lg:col-span-3">
                <CardHeader>
                    <CardTitle>By state</CardTitle>
                    <CardDescription>
                        Highest totals first. States without buildings are
                        hidden.
                    </CardDescription>
                </CardHeader>

                <CardContent>
                    <div class="overflow-x-auto rounded-md border">
                        <table class="w-full min-w-[420px] text-sm">
                            <thead
                                class="bg-muted/50 text-left text-muted-foreground"
                            >
                                <tr>
                                    <th
                                        scope="col"
                                        class="px-4 py-3 font-medium"
                                    >
                                        State
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-4 py-3 text-right font-medium"
                                    >
                                        Buildings
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-4 py-3 text-right font-medium"
                                    >
                                        Usage
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-4 py-3 text-right font-medium"
                                    >
                                        Bill
                                    </th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr
                                    v-for="state in stateRows"
                                    :key="state.label"
                                    class="border-t transition-colors hover:bg-muted/50"
                                >
                                    <td class="px-4 py-3 font-medium">
                                        {{ state.label }}
                                    </td>
                                    <td
                                        class="px-4 py-3 text-right tabular-nums"
                                    >
                                        {{ formatNumber(state.buildings) }}
                                    </td>
                                    <td
                                        class="px-4 py-3 text-right tabular-nums"
                                    >
                                        {{ formatNumber(state.usability) }} kWh
                                    </td>
                                    <td
                                        class="px-4 py-3 text-right font-medium tabular-nums"
                                    >
                                        {{ formatCurrency(state.bill) }}
                                    </td>
                                </tr>

                                <tr v-if="stateRows.length === 0">
                                    <td
                                        colspan="4"
                                        class="px-4 py-10 text-center text-muted-foreground"
                                    >
                                        No state data for the current filters.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
