import { ReactNode, useEffect, useState } from 'react';
import { AppShell, Burger, Group, Title, rem } from '@mantine/core';
import { useDisclosure, useMediaQuery } from '@mantine/hooks';
import Sidebar from './Sidebar';
import { PageTransition } from './motion';
import { RupeeIcon, ICON } from '../lib/icons';

export default function Layout({ title, children }: { title: string; children: ReactNode }) {
  const [mobileOpened, { toggle: toggleMobile, close: closeMobile }] = useDisclosure(false);
  const isMobile = useMediaQuery('(max-width: 48em)');
  const [collapsed, setCollapsed] = useState(() => localStorage.getItem('billing-sidebar-collapsed') === '1');

  useEffect(() => {
    localStorage.setItem('billing-sidebar-collapsed', collapsed ? '1' : '0');
  }, [collapsed]);

  const rail = !isMobile && collapsed;
  const navWidth = isMobile ? 260 : collapsed ? 74 : 268;

  return (
    <AppShell
      header={{ height: 54, collapsed: !isMobile }}
      navbar={{ width: navWidth, breakpoint: 'sm', collapsed: { mobile: !mobileOpened } }}
      padding={{ base: 'md', sm: 'lg' }}
    >
      <AppShell.Header withBorder={false} style={{ background: 'var(--grad-header)' }}>
        <Group h="100%" px="md" gap="sm" wrap="nowrap">
          <Burger opened={mobileOpened} onClick={toggleMobile} size="sm" color="#fff" />
          <RupeeIcon size={ICON.lg} color="#fff" />
          <Title order={1} c="#fff" lineClamp={1} style={{ fontSize: rem(15) }}>{title}</Title>
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
