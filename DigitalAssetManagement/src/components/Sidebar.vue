<template>
    <div class="sidebar">
      <el-menu
        :default-active="activeMenu"
        class="el-menu-vertical-demo"
        background-color="#2c3e50"
        text-color="#ecf0f1"
        active-text-color="#ffd04b"
        router
      >
        <el-menu-item index="/">
          <el-icon><i class="el-icon-menu"></i></el-icon>
          <span>Dashboard</span>
        </el-menu-item>
        
        <el-menu-item index="/manage-assets">
          <el-icon><i class="el-icon-setting"></i></el-icon>
          <span>Manage Assets</span>
        </el-menu-item>
      </el-menu>
    </div>
  </template>
  
  <script setup>
  import { ref, watch } from 'vue'
  import { useRoute } from 'vue-router'
  
  const route = useRoute()
  const activeMenu = ref('/')
  
  // Watch for route changes to update active menu item
  watch(() => route.path, (newPath) => {
    activeMenu.value = newPath
  }, { immediate: true })
  </script>
  
  <style scoped>
  .sidebar {
    width: 220px;
    height: calc(100vh - 60px);
    background-color: #2c3e50;
    padding-top: 20px;
    position: sticky;
    top: 60px;
    overflow: hidden;
  }
  
  .el-menu {
    width: 100%;
    border-right: none;
    overflow: hidden;
  }
  
  .el-menu-item {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  
  .el-menu-item.is-active {
    background-color: #1e2b38 !important;
  }
  </style>