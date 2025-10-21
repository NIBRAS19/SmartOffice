<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Departments</h1>
        <p class="mt-1 text-sm text-gray-500">Manage company departments</p>
      </div>
      <router-link
        v-if="authStore.hasPermission('departments.create')"
        to="/departments/create"
        class="btn btn-primary"
      >
        <PlusIcon class="w-5 h-5 mr-2" />
        Add Department
      </router-link>
    </div>

    <!-- Search -->
    <div class="card p-4">
      <input
        v-model="searchQuery"
        @input="debouncedSearch"
        type="text"
        placeholder="Search departments..."
        class="input max-w-md"
      />
    </div>

    <!-- Departments Grid -->
    <LoadingSpinner v-if="departmentsStore.loading" />

    <div v-else-if="departmentsStore.departments.length === 0" class="card px-6 py-12 text-center">
      <BuildingOfficeIcon class="mx-auto h-12 w-12 text-gray-400" />
      <p class="mt-2 text-sm text-gray-500">No departments found</p>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
        v-for="department in departmentsStore.departments"
        :key="department.id"
        class="card p-6 hover:shadow-lg transition-all"
      >
        <div class="flex items-start justify-between mb-4">
          <div class="flex items-center">
            <div class="p-3 rounded-lg bg-primary-100">
              <BuildingOfficeIcon class="h-6 w-6 text-primary-600" />
            </div>
            <div class="ml-3">
              <h3 class="text-lg font-semibold text-gray-900">{{ department.name }}</h3>
              <p class="text-sm text-gray-500">{{ department.users_count || 0 }} members</p>
            </div>
          </div>
        </div>

        <p class="text-sm text-gray-600 mb-4 line-clamp-2">
          {{ department.description || 'No description' }}
        </p>

        <div v-if="department.manager" class="flex items-center text-sm text-gray-600 mb-4">
          <UserIcon class="w-4 h-4 mr-2" />
          <span>Manager: {{ department.manager.name }}</span>
        </div>

        <div class="pt-4 border-t border-gray-200 flex justify-between items-center">
          <span class="text-sm text-gray-500">
            {{ department.tasks_count || 0 }} tasks
          </span>
          <div class="flex gap-2">
            <router-link
              v-if="authStore.hasPermission('departments.update')"
              :to="`/departments/${department.id}/edit`"
              class="text-primary-600 hover:text-primary-900 text-sm font-medium"
            >
              Edit
            </router-link>
            <button
              v-if="authStore.hasPermission('departments.delete')"
              @click="confirmDelete(department)"
              class="text-red-600 hover:text-red-900 text-sm font-medium"
            >
              Delete
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation -->
    <ConfirmDialog
      :show="showDeleteDialog"
      title="Delete Department"
      :message="`Are you sure you want to delete ${departmentToDelete?.name}? This action cannot be undone.`"
      @confirm="handleDelete"
      @cancel="showDeleteDialog = false"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useDepartmentsStore } from '@/stores/departments'
import { debounce } from '@/utils/helpers'
import { PlusIcon, BuildingOfficeIcon, UserIcon } from '@heroicons/vue/24/outline'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import ConfirmDialog from '@/components/common/ConfirmDialog.vue'

const authStore = useAuthStore()
const departmentsStore = useDepartmentsStore()

const searchQuery = ref('')
const showDeleteDialog = ref(false)
const departmentToDelete = ref(null)

const fetchDepartments = async () => {
  try {
    await departmentsStore.fetchDepartments({ search: searchQuery.value })
  } catch (error) {
    console.error('Error fetching departments:', error)
  }
}

const debouncedSearch = debounce(fetchDepartments, 500)

const confirmDelete = (department) => {
  departmentToDelete.value = department
  showDeleteDialog.value = true
}

const handleDelete = async () => {
  try {
    await departmentsStore.deleteDepartment(departmentToDelete.value.id)
    showDeleteDialog.value = false
    departmentToDelete.value = null
  } catch (error) {
    alert(error.response?.data?.message || 'Failed to delete department')
  }
}

onMounted(() => {
  fetchDepartments()
})
</script>