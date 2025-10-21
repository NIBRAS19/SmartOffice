<template>
  <div class="max-w-2xl mx-auto space-y-6">
    <div>
      <router-link to="/tasks" class="text-sm text-primary-600 hover:text-primary-700 flex items-center">
        <ArrowLeftIcon class="w-4 h-4 mr-1" />
        Back to Tasks
      </router-link>
      <h1 class="mt-2 text-2xl font-bold text-gray-900">Edit Task</h1>
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
          <label for="title" class="label">Title *</label>
          <input
            id="title"
            v-model="form.title"
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

        <div>
          <label for="department_id" class="label">Department *</label>
          <select
            id="department_id"
            v-model="form.department_id"
            @change="loadDepartmentUsers"
            required
            class="input"
          >
            <option value="">Select Department</option>
            <option v-for="dept in departments" :key="dept.id" :value="dept.id">
              {{ dept.name }}
            </option>
          </select>
        </div>

        <div>
          <label for="assigned_to" class="label">Assign To *</label>
          <select
            id="assigned_to"
            v-model="form.assigned_to"
            required
            class="input"
          >
            <option value="">Select User</option>
            <option v-for="user in departmentUsers" :key="user.id" :value="user.id">
              {{ user.name }}
            </option>
          </select>
        </div>

        <div>
          <label for="due_date" class="label">Due Date</label>
          <input
            id="due_date"
            v-model="form.due_date"
            type="date"
            class="input"
          />
        </div>

        <div>
          <label for="status" class="label">Status</label>
          <select id="status" v-model="form.status" class="input">
            <option value="pending">Pending</option>
            <option value="in_progress">In Progress</option>
            <option value="completed">Completed</option>
          </select>
        </div>

        <div class="flex justify-end gap-3">
          <router-link to="/tasks" class="btn btn-secondary">
            Cancel
          </router-link>
          <button type="submit" :disabled="loading" class="btn btn-primary">
            <span v-if="loading">Updating...</span>
            <span v-else>Update Task</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useTasksStore } from '@/stores/tasks'
import { useDepartmentsStore } from '@/stores/departments'
import { usersAPI } from '@/api/users'
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'
import Alert from '@/components/common/Alert.vue'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'

const route = useRoute()
const router = useRouter()
const tasksStore = useTasksStore()
const departmentsStore = useDepartmentsStore()

const form = ref({
  title: '',
  description: '',
  department_id: '',
  assigned_to: '',
  due_date: '',
  status: 'pending'
})

const pageLoading = ref(true)
const loading = ref(false)
const error = ref(null)
const departments = ref([])
const departmentUsers = ref([])

const loadDepartmentUsers = async () => {
  if (!form.value.department_id) return
  
  try {
    const response = await usersAPI.getByDepartment(form.value.department_id)
    departmentUsers.value = response.data
  } catch (err) {
    console.error('Error loading users:', err)
  }
}

const handleSubmit = async () => {
  loading.value = true
  error.value = null

  try {
    await tasksStore.updateTask(route.params.id, form.value)
    router.push('/tasks')
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to update task'
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  try {
    const task = await tasksStore.fetchTask(route.params.id)
    form.value = {
      title: task.title,
      description: task.description || '',
      department_id: task.department?.id || '',
      assigned_to: task.assigned_to?.id || '',
      due_date: task.due_date || '',
      status: task.status
    }

    await departmentsStore.fetchDepartments()
    departments.value = departmentsStore.departments

    await loadDepartmentUsers()
  } catch (err) {
    error.value = 'Failed to load task'
  } finally {
    pageLoading.value = false
  }
})
</script>