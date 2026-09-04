import React from 'react';
import TaskCard from './TaskCard';

const COLUMNS = [
  { id: 'todo', title: 'To Do', accent: 'border-sky-500/50 bg-sky-500/5', badgeClass: 'text-sky-400 bg-sky-500/10' },
  { id: 'in_progress', title: 'In Progress', accent: 'border-amber-500/50 bg-amber-500/5', badgeClass: 'text-amber-400 bg-amber-500/10' },
  { id: 'review', title: 'In Review', accent: 'border-purple-500/50 bg-purple-500/5', badgeClass: 'text-purple-400 bg-purple-500/10' },
  { id: 'done', title: 'Completed', accent: 'border-emerald-500/50 bg-emerald-500/5', badgeClass: 'text-emerald-400 bg-emerald-500/10' }
];

export default function TaskBoard({ tasks, onAdvanceStatus, onEdit, onDelete, onAttemptInvalidTransition }) {
  return (
    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
      {COLUMNS.map(col => {
        const colTasks = tasks.filter(t => t.status === col.id);
        return (
          <div 
            key={col.id}
            className={`glass-panel p-4 flex flex-col gap-3 min-h-[500px] border-t-2 ${col.accent}`}
          >
            {/* Column Header */}
            <div className="flex items-center justify-between pb-2 border-b border-slate-700/50">
              <h2 className="font-bold text-slate-200 text-sm tracking-wide uppercase flex items-center gap-2">
                {col.title}
              </h2>
              <span className={`px-2.5 py-0.5 rounded-full text-xs font-semibold ${col.badgeClass}`}>
                {colTasks.length}
              </span>
            </div>

            {/* Task List */}
            <div className="flex flex-col gap-3 flex-1 overflow-y-auto">
              {colTasks.length > 0 ? (
                colTasks.map(task => (
                  <TaskCard
                    key={task.id}
                    task={task}
                    onAdvanceStatus={onAdvanceStatus}
                    onEdit={onEdit}
                    onDelete={onDelete}
                    onAttemptInvalidTransition={onAttemptInvalidTransition}
                  />
                ))
              ) : (
                <div className="flex flex-col items-center justify-center h-48 border-2 border-dashed border-slate-800 rounded-xl p-4 text-center">
                  <p className="text-slate-500 text-xs font-medium">No tasks in {col.title}</p>
                </div>
              )}
            </div>
          </div>
        );
      })}
    </div>
  );
}
