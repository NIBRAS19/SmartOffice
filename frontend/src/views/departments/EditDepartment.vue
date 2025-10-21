<template>
  <div class="max-w-2xl mx-auto space-y-6">
    <div>
      <router-link to="/departments" class="text-sm text-primary-600 hover:text-primary-700 flex items-center">
        <ArrowLeftIcon class="w-4 h-4 mr-1" />
        Back to Departments
      </router-link>
      <h1 class="mt-2 text-2xl font-bold text-gray-900">Edit Department</h1>
    </div>

    <LoadingSpinner v-if="pageLoading" />

    <div v-else>
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
          />
        </div>

        <div>
          <label for="description" class="label">Description</label>
          <textarea
            id="description"
            v-model="form.description"
            rows="4"
            class="input"
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
            <span v-if="loading">Updating...</span>
            <span v-else>Update Department</span>
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
import { useDepartmentsStore } from '@/stores/departments'
import { useUsersStore } from '@/stores/users'
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'
import Alert from '@/components/common/Alert.vue'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const departmentsStore = useDepartmentsStore()
const usersStore = useUsersStore()

const form = ref({
  name: '',
  description: '',
  manager_id: ''
})

const pageLoading = ref(true)
const loading = ref(false)
const error = ref(null)
const managers = ref([])

const handleSubmit = async () => {
  loading.value = true
  error.value = null

  try {
    await departmentsStore.updateDepartment(route.params.id, form.value)
    router.push('/departments')
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to update department'
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  try {
    const department = await departmentsStore.fetchDepartment(route.params.id)
    form.value = {
      name: department.name,
      description: department.description || '',
      manager_id: department.manager?.id || ''
    }

    if (authStore.isAdmin) {
      await usersStore.fetchUsers({ role: 'manager', per_page: 100 })
      managers.value = usersStore.users
    }
  } catch (err) {
    error.value = 'Failed to load department'
  } finally {
    pageLoading.value = false
  }
})
</script>