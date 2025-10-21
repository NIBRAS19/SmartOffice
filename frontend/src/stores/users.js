// import { defineStore } from "pinia";
// import { usersAPI } from "@/api/users";

// export const useUsersStore = defineStore("users", {
//   state: () => ({
//     users: [],
//     currentUser: null,
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
//     async fetchUsers(params = {}) {
//       this.loading = true;
//       this.error = null;
//       try {
//         const response = await usersAPI.getAll(params);
//         this.users = response.data.users;
//         this.pagination = response.data.meta;
//       } catch (error) {
//         this.error = error.response?.data?.message || "Failed to fetch users";
//         throw error;
//       } finally {
//         this.loading = false;
//       }
//     },

//     async fetchUser(id) {
//       this.loading = true;
//       this.error = null;
//       try {
//         const response = await usersAPI.getById(id);
//         this.currentUser = response.data;
//         return response.data;
//       } catch (error) {
//         this.error = error.response?.data?.message || "Failed to fetch user";
//         throw error;
//       } finally {
//         this.loading = false;
//       }
//     },

//     async createUser(userData) {
//       this.loading = true;
//       this.error = null;
//       try {
//         const response = await usersAPI.create(userData);
//         await this.fetchUsers();
//         return response.data;
//       } catch (error) {
//         this.error = error.response?.data?.message || "Failed to create user";
//         throw error;
//       } finally {
//         this.loading = false;
//       }
//     },

//     async updateUser(id, userData) {
//       this.loading = true;
//       this.error = null;
//       try {
//         const response = await usersAPI.update(id, userData);
//         await this.fetchUsers();
//         return response.data;
//       } catch (error) {
//         this.error = error.response?.data?.message || "Failed to update user";
//         throw error;
//       } finally {
//         this.loading = false;
//       }
//     },

//     async deleteUser(id) {
//       this.loading = true;
//       this.error = null;
//       try {
//         await usersAPI.delete(id);
//         await this.fetchUsers();
//       } catch (error) {
//         this.error = error.response?.data?.message || "Failed to delete user";
//         throw error;
//       } finally {
//         this.loading = false;
//       }
//     },
//   },
// });


import { defineStore } from "pinia";
import { usersAPI } from "@/api/users";

export const useUsersStore = defineStore("users", {
  state: () => ({
    users: [],
    currentUser: null,
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
    async fetchUsers(params = {}) {
      this.loading = true;
      this.error = null;
      try {
        const response = await usersAPI.getAll(params);
        this.users = response.data.users;
        this.pagination = response.data.meta;
      } catch (error) {
        this.error = error.response?.data?.message || "Failed to fetch users";
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async fetchUser(id) {
      this.loading = true;
      this.error = null;
      try {
        const response = await usersAPI.getById(id);
        this.currentUser = response.data;
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || "Failed to fetch user";
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async createUser(userData) {
      this.loading = true;
      this.error = null;
      try {
        const response = await usersAPI.create(userData);
        await this.fetchUsers();
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || "Failed to create user";
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async updateUser(id, userData) {
      this.loading = true;
      this.error = null;
      try {
        const response = await usersAPI.update(id, userData);
        await this.fetchUsers();
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || "Failed to update user";
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async deleteUser(id) {
      this.loading = true;
      this.error = null;
      try {
        await usersAPI.delete(id);
        await this.fetchUsers();
      } catch (error) {
        this.error = error.response?.data?.message || "Failed to delete user";
        throw error;
      } finally {
        this.loading = false;
      }
    },
  },
});
