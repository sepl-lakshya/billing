import { Routes, Route, Navigate } from 'react-router-dom';
import { Center, Loader, Stack, Text } from '@mantine/core';
import { useAuth } from './state/AuthContext';
import { isCentralAuth } from './lib/sso';
import AccessDenied from './pages/AccessDenied';
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
  const { loading, isAuthenticated, forbidden } = useAuth();

  if (loading) {
    return (
      <Center h="100vh">
        <Loader />
      </Center>
    );
  }

  // Central SSO: the browser is redirecting to the shared login — show a brief
  // placeholder instead of a flash of the app.
  if (isCentralAuth && !isAuthenticated) {
    return (
      <Center h="100vh">
        <Stack align="center" gap="xs">
          <Loader />
          <Text size="sm" c="dimmed">Redirecting to sign in…</Text>
        </Stack>
      </Center>
    );
  }

  // Signed in but without billing access → dedicated screen (with Home button).
  if (forbidden) return <AccessDenied />;

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
