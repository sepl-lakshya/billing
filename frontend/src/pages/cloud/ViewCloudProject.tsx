import { useEffect, useRef, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { Button, SimpleGrid, TextInput, Textarea, Select as MSelect, Center, Loader, Group, Anchor, Paper, Box, Title, Text } from '@mantine/core';
import { BackIcon, CloudUploadIcon, EditIcon, ICON } from '../../lib/icons';
import Layout from '../../components/Layout';
import Modal from '../../components/Modal';
import { Section, MiniStat } from '../../components/ui';
import { apiGet, apiPut } from '../../api/client';
import { useToast } from '../../state/ToastContext';
import { useLookups } from '../../state/LookupContext';
import { Project } from '../../api/types';
import { fmtDate, money } from '../../lib/format';
import ItemsTab from './tabs/ItemsTab';
import MonthlyBillTab from './tabs/MonthlyBillTab';
import { DiscountsTab } from './tabs/OtherTabs';
import { AttachmentsTab } from './tabs/PeopleTabs';
import ImportConsumption from './import/ImportConsumption';

const SECTIONS = [
  { id: 'dashboard', label: 'Dashboard' },
  { id: 'discounts', label: 'Discounts' },
  { id: 'products', label: 'Headers & Products' },
  { id: 'billing', label: 'Monthly Billing' },
  { id: 'attachments', label: 'Attachments' },
];

export default function ViewCloudProject() {
  const { hash } = useParams();
  const nav = useNavigate();
  const toast = useToast();
  const { lookups } = useLookups();
  const [project, setProject] = useState<Project | null>(null);
  const [edit, setEdit] = useState(false);
  const [showImport, setShowImport] = useState(false);
  const [active, setActive] = useState('dashboard');
  const [reloadKey, setReloadKey] = useState(0);
  const [distributors, setDistributors] = useState<{ id: number; name: string }[]>([]);
  const [form, setForm] = useState({ city: '', state: '0', distributor: '0', tenderRefNo: '', description: '' });

  const load = () => {
    apiGet<Project>(`/cloud-projects/${hash}`).then((p) => {
      setProject(p);
      setForm({ city: p.city || '', state: String(p.state || '0'), distributor: String(p.distributor || '0'), tenderRefNo: p.tender_ref_no || '', description: p.description || '' });
    }).catch(() => setProject(null));
  };
  useEffect(() => { load(); /* eslint-disable-next-line */ }, [hash]);
  useEffect(() => { apiGet<{ id: number; name: string }[]>('/distributors').then(setDistributors).catch(() => setDistributors([])); }, []);

  // Scroll-spy for section nav
  useEffect(() => {
    const obs = new IntersectionObserver(
      (entries) => {
        const vis = entries.filter((e) => e.isIntersecting).sort((a, b) => a.boundingClientRect.top - b.boundingClientRect.top);
        if (vis[0]) setActive(vis[0].target.id);
      },
      { rootMargin: '-72px 0px -60% 0px' }
    );
    SECTIONS.forEach((s) => { const el = document.getElementById(s.id); if (el) obs.observe(el); });
    return () => obs.disconnect();
  }, [project, reloadKey]);

  const saveEdit = async () => {
    if (!project) return;
    const res = await apiPut(`/cloud-projects/${project.id}`, {
      'edit-project-city': form.city, 'edit-project-state': form.state,
      'edit-project-distributor': form.distributor,
      'edit-tender-ref-no': form.tenderRefNo, 'edit-project-description': form.description,
    });
    res.status === 1 ? (toast.success(res.msg), setEdit(false), load()) : toast.error(res.msg);
  };

  if (!project) return <Layout title="Cloud Project"><Center py="xl"><Loader color="brand" /></Center></Layout>;
  const pid = project.id;
  const p = project as any;

  return (
    <Layout title={project.name}>
      {/* ── Breadcrumb ── */}
      <Anchor c="dimmed" fz="sm" fw={600} mb="sm" href="/cloud-projects"
        onClick={(e) => { e.preventDefault(); nav('/cloud-projects'); }}
        style={{ display: 'inline-flex', alignItems: 'center', gap: 6 }}>
        <BackIcon size={ICON.xs} /> Cloud Projects
      </Anchor>

      {/* ── Project hero ── */}
      <Paper radius="md" p="lg" mb="lg" style={{ background: 'var(--grad-header)' }}>
        <Group justify="space-between" align="center" wrap="wrap" gap="md">
          <Box style={{ minWidth: 0 }}>
            <Title order={2} c="#fff" fz={20} fw={700} lh={1.2}>{project.name}</Title>
            <Text c="rgba(255,255,255,.75)" fz="sm" mt={4}>
              {[project.city, project.state_name, project.distributor_name && `Distributor: ${project.distributor_name}`, project.tender_ref_no && `Tender: ${project.tender_ref_no}`, project.start_date && `Started ${fmtDate(project.start_date)}`].filter(Boolean).join(' · ')}
            </Text>
          </Box>
          <Group gap="sm" wrap="wrap">
            <Button variant="outline" color="white" leftSection={<EditIcon size={ICON.sm} />} onClick={() => setEdit(true)}>Edit</Button>
          </Group>
        </Group>
      </Paper>

      <Section id="dashboard" title="Project Dashboard" description="Quick view after project details" style={{ scrollMarginTop: 72 }}>
        <SimpleGrid cols={{ base: 2, sm: 3, lg: 6 }} spacing="sm">
          <MiniStat label="Active Items" value={p.item_count} tone="primary" />
          <MiniStat label="Bills" value={p.bill_count} />
          <MiniStat label="Total Portal (₹)" value={money(p.total_portal)} tone="primary" mono />
          <MiniStat label="Total Sales (₹)" value={money(p.total_sales)} mono />
          <MiniStat label="Total Purchase (₹)" value={money(p.total_purchase)} mono />
          <MiniStat label="Total Invoiced (₹)" value={money(p.total_invoiced)} tone="success" mono />
        </SimpleGrid>
      </Section>

      {/* ── Section sticky nav ── */}
      <nav className="flow-nav">
        {SECTIONS.map((s) => (
          <a key={s.id} href={`#${s.id}`} className={active === s.id ? 'active' : ''}
            onClick={(e) => { e.preventDefault(); document.getElementById(s.id)?.scrollIntoView({ behavior: 'smooth' }); }}>
            {s.label}
          </a>
        ))}
      </nav>

      {/* ── Flowing sections ── */}
      <div key={reloadKey}>
        <Section id="discounts" title="Project Discounts" description="Set RI / PAYG discount slabs by effective month" style={{ scrollMarginTop: 72 }}>
          <DiscountsTab projectId={pid} />
        </Section>

        <Section id="products" title="Headers & Products" description="Sales headers with their products — collapse any header to focus"
          actions={<Button variant="light" leftSection={<CloudUploadIcon size={ICON.sm} />} onClick={() => setShowImport(true)}>Import Consumption</Button>}
          style={{ scrollMarginTop: 72 }}>
          <ItemsTab projectId={pid} />
        </Section>

        <Section id="billing" title="Monthly Billing" description="Portal → Sales Headers → Purchase Headers → Invoices / Debit Note / Credit Note" style={{ scrollMarginTop: 72 }}>
          <MonthlyBillTab projectId={pid} onBillChange={() => load()} />
        </Section>

        <Section id="attachments" title="Attachments" style={{ scrollMarginTop: 72 }}>
          <AttachmentsTab projectId={pid} />
        </Section>
      </div>

      {showImport && (
        <ImportConsumption projectId={pid} onClose={() => setShowImport(false)}
          onDone={() => { setShowImport(false); load(); setReloadKey((k) => k + 1); }} />
      )}

      {edit && (
        <Modal title="Edit Project Details" size="lg" onClose={() => setEdit(false)}
          footer={<><Button variant="default" onClick={() => setEdit(false)}>Cancel</Button><Button onClick={saveEdit}>Save</Button></>}>
          <SimpleGrid cols={{ base: 1, sm: 2 }} spacing="md">
            <TextInput label="City" value={form.city} onChange={(e) => setForm({ ...form, city: e.currentTarget.value })} />
            <MSelect label="State" placeholder="Select State" searchable
              value={form.state === '0' ? null : form.state} onChange={(v) => setForm({ ...form, state: v || '0' })}
              data={lookups.states.map((s) => ({ value: String(s.value), label: s.name }))} />
            <MSelect label="Distributor" placeholder="Select Distributor" searchable clearable
              value={form.distributor === '0' ? null : form.distributor} onChange={(v) => setForm({ ...form, distributor: v || '0' })}
              data={distributors.map((d) => ({ value: String(d.id), label: d.name }))} />
            <TextInput label="Tender Reference No" style={{ gridColumn: '1 / -1' }} value={form.tenderRefNo}
              onChange={(e) => setForm({ ...form, tenderRefNo: e.currentTarget.value })} />
            <Textarea label="Description" autosize minRows={2} style={{ gridColumn: '1 / -1' }} value={form.description}
              onChange={(e) => setForm({ ...form, description: e.currentTarget.value })} />
          </SimpleGrid>
        </Modal>
      )}
    </Layout>
  );
}
