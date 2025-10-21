<template>
  <div class="space-y-6">
    <!-- Header -->
    <div>
      <h1 class="text-2xl font-bold text-gray-900">My Tasks</h1>
      <p class="mt-1 text-sm text-gray-500">Tasks assigned to you</p>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div class="card p-4">
        <div class="flex items-center">
          <div class="p-3 rounded-lg bg-yellow-100">
            <ClockIcon class="h-6 w-6 text-yellow-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm text-gray-500">Pending</p>
            <p class="text-2xl font-semibold">{{ stats.pending }}</p>
          </div>
        </div>
      </div>
      <div class="card p-4">
        <div class="flex items-center">
          <div class="p-3 rounded-lg bg-blue-100">
            <ArrowPathIcon class="h-6 w-6 text-blue-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm text-gray-500">In Progress</p>
            <p class="text-2xl font-semibold">{{ stats.inProgress }}</p>
          </div>
        </div>
      </div>
      <div class="card p-4">
        <div class="flex items-center">
          <div class="p-3 rounded-lg bg-green-100">
            <CheckCircleIcon class="h-6 w-6 text-green-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm text-gray-500">Completed</p>
            <p class="text-2xl font-semibold">{{ stats.completed }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Filter -->
    <div class="card p-4">
      <select v-model="statusFilter" @change="filterTasks" class="input max-w-xs">
        <option value="">All Tasks</option>
        <option value="pending">Pending</option>
        <option value="in_progress">In Progress</option>
        <option value="completed">Completed</option>
      </select>
    </div>

    <!-- Tasks List -->
    <LoadingSpinner v-if="loading" />

    <div v-else-if="filteredTasks.length === 0" class="card px-6 py-12 text-center">
      <ClipboardDocumentListIcon class="mx-auto h-12 w-12 text-gray-400" />
      <p class="mt-2 text-sm text-gray-500">No tasks found</p>
    </div>

    <div v-else class="space-y-4">
      <div
        v-for="task in filteredTasks"
        :key="task.id"
        class="card p-6 hover:shadow-md transition-shadow"
      >
        <div class="flex items-start justify-between">
          <div class="flex-1">
            <div class="flex items-center gap-3 mb-2">
              <h3 class="text-lg font-semibold text-gray-900">{{ task.title }}</h3>
              <span :class="['badge', getStatusColor(task.status)]">
                {{ getStatusLabel(task.status) }}
              </span>
            </div>
            <p class="text-sm text-gray-600 mb-4">{{ task.description }}</p>
            
            <div class="flex flex-wrap gap-4 text-sm text-gray-500">
              <div class="flex items-center">
                <BuildingOfficeIcon class="w-4 h-4 mr-1" />
                {{ task.department?.name }}
              </div>
              <div class="flex items-center">
                <UserIcon class="w-4 h-4 mr-1" />
                Assigned by: {{ task.assigned_by?.name }}
              </div>
              <div v-if="task.due_date" class="flex items-center">
                <CalendarIcon class="w-4 h-4 mr-1" />
                Due: {{ formatDate(task.due_date) }}
              </div>
            </div>
          </div>

          <div class="ml-4 flex gap-2">
            <button
              v-if="task.status === 'pending'"
              @click="updateStatus(task.id, 'in_progress')"
              class="btn btn-sm btn-secondary"
            >
              Start
            </button>
            <button
              v-if="task.status !== 'completed'"
              @click="completeTask(task.id)"
              class="btn btn-sm btn-primary"
            >
              Complete
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useTasksStore } from '@/stores/tasks'
import { formatDate, getStatusColor, getStatusLabel } from '@/utils/helpers'
import {
  ClipboardDocumentListIcon,
  ClockIcon,
  ArrowPathIcon,
  CheckCircleIcon,
  BuildingOfficeIcon,
  UserIcon,
  CalendarIcon
} from '@heroicons/vue/24/outline'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'

const tasksStore = useTasksStore()

const loading = ref(false)
const tasks = ref([])
const statusFilter = ref('')

const stats = computed(() => {
  if (!tasks.value || !Array.isArray(tasks.value)) {
    return {
      pending: 0,
      inProgress: 0,
      completed: 0
    }
  }
  return {
    pending: tasks.value.filter(t => t.status === 'pending').length,
    inProgress: tasks.value.filter(t => t.status === 'in_progress').length,
    completed: tasks.value.filter(t => t.status === 'completed').length
  }
})

const filteredTasks = computed(() => {
  if (!tasks.value || !Array.isArray(tasks.value)) return []
  if (!statusFilter.value) return tasks.value
  return tasks.value.filter(t => t.status === statusFilter.value)
})

const fetchMyTasks = async () => {
  loading.value = true
  try {
    const response = await tasksStore.fetchMyTasks()
    // Handle both array response and data.data response
    if (Array.isArray(response)) {
      tasks.value = response
    } else if (response && response.data) {
      tasks.value = Array.isArray(response.data) ? response.data : []
    } else {
      tasks.value = []
    }
  } catch (error) {
    console.error('Error fetching tasks:', error)
    tasks.value = []
  } finally {
    loading.value = false
  }
}

const updateStatus = async (taskId, status) => {
  try {
    await tasksStore.updateTaskStatus(taskId, status)
    await fetchMyTasks()
  } catch (error) {
    alert('Failed to update task status')
  }
}

const completeTask = async (taskId) => {
  try {
    await tasksStore.completeTask(taskId)
    await fetchMyTasks()
  } catch (error) {
    alert('Failed to complete task')
  }
}

const filterTasks = () => {
  // Triggers computed property update
}

onMounted(() => {
  fetchMyTasks()
})
</script>
