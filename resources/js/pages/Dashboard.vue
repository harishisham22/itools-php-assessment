<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    Building2,
    ChartColumn,
    CircleDollarSign,
    Gauge,
    Plus,
    ReceiptText,
} from '@lucide/vue';
import { computed } from 'vue';
import BillsByMonthChart from '@/components/BillsByMonthChart.vue';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { formatCurrency, formatEnumLabel, formatNumber } from '@/lib/format';
import { dashboard } from '@/routes';
import {
    create as buildingCreate,
    edit as buildingEdit,
    index as buildingsIndex,
} from '@/routes/buildings';
import { index as reportsIndex } from '@/routes/reports';
import { index as tariffsIndex } from '@/routes/tariffs';
import type {
    Building,
    DashboardSummary,
    ReportMonthRow,
    ReportRow,
} from '@/types';

const props = defineProps<{
    summary: DashboardSummary;
    byMonth: ReportMonthRow[];
    byType: ReportRow[];
    recentBuildings: Building[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});

const averageUsage = computed(() =>
    props.summary.buildings > 0
        ? props.summary.usability / props.summary.buildings
        : 0,
);

const maxTypeBill = computed(() =>
    Math.max(...props.byType.map((type) => type.bill), 1),
);

const typeBarWidth = (bill: number): string =>
    bill <= 0 ? '0%' : `${Math.max((bill / maxTypeBill.value) * 100, 2)}%`;

const monthLabel = (month: number): string =>
    new Intl.DateTimeFormat('en-MY', { month: 'long' }).format(
        new Date(2024, month - 1, 1),
    );
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex flex-col gap-6 p-4">
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
        >
            <Heading
                title="Dashboard"
                description="Billing overview across all recorded buildings."
            />

            <div class="flex flex-wrap gap-2">
                <Button variant="outline" as-child>
                    <Link :href="reportsIndex()">
                        <ChartColumn />
                        View reports
                    </Link>
                </Button>
                <Button as-child>
                    <Link :href="buildingCreate()">
                        <Plus />
                        Add building
                    </Link>
                </Button>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <Card>
                <CardHeader
                    class="flex flex-row items-start justify-between gap-2"
                >
                    <div class="space-y-1">
                        <CardDescription>Total billed</CardDescription>
                        <CardTitle class="text-2xl tabular-nums">
                            {{ formatCurrency(summary.bill) }}
                        </CardTitle>
                        <p class="text-xs text-muted-foreground">
                            {{ formatCurrency(summary.average_bill) }} average
                            per building
                        </p>
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
                            {{ formatNumber(summary.buildings) }}
                        </CardTitle>
                        <p class="text-xs text-muted-foreground">
                            {{ formatNumber(summary.customers) }}
                            {{
                                summary.customers === 1
                                    ? 'customer'
                                    : 'customers'
                            }}
                        </p>
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
                            {{ formatNumber(summary.usability) }} kWh
                        </CardTitle>
                        <p class="text-xs text-muted-foreground">
                            {{ formatNumber(averageUsage) }} kWh per building
                        </p>
                    </div>
                    <Gauge class="size-5 text-muted-foreground" />
                </CardHeader>
            </Card>

            <Card>
                <CardHeader
                    class="flex flex-row items-start justify-between gap-2"
                >
                    <div class="space-y-1">
                        <CardDescription>Tariff tiers</CardDescription>
                        <CardTitle class="text-2xl tabular-nums">
                            {{ formatNumber(summary.tariffs) }}
                        </CardTitle>
                        <p class="text-xs text-muted-foreground">
                            <Link
                                :href="tariffsIndex()"
                                class="underline-offset-4 hover:underline"
                            >
                                Manage tariff tiers
                            </Link>
                        </p>
                    </div>
                    <ReceiptText class="size-5 text-muted-foreground" />
                </CardHeader>
            </Card>
        </div>

        <div class="grid gap-4 lg:grid-cols-5">
            <Card class="lg:col-span-3">
                <CardHeader>
                    <CardTitle>Bills by month</CardTitle>
                    <CardDescription>
                        Total charges recorded for each month.
                    </CardDescription>
                </CardHeader>

                <CardContent>
                    <BillsByMonthChart :months="byMonth" />
                </CardContent>
            </Card>

            <Card class="lg:col-span-2">
                <CardHeader>
                    <CardTitle>Bills by type</CardTitle>
                    <CardDescription>
                        How the charges split across building types.
                    </CardDescription>
                </CardHeader>

                <CardContent class="space-y-5">
                    <div
                        v-for="type in byType"
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
        </div>

        <Card>
            <CardHeader class="flex flex-row items-start justify-between gap-4">
                <div class="space-y-1">
                    <CardTitle>Recent buildings</CardTitle>
                    <CardDescription>
                        The five most recently added buildings.
                    </CardDescription>
                </div>
                <Button variant="outline" size="sm" as-child>
                    <Link :href="buildingsIndex()">View all</Link>
                </Button>
            </CardHeader>

            <CardContent>
                <div class="overflow-x-auto rounded-md border">
                    <table class="w-full min-w-[760px] text-sm">
                        <thead
                            class="bg-muted/50 text-left text-muted-foreground"
                        >
                            <tr>
                                <th scope="col" class="px-4 py-3 font-medium">
                                    Customer
                                </th>
                                <th scope="col" class="px-4 py-3 font-medium">
                                    Type
                                </th>
                                <th scope="col" class="px-4 py-3 font-medium">
                                    State
                                </th>
                                <th scope="col" class="px-4 py-3 font-medium">
                                    Month
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
                                <th
                                    scope="col"
                                    class="px-4 py-3 text-right font-medium"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr
                                v-for="building in recentBuildings"
                                :key="building.id"
                                class="border-t transition-colors hover:bg-muted/50"
                            >
                                <td class="px-4 py-3 font-medium">
                                    {{ building.customer_name }}
                                </td>
                                <td class="px-4 py-3">
                                    <Badge
                                        :variant="
                                            building.type === 'residential'
                                                ? 'secondary'
                                                : 'default'
                                        "
                                    >
                                        {{ formatEnumLabel(building.type) }}
                                    </Badge>
                                </td>
                                <td class="px-4 py-3">
                                    {{ formatEnumLabel(building.state) }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ monthLabel(building.month) }}
                                </td>
                                <td class="px-4 py-3 text-right tabular-nums">
                                    {{ formatNumber(building.usability) }} kWh
                                </td>
                                <td
                                    class="px-4 py-3 text-right font-medium tabular-nums"
                                >
                                    {{ formatCurrency(building.bill) }}
                                </td>
                                <td class="px-4 py-3">
                                    <div
                                        class="flex items-center justify-end gap-2"
                                    >
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            as-child
                                        >
                                            <Link
                                                :href="
                                                    buildingEdit(building.id)
                                                "
                                            >
                                                Edit
                                            </Link>
                                        </Button>
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="recentBuildings.length === 0">
                                <td
                                    colspan="7"
                                    class="px-4 py-10 text-center text-muted-foreground"
                                >
                                    No buildings yet.
                                    <Link
                                        :href="buildingCreate()"
                                        class="font-medium text-foreground underline underline-offset-4"
                                    >
                                        Add your first building
                                    </Link>
                                    .
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
