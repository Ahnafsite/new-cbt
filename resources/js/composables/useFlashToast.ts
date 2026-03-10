import { usePage } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import { watch, computed } from 'vue';

export function useFlashToast() {
    const page = usePage();

    const flash = computed(() => page.props.flash as Record<string, string | null> | undefined);

    // Watch each flash type individually using computed getters for reactivity
    watch(() => flash.value?.success, (msg) => {
        if (msg) toast.success(msg);
    });

    watch(() => flash.value?.error, (msg) => {
        if (msg) toast.error(msg);
    });

    watch(() => flash.value?.warning, (msg) => {
        if (msg) toast.warning(msg);
    });

    watch(() => flash.value?.info, (msg) => {
        if (msg) toast.info(msg);
    });
}
