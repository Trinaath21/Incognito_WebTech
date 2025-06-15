<script setup>
import {
  Document,
  Tools,
  Money,
  OfficeBuilding,
  Collection,
  Plus,
  Edit
} from '@element-plus/icons-vue';
import '../assets/dashboard.css';
import VueApexCharts from 'vue3-apexcharts'
import AssetDistribution from './chart/AssetDistribution.vue'
import AssetByCategory from './chart/AssetByCategory.vue'
import AssetStatusTable from './chart/AssetStatusTable.vue'
import AssetBreakdown from './chart/AssetBreakdown.vue'
import AssetValueByDept from './chart/AssetValueByDept.vue'
import WarrantyExpiryTable from './chart/WarrantyExpiryTable.vue'
import TotalAssetValueByUsageChart from './chart/TotalAssetValueByUsageChart.vue'
import Sidebar from './Sidebar.vue'
import { ElNotification } from 'element-plus'
import { computed } from 'vue'

const apexchart = VueApexCharts

const props = defineProps({
  assetData: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['update-asset','update-assetDept','update-assetValue','update-assetWarranty'])

const handleAssetDeptUpdate = async (updatedAsset) => {
  try {
    await emit('update-assetDept', updatedAsset)
  } catch (error) {
    console.error('Error handling asset update:', error)
    ElNotification({
      title: 'Error',
      message: 'Failed to update asset department',
      type: 'error'
    })
  }
}

const handleAssetValueUpdate = async (updatedAsset) => {
  console.log('=== DASHBOARD RECEIVED UPDATE ===')
  console.log('Updated Asset from Form:', updatedAsset)
  try {
    await emit('update-assetValue', updatedAsset)
    console.log('✅ Dashboard successfully passed update to App.vue')
  } catch (error) {
    console.error('Error handling asset value update:', error)
    ElNotification({
      title: 'Error',
      message: 'Failed to update asset value',
      type: 'error'
    })
  }
}

const handleAssetWarrantyUpdate = async (updatedAsset) => {
  console.log('=== DASHBOARD RECEIVED UPDATE ===')
  console.log('Updated Asset from Form:', updatedAsset)
  try {
    await emit('update-assetWarranty', updatedAsset)
    console.log('Dashboard successfully passed update to App.vue')
  } catch (error) {
    console.error('Error handling asset value update:', error)
    ElNotification({
      title: 'Error',
      message: 'Failed to update warranty value',
      type: 'error'
    })
  }
}

const handleAssetUpdate = async (updatedAsset) => {
  console.log('Dashboard received update-asset:', updatedAsset)
  try {
    await emit('update-assetDate', updatedAsset) // Changed to update-assetDate
    console.log('Dashboard passed update-assetDate to App.vue')
  } catch (error) {
    console.error('Error handling asset update:', error)
    ElNotification({
      title: 'Error',
      message: 'Failed to update asset purchase date',
      type: 'error'
    })
  }
}

// Computed property to get unique departments count
const uniqueDepartmentsCount = computed(() => {
  if (!props.assetData.departments) return 0;
  
  // If departments is an array of unique department names (from API)
  if (Array.isArray(props.assetData.departments) && 
      props.assetData.departments.length > 0 && 
      typeof props.assetData.departments[0] === 'string') {
    return props.assetData.departments.length;
  }
  
  // If departments contains asset records, get unique department names
  if (props.assetData.assets && Array.isArray(props.assetData.assets)) {
    const uniqueDepts = [...new Set(
      props.assetData.assets
        .map(asset => asset.department)
        .filter(dept => dept && dept.trim() !== '')
    )];
    return uniqueDepts.length;
  }
  
  return 0;
})
</script>

<template>
  <!-- Main Content-->
  <div class="dashboard">
    <!-- Dashboard Title -->
    <div class="mb-6">
      <h2 class="text-2xl font-bold text-gray-800">Dashboard</h2>
      <el-divider />
    </div>

    <!-- Summary Cards - First Row -->
    <el-row :gutter="20" class="mb-6">
      <el-col :xs="24" :sm="12" v-for="(stat, index) in [
        { title: 'Total Assets', value: assetData.assets.length, icon: Collection, color: 'primary', isCurrency: false },
        { title: 'Categories', value: assetData.categories.length, icon: Document, color: 'success', isCurrency: false }
      ]" :key="index">
        <el-card shadow="hover"
          :body-style="{ padding: '20px', display: 'flex', flexDirection: 'column', height: '100%' }"
          class="border-left-4" :style="{ borderLeftColor: `var(--el-color-${stat.color})` }">
          <el-space direction="vertical" alignment="flex-start" class="flex-grow">
            <el-icon :size="40" :color="stat.color">
              <component :is="stat.icon" />
            </el-icon>
            <div class="text-gray-500">{{ stat.title }}</div>
          </el-space>
          <div class="text-right mt-auto">
            <div class="text-4xl font-bold text-gray-800">
              {{ stat.isCurrency ? 'RM' + stat.value.toLocaleString() : stat.value }}
            </div>
          </div>
        </el-card>
      </el-col>
    </el-row>

    <!-- Summary Cards - Second Row -->
    <el-row :gutter="20" class="mb-10">
      <el-col :xs="24" :sm="12" v-for="(stat, index) in [
        { title: 'Departments', value: uniqueDepartmentsCount, icon: OfficeBuilding, color: 'warning', isCurrency: false },
        { title: 'Total Value', value: assetData.totalValue, icon: Money, color: 'danger', isCurrency: true }
      ]" :key="index">
        <el-card shadow="hover"
          :body-style="{ padding: '20px', display: 'flex', flexDirection: 'column', height: '100%' }"
          class="border-left-4" :style="{ borderLeftColor: `var(--el-color-${stat.color})` }">
          <el-space direction="vertical" alignment="flex-start" class="flex-grow">
            <el-icon :size="40" :color="stat.color">
              <component :is="stat.icon" />
            </el-icon>
            <div class="text-gray-500">{{ stat.title }}</div>
          </el-space>
          <div class="text-right mt-auto">
            <div class="text-4xl font-bold text-gray-800">
              {{ stat.isCurrency ? 'RM' + stat.value.toLocaleString() : stat.value }}
            </div>
          </div>
        </el-card>
      </el-col>
    </el-row>

    <!-- Chart Section -->
    <el-row :gutter="20" class="dashboard-section">
      <el-col :xs="24" :sm="12">
        <el-card shadow="hover" :body-style="{ padding: '20px', height: '100%' }" class="dashboard-card">
          <h3 class="text-lg font-semibold mb-4">Asset Distribution</h3>
          <AssetDistribution 
            :assetData="assetData" 
            @update-asset="handleAssetDeptUpdate" 
          />
        </el-card>

        <el-card shadow="hover" :body-style="{ padding: '20px', height: '100%' }" class="dashboard-card">
            <AssetBreakdown :assetData="assetData" @update-asset="handleAssetUpdate" />
        </el-card>

        <el-card shadow="hover" :body-style="{ padding: '20px', height: '100%' }" class="dashboard-card">
          <h3 class="text-lg font-semibold mb-4">Total Asset Value by Department</h3>
          <AssetValueByDept 
            :assetData="assetData" 
            @update-asset="handleAssetValueUpdate"
          />
        </el-card>
        
        <el-card shadow="hover" :body-style="{ padding: '20px', height: '100%' }" class="dashboard-card">
          <TotalAssetValueByUsageChart :assetData="assetData" />
        </el-card>
      </el-col>

      <el-col :xs="24" :sm="12">
        <el-card shadow="hover" :body-style="{ padding: '20px', height: '100%' }" class="dashboard-card">
          <h3 class="text-lg font-semibold mb-4">Asset Overview</h3>
          <AssetByCategory :assetData="assetData" />
        </el-card>

        <!-- Asset Status Summary with Table and Chart-->
        <el-card shadow="hover" :body-style="{ padding: '20px', height: '100%' }" class="dashboard-card">
          <h3 class="text-lg font-semibold mb-4">Asset Status Summary</h3>
          <AssetStatusTable 
            :assets="assetData.assets" 
            :departments="assetData.departments" 
          />
        </el-card>
        
        <el-card shadow="hover" :body-style="{ padding: '20px', height: '100%' }" class="dashboard-card">
          <h3 class="text-lg font-semibold mb-4">Upcoming Warranty Expiry</h3>
          <WarrantyExpiryTable 
            :assets="assetData.assets" 
            :departments="assetData.departments" 
            :assetData="assetData" 
            @update-asset="handleAssetWarrantyUpdate"
          />
        </el-card>
      </el-col>
    </el-row>
  </div>
</template>
