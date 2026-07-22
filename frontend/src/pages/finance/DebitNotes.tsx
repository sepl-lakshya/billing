import { Card, Button, Group, Badge } from '@mantine/core';
import Layout from '../../components/Layout';
import DataTable, { Column } from '../../components/DataTable';
import { PageHeader } from '../../components/ui';
import { DeleteIcon, ICON } from '../../lib/icons';
import { useFetch } from '../../hooks/useFetch';
import { apiDelete } from '../../api/client';
import { useToast } from '../../state/ToastContext';
import { useConfirm } from '../../state/ConfirmContext';
import { money, fmtDate } from '../../lib/format';

interface DebitNote {
  id: number; project_id: number; project_name?: string; invoice_id: number; invoice_number?: string;
  type: number; type_name?: string; credit_type: number; credit_type_name?: string;
  amount: number; cn_val: number; remark: string; created_on: string;
}

export default function DebitNotes() {
  const { data, loading, reload } = useFetch<DebitNote[]>('/notes/debit', []);
  const toast = useToast();
  const confirm = useConfirm();

  const remove = async (d: DebitNote) => {
    if (await confirm({ message: 'Delete this debit note and its credit notes?', danger: true, confirmLabel: 'Delete' })) {
      const res = await apiDelete(`/notes/debit/${d.id}`);
      res.status === 1 ? (toast.success(res.msg), reload()) : toast.error(res.msg);
    }
  };

  const columns: Column<DebitNote>[] = [
    { header: '#', cell: (_r, i) => i + 1 },
    { header: 'Project', sortable: true, sortValue: (r) => r.project_name || `#${r.project_id}`, cell: (r) => r.project_name || `#${r.project_id}` },
    { header: 'Invoice', sortable: true, sortValue: (r) => r.invoice_number || '', cell: (r) => r.invoice_number || '-' },
    { header: 'Type', sortable: true, sortValue: (r) => r.type_name || '', cell: (r) => r.type_name || '-' },
    { header: 'DN Type', sortable: true, sortValue: (r) => r.credit_type_name || '', cell: (r) => <Badge color="blue" variant="light">{r.credit_type_name}</Badge> },
    { header: 'Amount', className: 'num', sortable: true, sortValue: (r) => Number(r.amount) || 0, cell: (r) => money(r.amount) },
    { header: 'Credited', className: 'num', sortable: true, sortValue: (r) => Number(r.cn_val) || 0, cell: (r) => money(r.cn_val) },
    { header: 'Date', sortable: true, sortValue: (r) => new Date(r.created_on).getTime() || 0, cell: (r) => fmtDate(r.created_on) },
    { header: '', className: 'actions', cell: (r) => <Group justify="flex-end"><Button size="xs" variant="light" color="red" leftSection={<DeleteIcon size={ICON.xs} />} onClick={() => remove(r)}>Delete</Button></Group> },
  ];

  return (
    <Layout title="Debit Notes">
      <PageHeader title="Debit Notes" subtitle="Raised against purchase invoices (create within a project bill)" />
      <Card withBorder shadow="sm" radius="lg" p={0}>
        <DataTable columns={columns} rows={data} loading={loading} keyField={(r) => r.id} searchable searchPlaceholder="Search debit notes…" pageSize={12} />
      </Card>
    </Layout>
  );
}
