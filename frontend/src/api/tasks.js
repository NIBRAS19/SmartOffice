import api from "./axios";

export const tasksAPI = {
  async getAll(params = {}) {
    const response = await api.get("/tasks", { params });
    return response.data;
  },

  async getById(id) {
    const response = await api.get(`/tasks/${id}`);
    return response.data;
  },

  async create(taskData) {
    const response = await api.post("/tasks", taskData);
    return response.data;
  },

  async update(id, taskData) {
    const response = await api.put(`/tasks/${id}`, taskData);
    return response.data;
  },

  async delete(id) {
    const response = await api.delete(`/tasks/${id}`);
    return response.data;
  },

  async complete(id) {
    const response = await api.patch(`/tasks/${id}/complete`);
    return response.data;
  },

  async updateStatus(id, status) {
    const response = await api.patch(`/tasks/${id}/status`, { status });
    return response.data;
  },

  async reassign(id, userId) {
    const response = await api.patch(`/tasks/${id}/reassign`, {
      assigned_to: userId,
    });
    return response.data;
  },

  async getMyTasks(params = {}) {
    const response = await api.get("/tasks/my-tasks", { params });
    return response.data;
  },

  async getStatistics() {
    const response = await api.get("/tasks/statistics");
    return response.data;
  },
};
