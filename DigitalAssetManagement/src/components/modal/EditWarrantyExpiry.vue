<template>
  <!-- Main dialog component for updating warranty expiry dates -->
  <el-dialog :model-value="visible" title="Update Warranty Expiry" width="60%" @close="resetForm"
    @update:model-value="handleVisibilityChange">
    <el-form :model="formData" label-position="top" ref="formRef">
      <!-- Department selection row -->
      <el-row :gutter="20">
        <el-col :span="24">
          <el-form-item label="Department" prop="department">
            <el-select v-model="currentDepartment" placeholder="Select department" style="width: 100%"
              @change="handleDepartmentChange">
              <!-- Dynamically generate options based on available departments -->
              <el-option v-for="dept in departments" :key="dept" :label="dept" :value="dept" />
            </el-select>
          </el-form-item>
        </el-col>
      </el-row>

      <!-- Asset selection row - only enabled after department is selected -->
      <el-row :gutter="20">
        <el-col :span="24">
          <el-form-item label="Select Asset" prop="selectedAssetId">
            <el-select v-model="selectedAssetId" placeholder="Select asset" style="width: 100%"
              @change="handleAssetChange" :disabled="!currentDepartment">
              <!-- Display only assets from the selected department -->
              <el-option v-for="asset in filteredAssets" :key="asset.id" :label="asset.name" :value="asset.id" />
            </el-select>
          </el-form-item>
        </el-col>
      </el-row>

      <!-- Asset details row - read-only information fields -->
      <el-row :gutter="20">
        <el-col :span="12">
          <el-form-item label="Name" prop="name">
            <el-input v-model="formData.name" disabled />
          </el-form-item>
        </el-col>
        <el-col :span="12">
          <el-form-item label="Category" prop="category">
            <el-input v-model="formData.category" disabled />
          </el-form-item>
        </el-col>
      </el-row>

      <!-- Warranty expiry fields - current (read-only) and new date (editable) -->
      <el-row :gutter="20">
        <el-col :span="12">
          <el-form-item label="Current Warranty Expiry" prop="currentWarrantyExpiry">
            <el-input v-model="formData.currentWarrantyExpiry" disabled />
          </el-form-item>
        </el-col>
        <el-col :span="12">
          <el-form-item label="New Warranty Expiry" prop="newWarrantyExpiry">
            <el-date-picker
              v-model="formData.newWarrantyExpiry"
              type="date"
              placeholder="Select new date"
              format="YYYY-MM-DD"
              value-format="YYYY-MM-DD"
              style="width: 100%"
              :disabled-date="disablePastDates"
            />
          </el-form-item>
        </el-col>
      </el-row>

      <!-- Visual timeline component for warranty dates - only shown when warranty data is available -->
      <el-divider v-if="formData.currentWarrantyExpiry" content-position="left">Warranty Timeline</el-divider>
      
      <el-row v-if="formData.currentWarrantyExpiry" :gutter="20">
        <el-col :span="24">
          <div class="warranty-timeline">
            <el-steps :active="getTimelineActive()" finish-status="success">
              <el-step title="Purchase Date" :description="formData.purchaseDate || 'N/A'" />
              <el-step title="Current Warranty" :description="formData.currentWarrantyExpiry || 'N/A'" />
              <el-step title="New Warranty" :description="formData.newWarrantyExpiry || 'Select date'" />
            </el-steps>
          </div>
        </el-col>
      </el-row>
    </el-form>

    <!-- Dialog footer with action buttons -->
    <template #footer>
      <span class="dialog-footer">
        <el-button @click="close">Cancel</el-button>
        <el-button type="primary" @click="submitForm" :disabled="!isFormValid">
          Submit
        </el-button>
      </span>
    </template>
  </el-dialog>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { ElMessage } from 'element-plus'

// Component props definition
const props = defineProps({
  // Controls dialog visibility
  visible: {
    type: Boolean,
    default: false
  },
  // List of available departments for selection
  departments: {
    type: Array,
    default: () => []
  },
  // Array of all assets in the system
  assets: {
    type: Array,
    default: () => []
  },
  // Optional pre-selected asset object for direct editing
  selectedAsset: {
    type: Object,
    default: null
  }
})

// Event emitters for parent component communication
const emit = defineEmits(['update:visible', 'submit'])

// Template refs and reactive state variables
const formRef = ref(null)                   // Reference to the form element for validation methods
const currentDepartment = ref('')           // Currently selected department
const selectedAssetId = ref('')             // Currently selected asset ID
const filteredAssets = ref([])              // Assets filtered by the selected department

// Form data object containing all the editable fields
const formData = ref({
  name: '',                                 // Asset name (read-only)
  category: '',                             // Asset category (read-only)
  purchaseDate: '',                         // Asset purchase date (read-only)
  currentWarrantyExpiry: '',                // Current warranty expiry date (read-only)
  newWarrantyExpiry: ''                     // New warranty expiry date (editable)
})

// Computed property to determine if the form is valid and submission should be allowed
// The form is valid when an asset is selected, new date is provided, and date is different from current warranty date
const isFormValid = computed(() => {
  return selectedAssetId.value &&
    formData.value.newWarrantyExpiry &&
    formData.value.newWarrantyExpiry !== formData.value.currentWarrantyExpiry
})

// Handler for dialog visibility changes - propagates to parent component
const handleVisibilityChange = (value) => {
  emit('update:visible', value)
}

// Handler for department selection changes
const handleDepartmentChange = (dept) => {
  // Filter assets by selected department
  filteredAssets.value = props.assets.filter(asset => asset.department === dept)
  selectedAssetId.value = ''            // Reset asset selection when department changes
  resetFormData()                       // Reset form data when department changes
}

// Handler for asset selection changes
const handleAssetChange = (assetId) => {
  const selectedAsset = props.assets.find(asset => asset.id === assetId)
  if (selectedAsset) {
    // Populate form with selected asset data
    formData.value = {
      name: selectedAsset.name || '',
      category: selectedAsset.category || '',
      purchaseDate: selectedAsset.purchaseDate || '',
      currentWarrantyExpiry: selectedAsset.warrantyExpiry || '',
      newWarrantyExpiry: selectedAsset.warrantyExpiry || ''  // Initialize new date to current date as starting point
    }
  }
}

// Reset form data fields to empty/default values
const resetFormData = () => {
  formData.value = {
    name: '',
    category: '',
    purchaseDate: '',
    currentWarrantyExpiry: '',
    newWarrantyExpiry: ''
  }
}

// Close the dialog and reset all form state
const close = () => {
  currentDepartment.value = ''
  selectedAssetId.value = ''
  filteredAssets.value = []
  resetFormData()
  emit('update:visible', false)  // Notify parent component about dialog closure
}

// Reset form validation state and close the dialog
const resetForm = () => {
  formRef.value?.resetFields()   // Reset form validation state if formRef exists
  close()
}

// Determine which step in the timeline should be active based on form data
const getTimelineActive = () => {
  if (!formData.value.currentWarrantyExpiry) return 0  // No warranty data yet
  if (!formData.value.newWarrantyExpiry) return 1      // Current warranty but no new date selected
  return 2                                             // Both current and new dates are set
}

// Date validator function to prevent selecting dates in the past
const disablePastDates = (date) => {
  // Disable dates before today
  return date < new Date(new Date().setHours(0, 0, 0, 0))
}

// Form submission handler
const submitForm = () => {
  // Validate asset selection
  if (!selectedAssetId.value) {
    ElMessage.warning('Please select an asset')
    return
  }

  // Ensure the warranty date was actually changed
  if (formData.value.newWarrantyExpiry === formData.value.currentWarrantyExpiry) {
    ElMessage.warning('Please select a different warranty expiry date')
    return
  }

  // Find the original asset to preserve its properties
  const originalAsset = props.assets.find(asset => asset.id === selectedAssetId.value)

  // Create updatedAsset with ALL original properties plus the updated ones
  // Using spread operator to maintain all existing properties
  const updatedAsset = {
    ...originalAsset,  // Preserve all original properties
    warrantyExpiry: formData.value.newWarrantyExpiry  // Update only the warranty expiry property
  }

  // Emit submit event with updated asset to parent component
  emit('submit', updatedAsset)
  
  close()  // Close dialog after successful submission
}

// Watch for dialog visibility and initialize form if a selectedAsset was provided
// This allows direct editing of a specific asset without manual department/asset selection
watch(() => props.visible, (newVisible) => {
  if (newVisible && props.selectedAsset) {
    // Pre-select the department and asset when provided
    currentDepartment.value = props.selectedAsset.department
    handleDepartmentChange(currentDepartment.value)
    selectedAssetId.value = props.selectedAsset.id
    handleAssetChange(selectedAssetId.value)
  }
}, { immediate: true })  // Run immediately on component mount to handle initial values
</script>

<style scoped>
/* Push footer buttons to the right side of the dialog */
.dialog-footer {
  display: flex;
  justify-content: flex-end;
}

/* Add some spacing around the warranty timeline visualization */
.warranty-timeline {
  margin-top: 10px;
  margin-bottom: 20px;
}
</style>