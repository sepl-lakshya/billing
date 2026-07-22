import { useState } from 'react';
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
import { useLookups } from '../../state/LookupContext';
import { Product, Named } from '../../api/types';
import { fmtDate } from '../../lib/format';

export default function Products() {
  const { data: products, loading, reload } = useFetch<Product[]>('/products', []);
  const { lookups } = useLookups();
  const toast = useToast();
  const confirm = useConfirm();
  const [open, setOpen] = useState(false);
  const [oems, setOems] = useState<Named[]>([]);
  const [form, setForm] = useState({ name: '', oem: '0', cloudCategory: '0' });

  const openModal = async () => {
    setForm({ name: '', oem: '0', cloudCategory: '0' });
    setOems(await apiGet<Named[]>('/oem'));
    setOpen(true);
  };

  const save = async () => {
    const res = await apiPost('/products', {
      'product-name': form.name,
      'product-oem': form.oem,
      'cloud-category': form.cloudCategory,
    });
    if (res.status === 1) { toast.success(res.msg); setOpen(false); reload(); }
    else toast.error(res.msg);
  };

  const remove = async (p: Product) => {
    if (await confirm({ message: `Delete product "${p.name}"?`, danger: true, confirmLabel: 'Delete' })) {
      const res = await apiDelete(`/products/${p.id}`);
      res.status === 1 ? (toast.success(res.msg), reload()) : toast.error(res.msg);
    }
  };

  const columns: Column<Product>[] = [
    { header: '#', cell: (_r, i) => i + 1 },
    { header: 'Name', sortable: true, sortValue: (r) => r.name || '', cell: (r) => <strong>{r.name}</strong> },
    { header: 'OEM', sortable: true, sortValue: (r) => r.oem_name || '', cell: (r) => r.oem_name || '-' },
    { header: 'Category', sortable: true, sortValue: (r) => r.sub_category_name || '', cell: (r) => <Badge variant="light" color="brand">{r.sub_category_name || 'Cloud'}</Badge> },
    { header: 'Created', sortable: true, sortValue: (r) => new Date(r.created_on).getTime() || 0, cell: (r) => fmtDate(r.created_on) },
    {
      header: '', className: 'actions',
      cell: (r) => <Group justify="flex-end"><Button size="xs" variant="light" color="red" leftSection={<DeleteIcon size={ICON.xs} />} onClick={() => remove(r)}>Delete</Button></Group>,
    },
  ];

  return (
    <Layout title="Products">
      <PageHeader title="Products" subtitle="Master list of cloud products"
        actions={<Button leftSection={<AddIcon size={ICON.sm} />} onClick={openModal}>Add Product</Button>} />
      <Card withBorder shadow="sm" radius="lg" p={0}>
        <DataTable columns={columns} rows={products} loading={loading} keyField={(r) => r.id} searchable searchPlaceholder="Search products…" pageSize={12} />
      </Card>

      {open && (
        <Modal
          title="Add Product"
          onClose={() => setOpen(false)}
          footer={<>
            <Button variant="default" onClick={() => setOpen(false)}>Cancel</Button>
            <Button onClick={save}>Save Product</Button>
          </>}
        >
          <SimpleGrid cols={{ base: 1, sm: 2 }} spacing="md">
            <Select label="OEM" placeholder="Select OEM" searchable
              value={form.oem === '0' ? null : form.oem}
              onChange={(v) => setForm({ ...form, oem: v || '0' })}
              data={oems.map((o) => ({ value: String(o.id), label: o.name }))} />
            <Select label="Cloud Category" placeholder="Select Cloud Category" searchable
              value={form.cloudCategory === '0' ? null : form.cloudCategory}
              onChange={(v) => setForm({ ...form, cloudCategory: v || '0' })}
              data={lookups.cloudCategories.map((c) => ({ value: String(c.value), label: c.name }))} />
            <TextInput label="Product Name" style={{ gridColumn: '1 / -1' }} value={form.name} onChange={(e) => setForm({ ...form, name: e.currentTarget.value })} placeholder="Enter product name" />
          </SimpleGrid>
        </Modal>
      )}
    </Layout>
  );
}
