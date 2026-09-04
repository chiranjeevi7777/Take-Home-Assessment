import React from 'react';
import { ArrowRight, ShieldCheck, Zap } from 'lucide-react';

export default function WorkflowGuide() {
  const steps = [
    { label: 'To Do', color: 'text-sky-400 border-sky-500/40 bg-sky-500/10' },
    { label: 'In Progress', color: 'text-amber-400 border-amber-500/40 bg-amber-500/10' },
    { label: 'In Review', color: 'text-purple-400 border-purple-500/40 bg-purple-500/10' },
    { label: 'Completed', color: 'text-emerald-400 border-emerald-500/40 bg-emerald-500/10' }
  ];

  return (
    <div className="glass-panel p-4 mb-6 border-indigo-500/30 bg-gradient-to-r from-indigo-950/40 via-purple-950/30 to-slate-900/60">
      <div className="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
        
        <div className="flex items-center gap-3">
          <div className="p-2 rounded-lg bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
            <ShieldCheck className="w-5 h-5" />
          </div>
          <div>
            <div className="flex items-center gap-2">
              <h3 className="font-bold text-sm text-slate-100">
                Backend State Machine Rule (Trick Requirement)
              </h3>
              <span className="text-[10px] font-bold uppercase tracking-wider bg-indigo-500/20 text-indigo-300 px-2 py-0.5 rounded-full border border-indigo-500/30">
                Strict Order
              </span>
            </div>
            <p className="text-xs text-slate-400 mt-0.5">
              Tasks must advance strictly in order. Skipping states (e.g. TODO directly to DONE) is blocked by the API with HTTP 400.
            </p>
          </div>
        </div>

        {/* Visual Workflow Steps */}
        <div className="flex items-center gap-2 overflow-x-auto w-full lg:w-auto py-1">
          {steps.map((step, idx) => (
            <React.Fragment key={step.label}>
              <div className={`px-3 py-1.5 rounded-lg border text-xs font-semibold whitespace-nowrap ${step.color}`}>
                {step.label}
              </div>
              {idx < steps.length - 1 && (
                <ArrowRight className="w-3.5 h-3.5 text-slate-500 shrink-0" />
              )}
            </React.Fragment>
          ))}
        </div>

      </div>
    </div>
  );
}
