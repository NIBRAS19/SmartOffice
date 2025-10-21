import api from "./axios";

export const departmentsAPI = {
  async getAll(params = {}) {
    const response = await api.get("/departments", { params });
    return response.data;
  },

  async getById(id) {
    const response = await api.get(`/departments/${id}`);
    return response.data;
  },

  async create(departmentData) {
    const response = await api.post("/departments", departmentData);
    return response.data;
  },

  async update(id, departmentData) {
    const response = await api.put(`/departments/${id}`, departmentData);
    return response.data;
  },

  async delete(id) {
    const response = await api.delete(`/departments/${id}`);
    return response.data;
  },

  async getStatistics(id) {
    const response = await api.get(`/departments/${id}/statistics`);
    return response.data;
  },

  async assignManager(id, managerId) {
    const response = await api.post(`/departments/${id}/assign-manager`, {
      manager_id: managerId,
    });
    return response.data;
  },
};
