import { onMounted, ref } from 'vue';

/**
 * SSR-safe flag that becomes true after the component mounts on client.
 * Useful to gate Teleport/portal UI to avoid hydration mismatches.
 */
export function useHydrated() {
    const hydrated = ref(false);

    onMounted(() => {
        hydrated.value = true;
    });

    return hydrated;
}

