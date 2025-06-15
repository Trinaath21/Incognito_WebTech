<template>
  <div class="warranty-expiry-table">
    <!-- Edit Form Button -->
    <div class="mb-2 text-left">
      <el-button size="small" type="primary" @click="openEditForm">
        <el-icon><Edit /></el-icon>
        Update Warranty Date
      </el-button>
    </div>

    <!-- Filter controls -->
    <div class="mb-4 flex flex-wrap gap-4">
      <el-select v-model="filterDepartment" placeholder="Filter by Department" clearable>
        <el-option v-for="dept in departments" :key="dept" :label="dept" :value="dept" />
      </el-select>

      <el-select v-model="filterPeriod" placeholder="Expiry Period">
        <el-option
          v-for="period in expiryPeriods"
          :key="period.value"
          :label="period.label"
          :value="period.value"
        />
      </el-select>
    </div>

    <!-- Warranty Expiry Table -->
    <el-table
      :data="paginatedAssets"
      border
      style="width: 100%"
      :default-sort="{ prop: 'daysRemaining', order: 'ascending' }"
      stripe
      v-loading="loading"
    >
      <el-table-column prop="name" label="Asset Name" min-width="160" />
      <el-table-column prop="department" label="Department" width="120" />
      <el-table-column prop="warrantyExpiry" label="Warranty Expiry" width="140" sortable />
      <el-table-column prop="daysRemaining" label="Days Remaining" width="150" sortable>
        <template #default="{ row }">
          <el-tag :type="getStatusType(row.daysRemaining)" size="small" effect="light">
            {{ row.daysRemaining }} days
          </el-tag>
        </template>
      </el-table-column>
      <el-table-column label="Actions" width="100" fixed="right">
        <template #default="{ row }">
          <el-button size="small" type="primary" text @click="handleEditWarranty(row)">
            Update
          </el-button>
        </template>
      </el-table-column>
    </el-table>

    <!-- Pagination -->
    <div class="mt-4 flex justify-end">
      <el-pagination
        v-model:current-page="currentPage"
        v-model:page-size="pageSize"
        :page-sizes="[10, 20, 30, 50]"
        layout="total, sizes, prev, pager, next"
        :total="filteredAssets.length"
      />
    </div>

    <!-- Edit Warranty Dialog -->
    <EditWarrantyExpiry
      :visible="isDialogVisible"
      :departments="departments"
      :assets="assets"
      :selectedAsset="selectedAsset"
      @update:visible="(val) => (isDialogVisible = val)"
      @submit="handleWarrantyUpdate"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Edit } from '@element-plus/icons-vue'
import EditWarrantyExpiry from '../modal/EditWarrantyExpiry.vue'

// Define component options
defineOptions({
  name: 'WarrantyExpiryTable',
})

const props = defineProps({
  assets: {
    type: Array,
    required: true,
  },
  departments: {
    type: Array,
    required: true,
  },
})

const emit = defineEmits(['update-asset'])

// State variables
const loading = ref(true)
const isDialogVisible = ref(false)
const selectedAsset = ref(null)
const filterDepartment = ref('')
const filterPeriod = ref('all')
const currentPage = ref(1)
const pageSize = ref(10)

// Filter options
const expiryPeriods = [
  { value: 'all', label: 'All' },
  { value: 'expired', label: 'Expired' },
  { value: '30', label: 'Within 30 days' },
  { value: '90', label: 'Within 90 days' },
  { value: '180', label: 'Within 180 days' },
  { value: '365', label: 'Within 1 year' },
]

// Computed properties
const allAssets = computed(() => {
  return props.assets.map((asset) => {
    // Map backend snake_case to camelCase
    const warrantyExpiry = asset.warranty_expiry || asset.warrantyExpiry || ''

    // Calculate days remaining
    const warrantyDate = new Date(warrantyExpiry)
    const today = new Date()
    const diffTime = warrantyDate - today
    const daysRemaining = Math.ceil(diffTime / (1000 * 60 * 60 * 24))

    return {
      ...asset,
      warrantyExpiry,
      daysRemaining,
    }
  })
})

const filteredAssets = computed(() => {
  let filtered = allAssets.value

  if (filterDepartment.value) {
    filtered = filtered.filter((asset) => asset.department === filterDepartment.value)
  }
  if (filterPeriod.value !== 'all') {
    if (filterPeriod.value === 'expired') {
      filtered = filtered.filter((asset) => asset.daysRemaining <= 0)
    } else {
      const days = parseInt(filterPeriod.value)
      filtered = filtered.filter((asset) => asset.daysRemaining > 0 && asset.daysRemaining <= days)
    }
  }
  return filtered
})

const paginatedAssets = computed(() => {
  const startIndex = (currentPage.value - 1) * pageSize.value
  const endIndex = startIndex + pageSize.value
  return filteredAssets.value.slice(startIndex, endIndex)
})

// Methods
const getStatusType = (daysRemaining) => {
  if (daysRemaining <= 0) return 'danger'
  if (daysRemaining <= 30) return 'warning'
  if (daysRemaining <= 90) return 'info'
  return 'success'
}

const openEditForm = () => {
  selectedAsset.value = null
  isDialogVisible.value = true
}

const handleEditWarranty = (asset) => {
  selectedAsset.value = asset
  isDialogVisible.value = true
}

const handleWarrantyUpdate = (updatedAsset) => {
  emit('update-asset', updatedAsset)
}

onMounted(() => {
  setTimeout(() => {
    loading.value = false
  }, 500)
})
</script>

<style scoped>
.warranty-expiry-table {
  width: 100%;
}
</style>
