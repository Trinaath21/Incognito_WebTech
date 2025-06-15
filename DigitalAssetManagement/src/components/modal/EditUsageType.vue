<template>
  <el-dialog
    :model-value="modelValue"
    title="Edit Asset Status"
    width="80%"
    @update:model-value="(val) => emit('update:modelValue', val)"
  >
    <!-- Filter section -->
        <!-- Filter Area -->
        <el-row :gutter="20" class="mb-4">
      <el-col :span="7">
        <el-input
          v-model="searchQuery"
          placeholder="Search asset name..."
          clearable
        />
      </el-col>
      <el-col :span="5">
        <el-select
          v-model="selectedCategory"
          placeholder="Filter by category"
          clearable
          style="width: 100%"
        >
        <el-option
          v-for="category in categories"
          :key="category.id"
          :label="category.name"
          :value="category.name"
        />
        </el-select>
      </el-col>
      <el-col :span="5">
        <el-select
          v-model="selectedDepartment"
          placeholder="Filter by department"
          clearable
          style="width: 100%"
        >
          <el-option
            v-for="dept in departments"
            :key="dept"
            :label="dept"
            :value="dept"
          />
        </el-select>
      </el-col>
      <el-col :span="5">
        <el-select
          v-model="selectedUsageType"
          placeholder="Filter by Usage Type"
          clearable
          style="width: 100%"
        >
          <el-option label="Personal" value="Personal" />
          <el-option label="Shared" value="Shared" />
          <el-option label="Testing" value="Testing" />
          <el-option label="Backup" value="Backup" />
          <el-option label="Critical" value="Critical" />
        </el-select>
      </el-col>
    </el-row>


    <!-- Asset table -->
    <div class="asset-list-container" style="margin-top: 20px;">
      <el-table
        :data="filteredAssets"
        style="width: 100%"
        height="400"
        class="bold-table-headers"
      >
        <el-table-column prop="name" label="Asset Name" />
        <el-table-column prop="category_name" label="Category" />
        <el-table-column prop="department" label="Department" />
        <el-table-column prop="status" label="Status"/>
        <el-table-column label="Usage Type">
          <template #default="scope">
            <el-select
              v-model="scope.row.usage_type"
              @change="updateUsageType(scope.row)"
              size="small"
              style="width: 120px"
            >
              <el-option 
                v-for="option in usageTypeOptions"
                :key="option.value"
                :label="option.label"
                :value="option.value"
              />
            </el-select>
          </template>
        </el-table-column>
      </el-table>
    </div>

    <template #footer>
      <el-button @click="emit('update:modelValue', false)">Close</el-button>
    </template>
  </el-dialog>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import assetService from '../../api/assetService'

const props = defineProps({
  modelValue: Boolean,
  assets: Array,
  categories: Array,
  departments: Array
})

const emit = defineEmits(['update:modelValue', 'asset-updated'])


//  Log assets when component mounts
onMounted(() => {
  console.log('Assets received from API:', props.assets)
})

// Filter parameters
const searchQuery = ref('')
const selectedCategory = ref('')
const selectedDepartment = ref('')
const selectedUsageType = ref('')

// Usage type options
const usageTypeOptions = [
  { label: 'Personal', value: 'Personal' },
  { label: 'Shared', value: 'Shared' },
  { label: 'Testing', value: 'Testing' },
  { label: 'Backup', value: 'Backup' },
  { label: 'Critical', value: 'Critical' }
]

// Filtered assets
const filteredAssets = computed(() => {
  return props.assets.filter(asset => {
    const matchesSearch = asset.name.toLowerCase().includes(searchQuery.value.toLowerCase())
    const matchesCategory = selectedCategory.value ? asset.category_name === selectedCategory.value : true
    const matchesDepartment = selectedDepartment.value ? asset.department === selectedDepartment.value : true
    const matchesUsageType = selectedUsageType.value ? asset.usage_type === selectedUsageType.value : true
    return matchesSearch && matchesCategory && matchesDepartment && matchesUsageType
  })
})

// Update usage type with success message
const updateUsageType = async (asset) => {
  try {
    // Save original value in case we need to revert
    const originalValue = asset.usage_type
    
    // Show loading state (optional)
    const loading = ElMessage({
      message: 'Updating usage type...',
      type: 'info',
      duration: 0
    })
    
    // Call API
    await assetService.updateAssetUsageType(asset.id, asset.usage_type)
    
    // Close loading message
    loading.close()
    
    // Show success message
    ElMessage.success({
      message: `Usage type updated to ${asset.usage_type}`,
      duration: 3000
    })
    
    // Notify parent component
    emit('asset-updated', asset)
    
  } catch (error) {
    console.error('Error updating usage type:', error)
    
    // Revert the change in the UI
    asset.usage_type = originalValue
    
    // Show error message
    ElMessage.error({
      message: 'Failed to update usage type',
      duration: 3000
    })
  }
}
</script>

<style scoped>
.bold-table-headers th.el-table__cell {
  font-weight: bold;
  color: #494747;
  background-color: rgb(238, 248, 245);
}
</style>
