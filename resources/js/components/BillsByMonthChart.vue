<script setup lang="ts">
import { computed } from 'vue';
import { formatCurrency } from '@/lib/format';
import type { ReportMonthRow } from '@/types';

const props = defineProps<{
    months: ReportMonthRow[];
}>();

const maxBill = computed(() =>
    Math.max(...props.months.map((month) => month.bill), 1),
);

const hasData = computed(() =>
    props.months.some((month) => month.buildings > 0),
);

const barHeight = (bill: number): string =>
    bill <= 0 ? '0%' : `${Math.max((bill / maxBill.value) * 100, 2)}%`;

const shortMonth = (label: string): string => label.slice(0, 3);
</script>

<template>
    <p v-if="!hasData" class="py-10 text-center text-sm text-muted-foreground">
        No buildings in the current view.
    </p>

    <div v-else class="flex gap-1 sm:gap-2">
        <div
            v-for="month in months"
            :key="month.month"
            class="flex min-w-0 flex-1 flex-col items-center"
        >
            <div class="flex h-48 w-full items-end">
                <div
                    class="w-full rounded-t-md bg-primary/70 transition-colors hover:bg-primary"
                    :style="{ height: barHeight(month.bill) }"
                    :title="`${month.label}: ${formatCurrency(month.bill)}`"
                ></div>
            </div>
            <span class="mt-2 text-[10px] text-muted-foreground sm:text-xs">
                {{ shortMonth(month.label) }}
            </span>
        </div>
    </div>

    <p v-if="hasData" class="mt-2 text-right text-xs text-muted-foreground">
        Hover a bar to see its exact total.
    </p>
</template>
