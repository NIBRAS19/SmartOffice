<!-- ============================================ -->
<!-- src/layouts/MainLayout.vue -->
<!-- ============================================ -->
<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Sidebar -->
    <div :class="[
      'fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-gray-200 transform transition-transform duration-300 ease-in-out',
      sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
    ]">
      <div class="flex flex-col h-full">
        <!-- Logo -->
        <div class="flex items-center justify-between h-16 px-6 border-b border-gray-200">
          <div class="flex items-center space-x-2">
            <div class="w-8 h-8 bg-primary-600 rounded-lg flex items-center justify-center">
              <span class="text-white font-bold text-xl">S</span>
            </div>
            <span class="text-xl font-bold text-gray-900">SmartOffice</span>
          </div>
          <button @click="sidebarOpen = false" class="lg:hidden p-2 rounded-lg hover:bg-gray-100">
            <XMarkIcon class="w-6 h-6" />
          </button>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
          <router-link
            v-for="item in navigation"
            :key="item.name"
            :to="item.to"
            v-slot="{ isActive }"
            custom
          >
            <a
              @click="navigateTo(item.to)"
              :class="[
                isActive
                  ? 'bg-primary-50 text-primary-700'
                  : 'text-gray-700 hover:bg-gray-50',
                'group flex items-center px-3 py-2 text-sm font-medium rounded-lg cursor-pointer transition-colors'
              ]"
            >
              <component
                :is="item.icon"
                :class="[
                  isActive ? 'text-primary-600' : 'text-gray-400 group-hover:text-gray-600',
                  'mr-3 h-5 w-5 flex-shrink-0'
                ]"
              />
              {{ item.name }}
            </a>
          </router-link>
        </nav>

        <!-- User Menu -->
        <div class="border-t border-gray-200 p-4">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-10 h-10 rounded-full bg-primary-600 flex items-center justify-center">
                <span class="text-white font-medium">{{ userInitials }}</span>
              </div>
            </div>
            <div class="ml-3 flex-1">
              <p class="text-sm font-medium text-gray-900">{{ authStore.userName }}</p>
              <p class="text-xs text-gray-500">{{ primaryRole }}</p>
            </div>
            <button @click="handleLogout" class="p-2 rounded-lg hover:bg-gray-100" title="Logout">
              <ArrowRightOnRectangleIcon class="w-5 h-5 text-gray-500" />
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Main content -->
    <div class="lg:pl-64">
      <!-- Top bar -->
      <div class="sticky top-0 z-40 bg-white border-b border-gray-200 h-16">
        <div class="flex items-center justify-between h-full px-4 sm:px-6">
          <button @click="sidebarOpen = true" class="lg:hidden p-2 rounded-lg hover:bg-gray-100">
            <Bars3Icon class="w-6 h-6" />
          </button>

          <div class="flex items-center space-x-4 ml-auto">
            <!-- Notifications -->
            <button class="p-2 rounded-lg hover:bg-gray-100 relative">
              <BellIcon class="w-6 h-6 text-gray-500" />
              <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
            </button>

            <!-- Profile -->
            <router-link to="/profile" class="flex items-center space-x-2 hover:bg-gray-100 rounded-lg p-2">
              <UserCircleIcon class="w-6 h-6 text-gray-500" />
            </router-link>
          </div>
        </div>
      </div>

      <!-- Page content -->
      <main class="p-4 sm:p-6 lg:p-8">
        <router-view />
      </main>
    </div>

    <!-- Mobile sidebar backdrop -->
    <div
      v-if="sidebarOpen"
      @click="sidebarOpen = false"
      class="fixed inset-0 z-40 bg-black/30 lg:hidden"
    ></div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import {
  HomeIcon,
  UsersIcon,
  BuildingOfficeIcon,
  ClipboardDocumentListIcon,
  CheckCircleIcon,
  ShieldCheckIcon,
  Bars3Icon,
  XMarkIcon,
  BellIcon,
  UserCircleIcon,
  ArrowRightOnRectangleIcon
} from '@heroicons/vue/24/outline'

const router = useRouter()
const authStore = useAuthStore()
const sidebarOpen = ref(false)

const navigation = computed(() => {
  const items = []
  
  // Dashboard - everyone can see
  items.push({ 
    name: 'Dashboard', 
    to: '/dashboard', 
    icon: HomeIcon, 
    show: true 
  })
  
  // My Tasks - everyone can see
  items.push({ 
    name: 'My Tasks', 
    to: '/my-tasks', 
    icon: CheckCircleIcon, 
    show: true 
  })
  
  // Tasks - Admin, Manager, Staff with permission
  if (authStore.isAdmin || authStore.isManager || authStore.hasPermission('tasks.view')) {
    items.push({ 
      name: 'Tasks', 
      to: '/tasks', 
      icon: ClipboardDocumentListIcon, 
      show: true 
    })
  }
  
  // Users - Admin, Manager, or with permission
  if (authStore.isAdmin || authStore.isManager || authStore.hasPermission('users.view')) {
    items.push({ 
      name: 'Users', 
      to: '/users', 
      icon: UsersIcon, 
      show: true 
    })
  }
  
  // Departments - Admin, Manager, or with permission
  if (authStore.isAdmin || authStore.isManager || authStore.hasPermission('departments.view')) {
    items.push({ 
      name: 'Departments', 
      to: '/departments', 
      icon: BuildingOfficeIcon, 
      show: true 
    })
  }
  
  // Roles - Admin only
  if (authStore.isAdmin) {
    items.push({ 
      name: 'Roles', 
      to: '/roles', 
      icon: ShieldCheckIcon, 
      show: true 
    })
  }

  return items.filter(item => item.show)
})

const userInitials = computed(() => {
  const name = authStore.userName
  return name
    .split(' ')
    .map(n => n[0])
    .join('')
    .toUpperCase()
    .substring(0, 2)
})

const primaryRole = computed(() => {
  if (authStore.isAdmin) return 'Administrator'
  if (authStore.isManager) return 'Manager'
  if (authStore.isStaff) return 'Staff'
  return 'User'
})

const navigateTo = (path) => {
  router.push(path)
  sidebarOpen.value = false
}

const handleLogout = async () => {
  if (confirm('Are you sure you want to logout?')) {
    await authStore.logout()
  }
}

// Fetch permissions on mount
onMounted(async () => {
  if (!authStore.permissions.length) {
    await authStore.fetchPermissions()
  }
})
</script>