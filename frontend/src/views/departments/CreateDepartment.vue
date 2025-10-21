<template>
  <div class="max-w-2xl mx-auto space-y-6">
    <div>
      <router-link to="/departments" class="text-sm text-primary-600 hover:text-primary-700 flex items-center">
        <ArrowLeftIcon class="w-4 h-4 mr-1" />
        Back to Departments
      </router-link>
      <h1 class="mt-2 text-2xl font-bold text-gray-900">Create Department</h1>
    </div>

    <Alert
      v-if="error"
      type="error"
      :message="error"
      @dismiss="error = null"
    />

    <form @submit.prevent="handleSubmit" class="card p-6 space-y-6">
      <div>
        <label for="name" class="label">Department Name *</label>
        <input
          id="name"
          v-model="form.name"
          type="text"
          required
          class="input"
          placeholder="Enter department name"
        />
      </div>

      <div>
        <label for="description" class="label">Description</label>
        <textarea
          id="description"
          v-model="form.description"
          rows="4"
          class="input"
          placeholder="Enter department description"
        ></textarea>
      </div>

      <div v-if="authStore.isAdmin">
        <label for="manager_id" class="label">Manager</label>
        <select id="manager_id" v-model="form.manager_id" class="input">
          <option value="">Select Manager (Optional)</option>
          <option v-for="user in managers" :key="user.id" :value="user.id">
            {{ user.name }}
          </option>
        </select>
      </div>

      <div class="flex justify-end gap-3">
        <router-link to="/departments" class="btn btn-secondary">
          Cancel
        </router-link>
        <button type="submit" :disabled="loading" class="btn btn-primary">
          <span v-if="loading">Creating...</span>
          <span v-else>Create Department</span>
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useDepartmentsStore } from '@/stores/departments'
import { useUsersStore } from '@/stores/users'
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'
import Alert from '@/components/common/Alert.vue'

const router = useRouter()
const authStore = useAuthStore()
const departmentsStore = useDepartmentsStore()
const usersStore = useUsersStore()

const form = ref({
  name: '',
  description: '',
  manager_id: ''
})

const loading = ref(false)
const error = ref(null)
const managers = ref([])

const handleSubmit = async () => {
  loading.value = true
  error.value = null

  try {
    await departmentsStore.createDepartment(form.value)
    router.push('/departments')
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to create department'
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  if (authStore.isAdmin) {
    await usersStore.fetchUsers({ role: 'manager', per_page: 100 })
    managers.value = usersStore.users
  }
})
</script>