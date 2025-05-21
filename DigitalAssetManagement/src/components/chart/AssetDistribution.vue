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
          v-for="dept in allDepartments"
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

    <!-- Add v-if to ensure chart only renders when data is ready -->
    <apexchart
      v-if="series.length > 0"
      type="bar"
      height="300"
      :options="chartOptions"
      :series="series"
    />

    <EditAssetDept
  :visible="editModalVisible"
  @update:visible="val => editModalVisible = val"
  :departments="allDepartments"
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

const props = defineProps({
  assetData: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['update-asset'])

// Chart data preparation
const allDepartments = computed(() => props.assetData.departments || [])
const selectedDepartments = ref([])

const selectAllDepartments = () => {
  selectedDepartments.value = []
}

const filteredAssets = computed(() => {
  if (selectedDepartments.value.length === 0) {
    return props.assetData.assets || []
  }
  return (props.assetData.assets || []).filter(asset => 
    selectedDepartments.value.includes(asset.department)
  )
})

//Defining the series that will be used for the BarChart
const series = computed(() => {
  const categories = props.assetData.categories || []
  const departments = selectedDepartments.value.length > 0 
    ? selectedDepartments.value 
    : allDepartments.value

  return categories.map(category => {
    return {
      name: category,
      data: departments.map(dept => {
        return filteredAssets.value.filter(asset => 
          asset.category === category && asset.department === dept
        ).length
      })
    }
  })
})

 // Prepare BarChart Options
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
      : allDepartments.value,
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
const currentAsset = ref({})


const openEditModal = () => {
  currentAsset.value = props.assetData.assets.length > 0 
    ? {
        ...props.assetData.assets[0],
        purchaseDate: props.assetData.assets[0].purchaseDate || new Date()
      }
    : {
        name: 'New Asset',
        category: props.assetData.categories[0] || '',
        status: props.assetData.statusOptions[0] || '',
        usage: '',
        purchaseDate: new Date(),
        department: allDepartments.value[0] || '',
        value: 0
      }
  editModalVisible.value = true
}


const handleAssetUpdate = (updatedAsset) => {
  emit('update-asset', updatedAsset)
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
</style>