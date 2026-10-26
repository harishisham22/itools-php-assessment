<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import BuildingForm from '@/components/BuildingForm.vue';
import Heading from '@/components/Heading.vue';
import { create, index } from '@/routes/buildings';
import type { SelectOption } from '@/types';

defineProps<{
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
                title: 'Create building',
                href: create(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Create building" />

    <div class="flex flex-col gap-6 p-4">
        <Heading
            title="Create building"
            description="Record a building and calculate its monthly electricity bill."
        />

        <BuildingForm :types="types" :states="states" :months="months" />
    </div>
</template>
