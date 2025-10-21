<template>
  <div class="space-y-6">
    <!-- Header -->
    <div>
      <h1 class="text-2xl font-bold text-gray-900">Roles & Permissions</h1>
      <p class="mt-1 text-sm text-gray-500">Manage system roles and their permissions</p>
    </div>

    <LoadingSpinner v-if="loading" />

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
        v-for="role in roles"
        :key="role.id"
        class="card p-6"
      >
        <div class="flex items-start justify-between mb-4">
          <div>
            <h3 class="text-lg font-semibold text-gray-900">{{ role.name }}</h3>
            <p class="text-sm text-gray-500 mt-1">{{ role.description }}</p>
          </div>
          <span class="badge badge-primary">{{ role.users_count || 0 }} users</span>
        </div>

        <div class="space-y-2">
          <p class="text-sm font-medium text-gray-700">Permissions:</p>
          <div class="flex flex-wrap gap-2">
            <span
              v-for="permission in role.permissions"
              :key="permission.id"
              class="badge badge-gray text-xs"
            >
              {{ permission.name }}
            </span>
          </div>
        </div>

        <div v-if="!['admin', 'manager', 'staff'].includes(role.slug)" class="mt-4 pt-4 border-t border-gray-200">
          <button
            @click="confirmDelete(role)"
            class="text-red-600 hover:text-red-900 text-sm font-medium"
          >
            Delete Role
          </button>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation -->
    <ConfirmDialog
      :show="showDeleteDialog"
      title="Delete Role"
      :message="`Are you sure you want to delete the ${roleToDelete?.name} role? This action cannot be undone.`"
      @confirm="handleDelete"
      @cancel="showDeleteDialog = false"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { rolesAPI } from '@/api/roles'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import ConfirmDialog from '@/components/common/ConfirmDialog.vue'

const loading = ref(false)
const roles = ref([])
const showDeleteDialog = ref(false)
const roleToDelete = ref(null)

const fetchRoles = async () => {
  loading.value = true
  try {
    const response = await rolesAPI.getAll()
    roles.value = response.data
  } catch (error) {
    console.error('Error fetching roles:', error)
  } finally {
    loading.value = false
  }
}

const confirmDelete = (role) => {
  roleToDelete.value = role
  showDeleteDialog.value = true
}

const handleDelete = async () => {
  try {
    await rolesAPI.delete(roleToDelete.value.id)
    await fetchRoles()
    showDeleteDialog.value = false
    roleToDelete.value = null
  } catch (error) {
    alert(error.response?.data?.message || 'Failed to delete role')
  }
}

onMounted(() => {
  fetchRoles()
})
</script>
