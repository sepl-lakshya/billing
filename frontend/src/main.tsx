import React from 'react';
import ReactDOM from 'react-dom/client';
import { BrowserRouter } from 'react-router-dom';
import { MantineProvider } from '@mantine/core';
import { Notifications } from '@mantine/notifications';
import { ModalsProvider } from '@mantine/modals';
import { MotionConfig } from 'framer-motion';
import '@mantine/core/styles.css';
import '@mantine/notifications/styles.css';
import '@mantine/dropzone/styles.css';
import '@mantine/dates/styles.css';
import App from './App';
import { theme } from './theme';
import { ToastProvider } from './state/ToastContext';
import { LookupProvider } from './state/LookupContext';
import { ConfirmProvider } from './state/ConfirmContext';
import { AuthProvider } from './state/AuthContext';
import ErrorBoundary from './components/ErrorBoundary';
import './index.css';

ReactDOM.createRoot(document.getElementById('root')!).render(
  <React.StrictMode>
    <ErrorBoundary>
      <MantineProvider theme={theme} defaultColorScheme="light">
        <Notifications position="top-right" />
        <ModalsProvider>
          <BrowserRouter>
            <AuthProvider>
              <ToastProvider>
                <ConfirmProvider>
                  <LookupProvider>
                    <MotionConfig reducedMotion="user">
                      <App />
                    </MotionConfig>
                  </LookupProvider>
                </ConfirmProvider>
              </ToastProvider>
            </AuthProvider>
          </BrowserRouter>
        </ModalsProvider>
      </MantineProvider>
    </ErrorBoundary>
  </React.StrictMode>
);
