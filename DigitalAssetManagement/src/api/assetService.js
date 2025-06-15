import axios from 'axios'

// Configure axios instance
const apiClient = axios.create({
  baseURL: '/api', // This will now be properly proxied
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
})

export default {
  // Asset CRUD operations
  async getAssets() {
    try {
      const response = await apiClient.get('/assets') // Matches router plural
      return response.data
    } catch (error) {
      console.error('Error fetching assets:', error)
      throw error
    }
  },

  async getAsset(id) {
    try {
      const response = await apiClient.get(`/assets/${id}`) // Matches router plural
      return response.data
    } catch (error) {
      console.error(`Error fetching asset ${id}:`, error)
      throw error
    }
  },

  async createAsset(assetData) {
    try {
      const response = await apiClient.post('/assets', assetData) // Matches router plural
      return response.data
    } catch (error) {
      console.error('Error creating asset:', error)
      throw error
    }
  },

  async updateAsset(id, assetData) {
    console.log('=== ASSET SERVICE UPDATE ASSET ===')
    console.log('API Call - ID:', id)
    console.log('API Call - Data:', assetData)
    console.log('API Call - URL:', `/assets/${id}`)
    try {
      const response = await apiClient.put(`/assets/${id}`, assetData) // Matches router plural
      return response.data
    } catch (error) {
      console.error(`Error updating asset ${id}:`, error)
      throw error
    }
  },

  async deleteAsset(id) {
    try {
      const response = await apiClient.delete(`/assets/${id}`) // Matches router plural
      return response.data
    } catch (error) {
      console.error(`Error deleting asset ${id}:`, error)
      throw error
    }
  },

  // Asset department management - Two methods supported
  // Try the specific department endpoint first
  async changeAssetDepartment(assetId, department) {
    try {
      const response = await apiClient.patch(`/assets/${assetId}/department`, {
        department: department,
      })
      return response.data
    } catch (error) {
      console.error(`Error changing department for asset ${assetId}:`, error)
      throw error
    }
  },

  // Alternative method for department change
  async updateAssetDepartmentAlt(assetId, department) {
    try {
      // Method 2: Using general patch endpoint
      const response = await apiClient.patch(`/assets/${assetId}`, {
        department: department,
      })
      return response.data
    } catch (error) {
      console.error(`Error changing department for asset ${assetId}:`, error)
      throw error
    }
  },

  // Asset status management
  async updateAssetStatus(assetId, status) {
    try {
      const response = await apiClient.patch(`/assets/${assetId}`, {
        status: status,
      })
      return response.data
    } catch (error) {
      console.error(`Error updating status for asset ${assetId}:`, error)
      throw error
    }
  },

  // Warranty expiry update
  // Warranty expiry update - Specific endpoint style
  async updateWarrantyExpiry(assetId, warrantyExpiry) {
    try {
      const response = await apiClient.patch(`/assets/${assetId}`, {
        warranty_expiry: warrantyExpiry,
      })
      return response.data
    } catch (error) {
      console.error(`Error updating warranty expiry for asset ${assetId}:`, error)
      throw error
    }
  },
 // Purchase date update
  async updateAssetPurchaseDate(assetId, purchaseDate) {
    try {
      const response = await apiClient.patch(`/assets/${assetId}`, {
        purchase_date: purchaseDate,
      })
      return response.data
    } catch (error) {
      console.error(`Error updating purchase date for asset ${assetId}:`, error)
      throw error
    }
  },
  // Partial update for any field
  async updateAssetField(assetId, fieldData) {
    try {
      const response = await apiClient.patch(`/assets/${assetId}`, fieldData)
      return response.data
    } catch (error) {
      console.error(`Error updating asset ${assetId}:`, error)
      throw error
    }
  },

  // Categories and departments
  async getCategories() {
    try {
      const response = await apiClient.get('/categories')
      return response.data
    } catch (error) {
      console.error('Error fetching categories:', error)
      throw error
    }
  },

  async getDepartments() {
    try {
      const response = await apiClient.get('/departments')
      return response.data
    } catch (error) {
      console.error('Error fetching departments:', error)
      throw error
    }
  },
}
