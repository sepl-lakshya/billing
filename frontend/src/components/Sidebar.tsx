import { Link, useLocation } from 'react-router-dom';
import { Tooltip, Avatar } from '@mantine/core';
import {
  DashboardIcon, ProductIcon, DistributorIcon, OemIcon, PurchaseHeaderIcon,
  CloudIcon, DebitNoteIcon, CreditNoteIcon, InvoiceIcon,
  RupeeIcon, SystemIcon, HomeIcon, CollapseIcon, ExpandIcon, ICON,
  type AppIcon,
} from '../lib/icons';
import { useAuth } from '../state/AuthContext';
import { homeUrl } from '../lib/sso';

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
  const { user } = useAuth();
  const displayName = user?.name || 'System User';
  const initials =
    displayName.split(/\s+/).map((s) => s[0]).filter(Boolean).slice(0, 2).join('').toUpperCase() || 'SU';

  return (
    <div className={`sb-root${collapsed ? ' sb-collapsed' : ''}`}>
      {/* Brand + collapse toggle */}
      <div className="sb-head">
        <div className="sb-brand">
          <div className="sb-logo"><RupeeIcon size={ICON.lg} /></div>
          {!collapsed && (
            <div className="sb-brand-copy">
              <strong>BILLING</strong>
              <span>SEPL Portal</span>
            </div>
          )}
        </div>
        {showToggle && (
          <Tooltip label={collapsed ? 'Expand' : 'Collapse'} position="right" withArrow offset={12}>
            <button
              type="button"
              className="sb-toggle"
              onClick={onToggle}
              aria-label={collapsed ? 'Expand sidebar' : 'Collapse sidebar'}
            >
              {collapsed ? <ExpandIcon size={ICON.sm} /> : <CollapseIcon size={ICON.sm} />}
            </button>
          </Tooltip>
        )}
      </div>

      {/* Home — main dashboard of all applications (external, env-driven URL) */}
      <div className="sb-home-wrap">
        {collapsed ? (
          <Tooltip label="Main Dashboard" position="right" withArrow offset={12}>
            <a href={homeUrl} className="sb-link sb-home" aria-label="Main Dashboard">
              <span className="sb-ico"><HomeIcon size={ICON.lg} /></span>
            </a>
          </Tooltip>
        ) : (
          <a href={homeUrl} className="sb-link sb-home">
            <span className="sb-ico"><HomeIcon size={ICON.lg} /></span>
            <span>Main Dashboard</span>
          </a>
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
                <Tooltip key={n.to} label={n.label} position="right" withArrow offset={12}>{link}</Tooltip>
              ) : link;
            })}
          </div>
        ))}
      </nav>

      <div className="sb-foot">
        <Avatar color="brand" variant="white" radius="xl" size={collapsed ? 32 : 36}>{initials}</Avatar>
        {!collapsed && (
          <div className="sb-foot-copy">
            <strong>{displayName}</strong>
            <span>{user?.email || 'SEPL Billing'}</span>
          </div>
        )}
      </div>
    </div>
  );
}
