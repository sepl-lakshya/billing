import { useEffect, useState } from 'react';
import { Stack, Paper, Group, Badge, Text, Title, Loader, Center, Button } from '@mantine/core';
import { RefreshIcon, SystemIcon, ICON } from '../lib/icons';
import Layout from '../components/Layout';
import { PageHeader } from '../components/ui';
import { http } from '../api/client';
import { useAuth } from '../state/AuthContext';

interface Health {
  status: 0 | 1;
  msg: string;
  data?: {
    env: string;
    db: string;
    auth: string;
    uptime: number;
  };
}

export default function System() {
  const { user } = useAuth();
  const [health, setHealth] = useState<Health | null>(null);
  const [loading, setLoading] = useState(true);

  const checkHealth = async () => {
    setLoading(true);
    try {
      const res = await http.get('/api/health');
      setHealth(res.data);
    } catch (err: any) {
      setHealth({
        status: 0,
        msg: err?.message || 'Cannot reach backend',
        data: { env: '?', db: 'error', auth: '?', uptime: 0 },
      });
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    checkHealth();
  }, []);

  const dbStatus = health?.data?.db;
  const uptime = health?.data?.uptime || 0;
  const hours = Math.floor(uptime / 3600);
  const mins = Math.floor((uptime % 3600) / 60);

  return (
    <Layout title="System">
      <PageHeader
        title="System Status"
        subtitle="Backend health, identity, and configuration"
        actions={<Button size="xs" leftSection={<RefreshIcon size={ICON.xs} />} onClick={checkHealth} loading={loading}>Refresh</Button>}
      />

      <Stack gap="lg">
        {/* Backend status */}
        <Paper withBorder shadow="sm" radius="lg" p="lg">
          <Group justify="space-between" mb="md">
            <Group gap="sm">
              <SystemIcon size={ICON.xl} color={health?.status === 1 ? 'green' : 'red'} />
              <div>
                <Title order={4}>Backend</Title>
                <Text fz="xs" c="dimmed">{health?.data?.env || 'unknown'} environment</Text>
              </div>
            </Group>
            <Badge color={health?.status === 1 ? 'green' : 'red'} size="lg" leftSection={<SystemIcon size={ICON.xs} />}>
              {health?.status === 1 ? 'Healthy' : 'Unreachable'}
            </Badge>
          </Group>

          {loading ? (
            <Center py="lg"><Loader size="sm" /></Center>
          ) : (
            <Stack gap="xs" fz="sm">
              <Group justify="space-between">
                <Text c="dimmed">Database</Text>
                <Badge color={dbStatus === 'up' ? 'green' : 'red'} variant="light">{dbStatus || 'unknown'}</Badge>
              </Group>
              <Group justify="space-between">
                <Text c="dimmed">Authentication</Text>
                <Badge color="blue" variant="light">{health?.data?.auth || 'unknown'}</Badge>
              </Group>
              <Group justify="space-between">
                <Text c="dimmed">Uptime</Text>
                <Text fw={600}>
                  {hours > 0 ? `${hours}h ${mins}m` : `${mins}m`}
                </Text>
              </Group>
              {health?.msg && (
                <Text c={health.status === 1 ? 'green' : 'red'} fz="xs" fw={500}>
                  {health.msg}
                </Text>
              )}
            </Stack>
          )}
        </Paper>

        {/* Current identity */}
        <Paper withBorder shadow="sm" radius="lg" p="lg">
          <Title order={4} mb="md">Your Identity</Title>
          {user ? (
            <Stack gap="xs" fz="sm">
              <Group justify="space-between">
                <Text c="dimmed">ID</Text>
                <Text fw={600}>{user.id}</Text>
              </Group>
              <Group justify="space-between">
                <Text c="dimmed">Name</Text>
                <Text fw={600}>{user.name}</Text>
              </Group>
              <Group justify="space-between">
                <Text c="dimmed">Email</Text>
                <Text fw={600}>{user.email}</Text>
              </Group>
              <Group justify="space-between">
                <Text c="dimmed">Roles</Text>
                <Group gap={4}>
                  {user.roles.map((r) => (
                    <Badge key={r} size="sm" color="blue" variant="light">
                      {r}
                    </Badge>
                  ))}
                </Group>
              </Group>
            </Stack>
          ) : (
            <Text c="dimmed" fz="sm">Not authenticated (system user active in open mode)</Text>
          )}
        </Paper>
      </Stack>
    </Layout>
  );
}
