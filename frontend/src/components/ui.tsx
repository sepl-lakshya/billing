import { CSSProperties, ReactNode } from 'react';
import { Group, Title, Text, Box, Paper, ThemeIcon, Stack, Menu, ActionIcon, Tooltip, type MantineColor } from '@mantine/core';
import { Lift } from './motion';
import { EmptyIcon, MoreIcon, ICON } from '../lib/icons';

/** Standard page title row with optional subtitle + action buttons. */
export function PageHeader({ title, subtitle, actions }: { title: string; subtitle?: string; actions?: ReactNode }) {
  return (
    <Group justify="space-between" align="flex-start" mb="lg" wrap="wrap" gap="sm">
      <Box style={{ minWidth: 0 }}>
        <Title order={1}>{title}</Title>
        {subtitle && <Text c="dimmed" fz="sm" mt={4}>{subtitle}</Text>}
      </Box>
      {actions && <Group gap="sm" wrap="wrap">{actions}</Group>}
    </Group>
  );
}

/** Compact KPI / stat card. */
export function StatCard({
  label, value, icon, color = 'brand', hint,
}: { label: string; value: ReactNode; icon: ReactNode; color?: MantineColor; hint?: string }) {
  return (
    <Lift style={{ height: '100%' }}>
      <Paper withBorder shadow="sm" radius="lg" p="lg" h="100%">
        <Group justify="space-between" align="flex-start" wrap="nowrap">
          <Box style={{ minWidth: 0 }}>
            <Text fz="xs" c="dimmed" tt="uppercase" fw={700} style={{ letterSpacing: '.05em' }}>{label}</Text>
            <Text fz={26} fw={800} mt={6} lh={1.1}>{value}</Text>
            {hint && <Text fz="xs" c="dimmed" mt={4}>{hint}</Text>}
          </Box>
          <ThemeIcon size={46} radius="md" variant="light" color={color}>{icon}</ThemeIcon>
        </Group>
      </Paper>
    </Lift>
  );
}

/** Reusable empty state — icon, title, description and an optional primary action. */
export function EmptyState({
  icon, title = 'Nothing here yet', description, action,
}: { icon?: ReactNode; title?: string; description?: ReactNode; action?: ReactNode }) {
  return (
    <Stack align="center" justify="center" gap="xs" py={48} px="md">
      <ThemeIcon size={52} radius="lg" variant="light" color="brand">
        {icon ?? <EmptyIcon size={ICON.xl} />}
      </ThemeIcon>
      <Text fw={600} fz="md" mt={4}>{title}</Text>
      {description && <Text c="dimmed" fz="sm" ta="center" maw={360}>{description}</Text>}
      {action && <Box mt="xs">{action}</Box>}
    </Stack>
  );
}

/** Titled content section that separates blocks with whitespace (no nested cards). */
export function Section({
  id, title, description, actions, children, style,
}: { id?: string; title?: string; description?: string; actions?: ReactNode; children: ReactNode; style?: CSSProperties }) {
  return (
    <Box component="section" id={id} mb="xl" style={style}>
      {(title || actions) && (
        <Group justify="space-between" align="flex-end" mb="sm" wrap="wrap" gap="sm">
          <Box style={{ minWidth: 0 }}>
            {title && <Title order={2} fz="md" fw={600}>{title}</Title>}
            {description && <Text c="dimmed" fz="xs" mt={2}>{description}</Text>}
          </Box>
          {actions && <Group gap="xs" wrap="wrap">{actions}</Group>}
        </Group>
      )}
      {children}
    </Box>
  );
}

/** Compact KPI tile for dense strips inside a page section. */
export function MiniStat({
  label, value, tone = 'default', mono,
}: { label: string; value: ReactNode; tone?: 'default' | 'primary' | 'success' | 'muted'; mono?: boolean }) {
  const color = tone === 'primary' ? 'var(--primary)'
    : tone === 'success' ? 'var(--success)'
    : tone === 'muted' ? 'var(--muted)'
    : 'var(--text)';
  return (
    <Paper withBorder radius="md" p="sm">
      <Text fz="xs" fw={600} tt="uppercase" c="dimmed" style={{ letterSpacing: '.06em' }}>{label}</Text>
      <Text fz="lg" fw={700} mt={4} style={{ color, fontFamily: mono ? 'var(--font-num)' : undefined }}>{value}</Text>
    </Paper>
  );
}

/** Icon-only action button with an accessible tooltip label. */
export function IconButton({
  label, icon, onClick, color, variant = 'subtle', disabled, size = 'md',
}: {
  label: string;
  icon: ReactNode;
  onClick?: () => void;
  color?: MantineColor;
  variant?: 'subtle' | 'light' | 'filled' | 'default' | 'outline' | 'transparent';
  disabled?: boolean;
  size?: number | string;
}) {
  return (
    <Tooltip label={label} withArrow>
      <ActionIcon aria-label={label} onClick={onClick} color={color} variant={variant} disabled={disabled} size={size}>
        {icon}
      </ActionIcon>
    </Tooltip>
  );
}

export interface OverflowAction {
  label: string;
  icon?: ReactNode;
  onClick: () => void;
  color?: MantineColor;
  disabled?: boolean;
}

/** Row/toolbar overflow menu (⋯) to collapse multiple secondary actions. */
export function OverflowMenu({ actions, label = 'More actions' }: { actions: OverflowAction[]; label?: string }) {
  return (
    <Menu shadow="lg" width={190} position="bottom-end" withinPortal>
      <Menu.Target>
        <Tooltip label={label} withArrow>
          <ActionIcon variant="subtle" color="gray" aria-label={label}>
            <MoreIcon size={ICON.md} />
          </ActionIcon>
        </Tooltip>
      </Menu.Target>
      <Menu.Dropdown>
        {actions.map((a, i) => (
          <Menu.Item key={i} color={a.color} disabled={a.disabled} leftSection={a.icon} onClick={a.onClick}>
            {a.label}
          </Menu.Item>
        ))}
      </Menu.Dropdown>
    </Menu>
  );
}
