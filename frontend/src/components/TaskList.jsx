import React from 'react';
import { ArrowRight, Edit3, Trash2, Calendar, AlertOctagon } from 'lucide-react';

const NEXT_STATUS = {
  todo: 'in_progress',
  in_progress: 'review',
  review: 'done',
  done: null
};

export default function TaskList({ tasks, onAdvanceStatus, onEdit, onDelete, onAttemptInvalidTransition }) {
  if (tasks.length === 0) {
    return (
      <div className="glass-panel p-12 text-center text-slate-400">
        <p className="text-sm">No tasks found matching your filter criteria.</p>
      </div>
    );
  }

  return (
    <div className="glass-panel overflow-hidden">
      <div className="overflow-x-auto">
        <table className="w-full text-left text-sm text-slate-300">
          <thead className="bg-slate-800/80 text-xs uppercase tracking-wider text-slate-400 border-b border-slate-700/60">
            <tr>
              <th className="px-6 py-4">Task Details</th>
              <th className="px-4 py-4">Priority</th>
              <th className="px-4 py-4">Status</th>
              <th className="px-4 py-4">Due Date</th>
              <th className="px-6 py-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-slate-800/60">
            {tasks.map(task => {
              const nextStatus = NEXT_STATUS[task.status];
              return (
                <tr key={task.id} className="hover:bg-slate-800/40 transition-colors group">
                  <td className="px-6 py-4">
                    <div className="font-semibold text-slate-100 group-hover:text-indigo-300 transition-colors">
                      {task.title}
                    </div>
                    {task.description && (
                      <div className="text-slate-400 text-xs mt-0.5 line-clamp-1">
                        {task.description}
                      </div>
                    )}
                  </td>
                  <td className="px-4 py-4">
                    <span className={`priority-badge priority-${task.priority}`}>
                      {task.priority}
                    </span>
                  </td>
                  <td className="px-4 py-4">
                    <span className={`status-badge status-${task.status}`}>
                      {task.status.replace('_', ' ')}
                    </span>
                  </td>
                  <td className="px-4 py-4 text-xs text-slate-400">
                    <div className="flex items-center gap-1.5">
                      <Calendar className="w-3.5 h-3.5 text-slate-500" />
                      {task.due_date ? task.due_date : 'N/A'}
                    </div>
                  </td>
                  <td className="px-6 py-4 text-right">
                    <div className="flex items-center justify-end gap-2">
                      {task.status === 'todo' && (
                        <button
                          onClick={() => onAttemptInvalidTransition(task.id, 'done')}
                          className="px-2 py-1 rounded bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/30 text-[11px] font-medium flex items-center gap-1"
                          title="Test invalid transition"
                        >
                          <AlertOctagon className="w-3 h-3" />
                          Test Skip
                        </button>
                      )}

                      {nextStatus ? (
                        <button
                          onClick={() => onAdvanceStatus(task.id, nextStatus)}
                          className="btn-advance"
                        >
                          Advance
                          <ArrowRight className="w-3.5 h-3.5" />
                        </button>
                      ) : (
                        <span className="text-xs font-semibold text-emerald-400 px-2 py-1 rounded bg-emerald-500/10 border border-emerald-500/20">
                          Completed
                        </span>
                      )}

                      <button
                        onClick={() => onEdit(task)}
                        className="text-slate-400 hover:text-indigo-300 p-1.5 rounded-lg hover:bg-slate-700/50"
                      >
                        <Edit3 className="w-4 h-4" />
                      </button>

                      <button
                        onClick={() => onDelete(task.id)}
                        className="text-slate-400 hover:text-red-400 p-1.5 rounded-lg hover:bg-slate-700/50"
                      >
                        <Trash2 className="w-4 h-4" />
                      </button>
                    </div>
                  </td>
                </tr>
              );
            })}
          </tbody>
        </table>
      </div>
    </div>
  );
}
