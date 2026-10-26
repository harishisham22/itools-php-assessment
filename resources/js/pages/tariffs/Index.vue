<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus } from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import DeleteTariffDialog from '@/components/DeleteTariffDialog.vue';
import Heading from '@/components/Heading.vue';
import Pagination from '@/components/Pagination.vue';
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
import { formatCurrency, formatKwhRange } from '@/lib/format';
import { create, edit, index } from '@/routes/tariffs';
import type { Paginated, SelectOption, Tariff, TariffFilters } from '@/types';

const props = defineProps<{
    tariffs: Paginated<Tariff>;
    filters: TariffFilters;
    buildingTypes: SelectOption[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Tariffs',
                href: index(),
            },
        ],
    },
});

const selectedBuildingType = ref(props.filters.building_type ?? 'all');

watch(
    () => props.filters,
    (filters) => {
        selectedBuildingType.value = filters.building_type ?? 'all';
    },
    { deep: true },
);

const buildingTypeLabels = computed(
    () => new Map(props.buildingTypes.map((type) => [type.value, type.label])),
);

const applyFilters = () => {
    const query: Record<string, string> = {};

    if (selectedBuildingType.value !== 'all') {
        query.building_type = selectedBuildingType.value;
    }

    router.get(index.url(), query, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const updateBuildingType = (value: unknown) => {
    selectedBuildingType.value = String(value);
    applyFilters();
};
</script>

<template>
    <Head title="Tariffs" />

    <div class="flex flex-col gap-6 p-4">
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
        >
            <Heading
                title="Tariffs"
                description="Manage progressive electricity tariff tiers for each building type."
            />

            <Button as-child>
                <Link :href="create()">
                    <Plus />
                    Add tariff
                </Link>
            </Button>
        </div>

        <Card>
            <CardHeader>
                <CardTitle>Tariff tiers</CardTitle>
                <CardDescription>
                    {{ tariffs.total }}
                    {{ tariffs.total === 1 ? 'tier' : 'tiers' }}
                    in the current view.
                </CardDescription>
            </CardHeader>

            <CardContent class="space-y-4">
                <div class="flex flex-col gap-3 sm:flex-row">
                    <div class="grid gap-2">
                        <Label for="filter-building-type" class="sr-only">
                            Filter by building type
                        </Label>
                        <Select
                            :model-value="selectedBuildingType"
                            @update:model-value="updateBuildingType"
                        >
                            <SelectTrigger
                                id="filter-building-type"
                                class="w-full sm:w-52"
                            >
                                <SelectValue placeholder="All building types" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">
                                    All building types
                                </SelectItem>
                                <SelectItem
                                    v-for="type in buildingTypes"
                                    :key="type.value"
                                    :value="type.value"
                                >
                                    {{ type.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                <div class="overflow-x-auto rounded-md border">
                    <table class="w-full min-w-[860px] text-sm">
                        <thead
                            class="bg-muted/50 text-left text-muted-foreground"
                        >
                            <tr>
                                <th scope="col" class="px-4 py-3 font-medium">
                                    Building type
                                </th>
                                <th scope="col" class="px-4 py-3 font-medium">
                                    Category
                                </th>
                                <th scope="col" class="px-4 py-3 font-medium">
                                    kWh range
                                </th>
                                <th
                                    scope="col"
                                    class="px-4 py-3 text-right font-medium"
                                >
                                    Rate
                                </th>
                                <th scope="col" class="px-4 py-3 font-medium">
                                    Initial
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
                                v-for="tariff in tariffs.data"
                                :key="tariff.id"
                                class="border-t transition-colors hover:bg-muted/50"
                            >
                                <td class="px-4 py-3">
                                    <Badge
                                        :variant="
                                            tariff.building_type ===
                                            'residential'
                                                ? 'secondary'
                                                : 'default'
                                        "
                                    >
                                        {{
                                            buildingTypeLabels.get(
                                                tariff.building_type,
                                            ) ?? tariff.building_type
                                        }}
                                    </Badge>
                                </td>
                                <td class="px-4 py-3 font-medium">
                                    {{ tariff.category }}
                                </td>
                                <td class="px-4 py-3 tabular-nums">
                                    {{
                                        formatKwhRange(
                                            tariff.min_kwh,
                                            tariff.max_kwh,
                                        )
                                    }}
                                </td>
                                <td
                                    class="px-4 py-3 text-right font-medium tabular-nums"
                                >
                                    {{ formatCurrency(tariff.rate) }}
                                    <span class="text-muted-foreground">
                                        / kWh
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <Badge
                                        v-if="tariff.is_initial_rate"
                                        variant="outline"
                                    >
                                        Initial
                                    </Badge>
                                    <span v-else class="text-muted-foreground">
                                        —
                                    </span>
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
                                            <Link :href="edit(tariff.id)">
                                                Edit
                                            </Link>
                                        </Button>
                                        <DeleteTariffDialog :tariff="tariff" />
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="tariffs.data.length === 0">
                                <td
                                    colspan="6"
                                    class="px-4 py-10 text-center text-muted-foreground"
                                >
                                    No tariffs found. Try changing the filter or
                                    add a tariff tier.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <Pagination :paginator="tariffs" />
            </CardContent>
        </Card>
    </div>
</template>
