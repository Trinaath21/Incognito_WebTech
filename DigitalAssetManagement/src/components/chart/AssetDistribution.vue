<template>
  <div class="asset-distribution">
    <div class="chart-header flex justify-between items-center mb-4">
      <el-select
        v-model="selectedDepartments"
        multiple
        collapse-tags
        collapse-tags-tooltip
        placeholder="Select departments"
        style="width: 50%;"
      >
        <el-option
          label="All Departments"
          :value="[]"
          @click="selectAllDepartments"
        />
        <el-option
          v-for="dept in uniqueDepartments"
          :key="dept"
          :label="dept"
          :value="dept"
        />
      </el-select>
      <el-button 
        type="primary" 
        @click="openEditModal"
        :icon="Edit"
      >
        Edit Dept Data
      </el-button>
    </div>


    <apexchart
      v-if="series.length > 0 && uniqueDepartments.length > 0"
      type="bar"
      height="300"
      :options="chartOptions"
      :series="series"
    />
    
    <div v-else class="no-data-message text-center py-8 text-gray-500">
      <p>No data available to display chart</p>
      <el-button @click="debugMode = !debugMode" size="small" type="info">
        {{ debugMode ? 'Hide' : 'Show' }} Debug Info
      </el-button>
    </div>

    <EditAssetDept
      :visible="editModalVisible"
      @update:visible="val => editModalVisible = val"
      :departments="uniqueDepartments"
      :status-options="assetData.statusOptions"
      :assets="assetData.assets" 
      @submit="handleAssetUpdate"
    />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Edit } from '@element-plus/icons-vue'
import EditAssetDept from '../modal/EditAssetDept.vue'
import { ElNotification } from 'element-plus'

const props = defineProps({
  assetData: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['update-asset'])

// Debug mode for troubleshooting
const debugMode = ref(false)

// Get unique departments from assets or departments array
const uniqueDepartments = computed(() => {
  // If departments is already an array of unique strings (from API)
  if (props.assetData.departments && 
      Array.isArray(props.assetData.departments) && 
      props.assetData.departments.length > 0 && 
      typeof props.assetData.departments[0] === 'string') {
    return props.assetData.departments.filter(dept => dept && dept.trim() !== '');
  }
  
  // Extract unique departments from assets
  if (props.assetData.assets && Array.isArray(props.assetData.assets)) {
    const depts = [...new Set(
      props.assetData.assets
        .map(asset => asset.department)
        .filter(dept => dept && dept.trim() !== '')
    )];
    return depts.sort();
  }
  
  return [];
})

const selectedDepartments = ref([])

const selectAllDepartments = () => {
  selectedDepartments.value = []
}

const filteredAssets = computed(() => {
  if (!props.assetData.assets || !Array.isArray(props.assetData.assets)) {
    return []
  }
  
  if (selectedDepartments.value.length === 0) {
    return props.assetData.assets
  }
  
  return props.assetData.assets.filter(asset => 
    selectedDepartments.value.includes(asset.department)
  )
})

const series = computed(() => {
  // Extract category names from the category objects
  const categories = props.assetData.categories?.map(cat => cat.name) || []
  const departments = selectedDepartments.value.length > 0 
    ? selectedDepartments.value 
    : uniqueDepartments.value

  if (categories.length === 0 || departments.length === 0) {
    return []
  }

  return categories.map(categoryName => {
    return {
      name: categoryName,
      data: departments.map(dept => {
        const count = filteredAssets.value.filter(asset => 
          asset.category_name === categoryName && asset.department === dept
        ).length
        return count
      })
    }
  })
})

const chartOptions = computed(() => ({
  chart: {
    type: 'bar',
    stacked: false,
    toolbar: {
      show: false
    }
  },
  plotOptions: {
    bar: {
      horizontal: false,
      columnWidth: '70%',
      endingShape: 'rounded'
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
    categories: selectedDepartments.value.length > 0 
      ? selectedDepartments.value 
      : uniqueDepartments.value,
    title: {
      text: 'Departments'
    }
  },
  yaxis: {
    title: {
      text: 'Number of Assets'
    },
    min: 0,
    forceNiceScale: true
  },
  fill: {
    opacity: 1
  },
  tooltip: {
    y: {
      formatter: function (val) {
        return val + " assets"
      }
    }
  },
  colors: ['#008FFB', '#00E396', '#FEB019', '#FF4560', '#775DD0', '#3F51B5'],
  legend: {
    position: 'top',
    horizontalAlign: 'center'
  }
}))

// Edit modal functionality
const editModalVisible = ref(false)

const openEditModal = () => {
  if (!props.assetData.assets || props.assetData.assets.length === 0) {
    ElNotification({
      title: 'Warning',
      message: 'No assets available to edit',
      type: 'warning'
    })
    return
  }
  editModalVisible.value = true
}

const handleAssetUpdate = async (updatedAsset) => {
  try {
    await emit('update-asset', updatedAsset)
    editModalVisible.value = false
  } catch (error) {
    console.error('Error updating asset:', error)
    ElNotification({
      title: 'Error',
      message: 'Failed to update asset department',
      type: 'error'
    })
  }
}
</script>

<style scoped>
.asset-distribution {
  width: 100%;
  height: 100%;
}

.chart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.debug-info {
  border: 1px solid #ddd;
  border-radius: 4px;
}

.no-data-message {
  min-height: 200px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
}
</style>