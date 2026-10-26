<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { Trash2 } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { destroy } from '@/routes/buildings';
import type { Building } from '@/types';

defineProps<{
    building: Building;
}>();
</script>

<template>
    <Dialog>
        <DialogTrigger as-child>
            <Button
                variant="ghost"
                size="icon-sm"
                :aria-label="`Delete ${building.customer_name}`"
            >
                <Trash2 />
            </Button>
        </DialogTrigger>

        <DialogContent>
            <Form
                v-bind="destroy.form(building.id)"
                :options="{ preserveScroll: true }"
                v-slot="{ processing }"
            >
                <DialogHeader class="space-y-3">
                    <DialogTitle>Delete building?</DialogTitle>
                    <DialogDescription>
                        This will remove
                        <span class="font-medium text-foreground">
                            {{ building.customer_name }}
                        </span>
                        from the building list. This action can be undone by
                        restoring the soft-deleted record from the database.
                    </DialogDescription>
                </DialogHeader>

                <DialogFooter class="gap-2">
                    <DialogClose as-child>
                        <Button variant="secondary" type="button">
                            Cancel
                        </Button>
                    </DialogClose>

                    <Button
                        type="submit"
                        variant="destructive"
                        :disabled="processing"
                    >
                        Delete building
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
