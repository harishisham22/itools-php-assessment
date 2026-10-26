<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowDown, ArrowUp, ArrowUpDown, Plus } from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import DeleteBuildingDialog from '@/components/DeleteBuildingDialog.vue';
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
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { formatCurrency, formatNumber } from '@/lib/format';
import { create, edit, index } from '@/routes/buildings';
import type { Building, BuildingFilters, SelectOption } from '@/types';

const props = defineProps<{
    buildings: Building[];
    filters: BuildingFilters;
    types: SelectOption[];
    states: SelectOption[];
    months: SelectOption<number>[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Buildings',
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

const typeLabels = computed(
    () => new Map(props.types.map((type) => [type.value, type.label])),
);
const stateLabels = computed(
    () => new Map(props.states.map((state) => [state.value, state.label])),
);
const monthLabels = computed(
    () => new Map(props.months.map((month) => [month.value, month.label])),
);

const applyFilters = (
    overrides: Partial<Pick<BuildingFilters, 'sort_by' | 'direction'>> = {},
) => {
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

    const sortBy = overrides.sort_by ?? props.filters.sort_by;
    const direction = overrides.direction ?? props.filters.direction;

    if (sortBy) {
        query.sort_by = sortBy;
        query.direction = direction;
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

const sortBy = (column: 'type' | 'usability') => {
    const direction: 'asc' | 'desc' =
        props.filters.sort_by === column && props.filters.direction === 'asc'
            ? 'desc'
            : 'asc';

    applyFilters({ sort_by: column, direction });
};

const sortIcon = (column: 'type' | 'usability') => {
    if (props.filters.sort_by !== column) {
        return ArrowUpDown;
    }

    return props.filters.direction === 'asc' ? ArrowUp : ArrowDown;
};
</script>

<template>
    <Head title="Buildings" />

    <div class="flex flex-col gap-6 p-4">
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
        >
            <Heading
                title="Buildings"
                description="Manage building records and their progressive electricity bills."
            />

            <Button as-child>
                <Link :href="create()">
                    <Plus />
                    Add building
                </Link>
            </Button>
        </div>

        <Card>
            <CardHeader>
                <CardTitle>All buildings</CardTitle>
                <CardDescription>
                    {{ buildings.length }}
                    {{ buildings.length === 1 ? 'building' : 'buildings' }}
                    in the current view.
                </CardDescription>
            </CardHeader>

            <CardContent class="space-y-4">
                <div class="flex flex-col gap-3 sm:flex-row">
                    <div class="grid gap-2">
                        <Label for="filter-type" class="sr-only">
                            Filter by building type
                        </Label>
                        <Select
                            :model-value="selectedType"
                            @update:model-value="updateType"
                        >
                            <SelectTrigger
                                id="filter-type"
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
                        <Label for="filter-state" class="sr-only">
                            Filter by state
                        </Label>
                        <Select
                            :model-value="selectedState"
                            @update:model-value="updateState"
                        >
                            <SelectTrigger
                                id="filter-state"
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
                        <Label for="filter-month" class="sr-only">
                            Filter by month
                        </Label>
                        <Select
                            :model-value="selectedMonth"
                            @update:model-value="updateMonth"
                        >
                            <SelectTrigger
                                id="filter-month"
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
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        class="-ml-3"
                                        @click="sortBy('type')"
                                    >
                                        Type
                                        <component
                                            :is="sortIcon('type')"
                                            class="size-4"
                                        />
                                    </Button>
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
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        class="-mr-3"
                                        @click="sortBy('usability')"
                                    >
                                        Usage
                                        <component
                                            :is="sortIcon('usability')"
                                            class="size-4"
                                        />
                                    </Button>
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
                                v-for="building in buildings"
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
                                        {{
                                            typeLabels.get(building.type) ??
                                            building.type
                                        }}
                                    </Badge>
                                </td>
                                <td class="px-4 py-3">
                                    {{
                                        stateLabels.get(building.state) ??
                                        building.state
                                    }}
                                </td>
                                <td class="px-4 py-3">
                                    {{
                                        monthLabels.get(building.month) ??
                                        building.month
                                    }}
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
                                            <Link :href="edit(building.id)">
                                                Edit
                                            </Link>
                                        </Button>
                                        <DeleteBuildingDialog
                                            :building="building"
                                        />
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="buildings.length === 0">
                                <td
                                    colspan="7"
                                    class="px-4 py-10 text-center text-muted-foreground"
                                >
                                    No buildings found. Try changing the filters
                                    or add a building.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
