import { parse } from 'csv-parse/sync';
import * as XLSX from 'xlsx';

/** A normalised consumption row (only the fields we use). */
export interface ConsumptionRow {
  [key: string]: any;
}

/** Parse an uploaded CSV or XLSX buffer into an array of row objects. */
export function parseConsumptionFile(buffer: Buffer, filename: string): ConsumptionRow[] {
  const lower = (filename || '').toLowerCase();
  if (lower.endsWith('.xlsx') || lower.endsWith('.xls')) {
    const wb = XLSX.read(buffer, { type: 'buffer' });
    const sheet = wb.Sheets[wb.SheetNames[0]];
    return XLSX.utils.sheet_to_json<ConsumptionRow>(sheet, { defval: '' });
  }
  // CSV (default) - relax quotes because the Azure export embeds JSON in `tags`.
  return parse(buffer, {
    columns: true,
    skip_empty_lines: true,
    relax_quotes: true,
    relax_column_count: true,
    trim: true,
  }) as ConsumptionRow[];
}

function num(v: unknown): number {
  if (v === null || v === undefined) return 0;
  const n = parseFloat(String(v).replace(/[^0-9.\-]/g, ''));
  return Number.isFinite(n) ? n : 0;
}
function round2(n: number): number { return Math.round((n + Number.EPSILON) * 100) / 100; }
function round4(n: number): number { return Math.round((n + Number.EPSILON) * 10000) / 10000; }

export interface PreviewItem {
  key: string;
  productName: string;
  meterCategory: string;
  serviceFamily: string;
  model: 'RI' | 'PAYG';
  quantity: number;
  unitOfMeasure: string;
  unitPrice: number;
  cost: number;
  rowCount: number;
  subscription: string;
  resource: string;
  resourceCount: number;
  include: boolean;
}
export interface PreviewGroup {
  header: string;
  totalCost: number;
  items: PreviewItem[];
}
export interface ConsumptionPreview {
  summary: {
    rowCount: number;
    totalCost: number;
    riCost: number;
    paygCost: number;
    currency: string;
    periodStart: string;
    periodEnd: string;
    groupBy: string;
    productBy: string;
    resourceBy: string;
    customerName: string;
  };
  groups: PreviewGroup[];
  columns: string[];
}

/** Detect the best default column name from a list of candidates present in the data. */
function pickColumn(columns: string[], candidates: string[], fallback: string): string {
  const lower = new Map(columns.map((c) => [c.toLowerCase(), c]));
  for (const cand of candidates) {
    const hit = lower.get(cand.toLowerCase());
    if (hit) return hit;
  }
  return columns.includes(fallback) ? fallback : fallback;
}

/**
 * Aggregate raw Azure/partner consumption rows into the same structure the old
 * portal used manually:
 *   group (project header / "bar") = one per deployed RESOURCE, named
 *     "<dominant meter category> - <resource name>" (like the old hand-made bars)
 *   item (product line)           = one per meter within the resource
 *   model                         = RI for reservation rows, PAYG otherwise
 *
 *   groupBy    = 'resource' (default, old-portal style) or any raw column name
 *   productBy  = which column becomes the product line (default meterName)
 *   resourceBy = which column identifies the resource   (auto-detected)
 */
export function aggregateConsumption(
  rows: ConsumptionRow[],
  groupBy = 'resource',
  productBy = 'meterName',
  resourceBy?: string
): ConsumptionPreview {
  const columns = collectColumns(rows);
  const resourceCol = resourceBy && columns.includes(resourceBy)
    ? resourceBy
    : pickColumn(columns, ['resourceId', 'instanceId', 'resourceGuid', 'ResourceId', 'subscriptionName', 'subscriptionId'], 'resourceId');

  const groups = new Map<string, Map<string, PreviewItem>>();
  const resourceSets = new Map<string, Set<string>>();
  const groupCatCost = new Map<string, Map<string, number>>();
  let totalCost = 0, riCost = 0, paygCost = 0;
  let periodStart = '', periodEnd = '', currency = 'INR', customerName = '';

  for (const r of rows) {
    const cost = num(r.costInBillingCurrency ?? r.CostInBillingCurrency ?? r.cost);
    const qty = num(r.quantity ?? r.Quantity);
    const model: 'RI' | 'PAYG' =
      String(r.pricingModel).toLowerCase() === 'reservation' || String(r.chargeType).toLowerCase() === 'purchase'
        ? 'RI'
        : 'PAYG';
    const resourceFull = String(r[resourceCol] ?? '').trim();
    const resourceName = resourceFull.split('/').filter(Boolean).pop() || '';
    const meterCategory = String(r.meterCategory ?? '').trim();
    const isRiPurchase = String(r.chargeType ?? '').toLowerCase() === 'purchase';

    let groupName: string;
    if (groupBy === 'resource') {
      // One bar per resource, exactly how the old portal organised projects.
      // RI purchases have no meaningful resource (reservation order GUID), so
      // they get their own "Reserved Instances" bar.
      if (isRiPurchase || (model === 'RI' && !resourceName)) groupName = 'Reserved Instances';
      else groupName = resourceName || meterCategory || 'Other';
    } else {
      groupName = String(r[groupBy] ?? '').trim() || 'Other';
    }
    const productName = String(r[productBy] ?? r.meterName ?? r.ProductName ?? '').trim() || 'Unknown';
    // Full path keeps item identity unique even when short names repeat.
    const resource = resourceFull;

    totalCost += cost;
    if (model === 'RI') riCost += cost; else paygCost += cost;
    if (r.billingPeriodStartDate) periodStart = String(r.billingPeriodStartDate);
    if (r.billingPeriodEndDate) periodEnd = String(r.billingPeriodEndDate);
    if (r.billingCurrency) currency = String(r.billingCurrency);
    if (r.customerName) customerName = String(r.customerName);

    if (!groups.has(groupName)) groups.set(groupName, new Map());
    const items = groups.get(groupName)!;
    if (!groupCatCost.has(groupName)) groupCatCost.set(groupName, new Map());
    if (meterCategory) {
      const cc = groupCatCost.get(groupName)!;
      cc.set(meterCategory, (cc.get(meterCategory) ?? 0) + Math.abs(cost));
    }
    const key = `${productName}||${model}`;
    if (!items.has(key)) {
      items.set(key, {
        key, productName, model,
        meterCategory: String(r.meterCategory ?? ''),
        serviceFamily: String(r.serviceFamily ?? ''),
        quantity: 0, cost: 0, rowCount: 0, unitPrice: 0,
        unitOfMeasure: String(r.unitOfMeasure ?? ''),
        subscription: String(r.subscriptionName ?? ''),
        resource: resource,
        resourceCount: 0,
        include: true,
      });
      resourceSets.set(`${groupName}||${key}`, new Set());
    }
    const it = items.get(key)!;
    if (resource && !it.resource) it.resource = resource;
    if (resource) resourceSets.get(`${groupName}||${key}`)!.add(resource);
    it.quantity += qty;
    it.cost += cost;
    it.rowCount += 1;
  }

  const groupArr: PreviewGroup[] = [...groups.entries()]
    .map(([groupKey, items]) => {
      const itemArr = [...items.values()]
        .map((it) => ({
          ...it,
          cost: round2(it.cost),
          quantity: round4(it.quantity),
          resourceCount: resourceSets.get(`${groupKey}||${it.key}`)?.size ?? (it.resource ? 1 : 0),
          unitPrice: it.quantity > 0 ? round4(it.cost / it.quantity) : round4(it.cost),
        }))
        .sort((a, b) => b.cost - a.cost);
      // Old-portal style bar name: "<dominant category> - <resource name>".
      let header = groupKey;
      if (groupBy === 'resource' && groupKey !== 'Reserved Instances') {
        const cats = groupCatCost.get(groupKey);
        const topCat = cats && cats.size
          ? [...cats.entries()].sort((a, b) => b[1] - a[1])[0][0]
          : '';
        header = topCat && !groupKey.startsWith(topCat) ? `${topCat} - ${groupKey}` : groupKey;
      }
      return { header, totalCost: round2(itemArr.reduce((s, i) => s + i.cost, 0)), items: itemArr };
    })
    .sort((a, b) => b.totalCost - a.totalCost);

  return {
    summary: {
      rowCount: rows.length,
      totalCost: round2(totalCost),
      riCost: round2(riCost),
      paygCost: round2(paygCost),
      currency,
      periodStart,
      periodEnd,
      groupBy,
      productBy,
      resourceBy: resourceCol,
      customerName,
    },
    groups: groupArr,
    columns,
  };
}

/** Union of column keys across a sample of rows (Azure exports can vary row-to-row). */
function collectColumns(rows: ConsumptionRow[]): string[] {
  const seen = new Set<string>();
  const limit = Math.min(rows.length, 200);
  for (let i = 0; i < limit; i++) {
    for (const k of Object.keys(rows[i])) seen.add(k);
  }
  return [...seen];
}
