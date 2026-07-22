import { useState } from 'react';
import Modal from '../../../components/Modal';
import DataTable, { Column } from '../../../components/DataTable';
import { Card, Group, Button, Badge, NumberInput, SimpleGrid, Select as MSelect } from '@mantine/core';
import { AddIcon, EditIcon, ICON } from '../../../lib/icons';
import { useFetch } from '../../../hooks/useFetch';
import { apiPost, apiPut } from '../../../api/client';
import { useToast } from '../../../state/ToastContext';
import { useLookups } from '../../../state/LookupContext';
import { money, fmtDate } from '../../../lib/format';

// ---------------- Discounts ----------------
export function DiscountsTab({ projectId }: { projectId: number }) {
  const { data, loading, reload } = useFetch<any[]>(`/cloud-projects/${projectId}/discounts`, []);
  const { lookups } = useLookups();
  const toast = useToast();
  const [addOpen, setAddOpen] = useState(false);
  const [updOpen, setUpdOpen] = useState(false);
  const [addF, setAddF] = useState({ riDiscount: '', paygDiscount: '', creditDays: '' });
  const [updF, setUpdF] = useState({ riDiscount: '', paygDiscount: '', creditDays: '', month: '0', year: '0' });

  const add = async () => {
    const res = await apiPost(`/cloud-projects/${projectId}/discounts`, {
      'add-ri-discount': addF.riDiscount, 'add-payg-discount': addF.paygDiscount, 'add-credit-days': addF.creditDays,
    });
    res.status === 1 ? (toast.success(res.msg), setAddOpen(false), reload()) : toast.error(res.msg);
  };
  const upd = async () => {
    const res = await apiPut(`/cloud-projects/${projectId}/discounts`, {
      'change-ri-discount': updF.riDiscount, 'change-payg-discount': updF.paygDiscount,
      'change-credit-days': updF.creditDays, 'discount-month': updF.month, 'discount-year': updF.year,
    });
    res.status === 1 ? (toast.success(res.msg), setUpdOpen(false), reload()) : toast.error(res.msg);
  };

  const columns: Column<any>[] = [
    { header: 'From', cell: (r) => fmtDate(r.from_date) },
    { header: 'To', cell: (r) => (r.to_date ? fmtDate(r.to_date) : <Badge color="green" variant="light">Current</Badge>) },
    { header: 'RI %', className: 'num', cell: (r) => r.ri_discount },
    { header: 'PAYG %', className: 'num', cell: (r) => r.payg_discount },
    { header: 'Credit Days', className: 'num', cell: (r) => r.credit_days },
  ];

  return (
    <Card withBorder shadow="sm" radius="lg" p={0}>
      <Group justify="flex-end" px="md" py="xs" style={{ borderBottom: '1px solid var(--border)' }}>
        {data.length === 0
          ? <Button size="xs" leftSection={<AddIcon size={ICON.xs} />} onClick={() => { setAddF({ riDiscount: '', paygDiscount: '', creditDays: '' }); setAddOpen(true); }}>Add Discount</Button>
          : <Button size="xs" variant="light" leftSection={<EditIcon size={ICON.xs} />} onClick={() => { setUpdF({ riDiscount: '', paygDiscount: '', creditDays: '', month: '0', year: '0' }); setUpdOpen(true); }}>Change Discount</Button>}
      </Group>
      <DataTable columns={columns} rows={data} loading={loading} keyField={(r) => r.id} empty="No discount slabs set" />
      {addOpen && (
        <Modal title="Add Discount" onClose={() => setAddOpen(false)}
          footer={<><Button variant="default" onClick={() => setAddOpen(false)}>Cancel</Button><Button onClick={add}>Save</Button></>}>
          <SimpleGrid cols={{ base: 1, sm: 2 }} spacing="md">
            <NumberInput label="RI Discount (%)" placeholder="0" hideControls allowNegative={false} value={addF.riDiscount} onChange={(v) => setAddF({ ...addF, riDiscount: v === '' ? '' : String(v) })} />
            <NumberInput label="PAYG Discount (%)" placeholder="0" hideControls allowNegative={false} value={addF.paygDiscount} onChange={(v) => setAddF({ ...addF, paygDiscount: v === '' ? '' : String(v) })} />
            <NumberInput label="Credit Days" placeholder="0" hideControls allowNegative={false} style={{ gridColumn: '1 / -1' }} value={addF.creditDays} onChange={(v) => setAddF({ ...addF, creditDays: v === '' ? '' : String(v) })} />
          </SimpleGrid>
        </Modal>
      )}
      {updOpen && (
        <Modal title="Change Discount (from month)" onClose={() => setUpdOpen(false)}
          footer={<><Button variant="default" onClick={() => setUpdOpen(false)}>Cancel</Button><Button onClick={upd}>Save</Button></>}>
          <SimpleGrid cols={{ base: 1, sm: 2 }} spacing="md">
            <NumberInput label="RI Discount (%)" placeholder="0" hideControls allowNegative={false} value={updF.riDiscount} onChange={(v) => setUpdF({ ...updF, riDiscount: v === '' ? '' : String(v) })} />
            <NumberInput label="PAYG Discount (%)" placeholder="0" hideControls allowNegative={false} value={updF.paygDiscount} onChange={(v) => setUpdF({ ...updF, paygDiscount: v === '' ? '' : String(v) })} />
            <NumberInput label="Credit Days" placeholder="0" hideControls allowNegative={false} value={updF.creditDays} onChange={(v) => setUpdF({ ...updF, creditDays: v === '' ? '' : String(v) })} />
            <MSelect label="Effective Month" placeholder="Month" value={updF.month === '0' ? null : updF.month} onChange={(v) => setUpdF({ ...updF, month: v || '0' })} data={lookups.months.map((m) => ({ value: String(m.value), label: m.name }))} />
            <MSelect label="Effective Year" placeholder="Year" style={{ gridColumn: '1 / -1' }} value={updF.year === '0' ? null : updF.year} onChange={(v) => setUpdF({ ...updF, year: v || '0' })} data={lookups.years.map((y) => ({ value: String(y.value), label: y.name }))} />
          </SimpleGrid>
        </Modal>
      )}
    </Card>
  );
}
