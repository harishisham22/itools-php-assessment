<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Spinner } from '@/components/ui/spinner';
import { index, store, update } from '@/routes/tariffs';
import type { SelectOption, Tariff, TariffFormData } from '@/types';

const props = withDefaults(
    defineProps<{
        tariff?: Tariff | null;
        buildingTypes: SelectOption[];
    }>(),
    {
        tariff: null,
    },
);

const isEditing = computed(() => props.tariff !== null);

const form = useForm<TariffFormData>({
    building_type:
        props.tariff?.building_type ?? props.buildingTypes[0]?.value ?? '',
    category: props.tariff?.category ?? '',
    min_kwh: props.tariff?.min_kwh ?? '',
    max_kwh: props.tariff?.max_kwh ?? '',
    rate: props.tariff?.rate ?? '',
    is_initial_rate: props.tariff?.is_initial_rate ?? false,
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        min_kwh: Number(data.min_kwh),
        max_kwh:
            data.max_kwh === '' || data.max_kwh === null
                ? null
                : Number(data.max_kwh),
        rate: Number(data.rate),
    }));

    if (props.tariff) {
        form.put(update.url(props.tariff.id), {
            preserveScroll: true,
        });

        return;
    }

    form.post(store.url(), {
        preserveScroll: true,
    });
};

const updateBuildingType = (value: unknown) => {
    form.building_type = String(value);
};

const updateInitialRate = (value: boolean | 'indeterminate') => {
    form.is_initial_rate = value === true;
};
</script>

<template>
    <Card class="max-w-2xl">
        <CardHeader>
            <CardTitle>{{
                isEditing ? 'Tariff details' : 'New tariff'
            }}</CardTitle>
            <CardDescription>
                {{
                    isEditing
                        ? 'Update the tariff tier. Tiers for the same building type must not overlap.'
                        : 'Add a progressive tariff tier for a building type.'
                }}
            </CardDescription>
        </CardHeader>

        <CardContent>
            <form class="grid gap-6" @submit.prevent="submit">
                <div class="grid gap-2">
                    <Label for="building_type">Building type</Label>
                    <Select
                        :model-value="form.building_type"
                        @update:model-value="updateBuildingType"
                    >
                        <SelectTrigger id="building_type" class="w-full">
                            <SelectValue placeholder="Select a type" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="type in buildingTypes"
                                :key="type.value"
                                :value="type.value"
                            >
                                {{ type.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.building_type" />
                </div>

                <div class="grid gap-2">
                    <Label for="category">Category</Label>
                    <Input
                        id="category"
                        v-model="form.category"
                        name="category"
                        placeholder="e.g. For the first 100 kWh"
                        required
                    />
                    <InputError :message="form.errors.category" />
                </div>

                <div class="grid gap-6 sm:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="min_kwh">Minimum kWh</Label>
                        <Input
                            id="min_kwh"
                            v-model="form.min_kwh"
                            type="number"
                            name="min_kwh"
                            min="0"
                            step="1"
                            placeholder="0"
                            required
                        />
                        <InputError :message="form.errors.min_kwh" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="max_kwh">Maximum kWh</Label>
                        <Input
                            id="max_kwh"
                            v-model="form.max_kwh"
                            type="number"
                            name="max_kwh"
                            min="0"
                            step="1"
                            placeholder="Leave empty for no upper limit"
                        />
                        <p class="text-sm text-muted-foreground">
                            Leave empty when this tier has no upper limit.
                        </p>
                        <InputError :message="form.errors.max_kwh" />
                    </div>
                </div>

                <div class="grid gap-2">
                    <Label for="rate">Rate (RM per kWh)</Label>
                    <Input
                        id="rate"
                        v-model="form.rate"
                        type="number"
                        name="rate"
                        min="0"
                        step="0.0001"
                        placeholder="0.0000"
                        required
                    />
                    <InputError :message="form.errors.rate" />
                </div>

                <div class="flex items-start gap-3">
                    <Checkbox
                        id="is_initial_rate"
                        :model-value="form.is_initial_rate"
                        @update:model-value="updateInitialRate"
                    />
                    <div class="grid gap-1">
                        <Label for="is_initial_rate">Initial rate</Label>
                        <p class="text-sm text-muted-foreground">
                            Mark this tier as the first tier charged for the
                            building type.
                        </p>
                        <InputError :message="form.errors.is_initial_rate" />
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <Button type="submit" :disabled="form.processing">
                        <Spinner v-if="form.processing" />
                        {{ isEditing ? 'Save changes' : 'Create tariff' }}
                    </Button>

                    <Button variant="secondary" as-child>
                        <Link :href="index()">Cancel</Link>
                    </Button>
                </div>
            </form>
        </CardContent>
    </Card>
</template>
