<template>
  <div class="dashboard-container">
    <!-- Filter and Action Bar -->
    <div class="flex items-center justify-between mb-4">
      <!-- Department filter dropdown -->
      <el-select
        v-model="selectedDepartments"
        multiple
        collapse-tags
        collapse-tags-tooltip
        placeholder="All Departments"
        style="width: 50%; margin-right: 16px"
        clearable
      >
        <!-- Option to select all department -->
        <el-option
          label="All Departments"
          :value="[]"
          @click="selectedDepartments = []"
          key="all-departments"
        />
        <!-- Dynamic options for each department -->
        <el-option
          v-for="dept in allDepartments"
          :key="dept"
          :label="dept"
          :value="dept"
        />
      </el-select>

      <!-- Edit button to open edit asset status modal -->
      <el-button 
        type="primary" 
        @click="openEditModal" 
        :icon="Edit"
        :loading="loading"
      >
        Edit
      </el-button>
    </div>

    <!-- Asset Summary Table -->
    <el-table
      :data="statusSummary"
      stripe
      border
      class="black-text-table mt-6"
      style="width: 100%; table-layout: auto"
      v-loading="loading"
    >
      <el-table-column prop="status" label="Status" />
      <el-table-column prop="count" label="Total Assets" />
    </el-table>

    <!-- Donut Chart -->
    <div style="margin-top: 30px; width: 100%; display: flex; justify-content: center">
      <apexchart
        type="donut"
        :options="chartOptions"
        :series="chartSeries"
        height="400"
      />
    </div>

    <!-- Edit Modal shown when edit button is clicked -->
    <EditAssetStatus
      v-model="showEditModal"
      :assets="assets"
      :departments="departments"
      :categories="categories"
      :usage-types="usageTypes"
      @refresh="fetchAssets"
      @update-assets="handleAssetUpdate"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Edit } from '@element-plus/icons-vue'
import ApexCharts from 'vue3-apexcharts'
import EditAssetStatus from '../modal/EditAssetStatus.vue'
import assetService from '../../api/assetService'

// Reactive state variables
const showEditModal = ref(false)
const loading = ref(false)
const assets = ref([])
const departments = ref([])
const categories = ref([])
const usageTypes = ref([])
const selectedDepartments = ref([])

// Fetches assets, departments, and categories from the API
const fetchAssets = async () => {
  loading.value = true
  try {
    assets.value = await assetService.getAssets()
    departments.value = await assetService.getDepartments()
    categories.value = await assetService.getCategories()
  } catch (err) {
    console.error('Failed to load data:', err)
  } finally {
    loading.value = false
  }
}

// Open modal with fresh data
const openEditModal = async () => {
  loading.value = true
  try {
    await fetchAssets()
    showEditModal.value = true
  } catch (err) {
    console.error('Failed to refresh data:', err)
  } finally {
    loading.value = false
  }
}

// Computed properties
// Gets unique list of all departments from assets
const allDepartments = computed(() => 
  [...new Set(assets.value.map(asset => asset.department))].filter(Boolean)
)

// Filters assets based on selected departments
const filteredAssets = computed(() => 
  selectedDepartments.value.length === 0
    ? assets.value
    : assets.value.filter(asset => 
        selectedDepartments.value.includes(asset.department)
      )
)

// Calculates counts for each asset status
const calculateStatusCounts = () => {
  const counts = {
    'In Use': 0,
    'Storage': 0,
    'Under Repair': 0,
    'Disposal': 0
  }

  filteredAssets.value.forEach(asset => {
    const status = asset.status || 'Unknown'
    if (counts[status] !== undefined) counts[status]++
  })

  return counts
}

// Formats status counts for display in table
const statusSummary = computed(() => 
  Object.entries(calculateStatusCounts()).map(([status, count]) => ({
    status,
    count
  }))
)

// Provides data series for the donut chart
const chartSeries = computed(() => {
  const counts = calculateStatusCounts()
  // Get all unique statuses from the counts object
  const allStatuses = Object.keys(counts)
  /*return [
    counts['In Use'],
    counts['Storage'],
    counts['Under Repair'],
    counts['Disposal']
  ]*/
 // Return counts in the order of the statuses
 return allStatuses.map(status => counts[status])

})

// Configuration options for the donut chart
const chartOptions = computed(() => ({
  chart: {
    id: 'asset-status-donut-chart',
    toolbar: { show: false }  // Hide chart toolbar
  },
  labels: Object.keys(calculateStatusCounts()), // Dynamic labels
  
  //labels: ['In Use', 'Storage', 'Under Repair', 'Disposal'],
  plotOptions: {
    pie: {
      donut: {
        size: '60%'  // Size of the donut hole
      }
    }
  },
  colors: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0'],
  responsive: [{
    breakpoint: 600,
    options: {
      chart: { width: '100%' },
      legend: { position: 'bottom' }
    }
  }]
}))

// Handles asset updates from the modal
const handleAssetUpdate = async () => {
  try {
    await fetchAssets()   // Refresh data after update
  } catch (error) {
    console.error('Failed to refresh assets:', error)
  }
}

// Initial load
onMounted(fetchAssets)
</script>

<script>
// Component registration (needed for apexcharts)
export default {
  components: {
    apexchart: ApexCharts
  }
}
</script>

<style scoped>
.dashboard-container {
  padding: 20px;
}

.mt-6 {
  margin-top: 1.5rem;
}

/* Custom table styling */
.black-text-table :deep(.el-table__cell) {
  color: #444444 !important;
}

.black-text-table :deep(th.el-table__cell) {
  font-weight: bold;
  color: #5c5858;
}
</style>
