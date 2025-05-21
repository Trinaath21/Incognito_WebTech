<template>
  <!-- Main dialog component for editing asset values -->
  <el-dialog :model-value="visible" title="Edit Asset Value" width="60%" @close="resetForm"
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

      <!-- Asset value fields - current (read-only) and new value (editable) -->
      <el-row :gutter="20">
        <el-col :span="12">
          <el-form-item label="Current Value (RM)" prop="currentValue">
            <el-input v-model="formData.currentValue" disabled />
          </el-form-item>
        </el-col>
        <el-col :span="12">
          <el-form-item label="New Value (RM)" prop="newValue">
            <el-input-number v-model="formData.newValue" :min="0" :step="100" style="width: 100%" />
          </el-form-item>
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
import { ref, computed, watch } from 'vue'
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
  currentValue: '',                         // Current asset value (read-only)
  newValue: 0                               // New asset value (editable)
})

// Computed property to determine if the form is valid and submission should be allowed
// The form is valid when an asset is selected, new value is positive, and different from current value
const isFormValid = computed(() => {
  return selectedAssetId.value &&
    formData.value.newValue > 0 &&
    formData.value.newValue !== formData.value.currentValue
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
      currentValue: selectedAsset.value || 0,
      newValue: selectedAsset.value || 0  // Initialize new value to current value as starting point
    }
  }
}

// Reset form data fields to empty/default values
const resetFormData = () => {
  formData.value = {
    name: '',
    category: '',
    currentValue: '',
    newValue: 0
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

// Form submission handler
const submitForm = () => {
  // Validate asset selection
  if (!selectedAssetId.value) {
    ElMessage.warning('Please select an asset')
    return
  }

  // Ensure the value was actually changed
  if (formData.value.newValue === formData.value.currentValue) {
    ElMessage.warning('Please change the asset value')
    return
  }

  // Find the original asset to preserve its properties
  const originalAsset = props.assets.find(asset => asset.id === selectedAssetId.value)

  // Create updatedAsset with ALL original properties plus the updated ones
  // Using spread operator to maintain all existing properties
  const updatedAsset = {
    ...originalAsset,  // Preserve all original properties
    value: formData.value.newValue  // Update only the value property
  }

  // Emit submit event with updated asset to parent component
  emit('submit', updatedAsset)
  ElMessage.success('Asset value updated successfully')
  close()  // Close dialog after successful submission
}
</script>

<style scoped>
/* Push footer buttons to the right side of the dialog */
.dialog-footer {
  display: flex;
  justify-content: flex-end;
}
</style>