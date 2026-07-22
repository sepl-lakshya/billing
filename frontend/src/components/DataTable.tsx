import { ReactNode, useMemo, useState } from 'react';
import { Table, ScrollArea, Skeleton, TextInput, Pagination, ActionIcon, Text } from '@mantine/core';
import { SearchIcon, CloseIcon, ChevronUpIcon, ChevronDownIcon, SortIcon, ICON } from '../lib/icons';
import { EmptyState } from './ui';

export interface Column<T> {
  header: string;
  cell: (row: T, index: number) => ReactNode;
  className?: string;
  /** Enable click-to-sort on this column header (requires `sortValue`). */
  sortable?: boolean;
  /** Comparable value used when this column drives the sort. */
  sortValue?: (row: T) => string | number;
}

interface DataTableProps<T> {
  columns: Column<T>[];
  rows: T[];
  loading?: boolean;
  empty?: string;
  keyField?: (row: T, index: number) => string | number;
  minWidth?: number;
  stickyHeader?: boolean;
  /** Show a global search box that filters the rows client-side. */
  searchable?: boolean;
  searchPlaceholder?: string;
  /** Custom searchable-text extractor; defaults to all string/number fields. */
  searchAccessor?: (row: T) => string;
  /** Enable client-side pagination with the given page size. */
  pageSize?: number;
  /** Extra controls rendered on the right side of the toolbar (e.g. export). */
  toolbar?: ReactNode;
}

type SortState = { key: number; dir: 'asc' | 'desc' } | null;

function defaultSearchText(row: unknown): string {
  if (row && typeof row === 'object') {
    return Object.values(row as Record<string, unknown>)
      .filter((v) => typeof v === 'string' || typeof v === 'number')
      .join(' ');
  }
  return String(row ?? '');
}

export default function DataTable<T>({
  columns, rows, loading, empty = 'No records found', keyField, minWidth = 720,
  stickyHeader = true, searchable, searchPlaceholder = 'Search…', searchAccessor, pageSize, toolbar,
}: DataTableProps<T>) {
  const [query, setQuery] = useState('');
  const [sort, setSort] = useState<SortState>(null);
  const [page, setPage] = useState(1);

  const onSearch = (v: string) => { setQuery(v); setPage(1); };
  const toggleSort = (key: number) => {
    setPage(1);
    setSort((s) => (s && s.key === key ? (s.dir === 'asc' ? { key, dir: 'desc' } : null) : { key, dir: 'asc' }));
  };

  const processed = useMemo(() => {
    let out = rows;
    const q = query.trim().toLowerCase();
    if (q) {
      out = out.filter((row) =>
        (searchAccessor ? searchAccessor(row) : defaultSearchText(row)).toLowerCase().includes(q),
      );
    }
    if (sort) {
      const sv = columns[sort.key]?.sortValue;
      if (sv) {
        const dir = sort.dir === 'asc' ? 1 : -1;
        out = [...out].sort((a, b) => {
          const va = sv(a);
          const vb = sv(b);
          if (typeof va === 'number' && typeof vb === 'number') return (va - vb) * dir;
          return String(va).localeCompare(String(vb), undefined, { numeric: true, sensitivity: 'base' }) * dir;
        });
      }
    }
    return out;
  }, [rows, query, sort, columns, searchAccessor]);

  const total = processed.length;
  const totalPages = pageSize ? Math.max(1, Math.ceil(total / pageSize)) : 1;
  const safePage = Math.min(page, totalPages);
  const start = pageSize ? (safePage - 1) * pageSize : 0;
  const visible = pageSize ? processed.slice(start, start + pageSize) : processed;

  const showToolbar = searchable || Boolean(toolbar);

  return (
    <div className="dt">
      {showToolbar && (
        <div className="dt-toolbar">
          {searchable ? (
            <TextInput
              className="dt-search"
              value={query}
              onChange={(e) => onSearch(e.currentTarget.value)}
              placeholder={searchPlaceholder}
              size="sm"
              aria-label="Search table"
              leftSection={<SearchIcon size={ICON.sm} />}
              rightSection={query ? (
                <ActionIcon variant="subtle" color="gray" size="sm" aria-label="Clear search" onClick={() => onSearch('')}>
                  <CloseIcon size={ICON.sm} />
                </ActionIcon>
              ) : null}
            />
          ) : <span />}
          {toolbar && <div className="dt-toolbar-actions">{toolbar}</div>}
        </div>
      )}

      <ScrollArea type="auto" offsetScrollbars>
        <Table striped highlightOnHover stickyHeader={stickyHeader} verticalSpacing="sm" horizontalSpacing="md" miw={minWidth} style={{ whiteSpace: 'nowrap' }}>
          <Table.Thead>
            <Table.Tr>
              {columns.map((c, i) => (
                <Table.Th key={i} className={c.className}>
                  {c.sortable && c.sortValue ? (
                    <button type="button" className={`dt-sort${sort?.key === i ? ' is-active' : ''}`} onClick={() => toggleSort(i)}>
                      <span>{c.header}</span>
                      {sort?.key === i
                        ? (sort.dir === 'asc' ? <ChevronUpIcon size={ICON.xs} /> : <ChevronDownIcon size={ICON.xs} />)
                        : <SortIcon size={ICON.xs} className="dt-sort-idle" />}
                    </button>
                  ) : c.header}
                </Table.Th>
              ))}
            </Table.Tr>
          </Table.Thead>
          <Table.Tbody>
            {loading ? (
              Array.from({ length: 6 }).map((_, r) => (
                <Table.Tr key={`sk-${r}`}>
                  {columns.map((_, c) => (
                    <Table.Td key={c}><Skeleton height={14} radius="sm" width={c === 0 ? '55%' : '85%'} /></Table.Td>
                  ))}
                </Table.Tr>
              ))
            ) : visible.length === 0 ? (
              <Table.Tr>
                <Table.Td colSpan={columns.length}>
                  <EmptyState
                    title={query ? 'No matches' : 'Nothing here yet'}
                    description={query ? `No results for “${query}”.` : empty}
                  />
                </Table.Td>
              </Table.Tr>
            ) : (
              visible.map((row, i) => {
                const idx = start + i;
                return (
                  <Table.Tr key={keyField ? keyField(row, idx) : idx}>
                    {columns.map((c, j) => (
                      <Table.Td key={j} className={c.className}>{c.cell(row, idx)}</Table.Td>
                    ))}
                  </Table.Tr>
                );
              })
            )}
          </Table.Tbody>
        </Table>
      </ScrollArea>

      {pageSize && !loading && total > 0 && (
        <div className="dt-foot">
          <Text className="dt-count" c="dimmed" fz="xs">
            Showing {start + 1}–{Math.min(start + pageSize, total)} of {total}
          </Text>
          {totalPages > 1 && (
            <Pagination size="sm" value={safePage} total={totalPages} onChange={setPage} withEdges />
          )}
        </div>
      )}
    </div>
  );
}
