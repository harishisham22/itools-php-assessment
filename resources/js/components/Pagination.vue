<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { buttonVariants } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import type { Paginated } from '@/types';

const props = defineProps<{
    paginator: Paginated<unknown>;
}>();

const pageLinks = computed(() => props.paginator.links.slice(1, -1));
</script>

<template>
    <div
        class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
    >
        <p v-if="paginator.total === 0" class="text-sm text-muted-foreground">
            No results.
        </p>
        <p v-else class="text-sm text-muted-foreground">
            Showing {{ paginator.from }}–{{ paginator.to }} of
            {{ paginator.total }}
        </p>

        <div
            v-if="paginator.last_page > 1"
            class="flex flex-wrap items-center gap-1"
        >
            <Link
                v-if="paginator.prev_page_url"
                :href="paginator.prev_page_url"
                preserve-scroll
                :class="cn(buttonVariants({ variant: 'outline', size: 'sm' }))"
            >
                Previous
            </Link>
            <span
                v-else
                aria-disabled="true"
                :class="
                    cn(
                        buttonVariants({ variant: 'outline', size: 'sm' }),
                        'pointer-events-none opacity-50',
                    )
                "
            >
                Previous
            </span>

            <template v-for="(link, index) in pageLinks" :key="index">
                <Link
                    v-if="link.url"
                    :href="link.url"
                    preserve-scroll
                    :aria-current="link.active ? 'page' : undefined"
                    :class="
                        cn(
                            buttonVariants({
                                variant: link.active ? 'default' : 'outline',
                                size: 'sm',
                            }),
                            'min-w-8',
                        )
                    "
                >
                    {{ link.label }}
                </Link>
                <span
                    v-else
                    aria-disabled="true"
                    :class="
                        cn(
                            buttonVariants({ variant: 'outline', size: 'sm' }),
                            'pointer-events-none min-w-8',
                        )
                    "
                >
                    {{ link.label }}
                </span>
            </template>

            <Link
                v-if="paginator.next_page_url"
                :href="paginator.next_page_url"
                preserve-scroll
                :class="cn(buttonVariants({ variant: 'outline', size: 'sm' }))"
            >
                Next
            </Link>
            <span
                v-else
                aria-disabled="true"
                :class="
                    cn(
                        buttonVariants({ variant: 'outline', size: 'sm' }),
                        'pointer-events-none opacity-50',
                    )
                "
            >
                Next
            </span>
        </div>
    </div>
</template>
