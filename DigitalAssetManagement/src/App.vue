<script setup>
import { ref, onMounted } from 'vue'
import Dashboard from './components/Dashboard.vue'
import Sidebar from './components/Sidebar.vue'
import assetService from './api/assetService'
import { ElNotification } from 'element-plus'
import './assets/app.css';

const assetData = ref({
  assets: [],
  categories: [],
  departments: [],
  statusOptions: ['In Use', 'Storage', 'Under Repair', 'Disposal'],
  totalValue: 0
})

// Fetch initial data
const fetchData = async () => {
  try {
    const [assets, categories, departments] = await Promise.all([
      assetService.getAssets(),
      assetService.getCategories(),
      assetService.getDepartments()
    ])
    
    assetData.value = {
      ...assetData.value,
      assets,
      categories,
      departments,
      totalValue: assets.reduce((sum, asset) => sum + (parseFloat(asset.value) || 0), 0)
    }
  } catch (error) {
    console.error('Error loading initial data:', error)
    ElNotification({
      title: 'Error',
      message: 'Failed to load asset data',
      type: 'error'
    })
  }
}

// Update asset department
// In App.vue's updateAsset method
const updateAsset = async (updatedAsset) => {
  try {
    // Use the standard update method instead of specialized endpoint
    const updated = await assetService.updateAsset(updatedAsset.id, {
      department: updatedAsset.newDepartment
    })
    
    // Update local state
    const index = assetData.value.assets.findIndex(a => a.id === updated.id)
    if (index !== -1) {
      assetData.value.assets[index] = updated
      assetData.value.totalValue = assetData.value.assets.reduce(
        (sum, asset) => sum + (parseFloat(asset.value) || 0), 0
      )
      
      ElNotification({
        title: 'Success',
        message: 'Department updated successfully',
        type: 'success'
      })
    }
  } catch (error) {
    console.error('Error updating asset:', error)
    ElNotification({
      title: 'Error',
      message: error.response?.data?.error || 'Failed to update department',
      type: 'error'
    })
    throw error
  }
}

const updateAssetDept = async (updatedAsset) => {
  try {
    // Use the standard update method instead of specialized endpoint
    const updated = await assetService.changeAssetDepartment(updatedAsset.id, updatedAsset.newDepartment)
    
    // Update local state
    const index = assetData.value.assets.findIndex(a => a.id === updated.id)
    if (index !== -1) {
      assetData.value.assets[index] = updated
      assetData.value.totalValue = assetData.value.assets.reduce(
        (sum, asset) => sum + (parseFloat(asset.value) || 0), 0
      )
      
      ElNotification({
        title: 'Success',
        message: 'Department updated successfully',
        type: 'success'
      })
    }
  } catch (error) {
    console.error('Error updating asset:', error)
    ElNotification({
      title: 'Error',
      message: error.response?.data?.error || 'Failed to update department',
      type: 'error'
    })
    throw error
  }
}

const updateAssetValue = async (updatedAsset) => {
  try {
    // Use the updateAsset method to update the value
    const updated = await assetService.updateAsset(updatedAsset.id, {
      value: updatedAsset.value  
    })
    
    // Update local state
    const index = assetData.value.assets.findIndex(a => a.id === updated.id)
    if (index !== -1) {
      assetData.value.assets[index] = updated
      // Recalculate total value
      assetData.value.totalValue = assetData.value.assets.reduce(
        (sum, asset) => sum + (parseFloat(asset.value) || 0), 0
      )
      
      ElNotification({
        title: 'Success',
        message: 'Asset value updated successfully',
        type: 'success'
      })
    }
  } catch (error) {
    console.error('Error updating asset value:', error)
    ElNotification({
      title: 'Error',
      message: error.response?.data?.error || 'Failed to update asset value',
      type: 'error'
    })
    throw error
  }
}

const updateAssetWarranty = async (updatedAsset) => {
  try {
    // Call the correct service function with ID and updated warranty date
    const updated = await assetService.updateWarrantyExpiry(updatedAsset.id, updatedAsset.warrantyExpiry)
    
    // Update local asset state
    const index = assetData.value.assets.findIndex(a => a.id === updated.id)
    if (index !== -1) {
      assetData.value.assets[index] = updated
      assetData.value.totalValue = assetData.value.assets.reduce(
        (sum, asset) => sum + (parseFloat(asset.value) || 0), 0
      )

      ElNotification({
        title: 'Success',
        message: 'Warranty expiry updated successfully',
        type: 'success'
      })
    }
  } catch (error) {
    console.error('Error updating warranty expiry:', error)
    ElNotification({
      title: 'Error',
      message: error.response?.data?.error || 'Failed to update warranty expiry',
      type: 'error'
    })
    throw error
  }
}

// Update asset purchase date
const updateAssetDate = async (updatedAsset) => {
  try {
    const updated = await assetService.updateAssetPurchaseDate(updatedAsset.id, updatedAsset.purchase_date)
    const index = assetData.value.assets.findIndex(a => a.id === updated.id)
    if (index !== -1) {
      assetData.value.assets.splice(index, 1, updated)
      assetData.value.totalValue = assetData.value.assets.reduce(
        (sum, asset) => sum + (parseFloat(asset.value) || 0), 0
      )
      ElNotification({
        title: 'Success',
        message: 'Purchase date updated successfully',
        type: 'success'
      })
    }
  } catch (error) {
    console.error('Error updating asset purchase date:', error)
    ElNotification({
      title: 'Error',
      message: error.response?.data?.error || 'Failed to update purchase date',
      type: 'error'
    })
    throw error
  }
}

// Load data when component mounts
onMounted(() => {
  fetchData()
})
</script>

<template>
  <el-container class="app-container" style="height: 100vh;">
    <!-- HEADER -->
    <el-header height="60px" class="app-header" style="position: sticky; top: 0; z-index: 1000;">
      <div class="header-content">
        <div class="logo-section">
          <img src="/images/companyLogo.png" class="logo" alt="Company Logo">
        </div>
        <h1 class="title">DIGITAL ASSET MANAGEMENT SYSTEM</h1>
      </div>
    </el-header>

    <!-- MAIN CONTENT -->
    <el-container style="height: calc(100vh - 60px);">
      <el-aside width="220px" class="no-scroll-sidebar">
        <Sidebar />
      </el-aside>
      <el-main class="app-main" style="overflow-y: auto; padding: 20px;">
  <router-view 
    v-slot="{ Component }"
  >
    <component 
      :is="Component" 
      :assetData="assetData"
      @update-asset="updateAsset" 
      @update-assetDept="updateAssetDept" 
      @update-assetValue="updateAssetValue"
      @update-assetWarranty="updateAssetWarranty"
      @update-assetDate="updateAssetDate"
    />
  </router-view>
</el-main>

  </el-container>
  </el-container>
</template>

<style>
/* Disable scrolling for sidebar */
.no-scroll-sidebar {
  overflow: hidden !important;
}

/* Also ensure the Sidebar component doesn’t trigger overflow */
.sidebar {
  width: 220px;
  height: 100%;
  background-color: #2c3e50;
  padding-top: 20px;
  overflow: hidden;
}

.el-menu {
  width: 100%;
  border-right: none;
  overflow: hidden;
}

.el-menu-item {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

</style>
