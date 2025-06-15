```vue
<template>
    <!-- Dialog for updating asset purchase date -->
    <el-dialog :model-value="visible" title="Update Asset Purchase Date" width="50%" @close="resetForm"
        @update:model-value="handleVisibilityChange">
        <!-- Form for selecting asset and purchase date -->
        <el-form :model="formData" label-position="top" ref="formRef">
            <el-row :gutter="20">
                <el-col :span="24">
                    <el-form-item label="Select Asset" prop="selectedAssetId">
                        <el-select v-model="selectedAssetId" placeholder="Select asset" style="width: 100%"
                            @change="handleAssetChange">
                            <el-option v-for="asset in props.assets" :key="asset.id" :label="asset.name"
                                :value="asset.id" />
                        </el-select>
                    </el-form-item>
                </el-col>
            </el-row>

            <el-row :gutter="20">
                <el-col :span="24">
                    <el-form-item label="Purchase Date" prop="purchase_date">
                        <el-date-picker v-model="formData.purchase_date" type="date" placeholder="Select date"
                            style="width: 100%" />
                    </el-form-item>
                </el-col>
            </el-row>
        </el-form>

        <!-- Dialog footer with action buttons -->
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
import { ref, computed } from 'vue'
import { ElMessage } from 'element-plus'

// Define component props
const props = defineProps({
    visible: {
        type: Boolean,
        default: false,
    },
    assets: {
        type: Array,
        default: () => [],
    },
})

// Define emits
const emit = defineEmits(['update:visible', 'submit'])

// Reactive references
const formRef = ref(null)
const selectedAssetId = ref('')
const formData = ref({
    purchase_date: '',
})

// Handle dialog visibility changes
const handleVisibilityChange = value => {
    emit('update:visible', value)
}

// Update purchase date when an asset is selected
const handleAssetChange = assetId => {
    const selectedAsset = props.assets.find(asset => asset.id === assetId)
    if (selectedAsset) {
        formData.value.purchase_date = selectedAsset.purchase_date ? new Date(selectedAsset.purchase_date) : new Date()
    }
}

// Reset form fields and close dialog
const resetForm = () => {
    selectedAssetId.value = ''
    formData.value.purchase_date = ''
    formRef.value?.resetFields()
    close()
}

// Close dialog
const close = () => {
    emit('update:visible', false)
}

// Handle form submission
const submitForm = () => {
    if (!selectedAssetId.value) {
        ElMessage.warning('Please select an asset')
        return
    }

    if (!formData.value.purchase_date) {
        ElMessage.warning('Please select a purchase date')
        return
    }

    const updatedAsset = {
        id: selectedAssetId.value,
        purchase_date: formData.value.purchase_date.toISOString().split('T')[0], // Format as YYYY-MM-DD
    }

    // Emit updated asset data
    emit('submit', updatedAsset)
    resetForm()
}
</script>

<style scoped>
.dialog-footer {
    display: flex;
    justify-content: flex-end;
}
</style>
```