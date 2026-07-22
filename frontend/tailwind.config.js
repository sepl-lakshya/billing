/** @type {import('tailwindcss').Config} */
export default {
  content: ['./index.html', './src/**/*.{ts,tsx}'],
  // Preflight is disabled so Tailwind utilities layer on top of the existing
  // hand-written design system instead of resetting it.
  corePlugins: { preflight: false },
  theme: {
    extend: {
      colors: {
        brand: {
          50: '#eef4ff',
          100: '#dbe6ff',
          200: '#bcd0ff',
          300: '#8fb0ff',
          400: '#5b85fb',
          500: '#3563f0',
          600: '#1e5bf0',
          700: '#1644c8',
          800: '#173fa9',
          900: '#182f7f',
        },
        ink: {
          500: '#6f82a2',
          700: '#33456b',
          900: '#14244d',
        },
      },
      boxShadow: {
        card: '0 1px 3px rgba(22,44,88,.10), 0 10px 30px rgba(22,44,88,.06)',
        pop: '0 20px 45px rgba(16,24,40,.18)',
      },
      borderRadius: {
        xl2: '14px',
      },
    },
  },
  plugins: [],
};
