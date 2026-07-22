/**
 * Central icon registry — the single source of truth for iconography.
 *
 * Every component/page must import icons from here (never directly from
 * 'lucide-react' or any other icon package). Swapping an icon in one place
 * updates it across the whole application, and it guarantees we never mix
 * icon libraries.
 */
export {
  // ---- Actions ----
  Plus as AddIcon,
  Pencil as EditIcon,
  Trash2 as DeleteIcon,
  RefreshCw as RefreshIcon,
  Search as SearchIcon,
  X as CloseIcon,
  Check as CheckIcon,
  ExternalLink as OpenIcon,
  Upload as UploadIcon,
  Download as DownloadIcon,
  Copy as CopyIcon,
  EllipsisVertical as MoreIcon,
  Filter as FilterIcon,
  Settings as SettingsIcon,
  Eye as ViewIcon,
  Info as InfoIcon,
  TriangleAlert as WarningIcon,
  CircleCheck as SuccessIcon,
  CircleX as ErrorIcon,
  Sparkles as SparklesIcon,
  Ban as DisableIcon,
  GripVertical as GripIcon,

  // ---- Directional ----
  ChevronUp as ChevronUpIcon,
  ChevronDown as ChevronDownIcon,
  ChevronRight as ChevronRightIcon,
  ChevronsUpDown as SortIcon,
  ArrowUp as ArrowUpIcon,
  ArrowDown as ArrowDownIcon,
  ArrowLeft as BackIcon,
  ArrowUpDown as MoveVerticalIcon,

  // ---- Entities / navigation ----
  LayoutDashboard as DashboardIcon,
  Cloud as CloudIcon,
  Package as ProductIcon,
  Truck as DistributorIcon,
  Factory as OemIcon,
  Receipt as InvoiceIcon,
  ReceiptText as PurchaseHeaderIcon,
  FileMinus as DebitNoteIcon,
  FilePlus as CreditNoteIcon,
  ServerCog as SystemIcon,
  ServerOff as ServerOffIcon,
  Server as ServerIcon,
  IndianRupee as RupeeIcon,
  CalendarDays as CalendarIcon,
  Clock as ClockIcon,
  ChartColumn as ChartIcon,
  CloudUpload as CloudUploadIcon,
  FileSpreadsheet as SpreadsheetIcon,
  Table as TableIcon,
  Layers as LayersIcon,
  Hash as IdIcon,
  Inbox as EmptyIcon,

  type LucideIcon as AppIcon,
} from 'lucide-react';

/** Standard icon sizes (px). Consume these instead of hardcoding numbers. */
export const ICON = { xs: 14, sm: 16, md: 18, lg: 20, xl: 24 } as const;
export type IconSize = keyof typeof ICON;
