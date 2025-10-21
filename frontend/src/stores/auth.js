// import { defineStore } from "pinia";
// import { authAPI } from "@/api/auth";
// import router from "@/router";

// export const useAuthStore = defineStore("auth", {
//   state: () => ({
//     user: JSON.parse(localStorage.getItem("user")) || null,
//     token: localStorage.getItem("token") || null,
//     permissions: JSON.parse(localStorage.getItem("permissions")) || [],
//     roles: JSON.parse(localStorage.getItem("roles")) || [],
//     loading: false,
//     error: null,
//   }),

//   getters: {
//     isAuthenticated: (state) => !!state.token,
//     isAdmin: (state) =>
//       state.roles.includes("Administrator") || state.roles.includes("admin"),
//     isManager: (state) =>
//       state.roles.includes("Manager") || state.roles.includes("manager"),
//     isStaff: (state) =>
//       state.roles.includes("Staff") || state.roles.includes("staff"),
//     userName: (state) => state.user?.name || "",
//     userEmail: (state) => state.user?.email || "",
//   },

//   actions: {
//     async login(credentials) {
//       this.loading = true;
//       this.error = null;
//       try {
//         const response = await authAPI.login(credentials);
//         this.token = response.data.token;
//         this.user = response.data.user;

//         // Store in localStorage first
//         localStorage.setItem("token", this.token);
//         localStorage.setItem("user", JSON.stringify(this.user));

//         // Extract roles from user object
//         if (this.user.role_names && Array.isArray(this.user.role_names)) {
//           this.roles = this.user.role_names;
//         } else if (this.user.roles && Array.isArray(this.user.roles)) {
//           this.roles = this.user.roles.map((r) => r.name || r.slug || r);
//         }
//         localStorage.setItem("roles", JSON.stringify(this.roles));

//         // Fetch permissions after token is set
//         await this.fetchPermissions();

//         router.push("/dashboard");
//         return response;
//       } catch (error) {
//         this.error = error.response?.data?.message || "Login failed";
//         throw error;
//       } finally {
//         this.loading = false;
//       }
//     },

//     async register(userData) {
//       this.loading = true;
//       this.error = null;
//       try {
//         const response = await authAPI.register(userData);
//         this.token = response.data.token;
//         this.user = response.data.user;

//         localStorage.setItem("token", this.token);
//         localStorage.setItem("user", JSON.stringify(this.user));

//         // Extract roles from user object
//         if (this.user.role_names && Array.isArray(this.user.role_names)) {
//           this.roles = this.user.role_names;
//         } else if (this.user.roles && Array.isArray(this.user.roles)) {
//           this.roles = this.user.roles.map((r) => r.name || r.slug || r);
//         }
//         localStorage.setItem("roles", JSON.stringify(this.roles));

//         await this.fetchPermissions();
//         router.push("/dashboard");
//         return response;
//       } catch (error) {
//         this.error = error.response?.data?.message || "Registration failed";
//         throw error;
//       } finally {
//         this.loading = false;
//       }
//     },

//     async logout() {
//       try {
//         await authAPI.logout();
//       } catch (error) {
//         console.error("Logout error:", error);
//       } finally {
//         this.user = null;
//         this.token = null;
//         this.permissions = [];
//         this.roles = [];
//         localStorage.removeItem("token");
//         localStorage.removeItem("user");
//         localStorage.removeItem("permissions");
//         localStorage.removeItem("roles");
//         router.push("/login");
//       }
//     },

//     async fetchUser() {
//       try {
//         const response = await authAPI.getUser();
//         this.user = response.data;
//         localStorage.setItem("user", JSON.stringify(this.user));

//         // Update roles from user data
//         if (this.user.role_names && Array.isArray(this.user.role_names)) {
//           this.roles = this.user.role_names;
//           localStorage.setItem("roles", JSON.stringify(this.roles));
//         }
//       } catch (error) {
//         console.error("Fetch user error:", error);
//       }
//     },

//     async fetchPermissions() {
//       try {
//         const response = await authAPI.getPermissions();
//         this.permissions = response.data.permissions || [];
//         this.roles = response.data.roles || this.roles;

//         localStorage.setItem("permissions", JSON.stringify(this.permissions));
//         localStorage.setItem("roles", JSON.stringify(this.roles));
//       } catch (error) {
//         console.error("Fetch permissions error:", error);
//         // Don't throw error, just use existing permissions
//       }
//     },

//     hasPermission(permission) {
//       return this.permissions.includes(permission) || this.isAdmin;
//     },

//     hasRole(role) {
//       return this.roles.some(
//         (r) => r.toLowerCase() === role.toLowerCase() || r === role
//       );
//     },

//     hasAnyRole(roles) {
//       return roles.some((role) => this.hasRole(role));
//     },
//   },
// });

import { defineStore } from "pinia";
import { authAPI } from "@/api/auth";
import router from "@/router";

export const useAuthStore = defineStore("auth", {
  state: () => ({
    user: JSON.parse(localStorage.getItem("user")) || null,
    token: localStorage.getItem("token") || null,
    permissions: JSON.parse(localStorage.getItem("permissions")) || [],
    roles: JSON.parse(localStorage.getItem("roles")) || [],
    loading: false,
    error: null,
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
    isAdmin: (state) => {
      return state.roles.some((role) => {
        const r = role.toLowerCase();
        return r === "administrator" || r === "admin";
      });
    },
    isManager: (state) => {
      return state.roles.some((role) => role.toLowerCase() === "manager");
    },
    isStaff: (state) => {
      return state.roles.some((role) => role.toLowerCase() === "staff");
    },
    userName: (state) => state.user?.name || "",
    userEmail: (state) => state.user?.email || "",
  },

  actions: {
    async login(credentials) {
      this.loading = true;
      this.error = null;
      try {
        const response = await authAPI.login(credentials);
        this.token = response.data.token;
        this.user = response.data.user;

        localStorage.setItem("token", this.token);
        localStorage.setItem("user", JSON.stringify(this.user));

        await this.fetchPermissions();

        console.log("✅ Login successful");
        console.log("User:", this.user.name);
        console.log("Roles:", this.roles);
        console.log("Permissions:", this.permissions);

        router.push("/dashboard");
        return response;
      } catch (error) {
        this.error = error.response?.data?.message || "Login failed";
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async register(userData) {
      this.loading = true;
      this.error = null;
      try {
        const response = await authAPI.register(userData);
        this.token = response.data.token;
        this.user = response.data.user;

        localStorage.setItem("token", this.token);
        localStorage.setItem("user", JSON.stringify(this.user));

        await this.fetchPermissions();
        router.push("/dashboard");
        return response;
      } catch (error) {
        this.error = error.response?.data?.message || "Registration failed";
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async logout() {
      try {
        await authAPI.logout();
      } catch (error) {
        console.error("Logout error:", error);
      } finally {
        this.user = null;
        this.token = null;
        this.permissions = [];
        this.roles = [];
        localStorage.clear();
        router.push("/login");
      }
    },

    async fetchUser() {
      try {
        const response = await authAPI.getUser();
        this.user = response.data;
        localStorage.setItem("user", JSON.stringify(this.user));
      } catch (error) {
        console.error("Fetch user error:", error);
      }
    },

    async fetchPermissions() {
      try {
        const response = await authAPI.getPermissions();

        // Backend now returns permission slugs directly
        this.permissions = response.data.permissions || [];
        this.roles = response.data.roles || [];

        // Store in localStorage
        localStorage.setItem("permissions", JSON.stringify(this.permissions));
        localStorage.setItem("roles", JSON.stringify(this.roles));

        console.log("✅ Permissions loaded:", this.permissions);
        console.log("✅ Roles loaded:", this.roles);
      } catch (error) {
        console.error("Fetch permissions error:", error);
      }
    },

    hasPermission(permission) {
      // Admin has all permissions
      if (this.isAdmin) {
        return true;
      }
      // Check if permission slug exists
      return this.permissions.includes(permission);
    },

    hasRole(role) {
      const roleLower = role.toLowerCase();
      return this.roles.some((r) => r.toLowerCase() === roleLower);
    },

    hasAnyRole(roles) {
      return roles.some((role) => this.hasRole(role));
    },
  },
});