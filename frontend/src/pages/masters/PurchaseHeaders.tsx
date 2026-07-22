import { useEffect, useState } from 'react';
import { Card, Button, Group, Badge, TextInput, Select, SimpleGrid } from '@mantine/core';
import { AddIcon, DeleteIcon, ICON } from '../../lib/icons';
import Layout from '../../components/Layout';
import Modal from '../../components/Modal';
import DataTable, { Column } from '../../components/DataTable';
import { PageHeader } from '../../components/ui';
import { useFetch } from '../../hooks/useFetch';
import { apiGet, apiPost, apiDelete } from '../../api/client';
import { useToast } from '../../state/ToastContext';
import { useConfirm } from '../../state/ConfirmContext';
import { PurchaseHeader, Project } from '../../api/types';
import { fmtDate } from '../../lib/format';

export default function PurchaseHeaders() {
  const { data, loading, reload } = useFetch<PurchaseHeader[]>('/purchase-headers', []);
  const toast = useToast();
  const confirm = useConfirm();
  const [open, setOpen] = useState(false);
  const [projects, setProjects] = useState<Project[]>([]);
  const [form, setForm] = useState({ name: '', projectId: '0' });

  useEffect(() => { apiGet<Project[]>('/cloud-projects').then(setProjects).catch(() => setProjects([])); }, []);

  const save = async () => {
    const res = await apiPost('/purchase-headers', {
      'purchase-header-name': form.name, 'purchase-header-project-id': form.projectId,
    });
    if (res.status === 1) { toast.success(res.msg); setOpen(false); reload(); }
    else toast.error(res.msg);
  };

  const remove = async (h: PurchaseHeader) => {
    if (await confirm({ message: `Delete purchase header "${h.name}"?`, danger: true, confirmLabel: 'Delete' })) {
      const res = await apiDelete(`/purchase-headers/${h.id}`);
      res.status === 1 ? (toast.success(res.msg), reload()) : toast.error(res.msg);
    }
  };

  const columns: Column<PurchaseHeader>[] = [
    { header: '#', cell: (_r, i) => i + 1 },
    { header: 'Name', sortable: true, sortValue: (r) => r.name || '', cell: (r) => <strong>{r.name}</strong> },
    { header: 'Project', sortable: true, sortValue: (r) => (r.project_id === 0 ? 'Global' : r.project_name || `#${r.project_id}`), cell: (r) => (r.project_id === 0 ? <Badge variant="light" color="brand">Global</Badge> : r.project_name || `#${r.project_id}`) },
    { header: 'Created', sortable: true, sortValue: (r) => new Date(r.created_on).getTime() || 0, cell: (r) => fmtDate(r.created_on) },
    { header: '', className: 'actions', cell: (r) => <Group justify="flex-end"><Button size="xs" variant="light" color="red" leftSection={<DeleteIcon size={ICON.xs} />} onClick={() => remove(r)}>Delete</Button></Group> },
  ];

  return (
    <Layout title="Purchase Header">
      <PageHeader title="Purchase Headers" subtitle="Global or project-scoped purchase headers"
        actions={<Button leftSection={<AddIcon size={ICON.sm} />} onClick={() => { setForm({ name: '', projectId: '0' }); setOpen(true); }}>Add Header</Button>} />
      <Card withBorder shadow="sm" radius="lg" p={0}>
        <DataTable columns={columns} rows={data} loading={loading} keyField={(r) => r.id} minWidth={560} searchable searchPlaceholder="Search headers…" pageSize={12} />
      </Card>

      {open && (
        <Modal title="Add Purchase Header" onClose={() => setOpen(false)}
          footer={<>
            <Button variant="default" onClick={() => setOpen(false)}>Cancel</Button>
            <Button onClick={save}>Save</Button>
          </>}>
          <SimpleGrid cols={{ base: 1, sm: 2 }} spacing="md">
            <TextInput label="Header Name" value={form.name} onChange={(e) => setForm({ ...form, name: e.currentTarget.value })} />
            <Select label="Project" placeholder="Global (all projects)" searchable clearable
              value={form.projectId === '0' ? null : form.projectId}
              onChange={(v) => setForm({ ...form, projectId: v || '0' })}
              data={projects.map((p) => ({ value: String(p.id), label: p.name }))} />
          </SimpleGrid>
        </Modal>
      )}
    </Layout>
  );
}
