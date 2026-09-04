import React from 'react';
import { ArrowRight, Edit3, Trash2, Calendar, AlertOctagon, CheckCircle2, Clock } from 'lucide-react';

const STATUS_TRANSITIONS = {
  todo: { next: 'in_progress', label: 'Start Progress', icon: Clock },
  in_progress: { next: 'review', label: 'Submit Review', icon: ArrowRight },
  review: { next: 'done', label: 'Mark Completed', icon: CheckCircle2 },
  done: { next: null, label: 'Final State', icon: CheckCircle2 }
};

export default function TaskCard({ task, onAdvanceStatus, onEdit, onDelete, onAttemptInvalidTransition }) {
  const transitionConfig = STATUS_TRANSITIONS[task.status] || {};

  return (
    <div className="glass-card p-4 flex flex-col justify-between gap-3 animate-fade-in group">
      
      {/* Top Meta Info */}
      <div>
        <div className="flex items-center justify-between mb-2">
          <span className={`status-badge status-${task.status}`}>
            <span className="w-1.5 h-1.5 rounded-full bg-current"></span>
            {task.status.replace('_', ' ')}
          </span>

          <div className="flex items-center gap-2">
            <span className={`priority-badge priority-${task.priority}`}>
              {task.priority}
            </span>
            <button 
              onClick={() => onEdit(task)}
              className="text-slate-400 hover:text-indigo-300 transition-colors p-1 rounded-md hover:bg-slate-700/50"
              title="Edit task"
            >
              <Edit3 className="w-3.5 h-3.5" />
            </button>
            <button 
              onClick={() => onDelete(task.id)}
              className="text-slate-400 hover:text-red-400 transition-colors p-1 rounded-md hover:bg-slate-700/50"
              title="Delete task"
            >
              <Trash2 className="w-3.5 h-3.5" />
            </button>
          </div>
        </div>

        {/* Task Title & Description */}
        <h3 className="font-semibold text-slate-100 text-base leading-snug group-hover:text-indigo-300 transition-colors">
          {task.title}
        </h3>
        {task.description && (
          <p className="text-slate-400 text-xs mt-1.5 line-clamp-2 leading-relaxed">
            {task.description}
          </p>
        )}
      </div>

      {/* Footer & State Transition Actions */}
      <div className="pt-3 border-t border-slate-700/50 flex flex-wrap items-center justify-between gap-2 mt-1">
        
        {/* Due Date */}
        <div className="flex items-center gap-1.5 text-slate-400 text-xs">
          <Calendar className="w-3.5 h-3.5 text-slate-500" />
          <span>{task.due_date ? task.due_date : 'No due date'}</span>
        </div>

        {/* Action Controls */}
        <div className="flex items-center gap-2">
          
          {/* Test Invalid Transition Button (Triggers backend trick check) */}
          {task.status === 'todo' && (
            <button
              onClick={() => onAttemptInvalidTransition(task.id, 'done')}
              className="px-2 py-1 rounded bg-red-500/10 hover:bg-red-500/20 border border-red-500/30 text-red-400 text-[11px] font-medium transition-colors flex items-center gap-1"
              title="Test invalid transition (todo -> done) to verify backend state enforcement"
            >
              <AlertOctagon className="w-3 h-3" />
              Test Skip
            </button>
          )}

          {/* Valid Transition Button */}
          {transitionConfig.next ? (
            <button
              onClick={() => onAdvanceStatus(task.id, transitionConfig.next)}
              className="btn-advance"
            >
              {transitionConfig.label}
              <ArrowRight className="w-3.5 h-3.5" />
            </button>
          ) : (
            <span className="text-[11px] font-medium text-emerald-400/80 bg-emerald-500/10 px-2 py-1 rounded-md border border-emerald-500/20">
              ✓ Done
            </span>
          )}
        </div>

      </div>

    </div>
  );
}
