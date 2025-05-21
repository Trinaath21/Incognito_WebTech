<template>
  <div class="chart-container">
    <h1>Total Asset Value by Usage Type</h1>

    <!-- Edit button -->
    <button @click="showModal = true" class="edit-button">Edit Usage Type</button>

    <!-- ApexChart displaying asset values grouped by usage type -->
    <apexchart
      type="pie"
      :options="chartOptions"
      :series="series"
      width="500"
    />

    <!-- Modal component for editing asset usage types -->
    <div v-if="showModal">
      <div class="modal-content">
        <EditUsageType
          :assets="currentAssets"
          @update="handleAssetUpdate"
          @close="showModal = false"
        />
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed } from 'vue'
import VueApexCharts from 'vue3-apexcharts'
import { assets } from '../../data/assetData'
import EditUsageType from '../modal/EditUsageType.vue'

export default {
  name: 'AssetValueChart',
  components: {
    apexchart: VueApexCharts,
    EditUsageType
  },
  setup() {
    // Modal visibility state
    const showModal = ref(false)

    // Local reactive copy of the asset list
    const currentAssets = ref([...assets])

    // Computed map of total value per usage type
    const usageMap = computed(() => {
      const map = {}
      currentAssets.value.forEach(asset => {
        const usage = asset.usage || 'Unassigned'
        map[usage] = (map[usage] || 0) + (asset.value || 0)
      })
      return map
    })

    // Chart series values
    const series = computed(() => Object.values(usageMap.value))

    // ApexChart options including dynamic labels
    const chartOptions = computed(() => ({
      labels: Object.keys(usageMap.value),
      legend: {
        position: 'bottom'
      },
      responsive: [{
        breakpoint: 480,
        options: {
          chart: {
            width: 300
          },
          legend: {
            position: 'bottom'
          }
        }
      }]
    }))

    // Called when modal emits updated asset data
    const handleAssetUpdate = (updatedAssets) => {
      currentAssets.value = updatedAssets
      showModal.value = false
    }

    return {
      chartOptions,
      series,
      showModal,
      currentAssets,
      handleAssetUpdate
    }
  }
}
</script>

<style scoped>
.chart-container {
  padding: 20px;
}
h1 {
  margin-bottom: 10px;
}

.edit-button {
  margin-bottom: 20px;
  padding: 8px 16px;
  background-color: #3b82f6;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.3s;
}
.edit-button:hover {
  background-color: #2563eb;
}

/* Modal overlay background */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background-color: rgba(0, 0, 0, 0.6);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

/* Modal container styling */
.modal-content {
  background: white;
  padding: 30px;
  border-radius: 10px;
  width: 80vw;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
}
</style>
