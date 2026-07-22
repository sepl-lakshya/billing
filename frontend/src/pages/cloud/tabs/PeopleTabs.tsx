import { useState } from 'react';
import Modal from '../../../components/Modal';
import DataTable, { Column } from '../../../components/DataTable';
import { IconButton } from '../../../components/ui';
import { Card, Group, Button, FileInput, TextInput, SimpleGrid, Anchor } from '@mantine/core';
import { UploadIcon, DeleteIcon, ICON } from '../../../lib/icons';
import { useFetch } from '../../../hooks/useFetch';
import { apiDelete, apiUpload } from '../../../api/client';
import { useToast } from '../../../state/ToastContext';
import { useConfirm } from '../../../state/ConfirmContext';
import { fmtDate } from '../../../lib/format';

// ---------------- Attachments ----------------
export function AttachmentsTab({ projectId }: { projectId: number }) {
  const { data, loading, reload } = useFetch<any[]>(`/cloud-projects/${projectId}/attachments`, []);
  const toast = useToast();
  const confirm = useConfirm();
  const [open, setOpen] = useState(false);
  const [title, setTitle] = useState('');
  const [file, setFile] = useState<File | null>(null);

  const upload = async () => {
    if (!file) { toast.error('Please choose a file'); return; }
    const form = new FormData();
    form.append('file-title', title);
    form.append('file', file);
    const res = await apiUpload(`/cloud-projects/${projectId}/attachments`, form);
    res.status === 1 ? (toast.success(res.msg), setOpen(false), setTitle(''), setFile(null), reload()) : toast.error(res.msg);
  };
  const remove = async (r: any) => {
    if (await confirm({ message: `Delete "${r.title || r.original_name}"?`, danger: true, confirmLabel: 'Delete' })) {
      const res = await apiDelete(`/cloud-projects/${projectId}/attachments/${r.id}`);
      res.status === 1 ? (toast.success(res.msg), reload()) : toast.error(res.msg);
    }
  };
  const columns: Column<any>[] = [
    { header: 'Title', cell: (r) => <strong>{r.title || '-'}</strong> },
    { header: 'File', cell: (r) => <Anchor href={`/uploads/attachment/${r.file_name}`} target="_blank" rel="noreferrer" fz="sm">{r.original_name}</Anchor> },
    { header: 'Uploaded', cell: (r) => fmtDate(r.created_on) },
    { header: '', className: 'actions', cell: (r) => <Group justify="flex-end"><IconButton label="Delete" color="red" icon={<DeleteIcon size={ICON.sm} />} onClick={() => remove(r)} /></Group> },
  ];
  return (
    <Card withBorder shadow="sm" radius="lg" p={0}>
      <Group justify="flex-end" px="md" py="xs" style={{ borderBottom: '1px solid var(--border)' }}>
        <Button size="xs" leftSection={<UploadIcon size={ICON.xs} />} onClick={() => { setTitle(''); setFile(null); setOpen(true); }}>Upload File</Button>
      </Group>
      <DataTable columns={columns} rows={data} loading={loading} keyField={(r) => r.id} empty="No attachments yet" />
      {open && (
        <Modal title="Upload Attachment" onClose={() => setOpen(false)}
          footer={<><Button variant="default" onClick={() => setOpen(false)}>Cancel</Button><Button onClick={upload}>Upload</Button></>}>
          <SimpleGrid cols={1} spacing="md">
            <TextInput label="Title" placeholder="e.g. Signed agreement" value={title} onChange={(e) => setTitle(e.currentTarget.value)} />
            <FileInput label="File" placeholder="Choose a file" value={file} onChange={setFile} clearable />
          </SimpleGrid>
        </Modal>
      )}
    </Card>
  );
}
