import { Routes, Route, Navigate } from 'react-router-dom';
import Dashboard from './pages/Dashboard';
import Products from './pages/masters/Products';
import Oem from './pages/masters/Oem';
import Distributors from './pages/masters/Distributors';
import PurchaseHeaders from './pages/masters/PurchaseHeaders';
import CloudProjects from './pages/cloud/CloudProjects';
import ViewCloudProject from './pages/cloud/ViewCloudProject';
import Invoices from './pages/finance/Invoices';
import DebitNotes from './pages/finance/DebitNotes';
import CreditNotes from './pages/finance/CreditNotes';
import System from './pages/System';

export default function App() {
  return (
    <Routes>
      <Route path="/" element={<Dashboard />} />
      <Route path="/products" element={<Products />} />
      <Route path="/distributors" element={<Distributors />} />
      <Route path="/oem" element={<Oem />} />
      <Route path="/purchase-headers" element={<PurchaseHeaders />} />

      <Route path="/cloud-projects" element={<CloudProjects />} />
      <Route path="/cloud-projects/:hash" element={<ViewCloudProject />} />

      <Route path="/invoices" element={<Invoices />} />
      <Route path="/debit-notes" element={<DebitNotes />} />
      <Route path="/credit-notes" element={<CreditNotes />} />

      <Route path="/system" element={<System />} />

      <Route path="*" element={<Navigate to="/" replace />} />
    </Routes>
  );
}
