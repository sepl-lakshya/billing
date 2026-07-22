import { Fragment, useEffect, useState } from 'react';
import { Card, Group, Button, Badge, ActionIcon, Tooltip, Select as MSelect, NumberInput, TextInput, Textarea, SimpleGrid, Paper, Text } from '@mantine/core';
import Modal from '../../../components/Modal';
import { OverflowMenu, type OverflowAction } from '../../../components/ui';
import { AddIcon, EditIcon, DeleteIcon, DisableIcon, ChevronDownIcon, ChevronRightIcon, ICON } from '../../../lib/icons';
import { apiGet, apiPost, apiPut, apiDelete } from '../../../api/client';
import { useToast } from '../../../state/ToastContext';
import { useConfirm } from '../../../state/ConfirmContext';
import { useLookups } from '../../../state/LookupContext';
import { ProjectHeader, ProjectItem, Named, PurchaseHeader } from '../../../api/types';
import { money, fmtDate } from '../../../lib/format';

interface Row {
  product: string; productType: string; distributor: string; deploymentStart: string;
  deploymentEnd: string; unitMeasure: string; unitPrice: string; quantity: string;
  deployedProduct: string; discoveryStatus: string; purchaseHeaderId: string; resourceId: string;
  model: string; description: string;
}
const emptyRow = (): Row => ({
  product: '0', productType: '0', distributor: '0', deploymentStart: '', deploymentEnd: '',
  unitMeasure: '0', unitPrice: '', quantity: '', deployedProduct: '0', discoveryStatus: '0',
  purchaseHeaderId: '0', resourceId: '', model: '', description: '',
});

export default function ItemsTab({ projectId }: { projectId: number }) {
  const { lookups } = useLookups();
  const toast = useToast();
  const confirm = useConfirm();
  const [headers, setHeaders] = useState<ProjectHeader[]>([]);
  const [items, setItems] = useState<ProjectItem[]>([]);
  const [products, setProducts] = useState<Named[]>([]);
  const [distributors, setDistributors] = useState<Named[]>([]);
  const [purchaseHeaders, setPurchaseHeaders] = useState<PurchaseHeader[]>([]);
  const [loading, setLoading] = useState(true);
  const [collapsed, setCollapsed] = useState<Record<number, boolean>>({});

  const [itemModal, setItemModal] = useState<{ headerId: number } | null>(null);
  const [rows, setRows] = useState<Row[]>([emptyRow()]);
  const [editItem, setEditItem] = useState<ProjectItem | null>(null);
  const [disableTarget, setDisableTarget] = useState<ProjectItem | null>(null);
  const [disableDate, setDisableDate] = useState('');
  const [headerModal, setHeaderModal] = useState<{ mode: 'add' | 'edit'; header?: ProjectHeader } | null>(null);
  const [headerForm, setHeaderForm] = useState({ name: '', quantity: '', description: '' });

  const load = async () => {
    setLoading(true);
    const [h, it] = await Promise.all([
      apiGet<ProjectHeader[]>(`/cloud-projects/${projectId}/headers`),
      apiGet<ProjectItem[]>(`/cloud-projects/${projectId}/items`),
    ]);
    setHeaders(h); setItems(it); setLoading(false);
  };
  useEffect(() => {
    load();
    apiGet<Named[]>('/products').then((p) => setProducts(p.filter((x: any) => x.category === 1)));
    apiGet<Named[]>('/distributors').then(setDistributors);
    apiGet<PurchaseHeader[]>(`/purchase-headers?projectId=${projectId}`).then(setPurchaseHeaders);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [projectId]);

  const saveItems = async () => {
    const payload = rows.map((r) => ({
      product: r.product, productType: r.productType, distributor: r.distributor,
      deploymentStart: r.deploymentStart, deploymentEnd: r.deploymentEnd, unitMeasure: r.unitMeasure,
      unitPrice: r.unitPrice, quantity: r.quantity, deployedProduct: r.deployedProduct,
      discoveryStatus: r.discoveryStatus, purchaseHeaderId: r.purchaseHeaderId, resourceId: r.resourceId,
      model: r.model, description: r.description,
    }));
    const res = await apiPost(`/cloud-projects/${projectId}/items`, { headerId: itemModal!.headerId, items: payload });
    if (res.status === 1) { toast.success(res.msg); setItemModal(null); setRows([emptyRow()]); load(); }
    else toast.error(res.msg);
  };

  const saveEdit = async () => {
    if (!editItem) return;
    const res = await apiPut(`/cloud-projects/items/${editItem.id}`, {
      unitMeasure: editItem.unit_measure, unitPrice: editItem.unit_price, quantity: editItem.quantity,
      deployedProduct: editItem.deployed_product, discoveryStatus: editItem.discovery_status,
      purchaseHeaderId: editItem.purchase_header_id, resourceId: editItem.resource_id, description: editItem.description,
    });
    if (res.status === 1) { toast.success(res.msg); setEditItem(null); load(); }
    else toast.error(res.msg);
  };

  const deleteItem = async (it: ProjectItem) => {
    if (await confirm({ message: 'Delete this item?', danger: true, confirmLabel: 'Delete' })) {
      const res = await apiDelete(`/cloud-projects/items/${it.id}`);
      res.status === 1 ? (toast.success(res.msg), load()) : toast.error(res.msg);
    }
  };

  const confirmDisable = async () => {
    if (!disableTarget) return;
    if (!disableDate) { toast.error('Select a deactivation date'); return; }
    const res = await apiPost(`/cloud-projects/items/${disableTarget.id}/disable`, { deploymentEnd: disableDate });
    res.status === 1 ? (toast.success(res.msg), setDisableTarget(null), load()) : toast.error(res.msg);
  };

  const copyResource = async (resourceId: string) => {
    if (!resourceId) return;
    try {
      await navigator.clipboard.writeText(resourceId);
      toast.success('Resource ID copied');
    } catch {
      toast.error('Failed to copy resource ID');
    }
  };

  const openAddHeader = () => { setHeaderForm({ name: '', quantity: '', description: '' }); setHeaderModal({ mode: 'add' }); };
  const openEditHeader = (h: ProjectHeader) => {
    setHeaderForm({ name: h.name || '', quantity: String(h.quantity ?? ''), description: h.description || '' });
    setHeaderModal({ mode: 'edit', header: h });
  };
  const saveHeader = async () => {
    const isEdit = headerModal?.mode === 'edit' && headerModal.header;
    const res = isEdit
      ? await apiPut(`/cloud-projects/${projectId}/headers/${headerModal!.header!.id}`, {
          'edit-header-name': headerForm.name, 'edit-header-quantity': headerForm.quantity, 'edit-header-description': headerForm.description,
        })
      : await apiPost(`/cloud-projects/${projectId}/headers`, {
          'add-header-name': headerForm.name, 'add-header-quantity': headerForm.quantity, 'add-header-description': headerForm.description,
        });
    res.status === 1 ? (toast.success(res.msg), setHeaderModal(null), load()) : toast.error(res.msg);
  };
  const deleteHeader = async (h: ProjectHeader) => {
    if (await confirm({ title: 'Delete sales header', message: `Delete header "${h.name}" and linked products?`, danger: true, confirmLabel: 'Delete' })) {
      const res = await apiDelete(`/cloud-projects/headers/${h.id}`);
      res.status === 1 ? (toast.success(res.msg), load()) : toast.error(res.msg);
    }
  };
  const updRow = (idx: number, patch: Partial<Row>) => setRows((prev) => prev.map((x, i) => (i === idx ? { ...x, ...patch } : x)));
  const toggleCollapse = (id: number) => setCollapsed((c) => ({ ...c, [id]: !c[id] }));
  const itemActions = (it: ProjectItem): OverflowAction[] => {
    const a: OverflowAction[] = [{ label: 'Edit', icon: <EditIcon size={ICON.sm} />, onClick: () => setEditItem(it) }];
    if (it.status === 1) a.push({ label: 'Disable', icon: <DisableIcon size={ICON.sm} />, onClick: () => { setDisableDate(new Date().toISOString().slice(0, 10)); setDisableTarget(it); } });
    a.push({ label: 'Delete', icon: <DeleteIcon size={ICON.sm} />, color: 'red', onClick: () => deleteItem(it) });
    return a;
  };

  if (loading) return <div className="loading">Loading…</div>;

  return (
    <div>
      <Card withBorder shadow="sm" radius="lg" p={0}>
        <Group justify="space-between" px="md" py="xs" style={{ borderBottom: '1px solid var(--border)' }}>
          <Text fw={600} fz="sm">Headers &amp; products</Text>
          <Group gap="xs">
            {headers.length > 0 && (
              <>
                <Button size="xs" variant="subtle" color="gray" onClick={() => setCollapsed({})}>Expand all</Button>
                <Button size="xs" variant="subtle" color="gray" onClick={() => setCollapsed(Object.fromEntries(headers.map((h) => [h.id, true])))}>Collapse all</Button>
              </>
            )}
            <Button size="xs" leftSection={<AddIcon size={ICON.xs} />} onClick={openAddHeader}>Add Header</Button>
          </Group>
        </Group>
        <div style={{ padding: 0 }}>
          {headers.length === 0 ? (
            <div className="empty">No sales headers yet. Click “Add Header” to create one, then add products under it.</div>
          ) : (
            <div className="table-wrap">
              <table className="data">
                <thead><tr>
                  <th>SNo</th>
                  <th>Product</th>
                  <th>Type</th>
                  <th>Distributor</th>
                  <th className="num">Quoted</th>
                  <th>Unit</th>
                  <th className="num">Qty</th>
                  <th>Model</th>
                  <th>Deployed On</th>
                  <th>Status / Deactivation</th>
                  <th>Discovery</th>
                  <th>Purchase Header</th>
                  <th>Description</th>
                  <th>Resource ID</th>
                  <th className="actions"></th>
                </tr></thead>
                <tbody>
                  {headers.map((h) => {
                    const hItems = items.filter((it) => it.header_id === h.id);
                    return (
                      <Fragment key={`grp-${h.id}`}>
                        <tr key={`h-${h.id}`} className="group-row">
                          <td colSpan={14}>
                            <span onClick={() => toggleCollapse(h.id)} style={{ cursor: 'pointer', display: 'inline-flex', alignItems: 'center', gap: 6, userSelect: 'none' }} title={collapsed[h.id] ? 'Expand' : 'Collapse'}>
                              {collapsed[h.id] ? <ChevronRightIcon size={ICON.sm} /> : <ChevronDownIcon size={ICON.sm} />}
                              <span>{h.name}</span>
                            </span>
                            {h.quantity > 0 && <span style={{ opacity: .65 }}> · Qty {h.quantity}</span>}
                            <span style={{ opacity: .5, marginLeft: 8, fontWeight: 400 }}>{hItems.length} item{hItems.length === 1 ? '' : 's'}</span>
                            {h.description ? <span style={{ opacity: .68, marginLeft: 12, fontWeight: 400 }}> · {h.description}</span> : null}
                          </td>
                          <td className="actions">
                            <Group gap={4} justify="flex-end" wrap="nowrap">
                              <Tooltip label="Add products" withArrow>
                                <ActionIcon variant="white" size="sm" onClick={() => { setRows([emptyRow()]); setItemModal({ headerId: h.id }); }}><AddIcon size={ICON.sm} /></ActionIcon>
                              </Tooltip>
                              <Tooltip label="Edit header" withArrow>
                                <ActionIcon variant="white" color="gray" size="sm" onClick={() => openEditHeader(h)}><EditIcon size={ICON.sm} /></ActionIcon>
                              </Tooltip>
                              <Tooltip label="Delete header" withArrow>
                                <ActionIcon variant="white" color="red" size="sm" onClick={() => deleteHeader(h)}><DeleteIcon size={ICON.sm} /></ActionIcon>
                              </Tooltip>
                            </Group>
                          </td>
                        </tr>
                        {!collapsed[h.id] && hItems.map((it, idx) => (
                          <tr key={it.id}>
                            <td data-label="SNo">{idx + 1}</td>
                            <td data-label="Product">
                              <strong>{it.product_name}</strong>
                            </td>
                            <td data-label="Type">{(it as any).product_type_name || '-'}</td>
                            <td data-label="Distributor">{it.distributor_name || '-'}</td>
                            <td data-label="Quoted" className="num">{money(it.unit_price)}</td>
                            <td data-label="Unit">{it.unit_measure_name || '-'}</td>
                            <td data-label="Qty" className="num">{it.quantity}</td>
                            <td data-label="Model">{it.model ? <span className={`badge ${it.model === 'RI' ? 'chip-ri' : 'chip-payg'}`}>{it.model}</span> : 'N/A'}</td>
                            <td data-label="Deployed On">{fmtDate(it.deployment_start)}</td>
                            <td data-label="Status">
                              {it.status === 1
                                ? <Badge color="green" variant="light" size="sm">Active</Badge>
                                : <Badge color="red" variant="light" size="sm">{it.deployment_end ? fmtDate(it.deployment_end) : 'Disabled'}</Badge>}
                            </td>
                            <td data-label="Discovery">{it.discovery_name || '-'}</td>
                            <td data-label="Purchase Header">{it.purchase_header_name || '-'}</td>
                            <td data-label="Description">{it.description || '-'}</td>
                            <td data-label="Resource ID" className="resource-cell">
                              {it.resource_id ? (
                                <button className="copy-resource-btn" onClick={() => copyResource(it.resource_id)} title="Click to copy resource id">
                                  {it.resource_id}
                                </button>
                              ) : '-'}
                            </td>
                            <td data-label="" className="actions">
                              <OverflowMenu actions={itemActions(it)} />
                            </td>
                          </tr>
                        ))}
                        {!collapsed[h.id] && hItems.length === 0 && (
                          <tr><td colSpan={15} className="muted" style={{ fontStyle: 'italic' }}>No products under this header.</td></tr>
                        )}
                      </Fragment>
                    );
                  })}
                </tbody>
              </table>
            </div>
          )}
        </div>
      </Card>

      {itemModal && (
        <Modal title="Add Products" size="xl" onClose={() => setItemModal(null)}
          footer={<>
            <Button variant="light" leftSection={<AddIcon size={ICON.xs} />} onClick={() => setRows([...rows, emptyRow()])}>Add Row</Button>
            <span style={{ flex: 1 }} />
            <Button variant="default" onClick={() => setItemModal(null)}>Cancel</Button>
            <Button onClick={saveItems}>Save Products</Button>
          </>}>
          {rows.map((r, idx) => (
            <Paper key={idx} withBorder radius="md" p="md" mb="sm">
              <SimpleGrid cols={{ base: 1, sm: 2, lg: 3 }} spacing="sm">
                <MSelect label="Product" placeholder="Select" searchable value={r.product === '0' ? null : r.product} onChange={(v) => updRow(idx, { product: v || '0' })} data={products.map((p) => ({ value: String(p.id), label: p.name }))} />
                <MSelect label="Type" placeholder="Select" value={r.productType === '0' ? null : r.productType} onChange={(v) => updRow(idx, { productType: v || '0' })} data={lookups.cloudCategories.map((c) => ({ value: String(c.value), label: c.name }))} />
                <MSelect label="Distributor" placeholder="Select" searchable value={r.distributor === '0' ? null : r.distributor} onChange={(v) => updRow(idx, { distributor: v || '0' })} data={distributors.map((d) => ({ value: String(d.id), label: d.name }))} />
                <TextInput label="Deployment Start" type="date" value={r.deploymentStart} onChange={(e) => updRow(idx, { deploymentStart: e.currentTarget.value })} />
                <TextInput label="Deployment End" type="date" value={r.deploymentEnd} onChange={(e) => updRow(idx, { deploymentEnd: e.currentTarget.value })} />
                <MSelect label="Unit Measure" placeholder="Select" value={r.unitMeasure === '0' ? null : r.unitMeasure} onChange={(v) => updRow(idx, { unitMeasure: v || '0' })} data={lookups.unitMeasures.map((u) => ({ value: String(u.value), label: u.name }))} />
                <NumberInput label="Unit Price" placeholder="0" hideControls allowNegative={false} value={r.unitPrice} onChange={(v) => updRow(idx, { unitPrice: v === '' ? '' : String(v) })} />
                <NumberInput label="Quantity" placeholder="0" hideControls allowNegative={false} value={r.quantity} onChange={(v) => updRow(idx, { quantity: v === '' ? '' : String(v) })} />
                <MSelect label="Purchase Header" placeholder="Select" searchable value={r.purchaseHeaderId === '0' ? null : r.purchaseHeaderId} onChange={(v) => updRow(idx, { purchaseHeaderId: v || '0' })} data={purchaseHeaders.map((p) => ({ value: String(p.id), label: p.name }))} />
                <MSelect label="Deployed Product" placeholder="Select" searchable value={r.deployedProduct === '0' ? null : r.deployedProduct} onChange={(v) => updRow(idx, { deployedProduct: v || '0' })} data={products.map((p) => ({ value: String(p.id), label: p.name }))} />
                <MSelect label="Discovery" placeholder="Select" value={r.discoveryStatus === '0' ? null : r.discoveryStatus} onChange={(v) => updRow(idx, { discoveryStatus: v || '0' })} data={lookups.discoveryStatuses.map((d) => ({ value: String(d.value), label: d.name }))} />
                <TextInput label="Resource ID" value={r.resourceId} onChange={(e) => updRow(idx, { resourceId: e.currentTarget.value })} />
                <MSelect label="Model (RI / PAYG)" placeholder="Select model" value={r.model || null} onChange={(v) => updRow(idx, { model: v || '' })} data={[{ value: 'RI', label: 'RI' }, { value: 'PAYG', label: 'PAYG' }]} />
                <Textarea label="Description" style={{ gridColumn: '1 / -1' }} autosize minRows={1} value={r.description} onChange={(e) => updRow(idx, { description: e.currentTarget.value })} />
              </SimpleGrid>
              {rows.length > 1 && <Button mt="sm" size="xs" variant="light" color="red" leftSection={<DeleteIcon size={ICON.xs} />} onClick={() => setRows(rows.filter((_, i) => i !== idx))}>Remove Row</Button>}
            </Paper>
          ))}
        </Modal>
      )}

      {editItem && (
        <Modal title="Edit Item" onClose={() => setEditItem(null)}
          footer={<>
            <Button variant="default" onClick={() => setEditItem(null)}>Cancel</Button>
            <Button onClick={saveEdit}>Save</Button>
          </>}>
          <SimpleGrid cols={{ base: 1, sm: 2 }} spacing="md">
            <MSelect label="Unit Measure" placeholder="Select" value={String(editItem.unit_measure)} onChange={(v) => setEditItem({ ...editItem, unit_measure: Number(v) })} data={lookups.unitMeasures.map((u) => ({ value: String(u.value), label: u.name }))} />
            <NumberInput label="Unit Price" placeholder="0" hideControls allowNegative={false} value={editItem.unit_price} onChange={(v) => setEditItem({ ...editItem, unit_price: Number(v) || 0 })} />
            <NumberInput label="Quantity" placeholder="0" hideControls allowNegative={false} value={editItem.quantity} onChange={(v) => setEditItem({ ...editItem, quantity: Number(v) || 0 })} />
            <MSelect label="Purchase Header" placeholder="Select" searchable value={String(editItem.purchase_header_id)} onChange={(v) => setEditItem({ ...editItem, purchase_header_id: Number(v) })} data={purchaseHeaders.map((p) => ({ value: String(p.id), label: p.name }))} />
            <MSelect label="Discovery" placeholder="Select" value={String(editItem.discovery_status)} onChange={(v) => setEditItem({ ...editItem, discovery_status: Number(v) })} data={lookups.discoveryStatuses.map((d) => ({ value: String(d.value), label: d.name }))} />
            <TextInput label="Resource ID" value={editItem.resource_id || ''} onChange={(e) => setEditItem({ ...editItem, resource_id: e.currentTarget.value })} />
            <Textarea label="Description" style={{ gridColumn: '1 / -1' }} autosize minRows={2} value={editItem.description || ''} onChange={(e) => setEditItem({ ...editItem, description: e.currentTarget.value })} />
          </SimpleGrid>
        </Modal>
      )}

      {disableTarget && (
        <Modal title={`Disable · ${disableTarget.product_name}`} onClose={() => setDisableTarget(null)}
          footer={<>
            <Button variant="default" onClick={() => setDisableTarget(null)}>Cancel</Button>
            <Button color="red" onClick={confirmDisable}>Disable Item</Button>
          </>}>
          <Text c="dimmed" size="sm" mb="sm">Set the deactivation (deployment end) date. The item stays billable through this date, then drops out of later months.</Text>
          <TextInput label="Deactivation Date" type="date" value={disableDate} onChange={(e) => setDisableDate(e.currentTarget.value)} />
        </Modal>
      )}

      {headerModal && (
        <Modal title={headerModal.mode === 'edit' ? 'Edit Sales Header' : 'Add Sales Header'} onClose={() => setHeaderModal(null)}
          footer={<>
            <Button variant="default" onClick={() => setHeaderModal(null)}>Cancel</Button>
            <Button onClick={saveHeader}>Save</Button>
          </>}>
          <SimpleGrid cols={{ base: 1, sm: 2 }} spacing="md">
            <TextInput label="Header Name" placeholder="e.g. Compute - Production" value={headerForm.name} onChange={(e) => setHeaderForm({ ...headerForm, name: e.currentTarget.value })} />
            <NumberInput label="Quantity" placeholder="0" hideControls allowNegative={false} value={headerForm.quantity} onChange={(v) => setHeaderForm({ ...headerForm, quantity: v === '' ? '' : String(v) })} />
            <TextInput label="Description" placeholder="Optional" style={{ gridColumn: '1 / -1' }} value={headerForm.description} onChange={(e) => setHeaderForm({ ...headerForm, description: e.currentTarget.value })} />
          </SimpleGrid>
        </Modal>
      )}
    </div>
  );
}
