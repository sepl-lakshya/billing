import { useEffect, useState } from 'react';
import { Card, Button, Group, Badge, TextInput, NumberInput, Select, Textarea, SimpleGrid } from '@mantine/core';
import Layout from '../../components/Layout';
import Modal from '../../components/Modal';
import DataTable, { Column } from '../../components/DataTable';
import { PageHeader } from '../../components/ui';
import { AddIcon, DeleteIcon, ICON } from '../../lib/icons';
import { useFetch } from '../../hooks/useFetch';
import { apiGet, apiPost, apiDelete } from '../../api/client';
import { useToast } from '../../state/ToastContext';
import { useConfirm } from '../../state/ConfirmContext';
import { useLookups } from '../../state/LookupContext';
import { Invoice, Project } from '../../api/types';
import { money, fmtDate } from '../../lib/format';

export default function Invoices() {
  const { data, loading, reload } = useFetch<Invoice[]>('/invoices', []);
  const { lookups } = useLookups();
  const toast = useToast();
  const confirm = useConfirm();
  const [open, setOpen] = useState(false);
  const [projects, setProjects] = useState<Project[]>([]);
  const [form, setForm] = useState({ projectId: '0', referenceNo: '', amount: '', gstSlab: '0', date: '', type: '1', description: '' });

  useEffect(() => {
    apiGet<Project[]>('/cloud-projects').then(setProjects).catch(() => setProjects([]));
  }, []);

  const save = async () => {
    const res = await apiPost('/invoices', {
      'add-invoice-project-id': form.projectId,
      'add-invoice-reference-no': form.referenceNo,
      'add-invoice-amount': form.amount,
      'add-invoice-gst-slab': form.gstSlab,
      'add-invoice-date': form.date,
      'add-invoice-type': form.type,
      'add-invoice-desc': form.description,
    });
    if (res.status === 1) { toast.success(res.msg); setOpen(false); reload(); }
    else toast.error(res.msg);
  };

  const remove = async (inv: Invoice) => {
    if (await confirm({ message: `Delete invoice "${inv.number}"? This also removes its debit/credit notes.`, danger: true, confirmLabel: 'Delete' })) {
      const res = await apiDelete(`/invoices/${inv.id}`);
      res.status === 1 ? (toast.success(res.msg), reload()) : toast.error(res.msg);
    }
  };

  const columns: Column<Invoice>[] = [
    { header: '#', cell: (_r, i) => i + 1 },
    { header: 'Invoice No', sortable: true, sortValue: (r) => r.number || '', cell: (r) => <strong>{r.number}</strong> },
    { header: 'Project', sortable: true, sortValue: (r) => r.project_name || `#${r.project_id}`, cell: (r) => r.project_name || `#${r.project_id}` },
    { header: 'Amount', className: 'num', sortable: true, sortValue: (r) => Number(r.amount) || 0, cell: (r) => money(r.amount) },
    { header: 'GST', sortable: true, sortValue: (r) => Number(r.gst_slab) || 0, cell: (r) => r.gst_name || `${r.gst_slab}%` },
    { header: 'Date', sortable: true, sortValue: (r) => new Date(r.invoice_date).getTime() || 0, cell: (r) => fmtDate(r.invoice_date) },
    { header: 'Tagged', sortable: true, sortValue: (r) => (r.bill_id ? 1 : 0), cell: (r) => (r.bill_id ? <Badge color="green" variant="light">Tagged</Badge> : <Badge color="gray" variant="light">Free</Badge>) },
    { header: '', className: 'actions', cell: (r) => <Group justify="flex-end"><Button size="xs" variant="light" color="red" leftSection={<DeleteIcon size={ICON.xs} />} onClick={() => remove(r)}>Delete</Button></Group> },
  ];

  return (
    <Layout title="Invoices">
      <PageHeader
        title="Invoices"
        subtitle="Purchase invoices across all projects"
        actions={<Button leftSection={<AddIcon size={ICON.sm} />} onClick={() => { setForm({ projectId: '0', referenceNo: '', amount: '', gstSlab: '0', date: '', type: '1', description: '' }); setOpen(true); }}>Add Invoice</Button>}
      />
      <Card withBorder shadow="sm" radius="lg" p={0}>
        <DataTable columns={columns} rows={data} loading={loading} keyField={(r) => r.id} searchable searchPlaceholder="Search invoices…" pageSize={12} />
      </Card>

      {open && (
        <Modal title="Add Invoice" size="lg" onClose={() => setOpen(false)}
          footer={<>
            <Button variant="default" onClick={() => setOpen(false)}>Cancel</Button>
            <Button onClick={save}>Save Invoice</Button>
          </>}>
          <SimpleGrid cols={{ base: 1, sm: 2 }} spacing="md">
            <Select label="Project" placeholder="Select project" searchable
              value={form.projectId === '0' ? null : form.projectId}
              onChange={(v) => setForm({ ...form, projectId: v || '0' })}
              data={projects.map((p) => ({ value: String(p.id), label: p.name }))} />
            <TextInput label="Invoice Reference No" placeholder="Enter reference no" value={form.referenceNo} onChange={(e) => setForm({ ...form, referenceNo: e.currentTarget.value })} />
            <NumberInput label="Amount" placeholder="Enter amount" hideControls allowNegative={false} value={form.amount} onChange={(v) => setForm({ ...form, amount: v === '' ? '' : String(v) })} />
            <Select label="GST Slab" placeholder="Select GST" searchable
              value={form.gstSlab === '0' ? null : form.gstSlab}
              onChange={(v) => setForm({ ...form, gstSlab: v || '0' })}
              data={lookups.gstSlabs.map((g) => ({ value: String(g.percentage), label: g.name }))} />
            <TextInput label="Invoice Date" type="date" value={form.date} onChange={(e) => setForm({ ...form, date: e.currentTarget.value })} />
            <Select label="Invoice Type"
              value={form.type}
              onChange={(v) => setForm({ ...form, type: v || '1' })}
              data={[{ value: '1', label: 'Purchase' }, { value: '2', label: 'Service' }, { value: '3', label: 'Other' }]} />
            <Textarea label="Description" placeholder="Optional notes" autosize minRows={2} style={{ gridColumn: '1 / -1' }} value={form.description} onChange={(e) => setForm({ ...form, description: e.currentTarget.value })} />
          </SimpleGrid>
        </Modal>
      )}
    </Layout>
  );
}
