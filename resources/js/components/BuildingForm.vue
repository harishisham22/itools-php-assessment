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
import { formatCurrency } from '@/lib/format';
import { index, store, update } from '@/routes/buildings';
import type { Building, BuildingFormData, SelectOption } from '@/types';

const props = withDefaults(
    defineProps<{
        building?: Building | null;
        types: SelectOption[];
        states: SelectOption[];
        months: SelectOption<number>[];
    }>(),
    {
        building: null,
    },
);

const isEditing = computed(() => props.building !== null);

const form = useForm<BuildingFormData>({
    customer_name: props.building?.customer_name ?? '',
    type: props.building?.type ?? props.types[0]?.value ?? '',
    month: props.building?.month ?? new Date().getMonth() + 1,
    usability: props.building?.usability ?? '',
    state: props.building?.state ?? props.states[0]?.value ?? '',
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        month: Number(data.month),
        usability: Number(data.usability),
    }));

    if (props.building) {
        form.put(update.url(props.building.id), {
            preserveScroll: true,
        });

        return;
    }

    form.post(store.url(), {
        preserveScroll: true,
    });
};

const updateType = (value: unknown) => {
    form.type = String(value);
};

const updateState = (value: unknown) => {
    form.state = String(value);
};

const updateMonth = (value: unknown) => {
    form.month = Number(value);
};
</script>

<template>
    <Card class="max-w-2xl">
        <CardHeader>
            <CardTitle>{{
                isEditing ? 'Building details' : 'New building'
            }}</CardTitle>
            <CardDescription>
                {{
                    isEditing
                        ? 'Update the building details and save to recalculate the bill.'
                        : 'Record a building and calculate its monthly electricity bill.'
                }}
            </CardDescription>
        </CardHeader>

        <CardContent>
            <form class="grid gap-6" @submit.prevent="submit">
                <div class="grid gap-2">
                    <Label for="customer_name">Customer name</Label>
                    <Input
                        id="customer_name"
                        v-model="form.customer_name"
                        name="customer_name"
                        placeholder="e.g. Ahmad"
                        autocomplete="name"
                        required
                    />
                    <InputError :message="form.errors.customer_name" />
                </div>

                <div class="grid gap-2">
                    <Label for="type">Building type</Label>
                    <Select
                        :model-value="form.type"
                        @update:model-value="updateType"
                    >
                        <SelectTrigger id="type" class="w-full">
                            <SelectValue placeholder="Select a type" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="type in types"
                                :key="type.value"
                                :value="type.value"
                            >
                                {{ type.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.type" />
                </div>

                <div class="grid gap-2">
                    <Label for="state">State</Label>
                    <Select
                        :model-value="form.state"
                        @update:model-value="updateState"
                    >
                        <SelectTrigger id="state" class="w-full">
                            <SelectValue placeholder="Select a state" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="state in states"
                                :key="state.value"
                                :value="state.value"
                            >
                                {{ state.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.state" />
                </div>

                <div class="grid gap-6 sm:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="month">Month</Label>
                        <Select
                            :model-value="form.month"
                            @update:model-value="updateMonth"
                        >
                            <SelectTrigger id="month" class="w-full">
                                <SelectValue placeholder="Select a month" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="month in months"
                                    :key="month.value"
                                    :value="month.value"
                                >
                                    {{ month.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="form.errors.month" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="usability">Usage (kWh)</Label>
                        <Input
                            id="usability"
                            v-model="form.usability"
                            type="number"
                            name="usability"
                            min="0"
                            step="0.01"
                            placeholder="0.00"
                            required
                        />
                        <InputError :message="form.errors.usability" />
                    </div>
                </div>

                <div
                    v-if="isEditing && building"
                    class="rounded-lg border bg-muted/50 p-4"
                >
                    <div class="flex items-center justify-between gap-4">
                        <p class="text-sm font-medium">Current bill</p>
                        <p class="text-sm font-semibold">
                            {{ formatCurrency(building.bill) }}
                        </p>
                    </div>
                    <p class="mt-1 text-sm text-muted-foreground">
                        The bill is recalculated from the selected type and
                        usage when the building is saved.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <Button type="submit" :disabled="form.processing">
                        <Spinner v-if="form.processing" />
                        {{ isEditing ? 'Save changes' : 'Create building' }}
                    </Button>

                    <Button variant="secondary" as-child>
                        <Link :href="index()">Cancel</Link>
                    </Button>
                </div>
            </form>
        </CardContent>
    </Card>
</template>
