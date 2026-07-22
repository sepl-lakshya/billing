import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { Card, Button, Group, Anchor, Text, TextInput, Textarea, Select, SimpleGrid } from '@mantine/core';
import { AddIcon, OpenIcon, DeleteIcon, ICON } from '../../lib/icons';
import Layout from '../../components/Layout';
import Modal from '../../components/Modal';
import DataTable, { Column } from '../../components/DataTable';
import { PageHeader } from '../../components/ui';
import { useFetch } from '../../hooks/useFetch';
import { apiPost, apiDelete } from '../../api/client';
import { useToast } from '../../state/ToastContext';
import { useConfirm } from '../../state/ConfirmContext';
import { useLookups } from '../../state/LookupContext';
import { Project } from '../../api/types';
import { fmtDate } from '../../lib/format';

export default function CloudProjects() {
  const { data, loading, reload } = useFetch<Project[]>('/cloud-projects', []);
  const { lookups } = useLookups();
  const toast = useToast();
  const confirm = useConfirm();
  const nav = useNavigate();
  const [open, setOpen] = useState(false);
  const [form, setForm] = useState({ name: '', city: '', state: '0', tenderRefNo: '', startDate: '', description: '' });

  const save = async () => {
    const res = await apiPost('/cloud-projects', {
      'project-name': form.name, 'project-city': form.city, 'project-state': form.state,
      'tender-ref-no': form.tenderRefNo, 'project-start-date': form.startDate,
      'product-category': 1, 'project-description': form.description,
    });
    if (res.status === 1) { toast.success(res.msg); setOpen(false); reload(); }
    else toast.error(res.msg);
  };

  const remove = async (p: Project) => {
    if (await confirm({ message: `Delete project "${p.name}"? This removes ALL its items, bills and notes.`, danger: true, confirmLabel: 'Delete' })) {
      const res = await apiDelete(`/cloud-projects/${p.id}`);
      res.status === 1 ? (toast.success(res.msg), reload()) : toast.error(res.msg);
    }
  };

  const columns: Column<Project>[] = [
    { header: '#', cell: (_r, i) => i + 1 },
    { header: 'Project', sortable: true, sortValue: (r) => r.name || '', cell: (r) => <Anchor fw={600} onClick={() => nav(`/cloud-projects/${r.hash}`)}>{r.name}</Anchor> },
    { header: 'City', sortable: true, sortValue: (r) => r.city || '', cell: (r) => r.city || '-' },
    { header: 'State', sortable: true, sortValue: (r) => r.state_name || '', cell: (r) => r.state_name || '-' },
    { header: 'Tender Ref', sortable: true, sortValue: (r) => r.tender_ref_no || '', cell: (r) => r.tender_ref_no || '-' },
    { header: 'Start', sortable: true, sortValue: (r) => new Date(r.start_date).getTime() || 0, cell: (r) => fmtDate(r.start_date) },
    { header: 'Created By', sortable: true, sortValue: (r) => r.created_by_name || '', cell: (r) => r.created_by_name || '-' },
    {
      header: '', className: 'actions',
      cell: (r) => (
        <Group gap={6} wrap="nowrap" justify="flex-end">
          <Button size="xs" variant="light" leftSection={<OpenIcon size={ICON.xs} />} onClick={() => nav(`/cloud-projects/${r.hash}`)}>Open</Button>
          <Button size="xs" variant="light" color="red" leftSection={<DeleteIcon size={ICON.xs} />} onClick={() => remove(r)}>Delete</Button>
        </Group>
      ),
    },
  ];

  return (
    <Layout title="Cloud Projects">
      <PageHeader
        title="Cloud Projects"
        subtitle="Manage cloud billing projects"
        actions={
          <Button leftSection={<AddIcon size={ICON.sm} />} onClick={() => { setForm({ name: '', city: '', state: '0', tenderRefNo: '', startDate: '', description: '' }); setOpen(true); }}>
            New Project
          </Button>
        }
      />
      <Card withBorder shadow="sm" radius="lg" p={0}>
        <DataTable columns={columns} rows={data} loading={loading} keyField={(r) => r.id} searchable searchPlaceholder="Search projects…" pageSize={12} />
      </Card>

      {open && (
        <Modal title="Create Cloud Project" size="lg" onClose={() => setOpen(false)}
          footer={<>
            <Button variant="default" onClick={() => setOpen(false)}>Cancel</Button>
            <Button onClick={save}>Create Project</Button>
          </>}>
          <SimpleGrid cols={{ base: 1, sm: 2 }} spacing="md">
            <TextInput label="Project Name" required style={{ gridColumn: '1 / -1' }} value={form.name} onChange={(e) => setForm({ ...form, name: e.currentTarget.value })} />
            <TextInput label="City" value={form.city} onChange={(e) => setForm({ ...form, city: e.currentTarget.value })} />
            <Select label="State" placeholder="Select State" searchable
              value={form.state === '0' ? null : form.state}
              onChange={(v) => setForm({ ...form, state: v || '0' })}
              data={lookups.states.map((s) => ({ value: String(s.value), label: s.name }))} />
            <TextInput label="Tender Reference No" value={form.tenderRefNo} onChange={(e) => setForm({ ...form, tenderRefNo: e.currentTarget.value })} />
            <TextInput label="Start Date" type="date" value={form.startDate} onChange={(e) => setForm({ ...form, startDate: e.currentTarget.value })} />
            <Textarea label="Description" autosize minRows={2} style={{ gridColumn: '1 / -1' }} value={form.description} onChange={(e) => setForm({ ...form, description: e.currentTarget.value })} />
          </SimpleGrid>
        </Modal>
      )}
    </Layout>
  );
}
