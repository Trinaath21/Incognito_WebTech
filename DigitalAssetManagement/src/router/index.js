// src/router/index.js
import { createRouter, createWebHistory } from 'vue-router'
import Dashboard from '../../src/components/Dashboard.vue'
import ManageAssets from '../../src/components/ManageAssets.vue'

const routes = [
  {
    path: '/',
    name: 'Dashboard',
    component: Dashboard
  },
  {
    path: '/manage-assets',
    name: 'ManageAssets',
    component: ManageAssets
  }
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes
})

export default router