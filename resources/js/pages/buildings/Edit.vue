<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import BuildingForm from '@/components/BuildingForm.vue';
import Heading from '@/components/Heading.vue';
import { index } from '@/routes/buildings';
import type { Building, SelectOption } from '@/types';

defineProps<{
    building: Building;
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
            {
                title: 'Edit building',
                href: index(),
            },
        ],
    },
});
</script>

<template>
    <Head :title="`Edit ${building.customer_name}`" />

    <div class="flex flex-col gap-6 p-4">
        <Heading
            :title="`Edit ${building.customer_name}`"
            description="Update the building details and recalculate its electricity bill."
        />

        <BuildingForm
            :building="building"
            :types="types"
            :states="states"
            :months="months"
        />
    </div>
</template>
