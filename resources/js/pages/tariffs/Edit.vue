<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import TariffForm from '@/components/TariffForm.vue';
import { index } from '@/routes/tariffs';
import type { SelectOption, Tariff } from '@/types';

defineProps<{
    tariff: Tariff;
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
                title: 'Edit tariff',
                href: index(),
            },
        ],
    },
});
</script>

<template>
    <Head :title="`Edit ${tariff.category}`" />

    <div class="flex flex-col gap-6 p-4">
        <Heading
            :title="`Edit ${tariff.category}`"
            description="Update the tariff tier. Tiers for the same building type must not overlap."
        />

        <TariffForm :tariff="tariff" :building-types="buildingTypes" />
    </div>
</template>
