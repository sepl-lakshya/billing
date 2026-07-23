import { useEffect, useMemo, useRef, useState } from 'react';
import {
  Modal, Stack, Group, Text, Title, Button, Table, Checkbox, TextInput,
  NumberInput, Select, Badge, ThemeIcon, Switch, SimpleGrid, Paper, Menu,
  LoadingOverlay, ActionIcon, Tooltip, Box, rem, Alert, SegmentedControl,
} from '@mantine/core';
import { Dropzone } from '@mantine/dropzone';
// Icons come from the central Lucide registry (aliased to the local names used below).
import {
  CloudUploadIcon as IconCloudUpload, SpreadsheetIcon as IconFileSpreadsheet, DeleteIcon as IconTrash,
  SparklesIcon as IconSparkles, CheckIcon as IconCheck, CloseIcon as IconX,
  TableIcon as IconTable, ServerIcon as IconServer2, IdIcon as IconId,
  RupeeIcon as IconCurrencyRupee, LayersIcon as IconLayersSubtract, InfoIcon as IconInfoCircle,
  AddIcon as IconPlus, GripIcon as IconGripVertical, ChevronDownIcon as IconChevronDown,
  ChevronRightIcon as IconChevronRight, ChevronUpIcon as IconChevronUp, ArrowUpIcon as IconArrowUp,
  ArrowDownIcon as IconArrowDown, MoveVerticalIcon as IconArrowsMoveVertical,
} from '../../../lib/icons';
import { http } from '../../../api/client';
import { useToast } from '../../../state/ToastContext';
import { useLookups } from '../../../state/LookupContext';
import { money } from '../../../lib/format';

interface PreviewItem {
  key: string; uid?: string; productName: string; meterCategory: string; serviceFamily: string;
  model: 'RI' | 'PAYG'; quantity: number; unitOfMeasure: string; unitPrice: number;
  cost: number; rowCount: number; subscription: string; resource: string; resourceCount: number; include: boolean;
  changeType?: 'new' | 'continuing'; existingItemId?: number; existingHeaderId?: number; existingHeaderName?: string; prevUnitPrice?: number; prevCost?: number | null;
}
interface PreviewGroup { header: string; uid?: string; headerId?: number; purchaseHeaderId?: number; totalCost: number; items: PreviewItem[]; }
interface StoppedItem {
  id: number; resource_id: string; model: 'RI' | 'PAYG' | string;
  product_name: string; header_name: string; purchase_header_name: string;
  unit_price: number; last_cost: number | null;
}
interface ImportDiff {
  newCount: number; continuingCount: number; stoppedCount: number;
  stopped: StoppedItem[]; lastBill: { month: number; year: number } | null;
}
interface Preview {
  summary: { rowCount: number; totalCost: number; riCost: number; paygCost: number; currency: string; resourceBy: string; groupBy: string; productBy: string; customerName: string };
  groups: PreviewGroup[];
  columns: string[];
  mode?: 'setup' | 'monthly';
  diff?: ImportDiff;
}
interface ImportContext {
  mode: 'setup' | 'monthly';
  itemCount: number; billCount: number;
  distributor: number;
  distributors: { id: number; name: string }[];
  headers: { id: number; name: string }[];
  purchaseHeaders: { id: number; name: string }[];
  hasDiscount: boolean;
  lastBill: { month: number; year: number } | null;
  suggestedMonth: number; suggestedYear: number;
}

const humanize = (c: string) => c.replace(/([A-Z])/g, ' $1').replace(/^./, (m) => m.toUpperCase()).trim();

export default function ImportConsumption({ projectId, onClose, onDone }: { projectId: number; onClose: () => void; onDone: () => void }) {
  const toast = useToast();
  const { lookups } = useLookups();
  const [files, setFiles] = useState<File[]>([]);
  const [analyzing, setAnalyzing] = useState(false);
  const [committing, setCommitting] = useState(false);
  const [preview, setPreview] = useState<Preview | null>(null);
  const [groups, setGroups] = useState<PreviewGroup[]>([]);
  const [map, setMap] = useState({ groupBy: 'resource', productBy: 'meterName', resourceBy: '' });
  const [showResource, setShowResource] = useState(true);
  const [createBill, setCreateBill] = useState(true);
  const [collapsed, setCollapsed] = useState<Record<string, boolean>>({});
  const [dragHint, setDragHint] = useState<string | null>(null);
  const drag = useRef<{ kind: 'header' | 'item'; gi: number; ii?: number } | null>(null);
  const uid = useRef(0);
  const now = new Date();
  const [month, setMonth] = useState(String(now.getMonth() === 0 ? 12 : now.getMonth()));
  const [year, setYear] = useState(String(now.getMonth() === 0 ? now.getFullYear() - 1 : now.getFullYear()));
  const [ctx, setCtx] = useState<ImportContext | null>(null);
  const [distributor, setDistributor] = useState('0');
  const [stopped, setStopped] = useState<StoppedItem[]>([]);
  const [disableStopped, setDisableStopped] = useState<Record<number, boolean>>({});
  const [layout, setLayout] = useState<'auto' | 'manual'>('auto');

  // Fetch import context up-front so the screen adapts to setup vs monthly.
  useEffect(() => {
    http.get(`/api/imports/${projectId}/context`).then((res) => {
      if (res.data.status === 1) {
        const c: ImportContext = res.data.data;
        setCtx(c);
        const def = c.distributor || (c.distributors.length === 1 ? c.distributors[0].id : 0);
        setDistributor(String(def || 0));
        setMonth(String(c.suggestedMonth));
        setYear(String(c.suggestedYear));
      }
    }).catch(() => { /* context is best-effort */ });
  }, [projectId]);

  // Attach stable client ids so React keys + collapse state survive reorder/rename/move.
  const withUids = (gs: PreviewGroup[]): PreviewGroup[] =>
    gs.map((g) => ({
      ...g,
      uid: g.uid || `h${++uid.current}`,
      items: g.items.map((it) => ({ ...it, uid: it.uid || `i${++uid.current}` })),
    }));

  // Turn the resource-grouped preview into the chosen header layout:
  //  - 'auto'  : one sales header per resource (original behaviour)
  //  - 'manual': products bucketed under the project's existing sales headers;
  //              new/unmatched products land in an "Unassigned" bucket to place.
  const layoutGroups = (previewGroups: PreviewGroup[], mode: 'auto' | 'manual'): PreviewGroup[] => {
    if (mode === 'auto') return withUids(previewGroups);
    const buckets = new Map<number, PreviewGroup>();
    for (const h of ctx?.headers || []) buckets.set(h.id, { header: h.name, headerId: h.id, totalCost: 0, items: [], uid: `h${++uid.current}` });
    const unassigned: PreviewGroup = { header: 'Unassigned', totalCost: 0, items: [], uid: `h${++uid.current}` };
    for (const g of previewGroups) {
      for (const it of g.items) {
        const bucket = it.changeType === 'continuing' && it.existingHeaderId && buckets.has(it.existingHeaderId)
          ? buckets.get(it.existingHeaderId)!
          : unassigned;
        bucket.items.push({ ...it, uid: `i${++uid.current}` });
      }
    }
    const result = [...buckets.values(), unassigned];
    for (const grp of result) grp.totalCost = grp.items.reduce((s, i) => s + i.cost, 0);
    return result;
  };

  const changeLayout = (next: 'auto' | 'manual') => {
    setLayout(next);
    setCollapsed({});
    if (preview) setGroups(layoutGroups(preview.groups, next));
  };

  const analyze = async (mapping = map) => {
    if (files.length === 0) { toast.error('Add at least one CSV/Excel file'); return; }
    setAnalyzing(true);
    try {
      const form = new FormData();
      files.forEach((f) => form.append('files', f));
      form.append('groupBy', mapping.groupBy);
      form.append('productBy', mapping.productBy);
      if (mapping.resourceBy) form.append('resourceBy', mapping.resourceBy);
      form.append('mode', ctx?.mode || 'auto');
      const res = await http.post(`/api/imports/${projectId}/preview`, form, { headers: { 'Content-Type': 'multipart/form-data' } });
      if (res.data.status === 1) {
        const data: Preview = res.data.data;
        setPreview(data);
        setGroups(layoutGroups(data.groups, layout));
        setCollapsed({});
        setMap({ groupBy: data.summary.groupBy, productBy: data.summary.productBy, resourceBy: data.summary.resourceBy });
        const st = data.diff?.stopped || [];
        setStopped(st);
        setDisableStopped(Object.fromEntries(st.map((s) => [s.id, true])));
      } else toast.error(res.data.msg);
    } catch (e: any) {
      toast.error(e?.response?.data?.msg || 'Failed to analyze file');
    } finally {
      setAnalyzing(false);
    }
  };

  const remap = (patch: Partial<typeof map>) => {
    const next = { ...map, ...patch };
    setMap(next);
    analyze(next);
  };

  const totals = useMemo(() => {
    let items = 0, cost = 0, ri = 0, payg = 0;
    for (const g of groups) for (const it of g.items) if (it.include) {
      items++; cost += it.cost; if (it.model === 'RI') ri += it.cost; else payg += it.cost;
    }
    return { items, cost, ri, payg };
  }, [groups]);

  const stoppedToDisable = stopped.filter((s) => disableStopped[s.id]).length;
  const monthName = (m: number) => lookups.months.find((x) => x.value === m)?.name || String(m);

  const commit = async () => {
    if (totals.items === 0) { toast.error('Nothing selected to import'); return; }
    setCommitting(true);
    try {
      const res = await http.post(`/api/imports/${projectId}/commit`, {
        groups, createBill, month: Number(month), year: Number(year),
        mode: ctx?.mode || 'auto',
        distributor: Number(distributor) || 0,
        stopped: stopped.filter((s) => disableStopped[s.id]).map((s) => s.id),
      });
      if (res.data.status === 1) { toast.success(res.data.msg); onDone(); }
      else toast.error(res.data.msg);
    } catch (e: any) {
      toast.error(e?.response?.data?.msg || 'Import failed');
    } finally {
      setCommitting(false);
    }
  };

  // ---- full-control edit helpers (headers + items + ordering) ----
  const setHeader = (gi: number, name: string) => setGroups((gs) => gs.map((g, i) => (i === gi ? { ...g, header: name } : g)));
  const setGroupPurchaseHeader = (gi: number, phId?: number) => setGroups((gs) => gs.map((g, i) => (i === gi ? { ...g, purchaseHeaderId: phId } : g)));
  const patchItem = (gi: number, ii: number, patch: Partial<PreviewItem>) =>
    setGroups((gs) => gs.map((g, i) => i === gi ? { ...g, items: g.items.map((it, j) => j === ii ? { ...it, ...patch } : it) } : g));
  const toggleGroup = (gi: number, include: boolean) =>
    setGroups((gs) => gs.map((g, i) => i === gi ? { ...g, items: g.items.map((it) => ({ ...it, include })) } : g));

  const addHeader = () =>
    setGroups((gs) => [...gs, { header: `New Header ${gs.length + 1}`, totalCost: 0, items: [], uid: `h${++uid.current}` }]);
  const deleteHeader = (gi: number) => setGroups((gs) => gs.filter((_, i) => i !== gi));
  const deleteItem = (gi: number, ii: number) =>
    setGroups((gs) => gs.map((g, i) => (i === gi ? { ...g, items: g.items.filter((_, j) => j !== ii) } : g)));

  const moveHeader = (gi: number, to: number) =>
    setGroups((gs) => {
      if (to < 0 || to >= gs.length || to === gi) return gs;
      const next = [...gs];
      const [m] = next.splice(gi, 1);
      next.splice(to, 0, m);
      return next;
    });

  const moveItemWithin = (gi: number, ii: number, to: number) =>
    setGroups((gs) => gs.map((g, i) => {
      if (i !== gi || to < 0 || to >= g.items.length || to === ii) return g;
      const items = [...g.items];
      const [m] = items.splice(ii, 1);
      items.splice(to, 0, m);
      return { ...g, items };
    }));

  // Move an item to any position in any header (used by drag-drop + the move menu).
  const moveItem = (from: { gi: number; ii: number }, to: { gi: number; ii: number }) =>
    setGroups((gs) => {
      const moved = gs[from.gi]?.items[from.ii];
      if (!moved) return gs;
      const removed = gs.map((g, i) => (i === from.gi ? { ...g, items: g.items.filter((_, j) => j !== from.ii) } : g));
      let at = to.ii;
      if (from.gi === to.gi && from.ii < to.ii) at -= 1;
      return removed.map((g, i) => (i === to.gi ? { ...g, items: [...g.items.slice(0, at), moved, ...g.items.slice(at)] } : g));
    });
  const moveItemToHeaderEnd = (fromGi: number, fromIi: number, toGi: number) =>
    moveItem({ gi: fromGi, ii: fromIi }, { gi: toGi, ii: groups[toGi]?.items.length ?? 0 });

  const colOptions = (preview?.columns || []).map((c) => ({ value: c, label: humanize(c) }));
  const groupOptions = [
    { value: 'resource', label: 'Resource (one bar per resource — recommended)' },
    ...colOptions,
  ];

  return (
    <Modal opened onClose={onClose} fullScreen radius={0} withCloseButton={false} padding={0}
      styles={{ body: { height: '100dvh', display: 'flex', flexDirection: 'column' } }}>
      {/* Header */}
      <Group justify="space-between" px="lg" py="md" wrap="nowrap" style={{ borderBottom: '1px solid var(--mantine-color-gray-3)' }}>
        <Group gap="sm" wrap="nowrap">
          <ThemeIcon size={42} radius="md" variant="gradient" gradient={{ from: 'brand.5', to: 'brand.8', deg: 135 }}>
            <IconCloudUpload size={22} />
          </ThemeIcon>
          <div>
            <Title order={3}>Import Azure Consumption</Title>
            <Text c="dimmed" fz="sm">Upload the partner export — pick your columns, review, then commit.</Text>
          </div>
        </Group>
        <ActionIcon variant="subtle" color="gray" size="lg" onClick={onClose}><IconX size={20} /></ActionIcon>
      </Group>

      <Box style={{ flex: 1, overflowY: 'auto', position: 'relative' }}>
        <LoadingOverlay visible={analyzing} zIndex={5} overlayProps={{ blur: 1 }} />
        {!preview ? (
          /* ---------- Upload ---------- */
          <Stack maw={720} mx="auto" px="lg" py="xl" gap="lg">
            <Dropzone
              onDrop={(fs) => setFiles((p) => [...p, ...fs])}
              accept={['text/csv', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']}
              maxSize={30 * 1024 ** 2}
              multiple
            >
              <Stack align="center" gap={6} py="xl" style={{ pointerEvents: 'none' }}>
                <Dropzone.Accept><ThemeIcon size={56} radius="xl" variant="light" color="brand"><IconCloudUpload size={30} /></ThemeIcon></Dropzone.Accept>
                <Dropzone.Reject><ThemeIcon size={56} radius="xl" variant="light" color="red"><IconX size={30} /></ThemeIcon></Dropzone.Reject>
                <Dropzone.Idle><ThemeIcon size={56} radius="xl" variant="light" color="brand"><IconCloudUpload size={30} /></ThemeIcon></Dropzone.Idle>
                <Text fw={600} fz="lg" mt="sm">Drop consumption files here</Text>
                <Text c="dimmed" fz="sm" ta="center">CSV or Excel — add both the RI (reservation) and PAYG (usage) parts if you have them</Text>
              </Stack>
            </Dropzone>

            {files.length > 0 && (
              <Stack gap="xs">
                {files.map((f, i) => (
                  <Paper key={i} withBorder radius="md" p="sm">
                    <Group wrap="nowrap">
                      <ThemeIcon variant="light" color="teal" radius="md"><IconFileSpreadsheet size={18} /></ThemeIcon>
                      <div style={{ flex: 1, minWidth: 0 }}>
                        <Text fz="sm" fw={500} truncate>{f.name}</Text>
                        <Text fz="xs" c="dimmed">{(f.size / 1024).toFixed(0)} KB</Text>
                      </div>
                      <ActionIcon variant="subtle" color="red" onClick={() => setFiles((p) => p.filter((_, j) => j !== i))}><IconTrash size={16} /></ActionIcon>
                    </Group>
                  </Paper>
                ))}
              </Stack>
            )}

            <Button size="md" leftSection={<IconSparkles size={18} />} loading={analyzing} disabled={files.length === 0} onClick={() => analyze()}>
              Analyze &amp; Build Preview
            </Button>
          </Stack>
        ) : (
          /* ---------- Review & arrange ---------- */
          <Stack maw={1240} mx="auto" px="lg" py="lg" gap="md">
            <Group justify="space-between" wrap="wrap" gap="xs">
              <Button variant="subtle" color="gray" w="fit-content" size="compact-sm" onClick={() => { setPreview(null); setGroups([]); }}>← Back to upload</Button>
              <Text fz="xs" c="dimmed">Drag the <IconGripVertical size={12} style={{ verticalAlign: 'middle' }} /> handles to reorder headers, reorder products, or drop a product into another header.</Text>
            </Group>

            {preview.mode === 'monthly' ? (
              <Alert icon={<IconInfoCircle size={18} />} color="grape" variant="light" radius="lg" py={8}>
                <Group gap="sm" wrap="wrap">
                  <Text fz="sm" fw={600}>Monthly import.</Text>
                  <Text fz="sm" c="dimmed">Compared against what&rsquo;s already in this project{preview.diff?.lastBill ? ` (last billed ${monthName(preview.diff.lastBill.month)} ${preview.diff.lastBill.year})` : ''}:</Text>
                  <Badge color="teal" variant="light">{preview.diff?.newCount ?? 0} new</Badge>
                  <Badge color="gray" variant="light">{preview.diff?.continuingCount ?? 0} continuing</Badge>
                  <Badge color="orange" variant="light">{stopped.length} stopped</Badge>
                </Group>
              </Alert>
            ) : (
              <Alert icon={<IconInfoCircle size={18} />} color="blue" variant="light" radius="lg" py={8}>
                <Text fz="sm"><b>First-time setup import.</b> Arrange everything exactly how you want it &mdash; the order you set here is the order it is created. This creates the sales &amp; purchase headers and the first monthly bill; assign the project&rsquo;s distributor in the bar below.</Text>
              </Alert>
            )}

            {createBill && ctx && !ctx.hasDiscount && (
              <Alert icon={<IconInfoCircle size={18} />} color="yellow" variant="light" radius="lg" py={8}>
                <Text fz="sm">No discount is set for this project yet, so this month starts at <b>0% RI / 0% PAYG</b>. Add it in the <b>Discounts</b> section &mdash; it applies to this month automatically once set (until the purchase is finalized).</Text>
              </Alert>
            )}

            {/* Header layout */}
            <Paper withBorder radius="lg" p="md">
              <Group justify="space-between" wrap="wrap" gap="sm">
                <Group gap={6}><IconLayersSubtract size={16} /><Text fw={600} fz="sm">Header layout</Text></Group>
                <SegmentedControl size="xs" value={layout} onChange={(v) => changeLayout(v as 'auto' | 'manual')}
                  data={[{ label: 'Auto \u2014 one per resource', value: 'auto' }, { label: 'Manual \u2014 my headers', value: 'manual' }]} />
              </Group>
              <Text c="dimmed" fz="xs" mt="xs">
                {layout === 'auto'
                  ? 'Products are grouped into one sales header per Azure resource (the original behaviour).'
                  : 'Products go under the sales headers you already created; anything new lands in \u201cUnassigned\u201d \u2014 drag it into the right header before committing. Existing products keep their header.'}
              </Text>
            </Paper>

            {/* Column mapping */}
            <Paper withBorder radius="lg" p="md">
              <Group gap={6} mb="sm"><IconTable size={16} /><Text fw={600} fz="sm">Column mapping — you control what becomes what</Text></Group>
              <SimpleGrid cols={{ base: 1, sm: 3 }} spacing="md">
                <Select label="Header / bar grouping" description="One project header (bar) per group — like the old portal" leftSection={<IconLayersSubtract size={16} />}
                  data={groupOptions} value={map.groupBy} onChange={(v) => v && remap({ groupBy: v })} searchable comboboxProps={{ withinPortal: true }} />
                <Select label="Product line column" description="Becomes each product row" leftSection={<IconServer2 size={16} />}
                  data={colOptions} value={map.productBy} onChange={(v) => v && remap({ productBy: v })} searchable comboboxProps={{ withinPortal: true }} />
                <Select label="Resource ID column" description="Stored on every item (legacy key)" leftSection={<IconId size={16} />}
                  data={colOptions} value={map.resourceBy} onChange={(v) => v && remap({ resourceBy: v })} searchable comboboxProps={{ withinPortal: true }} />
              </SimpleGrid>
              <Text c="dimmed" fz="xs" mt="xs">
                Purchase headers are created automatically: one for Reserved Instances (RI) and one for Pay-as-you-go (PAYG) consumption — exactly like the old portal.
              </Text>
            </Paper>

            {/* Summary */}
            <SimpleGrid cols={{ base: 2, md: 4 }} spacing="md">
              <Stat label="Line items (rows)" value={preview.summary.rowCount.toLocaleString('en-IN')} icon={<IconTable size={20} />} color="gray" />
              <Stat label="Total consumption" value={money(totals.cost)} icon={<IconCurrencyRupee size={20} />} color="brand" />
              <Stat label="RI (reserved)" value={money(totals.ri)} icon={<IconCheck size={20} />} color="orange" />
              <Stat label="PAYG (on-demand)" value={money(totals.payg)} icon={<IconCloudUpload size={20} />} color="cyan" />
            </SimpleGrid>

            {/* Toolbar */}
            <Group justify="space-between" wrap="wrap" gap="xs">
              <Group gap="xs">
                <Button size="compact-sm" variant="light" leftSection={<IconPlus size={14} />} onClick={addHeader}>Add header</Button>
                <Button size="compact-sm" variant="subtle" color="gray" onClick={() => setCollapsed({})}>Expand all</Button>
                <Button size="compact-sm" variant="subtle" color="gray" onClick={() => setCollapsed(Object.fromEntries(groups.map((g) => [g.uid!, true])))}>Collapse all</Button>
              </Group>
              <Group gap="md">
                <Text c="dimmed" fz="sm"><b>{totals.items}</b> products · <b>{groups.length}</b> headers</Text>
                <Switch label="Show resource id" checked={showResource} onChange={(e) => setShowResource(e.currentTarget.checked)} />
              </Group>
            </Group>

            {groups.length === 0 ? (
              <Alert icon={<IconInfoCircle size={18} />} color="yellow">No headers — click “Add header”, or go back and try different column mapping.</Alert>
            ) : (
              <Stack gap="sm">
                {groups.map((g, gi) => {
                  const grpCost = g.items.filter((i) => i.include).reduce((s, i) => s + i.cost, 0);
                  const allOn = g.items.length > 0 && g.items.every((i) => i.include);
                  const someOn = g.items.some((i) => i.include);
                  const isCollapsed = !!collapsed[g.uid!];
                  const isDropTarget = dragHint === g.uid;
                  return (
                    <Paper
                      key={g.uid} withBorder radius="lg" p={0}
                      onDragOver={(e) => { if (drag.current?.kind === 'header') e.preventDefault(); }}
                      onDrop={(e) => { if (drag.current?.kind === 'header') { e.preventDefault(); moveHeader(drag.current.gi, gi); drag.current = null; } }}
                      style={{ overflow: 'hidden', outline: isDropTarget ? '2px dashed var(--mantine-color-brand-4)' : undefined, transition: 'outline 120ms' }}
                    >
                      {/* header bar */}
                      <Group justify="space-between" wrap="nowrap" px="sm" py={8}
                        style={{ background: 'var(--mantine-color-brand-0)', borderBottom: isCollapsed ? 'none' : '1px solid var(--mantine-color-gray-2)' }}>
                        <Group gap={6} wrap="nowrap" style={{ flex: 1, minWidth: 0 }}>
                          <Box draggable
                            onDragStart={(e) => { drag.current = { kind: 'header', gi }; e.dataTransfer.effectAllowed = 'move'; e.dataTransfer.setData('text/plain', 'header'); }}
                            onDragEnd={() => { drag.current = null; setDragHint(null); }}
                            style={{ cursor: 'grab', display: 'flex', color: 'var(--mantine-color-gray-5)' }} title="Drag to reorder this header"><IconGripVertical size={18} /></Box>
                          <ActionIcon variant="subtle" color="gray" size="sm" onClick={() => setCollapsed((c) => ({ ...c, [g.uid!]: !isCollapsed }))} title={isCollapsed ? 'Expand' : 'Collapse'}>
                            {isCollapsed ? <IconChevronRight size={16} /> : <IconChevronDown size={16} />}
                          </ActionIcon>
                          <Checkbox checked={allOn} indeterminate={!allOn && someOn} onChange={(e) => toggleGroup(gi, e.currentTarget.checked)} />
                          <TextInput variant="unstyled" value={g.header} onChange={(e) => setHeader(gi, e.currentTarget.value)} placeholder="Header name"
                            styles={{ input: { fontWeight: 700, fontSize: rem(15), minWidth: rem(160) } }} style={{ flex: 1, minWidth: 0 }} />
                        </Group>
                        <Group gap={8} wrap="nowrap">
                          {layout === 'manual' && (
                            <Select size="xs" w={160} variant="filled" placeholder="Auto RI/PAYG"
                              data={(ctx?.purchaseHeaders || []).map((p) => ({ value: String(p.id), label: p.name }))}
                              value={g.purchaseHeaderId ? String(g.purchaseHeaderId) : null}
                              onChange={(v) => setGroupPurchaseHeader(gi, v ? Number(v) : undefined)}
                              clearable comboboxProps={{ withinPortal: true }} title="Purchase header for this group's products" />
                          )}
                          <Badge variant="light" color="gray">{g.items.length}</Badge>
                          <Text fw={700} fz="sm" miw={86} ta="right">{money(grpCost)}</Text>
                          <ActionIcon.Group>
                            <ActionIcon variant="default" size="sm" disabled={gi === 0} onClick={() => moveHeader(gi, gi - 1)} title="Move header up"><IconArrowUp size={14} /></ActionIcon>
                            <ActionIcon variant="default" size="sm" disabled={gi === groups.length - 1} onClick={() => moveHeader(gi, gi + 1)} title="Move header down"><IconArrowDown size={14} /></ActionIcon>
                          </ActionIcon.Group>
                          <Tooltip label="Delete header"><ActionIcon variant="subtle" color="red" size="sm" onClick={() => deleteHeader(gi)}><IconTrash size={15} /></ActionIcon></Tooltip>
                        </Group>
                      </Group>

                      {/* items */}
                      {!isCollapsed && (
                        <Box
                          onDragOver={(e) => { if (drag.current?.kind === 'item') { e.preventDefault(); if (dragHint !== g.uid) setDragHint(g.uid!); } }}
                          onDragLeave={() => setDragHint((h) => (h === g.uid ? null : h))}
                          onDrop={(e) => { const d = drag.current; if (d?.kind === 'item') { e.preventDefault(); moveItem({ gi: d.gi, ii: d.ii! }, { gi, ii: g.items.length }); drag.current = null; } setDragHint(null); }}
                        >
                          {g.items.length === 0 ? (
                            <Text c="dimmed" fz="sm" ta="center" py="lg">Empty header — drag products here, or delete it.</Text>
                          ) : (
                            <Table verticalSpacing={6} horizontalSpacing="sm" highlightOnHover style={{ whiteSpace: 'nowrap' }}>
                              <Table.Thead>
                                <Table.Tr>
                                  <Table.Th w={58}></Table.Th>
                                  <Table.Th w={34}></Table.Th>
                                  <Table.Th>Product</Table.Th>
                                  <Table.Th w={100}>Model</Table.Th>
                                  {showResource && <Table.Th>Resource ID</Table.Th>}
                                  <Table.Th ta="right" w={64}>Qty</Table.Th>
                                  <Table.Th ta="right" w={120}>Unit Price</Table.Th>
                                  <Table.Th ta="right" w={140}>Cost (₹)</Table.Th>
                                  <Table.Th w={84}></Table.Th>
                                </Table.Tr>
                              </Table.Thead>
                              <Table.Tbody>
                                {g.items.map((it, ii) => (
                                  <Table.Tr
                                    key={it.uid} opacity={it.include ? 1 : 0.4}
                                    onDragOver={(e) => { if (drag.current?.kind === 'item') e.preventDefault(); }}
                                    onDrop={(e) => { const d = drag.current; if (d?.kind === 'item') { e.preventDefault(); e.stopPropagation(); moveItem({ gi: d.gi, ii: d.ii! }, { gi, ii }); drag.current = null; setDragHint(null); } }}
                                  >
                                    <Table.Td>
                                      <Group gap={0} wrap="nowrap">
                                        <Box draggable
                                          onDragStart={(e) => { drag.current = { kind: 'item', gi, ii }; e.dataTransfer.effectAllowed = 'move'; e.dataTransfer.setData('text/plain', 'item'); }}
                                          onDragEnd={() => { drag.current = null; setDragHint(null); }}
                                          style={{ cursor: 'grab', display: 'flex', color: 'var(--mantine-color-gray-4)' }} title="Drag to move or reorder"><IconGripVertical size={16} /></Box>
                                        <Stack gap={0}>
                                          <ActionIcon variant="transparent" color="gray" size={16} disabled={ii === 0} onClick={() => moveItemWithin(gi, ii, ii - 1)} title="Up"><IconChevronUp size={13} /></ActionIcon>
                                          <ActionIcon variant="transparent" color="gray" size={16} disabled={ii === g.items.length - 1} onClick={() => moveItemWithin(gi, ii, ii + 1)} title="Down"><IconChevronDown size={13} /></ActionIcon>
                                        </Stack>
                                      </Group>
                                    </Table.Td>
                                    <Table.Td><Checkbox checked={it.include} onChange={(e) => patchItem(gi, ii, { include: e.currentTarget.checked })} /></Table.Td>
                                    <Table.Td>
                                      <Group gap={6} wrap="nowrap">
                                        {it.changeType === 'new' && <Badge size="xs" variant="light" color="teal">New</Badge>}
                                        {it.changeType === 'continuing' && <Badge size="xs" variant="light" color="gray">Continuing</Badge>}
                                        <TextInput variant="unstyled" value={it.productName} onChange={(e) => patchItem(gi, ii, { productName: e.currentTarget.value })} styles={{ input: { fontWeight: 500, minWidth: rem(150) } }} />
                                      </Group>
                                      <Text fz={11} c="dimmed">{it.rowCount} rows · {it.unitOfMeasure || '—'}{it.changeType === 'continuing' && it.prevCost != null ? ` · was ${money(it.prevCost)}` : ''}</Text>
                                    </Table.Td>
                                    <Table.Td>
                                      <Select data={['RI', 'PAYG']} value={it.model} onChange={(v) => v && patchItem(gi, ii, { model: v as 'RI' | 'PAYG' })}
                                        variant="filled" size="xs" w={82} comboboxProps={{ withinPortal: true }} allowDeselect={false}
                                        styles={{ input: { fontWeight: 700, color: it.model === 'RI' ? 'var(--mantine-color-orange-7)' : 'var(--mantine-color-cyan-7)' } }} />
                                    </Table.Td>
                                    {showResource && (
                                      <Table.Td>
                                        <Text fz="xs" ff="monospace" truncate maw={220} title={it.resource}>{it.resource || '—'}</Text>
                                        {it.resourceCount > 1 && <Text fz={10} c="dimmed">+{it.resourceCount - 1} more</Text>}
                                      </Table.Td>
                                    )}
                                    <Table.Td ta="right"><Text fz="sm">{it.quantity}</Text></Table.Td>
                                    <Table.Td><NumberInput size="xs" hideControls value={it.unitPrice} onChange={(v) => patchItem(gi, ii, { unitPrice: Number(v) || 0 })} decimalScale={4} styles={{ input: { textAlign: 'right' } }} /></Table.Td>
                                    <Table.Td><NumberInput size="xs" hideControls value={it.cost} onChange={(v) => patchItem(gi, ii, { cost: Number(v) || 0 })} decimalScale={2} thousandSeparator="," styles={{ input: { textAlign: 'right', fontWeight: 600 } }} /></Table.Td>
                                    <Table.Td>
                                      <Group gap={2} wrap="nowrap" justify="flex-end">
                                        <Menu withinPortal position="bottom-end" shadow="md" width={240}>
                                          <Menu.Target><Tooltip label="Move to another header"><ActionIcon variant="subtle" color="gray" size="sm"><IconArrowsMoveVertical size={15} /></ActionIcon></Tooltip></Menu.Target>
                                          <Menu.Dropdown>
                                            <Menu.Label>Move to header</Menu.Label>
                                            {groups.map((tg, tgi) => (
                                              <Menu.Item key={tg.uid} disabled={tgi === gi} onClick={() => moveItemToHeaderEnd(gi, ii, tgi)}>{tg.header || `Header ${tgi + 1}`}</Menu.Item>
                                            ))}
                                          </Menu.Dropdown>
                                        </Menu>
                                        <Tooltip label="Remove product"><ActionIcon variant="subtle" color="red" size="sm" onClick={() => deleteItem(gi, ii)}><IconTrash size={15} /></ActionIcon></Tooltip>
                                      </Group>
                                    </Table.Td>
                                  </Table.Tr>
                                ))}
                              </Table.Tbody>
                            </Table>
                          )}
                        </Box>
                      )}
                    </Paper>
                  );
                })}
              </Stack>
            )}

            {preview.mode === 'monthly' && stopped.length > 0 && (
              <Paper withBorder radius="lg" p={0} style={{ borderColor: 'var(--mantine-color-orange-3)', overflow: 'hidden' }}>
                <Group justify="space-between" wrap="wrap" px="sm" py={8}
                  style={{ background: 'var(--mantine-color-orange-0)', borderBottom: '1px solid var(--mantine-color-orange-2)' }}>
                  <Group gap={8} wrap="nowrap">
                    <IconInfoCircle size={16} />
                    <Text fw={700} fz="sm">Stopped resources ({stopped.length})</Text>
                    <Text fz="xs" c="dimmed">billed last month, absent from this upload</Text>
                  </Group>
                  <Switch size="xs" label="Disable all"
                    checked={stopped.every((s) => disableStopped[s.id])}
                    onChange={(e) => setDisableStopped(Object.fromEntries(stopped.map((s) => [s.id, e.currentTarget.checked])))} />
                </Group>
                <Table verticalSpacing={6} horizontalSpacing="sm" highlightOnHover style={{ whiteSpace: 'nowrap' }}>
                  <Table.Thead>
                    <Table.Tr>
                      <Table.Th>Product</Table.Th>
                      <Table.Th w={90}>Model</Table.Th>
                      <Table.Th>Header</Table.Th>
                      <Table.Th ta="right" w={140}>Last cost (₹)</Table.Th>
                      <Table.Th w={130} ta="center">Action</Table.Th>
                    </Table.Tr>
                  </Table.Thead>
                  <Table.Tbody>
                    {stopped.map((s) => (
                      <Table.Tr key={s.id} opacity={disableStopped[s.id] ? 1 : 0.5}>
                        <Table.Td>
                          <Text fz="sm" fw={500}>{s.product_name || '—'}</Text>
                          <Text fz={11} c="dimmed" ff="monospace" truncate maw={260} title={s.resource_id}>{s.resource_id || '—'}</Text>
                        </Table.Td>
                        <Table.Td><Badge size="sm" variant="light" color={s.model === 'RI' ? 'orange' : 'cyan'}>{s.model}</Badge></Table.Td>
                        <Table.Td><Text fz="xs">{s.header_name || '—'}</Text></Table.Td>
                        <Table.Td ta="right"><Text fz="sm">{s.last_cost != null ? money(s.last_cost) : '—'}</Text></Table.Td>
                        <Table.Td ta="center">
                          <Switch size="xs" onLabel="Disable" offLabel="Keep" checked={!!disableStopped[s.id]}
                            onChange={(e) => setDisableStopped((m) => ({ ...m, [s.id]: e.currentTarget.checked }))} />
                        </Table.Td>
                      </Table.Tr>
                    ))}
                  </Table.Tbody>
                </Table>
              </Paper>
            )}
          </Stack>
        )}
      </Box>

      {/* Bottom action bar */}
      {preview && (
        <Group justify="space-between" px="lg" py="md" wrap="wrap" gap="sm" style={{ borderTop: '1px solid var(--mantine-color-gray-3)' }}>
          <Group gap="sm" wrap="wrap">
            {preview.mode !== 'monthly' && (
              <Select size="xs" w={190} placeholder="Assign distributor"
                data={(ctx?.distributors || []).map((d) => ({ value: String(d.id), label: d.name }))}
                value={distributor === '0' ? null : distributor} onChange={(v) => setDistributor(v || '0')}
                searchable clearable comboboxProps={{ withinPortal: true }} />
            )}
            <Switch checked={createBill} onChange={(e) => setCreateBill(e.currentTarget.checked)} label="Create monthly bill (portal prices pre-filled)" />
            <Select size="xs" w={110} disabled={!createBill} data={lookups.months.map((m) => ({ value: String(m.value), label: m.name }))} value={month} onChange={(v) => v && setMonth(v)} comboboxProps={{ withinPortal: true }} />
            <Select size="xs" w={95} disabled={!createBill} data={lookups.years.map((y) => ({ value: String(y.value), label: y.name }))} value={year} onChange={(v) => v && setYear(v)} comboboxProps={{ withinPortal: true }} />
          </Group>
          <Group gap="md">
            <Text fz="sm" c="dimmed">
              {preview.mode === 'monthly'
                ? <>Applying <b>{totals.items}</b> products{stoppedToDisable > 0 ? <> · disabling <b>{stoppedToDisable}</b></> : null}</>
                : <>Importing <b>{totals.items}</b> products · <b>{money(totals.cost)}</b></>}
            </Text>
            <Button color="teal" leftSection={<IconCheck size={16} />} loading={committing} disabled={totals.items === 0 && stoppedToDisable === 0} onClick={commit}>
              {preview.mode === 'monthly' ? 'Commit Monthly Import' : 'Commit Setup Import'}
            </Button>
          </Group>
        </Group>
      )}
    </Modal>
  );
}

function Stat({ label, value, icon, color }: { label: string; value: string; icon: React.ReactNode; color: string }) {
  return (
    <Paper withBorder radius="lg" p="md">
      <ThemeIcon variant="light" color={color} radius="md" size={36} mb="xs">{icon}</ThemeIcon>
      <Text fz="xs" c="dimmed" tt="uppercase" fw={700}>{label}</Text>
      <Text fz="xl" fw={800} mt={2}>{value}</Text>
    </Paper>
  );
}
