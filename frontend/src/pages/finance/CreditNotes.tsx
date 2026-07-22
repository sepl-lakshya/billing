import { Card, Button, Group } from '@mantine/core';
import Layout from '../../components/Layout';
import DataTable, { Column } from '../../components/DataTable';
import { PageHeader } from '../../components/ui';
import { DeleteIcon, ICON } from '../../lib/icons';
import { useFetch } from '../../hooks/useFetch';
import { apiDelete } from '../../api/client';
import { useToast } from '../../state/ToastContext';
import { useConfirm } from '../../state/ConfirmContext';
import { money, fmtDate } from '../../lib/format';

interface CreditNote {
  id: number; project_id: number; project_name?: string; invoice_id: number; invoice_number?: string;
  debit_note_id: number; reference_no: string; amount: number; remark: string; created_on: string;
}

export default function CreditNotes() {
  const { data, loading, reload } = useFetch<CreditNote[]>('/notes/credit', []);
  const toast = useToast();
  const confirm = useConfirm();

  const remove = async (c: CreditNote) => {
    if (await confirm({ message: 'Delete this credit note?', danger: true, confirmLabel: 'Delete' })) {
      const res = await apiDelete(`/notes/credit/${c.id}`);
      res.status === 1 ? (toast.success(res.msg), reload()) : toast.error(res.msg);
    }
  };

  const columns: Column<CreditNote>[] = [
    { header: '#', cell: (_r, i) => i + 1 },
    { header: 'Project', sortable: true, sortValue: (r) => r.project_name || `#${r.project_id}`, cell: (r) => r.project_name || `#${r.project_id}` },
    { header: 'Invoice', sortable: true, sortValue: (r) => r.invoice_number || '', cell: (r) => r.invoice_number || '-' },
    { header: 'Reference No', sortable: true, sortValue: (r) => r.reference_no || '', cell: (r) => <strong>{r.reference_no}</strong> },
    { header: 'Amount', className: 'num', sortable: true, sortValue: (r) => Number(r.amount) || 0, cell: (r) => money(r.amount) },
    { header: 'Remark', sortable: true, sortValue: (r) => r.remark || '', cell: (r) => r.remark || '-' },
    { header: 'Date', sortable: true, sortValue: (r) => new Date(r.created_on).getTime() || 0, cell: (r) => fmtDate(r.created_on) },
    { header: '', className: 'actions', cell: (r) => <Group justify="flex-end"><Button size="xs" variant="light" color="red" leftSection={<DeleteIcon size={ICON.xs} />} onClick={() => remove(r)}>Delete</Button></Group> },
  ];

  return (
    <Layout title="Credit Notes">
      <PageHeader title="Credit Notes" subtitle="Received against debit notes (create within a project bill)" />
      <Card withBorder shadow="sm" radius="lg" p={0}>
        <DataTable columns={columns} rows={data} loading={loading} keyField={(r) => r.id} searchable searchPlaceholder="Search credit notes…" pageSize={12} />
      </Card>
    </Layout>
  );
}
