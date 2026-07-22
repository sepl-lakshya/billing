import { createTheme, type MantineColorsTuple } from '@mantine/core';

// SEPL app blue — primary shade 6 == #1e5bf0 (shared identity across SEPL apps).
const brand: MantineColorsTuple = [
  '#e8f0ff', '#d3e0ff', '#a6bdff', '#7798ff', '#5079fb',
  '#3766f5', '#1e5bf0', '#1644c8', '#0f39a0', '#062879',
];
// Deep navy neutrals used by dark surfaces.
const ink: MantineColorsTuple = [
  '#eef2fa', '#dbe3f2', '#b7c5e3', '#93a6d3', '#7289c2',
  '#5670ab', '#40588f', '#2e4272', '#1d3161', '#14244d',
];

export const theme = createTheme({
  primaryColor: 'brand',
  primaryShade: { light: 6, dark: 5 },
  colors: { brand, ink },
  defaultGradient: { from: 'brand.5', to: 'brand.7', deg: 135 },
  fontFamily:
    "'Inter', 'Segoe UI', system-ui, -apple-system, Roboto, Helvetica, Arial, sans-serif",
  fontFamilyMonospace: "'IBM Plex Mono', ui-monospace, 'Cascadia Mono', monospace",
  defaultRadius: 'md',
  focusRing: 'auto',
  cursorType: 'pointer',
  respectReducedMotion: true,
  // Radius scale mirrors the CSS design tokens in index.css.
  radius: { xs: '4px', sm: '6px', md: '10px', lg: '14px', xl: '20px' },
  headings: {
    fontWeight: '600',
    sizes: {
      h1: { fontSize: '1.45rem', lineHeight: '1.25' },
      h2: { fontSize: '1.15rem' },
      h3: { fontSize: '1rem' },
    },
  },
  // Layered soft shadows (navy tint) matching the design system.
  shadows: {
    xs: '0 1px 2px rgba(22, 44, 88, .06)',
    sm: '0 1px 2px rgba(22, 44, 88, .06), 0 2px 6px rgba(22, 44, 88, .07)',
    md: '0 4px 10px rgba(22, 44, 88, .08), 0 10px 26px rgba(22, 44, 88, .08)',
    lg: '0 12px 28px rgba(22, 44, 88, .12), 0 6px 12px rgba(22, 44, 88, .07)',
    xl: '0 24px 56px rgba(16, 24, 40, .18)',
  },
  // Non-visual design tokens (consumed via useMantineTheme().other).
  other: {
    iconSize: { xs: 14, sm: 16, md: 18, lg: 20, xl: 24 },
    controlHeight: { sm: 30, md: 36, lg: 42 },
    zIndex: { dropdown: 1000, sticky: 1100, overlay: 1200, modal: 1300, popover: 1400, toast: 1500, tooltip: 1600 },
  },
  components: {
    Card: { defaultProps: { withBorder: true, shadow: 'sm', radius: 'md' } },
    Paper: { defaultProps: { radius: 'md' } },
    Button: { defaultProps: { radius: 'md' } },
    ActionIcon: { defaultProps: { radius: 'md' } },
    Badge: { defaultProps: { radius: 'sm' } },
    Menu: { defaultProps: { radius: 'md', shadow: 'lg', transitionProps: { transition: 'pop', duration: 160 } } },
    Popover: { defaultProps: { radius: 'md', shadow: 'lg' } },
    Notification: { defaultProps: { radius: 'md' } },
    Tooltip: {
      defaultProps: {
        radius: 'md', withArrow: true, arrowSize: 6,
        transitionProps: { transition: 'fade', duration: 150 },
      },
    },
    Modal: {
      defaultProps: {
        radius: 'lg', centered: true, shadow: 'xl',
        overlayProps: { blur: 3, backgroundOpacity: 0.45 },
        transitionProps: { transition: 'pop', duration: 200 },
      },
    },
    Drawer: {
      defaultProps: {
        overlayProps: { blur: 3, backgroundOpacity: 0.45 },
      },
    },
  },
});
