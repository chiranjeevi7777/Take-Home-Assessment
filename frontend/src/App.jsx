import React, { useState, useEffect, useCallback } from 'react';
import Header from './components/Header';
import TaskBoard from './components/TaskBoard';
import TaskList from './components/TaskList';
import TaskFormModal from './components/TaskFormModal';
import WorkflowGuide from './components/WorkflowGuide';
import Toast from './components/Toast';
import { api } from './services/api';

export default function App() {
  const [tasks, setTasks] = useState([]);
  const [loading, setLoading] = useState(false);
  const [searchQuery, setSearchQuery] = useState('');
  const [statusFilter, setStatusFilter] = useState('');
  const [viewMode, setViewMode] = useState('board'); // 'board' or 'list'
  
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [editingTask, setEditingTask] = useState(null);
  const [toast, setToast] = useState(null);

  // Fetch tasks
  const loadTasks = useCallback(async () => {
    setLoading(true);
    try {
      const data = await api.getTasks(statusFilter, searchQuery);
      setTasks(data);
    } catch (err) {
      setToast({ type: 'error', message: err.message });
    } finally {
      setLoading(false);
    }
  }, [statusFilter, searchQuery]);

  useEffect(() => {
    loadTasks();
  }, [loadTasks]);

  // Handle task creation or update
  const handleSaveTask = async (formData) => {
    try {
      if (editingTask) {
        await api.updateTask(editingTask.id, formData);
        setToast({ type: 'success', message: 'Task updated successfully.' });
      } else {
        await api.createTask(formData);
        setToast({ type: 'success', message: 'New task created successfully in "TODO" status.' });
      }
      loadTasks();
    } catch (err) {
      setToast({ type: 'error', message: err.message });
    }
  };

  // Advance status sequentially
  const handleAdvanceStatus = async (taskId, nextStatus) => {
    try {
      await api.updateTaskStatus(taskId, nextStatus);
      setToast({ type: 'success', message: `Task status updated to "${nextStatus.replace('_', ' ')}".` });
      loadTasks();
    } catch (err) {
      setToast({ type: 'error', message: err.message });
    }
  };

  // Attempt invalid transition (to demonstrate/test trick requirement)
  const handleAttemptInvalidTransition = async (taskId, invalidStatus) => {
    try {
      await api.updateTaskStatus(taskId, invalidStatus);
      loadTasks();
    } catch (err) {
      setToast({ type: 'error', message: err.message });
    }
  };

  // Delete task
  const handleDeleteTask = async (taskId) => {
    if (!window.confirm('Are you sure you want to delete this task?')) return;
    try {
      await api.deleteTask(taskId);
      setToast({ type: 'success', message: 'Task deleted successfully.' });
      loadTasks();
    } catch (err) {
      setToast({ type: 'error', message: err.message });
    }
  };

  return (
    <div className="min-h-screen p-4 md:p-8 max-w-7xl mx-auto">
      
      {/* Navigation & Control Header */}
      <Header
        searchQuery={searchQuery}
        setSearchQuery={setSearchQuery}
        statusFilter={statusFilter}
        setStatusFilter={setStatusFilter}
        viewMode={viewMode}
        setViewMode={setViewMode}
        onNewTaskClick={() => {
          setEditingTask(null);
          setIsModalOpen(true);
        }}
        onRefresh={loadTasks}
        loading={loading}
      />

      {/* State Machine Workflow Explanation Widget */}
      <WorkflowGuide />

      {/* Main Board / List Content */}
      <main>
        {viewMode === 'board' ? (
          <TaskBoard
            tasks={tasks}
            onAdvanceStatus={handleAdvanceStatus}
            onEdit={(task) => {
              setEditingTask(task);
              setIsModalOpen(true);
            }}
            onDelete={handleDeleteTask}
            onAttemptInvalidTransition={handleAttemptInvalidTransition}
          />
        ) : (
          <TaskList
            tasks={tasks}
            onAdvanceStatus={handleAdvanceStatus}
            onEdit={(task) => {
              setEditingTask(task);
              setIsModalOpen(true);
            }}
            onDelete={handleDeleteTask}
            onAttemptInvalidTransition={handleAttemptInvalidTransition}
          />
        )}
      </main>

      {/* Create / Edit Modal */}
      <TaskFormModal
        isOpen={isModalOpen}
        onClose={() => setIsModalOpen(false)}
        onSubmit={handleSaveTask}
        initialData={editingTask}
      />

      {/* Toast Notification */}
      <Toast toast={toast} onClose={() => setToast(null)} />

    </div>
  );
}
