import React, { ReactNode } from 'react';
import { Alert, Stack, Title, Text, Button, Box } from '@mantine/core';
import { WarningIcon, RefreshIcon, ICON } from '../lib/icons';

interface Props {
  children: ReactNode;
}

interface State {
  hasError: boolean;
  error?: Error;
}

/**
 * Catch component errors and boundary crashes, preventing the entire app from
 * going blank. Shows a recoverable error page so the user can refresh or navigate.
 */
export default class ErrorBoundary extends React.Component<Props, State> {
  constructor(props: Props) {
    super(props);
    this.state = { hasError: false };
  }

  static getDerivedStateFromError(error: Error): State {
    return { hasError: true, error };
  }

  componentDidCatch(error: Error, info: React.ErrorInfo) {
    // eslint-disable-next-line no-console
    console.error('Error boundary caught:', error, info);
  }

  render() {
    if (this.state.hasError) {
      return (
        <Box p="xl" style={{ minHeight: '100vh', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
          <Stack align="center" gap="md" maw={500}>
            <WarningIcon size={48} color="red" />
            <div style={{ textAlign: 'center' }}>
              <Title order={3}>Something went wrong</Title>
              <Text c="dimmed" mt={6}>
                {this.state.error?.message || 'The application encountered an unexpected error.'}
              </Text>
            </div>
            <Stack gap={8} w="100%">
              <Button
                leftSection={<RefreshIcon size={ICON.sm} />}
                onClick={() => {
                  this.setState({ hasError: false });
                  window.location.reload();
                }}
              >
                Refresh page
              </Button>
              <Button variant="subtle" onClick={() => (window.location.href = '/')}>
                Go to home
              </Button>
            </Stack>
          </Stack>
        </Box>
      );
    }

    return this.props.children;
  }
}
