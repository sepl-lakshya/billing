import { CSSProperties, ReactNode } from 'react';
import { motion, useReducedMotion, type Transition, type Variants } from 'framer-motion';

/**
 * Shared Framer Motion primitives for the SEPL Billing UI.
 * All helpers are reduced-motion aware (offsets collapse to 0) and purely
 * presentational — they never alter layout, data or behaviour.
 */

const EASE_OUT = 'easeOut' as const;
const DURATION = 0.28;

export const springSoft: Transition = { type: 'spring', stiffness: 380, damping: 30 };

interface BaseProps {
  children: ReactNode;
  className?: string;
  style?: CSSProperties;
}

/** Fade (and optionally rise) content in on mount. */
export function FadeIn({
  children,
  className,
  style,
  y = 8,
  delay = 0,
  duration = DURATION,
}: BaseProps & { y?: number; delay?: number; duration?: number }) {
  const reduce = useReducedMotion();
  return (
    <motion.div
      className={className}
      style={style}
      initial={{ opacity: 0, y: reduce ? 0 : y }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration, delay, ease: EASE_OUT }}
    >
      {children}
    </motion.div>
  );
}

/** Page-level entrance wrapper — mount it per route for a subtle transition. */
export function PageTransition({ children, className, style }: BaseProps) {
  const reduce = useReducedMotion();
  return (
    <motion.div
      className={className}
      style={style}
      initial={{ opacity: 0, y: reduce ? 0 : 10 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.3, ease: EASE_OUT }}
    >
      {children}
    </motion.div>
  );
}

/** Container that staggers the entrance of its <StaggerItem> children. */
export function Stagger({
  children,
  className,
  style,
  gap = 0.05,
  delay = 0,
}: BaseProps & { gap?: number; delay?: number }) {
  const reduce = useReducedMotion();
  const variants: Variants = {
    hidden: {},
    show: { transition: { staggerChildren: reduce ? 0 : gap, delayChildren: delay } },
  };
  return (
    <motion.div className={className} style={style} variants={variants} initial="hidden" animate="show">
      {children}
    </motion.div>
  );
}

/** Item to be placed inside <Stagger>. */
export function StaggerItem({ children, className, style }: BaseProps) {
  const reduce = useReducedMotion();
  const variants: Variants = {
    hidden: { opacity: 0, y: reduce ? 0 : 10 },
    show: { opacity: 1, y: 0, transition: { duration: DURATION, ease: EASE_OUT } },
  };
  return (
    <motion.div className={className} style={style} variants={variants}>
      {children}
    </motion.div>
  );
}

/** Hover-lift + tap-press wrapper for cards / tiles / interactive surfaces. */
export function Lift({
  children,
  className,
  style,
  y = -3,
}: BaseProps & { y?: number }) {
  const reduce = useReducedMotion();
  return (
    <motion.div
      className={className}
      style={style}
      whileHover={reduce ? undefined : { y }}
      whileTap={reduce ? undefined : { scale: 0.99 }}
      transition={springSoft}
    >
      {children}
    </motion.div>
  );
}
