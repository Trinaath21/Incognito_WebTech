<template>
    <div class="asset-by-category">
        <!-- PieChart -->
        <div class="h-full flex items-center justify-center bg-gray-100 rounded">
          <apexchart
            type="pie"
            height="240%"
            :options="pieChartOptions"
            :series="pieChartSeries"
          />
        </div>
    </div>
  </template>
  
  <script setup>
  import { ref, computed } from 'vue'
  import VueApexCharts from 'vue3-apexcharts'
  
  // Registering ApexCharts component
  defineExpose()
  defineOptions({
    components: {
      apexchart: VueApexCharts
    }
  })
  
  // Setting up the props property to receive the assetData from Dashboard component(parent)
  const props = defineProps({
    assetData: {
      type: Object,
      required: true,
    }
  })
  
  // Get all categories from assetData
  const allCategories = computed(() => {
    const cat = props.assetData.categories
    return cat?.value ? cat.value : cat
  })
  
  // Get all assets from assetData
  const allAssets = computed(() => {
    return props.assetData.assets
  })
  
  // Prepare PieChart Data - Total assets per category
  const pieChartSeries = computed(() => {
    return allCategories.value.map(category => {
      return allAssets.value.filter(asset => asset.category === category).length
    })
  })
  
  // Prepare PieChart Options
  const pieChartOptions = computed(() => ({
    chart: {
      type: 'pie',
    },
    labels: allCategories.value,
    colors: ['#008FFB', '#00E396', '#FEB019', '#FF4560', '#775DD0', '#3F51B5'],
    tooltip: {
      y: {
        formatter: function (val) {
          return val + " assets"
        }
      }
    }
  }))
  </script>
  
  <style scoped>
  .el-card {
    max-width: 100%;
    margin: 20px 0;
  }
  </style>
  