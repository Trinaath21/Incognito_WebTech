<template>
    <el-dialog 
      :model-value="visible" 
      title="Edit Department Data" 
      width="70%"
      @close="resetForm"
      @update:model-value="handleVisibilityChange"
    >
      <el-form 
        :model="formData" 
        label-position="top"
        ref="formRef"
      >
        <el-row :gutter="20">
          <el-col :span="24">
            <el-form-item label="Current Department" prop="currentDepartment">
              <el-select 
                v-model="currentDepartment"
                placeholder="Select department"
                style="width: 100%"
                @change="handleDepartmentChange"
              >
                <el-option
                  v-for="dept in departments"
                  :key="dept"
                  :label="dept"
                  :value="dept"
                />
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>
  
        <el-row :gutter="20">
          <el-col :span="24">
            <el-form-item label="Select Asset" prop="selectedAssetId">
              <el-select 
                v-model="selectedAssetId"
                placeholder="Select asset"
                style="width: 100%"
                @change="handleAssetChange"
                :disabled="!currentDepartment"
              >
                <el-option
                  v-for="asset in filteredAssets"
                  :key="asset.id"
                  :label="asset.name"
                  :value="asset.id"
                />
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>
  
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
  
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="Status" prop="status">
              <el-input v-model="formData.status" disabled />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="Usage" prop="usage">
              <el-input v-model="formData.usage" disabled />
            </el-form-item>
          </el-col>
        </el-row>
  
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="Purchase Date" prop="purchaseDate">
              <el-date-picker
                v-model="formData.purchaseDate"
                type="date"
                placeholder="Select date"
                style="width: 100%"
                disabled
              />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="New Department" prop="newDepartment">
              <el-select 
                v-model="formData.newDepartment" 
                placeholder="Select new department"
                style="width: 100%"
              >
                <el-option
                  v-for="dept in departments"
                  :key="dept"
                  :label="dept"
                  :value="dept"
                />
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
  
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="close">Cancel</el-button>
          <el-button type="primary" @click="submitForm">
            Submit
          </el-button>
        </span>
      </template>
    </el-dialog>
  </template>
  
  <script setup>
  import { ref, computed, watch } from 'vue'
  import { ElMessage } from 'element-plus'
  
  const props = defineProps({
    visible: {
      type: Boolean,
      default: false
    },
    currentAsset: {
      type: Object,
      default: () => ({})
    },
    departments: {
      type: Array,
      default: () => []
    },
    statusOptions: {
      type: Array,
      default: () => []
    },
    assets: {  // Add assets prop to receive all assets data
      type: Array,
      default: () => []
    }
  })
  
  const emit = defineEmits(['update:visible', 'submit'])
  
  const formRef = ref(null)
  const currentDepartment = ref('')
  const selectedAssetId = ref('')
  const filteredAssets = ref([])
  
  const formData = ref({
    name: '',
    category: '',
    status: '',
    usage: '',
    purchaseDate: '',
    currentDepartment: '',
    newDepartment: ''
  })
  
  const handleVisibilityChange = (value) => {
    emit('update:visible', value)
  }
  
  const handleDepartmentChange = (dept) => {
    // Filter assets by selected department
    filteredAssets.value = props.assets.filter(asset => asset.department === dept)
    selectedAssetId.value = ''
    resetFormData()
  }
  
  const handleAssetChange = (assetId) => {
    const selectedAsset = props.assets.find(asset => asset.id === assetId)
    if (selectedAsset) {
      formData.value = {
        name: selectedAsset.name || '',
        category: selectedAsset.category || '',
        status: selectedAsset.status || '',
        usage: selectedAsset.usage || '',
        purchaseDate: selectedAsset.purchaseDate || new Date(),
        currentDepartment: currentDepartment.value,
        newDepartment: currentDepartment.value // Default to current department
      }
    }
  }
  
  const resetFormData = () => {
    formData.value = {
      name: '',
      category: '',
      status: '',
      usage: '',
      purchaseDate: '',
      currentDepartment: currentDepartment.value,
      newDepartment: currentDepartment.value
    }
  }
  
  const close = () => {
    currentDepartment.value = ''
    selectedAssetId.value = ''
    filteredAssets.value = []
    resetFormData()
    emit('update:visible', false)
  }
  
  const resetForm = () => {
    formRef.value?.resetFields()
    close()
  }
  
  const submitForm = () => {
  if (!selectedAssetId.value) {
    ElMessage.warning('Please select an asset')
    return
  }

  const updatedAsset = {
    id: selectedAssetId.value, // Make sure this matches your asset data structure
    newDepartment: formData.value.newDepartment
  }

  emit('submit', updatedAsset)
  close()
}
  </script>
  
  <style scoped>
  .dialog-footer {
    display: flex;
    justify-content: flex-end;
  }
  </style>