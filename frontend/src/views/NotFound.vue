<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 px-4">
    <div class="text-center">
      <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-primary-100 mb-6">
        <ExclamationTriangleIcon class="h-12 w-12 text-primary-600" />
      </div>
      
      <h1 class="text-6xl font-bold text-gray-900 mb-4">404</h1>
      <h2 class="text-2xl font-semibold text-gray-700 mb-2">Page Not Found</h2>
      <p class="text-gray-600 mb-8 max-w-md">
        The page you're looking for doesn't exist or has been moved.
      </p>
      
      <div class="flex gap-4 justify-center">
        <button @click="goBack" class="btn btn-secondary">
          <ArrowLeftIcon class="w-5 h-5 mr-2" />
          Go Back
        </button>
        <router-link to="/dashboard" class="btn btn-primary">
          <HomeIcon class="w-5 h-5 mr-2" />
          Go to Dashboard
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
import {
  ExclamationTriangleIcon,
  ArrowLeftIcon,
  HomeIcon
} from '@heroicons/vue/24/outline'

const router = useRouter()

const goBack = () => {
  router.go(-1)
}
</script>