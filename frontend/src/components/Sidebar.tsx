import { Link, useLocation } from 'react-router-dom';
import { Tooltip, Avatar } from '@mantine/core';
import {
  DashboardIcon, ProductIcon, DistributorIcon, OemIcon, PurchaseHeaderIcon,
  CloudIcon, DebitNoteIcon, CreditNoteIcon, InvoiceIcon,
  RupeeIcon, SystemIcon, ICON,
  type AppIcon,
} from '../lib/icons';

interface NavItem { to: string; label: string; icon: AppIcon; }
interface NavGroup { label: string; items: NavItem[]; }

const groups: NavGroup[] = [
  {
    label: 'Overview',
    items: [{ to: '/', label: 'Dashboard', icon: DashboardIcon }],
  },
  {
    label: 'Cloud Billing',
    items: [
      { to: '/cloud-projects', label: 'Cloud Projects', icon: CloudIcon },
    ],
  },
  {
    label: 'Finance',
    items: [
      { to: '/invoices', label: 'Invoices', icon: InvoiceIcon },
      { to: '/debit-notes', label: 'Debit Notes', icon: DebitNoteIcon },
      { to: '/credit-notes', label: 'Credit Notes', icon: CreditNoteIcon },
    ],
  },
  {
    label: 'Masters',
    items: [
      { to: '/products', label: 'Products', icon: ProductIcon },
      { to: '/purchase-headers', label: 'Purchase Headers', icon: PurchaseHeaderIcon },
      { to: '/oem', label: 'OEM', icon: OemIcon },
      { to: '/distributors', label: 'Distributors', icon: DistributorIcon },
    ],
  },
  {
    label: 'Administration',
    items: [
      { to: '/system', label: 'System Status', icon: SystemIcon },
    ],
  },
];

function isActive(pathname: string, to: string) {
  if (to === '/') return pathname === '/';
  return pathname === to || pathname.startsWith(to + '/');
}

export default function Sidebar({
  collapsed, onToggle, onNavigate, showToggle = true,
}: {
  collapsed: boolean;
  onToggle?: () => void;
  onNavigate: () => void;
  showToggle?: boolean;
}) {
  const { pathname } = useLocation();

  return (
    <div className={`sb-root${collapsed ? ' sb-collapsed' : ''}`}>
      <div className="sb-brand">
        <Tooltip label={collapsed ? 'Expand sidebar' : 'Collapse sidebar'} position="right" withArrow disabled={!showToggle}>
          <button
            className="sb-logo"
            onClick={showToggle ? onToggle : undefined}
            aria-label={collapsed ? 'Expand sidebar' : 'Collapse sidebar'}
          >
            <RupeeIcon size={ICON.xl} />
          </button>
        </Tooltip>
        {!collapsed && (
          <div className="sb-brand-copy">
            <strong>BILLING</strong>
            <span>SEPL Portal</span>
          </div>
        )}
      </div>

      <nav className="sb-nav">
        {groups.map((g) => (
          <div className="sb-group" key={g.label}>
            {!collapsed && <div className="sb-group-label">{g.label}</div>}
            {g.items.map((n) => {
              const active = isActive(pathname, n.to);
              const link = (
                <Link key={n.to} to={n.to} onClick={onNavigate} className={`sb-link${active ? ' active' : ''}`}>
                  <span className="sb-ico"><n.icon size={ICON.lg} /></span>
                  {!collapsed && <span>{n.label}</span>}
                </Link>
              );
              return collapsed ? (
                <Tooltip key={n.to} label={n.label} position="right" withArrow offset={10}>{link}</Tooltip>
              ) : link;
            })}
          </div>
        ))}
      </nav>

      <div className="sb-foot">
        <Avatar color="brand" variant="white" radius="xl" size={collapsed ? 34 : 38}>SU</Avatar>
        {!collapsed && (
          <div className="sb-foot-copy">
            <strong>System User</strong>
            <span>SEPL Billing</span>
          </div>
        )}
      </div>
    </div>
  );
}
