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
import { destroy } from '@/routes/tariffs';
import type { Tariff } from '@/types';

defineProps<{
    tariff: Tariff;
}>();
</script>

<template>
    <Dialog>
        <DialogTrigger as-child>
            <Button
                variant="ghost"
                size="icon-sm"
                :aria-label="`Delete ${tariff.category}`"
            >
                <Trash2 />
            </Button>
        </DialogTrigger>

        <DialogContent>
            <Form
                v-bind="destroy.form(tariff.id)"
                :options="{ preserveScroll: true }"
                v-slot="{ processing }"
            >
                <DialogHeader class="space-y-3">
                    <DialogTitle>Delete tariff?</DialogTitle>
                    <DialogDescription>
                        This will remove
                        <span class="font-medium text-foreground">
                            {{ tariff.category }}
                        </span>
                        from the tariff tiers. Buildings are not recalculated
                        automatically.
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
                        Delete tariff
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
