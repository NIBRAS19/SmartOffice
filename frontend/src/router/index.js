// ============================================
// src/router/index.js - COMPLETE FIXED VERSION
// ============================================
import { createRouter, createWebHistory } from "vue-router";
import { useAuthStore } from "@/stores/auth";

const routes = [
  {
    path: "/login",
    name: "Login",
    component: () => import("@/views/auth/LoginView.vue"),
    meta: { guest: true },
  },
  {
    path: "/register",
    name: "Register",
    component: () => import("@/views/auth/RegisterView.vue"),
    meta: { guest: true },
  },
  {
    path: "/",
    component: () => import("@/layouts/MainLayout.vue"),
    meta: { requiresAuth: true },
    children: [
      {
        path: "",
        redirect: "/dashboard",
      },
      {
        path: "dashboard",
        name: "Dashboard",
        component: () => import("@/views/dashboard/DashboardView.vue"),
      },
      // MY TASKS - Everyone can access
      {
        path: "my-tasks",
        name: "MyTasks",
        component: () => import("@/views/tasks/MyTasksView.vue"),
      },
      // TASKS - Admin, Manager, Staff
      {
        path: "tasks",
        name: "Tasks",
        component: () => import("@/views/tasks/TasksView.vue"),
      },
      {
        path: "tasks/create",
        name: "CreateTask",
        component: () => import("@/views/tasks/CreateTask.vue"),
      },
      {
        path: "tasks/:id/edit",
        name: "EditTask",
        component: () => import("@/views/tasks/EditTask.vue"),
      },
      // USERS - Admin, Manager
      {
        path: "users",
        name: "Users",
        component: () => import("@/views/users/UsersView.vue"),
      },
      {
        path: "users/create",
        name: "CreateUser",
        component: () => import("@/views/users/CreateUser.vue"),
      },
      {
        path: "users/:id/edit",
        name: "EditUser",
        component: () => import("@/views/users/EditUser.vue"),
      },
      // DEPARTMENTS - Admin, Manager
      {
        path: "departments",
        name: "Departments",
        component: () => import("@/views/departments/DepartmentsView.vue"),
      },
      {
        path: "departments/create",
        name: "CreateDepartment",
        component: () => import("@/views/departments/CreateDepartment.vue"),
      },
      {
        path: "departments/:id/edit",
        name: "EditDepartment",
        component: () => import("@/views/departments/EditDepartment.vue"),
      },
      // ROLES - Admin only
      {
        path: "roles",
        name: "Roles",
        component: () => import("@/views/roles/RolesView.vue"),
        meta: { adminOnly: true },
      },
      // PROFILE - Everyone
      {
        path: "profile",
        name: "Profile",
        component: () => import("@/views/ProfileView.vue"),
      },
    ],
  },
  {
    path: "/:pathMatch(.*)*",
    name: "NotFound",
    component: () => import("@/views/NotFound.vue"),
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

// SIMPLIFIED Navigation guard - Only check authentication and admin routes
router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore();
  const isAuthenticated = authStore.isAuthenticated;

  // Redirect to login if not authenticated
  if (to.meta.requiresAuth && !isAuthenticated) {
    next({ name: "Login" });
    return;
  }

  // Redirect to dashboard if authenticated and accessing guest routes
  if (to.meta.guest && isAuthenticated) {
    next({ name: "Dashboard" });
    return;
  }

  // Load permissions if authenticated and not loaded yet
  if (isAuthenticated && authStore.permissions.length === 0) {
    await authStore.fetchPermissions();
  }

  // Only check admin for admin-only routes
  if (to.meta.adminOnly && !authStore.isAdmin) {
    console.warn("Access denied: Admin only route");
    next({ name: "Dashboard" });
    return;
  }

  // Allow all other authenticated routes - permission checks done in components
  next();
});

export default router;
