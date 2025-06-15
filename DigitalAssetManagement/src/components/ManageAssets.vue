<template>
  <div class="asset-management">
    <!-- First Row: Search + Action Buttons -->
    <div class="action-row">
      <el-input
        v-model="searchQuery"
        placeholder="Search asset by name"
        clearable
        class="search-input"
        @input="filterAssets"
      />
      
      <div class="action-buttons">
        <el-button type="primary" @click="openAddDialog">
          <el-icon><Plus /></el-icon>
          Add Asset
        </el-button>
        <el-button 
          type="danger" 
          :disabled="!selected.length" 
          @click="deleteSelected"
        >
          <el-icon><Delete /></el-icon>
          Delete Selected ({{ selected.length }})
        </el-button>
      </div>
    </div>

    <!-- Filter Row - All dropdowns in one line -->
    <div class="filter-controls">
      <el-select
        v-model="selectedCategory"
        placeholder="All Categories"
        clearable
        class="filter-select"
        @change="filterAssets"
      >
        <el-option 
          v-for="cat in categories" 
          :key="cat.id" 
          :label="cat.name" 
          :value="cat.name" 
        />
      </el-select>

      <el-select
        v-model="selectedDepartment"
        placeholder="All Departments"
        clearable
        class="filter-select"
        @change="filterAssets"
      >
        <el-option 
          v-for="dept in departments" 
          :key="dept" 
          :label="dept" 
          :value="dept" 
        />
      </el-select>

      <el-select
        v-model="selectedStatus"
        placeholder="All Statuses"
        clearable
        class="filter-select"
        @change="filterAssets"
      >
        <el-option 
          v-for="status in statusOptions" 
          :key="status" 
          :label="status" 
          :value="status" 
        />
      </el-select>

      <el-select
        v-model="selectedUsageType"
        placeholder="All Usage Types"
        clearable
        class="filter-select"
        @change="filterAssets"
      >
        <el-option 
          v-for="usage in usageTypes" 
          :key="usage" 
          :label="usage" 
          :value="usage" 
        />
      </el-select>
    </div>


      

    <!-- Asset Table -->
    <el-table
      :data="filteredAssets"
      border
      style="width: 100%"
      @selection-change="handleSelectionChange"
      v-loading="loading"
    >
      <el-table-column type="selection" width="55" />
      <el-table-column prop="name" label="Name" sortable />
      <el-table-column prop="department" label="Department" sortable />
      <el-table-column prop="category_name" label="Category" sortable />
      <el-table-column prop="status" label="Status" sortable />
      <el-table-column prop="purchase_date" label="Purchase Date" sortable />
      <el-table-column prop="warranty_expiry" label="Warranty Expiry" sortable />
      <el-table-column prop="value" label="Value (RM)" sortable />
      <el-table-column prop="usage_type" label="Usage Type" sortable />
      <el-table-column label="Actions" width="120">
        <template #default="scope">
          <el-button 
            type="danger" 
            size="small" 
            @click="deleteAsset(scope.row.id)"
            :icon="Delete"
          />
        </template>
      </el-table-column>
    </el-table>

    <!-- Add Asset Dialog -->
    <el-dialog 
      v-model="showAddDialog" 
      title="Add New Asset" 
      width="50%"
    >
      <el-form 
        :model="newAsset" 
        label-width="140px"
        :rules="assetRules"
        ref="assetForm"
      >
        <el-form-item label="Name:" prop="name">
          <el-input v-model="newAsset.name" />
        </el-form-item>

        <el-form-item label="Department:" prop="department">
          <el-select 
            v-model="newAsset.department" 
            placeholder="Select Department"
          >
            <el-option 
              v-for="dept in departments" 
              :key="dept" 
              :label="dept" 
              :value="dept" 
            />
          </el-select>
        </el-form-item>

        <el-form-item label="Category:" prop="category_id">
          <el-select 
            v-model="newAsset.category_id" 
            placeholder="Select Category"
          >
            <el-option 
              v-for="cat in categories" 
              :key="cat.id" 
              :label="cat.name" 
              :value="cat.id" 
            />
          </el-select>
        </el-form-item>

        <el-form-item label="Status:" prop="status">
          <el-select 
            v-model="newAsset.status" 
            placeholder="Select Status"
          >
            <el-option 
              v-for="status in statusOptions" 
              :key="status" 
              :label="status" 
              :value="status" 
            />
          </el-select>
        </el-form-item>

        <el-form-item label="Purchase Date:" prop="purchase_date">
          <el-date-picker 
            v-model="newAsset.purchase_date" 
            type="date" 
            placeholder="Select date" 
            format="YYYY-MM-DD" 
            value-format="YYYY-MM-DD"
          />
        </el-form-item>

        <el-form-item label="Warranty Expiry:" prop="warranty_expiry">
          <el-date-picker 
            v-model="newAsset.warranty_expiry" 
            type="date" 
            placeholder="Select date" 
            format="YYYY-MM-DD" 
            value-format="YYYY-MM-DD"
          />
        </el-form-item>

        <el-form-item label="Value (RM):" prop="value">
          <el-input-number 
            v-model="newAsset.value" 
            :min="0" 
            :precision="2" 
          />
        </el-form-item>

        <el-form-item label="Usage Type:" prop="usage_type">
          <el-select 
            v-model="newAsset.usage_type" 
            placeholder="Select Usage Type"
          >
            <el-option 
              v-for="usage in usageTypes" 
              :key="usage" 
              :label="usage" 
              :value="usage" 
            />
          </el-select>
        </el-form-item>
      </el-form>

      <template #footer>
        <span class="dialog-footer">
        <el-button @click="showAddDialog = false">Cancel</el-button>
        <el-button 
          type="primary" 
          @click="validateAndAddAsset"
        >
          Add Asset
        </el-button>
      </span>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { ElMessageBox, ElMessage } from 'element-plus'
import { Delete, Plus } from '@element-plus/icons-vue'
import assetService from '../api/assetService'

const assetForm = ref(null)


// Constants
const statusOptions = ['In Use', 'Storage', 'Under Repair', 'Disposal']
const usageTypes = ['Personal', 'Shared', 'Testing', 'Backup', 'Critical']

// State
const loading = ref(false)
const assets = ref([])
const filteredAssets = ref([])
const searchQuery = ref('')
const selected = ref([])
const showAddDialog = ref(false)
const departments = ref([])
const categories = ref([])

// Filter values
const selectedCategory = ref('')
const selectedDepartment = ref('')
const selectedStatus = ref('')
const selectedUsageType = ref('')

// New asset form
const newAsset = ref({
  name: '',
  department: '',
  category_id: null,
  status: '',
  purchase_date: '',
  warranty_expiry: '',
  value: 0,
  usage_type: '',
})

// Form validation rules
const assetRules = {
  name: [{ required: true, message: 'Please input asset name', trigger: 'blur' }],
  department: [{ required: true, message: 'Please select department', trigger: 'change' }],
  category_id: [{ required: true, message: 'Please select category', trigger: 'change' }],
  status: [{ required: true, message: 'Please select status', trigger: 'change' }],
  purchase_date: [{ required: true, message: 'Please select purchase date', trigger: 'change' }],
  value: [{ required: true, message: 'Please input value', trigger: 'blur' }],
  usage_type: [{ required: true, message: 'Please select usage type', trigger: 'change' }],
}

// Methods
const fetchAssets = async () => {
  loading.value = true
  try {
    const res = await assetService.getAssets()
    assets.value = res
    filterAssets()
  } catch (err) {
    console.error('Fetch assets failed:', err)
    ElMessage.error('Failed to load assets')
  } finally {
    loading.value = false
  }
}

const fetchDepartments = async () => {
  try {
    departments.value = await assetService.getDepartments()
  } catch (err) {
    console.error('Fetch departments failed:', err)
  }
}

const fetchCategories = async () => {
  try {
    categories.value = await assetService.getCategories()
  } catch (err) {
    console.error('Fetch categories failed:', err)
  }
}

const filterAssets = () => {
  const query = searchQuery.value.toLowerCase()
  filteredAssets.value = assets.value.filter(asset =>
    asset.name.toLowerCase().includes(query) &&
    (!selectedCategory.value || asset.category_name === selectedCategory.value) &&
    (!selectedDepartment.value || asset.department === selectedDepartment.value) &&
    (!selectedStatus.value || asset.status === selectedStatus.value) &&
    (!selectedUsageType.value || asset.usage_type === selectedUsageType.value)
  )
}

const openAddDialog = () => {
  showAddDialog.value = true
}

const validateAndAddAsset = async () => {
  try {
    console.log("Add button clicked")
    await assetForm.value.validate()
    await addAsset()
  } catch (err) {
    // Validation will automatically show errors
    console.error("Form validation failed", err)
  }
}

const addAsset = async () => {
  try {
    await assetService.createAsset(newAsset.value)
    ElMessage.success('Asset added successfully')
    showAddDialog.value = false
    resetNewAssetForm()
    fetchAssets()
  } catch (err) {
    console.error('Add asset failed:', err)
    ElMessage.error('Failed to add asset')
  }
}

const resetNewAssetForm = () => {
  newAsset.value = {
    name: '',
    department: '',
    category_id: null,
    status: '',
    purchase_date: '',
    warranty_expiry: '',
    value: 0,
    usage_type: '',
  }
  assetForm.value?.resetFields()
}

const handleSelectionChange = (val) => {
  selected.value = val
}

const deleteAsset = async (id) => {
  try {
    await ElMessageBox.confirm(
      'This will permanently delete the asset. Continue?',
      'Warning',
      { confirmButtonText: 'OK', cancelButtonText: 'Cancel', type: 'warning' }
    )
    await assetService.deleteAsset(id)
    ElMessage.success('Asset deleted')
    fetchAssets()
  } catch (err) {
    if (err !== 'cancel') {
      console.error('Delete failed:', err)
      ElMessage.error('Failed to delete asset')
    }
  }
}

const deleteSelected = async () => {
  if (!selected.value.length) return

  try {
    await ElMessageBox.confirm(
      `Delete ${selected.value.length} selected assets?`,
      'Warning',
      { confirmButtonText: 'OK', cancelButtonText: 'Cancel', type: 'warning' }
    )

    await Promise.all(selected.value.map(asset => assetService.deleteAsset(asset.id)))

    ElMessage.success('Selected assets deleted')
    selected.value = []
    fetchAssets()
  } catch (err) {
    if (err !== 'cancel') {
      console.error('Bulk delete failed:', err)
      ElMessage.error('Failed to delete selected assets')
    }
  }
}


// Lifecycle
onMounted(() => {
  fetchAssets()
  fetchDepartments()
  fetchCategories()
})
</script>

<style scoped>
.asset-management {
  padding: 20px;
}

.action-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 20px;
  margin-bottom: 20px;
}

.search-input {
  flex: 1;
  max-width: 400px;
}

.action-buttons {
  display: flex;
  gap: 10px;
}

.dialog-footer {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}

.filter-row {
  display: flex;
  flex-wrap: wrap;
  gap: 15px;
  margin-bottom: 20px;
}

.filter-control {
  min-width: 180px;
}

.filter-controls {
  display: flex;
  gap: 12px;
  margin-bottom: 16px;
  flex-wrap: wrap;
}

.filter-select {
  flex: 1;
  min-width: 220px;
  max-width: 250px;
}

.el-table {
  margin-top: 20px;
}

.el-button + .el-button {
  margin-left: 0;
}
</style>