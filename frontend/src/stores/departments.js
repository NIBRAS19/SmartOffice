// import { defineStore } from "pinia";
// import { departmentsAPI } from "@/api/departments";

// export const useDepartmentsStore = defineStore("departments", {
//   state: () => ({
//     departments: [],
//     currentDepartment: null,
//     loading: false,
//     error: null,
//   }),

//   actions: {
//     async fetchDepartments(params = {}) {
//       this.loading = true;
//       this.error = null;
//       try {
//         const response = await departmentsAPI.getAll(params);
//         this.departments = response.data.departments;
//       } catch (error) {
//         this.error =
//           error.response?.data?.message || "Failed to fetch departments";
//         throw error;
//       } finally {
//         this.loading = false;
//       }
//     },

//     async fetchDepartment(id) {
//       this.loading = true;
//       this.error = null;
//       try {
//         const response = await departmentsAPI.getById(id);
//         this.currentDepartment = response.data;
//         return response.data;
//       } catch (error) {
//         this.error =
//           error.response?.data?.message || "Failed to fetch department";
//         throw error;
//       } finally {
//         this.loading = false;
//       }
//     },

//     async createDepartment(departmentData) {
//       this.loading = true;
//       this.error = null;
//       try {
//         const response = await departmentsAPI.create(departmentData);
//         await this.fetchDepartments();
//         return response.data;
//       } catch (error) {
//         this.error =
//           error.response?.data?.message || "Failed to create department";
//         throw error;
//       } finally {
//         this.loading = false;
//       }
//     },

//     async updateDepartment(id, departmentData) {
//       this.loading = true;
//       this.error = null;
//       try {
//         const response = await departmentsAPI.update(id, departmentData);
//         await this.fetchDepartments();
//         return response.data;
//       } catch (error) {
//         this.error =
//           error.response?.data?.message || "Failed to update department";
//         throw error;
//       } finally {
//         this.loading = false;
//       }
//     },

//     async deleteDepartment(id) {
//       this.loading = true;
//       this.error = null;
//       try {
//         await departmentsAPI.delete(id);
//         await this.fetchDepartments();
//       } catch (error) {
//         this.error =
//           error.response?.data?.message || "Failed to delete department";
//         throw error;
//       } finally {
//         this.loading = false;
//       }
//     },
//   },
// });


import { defineStore } from "pinia";
import { departmentsAPI } from "@/api/departments";

export const useDepartmentsStore = defineStore("departments", {
  state: () => ({
    departments: [],
    currentDepartment: null,
    loading: false,
    error: null,
  }),

  actions: {
    async fetchDepartments(params = {}) {
      this.loading = true;
      this.error = null;
      try {
        const response = await departmentsAPI.getAll(params);
        this.departments = response.data.departments;
      } catch (error) {
        this.error =
          error.response?.data?.message || "Failed to fetch departments";
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async fetchDepartment(id) {
      this.loading = true;
      this.error = null;
      try {
        const response = await departmentsAPI.getById(id);
        this.currentDepartment = response.data;
        return response.data;
      } catch (error) {
        this.error =
          error.response?.data?.message || "Failed to fetch department";
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async createDepartment(departmentData) {
      this.loading = true;
      this.error = null;
      try {
        const response = await departmentsAPI.create(departmentData);
        await this.fetchDepartments();
        return response.data;
      } catch (error) {
        this.error =
          error.response?.data?.message || "Failed to create department";
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async updateDepartment(id, departmentData) {
      this.loading = true;
      this.error = null;
      try {
        const response = await departmentsAPI.update(id, departmentData);
        await this.fetchDepartments();
        return response.data;
      } catch (error) {
        this.error =
          error.response?.data?.message || "Failed to update department";
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async deleteDepartment(id) {
      this.loading = true;
      this.error = null;
      try {
        await departmentsAPI.delete(id);
        await this.fetchDepartments();
      } catch (error) {
        this.error =
          error.response?.data?.message || "Failed to delete department";
        throw error;
      } finally {
        this.loading = false;
      }
    },
  },
});