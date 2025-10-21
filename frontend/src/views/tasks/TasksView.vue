<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">All Tasks</h1>
        <p class="mt-1 text-sm text-gray-500">Manage and track all tasks</p>
      </div>
      <router-link
        v-if="authStore.hasPermission('tasks.create')"
        to="/tasks/create"
        class="btn btn-primary"
      >
        <PlusIcon class="w-5 h-5 mr-2" />
        Create Task
      </router-link>
    </div>

    <!-- Filters -->
    <div class="card p-4">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <input
            v-model="filters.search"
            @input="debouncedSearch"
            type="text"
            placeholder="Search tasks..."
            class="input"
          />
        </div>
        <div>
          <select v-model="filters.status" @change="fetchTasks" class="input">
            <option value="">All Status</option>
            <option value="pending">Pending</option>
            <option value="in_progress">In Progress</option>
            <option value="completed">Completed</option>
          </select>
        </div>
        <div v-if="authStore.hasAnyRole(['admin', 'manager'])">
          <select v-model="filters.department_id" @change="fetchTasks" class="input">
            <option value="">All Departments</option>
            <option v-for="dept in departments" :key="dept.id" :value="dept.id">
              {{ dept.name }}
            </option>
          </select>
        </div>
        <div v-if="authStore.hasAnyRole(['admin', 'manager'])">
          <select v-model="filters.assigned_to" @change="fetchTasks" class="input">
            <option value="">All Assignees</option>
            <option v-for="user in users" :key="user.id" :value="user.id">
              {{ user.name }}
            </option>
          </select>
        </div>
      </div>
    </div>

    <!-- Tasks Grid -->
    <LoadingSpinner v-if="tasksStore.loading" />

    <div v-else-if="tasksStore.tasks.length === 0" class="card px-6 py-12 text-center">
      <ClipboardDocumentListIcon class="mx-auto h-12 w-12 text-gray-400" />
      <p class="mt-2 text-sm text-gray-500">No tasks found</p>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <div
        v-for="task in tasksStore.tasks"
        :key="task.id"
        class="card p-6 hover:shadow-md transition-shadow"
      >
        <div class="flex items-start justify-between mb-3">
          <span :class="['badge', getStatusColor(task.status)]">
            {{ getStatusLabel(task.status) }}
          </span>
          <div class="flex gap-2">
            <router-link
              v-if="authStore.hasPermission('tasks.update')"
              :to="`/tasks/${task.id}/edit`"
              class="text-primary-600 hover:text-primary-900 text-sm"
            >
              Edit
            </router-link>
            <button
              v-if="authStore.hasPermission('tasks.delete')"
              @click="confirmDelete(task)"
              class="text-red-600 hover:text-red-900 text-sm"
            >
              Delete
            </button>
          </div>
        </div>

        <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ task.title }}</h3>
        <p class="text-sm text-gray-600 mb-4 line-clamp-2">{{ task.description }}</p>

        <div class="space-y-2 text-sm">
          <div class="flex items-center text-gray-600">
            <UserIcon class="w-4 h-4 mr-2" />
            <span>{{ task.assigned_to?.name }}</span>
          </div>
          <div v-if="task.department" class="flex items-center text-gray-600">
            <BuildingOfficeIcon class="w-4 h-4 mr-2" />
            <span>{{ task.department.name }}</span>
          </div>
          <div v-if="task.due_date" class="flex items-center text-gray-600">
            <CalendarIcon class="w-4 h-4 mr-2" />
            <span>Due: {{ formatDate(task.due_date) }}</span>
          </div>
        </div>

        <div class="mt-4 pt-4 border-t border-gray-200">
          <button
            v-if="task.status !== 'completed' && canCompleteTask(task)"
            @click="completeTask(task.id)"
            class="btn btn-primary w-full text-sm"
          >
            Mark as Complete
          </button>
        </div>
      </div>
    </div>

    <!-- Pagination -->
    <Pagination
      v-if="tasksStore.pagination.total > 0"
      :current-page="tasksStore.pagination.current_page"
      :total-pages="tasksStore.pagination.last_page"
      :total="tasksStore.pagination.total"
      :per-page="tasksStore.pagination.per_page"
      @page-change="handlePageChange"
    />

    <!-- Delete Confirmation -->
    <ConfirmDialog
      :show="showDeleteDialog"
      title="Delete Task"
      :message="`Are you sure you want to delete this task? This action cannot be undone.`"
      @confirm="handleDelete"
      @cancel="showDeleteDialog = false"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useTasksStore } from '@/stores/tasks'
import { useDepartmentsStore } from '@/stores/departments'
import { useUsersStore } from '@/stores/users'
import { formatDate, getStatusColor, getStatusLabel, debounce } from '@/utils/helpers'
import {
  PlusIcon,
  ClipboardDocumentListIcon,
  UserIcon,
  BuildingOfficeIcon,
  CalendarIcon
} from '@heroicons/vue/24/outline'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import Pagination from '@/components/common/Pagination.vue'
import ConfirmDialog from '@/components/common/ConfirmDialog.vue'

const authStore = useAuthStore()
const tasksStore = useTasksStore()
const departmentsStore = useDepartmentsStore()
const usersStore = useUsersStore()

const filters = ref({
  search: '',
  status: '',
  department_id: '',
  assigned_to: ''
})

const showDeleteDialog = ref(false)
const taskToDelete = ref(null)
const departments = ref([])
const users = ref([])

const fetchTasks = async () => {
  try {
    await tasksStore.fetchTasks({
      ...filters.value,
      page: tasksStore.pagination.current_page
    })
  } catch (error) {
    console.error('Error fetching tasks:', error)
  }
}

const debouncedSearch = debounce(fetchTasks, 500)

const handlePageChange = (page) => {
  tasksStore.pagination.current_page = page
  fetchTasks()
}

const canCompleteTask = (task) => {
  return authStore.hasPermission('tasks.update') &&
    (authStore.isAdmin || authStore.isManager || task.assigned_to?.id === authStore.user?.id)
}

const completeTask = async (taskId) => {
  try {
    await tasksStore.completeTask(taskId)
    await fetchTasks()
  } catch (error) {
    alert('Failed to complete task')
  }
}

const confirmDelete = (task) => {
  taskToDelete.value = task
  showDeleteDialog.value = true
}

const handleDelete = async () => {
  try {
    await tasksStore.deleteTask(taskToDelete.value.id)
    showDeleteDialog.value = false
    taskToDelete.value = null
  } catch (error) {
    alert('Failed to delete task')
  }
}

onMounted(async () => {
  await fetchTasks()
  if (authStore.hasAnyRole(['admin', 'manager'])) {
    await departmentsStore.fetchDepartments()
    departments.value = departmentsStore.departments
    await usersStore.fetchUsers({ per_page: 100 })
    users.value = usersStore.users
  }
})
</script>