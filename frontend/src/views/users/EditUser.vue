<template>
  <div class="max-w-2xl mx-auto space-y-6">
    <div>
      <router-link to="/users" class="text-sm text-primary-600 hover:text-primary-700 flex items-center">
        <ArrowLeftIcon class="w-4 h-4 mr-1" />
        Back to Users
      </router-link>
      <h1 class="mt-2 text-2xl font-bold text-gray-900">Edit User</h1>
    </div>

    <LoadingSpinner v-if="loading" />

    <div v-else>
      <Alert
        v-if="error"
        type="error"
        :message="error"
        @dismiss="error = null"
      />

      <form @submit.prevent="handleSubmit" class="card p-6 space-y-6">
        <div>
          <label for="name" class="label">Full Name *</label>
          <input
            id="name"
            v-model="form.name"
            type="text"
            required
            class="input"
          />
        </div>

        <div>
          <label for="email" class="label">Email *</label>
          <input
            id="email"
            v-model="form.email"
            type="email"
            required
            class="input"
          />
        </div>

        <div>
          <label for="password" class="label">Password (leave blank to keep current)</label>
          <input
            id="password"
            v-model="form.password"
            type="password"
            minlength="8"
            class="input"
            placeholder="Enter new password"
          />
        </div>

        <div v-if="form.password">
          <label for="password_confirmation" class="label">Confirm Password</label>
          <input
            id="password_confirmation"
            v-model="form.password_confirmation"
            type="password"
            class="input"
            placeholder="Confirm new password"
          />
        </div>

        <div>
          <label for="department_id" class="label">Department</label>
          <select id="department_id" v-model="form.department_id" class="input">
            <option value="">Select Department</option>
            <option v-for="dept in departments" :key="dept.id" :value="dept.id">
              {{ dept.name }}
            </option>
          </select>
        </div>

        <div v-if="authStore.isAdmin">
          <label class="label">Roles</label>
          <div class="space-y-2">
            <label class="flex items-center">
              <input
                type="checkbox"
                value="admin"
                v-model="form.roles"
                class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
              />
              <span class="ml-2 text-sm text-gray-700">Administrator</span>
            </label>
            <label class="flex items-center">
              <input
                type="checkbox"
                value="manager"
                v-model="form.roles"
                class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
              />
              <span class="ml-2 text-sm text-gray-700">Manager</span>
            </label>
            <label class="flex items-center">
              <input
                type="checkbox"
                value="staff"
                v-model="form.roles"
                class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
              />
              <span class="ml-2 text-sm text-gray-700">Staff</span>
            </label>
          </div>
        </div>

        <div class="flex justify-end gap-3">
          <router-link to="/users" class="btn btn-secondary">
            Cancel
          </router-link>
          <button type="submit" :disabled="submitting" class="btn btn-primary">
            <span v-if="submitting">Updating...</span>
            <span v-else">Update User</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useUsersStore } from '@/stores/users'
import { useDepartmentsStore } from '@/stores/departments'
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'
import Alert from '@/components/common/Alert.vue'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const usersStore = useUsersStore()
const departmentsStore = useDepartmentsStore()

const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  department_id: '',
  roles: []
})

const loading = ref(true)
const submitting = ref(false)
const error = ref(null)
const departments = ref([])

const handleSubmit = async () => {
  if (form.value.password && form.value.password !== form.value.password_confirmation) {
    error.value = 'Passwords do not match'
    return
  }

  submitting.value = true
  error.value = null

  try {
    const data = { ...form.value }
    if (!data.password) {
      delete data.password
      delete data.password_confirmation
    }
    
    await usersStore.updateUser(route.params.id, data)
    router.push('/users')
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to update user'
  } finally {
    submitting.value = false
  }
}

onMounted(async () => {
  try {
    const user = await usersStore.fetchUser(route.params.id)
    form.value = {
      name: user.name,
      email: user.email,
      password: '',
      password_confirmation: '',
      department_id: user.department?.id || '',
      roles: user.roles?.map(r => r.slug) || []
    }

    await departmentsStore.fetchDepartments()
    departments.value = departmentsStore.departments
  } catch (err) {
    error.value = 'Failed to load user'
  } finally {
    loading.value = false
  }
})
</script>