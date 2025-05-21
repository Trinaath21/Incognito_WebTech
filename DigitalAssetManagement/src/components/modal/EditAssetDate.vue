<template>
    <el-dialog :model-value="visible" title="Update Asset Purchase Date" width="50%" @close="resetForm"
        @update:model-value="handleVisibilityChange">
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
                    <el-form-item label="Purchase Date" prop="purchaseDate">
                        <el-date-picker v-model="formData.purchaseDate" type="date" placeholder="Select date"
                            style="width: 100%" />
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
import { ref, computed } from 'vue'
import { ElMessage } from 'element-plus'

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

const emit = defineEmits(['update:visible', 'submit'])

const formRef = ref(null)
const selectedAssetId = ref('')
const formData = ref({
    purchaseDate: '',
})

const handleVisibilityChange = value => {
    emit('update:visible', value)
}

const handleAssetChange = assetId => {
    const selectedAsset = props.assets.find(asset => asset.id === assetId)
    if (selectedAsset) {
        formData.value.purchaseDate = selectedAsset.purchaseDate ? new Date(selectedAsset.purchaseDate) : new Date()
    }
}

const resetForm = () => {
    selectedAssetId.value = ''
    formData.value.purchaseDate = ''
    formRef.value?.resetFields()
    close()
}

const close = () => {
    emit('update:visible', false)
}

const submitForm = () => {
    if (!selectedAssetId.value) {
        ElMessage.warning('Please select an asset')
        return
    }

    if (!formData.value.purchaseDate) {
        ElMessage.warning('Please select a purchase date')
        return
    }

    const updatedAsset = {
        id: selectedAssetId.value,
        purchaseDate: formData.value.purchaseDate.toISOString().split('T')[0], // Format as YYYY-MM-DD
    }

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