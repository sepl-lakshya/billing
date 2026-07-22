import { ReactNode } from 'react';
import { Modal as MModal, Group, Divider, Box } from '@mantine/core';
import { FadeIn } from './motion';

interface ModalProps {
  title: string;
  onClose: () => void;
  children: ReactNode;
  footer?: ReactNode;
  size?: 'md' | 'lg' | 'xl';
  /** Full-width workspace takeover for pricing wizards — no nested scrollbars. */
  wizard?: boolean;
  /** Old-portal style step timeline (step = 1-based, of = total steps). */
  step?: { at: number; of: number };
}

const sizeMap = { md: '560px', lg: '760px', xl: '1080px' } as const;

export default function Modal({ title, onClose, children, footer, size = 'md', wizard, step }: ModalProps) {
  return (
    <MModal
      opened
      onClose={onClose}
      title={title}
      size={wizard ? '100%' : sizeMap[size]}
      fullScreen={wizard}
      radius={wizard ? 0 : undefined}
      styles={{
        title: { fontWeight: 700, fontSize: '1rem', textTransform: 'uppercase', letterSpacing: '.06em' },
        content: wizard ? { display: 'flex', flexDirection: 'column' } : undefined,
        body: wizard ? { flex: 1, display: 'flex', flexDirection: 'column', maxWidth: 1280, width: '100%', margin: '0 auto' } : undefined,
      }}
    >
      {step && (
        <div className="wiz-timeline">
          {Array.from({ length: step.of }).map((_, i) => (
            <span key={i} style={{ display: 'contents' }}>
              {i > 0 && <span className={`line ${i < step.at ? 'fill' : ''}`} />}
              <span className={`step ${i < step.at ? 'fill' : ''}`} />
            </span>
          ))}
        </div>
      )}
      <Box style={wizard ? { flex: 1 } : undefined}>
        {wizard ? children : <FadeIn y={6} duration={0.22}>{children}</FadeIn>}
      </Box>
      {footer && (
        <>
          <Divider my="md" />
          <Group justify="flex-end" gap="sm">{footer}</Group>
        </>
      )}
    </MModal>
  );
}
