<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Users</h1>
        <p class="mt-1 text-sm text-gray-500">
          Manage system users and their roles
        </p>
      </div>
      <router-link
        v-if="authStore.hasPermission('users.create')"
        to="/users/create"
        class="btn btn-primary"
      >
        <PlusIcon class="w-5 h-5 mr-2" />
        Add User
      </router-link>
    </div>

    <!-- Filters -->
    <div class="card p-4">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <input
            v-model="filters.search"
            @input="debouncedSearch"
            type="text"
            placeholder="Search users..."
            class="input"
          />
        </div>
        <div>
          <select v-model="filters.role" @change="fetchUsers" class="input">
            <option value="">All Roles</option>
            <option value="admin">Admin</option>
            <option value="manager">Manager</option>
            <option value="staff">Staff</option>
          </select>
        </div>
        <div>
          <select v-model="filters.department_id" @change="fetchUsers" class="input">
            <option value="">All Departments</option>
            <option v-for="dept in departments" :key="dept.id" :value="dept.id">
              {{ dept.name }}
            </option>
          </select>
        </div>
      </div>
    </div>

    <!-- Users Table -->
    <div class="card overflow-hidden">
      <LoadingSpinner v-if="usersStore.loading" />
      
      <div v-else-if="usersStore.users.length === 0" class="px-6 py-12 text-center">
        <UsersIcon class="mx-auto h-12 w-12 text-gray-400" />
        <p class="mt-2 text-sm text-gray-500">No users found</p>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Roles
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Created
              </th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="user in usersStore.users" :key="user.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-10 w-10">
                    <div class="h-10 w-10 rounded-full bg-primary-100 flex items-center justify-center">
                      <span class="text-primary-700 font-medium text-sm">
                        {{ getUserInitials(user.name) }}
                      </span>
                    </div>
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                    <div class="text-sm text-gray-500">{{ user.email }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">
                  {{ user.department?.name || 'N/A' }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex gap-1">
                  <span
                    v-for="role in user.role_names"
                    :key="role"
                    class="badge badge-primary"
                  >
                    {{ role }}
                  </span>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ formatDate(user.created_at) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex justify-end gap-2">
                  <router-link
                    v-if="authStore.hasPermission('users.update')"
                    :to="`/users/${user.id}/edit`"
                    class="text-primary-600 hover:text-primary-900"
                  >
                    Edit
                  </router-link>
                  <button
                    v-if="authStore.hasPermission('users.delete') && user.id !== authStore.user?.id"
                    @click="confirmDelete(user)"
                    class="text-red-600 hover:text-red-900"
                  >
                    Delete
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <Pagination
        v-if="usersStore.pagination.total > 0"
        :current-page="usersStore.pagination.current_page"
        :total-pages="usersStore.pagination.last_page"
        :total="usersStore.pagination.total"
        :per-page="usersStore.pagination.per_page"
        @page-change="handlePageChange"
      />
    </div>

    <!-- Delete Confirmation -->
    <ConfirmDialog
      :show="showDeleteDialog"
      title="Delete User"
      :message="`Are you sure you want to delete ${userToDelete?.name}? This action cannot be undone.`"
      @confirm="handleDelete"
      @cancel="showDeleteDialog = false"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useUsersStore } from '@/stores/users'
import { useDepartmentsStore } from '@/stores/departments'
import { formatDate, debounce } from '@/utils/helpers'
import { PlusIcon, UsersIcon } from '@heroicons/vue/24/outline'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import Pagination from '@/components/common/Pagination.vue'
import ConfirmDialog from '@/components/common/ConfirmDialog.vue'

const authStore = useAuthStore()
const usersStore = useUsersStore()
const departmentsStore = useDepartmentsStore()

const filters = ref({
  search: '',
  role: '',
  department_id: ''
})

const showDeleteDialog = ref(false)
const userToDelete = ref(null)

const departments = computed(() => departmentsStore.departments)

const fetchUsers = async () => {
  try {
    await usersStore.fetchUsers({
      ...filters.value,
      page: usersStore.pagination.current_page
    })
  } catch (error) {
    console.error('Error fetching users:', error)
  }
}

const debouncedSearch = debounce(fetchUsers, 500)

const handlePageChange = (page) => {
  usersStore.pagination.current_page = page
  fetchUsers()
}

const getUserInitials = (name) => {
  return name
    .split(' ')
    .map(n => n[0])
    .join('')
    .toUpperCase()
    .substring(0, 2)
}

const confirmDelete = (user) => {
  userToDelete.value = user
  showDeleteDialog.value = true
}

const handleDelete = async () => {
  try {
    await usersStore.deleteUser(userToDelete.value.id)
    showDeleteDialog.value = false
    userToDelete.value = null
  } catch (error) {
    alert('Failed to delete user')
  }
}

onMounted(async () => {
  await fetchUsers()
  await departmentsStore.fetchDepartments()
})
</script>
