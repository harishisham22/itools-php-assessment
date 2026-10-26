<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import TariffForm from '@/components/TariffForm.vue';
import { create, index } from '@/routes/tariffs';
import type { SelectOption } from '@/types';

defineProps<{
    buildingTypes: SelectOption[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Tariffs',
                href: index(),
            },
            {
                title: 'Create tariff',
                href: create(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Create tariff" />

    <div class="flex flex-col gap-6 p-4">
        <Heading
            title="Create tariff"
            description="Add a progressive electricity tariff tier for a building type."
        />

        <TariffForm :building-types="buildingTypes" />
    </div>
</template>
