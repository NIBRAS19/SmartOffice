<template>
  <div class="space-y-6">
    <!-- Header -->
    <div>
      <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
      <p class="mt-1 text-sm text-gray-500">
        Welcome back, {{ authStore.userName }}!
      </p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
      <div
        v-for="stat in stats"
        :key="stat.name"
        class="card p-6 hover:shadow-md transition-shadow"
      >
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div :class="[stat.bgColor, 'p-3 rounded-lg']">
              <component :is="stat.icon" :class="[stat.iconColor, 'h-6 w-6']" />
            </div>
          </div>
          <div class="ml-5 w-0 flex-1">
            <dl>
              <dt class="text-sm font-medium text-gray-500 truncate">
                {{ stat.name }}
              </dt>
              <dd class="flex items-baseline">
                <div class="text-2xl font-semibold text-gray-900">
                  {{ stat.value }}
                </div>
              </dd>
            </dl>
          </div>
        </div>
      </div>
    </div>

    <!-- My Tasks Section -->
    <div class="card">
      <div class="px-6 py-5 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-medium text-gray-900">My Tasks</h3>
          <router-link to="/my-tasks" class="text-sm text-primary-600 hover:text-primary-700 font-medium">
            View all
          </router-link>
        </div>
      </div>
      
      <LoadingSpinner v-if="loadingTasks" />
      
      <div v-else-if="myTasks.length === 0" class="px-6 py-12 text-center">
        <ClipboardDocumentListIcon class="mx-auto h-12 w-12 text-gray-400" />
        <p class="mt-2 text-sm text-gray-500">No tasks assigned to you</p>
      </div>
      
      <ul v-else class="divide-y divide-gray-200">
        <li
          v-for="task in myTasks.slice(0, 5)"
          :key="task.id"
          class="px-6 py-4 hover:bg-gray-50 transition-colors"
        >
          <div class="flex items-center justify-between">
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-900 truncate">
                {{ task.title }}
              </p>
              <p class="mt-1 text-sm text-gray-500 truncate">
                {{ task.description }}
              </p>
              <div class="mt-2 flex items-center gap-2">
                <span :class="['badge', getStatusColor(task.status)]">
                  {{ getStatusLabel(task.status) }}
                </span>
                <span v-if="task.due_date" class="text-xs text-gray-500">
                  Due: {{ formatDate(task.due_date) }}
                </span>
              </div>
            </div>
            <div class="ml-4 flex-shrink-0">
              <button
                v-if="task.status !== 'completed'"
                @click="markAsComplete(task.id)"
                class="btn btn-sm btn-primary"
              >
                Complete
              </button>
            </div>
          </div>
        </li>
      </ul>
    </div>

    <!-- Recent Activity (if admin/manager) -->
    <div v-if="authStore.isAdmin || authStore.isManager" class="grid grid-cols-1 gap-5 lg:grid-cols-2">
      <!-- Task Status Chart -->
      <div class="card p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Task Status</h3>
        <div class="h-64">
          <Doughnut v-if="chartData" :data="chartData" :options="chartOptions" />
        </div>
      </div>

      <!-- Recent Users -->
      <div class="card">
        <div class="px-6 py-5 border-b border-gray-200">
          <h3 class="text-lg font-medium text-gray-900">Recent Users</h3>
        </div>
        <LoadingSpinner v-if="loadingUsers" />
        <ul v-else class="divide-y divide-gray-200">
          <li
            v-for="user in recentUsers"
            :key="user.id"
            class="px-6 py-4 hover:bg-gray-50"
          >
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center">
                  <span class="text-primary-700 font-medium text-sm">
                    {{ getUserInitials(user.name) }}
                  </span>
                </div>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-900">{{ user.name }}</p>
                <p class="text-sm text-gray-500">{{ user.email }}</p>
              </div>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useTasksStore } from '@/stores/tasks'
import { useUsersStore } from '@/stores/users'
import { formatDate, getStatusColor, getStatusLabel } from '@/utils/helpers'
import {
  ClipboardDocumentListIcon,
  CheckCircleIcon,
  ClockIcon,
  UsersIcon,
  BuildingOfficeIcon
} from '@heroicons/vue/24/outline'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import { Doughnut } from 'vue-chartjs'
import {
  Chart as ChartJS,
  ArcElement,
  Tooltip,
  Legend
} from 'chart.js'

ChartJS.register(ArcElement, Tooltip, Legend)

const authStore = useAuthStore()
const tasksStore = useTasksStore()
const usersStore = useUsersStore()

const loadingTasks = ref(false)
const loadingUsers = ref(false)
const myTasks = ref([])
const recentUsers = ref([])
const statistics = ref(null)

const stats = computed(() => {
  const data = [
    {
      name: 'Total Tasks',
      value: statistics.value?.total || 0,
      icon: ClipboardDocumentListIcon,
      bgColor: 'bg-blue-100',
      iconColor: 'text-blue-600'
    },
    {
      name: 'Pending',
      value: statistics.value?.pending || 0,
      icon: ClockIcon,
      bgColor: 'bg-yellow-100',
      iconColor: 'text-yellow-600'
    },
    {
      name: 'Completed',
      value: statistics.value?.completed || 0,
      icon: CheckCircleIcon,
      bgColor: 'bg-green-100',
      iconColor: 'text-green-600'
    },
  ]

  if (authStore.isAdmin) {
    data.push({
      name: 'Total Users',
      value: usersStore.pagination?.total || 0,
      icon: UsersIcon,
      bgColor: 'bg-purple-100',
      iconColor: 'text-purple-600'
    })
  }

  return data
})

const chartData = computed(() => {
  if (!statistics.value) return null
  
  return {
    labels: ['Pending', 'In Progress', 'Completed'],
    datasets: [{
      data: [
        statistics.value.pending || 0,
        statistics.value.in_progress || 0,
        statistics.value.completed || 0
      ],
      backgroundColor: [
        '#FCD34D',
        '#60A5FA',
        '#34D399'
      ],
      borderWidth: 0
    }]
  }
})

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'bottom'
    }
  }
}

const fetchData = async () => {
  try {
    loadingTasks.value = true
    const tasksResponse = await tasksStore.fetchMyTasks()
    myTasks.value = Array.isArray(tasksResponse) ? tasksResponse : []

    const statsResponse = await tasksStore.fetchStatistics()
    statistics.value = statsResponse || { total: 0, pending: 0, in_progress: 0, completed: 0, overdue: 0 }

    if (authStore.isAdmin || authStore.isManager) {
      loadingUsers.value = true
      await usersStore.fetchUsers({ per_page: 5 })
      recentUsers.value = usersStore.users || []
    }
  } catch (error) {
    console.error('Error fetching dashboard data:', error)
    myTasks.value = []
    statistics.value = { total: 0, pending: 0, in_progress: 0, completed: 0, overdue: 0 }
  } finally {
    loadingTasks.value = false
    loadingUsers.value = false
  }
}

const markAsComplete = async (taskId) => {
  try {
    await tasksStore.completeTask(taskId)
    await fetchData()
  } catch (error) {
    alert('Failed to complete task')
  }
}

const getUserInitials = (name) => {
  return name
    .split(' ')
    .map(n => n[0])
    .join('')
    .toUpperCase()
    .substring(0, 2)
}

onMounted(() => {
  fetchData()
})
</script>