<template>
    <div class="asset-breakdown">
        <div class="chart-header flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Assets by Purchase Year</h3>
            <el-button type="primary" @click="openEditModal" :icon="Edit" class="update-button">
                Update Purchase Date
            </el-button>
        </div>

        <div v-if="series.length === 0" class="error-message">
            No data available for the chart. Check assetData.assets purchase dates.
        </div>
        <apexchart v-if="series.length > 0" type="bar" height="300" :options="chartOptions" :series="series"
            class="chart-container" />
        <EditAssetDate :visible="editModalVisible" @update:visible="val => editModalVisible = val"
            :assets="props.assetData.assets" @submit="handleAssetUpdate" />
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { Edit } from '@element-plus/icons-vue'
import VueApexCharts from 'vue3-apexcharts'
import EditAssetDate from '../modal/EditAssetDate.vue' // Change to '../../modal/EditAssetDate.vue' if in src/modal/

const props = defineProps({
    assetData: {
        type: Object,
        required: true,
    },
})

const emit = defineEmits(['update-asset'])

const purchaseYears = computed(() => {
    const years = new Set(
        props.assetData.assets
            .filter(asset => asset.purchaseDate)
            .map(asset => new Date(asset.purchaseDate).getFullYear())
    )
    return [...years].sort()
})

const series = computed(() => [
    {
        name: 'Assets',
        data: purchaseYears.value.map(year =>
            props.assetData.assets.filter(
                asset => asset.purchaseDate && new Date(asset.purchaseDate).getFullYear() === year
            ).length
        ),
    },
])

const chartOptions = computed(() => ({
    chart: {
        type: 'bar',
        toolbar: {
            show: false,
        },
    },
    plotOptions: {
        bar: {
            horizontal: false,
            columnWidth: '50%',
            endingShape: 'rounded',
        },
    },
    dataLabels: {
        enabled: false,
    },
    xaxis: {
        categories: purchaseYears.value,
        title: {
            text: 'Purchase Year',
            style: {
                fontSize: '14px',
                fontWeight: 'bold',
                color: '#333',
            },
        },
        labels: {
            style: {
                fontSize: '12px',
                colors: '#666',
            },
        },
    },
    yaxis: {
        title: {
            text: 'Number of Assets',
            style: {
                fontSize: '14px',
                fontWeight: 'bold',
                color: '#333',
            },
        },
        labels: {
            style: {
                fontSize: '12px',
                colors: '#666',
            },
        },
        min: 0,
        forceNiceScale: true,
    },
    fill: {
        opacity: 1,
    },
    tooltip: {
        custom: ({ series, seriesIndex, dataPointIndex }) => {
            const year = purchaseYears.value[dataPointIndex]
            const assetsInYear = props.assetData.assets
                .filter(asset => asset.purchaseDate && new Date(asset.purchaseDate).getFullYear() === year)
                .map(asset => asset.name)
            return `
          <div class="custom-tooltip">
            <div class="tooltip-header">
              <strong>${year}</strong>
              <span>${series[seriesIndex][dataPointIndex]} assets</span>
            </div>
            <ul>
              ${assetsInYear.map(name => `<li>${name}</li>`).join('')}
            </ul>
          </div>
        `
        },
    },
    colors: ['#008FFB'],
}))

// Debug data
watch(series, (newSeries) => {
    console.log('Series:', newSeries)
    console.log('Purchase Years:', purchaseYears.value)
    console.log('Assets:', props.assetData.assets)
    console.log('CSS Debug: .custom-tooltip font set to Arial')
})

const editModalVisible = ref(false)

const openEditModal = () => {
    editModalVisible.value = true
}

const handleAssetUpdate = updatedAsset => {
    emit('update-asset', updatedAsset)
}
</script>

<style scoped>
/* Cache-busting comment: Updated 2025-05-04 */
.asset-breakdown {
    width: 100%;
    height: 100%;
    padding: 10px;
    background: #f9fafb;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.chart-header {
    padding: 0 10px;
}

.update-button {
    transition: all 0.3s ease;
}

.update-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 123, 255, 0.3);
}

.chart-container {
    background: #fff;
    border-radius: 6px;
    padding: 10px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.error-message {
    color: #e3342f;
    font-size: 14px;
    text-align: center;
    padding: 10px;
    background: #fef2f2;
    border-radius: 4px;
    margin-bottom: 10px;
}
</style>

<style>
/* Global style for tooltip to ensure it applies */
.custom-tooltip {
    padding: 10px 12px;
    background: #e9eff8;
    border-radius: 4px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    font-size: 13px;
    font-family: 'Arial', sans-serif;
    max-width: 250px;
}

.tooltip-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.tooltip-header strong {
    font-size: 14px;
    font-weight: 600;
    font-family: 'Arial', sans-serif;
    color: #000;
}

.tooltip-header span {
    font-size: 13px;
    font-family: 'Arial', sans-serif;
    color: #000;
}

.custom-tooltip ul {
    margin: 0;
    padding: 0;
    list-style: none;
}

.custom-tooltip li {
    position: relative;
    padding-left: 16px;
    font-size: 13px;
    font-family: 'Arial', sans-serif;
    color: #000;
    line-height: 1.5;
    margin-bottom: 4px;
}

.custom-tooltip li::before {
    content: '';
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    width: 8px;
    height: 8px;
    background: #aa0055;
    /* Pink color for the bullet */
    border-radius: 50%;
}
</style>