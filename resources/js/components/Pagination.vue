<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    ChevronLeft,
    ChevronRight,
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';

export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

defineProps<{
    links: PaginationLink[];
    from: number;
    to: number;
    total: number;
}>();
</script>

<template>
    <div class="flex flex-col items-center gap-4 sm:flex-row sm:justify-between">
        <p class="text-sm text-muted-foreground">
            Menampilkan <span class="font-medium">{{ from }}</span> sampai
            <span class="font-medium">{{ to }}</span> dari
            <span class="font-medium">{{ total }}</span> data
        </p>
        <div class="flex items-center gap-1" v-if="links.length > 3">
            <template v-for="(link, index) in links" :key="index">
                <Button
                    v-if="index === 0"
                    variant="outline"
                    size="icon"
                    class="h-8 w-8"
                    :disabled="!link.url"
                    as-child
                >
                    <Link v-if="link.url" :href="link.url" preserve-state>
                        <ChevronLeft class="h-4 w-4" />
                    </Link>
                    <span v-else><ChevronLeft class="h-4 w-4" /></span>
                </Button>
                <Button
                    v-else-if="index === links.length - 1"
                    variant="outline"
                    size="icon"
                    class="h-8 w-8"
                    :disabled="!link.url"
                    as-child
                >
                    <Link v-if="link.url" :href="link.url" preserve-state>
                        <ChevronRight class="h-4 w-4" />
                    </Link>
                    <span v-else><ChevronRight class="h-4 w-4" /></span>
                </Button>
                <Button
                    v-else
                    :variant="link.active ? 'default' : 'outline'"
                    size="icon"
                    class="h-8 w-8"
                    :disabled="!link.url"
                    as-child
                >
                    <Link v-if="link.url" :href="link.url" preserve-state v-html="link.label" />
                    <span v-else v-html="link.label" />
                </Button>
            </template>
        </div>
    </div>
</template>
