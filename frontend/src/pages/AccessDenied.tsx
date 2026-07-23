import { Center, Stack, Paper, ThemeIcon, Title, Text, Button, Group } from '@mantine/core';
import { DisableIcon, HomeIcon, LogoutIcon, ICON } from '../lib/icons';
import { useAuth } from '../state/AuthContext';
import { homeUrl } from '../lib/sso';

/**
 * Shown when a user is signed in via central SSO but has NOT been granted the
 * `can_view_billing_portal` permission in the main app. The Home button is
 * env-driven (VITE_HOME_URL) — no hardcoded URLs.
 */
export default function AccessDenied() {
  const { user, logout } = useAuth();

  return (
    <Center mih="100vh" p="lg" style={{ background: 'var(--app-bg, #eef6ff)' }}>
      <Paper withBorder shadow="md" radius="lg" p="xl" maw={460} w="100%">
        <Stack align="center" gap="md">
          <ThemeIcon size={64} radius="xl" variant="light" color="red">
            <DisableIcon size={ICON.xl} />
          </ThemeIcon>
          <Title order={2} ta="center">No access to Billing</Title>
          <Text c="dimmed" ta="center" size="sm">
            {user?.name ? `Hi ${user.name}, your ` : 'Your '}
            account is signed in but doesn&apos;t have permission to use the Billing
            portal. Ask an administrator to grant you billing access in the main
            portal, then reload this page.
          </Text>
          <Group justify="center" gap="sm" mt="xs">
            <Button component="a" href={homeUrl} leftSection={<HomeIcon size={ICON.sm} />}>
              Go to Home
            </Button>
            <Button variant="light" color="gray" onClick={logout} leftSection={<LogoutIcon size={ICON.sm} />}>
              Sign out
            </Button>
          </Group>
        </Stack>
      </Paper>
    </Center>
  );
}
