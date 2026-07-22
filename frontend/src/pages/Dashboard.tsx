import { Link } from 'react-router-dom';
import {
  SimpleGrid, Paper, Group, Text, Title, Badge, Stack, Table,
  ThemeIcon, Progress, Anchor, Grid, Skeleton,
} from '@mantine/core';
import {
  CloudIcon, ProductIcon, OemIcon, DistributorIcon,
  InvoiceIcon, CalendarIcon, WarningIcon, ClockIcon,
  ChartIcon, ICON, type AppIcon,
} from '../lib/icons';
import Layout from '../components/Layout';
import { PageHeader, StatCard } from '../components/ui';
import { Stagger, StaggerItem } from '../components/motion';
import { useFetch } from '../hooks/useFetch';
import { money } from '../lib/format';
import type { MantineColor } from '@mantine/core';

interface PendingRow { id: number; hash: string; name: string; months_pending: number }
interface ProgressRow {
  id: number; project_id: number; month: number; year: number; status: number;
  purchase_header_status: number; month_name: string; project_name: string;
  project_hash: string; portal_total: number;
}
interface TrendRow { year: number; month: number; month_name: string; portal_total: number; sales_total: number }

interface Stats {
  cloudProjects: number;
  products: number;
  oems: number;
  distributors: number;
  invoices: number;
  bills: number;
  pendingBilling: PendingRow[];
  inProgress: ProgressRow[];
  trend: TrendRow[];
}

const cards: { key: keyof Stats; label: string; icon: AppIcon; color: MantineColor; money?: boolean }[] = [
  { key: 'cloudProjects', label: 'Cloud Projects', icon: CloudIcon, color: 'brand' },
  { key: 'bills', label: 'Monthly Bills', icon: CalendarIcon, color: 'blue' },
  { key: 'invoices', label: 'Invoices', icon: InvoiceIcon, color: 'orange' },
  { key: 'products', label: 'Products', icon: ProductIcon, color: 'teal' },
  { key: 'oems', label: 'OEMs', icon: OemIcon, color: 'indigo' },
  { key: 'distributors', label: 'Distributors', icon: DistributorIcon, color: 'cyan' },
];

/** Human label + colour for where a bill sits in the portal→sales→purchase pipeline. */
function billStage(b: ProgressRow): { label: string; color: MantineColor; pct: number } {
  if (b.status <= 0) return { label: 'Draft', color: 'gray', pct: 5 };
  if (b.status === 1) return { label: 'Sales pricing pending', color: 'yellow', pct: 33 };
  if (b.status === 2) return { label: 'Purchase pricing pending', color: 'orange', pct: 66 };
  if (b.purchase_header_status === 0) return { label: 'Purchase confirm pending', color: 'orange', pct: 85 };
  return { label: 'Complete', color: 'green', pct: 100 };
}

export default function Dashboard() {
  const { data, loading } = useFetch<Stats>('/dashboard', {
    cloudProjects: 0, products: 0, oems: 0,
    distributors: 0, invoices: 0, bills: 0,
    pendingBilling: [], inProgress: [], trend: [],
  });

  const trendMax = Math.max(1, ...(data.trend || []).map((t) => Math.max(Number(t.portal_total), Number(t.sales_total))));

  return (
    <Layout title="Home">
      <PageHeader title="Dashboard" subtitle="Overview of your billing workspace" />
      {loading ? (
        <SimpleGrid cols={{ base: 1, xs: 2, md: 4 }} spacing="lg">
          {Array.from({ length: 6 }).map((_, i) => (
            <Paper key={i} withBorder radius="lg" p="lg">
              <Skeleton height={11} width="55%" radius="sm" />
              <Skeleton height={26} width="70%" mt={14} radius="sm" />
            </Paper>
          ))}
        </SimpleGrid>
      ) : (
        <Stack gap="lg">
          <Stagger>
            <SimpleGrid cols={{ base: 1, xs: 2, md: 4 }} spacing="lg">
              {cards.map((c) => (
                <StaggerItem key={c.key} style={{ height: '100%' }}>
                  <StatCard
                    label={c.label}
                    color={c.color}
                    icon={<c.icon size={ICON.xl} />}
                    value={c.money ? money(data[c.key] as number) : (data[c.key] as number)}
                  />
                </StaggerItem>
              ))}
            </SimpleGrid>
          </Stagger>

          <Grid gutter="lg">
            {/* Pending billing months */}
            <Grid.Col span={{ base: 12, lg: 6 }}>
              <Paper withBorder shadow="sm" radius="lg" p="lg" h="100%">
                <Group gap="sm" mb="md">
                  <ThemeIcon size={36} radius="md" variant="light" color="red"><WarningIcon size={ICON.lg} /></ThemeIcon>
                  <div>
                    <Title order={4}>Billing Pending</Title>
                    <Text fz="xs" c="dimmed">Cloud projects with months not yet billed</Text>
                  </div>
                </Group>
                {data.pendingBilling.length === 0 ? (
                  <Text c="dimmed" fz="sm">All caught up — every month is billed.</Text>
                ) : (
                  <Table verticalSpacing={6} fz="sm">
                    <Table.Tbody>
                      {data.pendingBilling.map((p) => (
                        <Table.Tr key={p.id}>
                          <Table.Td>
                            <Anchor component={Link} to={`/cloud-projects/${p.hash}`} fw={600} fz="sm">
                              {p.name}
                            </Anchor>
                          </Table.Td>
                          <Table.Td w={150} ta="right">
                            <Badge color={p.months_pending > 2 ? 'red' : 'yellow'} variant="light">
                              {p.months_pending} month{p.months_pending > 1 ? 's' : ''} pending
                            </Badge>
                          </Table.Td>
                        </Table.Tr>
                      ))}
                    </Table.Tbody>
                  </Table>
                )}
              </Paper>
            </Grid.Col>

            {/* Bills in progress */}
            <Grid.Col span={{ base: 12, lg: 6 }}>
              <Paper withBorder shadow="sm" radius="lg" p="lg" h="100%">
                <Group gap="sm" mb="md">
                  <ThemeIcon size={36} radius="md" variant="light" color="orange"><ClockIcon size={ICON.lg} /></ThemeIcon>
                  <div>
                    <Title order={4}>Bills In Progress</Title>
                    <Text fz="xs" c="dimmed">Bills that still need pricing steps</Text>
                  </div>
                </Group>
                {data.inProgress.length === 0 ? (
                  <Text c="dimmed" fz="sm">No bills pending — nothing in the pipeline.</Text>
                ) : (
                  <Stack gap="xs">
                    {data.inProgress.map((b) => {
                      const stage = billStage(b);
                      return (
                        <Paper key={b.id} withBorder radius="md" p="xs">
                          <Group justify="space-between" wrap="nowrap" gap="xs">
                            <div style={{ minWidth: 0, flex: 1 }}>
                              <Anchor component={Link} to={`/cloud-projects/${b.project_hash}`} fw={600} fz="sm" truncate="end" display="block">
                                {b.project_name}
                              </Anchor>
                              <Text fz="xs" c="dimmed">{b.month_name} {b.year} · Portal {money(b.portal_total)}</Text>
                            </div>
                            <Badge color={stage.color} variant="light" style={{ flexShrink: 0 }}>{stage.label}</Badge>
                          </Group>
                          <Progress value={stage.pct} color={stage.color} size="xs" mt={6} radius="xl" />
                        </Paper>
                      );
                    })}
                  </Stack>
                )}
              </Paper>
            </Grid.Col>
          </Grid>

          {/* Monthly trend */}
          {data.trend.length > 0 && (
            <Paper withBorder shadow="sm" radius="lg" p="lg">
              <Group gap="sm" mb="md">
                <ThemeIcon size={36} radius="md" variant="light" color="brand"><ChartIcon size={ICON.lg} /></ThemeIcon>
                <div>
                  <Title order={4}>Monthly Totals</Title>
                  <Text fz="xs" c="dimmed">Portal cost vs sales billing for recent months</Text>
                </div>
              </Group>
              <Stack gap="sm">
                {data.trend.map((t) => (
                  <div key={`${t.year}-${t.month}`}>
                    <Group justify="space-between" mb={4}>
                      <Text fz="sm" fw={600}>{t.month_name} {t.year}</Text>
                      <Text fz="xs" c="dimmed">
                        Portal {money(t.portal_total)} · Sales {money(t.sales_total)}
                      </Text>
                    </Group>
                    <Progress.Root size="lg" radius="xl">
                      <Progress.Section value={(Number(t.portal_total) / trendMax) * 50} color="brand.4" />
                      <Progress.Section value={(Number(t.sales_total) / trendMax) * 50} color="teal.5" />
                    </Progress.Root>
                  </div>
                ))}
                <Group gap="lg" mt={2}>
                  <Group gap={6}><div style={{ width: 10, height: 10, borderRadius: 3, background: 'var(--mantine-color-brand-4)' }} /><Text fz="xs" c="dimmed">Portal (cost)</Text></Group>
                  <Group gap={6}><div style={{ width: 10, height: 10, borderRadius: 3, background: 'var(--mantine-color-teal-5)' }} /><Text fz="xs" c="dimmed">Sales (billed)</Text></Group>
                </Group>
              </Stack>
            </Paper>
          )}
        </Stack>
      )}
    </Layout>
  );
}
