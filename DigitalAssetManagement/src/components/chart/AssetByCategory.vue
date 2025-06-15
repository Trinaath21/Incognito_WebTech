<template>
  <div class="asset-by-category">
    <!-- PieChart Container -->
    <div class="chart-container h-full flex items-center justify-center bg-gray-100 rounded">
      <apexchart
        v-if="pieChartSeries.length > 0"
        type="pie"
        height="350"
        :options="pieChartOptions"
        :series="pieChartSeries"
      />
      <div v-else class="text-gray-500 p-4">
        No data available for the chart
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import VueApexCharts from 'vue3-apexcharts'

defineExpose()
defineOptions({
  components: {
    apexchart: VueApexCharts
  }
})

const props = defineProps({
  assetData: {
    type: Object,
    required: true,
  }
})

// Categories and assets
const allCategories = computed(() => {
  const cat = props.assetData.categories?.value || props.assetData.categories || []
  return cat
})

const allAssets = computed(() => {
  return props.assetData.assets || []
})

// Prepare labels from category names
const chartLabels = computed(() => allCategories.value.map(c => c.name))

// Prepare series (count of assets per category_id)
const pieChartSeries = computed(() =>
  allCategories.value.map(category =>
    allAssets.value.filter(asset => asset.category_id === category.id).length
  )
)

// ApexChart options
const pieChartOptions = computed(() => ({
  chart: {
    type: 'pie',
  },
  labels: chartLabels.value,
  colors: ['#008FFB', '#00E396', '#FEB019', '#FF4560', '#775DD0', '#3F51B5'],
  tooltip: {
    y: {
      formatter: val => `${val} assets`
    }
  },
  legend: {
    position: 'bottom'
  }
}))
</script>



<style scoped>
.chart-container {
  min-height: 400px;
  width: 100%;
}

.asset-by-category {
  width: 100%;
  height: 100%;
}
</style>