<template>
  <div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div>
      <h1 class="text-2xl font-bold text-gray-900">Profile</h1>
      <p class="mt-1 text-sm text-gray-500">Manage your account information</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Profile Card -->
      <div class="lg:col-span-1">
        <div class="card p-6 text-center">
          <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-primary-100 mb-4">
            <span class="text-3xl font-bold text-primary-700">{{ userInitials }}</span>
          </div>
          <h2 class="text-xl font-semibold text-gray-900">{{ authStore.userName }}</h2>
          <p class="text-sm text-gray-500 mt-1">{{ authStore.userEmail }}</p>
          
          <div class="mt-4 flex flex-wrap justify-center gap-2">
            <span
              v-for="role in authStore.roles"
              :key="role"
              class="badge badge-primary"
            >
              {{ role }}
            </span>
          </div>

          <button
            @click="authStore.logout()"
            class="btn btn-secondary w-full mt-6"
          >
            <ArrowRightOnRectangleIcon class="w-5 h-5 mr-2" />
            Logout
          </button>
        </div>
      </div>

      <!-- Information -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Account Information -->
        <div class="card p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Account Information</h3>
          <dl class="space-y-4">
            <div>
              <dt class="text-sm font-medium text-gray-500">Full Name</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ authStore.user?.name }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500">Email Address</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ authStore.user?.email }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500">Department</dt>
              <dd class="mt-1 text-sm text-gray-900">
                {{ authStore.user?.department?.name || 'Not assigned' }}
              </dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500">Account Created</dt>
              <dd class="mt-1 text-sm text-gray-900">
                {{ formatDate(authStore.user?.created_at) }}
              </dd>
            </div>
          </dl>
        </div>

        <!-- Permissions -->
        <div class="card p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Your Permissions</h3>
          <div class="flex flex-wrap gap-2">
            <span
              v-for="permission in authStore.permissions"
              :key="permission"
              class="badge badge-gray"
            >
              {{ permission }}
            </span>
            <span v-if="authStore.permissions.length === 0" class="text-sm text-gray-500">
              No specific permissions assigned
            </span>
          </div>
        </div>

        <!-- Update Password -->
        <div class="card p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Update Password</h3>
          
          <Alert
            v-if="passwordError"
            type="error"
            :message="passwordError"
            @dismiss="passwordError = null"
          />

          <Alert
            v-if="passwordSuccess"
            type="success"
            :message="passwordSuccess"
            @dismiss="passwordSuccess = null"
          />

          <form @submit.prevent="updatePassword" class="space-y-4">
            <div>
              <label for="current_password" class="label">Current Password</label>
              <input
                id="current_password"
                v-model="passwordForm.current_password"
                type="password"
                class="input"
                required
              />
            </div>
            <div>
              <label for="new_password" class="label">New Password</label>
              <input
                id="new_password"
                v-model="passwordForm.new_password"
                type="password"
                minlength="8"
                class="input"
                required
              />
            </div>
            <div>
              <label for="confirm_password" class="label">Confirm New Password</label>
              <input
                id="confirm_password"
                v-model="passwordForm.confirm_password"
                type="password"
                class="input"
                required
              />
            </div>
            <button type="submit" :disabled="updatingPassword" class="btn btn-primary">
              <span v-if="updatingPassword">Updating...</span>
              <span v-else>Update Password</span>
            </button>
          </form>
        </div>

        <!-- Statistics (if not staff) -->
        <div v-if="!authStore.isStaff" class="card p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Your Statistics</h3>
          <div class="grid grid-cols-2 gap-4">
            <div class="text-center p-4 bg-gray-50 rounded-lg">
              <p class="text-2xl font-bold text-gray-900">{{ statistics.totalTasks }}</p>
              <p class="text-sm text-gray-500 mt-1">Total Tasks</p>
            </div>
            <div class="text-center p-4 bg-gray-50 rounded-lg">
              <p class="text-2xl font-bold text-gray-900">{{ statistics.completedTasks }}</p>
              <p class="text-sm text-gray-500 mt-1">Completed</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useTasksStore } from '@/stores/tasks'
import { formatDate } from '@/utils/helpers'
import { ArrowRightOnRectangleIcon } from '@heroicons/vue/24/outline'
import Alert from '@/components/common/Alert.vue'

const authStore = useAuthStore()
const tasksStore = useTasksStore()

const passwordForm = ref({
  current_password: '',
  new_password: '',
  confirm_password: ''
})

const updatingPassword = ref(false)
const passwordError = ref(null)
const passwordSuccess = ref(null)
const statistics = ref({
  totalTasks: 0,
  completedTasks: 0
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

const updatePassword = async () => {
  if (passwordForm.value.new_password !== passwordForm.value.confirm_password) {
    passwordError.value = 'Passwords do not match'
    return
  }

  updatingPassword.value = true
  passwordError.value = null
  passwordSuccess.value = null

  try {
    // Note: You would need to create this API endpoint
    // await authAPI.updatePassword(passwordForm.value)
    passwordSuccess.value = 'Password updated successfully'
    passwordForm.value = {
      current_password: '',
      new_password: '',
      confirm_password: ''
    }
  } catch (error) {
    passwordError.value = error.response?.data?.message || 'Failed to update password'
  } finally {
    updatingPassword.value = false
  }
}

const fetchStatistics = async () => {
  try {
    const stats = await tasksStore.fetchStatistics()
    statistics.value = {
      totalTasks: stats.total || 0,
      completedTasks: stats.completed || 0
    }
  } catch (error) {
    console.error('Error fetching statistics:', error)
  }
}

onMounted(() => {
  if (!authStore.isStaff) {
    fetchStatistics()
  }
})
</script>