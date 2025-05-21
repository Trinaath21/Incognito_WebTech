<template>
  <div class="asset-value-by-dept">
    
    <!-- Edit Form Button -->
    <div class="mb-2 text-left">
      <el-button size="small" type="primary" @click="openEditForm">
        <el-icon><Edit /></el-icon>
        Edit Asset Value
      </el-button>
    </div>

    <div class="chart-container">
      <apexchart
        type="bar"
        height="240"
        :options="barChartOptions"
        :series="barChartSeries"
      />
    </div>
    
    <!-- Edit Form Dialog -->
    <edit-asset-value
      v-model:visible="editFormVisible"
      :assets="allAssets"
      :departments="allDepartments"
      @submit="handleAssetUpdate"
    />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import VueApexCharts from 'vue3-apexcharts'
import { Edit } from '@element-plus/icons-vue'
import EditAssetValue from '../modal/EditAssetValue.vue'

// Define component options
defineOptions({
  name: 'AssetValueByDept'
})

// Register ApexCharts component
defineExpose()

const props = defineProps({
  assetData: {
    type: Object,
    required: true,
  }
})

const emit = defineEmits(['update-asset'])

// State for edit form dialog
const editFormVisible = ref(false)

// Get all assets from assetData
const allAssets = computed(() => {
  return props.assetData.assets
})

// Get all departments
const allDepartments = computed(() => {
  // Extract unique departments from assets
  const deptSet = new Set()
  allAssets.value.forEach(asset => {
    if (asset.department) {
      deptSet.add(asset.department)
    }
  })
  return Array.from(deptSet)
})

// Calculate total value per department
const departmentValueData = computed(() => {
  const deptValues = {}
  
  allDepartments.value.forEach(dept => {
    // Sum all asset values for this department
    const totalValue = allAssets.value
      .filter(asset => asset.department === dept)
      .reduce((sum, asset) => sum + (Number(asset.value) || 0), 0)
    
    deptValues[dept] = totalValue
  })
  
  return deptValues
})

// Prepare bar chart series data
const barChartSeries = computed(() => {
  return [{
    name: 'Total Value',
    data: Object.values(departmentValueData.value)
  }]
})

// Prepare bar chart options
const barChartOptions = computed(() => ({
  chart: {
    type: 'bar',
    height: 240,
    animations: {
      enabled: true,
      easing: 'easeinout',
      speed: 800,
      dynamicAnimation: {
        enabled: true,
        speed: 350
      }
    }
  },
  plotOptions: {
    bar: {
      horizontal: false,
      columnWidth: '55%',
      borderRadius: 4
    },
  },
  dataLabels: {
    enabled: false
  },
  stroke: {
    show: true,
    width: 2,
    colors: ['transparent']
  },
  xaxis: {
    categories: Object.keys(departmentValueData.value),
    labels: {
      style: {
        fontSize: '12px'
      }
    }
  },
  yaxis: {
    title: {
      text: 'Value (RM)'
    },
    labels: {
      formatter: function (val) {
        return 'RM' + val.toLocaleString()
      }
    }
  },
  fill: {
    opacity: 1
  },
  tooltip: {
    y: {
      formatter: function (val) {
        return 'RM' + val.toLocaleString()
      }
    }
  },
  colors: ['#FF4560']
}))

// Open edit form dialog
const openEditForm = () => {
  editFormVisible.value = true
}

// Handle asset update from the edit form
const handleAssetUpdate = (updatedAsset) => {
  // Forward the update to parent component
  emit('update-asset', updatedAsset)
}
</script>

<style scoped>
.chart-container {
  min-height: 240px;
}
</style>