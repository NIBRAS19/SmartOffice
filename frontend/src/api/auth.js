import api from "./axios";

export const authAPI = {
  async login(credentials) {
    const response = await api.post("/auth/login", credentials);
    return response.data;
  },

  async register(userData) {
    const response = await api.post("/auth/register", userData);
    return response.data;
  },

  async logout() {
    const response = await api.post("/auth/logout");
    return response.data;
  },

  async getUser() {
    const response = await api.get("/auth/user");
    return response.data;
  },

  async getPermissions() {
    const response = await api.get("/auth/permissions");
    return response.data;
  },
};
