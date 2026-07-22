import { useEffect, useState, Fragment } from 'react';
import { Card, Group, Text, Button, Badge, NumberInput, TextInput, Textarea, Select as MSelect, SimpleGrid, Box, Paper } from '@mantine/core';
import Modal from '../../../../components/Modal';
import { MiniStat } from '../../../../components/ui';
import { AddIcon, CheckIcon, RefreshIcon, ICON } from '../../../../lib/icons';
import { apiGet, apiPost, apiDelete } from '../../../../api/client';
import { useToast } from '../../../../state/ToastContext';
import { useConfirm } from '../../../../state/ConfirmContext';
import { useLookups } from '../../../../state/LookupContext';
import { Invoice } from '../../../../api/types';
import { money, fmtDate } from '../../../../lib/format';

interface BaseProps {
  projectId: number;
  onClose: () => void;
  onSaved: () => void;
}

function DiscountBadge({ ri, payg }: { ri: number; payg: number }) {
  return (
    <Group justify="flex-end" gap="sm" mb="sm">
      <Badge variant="light" color="orange" size="lg" radius="sm">RI Discount {ri}%</Badge>
      <Badge variant="light" color="grape" size="lg" radius="sm">PAYG Discount {payg}%</Badge>
    </Group>
  );
}

function ModelBadge({ model }: { model: string }) {
  const cls = model === 'RI' ? 'chip-ri' : model === 'PAYG' ? 'chip-payg' : 'badge-gray';
  return <span className={`badge ${cls}`}>{model || '-'}</span>;
}

// =====================================================================
//  PORTAL PRICING (creates the bill on first save)
// =====================================================================
export function PortalModal({ projectId, billId, month, year, onClose, onSaved }: BaseProps & { billId: number | null; month: number; year: number }) {
  const toast = useToast();
  const [discount, setDiscount] = useState({ ri: 0, payg: 0 });
  const [rows, setRows] = useState<any[]>([]);
  const [prices, setPrices] = useState<Record<number, string>>({});
  const [loading, setLoading] = useState(true);
  const [readonly, setReadonly] = useState(false);

  useEffect(() => {
    const url = billId ? `/bills/${billId}/pricing` : `/bills/project/${projectId}/applicable/${month}/${year}`;
    apiGet<any>(url).then((d) => {
      setDiscount(d.discount || { ri: 0, payg: 0 });
      setRows(d.rows || []);
      const p: Record<number, string> = {};
      (d.rows || []).forEach((r: any) => { p[r.id] = String(r.portal_price ?? r.unit_price ?? ''); });
      setPrices(p);
      // Portal locked once a cloud inward exists (bill.status stays but CI blocks edits).
      setReadonly(!!(d.bill && d.bill.status >= 2));
      setLoading(false);
    });
  }, [projectId, billId, month, year]);

  const save = async () => {
    const ids = rows.map((r) => r.id);
    const vals = ids.map((id) => prices[id] ?? '0');
    const res = await apiPost('/bills/portal-price', {
      projectId, month, year, riDiscount: discount.ri, paygDiscount: discount.payg,
      projectItemIds: ids, portalPrices: vals,
    });
    res.status === 1 ? (toast.success(res.msg), onSaved()) : toast.error(res.msg);
  };

  return (
    <Modal title={`Portal Pricing`} wizard step={{ at: 2, of: 2 }} onClose={onClose}
      footer={<>
        <Button variant="subtle" color="gray" onClick={onClose}>Close</Button>
        {!readonly && <Button leftSection={<CheckIcon size={ICON.sm} />} onClick={save} disabled={loading || rows.length === 0}>Save Portal Prices</Button>}
      </>}>
      <DiscountBadge ri={discount.ri} payg={discount.payg} />
      {loading ? <Text c="dimmed" py="md">Loading…</Text> : (
        <div className="table-wrap">
          <table className="data">
            <thead><tr>
              <th>#</th><th>Product</th><th>Type</th><th>Model</th><th>Header</th>
              <th>Resource ID</th><th>Deployed On</th><th>Status</th><th className="num">Qty</th>
              <th className="num">Portal Price (₹)</th>
            </tr></thead>
            <tbody>
              {rows.map((r, i) => (
                <tr key={r.id}>
                  <td data-label="#">{i + 1}</td>
                  <td data-label="Product"><strong>{r.product_name}</strong></td>
                  <td data-label="Type">{r.category_name || '-'}</td>
                  <td data-label="Model"><ModelBadge model={r.model} /></td>
                  <td data-label="Header">{r.header_name || '-'}</td>
                  <td data-label="Resource ID">{r.resource_id || '-'}</td>
                  <td data-label="Deployed On">{fmtDate(r.deployment_start)}</td>
                  <td data-label="Status">{r.status === 1 ? <Badge color="green" variant="light" size="sm">Active</Badge> : <Badge color="red" variant="light" size="sm">{r.deployment_end ? fmtDate(r.deployment_end) : 'Inactive'}</Badge>}</td>
                  <td data-label="Qty" className="num">{r.quantity}</td>
                  <td data-label="Portal Price" className="num">
                    {readonly ? money(prices[r.id]) : (
                      <NumberInput size="xs" hideControls w={120} styles={{ input: { textAlign: 'right' } }} value={prices[r.id] ?? ''}
                        onChange={(v) => setPrices({ ...prices, [r.id]: v === '' ? '' : String(v) })} />
                    )}
                  </td>
                </tr>
              ))}
              {rows.length === 0 && <tr><td colSpan={10}><Text c="dimmed" ta="center" py="sm">No items active in this month.</Text></td></tr>}
            </tbody>
          </table>
        </div>
      )}
    </Modal>
  );
}

// =====================================================================
//  SALES PRICING — one amount per project header (bar), like the old portal.
//  Items are listed read-only under each header with portal + est. purchase.
// =====================================================================
export function SalesModal({ projectId, billId, onClose, onSaved }: BaseProps & { billId: number }) {
  const toast = useToast();
  const [data, setData] = useState<any>(null);
  const [headerAmts, setHeaderAmts] = useState<Record<number, string>>({});

  const load = () => apiGet<any>(`/bills/${billId}/pricing`).then((d) => {
    setData(d);
    const h: Record<number, string> = {};
    (d.headers || []).forEach((x: any) => { h[x.id] = x.amount == null ? '' : String(x.amount); });
    setHeaderAmts(h);
  });
  useEffect(() => { load(); /* eslint-disable-next-line */ }, [billId]);

  const save = async () => {
    if (!data) return;
    const hIds = (data.headers || []).map((h: any) => h.id);
    const hVals = hIds.map((id: number) => headerAmts[id] ?? '0');
    const res = await apiPost('/bills/sales-price', {
      projectId, billId, projectItemIds: [], salesPrices: [], projectHeaderIds: hIds, headerPrices: hVals,
    });
    res.status === 1 ? (toast.success(res.msg), onSaved()) : toast.error(res.msg);
  };

  // Group items by project header, mirroring the legacy sales bill layout.
  const groups = (() => {
    if (!data) return [];
    return (data.headers || []).map((h: any) => ({
      header: h,
      items: data.rows.filter((r: any) => r.header_id === h.id),
    }));
  })();
  const totals = (() => {
    if (!data) return { portal: 0, est: 0, sales: 0 };
    const portal = data.rows.reduce((s: number, r: any) => s + Number(r.portal_price || 0), 0);
    const est = data.rows.reduce((s: number, r: any) => s + Number(r.estimated_purchase || 0), 0);
    const sales = Object.values(headerAmts).reduce((s: number, v) => s + (parseFloat(v as string) || 0), 0);
    return { portal, est, sales };
  })();

  return (
    <Modal title="Sales Pricing" wizard step={{ at: 2, of: 2 }} onClose={onClose}
      footer={<>
        <Button variant="subtle" color="gray" onClick={onClose}>Close</Button>
        <Button leftSection={<CheckIcon size={ICON.sm} />} onClick={save} disabled={!data}>Save Sales Prices</Button>
      </>}>
      {!data ? <Text c="dimmed" py="md">Loading…</Text> : (
        <>
          <DiscountBadge ri={data.discount.ri} payg={data.discount.payg} />
          <div className="table-wrap">
            <table className="data">
              <thead><tr>
                <th>#</th><th>Product</th><th>Type</th><th>Model</th><th>Deployed On</th>
                <th className="num">Portal (₹)</th><th className="num">Est. Purchase (₹)</th><th className="num">Sales Price (₹)</th>
              </tr></thead>
              <tbody>
                {groups.map(({ header, items }: any) => (
                  <>
                    <tr key={`h-${header.id}`} className="group-row">
                      <td colSpan={7}>{header.name}{header.quantity > 0 && <span style={{ opacity: .65 }}> · Qty {header.quantity}</span>}</td>
                      <td className="num" style={{ background: 'inherit' }}>
                        <NumberInput size="xs" hideControls w={140} placeholder="Header amount" styles={{ input: { textAlign: 'right', fontWeight: 600 } }}
                          value={headerAmts[header.id] ?? ''}
                          onChange={(v) => setHeaderAmts({ ...headerAmts, [header.id]: v === '' ? '' : String(v) })} />
                      </td>
                    </tr>
                    {items.map((r: any, i: number) => (
                      <tr key={r.id}>
                        <td data-label="#">{i + 1}</td>
                        <td data-label="Product">{r.product_name}</td>
                        <td data-label="Type">{r.category_name || '-'}</td>
                        <td data-label="Model"><ModelBadge model={r.model} /></td>
                        <td data-label="Deployed On">{fmtDate(r.deployment_start)}</td>
                        <td data-label="Portal" className="num">{money(r.portal_price)}</td>
                        <td data-label="Est. Purchase" className="num">{money(r.estimated_purchase)}</td>
                        <td className="num muted">—</td>
                      </tr>
                    ))}
                  </>
                ))}
                <tr className="total-row">
                  <td colSpan={5}>Grand Total</td>
                  <td className="num">{money(totals.portal)}</td>
                  <td className="num">{money(totals.est)}</td>
                  <td className="num">{money(totals.sales)}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </>
      )}
    </Modal>
  );
}

// =====================================================================
//  PURCHASE PRICING — attach distributor invoice(s) + per-header actual amount.
//  Mirrors purchasebillitemsection.php: invoices live inside the purchase step.
// =====================================================================
export function PurchaseModal({ projectId, billId, onClose, onSaved }: BaseProps & { billId: number }) {
  const toast = useToast();
  const confirm = useConfirm();
  const { lookups } = useLookups();
  const [data, setData] = useState<any>(null);
  const [amounts, setAmounts] = useState<Record<number, string>>({});
  const [invoices, setInvoices] = useState<Invoice[]>([]);
  const [untagged, setUntagged] = useState<Invoice[]>([]);
  const [invoiceId, setInvoiceId] = useState('0');
  const [newInvoice, setNewInvoice] = useState({ referenceNo: '', amount: '', gstSlab: '0', date: '', type: '3', description: '' });

  const loadPricing = () => apiGet<any>(`/bills/${billId}/pricing`).then((d) => {
    setData(d);
    const a: Record<number, string> = {};
    const useCalculatedDefaults = Number(d?.bill?.status ?? 0) <= 2;
    (d.purchaseHeaders || []).forEach((p: any) => {
      const val = useCalculatedDefaults || p.amount == null ? Number(p.calculated_amount ?? 0) : Number(p.amount ?? 0);
      a[p.purchase_header_id] = String(val);
    });
    setAmounts(a);
  });
  const loadInvoices = async () => {
    const [inv, free] = await Promise.all([
      apiGet<Invoice[]>(`/invoices/project/${projectId}`),
      apiGet<Invoice[]>(`/invoices/project/${projectId}/untagged`),
    ]);
    setInvoices(inv); setUntagged(free); setInvoiceId('0');
  };
  useEffect(() => { loadPricing(); loadInvoices(); /* eslint-disable-next-line */ }, [billId]);

  const mapped = invoices.filter((i) => Number(i.bill_id || 0) === billId);
  const invoiceTotal = mapped.reduce((s, i) => s + Number(i.amount || 0), 0);

  const mapInvoice = async () => {
    if (invoiceId === '0') return;
    const res = await apiPost('/invoices/bill-mapping', { projectId, billId, invoiceId });
    res.status === 1 ? (toast.success(res.msg), loadInvoices()) : toast.error(res.msg);
  };
  const createAndMap = async () => {
    if (!newInvoice.referenceNo || newInvoice.amount === '') { toast.error('Enter invoice reference no and amount'); return; }
    const createRes = await apiPost<{ id: number }>('/invoices', {
      'add-invoice-project-id': projectId, 'add-invoice-reference-no': newInvoice.referenceNo,
      'add-invoice-amount': newInvoice.amount, 'add-invoice-gst-slab': newInvoice.gstSlab,
      'add-invoice-date': newInvoice.date, 'add-invoice-type': newInvoice.type, 'add-invoice-desc': newInvoice.description,
    });
    if (createRes.status !== 1) { toast.error(createRes.msg); return; }
    const createdId = Number(createRes.data?.id || 0);
    if (createdId) await apiPost('/invoices/bill-mapping', { projectId, billId, invoiceId: createdId });
    toast.success('Invoice created and mapped to this monthly bill');
    setNewInvoice({ referenceNo: '', amount: '', gstSlab: '0', date: '', type: '3', description: '' });
    loadInvoices();
  };
  const unlinkInvoice = async (inv: Invoice) => {
    if (!(await confirm({ message: `Unlink invoice ${inv.number}? Linked notes will be removed.`, danger: true, confirmLabel: 'Unlink' }))) return;
    const res = await apiDelete('/invoices/bill-mapping', { projectId, billId, invoiceId: inv.id });
    res.status === 1 ? (toast.success(res.msg), loadInvoices()) : toast.error(res.msg);
  };

  const save = async () => {
    if (!data) return;
    const itemIds = data.rows.map((r: any) => r.id);
    // Per-item purchase price = portal minus the model's discount (legacy behaviour).
    const purchasePrices = itemIds.map((id: number) => {
      const row = data.rows.find((r: any) => r.id === id);
      const pct = row?.model === 'RI' ? data.discount.ri : row?.model === 'PAYG' ? data.discount.payg : 0;
      return String(Math.round((Number(row?.portal_price ?? 0) * (1 - pct / 100) + Number.EPSILON) * 100) / 100);
    });
    const phIds = data.purchaseHeaders.map((p: any) => p.purchase_header_id);
    const phVals = phIds.map((id: number) => amounts[id] ?? '0');
    const res = await apiPost('/bills/purchase-price', {
      projectId, billId, projectItemIds: itemIds, purchasePrices,
      purchaseHeaderIds: phIds, headerPrices: phVals,
    });
    res.status === 1 ? (toast.success(res.msg), onSaved()) : toast.error(res.msg);
  };

  const reset = async () => {
    const res = await apiPost(`/bills/${billId}/reset-purchase`);
    res.status === 1 ? (toast.success(res.msg), onSaved()) : toast.error(res.msg);
  };

  return (
    <Modal title="Purchase Pricing" wizard step={{ at: 2, of: 2 }} onClose={onClose}
      footer={<>
        <Button variant="subtle" color="gray" onClick={onClose}>Close</Button>
        <Button color="red" variant="light" leftSection={<RefreshIcon size={ICON.sm} />} onClick={reset} disabled={!data}>Reset Purchase</Button>
        <Button leftSection={<CheckIcon size={ICON.sm} />} onClick={save} disabled={!data}>Save Purchase Prices</Button>
      </>}>
      {!data ? <Text c="dimmed" py="md">Loading…</Text> : (
        <>
          <DiscountBadge ri={data.discount.ri} payg={data.discount.payg} />

          {/* ── Distributor invoices (attached to this monthly bill) ── */}
          <Card withBorder radius="md" p={0} mb="md">
            <Group justify="space-between" px="md" py="sm" wrap="wrap" gap={4} style={{ borderBottom: '1px solid var(--border)' }}>
              <Text fw={600} fz="sm">Purchase Invoices</Text>
              <Text c="dimmed" fz="xs">Attach the distributor's invoice(s) — total {money(invoiceTotal)}</Text>
            </Group>
            <Box p="md">
              <SimpleGrid cols={{ base: 1, sm: 2, md: 3 }} spacing="sm" mb="sm">
                <TextInput label="Invoice Reference No" value={newInvoice.referenceNo} onChange={(e) => setNewInvoice({ ...newInvoice, referenceNo: e.target.value })} />
                <NumberInput label="Amount" hideControls value={newInvoice.amount} onChange={(v) => setNewInvoice({ ...newInvoice, amount: v === '' ? '' : String(v) })} />
                <MSelect label="GST Slab" placeholder="Select GST" value={newInvoice.gstSlab === '0' ? null : newInvoice.gstSlab} onChange={(v) => setNewInvoice({ ...newInvoice, gstSlab: v || '0' })}
                  data={lookups.gstSlabs.map((g) => ({ value: String(g.percentage), label: g.name }))} />
                <TextInput label="Invoice Date" type="date" value={newInvoice.date} onChange={(e) => setNewInvoice({ ...newInvoice, date: e.target.value })} />
                <MSelect label="Type" value={newInvoice.type} onChange={(v) => setNewInvoice({ ...newInvoice, type: v || '3' })}
                  data={[{ value: '1', label: 'RI' }, { value: '2', label: 'PAYG' }, { value: '3', label: 'Other / Mixed' }]} />
                <TextInput label="Description" value={newInvoice.description} onChange={(e) => setNewInvoice({ ...newInvoice, description: e.target.value })} />
              </SimpleGrid>
              <Group gap="sm" mb="sm" wrap="wrap" align="flex-end">
                <Button size="xs" leftSection={<AddIcon size={ICON.sm} />} onClick={createAndMap}>Create + Map Invoice</Button>
                <Text c="dimmed" fz="xs">or map an existing one:</Text>
                <MSelect placeholder="Select existing invoice" value={invoiceId === '0' ? null : invoiceId} onChange={(v) => setInvoiceId(v || '0')}
                  data={untagged.map((i) => ({ value: String(i.id), label: `${i.number} - ${money(i.amount)}` }))} style={{ minWidth: 240 }} />
                <Button size="xs" variant="default" onClick={mapInvoice} disabled={invoiceId === '0'}>Map</Button>
              </Group>
              <div className="table-wrap">
                <table className="data">
                  <thead><tr><th>Invoice No</th><th>Date</th><th className="num">Amount (₹)</th><th>GST</th><th className="actions"></th></tr></thead>
                  <tbody>
                    {mapped.map((inv) => (
                      <tr key={inv.id}>
                        <td data-label="Invoice No"><strong>{inv.number}</strong></td>
                        <td data-label="Date">{fmtDate(inv.invoice_date)}</td>
                        <td data-label="Amount" className="num">{money(inv.amount)}</td>
                        <td data-label="GST">{inv.gst_name || `${inv.gst_slab}%`}</td>
                        <td className="actions"><Button size="xs" variant="light" color="red" onClick={() => unlinkInvoice(inv)}>Unlink</Button></td>
                      </tr>
                    ))}
                    {mapped.length === 0 && <tr><td colSpan={5}><Text c="dimmed" ta="center" py="sm">No invoices attached yet.</Text></td></tr>}
                  </tbody>
                </table>
              </div>
            </Box>
          </Card>

          {/* ── Purchase price per header (actual distributor amount) ── */}
          <div className="table-wrap">
            <table className="data">
              <thead><tr>
                <th>#</th><th>Product</th><th>Type</th><th>Model</th>
                <th className="num">Portal (₹)</th><th className="num">Calculated (₹)</th><th className="num">Purchase Price (₹)</th>
              </tr></thead>
              <tbody>
                {data.purchaseHeaders.map((p: any) => {
                  const items = data.rows.filter((r: any) => r.purchase_header_id === p.purchase_header_id);
                  return (
                    <Fragment key={`ph-${p.purchase_header_id}`}>
                      <tr className="group-row">
                        <td colSpan={4}>{p.purchase_header_name}</td>
                        <td className="num" style={{ color: '#fff' }}>{money(p.portal_total)}</td>
                        <td className="num" style={{ color: '#fff' }}>{money(p.calculated_amount)}</td>
                        <td className="num">
                          <NumberInput size="xs" hideControls w={150} placeholder="Actual amount" styles={{ input: { textAlign: 'right', fontWeight: 600 } }}
                            value={amounts[p.purchase_header_id] ?? ''}
                            onChange={(v) => setAmounts({ ...amounts, [p.purchase_header_id]: v === '' ? '' : String(v) })} />
                        </td>
                      </tr>
                      {items.map((r: any, i: number) => {
                        const pct = r.model === 'RI' ? data.discount.ri : r.model === 'PAYG' ? data.discount.payg : 0;
                        const calc = Number(r.portal_price || 0) * (1 - pct / 100);
                        return (
                          <tr key={r.id}>
                            <td data-label="#">{i + 1}</td>
                            <td data-label="Product">{r.product_name}</td>
                            <td data-label="Type">{r.category_name || '-'}</td>
                            <td data-label="Model"><ModelBadge model={r.model} /></td>
                            <td data-label="Portal" className="num">{money(r.portal_price)}</td>
                            <td data-label="Calculated" className="num">{money(calc)}</td>
                            <td className="num muted">—</td>
                          </tr>
                        );
                      })}
                    </Fragment>
                  );
                })}
                {data.purchaseHeaders.length === 0 && <tr><td colSpan={7}><Text c="dimmed" ta="center" py="sm">No purchase headers. Assign purchase headers to items first.</Text></td></tr>}
              </tbody>
            </table>
          </div>
        </>
      )}
    </Modal>
  );
}

// =====================================================================
//  SUMMARY = purchase reconciliation hub (deviation + debit/credit notes).
//  Mirrors monthlybillitemsummarysection.php + the DN/CN flow.
// =====================================================================
export function SummaryModal({ projectId, billId, onClose, onSaved }: BaseProps & { billId: number }) {
  const toast = useToast();
  const { lookups } = useLookups();
  const [data, setData] = useState<any>(null);
  const [invoices, setInvoices] = useState<Invoice[]>([]);
  const [debitNotes, setDebitNotes] = useState<any[]>([]);
  const [creditNotes, setCreditNotes] = useState<any[]>([]);
  const [dnOpen, setDnOpen] = useState(false);
  const [dnForm, setDnForm] = useState({ invoiceId: '0', creditType: '0', amount: '', remark: '' });
  const [cnDebit, setCnDebit] = useState<any | null>(null);
  const [cnForm, setCnForm] = useState({ referenceNo: '', amount: '', remark: '' });

  const load = async () => {
    const [pricing, inv, dns, cns] = await Promise.all([
      apiGet<any>(`/bills/${billId}/pricing`),
      apiGet<Invoice[]>(`/invoices/project/${projectId}`),
      apiGet<any[]>(`/notes/debit/project/${projectId}`),
      apiGet<any[]>(`/notes/credit/project/${projectId}`),
    ]);
    setData(pricing); setInvoices(inv); setDebitNotes(dns); setCreditNotes(cns);
  };
  useEffect(() => { load(); /* eslint-disable-next-line */ }, [projectId, billId]);

  const recon = data?.reconciliation;
  const mapped = invoices.filter((i) => Number(i.bill_id || 0) === billId);
  const billDNs = debitNotes.filter((n) => Number(n.bill_id) === billId);
  const billCNs = creditNotes.filter((n) => Number(n.bill_id) === billId);
  const headers = data?.purchaseHeaders || [];

  const openDebit = () => {
    setDnForm({ invoiceId: mapped[0] ? String(mapped[0].id) : '0', creditType: '0', amount: recon ? String(recon.shortfall) : '', remark: '' });
    setDnOpen(true);
  };
  const createDebit = async () => {
    if (dnForm.invoiceId === '0') { toast.error('Select the invoice this DN relates to'); return; }
    if (dnForm.creditType === '0') { toast.error('Select DN type'); return; }
    if (dnForm.amount === '') { toast.error('Enter DN amount'); return; }
    const res = await apiPost('/notes/debit', {
      projectId, billId, invoiceId: dnForm.invoiceId, type: '2',
      creditType: dnForm.creditType, amount: dnForm.amount, remark: dnForm.remark,
    });
    if (res.status !== 1) { toast.error(res.msg); return; }
    toast.success(res.msg); setDnOpen(false); await load(); onSaved();
  };
  const openCredit = (dn: any) => {
    const pending = Math.max(0, Number(dn.amount || 0) - Number(dn.cn_val || 0));
    setCnForm({ referenceNo: '', amount: pending ? String(pending) : '', remark: '' });
    setCnDebit(dn);
  };
  const createCredit = async () => {
    if (!cnDebit) return;
    if (cnForm.referenceNo === '') { toast.error('Enter credit note reference no'); return; }
    if (cnForm.amount === '') { toast.error('Enter credit note amount'); return; }
    const res = await apiPost('/notes/credit', {
      projectId, billId, invoiceId: cnDebit.invoice_id, debitNoteId: cnDebit.id,
      referenceNo: cnForm.referenceNo, amount: cnForm.amount, remark: cnForm.remark,
    });
    if (res.status !== 1) { toast.error(res.msg); return; }
    toast.success(res.msg); setCnDebit(null); await load(); onSaved();
  };

  return (
    <Modal title="Monthly Bill Summary — Purchase Reconciliation" size="xl" onClose={onClose}
      footer={<Button variant="subtle" color="gray" onClick={onClose}>Close</Button>}>
      {!data ? <Text c="dimmed" py="md">Loading…</Text> : (
        <>
          <DiscountBadge ri={data.discount.ri} payg={data.discount.payg} />

          {/* Reconciliation banner */}
          {recon && (
            <SimpleGrid cols={{ base: 2, sm: 3, md: 5 }} spacing="sm" mb="md">
              <MiniStat label="Expected Purchase" value={money(recon.calculated_purchase)} mono />
              <MiniStat label="Actual Purchase" value={money(recon.actual_purchase)} mono />
              <MiniStat label="Debit Notes Raised" value={money(recon.debit_note_amount)} mono />
              <MiniStat label="Remaining Shortfall" mono value={recon.shortfall > 0 ? <span style={{ color: '#c0392b' }}>{money(recon.shortfall)}</span> : money(recon.shortfall)} />
              <Paper withBorder radius="md" p="sm">
                <Text fz="xs" fw={600} tt="uppercase" c="dimmed" style={{ letterSpacing: '.06em' }}>Status</Text>
                <Badge mt={6} radius="sm" style={{ background: recon.note_color, color: '#fff' }}>{recon.note_label} · {recon.deviation}%</Badge>
              </Paper>
            </SimpleGrid>
          )}

          {/* Per purchase header deviation */}
          {headers.length === 0 && <Text c="dimmed" ta="center" py="sm">No purchase-priced items in this bill.</Text>}
          {headers.map((p: any) => {
            const items = data.rows.filter((r: any) => r.purchase_header_id === p.purchase_header_id);
            return (
              <Card key={p.purchase_header_id} withBorder radius="md" p={0} mb="sm">
                <Group justify="space-between" px="md" py="sm" wrap="wrap" gap={4} style={{ borderBottom: '1px solid var(--border)' }}>
                  <Text fw={600} fz="sm">{p.purchase_header_name}</Text>
                  <Badge color={p.deviation_color === 'red' ? 'red' : 'green'} variant="light" radius="sm">
                    Calc {money(p.calculated_amount)} · Actual {money(p.amount)} · Dev {p.deviation}%
                  </Badge>
                </Group>
                <Box p="md">
                  <div className="table-wrap">
                    <table className="data">
                      <thead><tr><th>Product</th><th>Type</th><th>Model</th><th>Description</th><th className="num">Portal (₹)</th><th className="num">Calculated (₹)</th></tr></thead>
                      <tbody>
                        {items.map((r: any) => {
                          const disc = r.model === 'RI' ? data.discount.ri : r.model === 'PAYG' ? data.discount.payg : 0;
                          return (
                            <tr key={r.id}>
                              <td data-label="Product"><strong>{r.product_name}</strong></td>
                              <td data-label="Type">{r.category_name || '-'}</td>
                              <td data-label="Model"><ModelBadge model={r.model} /></td>
                              <td data-label="Description">{r.description || '-'}</td>
                              <td data-label="Portal" className="num">{money(r.portal_price)}</td>
                              <td data-label="Calculated" className="num">{money(Number(r.portal_price || 0) * (1 - disc / 100))}</td>
                            </tr>
                          );
                        })}
                        <tr className="total-row">
                          <td colSpan={4}>Header Total</td>
                          <td className="num">{money(p.portal_total)}</td>
                          <td className="num">{money(p.calculated_amount)}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </Box>
              </Card>
            );
          })}

          {/* Debit / Credit notes */}
          <Card withBorder radius="md" p={0} mb="sm">
            <Group justify="space-between" px="md" py="sm" wrap="wrap" gap={4} style={{ borderBottom: '1px solid var(--border)' }}>
              <Text fw={600} fz="sm">Debit &amp; Credit Notes</Text>
              {recon && recon.shortfall > 0 && (
                mapped.length > 0
                  ? <Button size="xs" leftSection={<AddIcon size={ICON.sm} />} onClick={openDebit}>Create Debit Note ({money(recon.shortfall)})</Button>
                  : <Text c="dimmed" fz="xs">Attach a purchase invoice (Purchase step) to raise a debit note.</Text>
              )}
            </Group>
            <Box p="md">
              <div className="table-wrap">
                <table className="data">
                  <thead><tr><th>Debit Note → Invoice</th><th>DN Type</th><th className="num">DN Amount (₹)</th><th className="num">Credited (₹)</th><th className="actions"></th></tr></thead>
                  <tbody>
                    {billDNs.map((n) => {
                      const pending = Math.max(0, Number(n.amount || 0) - Number(n.cn_val || 0));
                      return (
                        <tr key={n.id}>
                          <td data-label="Invoice">{n.invoice_number || `#${n.invoice_id}`}</td>
                          <td data-label="DN Type">{n.credit_type_name || '-'}</td>
                          <td data-label="DN Amount" className="num">{money(n.amount)}</td>
                          <td data-label="Credited" className="num">{money(n.cn_val)}</td>
                          <td className="actions">
                            {pending > 0
                              ? <Button size="xs" variant="subtle" onClick={() => openCredit(n)}>Create Credit Note</Button>
                              : <Badge color="green" variant="light" size="sm">Settled</Badge>}
                          </td>
                        </tr>
                      );
                    })}
                    {billDNs.length === 0 && <tr><td colSpan={5}><Text c="dimmed" ta="center" py="sm">No debit notes for this month.</Text></td></tr>}
                  </tbody>
                </table>
              </div>
              {billCNs.length > 0 && (
                <div className="table-wrap" style={{ marginTop: 10 }}>
                  <table className="data">
                    <thead><tr><th>Credit Note → Invoice</th><th>Reference No</th><th className="num">Amount (₹)</th><th>Remark</th></tr></thead>
                    <tbody>
                      {billCNs.map((n) => (
                        <tr key={n.id}>
                          <td data-label="Invoice">{n.invoice_number || `#${n.invoice_id}`}</td>
                          <td data-label="Reference No"><strong>{n.reference_no}</strong></td>
                          <td data-label="Amount" className="num">{money(n.amount)}</td>
                          <td data-label="Remark">{n.remark || '-'}</td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              )}
            </Box>
          </Card>

          {/* DN modal */}
          {dnOpen && (
            <Modal title="Create Debit Note" onClose={() => setDnOpen(false)}
              footer={<><Button variant="subtle" color="gray" onClick={() => setDnOpen(false)}>Cancel</Button><Button leftSection={<AddIcon size={ICON.sm} />} onClick={createDebit}>Create Debit Note</Button></>}>
              <Group justify="flex-end" mb="sm"><Text fz="sm" c="dimmed">Remaining Amount: <b>{money(recon?.shortfall ?? 0)}</b></Text></Group>
              <SimpleGrid cols={{ base: 1, sm: 2 }} spacing="sm">
                <MSelect label="Related Invoice" placeholder="Select invoice" value={dnForm.invoiceId === '0' ? null : dnForm.invoiceId} onChange={(v) => setDnForm({ ...dnForm, invoiceId: v || '0' })}
                  data={mapped.map((i) => ({ value: String(i.id), label: `${i.number} - ${money(i.amount)}` }))} />
                <MSelect label="DN Type" placeholder="Select DN type" value={dnForm.creditType === '0' ? null : dnForm.creditType} onChange={(v) => setDnForm({ ...dnForm, creditType: v || '0' })}
                  data={lookups.creditNoteTypes.map((t) => ({ value: String(t.value), label: t.name }))} />
                <NumberInput label="DN Amount (₹)" hideControls value={dnForm.amount} onChange={(v) => setDnForm({ ...dnForm, amount: v === '' ? '' : String(v) })} />
                <Textarea label="Remark" autosize minRows={2} value={dnForm.remark} onChange={(e) => setDnForm({ ...dnForm, remark: e.target.value })} style={{ gridColumn: '1 / -1' }} />
              </SimpleGrid>
            </Modal>
          )}

          {/* CN modal */}
          {cnDebit && (
            <Modal title={`Create Credit Note · DN ${money(cnDebit.amount)}`} onClose={() => setCnDebit(null)}
              footer={<><Button variant="subtle" color="gray" onClick={() => setCnDebit(null)}>Cancel</Button><Button leftSection={<AddIcon size={ICON.sm} />} onClick={createCredit}>Create Credit Note</Button></>}>
              <SimpleGrid cols={{ base: 1, sm: 2 }} spacing="sm">
                <TextInput label="Reference No" value={cnForm.referenceNo} onChange={(e) => setCnForm({ ...cnForm, referenceNo: e.target.value })} />
                <NumberInput label="Amount (₹)" hideControls value={cnForm.amount} onChange={(v) => setCnForm({ ...cnForm, amount: v === '' ? '' : String(v) })} />
                <Textarea label="Remark" autosize minRows={2} value={cnForm.remark} onChange={(e) => setCnForm({ ...cnForm, remark: e.target.value })} style={{ gridColumn: '1 / -1' }} />
              </SimpleGrid>
            </Modal>
          )}
        </>
      )}
    </Modal>
  );
}

// =====================================================================
//  PROGRESS
// =====================================================================
export function ProgressModal({ projectId, billId, currentProgress, onClose, onSaved }: BaseProps & { billId: number; currentProgress: number }) {
  const toast = useToast();
  const { lookups } = useLookups();
  const [progress, setProgress] = useState('0');
  const save = async () => {
    if (progress === '0') { toast.error('Please select progress'); return; }
    const res = await apiPost(`/bills/${billId}/progress`, { projectId, progress });
    res.status === 1 ? (toast.success(res.msg), onSaved()) : toast.error(res.msg);
  };
  return (
    <Modal title="Update Billing Progress" onClose={onClose}
      footer={<>
        <Button variant="subtle" color="gray" onClick={onClose}>Cancel</Button>
        <Button leftSection={<CheckIcon size={ICON.sm} />} onClick={save}>Update</Button>
      </>}>
      <MSelect label="Select Progress" placeholder="Select Progress" value={progress === '0' ? null : progress}
        onChange={(v) => setProgress(v || '0')}
        data={lookups.billingProgress.filter((p) => p.value > currentProgress).map((p) => ({ value: String(p.value), label: p.name }))} />
    </Modal>
  );
}
