<script setup lang="ts">
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { Search, X } from 'lucide-vue-next';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';

const props = defineProps<{
    modelValue: string;
    routeName: string;
    routeParams?: Record<string, any>;
    placeholder?: string;
    filters?: Record<string, any>;
}>();

const emit = defineEmits(['update:modelValue']);

const search = ref(props.modelValue || '');
let debounceTimer: ReturnType<typeof setTimeout>;

watch(search, (value) => {
    emit('update:modelValue', value);
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        router.get(
            route(props.routeName, props.routeParams || {}),
            { ...props.filters, search: value || undefined },
            { preserveState: true, replace: true }
        );
    }, 300);
});

function clear() {
    search.value = '';
}
</script>

<template>
    <div class="relative w-full max-w-sm">
        <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
        <Input
            v-model="search"
            :placeholder="placeholder || 'Search...'"
            class="pl-9 pr-9"
        />
        <button
            v-if="search"
            type="button"
            class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground"
            @click="clear"
        >
            <X class="h-4 w-4" />
        </button>
    </div>
</template>
