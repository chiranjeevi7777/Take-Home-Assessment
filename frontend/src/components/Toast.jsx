import React, { useEffect } from 'react';
import { AlertCircle, CheckCircle, X } from 'lucide-react';

export default function Toast({ toast, onClose }) {
  useEffect(() => {
    if (toast) {
      const timer = setTimeout(() => {
        onClose();
      }, 5000);
      return () => clearTimeout(timer);
    }
  }, [toast, onClose]);

  if (!toast) return null;

  const isError = toast.type === 'error';

  return (
    <div className="fixed bottom-6 right-6 z-50 animate-fade-in max-w-md">
      <div 
        className={`glass-panel p-4 flex items-start gap-3 shadow-2xl border ${
          isError 
            ? 'border-red-500/50 bg-red-950/80 text-red-100' 
            : 'border-emerald-500/50 bg-emerald-950/80 text-emerald-100'
        }`}
      >
        {isError ? (
          <AlertCircle className="w-5 h-5 text-red-400 shrink-0 mt-0.5" />
        ) : (
          <CheckCircle className="w-5 h-5 text-emerald-400 shrink-0 mt-0.5" />
        )}

        <div className="flex-1">
          <h4 className="text-xs font-bold uppercase tracking-wider mb-1">
            {isError ? 'API Constraint Violation (HTTP 400)' : 'Operation Success'}
          </h4>
          <p className="text-xs text-slate-200 leading-relaxed">
            {toast.message}
          </p>
        </div>

        <button 
          onClick={onClose}
          className="text-slate-400 hover:text-white p-1 rounded transition-colors"
        >
          <X className="w-4 h-4" />
        </button>
      </div>
    </div>
  );
}
