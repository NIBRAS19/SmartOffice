// import { defineStore } from "pinia";
// import { tasksAPI } from "@/api/tasks";

// export const useTasksStore = defineStore("tasks", {
//   state: () => ({
//     tasks: [],
//     myTasks: [],
//     currentTask: null,
//     statistics: null,
//     loading: false,
//     error: null,
//     pagination: {
//       current_page: 1,
//       last_page: 1,
//       per_page: 15,
//       total: 0,
//     },
//   }),

//   actions: {
//     async fetchTasks(params = {}) {
//       this.loading = true;
//       this.error = null;
//       try {
//         const response = await tasksAPI.getAll(params);
//         this.tasks = response.data.tasks;
//         this.pagination = response.data.meta;
//       } catch (error) {
//         this.error = error.response?.data?.message || "Failed to fetch tasks";
//         throw error;
//       } finally {
//         this.loading = false;
//       }
//     },

//     async fetchMyTasks(params = {}) {
//       this.loading = true;
//       this.error = null;
//       try {
//         const response = await tasksAPI.getMyTasks(params);
//         // Handle response data structure
//         if (response.success && response.data) {
//           this.myTasks = Array.isArray(response.data) ? response.data : [];
//         } else if (Array.isArray(response.data)) {
//           this.myTasks = response.data;
//         } else {
//           this.myTasks = [];
//         }
//         return this.myTasks;
//       } catch (error) {
//         this.error = error.response?.data?.message || "Failed to fetch tasks";
//         this.myTasks = [];
//         return [];
//       } finally {
//         this.loading = false;
//       }
//     },

//     async fetchTask(id) {
//       this.loading = true;
//       this.error = null;
//       try {
//         const response = await tasksAPI.getById(id);
//         this.currentTask = response.data;
//         return response.data;
//       } catch (error) {
//         this.error = error.response?.data?.message || "Failed to fetch task";
//         throw error;
//       } finally {
//         this.loading = false;
//       }
//     },

//     async createTask(taskData) {
//       this.loading = true;
//       this.error = null;
//       try {
//         const response = await tasksAPI.create(taskData);
//         await this.fetchTasks();
//         return response.data;
//       } catch (error) {
//         this.error = error.response?.data?.message || "Failed to create task";
//         throw error;
//       } finally {
//         this.loading = false;
//       }
//     },

//     async updateTask(id, taskData) {
//       this.loading = true;
//       this.error = null;
//       try {
//         const response = await tasksAPI.update(id, taskData);
//         await this.fetchTasks();
//         return response.data;
//       } catch (error) {
//         this.error = error.response?.data?.message || "Failed to update task";
//         throw error;
//       } finally {
//         this.loading = false;
//       }
//     },

//     async deleteTask(id) {
//       this.loading = true;
//       this.error = null;
//       try {
//         await tasksAPI.delete(id);
//         await this.fetchTasks();
//       } catch (error) {
//         this.error = error.response?.data?.message || "Failed to delete task";
//         throw error;
//       } finally {
//         this.loading = false;
//       }
//     },

//     async completeTask(id) {
//       this.loading = true;
//       this.error = null;
//       try {
//         const response = await tasksAPI.complete(id);
//         await this.fetchTasks();
//         return response.data;
//       } catch (error) {
//         this.error = error.response?.data?.message || "Failed to complete task";
//         throw error;
//       } finally {
//         this.loading = false;
//       }
//     },

//     async updateTaskStatus(id, status) {
//       this.loading = true;
//       this.error = null;
//       try {
//         const response = await tasksAPI.updateStatus(id, status);
//         await this.fetchTasks();
//         return response.data;
//       } catch (error) {
//         this.error =
//           error.response?.data?.message || "Failed to update task status";
//         throw error;
//       } finally {
//         this.loading = false;
//       }
//     },

//     async fetchStatistics() {
//       try {
//         const response = await tasksAPI.getStatistics();
//         this.statistics = response.data;
//         return response.data;
//       } catch (error) {
//         console.error("Failed to fetch statistics:", error);
//       }
//     },
//   },
// });


import { defineStore } from "pinia";
import { tasksAPI } from "@/api/tasks";

export const useTasksStore = defineStore("tasks", {
  state: () => ({
    tasks: [],
    myTasks: [],
    currentTask: null,
    statistics: null,
    loading: false,
    error: null,
    pagination: {
      current_page: 1,
      last_page: 1,
      per_page: 15,
      total: 0,
    },
  }),

  actions: {
    async fetchTasks(params = {}) {
      this.loading = true;
      this.error = null;
      try {
        const response = await tasksAPI.getAll(params);
        this.tasks = response.data.tasks;
        this.pagination = response.data.meta;
      } catch (error) {
        this.error = error.response?.data?.message || "Failed to fetch tasks";
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async fetchMyTasks(params = {}) {
      this.loading = true;
      this.error = null;
      try {
        const response = await tasksAPI.getMyTasks(params);
        this.myTasks = response.data;
      } catch (error) {
        this.error = error.response?.data?.message || "Failed to fetch tasks";
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async fetchTask(id) {
      this.loading = true;
      this.error = null;
      try {
        const response = await tasksAPI.getById(id);
        this.currentTask = response.data;
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || "Failed to fetch task";
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async createTask(taskData) {
      this.loading = true;
      this.error = null;
      try {
        const response = await tasksAPI.create(taskData);
        await this.fetchTasks();
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || "Failed to create task";
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async updateTask(id, taskData) {
      this.loading = true;
      this.error = null;
      try {
        const response = await tasksAPI.update(id, taskData);
        await this.fetchTasks();
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || "Failed to update task";
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async deleteTask(id) {
      this.loading = true;
      this.error = null;
      try {
        await tasksAPI.delete(id);
        await this.fetchTasks();
      } catch (error) {
        this.error = error.response?.data?.message || "Failed to delete task";
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async completeTask(id) {
      this.loading = true;
      this.error = null;
      try {
        const response = await tasksAPI.complete(id);
        await this.fetchTasks();
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || "Failed to complete task";
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async updateTaskStatus(id, status) {
      this.loading = true;
      this.error = null;
      try {
        const response = await tasksAPI.updateStatus(id, status);
        await this.fetchTasks();
        return response.data;
      } catch (error) {
        this.error =
          error.response?.data?.message || "Failed to update task status";
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async fetchStatistics() {
      try {
        const response = await tasksAPI.getStatistics();
        this.statistics = response.data;
        return response.data;
      } catch (error) {
        console.error("Failed to fetch statistics:", error);
      }
    },
  },
});