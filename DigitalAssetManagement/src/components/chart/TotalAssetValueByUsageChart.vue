<template>
  <div class="chart-container">
    <h1>Total Asset Value by Usage Type</h1>
    <button @click="showModal = true" class="edit-button">Edit Usage Type</button>
    
    <!-- ApexChart -->
    <apexchart
      type="pie"
      :options="chartOptions"
      :series="series"
      width="500"
    />

    <!-- Edit Modal  -->
    <div v-if="showModal" class="modal-overlay">
      <div class="modal-content">
        <EditUsageType
          :model-value="showModal"
          :assets="currentAssets"
          :categories="categories"
          :departments="departments"
          @update:modelValue="showModal = $event"
          @filter-assets="handleAssetUpdate"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed,  onMounted  } from 'vue';
import VueApexCharts from 'vue3-apexcharts';
import assetService from '../../api/assetService';
import EditUsageType from '../modal/EditUsageType.vue';

// Modal Control
const showModal = ref(false);


// Category/Department Data

const currentAssets = ref([]);
const categories = ref([]);
const departments = ref([]);

// Fetch from API when component mounts
onMounted(async () => {
  try {
    currentAssets.value = await assetService.getAssets();
    console.log('Assets:', currentAssets.value);
    categories.value = await assetService.getCategories();
    departments.value = await assetService.getDepartments();
  } catch (error) {
    console.error('Error fetching data:', error);
  }
});

// Chart Calculation
const usageMap = computed(() => {
  const map = {};
  currentAssets.value.forEach(asset => {
    const usage = asset.usage_type || 'Unassigned';
    const value = parseFloat(asset.value) || 0; 
    map[usage] = (map[usage] || 0) + value;
  });
  return map;
});


const series = computed(() => Object.values(usageMap.value));
const chartOptions = computed(() => ({
  labels: Object.keys(usageMap.value),
  legend: { position: 'bottom' },
  responsive: [
    {
      breakpoint: 480,
      options: {
        chart: { width: 300 },
        legend: { position: 'bottom' }
      }
    }
  ]
}));

// Asset Update Callback
const handleAssetUpdate = (updatedAssets) => {
  currentAssets.value = updatedAssets;
  showModal.value = false;
};
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
