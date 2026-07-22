import { createContext, useContext, useState, useCallback, ReactNode } from 'react';

type ToastKind = 'ok' | 'err';
interface Toast { id: number; kind: ToastKind; message: string; }

interface ToastCtx {
  push: (kind: ToastKind, message: string) => void;
  success: (message: string) => void;
  error: (message: string) => void;
}

const Ctx = createContext<ToastCtx>({ push: () => {}, success: () => {}, error: () => {} });

export function ToastProvider({ children }: { children: ReactNode }) {
  const [toasts, setToasts] = useState<Toast[]>([]);

  const push = useCallback((kind: ToastKind, message: string) => {
    const id = Date.now() + Math.random();
    setToasts((t) => [...t, { id, kind, message }]);
    setTimeout(() => setToasts((t) => t.filter((x) => x.id !== id)), 3500);
  }, []);

  const success = useCallback((m: string) => push('ok', m), [push]);
  const error = useCallback((m: string) => push('err', m), [push]);

  return (
    <Ctx.Provider value={{ push, success, error }}>
      {children}
      <div className="toast-wrap">
        {toasts.map((t) => (
          <div key={t.id} className={`toast ${t.kind}`}>
            <span>{t.kind === 'ok' ? '✓' : '⚠'}</span>
            <span>{t.message}</span>
          </div>
        ))}
      </div>
    </Ctx.Provider>
  );
}

export const useToast = () => useContext(Ctx);
