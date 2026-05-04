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
            /** Default datetime tooltips repeat the timestamp (title + series row). Custom = one clear block. */
            custom: ({
                series,
                seriesIndex,
                dataPointIndex,
                w,
            }: {
                series: number[][];
                seriesIndex: number;
                dataPointIndex: number;
                w: { globals: { seriesX: number[][] } };
            }) => {
                const yVal = series?.[seriesIndex]?.[dataPointIndex];
                const xVal = w.globals.seriesX[seriesIndex]?.[dataPointIndex];

                if (xVal === undefined) {
                    return '';
                }

                const scoreText =
                    yVal === undefined || yVal === null || Number.isNaN(Number(yVal)) ? '—' : Number(yVal).toFixed(1);
                const when = new Intl.DateTimeFormat(undefined, {
                    month: 'short',
                    day: 'numeric',
                    year: 'numeric',
                    hour: 'numeric',
                    minute: '2-digit',
                }).format(new Date(xVal));

                return `<div class="apexcharts-tooltip-custom px-3 py-2 text-xs leading-snug">
                    <div><span class="font-semibold tabular-nums">${scoreText}</span><span class="text-slate-500"> / 10</span><span class="text-slate-400"> · </span><span class="text-slate-600">${when}</span></div>
                </div>`;
            },
        },
    }));
</script>

<template>
    <div v-if="clientReady" class="apex-chart-wrap -mx-1 mt-4 min-h-55 w-full">
        <VueApexCharts type="line" height="240" width="100%" :options="chartOptions" :series="series" />
    </div>
</template>

<style scoped>
    .apex-chart-wrap :deep(.apexcharts-canvas),
    .apex-chart-wrap :deep(.apexcharts-svg) {
        font-family: inherit;
    }
</style>
