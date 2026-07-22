import { useState } from 'react';
import { Card, Button, TextInput, Group } from '@mantine/core';
import { AddIcon, DeleteIcon, ICON } from '../../lib/icons';
import Layout from '../../components/Layout';
import Modal from '../../components/Modal';
import DataTable, { Column } from '../../components/DataTable';
import { PageHeader } from '../../components/ui';
import { useFetch } from '../../hooks/useFetch';
import { apiPost, apiDelete } from '../../api/client';
import { useToast } from '../../state/ToastContext';
import { useConfirm } from '../../state/ConfirmContext';
import { Named } from '../../api/types';
import { fmtDate } from '../../lib/format';

interface Props {
  title: string;
  subtitle: string;
  endpoint: string;   // e.g. '/oem'
  fieldName: string;  // legacy POST field, e.g. 'oem-name'
  entity: string;     // e.g. 'OEM'
}

/** Reusable page for name-only master entities (OEM, Distributors). */
export default function SimpleMaster({ title, subtitle, endpoint, fieldName, entity }: Props) {
  const { data, loading, reload } = useFetch<Named[]>(endpoint, []);
  const toast = useToast();
  const confirm = useConfirm();
  const [open, setOpen] = useState(false);
  const [name, setName] = useState('');

  const save = async () => {
    const res = await apiPost(endpoint, { [fieldName]: name });
    if (res.status === 1) { toast.success(res.msg); setOpen(false); setName(''); reload(); }
    else toast.error(res.msg);
  };

  const remove = async (row: Named) => {
    if (await confirm({ message: `Delete ${entity} "${row.name}"?`, danger: true, confirmLabel: 'Delete' })) {
      const res = await apiDelete(`${endpoint}/${row.id}`);
      res.status === 1 ? (toast.success(res.msg), reload()) : toast.error(res.msg);
    }
  };

  const columns: Column<Named>[] = [
    { header: '#', cell: (_r, i) => i + 1 },
    { header: 'Name', sortable: true, sortValue: (r) => r.name || '', cell: (r) => <strong>{r.name}</strong> },
    { header: 'Created', sortable: true, sortValue: (r) => new Date(r.created_on || 0).getTime() || 0, cell: (r) => fmtDate(r.created_on) },
    { header: '', className: 'actions', cell: (r) => <Group justify="flex-end"><Button size="xs" variant="light" color="red" leftSection={<DeleteIcon size={ICON.xs} />} onClick={() => remove(r)}>Delete</Button></Group> },
  ];

  return (
    <Layout title={title}>
      <PageHeader title={title} subtitle={subtitle}
        actions={<Button leftSection={<AddIcon size={ICON.sm} />} onClick={() => { setName(''); setOpen(true); }}>Add {entity}</Button>} />
      <Card withBorder shadow="sm" radius="lg" p={0}>
        <DataTable columns={columns} rows={data} loading={loading} keyField={(r) => r.id} minWidth={480} searchable pageSize={12} />
      </Card>

      {open && (
        <Modal
          title={`Add ${entity}`}
          onClose={() => setOpen(false)}
          footer={<>
            <Button variant="default" onClick={() => setOpen(false)}>Cancel</Button>
            <Button onClick={save}>Save</Button>
          </>}
        >
          <TextInput label={`${entity} Name`} data-autofocus value={name} onChange={(e) => setName(e.currentTarget.value)}
            onKeyDown={(e) => e.key === 'Enter' && save()} placeholder={`Enter ${entity} name`} />
        </Modal>
      )}
    </Layout>
  );
}
