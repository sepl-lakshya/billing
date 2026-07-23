import { ReactNode, useEffect, useState } from 'react';
import { AppShell, Burger, Group, Title, rem, ActionIcon, Menu, Avatar, Text, Tooltip } from '@mantine/core';
import { useDisclosure, useMediaQuery } from '@mantine/hooks';
import Sidebar from './Sidebar';
import { PageTransition } from './motion';
import { RupeeIcon, HomeIcon, LogoutIcon, ICON } from '../lib/icons';
import { useAuth } from '../state/AuthContext';
import { homeUrl } from '../lib/sso';

export default function Layout({ title, children }: { title: string; children: ReactNode }) {
  const [mobileOpened, { toggle: toggleMobile, close: closeMobile }] = useDisclosure(false);
  const isMobile = useMediaQuery('(max-width: 48em)');
  const [collapsed, setCollapsed] = useState(() => localStorage.getItem('billing-sidebar-collapsed') === '1');
  const { user, logout, isAuthenticated } = useAuth();
  const displayName = user?.name || '';
  const userInitials =
    displayName.split(/\s+/).map((s) => s[0]).filter(Boolean).slice(0, 2).join('').toUpperCase() || 'U';

  useEffect(() => {
    localStorage.setItem('billing-sidebar-collapsed', collapsed ? '1' : '0');
  }, [collapsed]);

  const rail = !isMobile && collapsed;
  const navWidth = isMobile ? 260 : collapsed ? 64 : 268;

  return (
    <AppShell
      header={{ height: 54, collapsed: !isMobile }}
      navbar={{ width: navWidth, breakpoint: 'sm', collapsed: { mobile: !mobileOpened } }}
      padding={{ base: 'md', sm: 'lg' }}
    >
      <AppShell.Header withBorder={false} style={{ background: 'var(--grad-header)' }}>
        <Group h="100%" px="md" gap="sm" wrap="nowrap" justify="space-between">
          <Group gap="sm" wrap="nowrap" style={{ minWidth: 0 }}>
            <Burger opened={mobileOpened} onClick={toggleMobile} size="sm" color="#fff" />
            <RupeeIcon size={ICON.lg} color="#fff" />
            <Title order={1} c="#fff" lineClamp={1} style={{ fontSize: rem(15) }}>{title}</Title>
          </Group>
          <Group gap="xs" wrap="nowrap">
            <Tooltip label="Home" withArrow>
              <ActionIcon component="a" href={homeUrl} variant="white" radius="xl" size="lg" aria-label="Home">
                <HomeIcon size={ICON.md} />
              </ActionIcon>
            </Tooltip>
            {isAuthenticated && (
              <Menu shadow="md" width={220} position="bottom-end" withArrow>
                <Menu.Target>
                  <Tooltip label={user?.name || 'Account'} withArrow>
                    <Avatar radius="xl" size={34} variant="white" color="brand" style={{ cursor: 'pointer' }}>
                      {userInitials}
                    </Avatar>
                  </Tooltip>
                </Menu.Target>
                <Menu.Dropdown>
                  <Menu.Label>
                    <Text size="sm" fw={600} truncate>{user?.name || 'Signed in'}</Text>
                    {user?.email && <Text size="xs" c="dimmed" truncate>{user.email}</Text>}
                  </Menu.Label>
                  <Menu.Item leftSection={<HomeIcon size={ICON.sm} />} component="a" href={homeUrl}>
                    Home
                  </Menu.Item>
                  <Menu.Item color="red" leftSection={<LogoutIcon size={ICON.sm} />} onClick={logout}>
                    Sign out
                  </Menu.Item>
                </Menu.Dropdown>
              </Menu>
            )}
          </Group>
        </Group>
      </AppShell.Header>

      <AppShell.Navbar p={0} withBorder={false}>
        <Sidebar
          collapsed={rail}
          showToggle={!isMobile}
          onToggle={() => setCollapsed((v) => !v)}
          onNavigate={closeMobile}
        />
      </AppShell.Navbar>

      <AppShell.Main>
        <PageTransition>{children}</PageTransition>
      </AppShell.Main>
    </AppShell>
  );
}
