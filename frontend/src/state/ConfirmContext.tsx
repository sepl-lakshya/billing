import { createContext, useContext, useState, useCallback, ReactNode } from 'react';
import { Button, Text } from '@mantine/core';
import Modal from '../components/Modal';

interface ConfirmOptions {
  title?: string;
  message: string;
  confirmLabel?: string;
  danger?: boolean;
}

const Ctx = createContext<(opts: ConfirmOptions) => Promise<boolean>>(async () => false);

export function ConfirmProvider({ children }: { children: ReactNode }) {
  const [state, setState] = useState<ConfirmOptions | null>(null);
  const [resolver, setResolver] = useState<((v: boolean) => void) | null>(null);

  const confirm = useCallback((opts: ConfirmOptions) => {
    setState(opts);
    return new Promise<boolean>((resolve) => setResolver(() => resolve));
  }, []);

  const close = (result: boolean) => {
    resolver?.(result);
    setState(null);
    setResolver(null);
  };

  return (
    <Ctx.Provider value={confirm}>
      {children}
      {state && (
        <Modal
          title={state.title || 'Please confirm'}
          onClose={() => close(false)}
          footer={
            <>
              <Button variant="default" onClick={() => close(false)}>Cancel</Button>
              <Button color={state.danger ? 'red' : undefined} onClick={() => close(true)}>
                {state.confirmLabel || 'Confirm'}
              </Button>
            </>
          }
        >
          <Text size="sm" style={{ margin: 0 }}>{state.message}</Text>
        </Modal>
      )}
    </Ctx.Provider>
  );
}

export const useConfirm = () => useContext(Ctx);
