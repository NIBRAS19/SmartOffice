import api from "./axios";

export const usersAPI = {
  async getAll(params = {}) {
    const response = await api.get("/users", { params });
    return response.data;
  },

  async getById(id) {
    const response = await api.get(`/users/${id}`);
    return response.data;
  },

  async create(userData) {
    const response = await api.post("/users", userData);
    return response.data;
  },

  async update(id, userData) {
    const response = await api.put(`/users/${id}`, userData);
    return response.data;
  },

  async delete(id) {
    const response = await api.delete(`/users/${id}`);
    return response.data;
  },

  async assignRoles(id, roles) {
    const response = await api.post(`/users/${id}/roles`, { roles });
    return response.data;
  },

  async getByDepartment(departmentId) {
    const response = await api.get(`/users/department/${departmentId}`);
    return response.data;
  },
};
