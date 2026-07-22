import { useEffect, useState } from 'react';
import { Card, Group, Button, Select as MSelect } from '@mantine/core';
import { OverflowMenu, type OverflowAction } from '../../../components/ui';
import { AddIcon, ChartIcon, RupeeIcon, InvoiceIcon, ViewIcon, DeleteIcon, ICON } from '../../../lib/icons';
import { apiGet, apiDelete } from '../../../api/client';
import { useToast } from '../../../state/ToastContext';
import { useConfirm } from '../../../state/ConfirmContext';
import { money } from '../../../lib/format';
import { PortalModal, SalesModal, PurchaseModal, SummaryModal, ProgressModal } from './bill/PricingModals';

interface StatusRow {
  id: number; year: number; month: number; month_name: string; status: number; purchase_header_status: number;
  progress: number; portal_total: number; sales_total: number; purchase_total: number; invoice_total: number;
  invoice_count: number;
  ri_discount: number; payg_discount: number;
  deviation: number; deviation_color: string;
  note_state: 'pending' | 'ok' | 'deviation' | 'dn' | 'cn'; note_label: string; note_color: string;
  shortfall: number;
}

type ModalState =
  | { kind: 'portal'; billId: number | null; month: number; year: number }
  | { kind: 'sales' | 'purchase' | 'summary'; billId: number }
  | { kind: 'progress'; billId: number; progress: number }
  | null;

export default function MonthlyBillTab({ projectId, onBillChange }: { projectId: number; onBillChange?: () => void }) {
  const toast = useToast();
  const confirm = useConfirm();
  const [rows, setRows] = useState<StatusRow[]>([]);
  const [availableMonths, setAvailableMonths] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [newMonth, setNewMonth] = useState('0');
  const [modal, setModal] = useState<ModalState>(null);

  const load = async () => {
    setLoading(true);
    const d = await apiGet<{ rows: StatusRow[]; availableMonths: any[] }>(`/bills/project/${projectId}/status`);
    setRows(d.rows || []);
    setAvailableMonths(d.availableMonths || []);
    setNewMonth('0');
    setLoading(false);
  };
  useEffect(() => { load(); /* eslint-disable-next-line */ }, [projectId]);

  const startNewBill = () => {
    if (newMonth === '0') return;
    const [year, month] = newMonth.split('-').map(Number);
    setModal({ kind: 'portal', billId: null, month, year });
  };

  const deleteBill = async (row: StatusRow) => {
    if (!(await confirm({ title: 'Delete monthly bill', message: `Delete the bill for ${row.month_name} ${row.year} and all its data?`, danger: true, confirmLabel: 'Delete' }))) return;
    const res = await apiDelete(`/bills/${row.id}`);
    res.status === 1 ? (toast.success(res.msg), load(), onBillChange?.()) : toast.error(res.msg);
  };

  const statusName = (s: number) => (['Draft', 'Portal', 'Sales', 'Purchase'][s] || 'Draft');

  return (
    <div>
      <Card withBorder shadow="sm" radius="lg" p={0}>
        <Group justify="space-between" px="md" py="xs" gap="sm" wrap="wrap" style={{ borderBottom: '1px solid var(--border)' }}>
          <MSelect
            placeholder="Select month for new bill"
            value={newMonth === '0' ? null : newMonth}
            onChange={(v) => setNewMonth(v || '0')}
            data={availableMonths.map((m) => ({ value: `${m.year}-${m.month}`, label: m.label }))}
            w={260} searchable clearable
          />
          <Button size="xs" leftSection={<AddIcon size={ICON.xs} />} onClick={startNewBill} disabled={newMonth === '0'}>New Portal Bill</Button>
        </Group>
        <div style={{ padding: 0 }}>
          {loading ? <div className="loading">Loading…</div> : (
            <div className="table-wrap">
              <table className="data" style={{ width: '100%' }}>
                <thead>
                  <tr>
                    <th>Month</th><th>Year</th>
                    <th className="num">Portal Total <br /> excl. GST (Rs.)</th>
                    <th className="num">Sales Total <br /> excl. GST (Rs.)</th>
                    <th className="num">Purchase Total <br /> excl. GST (Rs.)</th>
                    <th className="num">Invoices Total <br /> excl. GST (Rs.)</th>
                    <th className="num" style={{ whiteSpace: 'nowrap' }}>RI %</th>
                    <th className="num" style={{ whiteSpace: 'nowrap' }}>PAYG %</th>
                    <th>Progress</th>
                    <th>Status</th>
                    <th className="actions">Action</th>
                  </tr>
                </thead>
                <tbody>
                  {rows.map((r) => {
                    const invoiceOk = r.status >= 3
                      && Math.abs(Number(r.purchase_total) - Number(r.invoice_total)) < 1
                      && Number(r.invoice_total) > 0;
                    const hasInvoices = Number(r.invoice_count || 0) > 0;
                    const invoiceReady = r.status >= 3 || hasInvoices;
                    const hasPortal = Number(r.portal_total) > 0;
                    const hasSales = Number(r.sales_total) > 0;
                    const hasPurchase = Number(r.purchase_total) > 0;
                    const leftBorder = r.note_color || 'var(--border-strong)';
                    const billActions: OverflowAction[] = [
                      { label: 'Portal pricing', icon: <ChartIcon size={ICON.sm} />, onClick: () => setModal({ kind: 'portal', billId: r.id, month: r.month, year: r.year }) },
                    ];
                    if (r.status >= 1) billActions.push({ label: 'Sales pricing', icon: <RupeeIcon size={ICON.sm} />, onClick: () => setModal({ kind: 'sales', billId: r.id }) });
                    if (r.status >= 2) billActions.push({ label: 'Purchase pricing', icon: <InvoiceIcon size={ICON.sm} />, onClick: () => setModal({ kind: 'purchase', billId: r.id }) });
                    if (r.status >= 1) billActions.push({ label: 'Summary', icon: <ViewIcon size={ICON.sm} />, onClick: () => setModal({ kind: 'summary', billId: r.id }) });
                    billActions.push({ label: 'Delete bill', icon: <DeleteIcon size={ICON.sm} />, color: 'red', onClick: () => deleteBill(r) });
                    return (
                      <tr key={r.id} className="bill-row">
                        <td data-label="Month" style={{ borderLeft: `5px solid ${leftBorder}` }}><strong>{r.month_name}</strong></td>
                        <td data-label="Year">{r.year}</td>
                        <td data-label="Portal Total" className="num">
                          {hasPortal
                            ? <a href="#" onClick={(e) => { e.preventDefault(); setModal({ kind: 'portal', billId: r.id, month: r.month, year: r.year }); }}>{money(r.portal_total)}</a>
                            : <a href="#" onClick={(e) => { e.preventDefault(); setModal({ kind: 'portal', billId: r.id, month: r.month, year: r.year }); }} style={{ color: 'var(--primary)' }}>+ Add</a>}
                          {' '}<a href="#" onClick={(e) => { e.preventDefault(); setModal({ kind: 'portal', billId: r.id, month: r.month, year: r.year }); }} style={{ color: 'var(--muted)', fontSize: 11 }}>Refresh</a>
                        </td>
                        <td data-label="Sales Total" className="num">
                          {r.status >= 1
                            ? <a href="#" onClick={(e) => { e.preventDefault(); setModal({ kind: 'sales', billId: r.id }); }}>{hasSales ? money(r.sales_total) : '+ Add'}</a>
                            : <span className="muted">Not Added</span>}
                        </td>
                        <td data-label="Purchase Total" className="num">
                          {r.status >= 2 ? (
                            <>
                              <a href="#" onClick={(e) => { e.preventDefault(); setModal({ kind: 'purchase', billId: r.id }); }}>{hasPurchase ? money(r.purchase_total) : '+ Add'}</a>
                              {r.status >= 3 && r.purchase_header_status === 1 && (
                                <>{' '}<a href="#" onClick={(e) => { e.preventDefault(); setModal({ kind: 'purchase', billId: r.id }); }} style={{ color: 'var(--muted)', fontSize: 11 }}>Clear</a></>
                              )}
                              {r.status >= 3 && r.purchase_header_status === 1 && r.deviation > 0 && (
                                <span className={`badge ${r.deviation_color === 'red' ? 'badge-red' : 'badge-green'}`} style={{ marginLeft: 6 }}>{r.deviation}%</span>
                              )}
                            </>
                          ) : <span className="muted">Not Added</span>}
                        </td>
                        <td data-label="Invoices Total" className="num">
                          {invoiceReady ? (
                            <>
                              <a href="#" onClick={(e) => { e.preventDefault(); setModal({ kind: 'purchase', billId: r.id }); }}>
                                {hasInvoices ? money(r.invoice_total) : '+ Add'}
                              </a>
                              {invoiceOk && <span title="Amounts match" style={{ color: 'var(--success)', marginLeft: 6 }}>✔</span>}
                              {' '}<a href="#" onClick={(e) => { e.preventDefault(); setModal({ kind: 'purchase', billId: r.id }); }} style={{ color: 'var(--muted)', fontSize: 11 }}>Manage</a>
                            </>
                          ) : <span className="muted">Not Added</span>}
                        </td>
                        <td className="num">{r.ri_discount}</td>
                        <td className="num">{r.payg_discount}</td>
                        <td>
                          <div>
                            <a href="#" onClick={(e) => { e.preventDefault(); setModal({ kind: 'progress', billId: r.id, progress: r.progress }); }} style={{ color: r.progress ? 'var(--text)' : 'var(--muted)' }}>
                              {r.progress ? `${r.progress}%` : 'Set'}
                            </a>
                            <div className="muted" style={{ fontSize: 11, marginTop: 2 }}>{statusName(r.status)}</div>
                          </div>
                        </td>
                        <td data-label="Status">
                          <span title={r.note_label} style={{ display: 'inline-flex', alignItems: 'center', gap: 6 }}>
                            <span style={{ width: 11, height: 11, borderRadius: '50%', background: r.note_color, display: 'inline-block', boxShadow: '0 0 0 2px rgba(0,0,0,.05)' }} />
                            <span style={{ fontSize: 11, color: 'var(--muted)', whiteSpace: 'nowrap' }}>{r.note_label}</span>
                          </span>
                        </td>
                        <td className="actions">
                          <OverflowMenu actions={billActions} />
                        </td>
                      </tr>
                    );
                  })}
                  {rows.length > 0 && (
                    <tr className="total-row">
                      <td colSpan={2}>Grand Total</td>
                      <td className="num">{money(rows.reduce((s, r) => s + Number(r.portal_total || 0), 0))}</td>
                      <td className="num">{money(rows.reduce((s, r) => s + Number(r.sales_total || 0), 0))}</td>
                      <td className="num">{money(rows.reduce((s, r) => s + Number(r.purchase_total || 0), 0))}</td>
                      <td className="num">{money(rows.reduce((s, r) => s + Number(r.invoice_total || 0), 0))}</td>
                      <td colSpan={5} />
                    </tr>
                  )}
                  {rows.length === 0 && (
                    <tr><td colSpan={11}><div className="empty">No monthly bills yet. Pick a month above — or use Import Consumption to create the first bill automatically.</div></td></tr>
                  )}
                </tbody>
              </table>
            </div>
          )}
        </div>
      </Card>

      <div className="bill-legend" style={{ display: 'flex', gap: 16, flexWrap: 'wrap', margin: '10px 2px 0', fontSize: 12, color: 'var(--muted)' }}>
        {[
          { c: '#22c55e', l: 'OK' },
          { c: '#ef6b5a', l: 'Deviation' },
          { c: '#f0b429', l: 'DN Generated' },
          { c: '#1e5bf0', l: 'CN Received' },
          { c: '#94a3b8', l: 'Pending' },
        ].map((x) => (
          <span key={x.l} style={{ display: 'inline-flex', alignItems: 'center', gap: 6 }}>
            <span style={{ width: 11, height: 11, borderRadius: '50%', background: x.c, display: 'inline-block' }} /> {x.l}
          </span>
        ))}
      </div>

      {modal?.kind === 'portal' && (
        <PortalModal projectId={projectId} billId={modal.billId} month={modal.month} year={modal.year}
          onClose={() => setModal(null)} onSaved={() => { setModal(null); load(); }} />
      )}
      {modal?.kind === 'sales' && (
        <SalesModal projectId={projectId} billId={modal.billId} onClose={() => setModal(null)} onSaved={() => { setModal(null); load(); }} />
      )}
      {modal?.kind === 'purchase' && (
        <PurchaseModal projectId={projectId} billId={modal.billId} onClose={() => { setModal(null); load(); }} onSaved={() => { setModal(null); load(); }} />
      )}
      {modal?.kind === 'summary' && (
        <SummaryModal projectId={projectId} billId={modal.billId} onClose={() => { setModal(null); load(); }} onSaved={() => load()} />
      )}
      {modal?.kind === 'progress' && (
        <ProgressModal projectId={projectId} billId={modal.billId} currentProgress={modal.progress}
          onClose={() => setModal(null)} onSaved={() => { setModal(null); load(); }} />
      )}
    </div>
  );
}
