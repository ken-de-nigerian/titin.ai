<script setup lang="ts">
    import type { ApexOptions } from 'apexcharts';
    import { computed, onMounted, ref } from 'vue';
    import VueApexCharts from 'vue3-apexcharts';

    import { CHART_PALETTE_HEX } from '@/constants/chartPalette';

    const props = defineProps<{
        points: Array<{ ended_at: string | null; score: number | null }>;
    }>();

    const clientReady = ref(false);

    onMounted(() => {
        clientReady.value = true;
    });

    const series = computed(() => {
        const data = props.points
            .map((p) => {
                if (!p.ended_at) {
                    return null;
                }

                const x = Date.parse(p.ended_at);

                if (!Number.isFinite(x)) {
                    return null;
                }

                return { x, y: p.score };
            })
            .filter((d): d is { x: number; y: number | null } => d !== null);

        return [{ name: 'Score', data }];
    });

    const chartOptions = computed((): ApexOptions => ({
        chart: {
            type: 'line',
            fontFamily: 'inherit',
            toolbar: { show: false },
            zoom: { enabled: false },
            animations: { enabled: true },
            foreColor: '#64748b',
        },
        colors: [CHART_PALETTE_HEX[0]],
        stroke: {
            curve: 'smooth',
            width: 2.5,
        },
        markers: {
            size: 4,
            strokeWidth: 2,
            strokeColors: CHART_PALETTE_HEX[0],
            colors: ['#ffffff'],
            hover: { size: 6 },
        },
        dataLabels: { enabled: false },
        grid: {
            borderColor: 'rgba(15, 23, 42, 0.08)',
            strokeDashArray: 4,
            padding: { left: 4, right: 12, top: 8, bottom: 4 },
        },
        xaxis: {
            type: 'datetime',
            labels: {
                datetimeUTC: false,
                style: { fontSize: '10px' },
            },
        },
        yaxis: {
            min: 0,
            max: 10,
            tickAmount: 5,
            labels: {
                formatter: (v: number) => String(Math.round(v)),
                style: { fontSize: '10px' },
            },
        },
        tooltip: {
            theme: 'light',
            x: {
                format: 'MMM d, yyyy · HH:mm',
            },
            y: {
                formatter: (val: number | undefined) => (val !== undefined && val !== null ? Number(val).toFixed(1) : '—'),
            },
        },
    }));
</script>

<template>
    <div v-if="clientReady" class="apex-chart-wrap -mx-1 mt-4 min-h-[220px] w-full">
        <VueApexCharts type="line" height="240" width="100%" :options="chartOptions" :series="series" />
    </div>
</template>

<style scoped>
    .apex-chart-wrap :deep(.apexcharts-canvas),
    .apex-chart-wrap :deep(.apexcharts-svg) {
        font-family: inherit;
    }
</style>
