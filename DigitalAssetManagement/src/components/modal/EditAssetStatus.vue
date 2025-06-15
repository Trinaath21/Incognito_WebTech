<template>
  <!-- Main modal dialog for managing asset status -->
  <el-dialog
    :model-value="modelValue"
    title="Manage Asset Status"
    width="90%"
    top="5vh"
    @update:model-value="val => emit('update:modelValue', val)"
    class="asset-status-modal"
  >
    <!-- Filters Row -->
    <div class="filter-row">
      <!-- Search aseet name with clear button -->
      <el-input
        v-model="searchQuery"
        placeholder="Search assets..."
        clearable
        class="search-input"
        @clear="resetFilters"
      >
        <template #prefix>
          <el-icon><search /></el-icon>
        </template>
      </el-input>

      <!-- Category filter dropdown -->
      <el-select
        v-model="selectedCategory"
        placeholder="All Categories"
        clearable
        class="filter-select"
      >
        <el-option
          v-for="category in categories"
          :key="category.id"
          :label="category.name"
          :value="category.name"
        />
      </el-select>

      <!-- Department filter dropdown -->
      <el-select
        v-model="selectedDepartment"
        placeholder="All Departments"
        clearable
        class="filter-select"
      >
        <el-option
          v-for="dept in departments"
          :key="dept"
          :label="dept"
          :value="dept"
        />
      </el-select>

      <!-- Status filter dropdown -->
      <el-select
        v-model="selectedStatus"
        placeholder="All Statuses"
        clearable
        class="filter-select"
      >
        <el-option label="In Use" value="In Use" />
        <el-option label="Storage" value="Storage" />
        <el-option label="Under Repair" value="Under Repair" />
        <el-option label="Disposal" value="Disposal" />
      </el-select>

      <!-- Usage Type filter dropdown -->
      <el-select
        v-model="selectedUsageType"
        placeholder="All Usage Types"
        clearable
        class="filter-select"
      >
        <el-option label="Personal" value="Personal" />
        <el-option label="Shared" value="Shared" />
        <el-option label="Testing" value="Testing" />
        <el-option label="Backup" value="Backup" />
        <el-option label="Critical" value="Critical" />
      </el-select>
    </div>

    <!-- Asset data table -->
    <div class="table-wrapper">
      <el-table
        :data="filteredAssets"
        height="calc(60vh - 20px)"
        v-loading="isLoading"
        :empty-text="noResultsText"
        class="asset-table"
        @sort-change="handleSortChange"
        style="width: 100%"
      >
        <el-table-column prop="name" label="Asset Name" sortable min-width="180" />
        <el-table-column prop="category_name" label="Category" sortable min-width="120" />
        <el-table-column prop="department" label="Department" sortable min-width="120" />
        <el-table-column prop="usage_type" label="Usage Type" sortable min-width="120" />
        <el-table-column prop="status" label="Current Status" sortable min-width="140">
          <template #default="{ row }">
            <el-tag :type="statusTagType(row.status)" effect="plain">
              {{ row.status }}
            </el-tag>
          </template>
        </el-table-column>

        <!-- New status selector -->
        <el-table-column label="New Status" min-width="160" fixed="right">
          <template #default="{ row }">
            <el-select
              v-model="row.newStatus"
              placeholder="Select status"
              :loading="loadingIds.has(row.id)"
              @change="() => onStatusChange(row)"
              class="status-select"
            >
              <el-option
                v-for="status in statusOptions"
                :key="status.value"
                :label="status.label"
                :value="status.value"
                :disabled="row.status === status.value"
              />
            </el-select>
          </template>
        </el-table-column>
      </el-table>
    </div>

    <!-- Modal footer with reset and close buttons -->
    <template #footer>
      <div class="dialog-footer">
        <el-button @click="resetFilters">Reset Filters</el-button>
        <el-button type="primary" @click="emit('update:modelValue', false)">
          Done
        </el-button>
      </div>
    </template>
  </el-dialog>
</template>

<script setup>
// Vue composition API + Element Plus icons and service
import { ref, computed, watch } from 'vue'
import { Search } from '@element-plus/icons-vue'
import { ElMessage } from 'element-plus'
import assetService from '../../api/assetService'

// Props: modal state and data from parent
const props = defineProps({
  modelValue: Boolean,
  assets: Array,
  categories: Array,
  departments: Array,
  isLoading: Boolean
})

// Emit events to parent component
const emit = defineEmits(['update:modelValue', 'refresh'])

// Filter states
const searchQuery = ref('')
const selectedCategory = ref('')
const selectedDepartment = ref('')
const selectedStatus = ref('')
const selectedUsageType = ref('')

// Status management
// Track loading status for specific assets (during update)
const loadingIds = ref(new Set())

// Status options available for change
const statusOptions = ref([])

// Fetch or extract status options
const getStatusOptions = () => {
  try {
    // Try to get from API first
    if (assetService.getStatusOptions) {
      return assetService.getStatusOptions()
    }
    // Fallback to extracting from assets
    return [...new Set(props.assets.map(asset => asset.status))]
  } catch (error) {
    console.error('Error getting status options:', error)
    // Final fallback to default options
    return ['In Use', 'Storage', 'Under Repair', 'Disposal']
  }
}

// Initialize status options
watch(() => props.assets, async (newAssets) => {
  if (newAssets?.length) {
    const options = await getStatusOptions()
    statusOptions.value = options.map(status => ({
      value: status,
      label: status
    }))
  }
}, { immediate: true })

// Prepare assets data with newStatus property
const preparedAssets = computed(() => {
  return props.assets.map(asset => ({
    ...asset,
    newStatus: asset.status
  }))
})

// Computed filtered list
const filteredAssets = computed(() => {
  return preparedAssets.value.filter(asset => {
    const matchesSearch = asset.name?.toLowerCase().includes(searchQuery.value.toLowerCase())
    const matchesCategory = selectedCategory.value
      ? asset.category_name === selectedCategory.value
      : true
    const matchesDepartment = selectedDepartment.value
      ? asset.department === selectedDepartment.value
      : true
    const matchesStatus = selectedStatus.value
      ? asset.status === selectedStatus.value
      : true
    const matchesUsageType = selectedUsageType.value
      ? asset.usage_type === selectedUsageType.value
      : true
    
    return matchesSearch && matchesCategory && matchesDepartment && 
           matchesStatus && matchesUsageType
  })
})

const noResultsText = computed(() => {
  if (props.isLoading) return 'Loading...'
  if (searchQuery.value || selectedCategory.value || 
      selectedDepartment.value || selectedStatus.value || selectedUsageType.value) {
    return 'No matching assets found'
  }
  return 'No assets available'
})

// Handle status change
const onStatusChange = async (asset) => {
  if (asset.status === asset.newStatus) return

  loadingIds.value.add(asset.id)
  try {
    asset.status = asset.newStatus
    await assetService.updateAssetStatus(asset.id, asset.newStatus)
    ElMessage.success({
      message: `Updated ${asset.name} to ${asset.newStatus}`,
      duration: 3000
    })
    emit('update-assets')
  } catch (error) {
    ElMessage.error(`Failed to update ${asset.name}`)
    console.error('Status update error:', error)
    // Revert the change in UI
    asset.newStatus = asset.status
  } finally {
    loadingIds.value.delete(asset.id)
  }
}

// Reset all filters
const resetFilters = () => {
  searchQuery.value = ''
  selectedCategory.value = ''
  selectedDepartment.value = ''
  selectedStatus.value = ''
  selectedUsageType.value = ''
}

// Define tag style (color) based on current status
// Dynamic tag colors with caching
const statusColors = ref({})
const statusTagType = (status) => {
  if (!status) return ''
  
  // Return cached color if exists
  if (statusColors.value[status]) {
    return statusColors.value[status]
  }

  // Default mappings
  const defaultMap = {
    'In Use': 'success',
    'Storage': 'info',
    'Under Repair': 'warning',
    'Disposal': 'danger',
    'Active': 'success',
    'Inactive': 'info',
    'Retired': 'danger'
  }

  // Check default mappings first
  if (defaultMap[status]) {
    statusColors.value[status] = defaultMap[status]
    return defaultMap[status]
  }
  
  // Generate consistent color for unknown statuses
  const hash = status.split('').reduce((acc, char) => acc + char.charCodeAt(0), 0)
  const types = ['', 'success', 'info', 'warning', 'danger']
  const color = types[hash % types.length] || 'info'
  statusColors.value[status] = color
  
  return color
}

</script>

<style scoped>
/* Flex layout for filter row */
.filter-row {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 16px;
  flex-wrap: wrap;
}

/* Responsive sizing */
.search-input {
  flex: 1;
  min-width: 200px;
}

.filter-select {
  min-width: 160px;
  flex: 1;
}

.table-wrapper {
  width: 100%;
  height: calc(60vh - 20px);
  overflow: hidden;
  position: relative;
}

.asset-table {
  width: 100%;
  height: 100%;
}

.status-select {
  width: 100%;
}

/* Dialog footer layout */
.dialog-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 16px;
}

/* Modal styling */
.asset-status-modal {
  max-width: 1200px;
}

/* Override Element Plus body padding */
:deep(.asset-status-modal) .el-dialog__body {
  padding: 16px 20px;
}

/* Table cell text overflow handling */
:deep(.el-table .cell) {
  white-space: nowrap;
  text-overflow: ellipsis;
  overflow: hidden;
}

/* Table header styling */
:deep(.el-table th) {
  background-color: #f5f7fa !important;
  color: #303133;
  font-weight: bold;
}

/* Border adjustments for table cells */
:deep(.el-table--border .el-table__cell) {
  border-right: 1px solid var(--el-table-border-color);
}

/* Mobile responsiveness */
@media (max-width: 1200px) {
  .filter-row {
    flex-direction: column;
    align-items: stretch;
    gap: 8px;
  }
  
  .search-input,
  .filter-select {
    width: 100%;
    min-width: 100%;
  }
  
  :deep(.el-table) {
    font-size: 14px;
  }
  
  
  :deep(.el-table th.el-table__cell > .cell) {
    padding: 8px 0;
  }
  
  :deep(.el-table td.el-table__cell) {
    padding: 6px 0;
  }
  
}
</style>
