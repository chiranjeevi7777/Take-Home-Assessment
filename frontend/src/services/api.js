const API_BASE_URL = 'http://localhost:8000/api';

async function handleResponse(response) {
  if (!response.ok) {
    let errorDetail = 'An unexpected error occurred';
    try {
      const errorData = await response.json();
      errorDetail = errorData.detail || errorData.message || JSON.stringify(errorData);
    } catch (e) {
      errorDetail = response.statusText;
    }
    throw new Error(errorDetail);
  }
  if (response.status === 204) {
    return null;
  }
  return await response.json();
}

export const api = {
  // Fetch tasks with optional filters
  async getTasks(status = '', search = '') {
    const params = new URLSearchParams();
    if (status) params.append('status', status);
    if (search) params.append('search', search);
    const query = params.toString() ? `?${params.toString()}` : '';
    const res = await fetch(`${API_BASE_URL}/tasks${query}`);
    return handleResponse(res);
  },

  // Create a new task
  async createTask(taskData) {
    const res = await fetch(`${API_BASE_URL}/tasks`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(taskData)
    });
    return handleResponse(res);
  },

  // Update existing task details or status
  async updateTask(taskId, taskData) {
    const res = await fetch(`${API_BASE_URL}/tasks/${taskId}`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(taskData)
    });
    return handleResponse(res);
  },

  // Advance task status specifically
  async updateTaskStatus(taskId, status) {
    const res = await fetch(`${API_BASE_URL}/tasks/${taskId}/status`, {
      method: 'PATCH',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ status })
    });
    return handleResponse(res);
  },

  // Delete task
  async deleteTask(taskId) {
    const res = await fetch(`${API_BASE_URL}/tasks/${taskId}`, {
      method: 'DELETE'
    });
    return handleResponse(res);
  },

  // Get workflow transition rules
  async getWorkflowRules() {
    const res = await fetch(`${API_BASE_URL}/workflow/rules`);
    return handleResponse(res);
  }
};
